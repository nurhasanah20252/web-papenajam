<?php

namespace App\Http\Controllers;

use App\Models\CourtSchedule;
use App\Models\SippCourtRoom;
use App\Models\SippJudge;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CourtScheduleController extends Controller
{
    /**
     * Display court schedules page.
     */
    public function index(Request $request)
    {
        $date = $request->query('date', now()->format('Y-m-d'));
        $caseType = $request->query('case_type');
        $judge = $request->query('judge');
        $courtRoom = $request->query('court_room');
        $search = $request->query('search');

        $query = CourtSchedule::query()
            ->whereDate('schedule_date', $date)
            ->where('schedule_status', 'scheduled');

        if ($caseType && $caseType !== 'all') {
            $query->where('case_type', $caseType);
        }

        if ($judge && $judge !== 'all') {
            $query->where('judge_name', 'like', "%{$judge}%");
        }

        if ($courtRoom && $courtRoom !== 'all') {
            $query->where('court_room', $courtRoom);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('case_number', 'like', "%{$search}%")
                    ->orWhere('case_title', 'like', "%{$search}%");
            });
        }

        $schedules = $query
            ->orderBy('schedule_time')
            ->get()
            ->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'case_number' => $schedule->case_number,
                    'case_title' => $schedule->case_title,
                    'case_type' => $schedule->case_type,
                    'judge' => $schedule->judge_name,
                    'courtroom' => $schedule->court_room,
                    'time_start' => $schedule->schedule_time,
                    'time_end' => $schedule->schedule_time ? $this->calculateEndTime($schedule->schedule_time) : null,
                    'date' => $schedule->schedule_date?->format('Y-m-d'),
                    'status' => $this->mapStatus($schedule->schedule_status),
                    'agenda' => $schedule->agenda,
                    'parties' => $this->parseParties($schedule->parties),
                ];
            });

        $judges = SippJudge::active()->get()->map(function ($judge) {
            return [
                'id' => $judge->id,
                'name' => $judge->full_name,
                'title' => $judge->title,
            ];
        });

        $courtRooms = SippCourtRoom::active()->get()->map(function ($room) {
            return [
                'id' => $room->id,
                'name' => $room->room_name,
                'building' => $room->room_type,
            ];
        });

        $caseTypes = CourtSchedule::select('case_type')
            ->distinct()
            ->whereNotNull('case_type')
            ->pluck('case_type')
            ->toArray();

        return Inertia::render('schedules', [
            'schedules' => $schedules,
            'judges' => $judges,
            'courtrooms' => $courtRooms,
            'caseTypes' => array_merge(['Semua Jenis'], $caseTypes),
            'filters' => [
                'date' => $date,
                'case_type' => $caseType ?? 'all',
                'judge' => $judge ?? 'all',
                'court_room' => $courtRoom ?? 'all',
                'search' => $search ?? '',
            ],
        ]);
    }

    /**
     * Display single schedule details.
     */
    public function show($id)
    {
        $schedule = CourtSchedule::findOrFail($id);

        return Inertia::render('schedules/[id]', [
            'schedule' => [
                'id' => $schedule->id,
                'case_number' => $schedule->case_number,
                'case_title' => $schedule->case_title,
                'case_type' => $schedule->case_type,
                'judge' => $schedule->judge_name,
                'courtroom' => $schedule->court_room,
                'time_start' => $schedule->schedule_time,
                'time_end' => $schedule->schedule_time ? $this->calculateEndTime($schedule->schedule_time) : null,
                'date' => $schedule->schedule_date?->format('Y-m-d'),
                'formatted_date' => $schedule->getFormattedDate(),
                'status' => $this->mapStatus($schedule->schedule_status),
                'agenda' => $schedule->agenda,
                'notes' => $schedule->notes,
                'parties' => $this->parseParties($schedule->parties),
                'last_sync_at' => $schedule->last_sync_at?->format('d M Y H:i'),
            ],
        ]);
    }

    /**
     * Calculate end time based on start time.
     */
    private function calculateEndTime(string $startTime): string
    {
        // Default 1.5 hours duration
        $time = \Carbon\Carbon::parse($startTime);
        $endTime = $time->addHours(1)->addMinutes(30);

        return $endTime->format('H:i');
    }

    /**
     * Map schedule status to frontend format.
     */
    private function mapStatus($status): string
    {
        return match ($status?->value) {
            'scheduled' => 'scheduled',
            'postponed' => 'postponed',
            'cancelled' => 'postponed',
            'completed' => 'completed',
            default => 'scheduled',
        };
    }

    /**
     * Parse parties JSON to array.
     */
    private function parseParties($parties): array
    {
        if (! $parties) {
            return [];
        }

        if (is_string($parties)) {
            $parties = json_decode($parties, true);
        }

        $result = [];

        if (isset($parties['plaintiff'])) {
            $result[] = $parties['plaintiff'];
        }

        if (isset($parties['defendant'])) {
            $result[] = $parties['defendant'];
        }

        return $result;
    }
}
