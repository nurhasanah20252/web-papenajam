# Analisis Tabel Web SIPP untuk Integrasi Website Pengadilan

## **Latar Belakang**
Struktur database SIPP memiliki tabel-tabel dengan suffix `web` yang kemungkinan digunakan untuk mengekspos data ke website publik. Analisis ini dilakukan untuk merancang integrasi API antara sistem Laravel dengan SIPP.

## **Metodologi**
1. Identifikasi tabel dengan suffix `web` pada file `sipp_struktur.sql`
2. Filter tabel yang relevan dengan kebutuhan website pengadilan
3. Analisis struktur kolom dan relasi
4. Mapping ke model data Laravel

## **Statistik**
- Total tabel web: 290 (termasuk view)
- Tabel web utama (non-view): ~150 (estimasi)
- View web: ~140 (estimasi)

## **Tabel Web Kunci untuk Integrasi**

### **1. Jadwal Sidang (`jadwalsidangweb`)**
**Deskripsi:** Tabel utama jadwal persidangan untuk website.
**Struktur Kolom Penting:**
- `ID` (bigint) - Primary key
- `IDPerkara` (bigint) - Foreign key ke perkara
- `tglSidang` (date) - Tanggal sidang
- `jamSidang` (time) - Jam mulai
- `selesaiSidang` (time) - Jam selesai
- `agenda` (text) - Agenda sidang
- `ruangan` (varchar) - Nama ruangan sidang
- `sidangKeliling` (char) - Flag sidang keliling
- `ditunda` (char) - Status penundaan
- `alasanDitunda` (text) - Alasan penundaan

**Catatan:** Tabel ini cocok untuk integrasi real-time jadwal sidang.

### **2. Data Umum Perkara (`dataumumweb`)**
**Deskripsi:** Data dasar perkara yang dipublikasikan ke website.
**Struktur Kolom Penting:**
- `IDPerkara` (bigint) - Primary key
- `tglPendaftaran` (date) - Tanggal pendaftaran
- `klasifikasiPerkara` (tinyint) - Klasifikasi perkara
- `IDJenisPerkara` (smallint) - Jenis perkara
- `jenisPerkara` (varchar) - Nama jenis perkara
- `noPerkara` (varchar) - Nomor perkara
- `tglSurat` (date) - Tanggal surat
- `noSurat` (varchar) - Nomor surat
- `petitumDakwaan` (text) - Petitum/dakwaan
- `dipublikasikan` (char) - Flag publikasi (Y/T)
- `pihakPertama` (text) - Nama pihak pertama (penggugat)
- `pihakKedua` (text) - Nama pihak kedua (tergugat)
- `tglPutusan` (date) - Tanggal putusan
- `tglMinutasi` (date) - Tanggal minutasi

**Catatan:** Kolom `dipublikasikan` kemungkinan digunakan sebagai filter untuk data yang boleh ditampilkan di website.

### **3. Hakim (`hakimweb`)**
**Deskripsi:** Mapping hakim yang menangani perkara.
**Struktur Kolom Penting:**
- `IDPerkara` (bigint) - Foreign key ke perkara
- `IDHakim` (bigint) - ID hakim
- `IDTahapan` (tinyint) - Tahapan proses
- `tglPenetapan` (date) - Tanggal penetapan
- `posisiHakim` (varchar) - Posisi (ketua/anggota)
- `urutan` (tinyint) - Urutan hakim
- `nama` (varchar) - Nama hakim
- `aktif` (char) - Status aktif
- `jenisAcara` (varchar) - Jenis acara

**Catatan:** Satu perkara bisa memiliki multiple hakim dengan posisi berbeda.

### **4. Alur Perkara (`alurperkaraweb`)**
**Deskripsi:** Tracking alur proses perkara.
**Struktur Kolom Penting:**
- `ID` (int) - Primary key
- `IDPerkara` (bigint) - Foreign key ke perkara
- `IDTahapan` (tinyint) - ID tahapan
- `tglTahapan` (date) - Tanggal tahapan
- `keterangan` (text) - Keterangan tahapan
- `dipublikasikan` (char) - Flag publikasi

### **5. Jenis Perkara (`jenisperkaraweb`)**
**Deskripsi:** Referensi jenis perkara.
**Struktur Kolom Penting:**
- `ID` (smallint) - Primary key
- `nama` (varchar) - Nama jenis perkara
- `keterangan` (varchar) - Keterangan
- `aktif` (char) - Status aktif

## **View untuk Website**
SIPP juga menyediakan view denormalized untuk mempermudah query:
- `viewjadwalsidangweb` - View jadwal sidang
- `viewdataumumweb` - View data umum perkara
- `viewhakimweb` - View hakim
- `viewalurperkaraweb` - View alur perkara
- `viewjenisperkaraweb` - View jenis perkara

## **Pola Umum Tabel Web**
1. **Flag Publikasi:** Kolom `dipublikasikan` (char) dengan nilai 'Y'/'T'
2. **Foreign Key:** Menggunakan `IDPerkara` untuk relasi ke perkara
3. **Audit Trail:** Tidak ada kolom audit trail (created_at/updated_at) di tabel web
4. **Denormalization:** Beberapa tabel sudah denormalized untuk performa website

## **Implikasi untuk Integrasi API**

### **Strategi Sync:**
1. **Incremental Sync:** Gunakan kolom timestamp jika ada (tidak ada di tabel web)
2. **Flag-based Sync:** Gunakan kolom `dipublikasikan` untuk filter data yang boleh disinkronisasi
3. **Full Table Scan:** Diperlukan untuk tabel tanpa mekanisme perubahan

### **Data Mapping ke Laravel:**
| Tabel SIPP | Model Laravel | Keterangan |
|------------|---------------|------------|
| `jadwalsidangweb` | `CourtSchedule` | Tambahkan `external_id` dari `ID` |
| `dataumumweb` | `Case` | Tambahkan `external_id` dari `IDPerkara` |
| `hakimweb` | `JudgeAssignment` | Relasi many-to-many perkara-hakim |
| `alurperkaraweb` | `CaseProgress` | History alur perkara |
| `jenisperkaraweb` | `CaseType` | Referensi jenis perkara |

### **Endpoint API yang Diperlukan:**
Berdasarkan struktur tabel, API SIPP harus menyediakan:
1. `GET /api/web/jadwal-sidang` - Filter by date, ruangan, status
2. `GET /api/web/perkara` - Filter by jenis, status, tanggal
3. `GET /api/web/hakim` - Data hakim aktif
4. `GET /api/web/alur-perkara` - History perkara tertentu
5. `GET /api/web/jenis-perkara` - Referensi jenis perkara

## **Rekomendasi Teknis**

### **1. Authentication & Authorization**
- API Key dengan IP whitelisting
- Rate limiting (100 requests/minute)
- Endpoint terpisah untuk data web vs data internal

### **2. Pagination & Filtering**
- Support pagination (limit/offset)
- Filter by `dipublikasikan='Y'` wajib
- Filter by date range untuk jadwal sidang

### **3. Response Format**
```json
{
  "success": true,
  "data": [],
  "pagination": {
    "total": 100,
    "per_page": 20,
    "current_page": 1
  }
}
```

### **4. Error Handling**
- HTTP status codes sesuai standard
- Message error dalam bahasa Indonesia
- Logging untuk monitoring

## **Langkah Selanjutnya**
1. **Validasi API Availability:** Cek apakah endpoint API SIPP sudah tersedia
2. **Proof of Concept:** Implementasi sync service sederhana
3. **Performance Testing:** Uji load dengan data real
4. **Monitoring Setup:** Alert untuk sync failures

## **Referensi**
- File struktur database: `docs/sipp_struktur.sql`
- PRD Proyek: `docs/PRD.md`

---
*Dokumen ini dibuat berdasarkan analisis struktur database SIPP per 18 Januari 2026.*
