# PROMPT CODEX — Transform SPMI App into Workflow-Based Quality Management System

Saya sedang membangun aplikasi SPMI kampus menggunakan Laravel + Filament.

Saat ini aplikasi sudah memiliki banyak Resource CRUD:

* Unit
* User
* Periode SPMI
* Standar Mutu
* Indikator Standar
* Dokumen Mutu
* Penugasan Indikator
* Capaian Indikator
* Validasi Capaian
* AMI
* Checklist Audit
* Temuan Audit
* Tindak Lanjut
* RTM / Rapat Tinjauan Manajemen
* Usulan Peningkatan Standar
* Laporan

Masalah:
UI/UX aplikasi masih terlalu table-based / CRUD-based. Saya ingin mengubah pengalaman pengguna menjadi Workflow-based Quality Management System.

Prinsip utama:

* CRUD tetap dipertahankan untuk master data dan admin teknis.
* Workflow utama harus menggunakan custom page yang clean, minimalistic, dan task-oriented.
* User tidak perlu paham struktur tabel. User hanya perlu tahu apa yang harus dikerjakan.
* UI harus berbasis role, status, task queue, progress, dan siklus PPEPP.
* Jangan merusak resource existing.
* Jangan menghapus CRUD existing.
* Tambahkan custom page/workspace yang menjadi pengalaman utama pengguna.

Target akhir:
Aplikasi terasa seperti sistem kerja SPMI, bukan panel database.

Role utama:

* super_admin
* admin_lpm
* pimpinan
* unit_pic
* auditor
* viewer

Siklus utama:
Penetapan → Pelaksanaan → Evaluasi AMI → Pengendalian → Peningkatan

==================================================
PHASE 1 — ROLE-BASED COMMAND CENTER DASHBOARD
=============================================

Tugas:
Buat dashboard utama berbasis role.

Jangan hanya tampilkan statistik total data. Dashboard harus menjawab:

* Apa yang harus saya kerjakan sekarang?
* Apa yang menunggu review saya?
* Apa yang terlambat?
* Apa yang belum selesai?
* Apa yang butuh keputusan?

Buat dashboard berbeda berdasarkan role.

A. Dashboard Admin LPM

Tampilkan:

1. Header:

   * Periode SPMI aktif
   * Status siklus berjalan
   * Shortcut action:

     * Buat Standar
     * Assign Indikator
     * Validasi Capaian
     * Jadwalkan AMI
     * Buat RTM

2. PPEPP Progress Cards:

   * Penetapan Standar
   * Pelaksanaan Standar
   * Evaluasi AMI
   * Pengendalian Temuan
   * Peningkatan Standar

Setiap card tampilkan:

* status
* progress percentage
* jumlah item terkait
* tombol buka workspace

3. Work Queue:

   * Capaian menunggu validasi
   * Unit belum submit capaian
   * Capaian dikembalikan
   * Temuan belum ditindaklanjuti
   * Tindak lanjut menunggu verifikasi
   * Usulan peningkatan menunggu proses

4. Warning / Attention:

   * Deadline capaian lewat
   * Tindak lanjut terlambat
   * AMI belum punya auditor
   * RTM belum difinalisasi

B. Dashboard Unit/PIC

Tampilkan:

* Total indikator ditugaskan
* Belum diisi
* Draft
* Menunggu validasi
* Dikembalikan
* Tervalidasi
* Temuan aktif
* Tindak lanjut menunggu revisi

Work Queue:

* Indikator yang harus diisi
* Capaian yang dikembalikan
* Temuan audit yang harus ditindaklanjuti
* Deadline terdekat

C. Dashboard Auditor

Tampilkan:

* Audit yang ditugaskan kepada saya
* Checklist belum selesai
* Temuan belum difinalisasi
* Tindak lanjut menunggu verifikasi

D. Dashboard Pimpinan

Tampilkan:

* Persentase capaian standar institusi
* Unit dengan progress terbaik/terendah
* Jumlah temuan mayor/minor
* Tindak lanjut strategis belum selesai
* Usulan peningkatan menunggu approval
* Shortcut ke laporan dan approval

UI requirement:

* Gunakan stat cards, section, badge, progress bar.
* Hindari table besar di dashboard.
* Maksimal 1-2 table ringkas.
* Gunakan bahasa Indonesia.
* Jangan tampilkan ID teknis.
* Gunakan empty state yang jelas.

Output:

* Update dashboard Filament
* Buat widget/card yang diperlukan
* Query berdasarkan role
* Pastikan unit_pic hanya melihat data unit sendiri
* Pastikan auditor hanya melihat audit yang ditugaskan
* Pastikan admin_lpm dan pimpinan bisa melihat semua sesuai hak akses

==================================================
PHASE 2 — SPMI CYCLE WORKSPACE / PETA SIKLUS PPEPP
==================================================

Tugas:
Buat custom page baru bernama "Peta Siklus SPMI" atau "Siklus PPEPP".

Tujuan:
Halaman ini menjadi pusat navigasi proses SPMI berdasarkan siklus:

1. Penetapan
2. Pelaksanaan
3. Evaluasi AMI
4. Pengendalian
5. Peningkatan

Navigation:

* Group: SPMI
* Label: Siklus SPMI
* Icon sesuai
* Letakkan dekat Dashboard atau Periode SPMI

Layout:

1. Header:

   * Title: Siklus SPMI
   * Subtitle: Pantau proses penjaminan mutu dari penetapan standar hingga peningkatan berkelanjutan.
   * Select periode SPMI aktif

2. Horizontal atau vertical stepper:
   Step 1: Penetapan Standar
   Step 2: Pelaksanaan Standar
   Step 3: Evaluasi AMI
   Step 4: Pengendalian
   Step 5: Peningkatan

3. Setiap step berupa card:

   * Judul tahap
   * Deskripsi singkat
   * Status
   * Progress percentage
   * Ringkasan angka
   * Primary action

Contoh card:

Penetapan Standar:

* Total standar aktif
* Total indikator
* Standar draft
* Standar belum disetujui
* Button: Kelola Standar

Pelaksanaan:

* Total assignment
* Sudah submit
* Belum submit
* Menunggu validasi
* Button: Monitoring Capaian

Evaluasi AMI:

* Periode AMI aktif
* Unit diaudit
* Checklist selesai
* Temuan audit
* Button: AMI Workspace

Pengendalian:

* Temuan terbuka
* Tindak lanjut dalam proses
* Menunggu verifikasi
* Terlambat
* Button: Monitoring Tindak Lanjut

Peningkatan:

* RTM draft
* RTM selesai
* Usulan submitted
* Usulan approved
* Button: Peningkatan Standar

UI requirement:

* Minimalistic
* Banyak gunakan card dan progress
* Jangan tampilkan table besar
* Jadikan halaman ini sebagai map proses, bukan CRUD
* Setiap button mengarah ke workspace terkait

Output:

* Custom page Siklus SPMI
* Query progress setiap tahap
* Filter periode SPMI
* Role-based visibility
* Integrasi navigation

==================================================
PHASE 3 — STANDAR MUTU WORKSPACE
================================

Tugas:
Ubah pengalaman Standar Mutu dari sekadar tabel CRUD menjadi Standar Mutu Workspace.

Jangan hapus QualityStandardResource yang sudah ada.
Tambahkan custom page atau perbaiki ViewRecord agar user melihat standar sebagai satu entitas lengkap.

Halaman detail standar harus memiliki tabs:

1. Ringkasan
2. Indikator
3. Dokumen Terkait
4. Unit Ditugaskan
5. Capaian
6. Riwayat Revisi

Tab Ringkasan:

* Kode standar
* Nama standar
* Kategori
* Periode
* Status
* Versi
* Deskripsi
* Approved by
* Approved at
* Action:

  * Edit Standar
  * Ajukan Approval
  * Approve
  * Arsipkan
  * Buat Usulan Revisi

Tab Indikator:

* Card/table ringkas indikator
* code
* statement
* target
* jenis indikator
* wajib bukti
* action edit

Tab Dokumen Terkait:

* Dokumen mutu yang terkait ke standar
* Upload dokumen
* Open file
* Open URL
* Approve document
* Archive document

Tab Unit Ditugaskan:

* Unit yang mendapat indikator dari standar ini
* Indikator
* Deadline
* Status assignment
* Action assign ulang / edit deadline

Tab Capaian:

* Unit
* Indikator
* Target
* Realisasi
* Status capaian
* Status validasi
* Jumlah bukti
* Action lihat detail capaian

Tab Riwayat Revisi:

* Revisi standar
* Revisi indikator
* Revisi target
* Usulan peningkatan terkait

UI requirement:

* Clean tabs
* Jangan tampilkan semua kolom teknis
* Detail standar harus terasa sebagai pusat data
* CRUD tetap boleh ada, tapi pengalaman utama adalah detail workspace

Output:

* Update QualityStandardResource/View page
* Relation manager/tab yang diperlukan
* Query nested assignments dan achievements
* Avoid N+1
* Authorization aman

==================================================
PHASE 4 — ASSIGNMENT WIZARD, BUKAN CRUD ASSIGNMENT
==================================================

Tugas:
Buat custom page "Assign Indikator" untuk menggantikan pengalaman CRUD manual pada indicator_unit_assignments.

Tujuan:
Admin LPM bisa menugaskan indikator ke banyak unit secara mudah.

Navigation:

* Group: SPMI / Penetapan Standar
* Label: Assign Indikator

Buat wizard:

Step 1: Pilih Periode

* spmi_period_id
* default periode aktif

Step 2: Pilih Standar

* quality_standard_id
* tampilkan deskripsi standar singkat

Step 3: Pilih Indikator

* checkbox list indikator dari standar terpilih
* tampilkan target setiap indikator
* tombol pilih semua

Step 4: Pilih Unit

* multiple select unit
* filter type: prodi/fakultas/unit
* tombol pilih semua prodi

Step 5: Deadline

* due_date
* status default assigned

Step 6: Review
Tampilkan ringkasan:

* periode
* standar
* jumlah indikator
* jumlah unit
* total assignment yang akan dibuat

Behavior:

* Gunakan updateOrCreate agar tidak duplikat
* Unique logic:
  spmi_period_id + standard_indicator_id + unit_id
* Setelah berhasil, tampilkan success notification
* Redirect ke monitoring assignment atau standar detail

UI requirement:

* Wizard clean
* Label Bahasa Indonesia
* Tidak perlu table besar
* Tampilkan progress step

Output:

* Custom page Assign Indikator
* Logic bulk assignment
* Validasi duplikasi
* Integrasi menu
* Jangan hapus resource assignment existing

==================================================
PHASE 5 — UNIT TASK WORKSPACE: CAPAIAN INDIKATOR SAYA
=====================================================

Tugas:
Buat custom page untuk user unit/prodi bernama "Capaian Indikator Saya".

Tujuan:
User unit tidak melihat CRUD table mentah, tapi melihat task list indikator yang harus dikerjakan.

Navigation untuk unit_pic:

* Label: Capaian Indikator Saya
* Group: Pelaksanaan

Query:

* unit_pic hanya melihat indicator_unit_assignments where unit_id = auth()->user()->unit_id
* admin_lpm/super_admin boleh lihat semua jika memakai mode monitoring

Layout:

1. Header:

   * Nama unit
   * Periode SPMI aktif
   * Progress pengisian
   * Deadline terdekat

2. Status tabs:

   * Semua
   * Belum Diisi
   * Draft
   * Menunggu Validasi
   * Dikembalikan
   * Tervalidasi
   * Belum Tercapai

3. Card list indikator:
   Setiap card menampilkan:

* Nama standar
* Kode indikator
* Pernyataan indikator
* Target
* Due date
* Status submission
* Status capaian
* Jumlah bukti
* Action:

  * Isi Capaian
  * Edit Draft
  * Lihat Review
  * Upload Bukti

4. Form Isi Capaian:
   Buat modal atau custom page sederhana.
   Field:

* Target ditampilkan readonly
* realization_value untuk percentage/number
* realization_text untuk text/checklist
* achievement_status otomatis jika memungkinkan
* notes
* upload bukti multiple
* external_url optional
* description bukti

Button:

* Simpan Draft
* Submit ke LPM

Behavior:

* Draft: submission_status = draft
* Submit: submission_status = submitted, submitted_at = now, submitted_by = auth user
* Update assignment status
* Jika evidence_required true, submit wajib punya minimal satu bukti
* Jika dikembalikan LPM, unit bisa submit ulang

UI requirement:

* Task-oriented
* Card/list lebih utama daripada table
* Ada progress bar
* Ada warning deadline
* Jangan tampilkan ID teknis

Output:

* Custom page Capaian Indikator Saya
* Form/modal submit capaian
* Upload evidence
* Status handling
* Role-based query

==================================================
PHASE 6 — LPM VALIDATION INBOX
==============================

Tugas:
Buat custom page "Inbox Validasi Capaian" untuk Admin LPM.

Tujuan:
Validasi capaian jangan terasa seperti CRUD, tapi seperti inbox review.

Navigation:

* Group: Pelaksanaan / SPMI
* Label: Inbox Validasi Capaian

Layout:

1. Header:

   * Title: Inbox Validasi Capaian
   * Subtitle: Periksa capaian indikator dan bukti yang dikirim unit.
   * Filter periode

2. Stat cards:

   * Menunggu Validasi
   * Dikembalikan
   * Tervalidasi
   * Ditolak / bermasalah jika ada

3. Tabs:

   * Menunggu Validasi
   * Dikembalikan
   * Tervalidasi
   * Semua

4. List review:
   Setiap item menampilkan:

* Unit
* Standar
* Indikator
* Target
* Realisasi
* Status capaian
* Jumlah bukti
* Submitted by
* Submitted at
* Action: Review

5. Review detail:
   Buat drawer/modal/custom page dengan layout 2 kolom:

Kiri:

* Informasi standar
* Informasi indikator
* Target
* Realisasi unit
* Catatan unit
* Status capaian

Kanan:

* Daftar bukti
* Link buka/download
* Riwayat review
* Form catatan LPM
* Button:

  * Validasi
  * Kembalikan untuk Revisi
  * Tolak jika dipakai

Behavior:

* Validasi:
  buat achievement_reviews status validated
  update indicator_achievements submission_status validated
  update assignment status validated

* Kembalikan:
  notes wajib
  buat review status returned
  update submission_status returned
  update assignment status returned

* Tolak:
  notes wajib
  buat review status rejected
  update sesuai enum existing

UI requirement:

* Inbox style
* Review cepat
* Tidak perlu create/edit CRUD biasa
* Bukti harus mudah dibuka
* Action jelas

Output:

* Custom page Inbox Validasi Capaian
* Review drawer/modal/page
* Actions validasi/return/reject
* Filters dan tabs
* Authorization admin_lpm/super_admin

==================================================
PHASE 7 — AUDITOR WORKSPACE
===========================

Tugas:
Buat custom page "Audit Saya" dan "Audit Workspace" untuk auditor.

Tujuan:
Auditor tidak bekerja dari banyak tabel CRUD, tapi dari satu halaman audit yang terarah.

Navigation untuk auditor:

* Audit Saya
* Verifikasi Tindak Lanjut

A. Audit Saya Index

Tampilkan:

* Daftar ami_audits yang user login menjadi auditor
* Unit auditee
* Periode AMI
* Jadwal audit
* Role auditor: lead/member/observer
* Progress checklist
* Jumlah temuan
* Status audit
* Action: Buka Audit

B. Audit Workspace Detail

Tabs:

1. Ringkasan
2. Checklist
3. Bukti Capaian
4. Temuan
5. Finalisasi

Tab Ringkasan:

* Unit auditee
* Jadwal audit
* Tim auditor
* Progress checklist
* Jumlah conform/minor/major/observasi/OFI
* Status audit

Tab Checklist:

* Tampilkan checklist sebagai task list/card
* Per indikator tampilkan:

  * Standar
  * Indikator
  * Target
  * Realisasi unit
  * Status validasi LPM
  * Bukti
  * Assessment result
  * Catatan auditor
  * Button simpan assessment
  * Button buat temuan jika assessment bukan conform

Tab Bukti Capaian:

* Group by standar/indikator
* Tampilkan semua evidence dari unit auditee
* Bisa open/download

Tab Temuan:

* Daftar temuan dari audit ini
* Kategori
* Deskripsi
* Rekomendasi
* Due date
* Status
* Action edit

Tab Finalisasi:

* Ringkasan hasil audit
* Checklist belum lengkap warning
* Temuan belum lengkap warning
* Button finalisasi audit khusus lead auditor/admin_lpm

Behavior:

* Auditor hanya bisa akses audit yang ditugaskan
* Observer view only
* Lead bisa finalisasi
* Admin LPM bisa monitor semua

Output:

* Custom page Audit Saya
* Custom page Audit Workspace
* Checklist task UI
* Create finding action from checklist
* Authorization auditor assignment

==================================================
PHASE 8 — FINDING & CORRECTIVE ACTION TICKETING WORKFLOW
========================================================

Tugas:
Ubah pengalaman Temuan dan Tindak Lanjut menjadi seperti ticketing system.

Buat custom page:

1. Temuan Saya untuk Unit/PIC
2. Monitoring Temuan untuk LPM
3. Verifikasi Tindak Lanjut untuk Auditor/LPM

A. Temuan Saya

Untuk unit_pic:
Query temuan dari ami_audits where auditee_unit_id = auth()->user()->unit_id.

Layout:

* Header unit
* Stat cards:

  * Terbuka
  * Dalam Proses
  * Menunggu Verifikasi
  * Perlu Revisi
  * Selesai
  * Terlambat

Tabs:

* Semua
* Terbuka
* Dalam Proses
* Menunggu Verifikasi
* Perlu Revisi
* Selesai

Card temuan:

* Nomor temuan
* Kategori
* Uraian singkat
* Rekomendasi
* Deadline
* Status
* Action: Tindak Lanjuti

Detail temuan:

* Uraian temuan
* Indikator terkait
* Rekomendasi auditor
* Due date
* Rencana perbaikan
* Root cause analysis
* PIC
* Target date
* Upload bukti
* Riwayat review
* Button:

  * Simpan Draft
  * Submit Verifikasi

B. Monitoring Temuan LPM

Tampilkan:

* Semua temuan
* Filter unit, periode, kategori, status
* Highlight overdue
* Table/card ringkas
* Action buka detail

C. Verifikasi Tindak Lanjut

Untuk auditor/admin_lpm:

* List corrective_actions status submitted/in_review/waiting verification
* Detail:

  * Temuan
  * Rencana perbaikan
  * Bukti perbaikan
  * Riwayat review
  * Catatan reviewer
  * Button:

    * Terima dan Tutup Temuan
    * Minta Revisi

Behavior:

* Accepted: corrective action accepted, finding closed
* Need revision: corrective action need_revision, finding need_revision
* Notes wajib untuk minta revisi

UI requirement:

* Ticketing style
* Card/list/tabs
* Jangan terasa CRUD
* Status timeline jika memungkinkan

Output:

* Custom page Temuan Saya
* Custom page Monitoring Temuan
* Custom page Verifikasi Tindak Lanjut
* Actions submit/review
* Authorization by role/unit/auditor

==================================================
PHASE 9 — RTM & IMPROVEMENT DECISION WORKSPACE
==============================================

Tugas:
Buat atau rapikan RTM dan Usulan Peningkatan Standar agar menjadi decision workspace, bukan CRUD biasa.

A. RTM Workspace

Index:

* Stat cards:

  * Total RTM
  * Draft
  * Completed
  * Usulan menunggu approval
* Table minimal:

  * Judul
  * Periode
  * Tanggal
  * Status
  * Jumlah item
  * Jumlah usulan
  * Action buka

Create RTM wizard:
Step 1 Informasi RTM
Step 2 Pilih bahan RTM
Step 3 Review
Step 4 Simpan Draft

Detail RTM:
Sections:

1. Header RTM
2. Ringkasan hasil AMI
3. Peserta
4. Item pembahasan
5. Keputusan RTM
6. Usulan peningkatan

Item pembahasan tampil sebagai cards:

* title
* type badge
* priority badge
* decision
* recommendation
* action buat usulan

B. Usulan Peningkatan Workspace

Index:

* Draft
* Submitted
* Approved
* Rejected
* Implemented

Detail proposal:

* Informasi usulan
* Objek terkait
* Preview perubahan
* Review/approval
* Implementasi
* Revision history

Actions:

* Ajukan
* Setujui
* Tolak
* Implementasikan

Implementasi:

* create_new_standard: buat standar draft
* revise_standard: update versi/deskripsi draft/revised
* create_new_indicator: buat indikator baru
* revise_indicator: update statement
* revise_target: update target
* simpan standard_revision_histories old_data/new_data
* jangan langsung active jika workflow approval standar masih ada

UI requirement:

* Decision page
* Clean cards
* Wizard untuk form panjang
* Action sedikit dan jelas

Output:

* RTM workspace
* Proposal workspace
* Actions approval/implementation
* Revision history display
* Authorization admin_lpm/pimpinan

==================================================
PHASE 10 — REPORTS WORKSPACE
============================

Tugas:
Buat halaman laporan yang workflow-based.

Jangan buat user memilih tabel mentah.
Buat custom page "Pusat Laporan".

Layout:

1. Header:

   * Title: Pusat Laporan
   * Subtitle: Unduh dan pantau laporan SPMI berdasarkan periode, unit, dan siklus mutu.

2. Report cards:

* Laporan Capaian Indikator
* Laporan Validasi Capaian
* Laporan AMI
* Laporan Temuan Audit
* Laporan Tindak Lanjut
* Laporan RTM
* Laporan Peningkatan Standar

Setiap card:

* Deskripsi
* Filter utama
* Button Generate PDF
* Button Export Excel

3. Filter global:

* Periode SPMI
* Periode AMI
* Unit
* Status
* Date range

4. Preview ringkas:
   Setelah memilih laporan, tampilkan preview 10 data pertama.

Authorization:

* admin_lpm dan pimpinan bisa semua
* unit hanya data unit sendiri
* auditor hanya data audit yang ditugaskan
* viewer terbatas

UI requirement:

* Report gallery/cards
* Minimal table preview
* Export actions jelas
* Jangan tampilkan banyak menu laporan terpisah kalau tidak perlu

Output:

* Custom page Pusat Laporan
* Report cards
* Filters
* Export buttons existing integration
* Role-based query

==================================================
PHASE 11 — NAVIGATION RESTRUCTURE
=================================

Tugas:
Rapikan navigation agar workflow-based.

Jangan semua Resource CRUD tampil ke semua role.

Navigation ideal:

Dashboard

Siklus SPMI

* Peta Siklus SPMI

Penetapan

* Standar Mutu
* Assign Indikator
* Dokumen Mutu

Pelaksanaan

* Capaian Indikator Saya
* Monitoring Capaian
* Inbox Validasi Capaian

Evaluasi AMI

* Audit Saya
* AMI Workspace
* Jadwal AMI
* Hasil Audit

Pengendalian

* Temuan Saya
* Monitoring Temuan
* Verifikasi Tindak Lanjut

Peningkatan

* Rapat Tinjauan Manajemen
* Usulan Peningkatan Standar
* Riwayat Revisi Standar

Laporan

* Pusat Laporan

Master Data

* Unit
* User
* Kategori Standar
* Periode SPMI
* Periode AMI

Aturan:

* super_admin: semua menu
* admin_lpm: semua workflow + master penting
* unit_pic: Dashboard, Capaian Saya, Temuan Saya, Dokumen Mutu, Laporan terbatas
* auditor: Dashboard, Audit Saya, Verifikasi Tindak Lanjut, Dokumen Mutu
* pimpinan: Dashboard, Siklus SPMI, Laporan, RTM, Usulan Approval
* viewer: dashboard/laporan terbatas

Resource CRUD teknis boleh tetap ada, tapi:

* sembunyikan dari role yang tidak perlu
* letakkan di Master Data / Pengaturan
* jangan jadi menu utama user operasional

Output:

* Update navigationGroup
* Update navigationLabel
* Update navigationIcon
* Update navigationSort
* Hide menu by role
* Pastikan tidak ada menu dobel/berantakan

==================================================
PHASE 12 — UI POLISH & CONSISTENCY
==================================

Tugas:
Rapikan visual consistency seluruh custom page/workspace.

Standar UI:

1. Bahasa Indonesia konsisten.

2. Badge status konsisten:

   * draft: gray
   * submitted/scheduled/waiting: warning
   * active/validated/approved/completed/closed: success
   * returned/need_revision/rejected/overdue: danger
   * in_progress/ongoing: info/primary

3. Empty state jelas:

   * "Belum ada capaian yang perlu divalidasi."
   * "Semua temuan sudah ditindaklanjuti."
   * "Belum ada audit yang ditugaskan kepada Anda."

4. Action button jelas:

   * Isi Capaian
   * Submit ke LPM
   * Validasi
   * Kembalikan
   * Buka Audit
   * Buat Temuan
   * Submit Tindak Lanjut
   * Terima Perbaikan
   * Minta Revisi
   * Buat RTM
   * Buat Usulan
   * Setujui
   * Tolak
   * Implementasikan

5. Hindari:

   * Menampilkan ID teknis
   * Table terlalu lebar
   * Terlalu banyak action dalam satu row
   * Form panjang tanpa section/wizard
   * Menu CRUD untuk user biasa

6. Gunakan:

   * Section
   * Card
   * Tabs
   * Wizard
   * Progress bar
   * Timeline
   * Infolist
   * Relation manager hanya di detail entitas

Output:

* Polish semua custom page
* Konsistensi badge/status
* Empty state
* Label dan helper text
* Action grouping
* Final navigation check
* Cursor pagination untuk list card - karena saat load cukup berat (default 10 per page)
* Ada fitur search untuk page yang memiliki list card

==================================================
PHASE 13 — WORKFLOW TESTING & QA
================================

Tugas:
Buat checklist testing manual dan feature test sederhana untuk workflow utama.

Manual test flow:

A. Admin LPM:

1. Login sebagai admin_lpm.
2. Buka dashboard.
3. Buka Siklus SPMI.
4. Buat standar.
5. Tambah indikator.
6. Assign indikator ke unit.
7. Cek monitoring capaian.
8. Validasi capaian unit.
9. Jadwalkan AMI.
10. Assign auditor.
11. Monitor temuan.
12. Buat RTM.
13. Buat usulan peningkatan.
14. Implementasikan revisi standar.

B. Unit/PIC:

1. Login sebagai unit.
2. Lihat dashboard unit.
3. Buka Capaian Indikator Saya.
4. Isi capaian.
5. Upload bukti.
6. Submit ke LPM.
7. Lihat hasil review.
8. Jika dikembalikan, revisi.
9. Buka Temuan Saya.
10. Submit tindak lanjut.

C. Auditor:

1. Login auditor.
2. Buka Audit Saya.
3. Buka Audit Workspace.
4. Isi checklist.
5. Buat temuan.
6. Verifikasi tindak lanjut.

D. Pimpinan:

1. Login pimpinan.
2. Lihat executive dashboard.
3. Lihat laporan.
4. Review usulan peningkatan.
5. Approve/reject.

Feature test prioritas:

* Unit tidak bisa melihat assignment unit lain.
* Unit tidak bisa validasi capaian.
* Admin LPM bisa validasi capaian.
* Auditor hanya bisa melihat audit yang ditugaskan.
* Unit hanya melihat temuan unitnya.
* Pimpinan bisa approve proposal.
* Implement proposal menyimpan revision history.

Output:

* Checklist manual testing
* Feature tests jika memungkinkan
* Perbaiki bug authorization/query yang ditemukan

==================================================
FINAL PRINCIPLE
===============

Jangan ubah aplikasi menjadi full custom dari nol.

Pertahankan:

* Resource CRUD untuk master data
* Relation manager untuk detail entitas
* Existing migration/model/relationship

Tambahkan:

* Workflow custom pages
* Role-based dashboard
* Task queues
* Workspace per role
* Wizard untuk proses panjang
* Ticketing style untuk temuan/tindak lanjut
* Decision workspace untuk RTM dan peningkatan standar

Target UX:
Aplikasi harus terasa seperti:
"Quality Management Workflow System"

Bukan:
"Admin panel berisi banyak tabel CRUD"
