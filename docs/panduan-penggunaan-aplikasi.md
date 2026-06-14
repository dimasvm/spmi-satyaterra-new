# Panduan Penggunaan Aplikasi SPMI

Panduan ini membantu pengguna menjalankan proses Sistem Penjaminan Mutu Internal (SPMI) melalui panel aplikasi. Fokus panduan ini adalah alur kerja harian: apa yang harus dibuka, tindakan apa yang dilakukan, dan status apa yang perlu dicek setelah tindakan selesai.

## 1. Akses Aplikasi

1. Buka alamat aplikasi.
2. Masuk ke panel admin melalui halaman login.
3. Gunakan akun sesuai role yang diberikan oleh administrator.
4. Setelah berhasil masuk, aplikasi akan menampilkan Dashboard SPMI.

Jika menu tertentu tidak terlihat, kemungkinan akun belum memiliki role atau permission yang sesuai. Hubungi Admin LPM atau administrator sistem.

## 2. Konsep Utama

Aplikasi ini mengikuti siklus PPEPP:

| Tahap | Tujuan | Menu utama |
| --- | --- | --- |
| Penetapan | Menetapkan standar, indikator, dokumen mutu, dan penugasan indikator ke unit | Standar Mutu, Indikator Standar, Dokumen Mutu, Assign Indikator |
| Pelaksanaan | Unit mengisi capaian indikator dan LPM memvalidasi capaian | Capaian Indikator Saya, Monitoring Capaian, Inbox Validasi Capaian |
| Evaluasi AMI | Auditor menjalankan audit, mengisi checklist, dan mencatat temuan | Jadwal AMI, Audit Saya, Ruang Kerja Audit, Checklist Audit, Hasil Audit |
| Pengendalian | Unit menindaklanjuti temuan dan auditor/LPM memverifikasi perbaikan | Temuan Saya, Monitoring Temuan, Tindak Lanjut, Verifikasi Tindak Lanjut |
| Peningkatan | RTM dan usulan peningkatan digunakan untuk memperbaiki standar berikutnya | Rapat Tinjauan Manajemen, Usulan Peningkatan Standar, Riwayat Revisi Standar |

## 3. Role dan Tanggung Jawab

| Role | Tanggung jawab utama |
| --- | --- |
| Admin LPM | Mengelola siklus SPMI, standar, indikator, assignment, validasi capaian, AMI, RTM, laporan, dan peningkatan standar |
| Unit/PIC | Mengisi capaian indikator, mengunggah bukti, memperbaiki capaian yang dikembalikan, dan menindaklanjuti temuan audit |
| Auditor | Menjalankan audit yang ditugaskan, mengisi checklist, membuat temuan, dan memverifikasi tindak lanjut |
| Pimpinan | Melihat ringkasan mutu, membaca laporan, dan menyetujui atau menolak usulan peningkatan standar |
| Viewer | Melihat data sesuai akses yang diberikan tanpa melakukan tindakan operasional utama |

## 4. Mengenal Dashboard dan Navigasi

Dashboard SPMI menampilkan ringkasan pekerjaan sesuai role. Gunakan dashboard untuk melihat:

- pekerjaan yang perlu diselesaikan;
- data yang menunggu review;
- item yang terlambat;
- progres siklus SPMI;
- shortcut ke workspace utama.

Menu aplikasi dikelompokkan berdasarkan proses:

- **Siklus SPMI**: Peta Siklus SPMI.
- **Penetapan**: Standar Mutu, Indikator Standar, Dokumen Mutu, Assign Indikator.
- **Pelaksanaan**: Capaian Indikator Saya, Monitoring Capaian, Inbox Validasi Capaian.
- **Evaluasi AMI**: Jadwal AMI, Audit Saya, Checklist Audit, Hasil Audit.
- **Pengendalian**: Temuan Saya, Monitoring Temuan, Tindak Lanjut, Verifikasi Tindak Lanjut.
- **Peningkatan**: Rapat Tinjauan Manajemen, Usulan Peningkatan Standar, Riwayat Revisi Standar.
- **Laporan**: Pusat Laporan.
- **Master Data**: Unit, User, Periode SPMI, Periode AMI, Kategori Standar.
- **Pengaturan**: Role & Permission.

## 5. Panduan Admin LPM

### 5.1 Memantau Siklus SPMI

1. Buka **Dashboard SPMI**.
2. Cek periode SPMI aktif dan ringkasan pekerjaan.
3. Buka **Siklus SPMI > Peta Siklus SPMI**.
4. Pilih periode yang ingin dipantau.
5. Cek progres setiap tahap PPEPP.
6. Gunakan tombol pada tiap tahap untuk membuka workspace terkait.

Gunakan halaman ini untuk memastikan proses Penetapan, Pelaksanaan, Evaluasi AMI, Pengendalian, dan Peningkatan berjalan berurutan.

### 5.2 Menyiapkan Standar Mutu

1. Buka **Penetapan > Standar Mutu**.
2. Tambahkan atau ubah standar sesuai periode SPMI.
3. Lengkapi kode, nama, deskripsi, status, dan versi standar.
4. Simpan data.
5. Buka detail standar untuk mengelola indikator, dokumen terkait, unit yang ditugaskan, dan capaian.

Status yang umum digunakan:

- **Draf**: standar masih disiapkan.
- **Dikirim**: standar sudah diajukan untuk proses berikutnya.
- **Disetujui** atau **Aktif**: standar siap digunakan.
- **Direvisi**: standar sudah mengalami perubahan.
- **Diarsipkan**: standar tidak dipakai untuk siklus aktif.

### 5.3 Menambahkan Indikator Standar

1. Buka **Penetapan > Indikator Standar** atau buka detail **Standar Mutu** lalu bagian **Indikator**.
2. Tambahkan indikator.
3. Isi kode indikator, pernyataan indikator, jenis indikator, target, satuan target, bobot, dan kebutuhan bukti.
4. Simpan.

Jenis indikator yang tersedia antara lain Persentase, Angka, Ya/Tidak, Checklist, dan Teks.

### 5.4 Mengelola Dokumen Mutu

1. Buka **Penetapan > Dokumen Mutu**.
2. Tambahkan dokumen mutu sesuai standar.
3. Isi jenis dokumen, judul, status, dan file atau tautan jika diperlukan.
4. Simpan.

Dokumen dapat berstatus Draf, Dikirim, Disetujui, Aktif, atau Diarsipkan.

### 5.5 Assign Indikator ke Unit

1. Buka **Penetapan > Assign Indikator**.
2. Pilih periode SPMI.
3. Pilih indikator yang akan ditugaskan.
4. Pilih unit penerima tugas.
5. Atur tenggat, prioritas, dan catatan bila diperlukan.
6. Simpan assignment.
7. Cek ringkasan hasil assignment.

Setelah assignment dibuat, Unit/PIC akan melihat indikator tersebut di **Capaian Indikator Saya**.

### 5.6 Monitoring dan Validasi Capaian

1. Buka **Pelaksanaan > Monitoring Capaian** untuk melihat capaian seluruh unit.
2. Gunakan filter periode, unit, standar, atau status untuk mempersempit data.
3. Buka **Pelaksanaan > Inbox Validasi Capaian** untuk melihat capaian yang sudah dikirim unit.
4. Pilih capaian yang perlu direview.
5. Periksa realisasi, catatan, dan bukti.
6. Pilih salah satu tindakan:
   - **Validasi** jika capaian benar dan bukti memadai.
   - **Kembalikan** jika unit perlu memperbaiki data atau bukti.
   - **Tolak** jika capaian tidak dapat diterima.
7. Pastikan status capaian berubah sesuai tindakan.

### 5.7 Menyiapkan dan Memantau AMI

1. Buka **Evaluasi AMI > Jadwal AMI**.
2. Buat periode atau jadwal audit.
3. Tentukan unit auditee.
4. Tambahkan auditor melalui bagian **Auditor** pada detail audit.
5. Pastikan audit memiliki checklist.
6. Pantau progres audit melalui **Audit Saya**, **Checklist Audit**, atau **Hasil Audit** sesuai kebutuhan.

### 5.8 Monitoring Temuan dan Tindak Lanjut

1. Buka **Pengendalian > Monitoring Temuan**.
2. Gunakan filter unit, periode AMI, kategori, status, atau pencarian.
3. Cek temuan yang Terbuka, Dalam Proses, Menunggu Verifikasi, Perlu Revisi, atau Ditutup.
4. Buka **Pengendalian > Tindak Lanjut** untuk melihat daftar tindak lanjut.
5. Buka **Pengendalian > Verifikasi Tindak Lanjut** untuk memverifikasi tindak lanjut yang dikirim.

### 5.9 Rapat Tinjauan Manajemen

1. Buka **Peningkatan > Rapat Tinjauan Manajemen**.
2. Buat RTM untuk periode SPMI yang sesuai.
3. Tambahkan ringkasan, kesimpulan, peserta, dan item pembahasan.
4. Gunakan detail RTM untuk melihat keputusan dan membuat usulan peningkatan.
5. Finalisasi RTM bila seluruh keputusan sudah dicatat.

### 5.10 Usulan Peningkatan Standar

1. Buka **Peningkatan > Usulan Peningkatan Standar**.
2. Buat draf atau langsung ajukan usulan.
3. Pilih jenis usulan, misalnya Revisi Standar, Standar Baru, Revisi Indikator, Indikator Baru, Hapus Indikator, Revisi Target, atau Revisi Dokumen.
4. Lengkapi latar belakang, kondisi saat ini, perubahan yang diusulkan, alasan, dampak, dan objek terkait.
5. Ajukan usulan untuk direview Pimpinan.
6. Jika disetujui, Admin LPM dapat membuka detail usulan dan memilih **Implementasikan**.
7. Cek **Riwayat Revisi Standar** untuk memastikan revisi tercatat.

### 5.11 Membuka Laporan

1. Buka **Laporan > Pusat Laporan**.
2. Pilih jenis laporan.
3. Gunakan filter periode, unit, atau status bila tersedia.
4. Tinjau data.
5. Ekspor laporan jika diperlukan.

Jenis laporan utama:

- Laporan Capaian Indikator per Periode.
- Laporan Capaian Indikator per Unit.
- Laporan Validasi LPM.
- Laporan AMI per Periode.
- Laporan Temuan Audit.
- Laporan Tindak Lanjut Temuan.
- Laporan RTM.
- Laporan Peningkatan Standar.

## 6. Panduan Unit/PIC

### 6.1 Melihat Tugas Capaian

1. Masuk sebagai Unit/PIC.
2. Buka **Dashboard SPMI** untuk melihat ringkasan tugas.
3. Buka **Pelaksanaan > Capaian Indikator Saya**.
4. Pilih periode SPMI jika tersedia.
5. Gunakan tab status untuk melihat indikator:
   - Semua.
   - Belum Diisi.
   - Draf.
   - Menunggu Validasi.
   - Dikembalikan.
   - Tervalidasi.
   - Belum Tercapai.
6. Gunakan pencarian untuk mencari kode atau nama indikator.

Unit/PIC hanya melihat assignment untuk unitnya sendiri.

### 6.2 Mengisi Capaian Indikator

1. Buka **Capaian Indikator Saya**.
2. Pilih indikator yang akan diisi.
3. Klik tombol untuk membuka form capaian.
4. Isi nilai realisasi atau teks realisasi sesuai jenis indikator.
5. Isi status capaian dan catatan.
6. Tambahkan bukti:
   - unggah file, atau
   - isi tautan bukti eksternal.
7. Pilih **Simpan Draf** jika belum siap dikirim.
8. Pilih **Submit** atau **Kirim ke LPM** jika data sudah siap divalidasi.

Setelah submit, status akan berubah menjadi **Dikirim** atau **Menunggu Validasi**.

### 6.3 Memperbaiki Capaian yang Dikembalikan

1. Buka **Capaian Indikator Saya**.
2. Pilih tab **Dikembalikan**.
3. Buka capaian yang perlu diperbaiki.
4. Baca catatan review dari LPM.
5. Perbaiki nilai, catatan, atau bukti.
6. Submit ulang ke LPM.

### 6.4 Menindaklanjuti Temuan Audit

1. Buka **Pengendalian > Temuan Saya**.
2. Cek daftar temuan untuk unit Anda.
3. Gunakan tab status untuk melihat temuan Terbuka, Dalam Proses, Menunggu Verifikasi, Perlu Revisi, atau Selesai.
4. Buka temuan yang akan ditindaklanjuti.
5. Isi akar masalah, rencana tindakan, PIC, target tanggal, dan bukti.
6. Pilih **Simpan Draf** jika belum selesai.
7. Pilih **Submit Tindak Lanjut** jika siap diverifikasi.

Untuk temuan Minor atau Mayor, pastikan bukti tindak lanjut dilampirkan.

### 6.5 Memperbaiki Tindak Lanjut yang Perlu Revisi

1. Buka **Temuan Saya**.
2. Pilih tab **Perlu Revisi**.
3. Buka temuan.
4. Baca catatan verifikasi.
5. Perbaiki rencana tindakan atau bukti.
6. Submit ulang untuk verifikasi.

## 7. Panduan Auditor

### 7.1 Melihat Audit yang Ditugaskan

1. Masuk sebagai Auditor.
2. Buka **Evaluasi AMI > Audit Saya**.
3. Cek audit yang ditugaskan kepada Anda.
4. Gunakan pencarian jika daftar audit banyak.
5. Buka audit yang akan dikerjakan.

Auditor hanya melihat audit yang ditugaskan kepadanya.

### 7.2 Mengisi Checklist Audit

1. Buka **Audit Saya**.
2. Pilih audit.
3. Masuk ke **Ruang Kerja Audit**.
4. Pilih checklist yang perlu dinilai.
5. Isi hasil asesmen:
   - Sesuai.
   - Observasi.
   - Minor.
   - Mayor.
   - OFI.
   - Tidak Berlaku.
6. Tambahkan catatan auditor.
7. Simpan.

### 7.3 Membuat Temuan Audit

1. Dari **Ruang Kerja Audit**, buka checklist yang memiliki ketidaksesuaian atau peluang perbaikan.
2. Pilih tindakan untuk membuat temuan.
3. Isi deskripsi temuan, rekomendasi, kategori, dan tenggat.
4. Simpan temuan.
5. Pastikan temuan muncul di daftar temuan audit.

Kategori temuan yang tersedia:

- Observasi.
- Minor.
- Mayor.
- OFI.

### 7.4 Finalisasi Audit

1. Pastikan semua checklist sudah dinilai.
2. Pastikan temuan penting sudah dicatat.
3. Pilih tindakan finalisasi audit.
4. Cek status audit menjadi **Final**.

Setelah audit final, unit dapat melihat temuan yang perlu ditindaklanjuti.

### 7.5 Verifikasi Tindak Lanjut

1. Buka **Pengendalian > Verifikasi Tindak Lanjut**.
2. Cek tindak lanjut yang berstatus **Dikirim** atau **Ditinjau**.
3. Buka detail tindak lanjut.
4. Periksa akar masalah, rencana tindakan, PIC, target tanggal, dan bukti.
5. Pilih tindakan:
   - **Terima Perbaikan** jika tindak lanjut sudah memadai.
   - **Minta Revisi** jika masih perlu perbaikan.
   - **Tolak** jika tindak lanjut tidak dapat diterima.

## 8. Panduan Pimpinan

### 8.1 Memantau Ringkasan Mutu

1. Masuk sebagai Pimpinan.
2. Buka **Dashboard SPMI**.
3. Cek ringkasan capaian standar, temuan, tindak lanjut, dan usulan peningkatan.
4. Buka **Laporan > Pusat Laporan** untuk melihat laporan detail.

### 8.2 Membaca Laporan

1. Buka **Pusat Laporan**.
2. Pilih jenis laporan.
3. Gunakan filter sesuai kebutuhan.
4. Tinjau hasil laporan.
5. Ekspor laporan jika diperlukan untuk rapat atau arsip.

### 8.3 Review Usulan Peningkatan

1. Buka **Peningkatan > Usulan Peningkatan Standar**.
2. Pilih usulan berstatus **Diajukan**.
3. Buka detail usulan.
4. Baca latar belakang, kondisi saat ini, perubahan yang diusulkan, alasan, dan dampak.
5. Pilih:
   - **Setujui** jika usulan layak diimplementasikan.
   - **Tolak** jika usulan belum dapat diterima.
6. Isi catatan review bila diperlukan.

Setelah disetujui, Admin LPM dapat mengimplementasikan perubahan dan riwayat revisi akan tercatat.

## 9. Panduan Viewer

Viewer dapat melihat data sesuai akses yang diberikan. Viewer umumnya tidak melakukan tindakan operasional seperti submit capaian, validasi, membuat temuan, atau approve usulan.

Jika data tidak terlihat, hubungi Admin LPM untuk memastikan akses sudah sesuai.

## 10. Referensi Status

### 10.1 Capaian dan Assignment Indikator

| Status | Arti |
| --- | --- |
| Ditugaskan | Indikator sudah ditugaskan ke unit, tetapi belum dikerjakan |
| Dalam Proses | Unit mulai mengisi atau menyimpan draf |
| Draf | Data disimpan sementara dan belum dikirim |
| Dikirim | Data sudah dikirim ke LPM |
| Dikembalikan | LPM meminta perbaikan |
| Tervalidasi | Capaian sudah diterima oleh LPM |

### 10.2 Audit AMI

| Status | Arti |
| --- | --- |
| Direncanakan | Audit masih tahap perencanaan |
| Terjadwal | Audit sudah dijadwalkan |
| Berjalan | Audit sedang dilaksanakan |
| Selesai | Audit sudah selesai dikerjakan |
| Final | Audit sudah difinalisasi |

### 10.3 Temuan Audit

| Status | Arti |
| --- | --- |
| Terbuka | Temuan baru dan belum ditindaklanjuti |
| Dalam Proses | Unit mulai menyiapkan tindak lanjut |
| Menunggu Verifikasi | Tindak lanjut sudah dikirim untuk diverifikasi |
| Perlu Revisi | Verifikator meminta perbaikan |
| Ditutup | Temuan sudah selesai |

### 10.4 Tindak Lanjut

| Status | Arti |
| --- | --- |
| Draf | Tindak lanjut disimpan sementara |
| Dikirim | Tindak lanjut dikirim untuk review |
| Ditinjau | Tindak lanjut sedang diperiksa |
| Perlu Revisi | Tindak lanjut harus diperbaiki |
| Diterima | Tindak lanjut diterima |

### 10.5 Usulan Peningkatan Standar

| Status | Arti |
| --- | --- |
| Draf | Usulan masih disiapkan |
| Diajukan | Usulan menunggu review Pimpinan |
| Disetujui | Usulan disetujui dan siap diimplementasikan |
| Ditolak | Usulan tidak disetujui |
| Diimplementasikan | Perubahan sudah diterapkan dan riwayat revisi tercatat |

## 11. Masalah Umum

### 11.1 Menu Tidak Muncul

Kemungkinan penyebab:

- akun belum diberi role yang sesuai;
- permission belum diberikan;
- menu memang hanya tersedia untuk role tertentu.

Solusi:

1. Cek role akun Anda.
2. Hubungi Admin LPM atau administrator sistem.
3. Minta pengecekan di **Pengaturan > Role & Permission**.

### 11.2 Capaian Tidak Muncul di Unit

Kemungkinan penyebab:

- indikator belum di-assign ke unit;
- periode SPMI yang dipilih tidak sesuai;
- assignment dibuat untuk unit lain;
- pencarian atau filter masih aktif.

Solusi:

1. Hapus filter atau kata kunci pencarian.
2. Pastikan periode SPMI benar.
3. Minta Admin LPM mengecek **Assign Indikator**.

### 11.3 Capaian Tidak Bisa Disubmit

Kemungkinan penyebab:

- realisasi belum diisi;
- bukti wajib belum dilampirkan;
- tautan bukti tidak valid;
- data masih belum lengkap.

Solusi:

1. Lengkapi realisasi.
2. Lampirkan file atau tautan bukti.
3. Isi deskripsi bukti bila diperlukan.
4. Submit ulang.

### 11.4 Capaian Dikembalikan

Artinya LPM meminta perbaikan. Buka capaian, baca catatan review, perbaiki data atau bukti, lalu submit ulang.

### 11.5 Temuan Tidak Muncul di Unit

Kemungkinan penyebab:

- audit belum difinalisasi;
- temuan bukan untuk unit Anda;
- filter atau pencarian masih aktif.

Solusi:

1. Hapus filter.
2. Pastikan audit sudah final.
3. Hubungi Admin LPM jika temuan seharusnya muncul.

### 11.6 Tindak Lanjut Tidak Bisa Dikirim

Kemungkinan penyebab:

- akar masalah belum diisi;
- rencana tindakan belum diisi;
- PIC belum dipilih;
- target tanggal belum diisi;
- temuan Minor atau Mayor belum memiliki bukti.

Solusi:

1. Lengkapi seluruh field wajib.
2. Lampirkan bukti untuk temuan Minor atau Mayor.
3. Submit ulang tindak lanjut.

### 11.7 Usulan Peningkatan Tidak Bisa Diimplementasikan

Kemungkinan penyebab:

- usulan belum disetujui Pimpinan;
- objek standar atau indikator terkait belum dipilih;
- akun tidak memiliki role Admin LPM.

Solusi:

1. Pastikan usulan berstatus **Disetujui**.
2. Pastikan data terkait sudah lengkap.
3. Gunakan akun Admin LPM untuk implementasi.

## 12. Alur Singkat End-to-End

1. Admin LPM membuat Periode SPMI.
2. Admin LPM menyiapkan Standar Mutu, Indikator Standar, dan Dokumen Mutu.
3. Admin LPM melakukan Assign Indikator ke unit.
4. Unit/PIC mengisi dan submit Capaian Indikator Saya.
5. Admin LPM memvalidasi capaian melalui Inbox Validasi Capaian.
6. Admin LPM menjadwalkan AMI dan menugaskan auditor.
7. Auditor menjalankan audit di Ruang Kerja Audit.
8. Auditor mencatat temuan.
9. Unit/PIC menindaklanjuti temuan melalui Temuan Saya.
10. Auditor atau LPM memverifikasi tindak lanjut.
11. Admin LPM menyelenggarakan RTM.
12. Admin LPM membuat atau mengajukan Usulan Peningkatan Standar.
13. Pimpinan menyetujui atau menolak usulan.
14. Admin LPM mengimplementasikan usulan yang disetujui.
15. Riwayat Revisi Standar dan Pusat Laporan digunakan sebagai arsip dan bahan siklus berikutnya.
