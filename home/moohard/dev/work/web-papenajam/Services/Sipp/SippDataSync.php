<?php

namespace App\Services\Sipp;

use App\Enums\SyncStatus;
use App\Enums\SyncType;
use App\Models\Sipp\CaseModel;
use App\Models\Sipp\CaseType as SippCaseType;
use App\Models\Sipp\CourtRoom;
use App\Models\Sipp\CourtSchedule;
use App\Models\Sipp\Judge;
use App\Models\Sipp\SyncLog;
use App\Notifications\SippSyncFailedNotification;
use App\Services\Sipp\Exceptions\SippApiException;
use App\Services\Sipp\Exceptions\SippAuthenticationException;
use App\Services\Sipp\Exceptions\SippRateLimitException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

class SippDataSync
{
    protected SippApiClient $apiClient;

    protected bool $isRunning = false;

    protected array $syncStats = [];

    protected int $batchSize;

    protected int $conflictResolutionStrategy;

    protected bool $sendNotifications;

    public const CONFLICT_STRATEGY_SIPP_WINS = 1;

    public const CONFLICT_STRATEGY_LOCAL_WINS = 2;

    public const CONFLICT_STRATEGY_LATEST_WINS = 3;

    public const CONFLICT_STRATEGY_MANUAL = 4;

    public function __construct(?SippApiClient $apiClient = null)
    {
        $this->apiClient = $apiClient ?? new SippApiClient();
        $this->batchSize = config('sipp.sync.batch_size', 100);
        $this->conflictResolutionStrategy = config('sipp.sync.conflict_resolution', self::CONFLICT_STRATEGY_LATEST_WINS);
        $this->sendNotifications = config('sipp.sync.notifications.enabled', true);
    }

    /**
     * Run full sync of all data.
     *
     * @param bool $force
     * @return array
     */
    public function fullSync(bool $force = false): array
    {
        if ($this->isRunning) {
            return [
                'success' => false,
                'message' => 'Sync is already running',
            ];
        }

        $this->isRunning = true;
        $this->resetStats();

        $syncLog = $this->startSyncLog(SyncType::FULL);

        try {
            $this->syncSchedules(true);
            $this->syncCases(true);
            $this->syncJudges();
            $this->syncCourtRooms();
            $this->syncCaseTypes();

            $this->completeSyncLog($syncLog, true);

            $this->isRunning = false;

            return $this->getStats();
        } catch (Throwable $e) {
            $this->handleSyncFailure($syncLog, $e);

            $this->isRunning = false;

            throw $e;
        }
    }

    /**
     * Run incremental sync (only new/updated records).
     *
     * @param Carbon|null $since
     * @return array
     */
    public function incrementalSync(?Carbon $since = null): array
    {
        if ($this->isRunning) {
            return [
                'success' => false,
                'message' => 'Sync is already running',
            ];
        }

        $this->isRunning = true;
        $this->resetStats();

        $syncLog = $this->startSyncLog(SyncType::INCREMENTAL);

        $since = $since ?? SyncLog::getLastSuccessfulSyncDate() ?? now()->subHours(1);

        try {
            $this->syncSchedules(false, $since);
            $this->syncCases(false, $since);

            $this->completeSyncLog($syncLog, true);

            $this->isRunning = false;

            return $this->getStats();
        } catch (Throwable $e) {
            $this->handleSyncFailure($syncLog, $e);

            $this->isRunning = false;

            throw $e;
        }
    }

    /**
     * Sync schedules from SIPP API.
     *
     * @param bool $fullSync
     * @param Carbon|null $since
     * @return array
     */
    public function syncSchedules(bool $fullSync = false, ?Carbon $since = null): array
    {
        Log::info('Starting schedules sync', ['full_sync' => $fullSync, 'since' => $since?->toDateTimeString()]);

        $this->syncStats['schedules']['started_at'] = now();

        $totalSynced = 0;
        $totalUpdated = 0;
        $totalFailed = 0;
        $page = 1;
        $hasMore = true;

        DB::beginTransaction();

        try {
            while ($hasMore) {
                $filters = [
                    'page' => $page,
                    'limit' => $this->batchSize,
                ];

                if (!$fullSync && $since) {
                    $filters['updated_since'] = $since->toIso8601String();
                }

                $response = $this->apiClient->getSchedules($filters);

                $schedules = $response['data'] ?? $response ?? [];
                $hasMore = !empty($response['next_page_url'] ?? null) || count($schedules) >= $this->batchSize;

                foreach ($schedules as $scheduleData) {
                    try {
                        $result = $this->upsertSchedule($scheduleData);

                        if ($result['action'] === 'created') {
                            $totalSynced++;
                        } elseif ($result['action'] === 'updated') {
                            $totalUpdated++;
                        }
                    } catch (Throwable $e) {
                        $totalFailed++;
                        Log::error('Failed to sync schedule', [
                            'sipp_case_id' => $scheduleData['id'] ?? 'unknown',
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                $page++;
            }

            DB::commit();

            $this->syncStats['schedules'] = [
                'synced' => $totalSynced,
                'updated' => $totalUpdated,
                'failed' => $totalFailed,
                'started_at' => $this->syncStats['schedules']['started_at'],
                'completed_at' => now(),
            ];

            Log::info('Schedules sync completed', $this->syncStats['schedules']);

            return $this->syncStats['schedules'];
        } catch (Throwable $e) {
            DB::rollBack();

            $this->syncStats['schedules']['failed'] = $totalFailed + 1;
            $this->syncStats['schedules']['error'] = $e->getMessage();

            throw $e;
        }
    }

    /**
     * Upsert schedule record.
     *
     * @param array $data
     * @return array
     */
    protected function upsertSchedule(array $data): array
    {
        $sippCaseId = $data['id'] ?? $data['case_id'] ?? null;

        if (!$sippCaseId) {
            throw new \InvalidArgumentException('Schedule data missing case ID');
        }

        $existing = CourtSchedule::where('sipp_case_id', $sippCaseId)->first();

        $scheduleData = $this->mapScheduleData($data);

        if (!$existing) {
            $schedule = CourtSchedule::create($scheduleData);

            return [
                'action' => 'created',
                'id' => $schedule->id,
            ];
        }

        if ($this->shouldUpdate($existing, $scheduleData)) {
            $existing->update($scheduleData);

            return [
                'action' => 'updated',
                'id' => $existing->id,
            ];
        }

        return [
            'action' => 'unchanged',
            'id' => $existing->id,
        ];
    }

    /**
     * Map SIPP API data to schedule model data.
     *
     * @param array $data
     * @return array
     */
    protected function mapScheduleData(array $data): array
    {
        return [
            'sipp_case_id' => $data['id'] ?? $data['case_id'] ?? null,
            'case_number' => $data['case_number'] ?? $data['nomor_perkara'] ?? null,
            'case_title' => $data['case_title'] ?? $data['pihak'] ?? null,
            'case_type' => $data['case_type'] ?? $data['jenis_perkara'] ?? null,
            'judge_name' => $data['judge_name'] ?? $data['majelis_hakim'] ?? null,
            'court_room' => $data['court_room'] ?? $data['ruang_sidang'] ?? null,
            'scheduled_date' => isset($data['scheduled_date']) ? Carbon::parse($data['scheduled_date'])->toDateString() : null,
            'scheduled_time' => isset($data['scheduled_time']) ? Carbon::parse($data['scheduled_time'])->format('H:i:s') : null,
            'status' => $this->mapScheduleStatus($data['status'] ?? $data['keadaan_perkara'] ?? 'scheduled'),
            'agenda' => $data['agenda'] ?? $data['agenda_perkara'] ?? null,
            'notes' => $data['notes'] ?? $data['keterangan'] ?? null,
            'sync_status' => SyncStatus::SYNCED->value,
            'last_synced_at' => now(),
        ];
    }

    /**
     * Map SIPP status to local status.
     *
     * @param string $sippStatus
     * @return string
     */
    protected function mapScheduleStatus(string $sippStatus): string
    {
        $statusMap = [
            'scheduled' => 'scheduled',
            'sedang_disidangkan' => 'in_progress',
            'putus' => 'completed',
            'ditunda' => 'postponed',
            'diberhentikan' => 'dismissed',
            'cancelled' => 'cancelled',
        ];

        return $statusMap[strtolower($sippStatus)] ?? 'scheduled';
    }

    /**
     * Sync cases from SIPP API.
     *
     * @param bool $fullSync
     * @param Carbon|null $since
     * @return array
     */
    public function syncCases(bool $fullSync = false, ?Carbon $since = null): array
    {
        Log::info('Starting cases sync', ['full_sync' => $fullSync, 'since' => $since?->toDateTimeString()]);

        $this->syncStats['cases']['started_at'] = now();

        $totalSynced = 0;
        $totalUpdated = 0;
        $totalFailed = 0;
        $page = 1;
        $hasMore = true;

        DB::beginTransaction();

        try {
            while ($hasMore) {
                $filters = [
                    'page' => $page,
                    'limit' => $this->batchSize,
                ];

                if (!$fullSync && $since) {
                    $filters['updated_since'] = $since->toIso8601String();
                }

                $response = $this->apiClient->getCases($filters);

                $cases = $response['data'] ?? $response ?? [];
                $hasMore = !empty($response['next_page_url'] ?? null) || count($cases) >= $this->batchSize;

                foreach ($cases as $caseData) {
                    try {
                        $result = $this->upsertCase($caseData);

                        if ($result['action'] === 'created') {
                            $totalSynced++;
                        } elseif ($result['action'] === 'updated') {
                            $totalUpdated++;
                        }
                    } catch (Throwable $e) {
                        $totalFailed++;
                        Log::error('Failed to sync case', [
                            'sipp_case_id' => $caseData['id'] ?? 'unknown',
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                $page++;
            }

            DB::commit();

            $this->syncStats['cases'] = [
                'synced' => $totalSynced,
                'updated' => $totalUpdated,
                'failed' => $totalFailed,
                'started_at' => $this->syncStats['cases']['started_at'],
                'completed_at' => now(),
            ];

            Log::info('Cases sync completed', $this->syncStats['cases']);

            return $this->syncStats['cases'];
        } catch (Throwable $e) {
            DB::rollBack();

            $this->syncStats['cases']['failed'] = $totalFailed + 1;
            $this->syncStats['cases']['error'] = $e->getMessage();

            throw $e;
        }
    }

    /**
     * Upsert case record.
     *
     * @param array $data
     * @return array
     */
    protected function upsertCase(array $data): array
    {
        $sippCaseId = $data['id'] ?? $data['case_id'] ?? null;

        if (!$sippCaseId) {
            throw new \InvalidArgumentException('Case data missing ID');
        }

        $existing = CaseModel::where('sipp_case_id', $sippCaseId)->first();

        $caseData = $this->mapCaseData($data);

        if (!$existing) {
            $case = CaseModel::create($caseData);

            return [
                'action' => 'created',
                'id' => $case->id,
            ];
        }

        if ($this->shouldUpdate($existing, $caseData)) {
            $existing->update($caseData);

            return [
                'action' => 'updated',
                'id' => $existing->id,
            ];
        }

        return [
            'action' => 'unchanged',
            'id' => $existing->id,
        ];
    }

    /**
     * Map SIPP API data to case model data.
     *
     * @param array $data
     * @return array
     */
    protected function mapCaseData(array $data): array
    {
        return [
            'sipp_case_id' => $data['id'] ?? $data['case_id'] ?? null,
            'case_number' => $data['case_number'] ?? $data['nomor_perkara'] ?? null,
            'case_title' => $data['case_title'] ?? $data['pihak'] ?? null,
            'case_type' => $data['case_type'] ?? $data['jenis_perkara'] ?? null,
            'registration_date' => isset($data['registration_date']) ? Carbon::parse($data['registration_date'])->toDateString() : null,
            'closing_date' => isset($data['closing_date']) ? Carbon::parse($data['closing_date'])->toDateString() : null,
            'status' => $data['status'] ?? $data['keadaan_perkara'] ?? 'open',
            'judge_name' => $data['judge_name'] ?? $data['majelis_hakim'] ?? null,
            'plaintiff' => $data['plaintiff'] ?? $data['penggugat'] ?? null,
            'defendant' => $data['defendant'] ?? $data['tergugat'] ?? null,
            'claim_amount' => $data['claim_amount'] ?? $data['nilai_perkara'] ?? null,
            'decision' => $data['decision'] ?? $data['putusan'] ?? null,
            'sync_status' => SyncStatus::SYNCED->value,
            'last_synced_at' => now(),
        ];
    }

    /**
     * Sync judges from SIPP API.
     *
     * @return array
     */
    public function syncJudges(): array
    {
        Log::info('Starting judges sync');

        $totalSynced = 0;
        $totalUpdated = 0;

        try {
            $response = $this->apiClient->getJudges();
            $judges = $response['data'] ?? $response ?? [];

            DB::beginTransaction();

            foreach ($judges as $judgeData) {
                $result = $this->upsertJudge($judgeData);

                if ($result['action'] === 'created') {
                    $totalSynced++;
                } elseif ($result['action'] === 'updated') {
                    $totalUpdated++;
                }
            }

            DB::commit();

            $this->syncStats['judges'] = [
                'synced' => $totalSynced,
                'updated' => $totalUpdated,
                'completed_at' => now(),
            ];

            Log::info('Judges sync completed', $this->syncStats['judges']);

            return $this->syncStats['judges'];
        } catch (Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }

    /**
     * Upsert judge record.
     *
     * @param array $data
     * @return array
     */
    protected function upsertJudge(array $data): array
    {
        $sippId = $data['id'] ?? null;

        if (!$sippId) {
            throw new \InvalidArgumentException('Judge data missing ID');
        }

        $existing = Judge::where('sipp_id', $sippId)->first();

        $judgeData = [
            'sipp_id' => $sippId,
            'name' => $data['name'] ?? $data['nama'] ?? null,
            'position' => $data['position'] ?? $data['jabatan'] ?? null,
            'court_name' => $data['court_name'] ?? $data['nama_pengadilan'] ?? null,
        ];

        if (!$existing) {
            $judge = Judge::create($judgeData);

            return [
                'action' => 'created',
                'id' => $judge->id,
            ];
        }

        $existing->update($judgeData);

        return [
            'action' => 'updated',
            'id' => $existing->id,
        ];
    }

    /**
     * Sync court rooms from SIPP API.
     *
     * @return array
     */
    public function syncCourtRooms(): array
    {
        Log::info('Starting court rooms sync');

        $totalSynced = 0;
        $totalUpdated = 0;

        try {
            $response = $this->apiClient->getCourtRooms();
            $rooms = $response['data'] ?? $response ?? [];

            DB::beginTransaction();

            foreach ($rooms as $roomData) {
                $result = $this->upsertCourtRoom($roomData);

                if ($result['action'] === 'created') {
                    $totalSynced++;
                } elseif ($result['action'] === 'updated') {
                    $totalUpdated++;
                }
            }

            DB::commit();

            $this->syncStats['court_rooms'] = [
                'synced' => $totalSynced,
                'updated' => $totalUpdated,
                'completed_at' => now(),
            ];

            Log::info('Court rooms sync completed', $this->syncStats['court_rooms']);

            return $this->syncStats['court_rooms'];
        } catch (Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }

    /**
     * Upsert court room record.
     *
     * @param array $data
     * @return array
     */
    protected function upsertCourtRoom(array $data): array
    {
        $name = $data['name'] ?? $data['nama'] ?? $data['room_name'] ?? null;

        if (!$name) {
            throw new \InvalidArgumentException('Court room data missing name');
        }

        $existing = CourtRoom::where('name', $name)->first();

        $roomData = [
            'name' => $name,
            'building' => $data['building'] ?? $data['gedung'] ?? null,
            'floor' => $data['floor'] ?? $data['lantai'] ?? null,
            'capacity' => $data['capacity'] ?? $data['kapasitas'] ?? null,
            'is_active' => $data['is_active'] ?? $data['aktif'] ?? true,
        ];

        if (!$existing) {
            $room = CourtRoom::create($roomData);

            return [
                'action' => 'created',
                'id' => $room->id,
            ];
        }

        $existing->update($roomData);

        return [
            'action' => 'updated',
            'id' => $existing->id,
        ];
    }

    /**
     * Sync case types from SIPP API.
     *
     * @return array
     */
    public function syncCaseTypes(): array
    {
        Log::info('Starting case types sync');

        $totalSynced = 0;
        $totalUpdated = 0;

        try {
            $response = $this->apiClient->getCaseTypes();
            $types = $response['data'] ?? $response ?? [];

            DB::beginTransaction();

            foreach ($types as $typeData) {
                $result = $this->upsertCaseType($typeData);

                if ($result['action'] === 'created') {
                    $totalSynced++;
                } elseif ($result['action'] === 'updated') {
                    $totalUpdated++;
                }
            }

            DB::commit();

            $this->syncStats['case_types'] = [
                'synced' => $totalSynced,
                'updated' => $totalUpdated,
                'completed_at' => now(),
            ];

            Log::info('Case types sync completed', $this->syncStats['case_types']);

            return $this->syncStats['case_types'];
        } catch (Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }

    /**
     * Upsert case type record.
     *
     * @param array $data
     * @return array
     */
    protected function upsertCaseType(array $data): array
    {
        $code = $data['code'] ?? $data['kode'] ?? $data['id'] ?? null;

        if (!$code) {
            throw new \InvalidArgumentException('Case type data missing code');
        }

        $existing = SippCaseType::where('code', $code)->first();

        $typeData = [
            'code' => $code,
            'name' => $data['name'] ?? $data['nama'] ?? null,
            'category' => $data['category'] ?? $data['kategori'] ?? null,
            'description' => $data['description'] ?? $data['deskripsi'] ?? null,
            'is_active' => $data['is_active'] ?? $data['aktif'] ?? true,
        ];

        if (!$existing) {
            $type = SippCaseType::create($typeData);

            return [
                'action' => 'created',
                'id' => $type->id,
            ];
        }

        $existing->update($typeData);

        return [
            'action' => 'updated',
            'id' => $existing->id,
        ];
    }

    /**
     * Determine if record should be updated based on conflict resolution strategy.
     *
     * @param mixed $existing
     * @param array $newData
     * @return bool
     */
    protected function shouldUpdate($existing, array $newData): bool
    {
        return match ($this->conflictResolutionStrategy) {
            self::CONFLICT_STRATEGY_SIPP_WINS => true,
            self::CONFLICT_STRATEGY_LOCAL_WINS => false,
            self::CONFLICT_STRATEGY_LATEST_WINS => true,
            self::CONFLICT_STRATEGY_MANUAL => false,
            default => true,
        };
    }

    /**
     * Start sync log record.
     *
     * @param SyncType $type
     * @return SyncLog
     */
    protected function startSyncLog(SyncType $type): SyncLog
    {
        return SyncLog::create([
            'type' => $type,
            'status' => SyncStatus::RUNNING,
            'started_at' => now(),
            'triggered_by' => 'system',
        ]);
    }

    /**
     * Complete sync log record.
     *
     * @param SyncLog $syncLog
     * @param bool $success
     */
    protected function completeSyncLog(SyncLog $syncLog, bool $success): void
    {
        $syncLog->update([
            'status' => $success ? SyncStatus::SUCCESS : SyncStatus::FAILED,
            'completed_at' => now(),
            'stats' => $this->syncStats,
        ]);
    }

    /**
     * Handle sync failure.
     *
     * @param SyncLog $syncLog
     * @param Throwable $e
     */
    protected function handleSyncFailure(SyncLog $syncLog, Throwable $e): void
    {
        $syncLog->update([
            'status' => SyncStatus::FAILED,
            'completed_at' => now(),
            'error_message' => $e->getMessage(),
            'stats' => $this->syncStats,
        ]);

        Log::error('SIPP sync failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'stats' => $this->syncStats,
        ]);

        if ($this->sendNotifications) {
            $this->sendFailureNotification($e);
        }
    }

    /**
     * Send failure notification.
     *
     * @param Throwable $e
     */
    protected function sendFailureNotification(Throwable $e): void
    {
        try {
            $notifyUsers = \App\Models\User::admins()->get();

            Notification::send($notifyUsers, new SippSyncFailedNotification($e, $this->syncStats));
        } catch (\Exception $notificationError) {
            Log::error('Failed to send sync failure notification', [
                'error' => $notificationError->getMessage(),
            ]);
        }
    }

    /**
     * Reset sync stats.
     */
    protected function resetStats(): void
    {
        $this->syncStats = [
            'schedules' => [],
            'cases' => [],
            'judges' => [],
            'court_rooms' => [],
            'case_types' => [],
            'started_at' => now(),
        ];
    }

    /**
     * Get current sync stats.
     *
     * @return array
     */
    public function getStats(): array
    {
        return array_merge($this->syncStats, [
            'success' => true,
            'is_running' => $this->isRunning,
        ]);
    }

    /**
     * Check if sync is currently running.
     *
     * @return bool
     */
    public function isRunning(): bool
    {
        return $this->isRunning;
    }

    /**
     * Validate data from SIPP API.
     *
     * @param array $data
     * @param string $type
     * @return bool
     */
    public function validateData(array $data, string $type): bool
    {
        return match ($type) {
            'schedule' => !empty($data['id'] ?? $data['case_id']),
            'case' => !empty($data['id'] ?? $data['case_id']),
            'judge' => !empty($data['id'] ?? $data['nama']),
            'court_room' => !empty($data['name'] ?? $data['nama']),
            'case_type' => !empty($data['code'] ?? $data['kode']),
            default => false,
        };
    }
}
