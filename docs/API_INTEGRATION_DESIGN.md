# Rancangan Teknis Integrasi API SIPP - Website Pengadilan Agama Penajam

## **1. Overview**

### **1.1 Konteks**
Integrasi antara sistem website Laravel dengan SIPP (Sistem Informasi Penelusuran Perkara) untuk:
- Menampilkan jadwal sidang real-time
- Menampilkan data perkara publik
- Menampilkan statistik perkara
- Menampilkan informasi hakim dan ruangan sidang

### **1.2 Asumsi**
1. SIPP menyediakan API endpoint untuk akses data tabel `web`
2. Authentication menggunakan API Key
3. Data sudah difilter melalui flag `dipublikasikan='Y'`
4. API mendukung pagination dan filtering dasar

## **2. Arsitektur Integrasi**

### **2.1 High-Level Architecture**
```
[Laravel Website] ←→ [SIPP API Gateway] ←→ [SIPP Database]
       ↑                       ↑
   [Cache]               [Auth/Filter]
```

### **2.2 Komponen Utama**
1. **SippApiClient:** HTTP client untuk konsumsi API SIPP
2. **SippDataSyncService:** Service untuk sinkronisasi data periodik
3. **SippCacheManager:** Cache layer untuk mengurangi load API
4. **SippWebhookHandler:** (Optional) Handler untuk webhook updates
5. **SippMonitoring:** Monitoring dan alerting untuk sync failures

## **3. Service Layer Design**

### **3.1 SippApiClient**
```php
namespace App\Services\Sipp;

class SippApiClient
{
    private string $baseUrl;
    private string $apiKey;
    private int $timeout;
    
    public function __construct(string $baseUrl, string $apiKey, int $timeout = 30)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
        $this->timeout = $timeout;
    }
    
    public function getCourtSchedules(array $filters = []): array
    {
        return $this->get('/api/web/jadwal-sidang', $filters);
    }
    
    public function getCases(array $filters = []): array
    {
        return $this->get('/api/web/perkara', $filters);
    }
    
    public function getJudges(array $filters = []): array
    {
        return $this->get('/api/web/hakim', $filters);
    }
    
    public function getCaseProgress(int $caseId): array
    {
        return $this->get('/api/web/alur-perkara', ['perkara_id' => $caseId]);
    }
    
    public function getCaseTypes(): array
    {
        return $this->get('/api/web/jenis-perkara');
    }
    
    private function get(string $endpoint, array $params = []): array
    {
        // Implementation dengan retry logic dan error handling
    }
}
```

### **3.2 SippDataSyncService**
```php
namespace App\Services\Sipp;

class SippDataSyncService
{
    private SippApiClient $client;
    private SippCacheManager $cache;
    
    public function syncCourtSchedules(\DateTime $date): SyncResult
    {
        // Sync jadwal sidang untuk tanggal tertentu
    }
    
    public function syncPublishedCases(int $limit = 100): SyncResult
    {
        // Sync perkara yang dipublikasikan
    }
    
    public function syncCaseStatistics(int $year, int $month): SyncResult
    {
        // Sync statistik perkara
    }
    
    public function syncMasterData(): SyncResult
    {
        // Sync data master (hakim, ruangan, jenis perkara)
    }
}
```

## **4. Database Schema untuk Cache**

### **4.1 Tabel Cache dari SIPP**
```php
// court_schedules table
Schema::create('court_schedules', function (Blueprint $table) {
    $table->id();
    $table->bigInteger('external_id')->unique(); // ID dari SIPP
    $table->bigInteger('case_external_id'); // IDPerkara dari SIPP
    $table->string('case_number')->nullable(); // noPerkara
    $table->string('case_title')->nullable(); // dari dataumumweb
    $table->string('case_type')->nullable(); // jenisPerkara
    $table->date('schedule_date'); // tglSidang
    $table->time('schedule_time')->nullable(); // jamSidang
    $table->time('end_time')->nullable(); // selesaiSidang
    $table->string('room')->nullable(); // ruangan
    $table->string('agenda')->nullable(); // agenda
    $table->string('judge_names')->nullable(); // dari hakimweb
    $table->string('status')->default('scheduled'); // ditunda, selesai, dll
    $table->json('parties')->nullable(); // { plaintiff: [], defendant: [] }
    $table->timestamp('last_sync_at');
    $table->string('sync_status')->default('pending');
    $table->timestamps();
});

// cases table (cache dari dataumumweb)
Schema::create('cases', function (Blueprint $table) {
    $table->id();
    $table->bigInteger('external_id')->unique(); // IDPerkara
    $table->string('case_number')->unique(); // noPerkara
    $table->string('case_title')->nullable();
    $table->string('case_type')->nullable(); // jenisPerkara
    $table->date('register_date'); // tglPendaftaran
    $table->string('register_number')->nullable(); // noSurat
    $table->string('status')->nullable(); // statusAkhir
    $table->json('plaintiffs')->nullable(); // pihakPertama
    $table->json('defendants')->nullable(); // pihakKedua
    $table->text('subject_matter')->nullable(); // petitumDakwaan
    $table->date('decision_date')->nullable(); // tglPutusan
    $table->boolean('is_published')->default(false); // dipublikasikan='Y'
    $table->timestamp('last_sync_at');
    $table->timestamps();
});

// judges table (cache dari hakimweb + master hakim)
Schema::create('judges', function (Blueprint $table) {
    $table->id();
    $table->bigInteger('external_id')->unique(); // IDHakim
    $table->string('judge_code')->nullable(); // kode hakim
    $table->string('full_name'); // nama
    $table->string('title')->nullable(); // gelar
    $table->string('specialization')->nullable(); // spesialisasi
    $table->string('chamber')->nullable(); // ruang/kamar
    $table->boolean('is_active')->default(true); // aktif='Y'
    $table->timestamp('last_sync_at');
    $table->timestamps();
});

// case_types table (cache dari jenisperkaraweb)
Schema::create('case_types', function (Blueprint $table) {
    $table->id();
    $table->integer('external_id')->unique(); // ID dari SIPP
    $table->string('type_code')->nullable(); // kode jenis
    $table->string('type_name'); // nama
    $table->string('category')->nullable(); // kategori
    $table->text('legal_basis')->nullable(); // dasar hukum
    $table->boolean('is_active')->default(true); // aktif='Y'
    $table->timestamp('last_sync_at');
    $table->timestamps();
});
```

### **4.2 Tabel Sync Logs**
```php
// sipp_sync_logs table
Schema::create('sipp_sync_logs', function (Blueprint $table) {
    $table->id();
    $table->string('sync_type'); // court_schedules, cases, judges, etc.
    $table->string('sync_mode'); // full, incremental, on_demand
    $table->timestamp('start_time');
    $table->timestamp('end_time')->nullable();
    $table->integer('records_fetched')->default(0);
    $table->integer('records_created')->default(0);
    $table->integer('records_updated')->default(0);
    $table->integer('records_skipped')->default(0);
    $table->text('error_message')->nullable();
    $table->json('metadata')->nullable(); // filters, pagination info
    $table->timestamps();
});
```

## **5. Sync Strategies**

### **5.1 Real-time Sync (Jadwal Sidang)**
- **Frequency:** Setiap 15 menit
- **Scope:** Hari ini + 7 hari ke depan
- **Filter:** `ditunda='T'` untuk sidang tidak ditunda
- **Cache TTL:** 10 menit

### **5.2 Daily Sync (Data Perkara)**
- **Frequency:** Setiap hari jam 02:00
- **Scope:** Perkara dengan `dipublikasikan='Y'`
- **Filter:** `tglPendaftaran` >= 1 tahun lalu
- **Cache TTL:** 24 jam

### **5.3 Weekly Sync (Master Data)**
- **Frequency:** Setiap Senin jam 03:00
- **Scope:** Data master (hakim, ruangan, jenis perkara)
- **Filter:** `aktif='Y'`
- **Cache TTL:** 7 hari

### **5.4 On-demand Sync**
- Triggered oleh user request untuk data spesifik
- Bypass cache untuk data real-time
- Rate limited untuk prevent abuse

## **6. Error Handling & Retry Logic**

### **6.1 Retry Strategy**
```php
class RetryPolicy
{
    private int $maxRetries = 3;
    private int $initialDelay = 1000; // ms
    private float $backoffFactor = 2.0;
    
    public function execute(callable $operation): mixed
    {
        $retryCount = 0;
        $lastException = null;
        
        while ($retryCount <= $this->maxRetries) {
            try {
                return $operation();
            } catch (SippApiException $e) {
                $lastException = $e;
                $retryCount++;
                
                if ($retryCount > $this->maxRetries) {
                    break;
                }
                
                $delay = $this->initialDelay * pow($this->backoffFactor, $retryCount - 1);
                usleep($delay * 1000); // Convert to microseconds
            }
        }
        
        throw new SippSyncException('Max retries exceeded', 0, $lastException);
    }
}
```

### **6.2 Error Classification**
- **Transient Errors:** Timeout, network issues → Retry
- **Client Errors:** Invalid API key, bad request → Alert immediately
- **Server Errors:** SIPP API down → Exponential backoff
- **Data Errors:** Invalid data format → Log and skip record

### **6.3 Monitoring & Alerting**
- **Metrics:** Sync success rate, latency, record counts
- **Alerts:** Failed sync for > 1 hour, high error rate
- **Dashboard:** Laravel Pulse integration

## **7. API Endpoint Design (SIPP Side)**

### **7.1 Required Endpoints**
```
# Jadwal Sidang
GET /api/v1/web/court-schedules
Query Params:
  - date (YYYY-MM-DD) - filter by date
  - date_from, date_to - date range
  - room - filter by ruangan
  - status - filter by status (scheduled, postponed, completed)
  - published_only=true (default)
  - page, per_page

# Data Perkara
GET /api/v1/web/cases
Query Params:
  - case_number - exact match
  - case_type - filter by jenis perkara
  - date_from, date_to - filter by tglPendaftaran
  - published_only=true (default)
  - page, per_page

# Hakim
GET /api/v1/web/judges
Query Params:
  - active_only=true (default)
  - page, per_page

# Alur Perkara
GET /api/v1/web/case-progress/{case_id}

# Jenis Perkara
GET /api/v1/web/case-types
```

### **7.2 Response Format Standard**
```json
{
  "success": true,
  "data": [
    {
      "id": 12345,
      "perkara_id": 67890,
      "tgl_sidang": "2026-01-20",
      "jam_sidang": "09:00:00",
      "ruangan": "Ruang Sidang 1",
      "agenda": "Pembacaan dakwaan"
    }
  ],
  "pagination": {
    "total": 150,
    "per_page": 20,
    "current_page": 1,
    "total_pages": 8
  },
  "metadata": {
    "sync_timestamp": "2026-01-18T10:30:00Z",
    "record_count": 20
  }
}
```

## **8. Implementation Phases**

### **Phase 1: Foundation (Week 1-2)**
1. SippApiClient dengan basic HTTP operations
2. Database schema untuk cache tables
3. Basic migration dan models
4. Unit tests untuk client

### **Phase 2: Core Sync (Week 3-4)**
1. SippDataSyncService untuk jadwal sidang
2. Scheduled jobs (Laravel Scheduler)
3. Error handling dan retry logic
4. Basic monitoring dengan logs

### **Phase 3: Data Expansion (Week 5-6)**
1. Sync untuk data perkara
2. Sync untuk master data (hakim, jenis perkara)
3. Cache layer dengan Redis
4. Performance optimization

### **Phase 4: Production Ready (Week 7-8)**
1. Comprehensive error handling
2. Monitoring dashboard
3. Alerting configuration
4. Documentation dan runbooks

## **9. Security Considerations**

### **9.1 Authentication**
- API Key dengan IP whitelisting
- Key rotation setiap 90 hari
- Separate keys untuk environment (dev/staging/prod)

### **9.2 Data Protection**
- Mask sensitive data (KTP, alamat lengkap) di API response
- GDPR/PPID compliance untuk data pribadi
- Audit trail untuk data access

### **9.3 Rate Limiting**
- Global rate limit: 100 requests/minute
- Per-endpoint limits
- Burst allowance untuk emergency sync

## **10. Performance Optimization**

### **10.1 Caching Strategy**
- **L1 Cache:** Redis dengan TTL sesuai sync frequency
- **L2 Cache:** Database cache tables
- **Cache Invalidation:** On sync completion, manual flush

### **10.2 Query Optimization**
- Index pada `external_id`, `sync_status`
- Composite indexes untuk frequent queries
- Query batching untuk bulk operations

### **10.3 Memory Management**
- Chunk processing untuk large datasets
- Stream processing untuk large responses
- Memory limits pada jobs

## **11. Testing Strategy**

### **11.1 Unit Tests**
- SippApiClient mocking
- Error scenarios simulation
- Retry logic verification

### **11.2 Integration Tests**
- Actual API calls (test environment)
- Sync service integration
- Database consistency checks

### **11.3 Load Tests**
- Concurrent sync jobs
- Large dataset processing
- API rate limiting tests

## **12. Deployment & Monitoring**

### **12.1 Deployment Checklist**
- [ ] API credentials configured
- [ ] Database migrations run
- [ ] Scheduled jobs configured
- [ ] Monitoring alerts set up
- [ ] Backup procedures verified

### **12.2 Monitoring Dashboard**
```php
// Laravel Pulse integration
Pulse::register(
    ['sipp_sync_duration', 'sipp_sync_records', 'sipp_api_errors'],
    function (Pulse $pulse) {
        // Custom metrics collection
    }
);
```

### **12.3 Disaster Recovery**
- Manual sync trigger capability
- Rollback to last known good state
- Data consistency validation scripts

## **13. Appendices**

### **13.1 SIPP Table Mapping**
| SIPP Table | Laravel Model | Key Fields | Sync Frequency |
|------------|---------------|------------|----------------|
| jadwalsidangweb | CourtSchedule | ID, IDPerkara | 15 minutes |
| dataumumweb | Case | IDPerkara | Daily |
| hakimweb | JudgeAssignment | IDPerkara, IDHakim | Weekly |
| jenisperkaraweb | CaseType | ID | Weekly |
| alurperkaraweb | CaseProgress | ID, IDPerkara | On-demand |

### **13.2 Error Codes Reference**
| Code | Meaning | Action |
|------|---------|--------|
| SIPP-001 | API Authentication Failed | Check API key |
| SIPP-002 | Rate Limit Exceeded | Wait and retry |
| SIPP-003 | Invalid Request Parameters | Validate input |
| SIPP-004 | Data Validation Failed | Skip record, log error |
| SIPP-005 | Service Unavailable | Exponential backoff |

### **13.3 Sample Configuration**
```env
SIPP_API_BASE_URL=https://sipp.example.com/api
SIPP_API_KEY=your_api_key_here
SIPP_SYNC_ENABLED=true
SIPP_SYNC_COURT_SCHEDULES_CRON=*/15 * * * *
SIPP_SYNC_CASES_CRON=0 2 * * *
SIPP_SYNC_MASTER_DATA_CRON=0 3 * * 1
SIPP_CACHE_TTL_MINUTES=10
```

---
*Dokumen ini melengkapi PRD di `docs/PRD.md` dengan detail teknis integrasi API SIPP.*
*Terakhir diperbarui: 18 Januari 2026*
