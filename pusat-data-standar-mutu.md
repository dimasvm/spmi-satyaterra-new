# Plan: Standar Mutu sebagai Pusat Data Standar

  ## Summary

  - Gunakan pendekatan stabil Filament v5: ViewRecord menampilkan Detail Standar sebagai infolist utama, lalu relation manager tabs di bawahnya dengan
    urutan Indikator, Dokumen Terkait, Unit yang Ditugaskan, Capaian.

  - QualityStandardResource tetap menjadi menu admin saja sesuai pilihan terakhir, tetapi route/query tetap diamankan untuk role lain jika punya permission
    view.

  - Tidak menggabungkan quality_standards dan quality_documents; dokumen tetap resource/model sendiri dan hanya ditampilkan sebagai relasi terkait.

  ## Key Changes

  - Rapikan QualityStandardResource:
      - Tambahkan/ekstrak QualityStandardInfolist berisi code, name, category.name, spmiPeriod.name, description, status, version, approver.name,
        approved_at, created_at, updated_at.

      - getEloquentQuery() eager load category, spmiPeriod, approver, dan count relasi penting untuk mencegah N+1.
      - Table tetap searchable code dan name, filter kategori/periode/status, badge status, navigation label Standar Mutu, group SPMI, icon dokumen.

  - Pertahankan dan rapikan IndicatorsRelationManager:
      - Title tab menjadi Indikator.
      - Reuse StandardIndicatorForm::components(includeQualityStandard: false).
      - Kolom: code, statement, indicator_type badge, target_operator, target_value, target_unit, weight, evidence_required icon.
      - CRUD mengikuti policy StandardIndicatorPolicy.

  - Tambahkan DocumentsRelationManager:
      - Relationship QualityStandard::documents().
      - Reuse/pecah QualityDocumentForm::components() agar field quality_standard_id dan spmi_period_id bisa disembunyikan saat dari standar.
      - Pada create, set otomatis quality_standard_id = ownerRecord->id, spmi_period_id = ownerRecord->spmi_period_id, uploaded_by = auth()->id().
      - Tambahkan action Buka File, Buka URL, Setujui, Arsipkan dengan policy yang sama seperti QualityDocumentResource.

  - Tambahkan AssignmentsRelationManager untuk tab Unit yang Ditugaskan:
      - Tambahkan QualityStandard::assignments() sebagai hasManyThrough ke IndicatorUnitAssignment melalui StandardIndicator.
      - Table eager load unit, standardIndicator, spmiPeriod, dan count achievements.
      - Kolom: unit.name, unit.type, standardIndicator.code, standardIndicator.statement, due_date, status, created_at.
      - Filter: unit, status, due date.
      - Action edit mengikuti policy; delete hanya muncul jika assignment belum punya capaian.
      - Tambahkan optional header action Tugaskan Unit yang memilih indikator dalam standar ini dan memakai flow existing AssignIndicatorsToUnits.

  - Tambahkan AchievementsRelationManager untuk tab Capaian:
      - Tambahkan QualityStandard::achievements() sebagai custom Builder query melalui IndicatorAchievement::whereHas('assignment.standardIndicator', ...).
      - Table eager load assignment.unit, assignment.standardIndicator, submittedBy, latestReview, dan withCount('evidences').
      - Kolom: unit, kode/pernyataan indikator, target, realisasi, status capaian, status submit, pengirim, waktu submit, jumlah bukti, review terakhir.
      - Action utama Detail mengarah ke IndicatorAchievementResource view agar bukti dan riwayat review memakai detail view existing.
      - Filter: unit, achievement status, submission status, submitted date, review status validated/returned.

  ## Authorization and Query Safety

  - Sidebar Standar Mutu tetap hanya super_admin dan admin_lpm.
  - Query tab:
      - admin_lpm dan super_admin: semua data standar terkait.
      - pimpinan: view-only semua data.
      - unit_pic: assignment/capaian hanya untuk auth()->user()->unit_id.
      - auditor: assignment/capaian dibatasi ke unit auditee dari audit yang ditugaskan ke auditor.
      - viewer: view terbatas, tanpa create/update/delete; dokumen mengikuti rule aktif yang sudah ada.

  - Semua relation manager menggunakan eager loading dan withCount() untuk menghindari N+1 pada tabel.

  ## Test Plan

  - Update/extend test relation manager indikator yang sudah ada.
  - Tambahkan feature tests untuk:
      - View detail Standar Mutu menampilkan infolist lengkap.
      - Dokumen terkait create otomatis mengisi quality_standard_id, spmi_period_id, uploaded_by.
      - Action approve/archive dokumen mengubah status dan audit fields.
      - Unit yang Ditugaskan hanya menampilkan assignment dari indikator standar tersebut.
      - Delete assignment hidden/gagal jika sudah punya capaian.
      - Capaian tab menampilkan capaian dari seluruh indikator standar dan menghormati scope unit.
      - Pimpinan/viewer tidak melihat action mutasi.

  - Verifikasi akhir:
      - php -l untuk file PHP yang diubah/dibuat.
      - vendor/bin/pint --dirty --format agent.
      - Test targeted: QualityStandardIndicatorsRelationManagerTest, test baru untuk hub Standar Mutu, dan test resource terkait dokumen/capaian/assignment.

  ## Assumptions

  - Detail Standar tidak dibuat sebagai tab Livewire tersendiri; ia menjadi infolist utama halaman view/edit, lalu relation managers menjadi tab di
    bawahnya. Ini pilihan paling stabil untuk Filament v5 dan tetap memenuhi urutan pengalaman pengguna.

  - Tidak ada migrasi baru; semua field yang diminta sudah ada di schema.
  - submitter() tidak perlu ditambahkan karena model sudah memiliki submittedBy(). Label UI akan memakai “Dikirim Oleh”.
