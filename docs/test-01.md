# Test 01 - Manual End-to-End Workflow Aplikasi SPMI

Dokumen ini dipakai untuk mengetes aplikasi SPMI secara manual dari awal sampai akhir. Checklist ini berasumsi tester memakai data demo dari seeder, lalu membuat beberapa data baru untuk memastikan semua status dan alur utama dapat dicoba.

## 1. Prasyarat

- Aplikasi sudah berjalan dan bisa diakses.
- Database sudah berisi data demo.
- Panel aplikasi dibuka melalui `/admin`.
- Browser disarankan memakai mode normal, bukan private, agar sesi login stabil.
- Setelah berpindah role, logout terlebih dahulu lalu login ulang dengan akun role berikutnya.

Jika perlu menyiapkan ulang data demo:

```bash
php artisan migrate:fresh --seed
```

## 2. Akun Demo

Semua akun demo memakai password:

```text
123456
```

| Role | Email | Digunakan untuk |
| --- | --- | --- |
| Super Admin | `superadmin@spmi.test` | Cek akses penuh dan pengaturan |
| Admin LPM | `admin.lpm1@spmi.test` | Menjalankan workflow utama LPM |
| Pimpinan | `pimpinan1@spmi.test` | Review laporan dan approval usulan |
| Auditor | `auditor1@spmi.test` | Audit Saya, checklist, temuan, verifikasi |
| Unit/PIC | `pic.<kode_unit>@spmi.test` | Capaian Indikator Saya dan Temuan Saya |
| Viewer | `viewer@spmi.test` | Cek akses baca saja |

Untuk akun Unit/PIC, ganti `<kode_unit>` dengan kode unit yang tersedia dari seeder. Contoh: `pic.lpm@spmi.test` jika unit `LPM` tersedia. Jika ragu, login sebagai Admin LPM lalu buka **Master Data > Unit** untuk melihat kode unit.

## 3. Cara Mencatat Hasil Test

Gunakan format berikut saat menjalankan setiap case:

```text
Status: PASS / FAIL / BLOCKED
Catatan:
- ...
Bukti:
- screenshot / data yang terlihat / pesan error
```

Kriteria umum:

- **PASS**: langkah dapat diselesaikan dan expected result sesuai.
- **FAIL**: aplikasi menampilkan error, status tidak berubah, data salah, atau akses tidak sesuai.
- **BLOCKED**: tidak bisa dilanjutkan karena data prasyarat tidak ada atau akun tidak tersedia.

## 4. Referensi Status yang Harus Diuji

### 4.1 Periode SPMI

- Draf
- Aktif
- Ditutup
- Diarsipkan

### 4.2 Periode AMI

- Draf
- Terjadwal
- Berjalan
- Selesai
- Ditutup

### 4.3 Standar dan Dokumen Mutu

- Draf
- Dikirim
- Disetujui
- Aktif
- Direvisi
- Diarsipkan

### 4.4 Assignment dan Capaian Indikator

- Ditugaskan
- Dalam Proses
- Draf
- Dikirim
- Dikembalikan
- Tervalidasi

### 4.5 Review Capaian

- Menunggu Review
- Tervalidasi
- Perlu Perbaikan
- Ditolak

### 4.6 Audit AMI

- Direncanakan
- Terjadwal
- Berjalan
- Selesai
- Final

### 4.7 Hasil Checklist AMI

- Sesuai
- Observasi
- Minor
- Mayor
- OFI
- Tidak Berlaku

### 4.8 Temuan Audit

- Terbuka
- Dalam Proses
- Menunggu Verifikasi
- Perlu Revisi
- Ditutup

### 4.9 Tindak Lanjut

- Draf
- Dikirim
- Ditinjau
- Perlu Revisi
- Diterima

### 4.10 Usulan Peningkatan Standar

- Draf
- Diajukan
- Disetujui
- Ditolak
- Diimplementasikan

---

# Bagian A - Login, Dashboard, dan Navigasi

## A01 - Login sebagai Admin LPM

**Tujuan:** memastikan Admin LPM dapat masuk dan melihat dashboard workflow.

**Akun:** `admin.lpm1@spmi.test`

**Menu:** `/admin`

**Langkah:**

- [ ] Buka `/admin`.
- [ ] Login dengan akun Admin LPM.
- [ ] Pastikan halaman Dashboard SPMI tampil.
- [ ] Periksa ringkasan pekerjaan, queue, dan shortcut.
- [ ] Klik ikon panduan di topbar, sebelah kiri global search.
- [ ] Pastikan panduan terbuka di tab baru.

**Expected Result:**

- Dashboard SPMI tampil.
- Role di topbar menunjukkan Admin LPM.
- Ikon panduan membuka `/admin/panduan-penggunaan` di tab baru.

**Status yang dicek:** tidak ada perubahan status.

**Catatan bug:**

- 

## A02 - Cek Navigasi Admin LPM

**Tujuan:** memastikan menu utama workflow tersedia untuk Admin LPM.

**Akun:** `admin.lpm1@spmi.test`

**Menu:** Sidebar panel admin.

**Langkah:**

- [ ] Buka grup **Siklus SPMI**.
- [ ] Pastikan menu **Peta Siklus SPMI** tersedia.
- [ ] Buka grup **Penetapan**.
- [ ] Pastikan menu **Standar Mutu**, **Indikator Standar**, **Dokumen Mutu**, dan **Assign Indikator** tersedia.
- [ ] Buka grup **Pelaksanaan**.
- [ ] Pastikan menu **Monitoring Capaian** dan **Inbox Validasi Capaian** tersedia.
- [ ] Buka grup **Evaluasi AMI**.
- [ ] Pastikan menu **Jadwal AMI**, **Checklist Audit**, dan **Hasil Audit** tersedia.
- [ ] Buka grup **Pengendalian**.
- [ ] Pastikan menu **Monitoring Temuan**, **Tindak Lanjut**, dan **Verifikasi Tindak Lanjut** tersedia.
- [ ] Buka grup **Peningkatan**.
- [ ] Pastikan menu **Rapat Tinjauan Manajemen**, **Usulan Peningkatan Standar**, dan **Riwayat Revisi Standar** tersedia.
- [ ] Buka grup **Laporan**.
- [ ] Pastikan menu **Pusat Laporan** tersedia.

**Expected Result:**

- Semua menu workflow utama tersedia untuk Admin LPM.
- Tidak ada error saat membuka grup menu.

**Status yang dicek:** tidak ada perubahan status.

**Catatan bug:**

- 

## A03 - Login sebagai Unit/PIC

**Tujuan:** memastikan Unit/PIC hanya melihat workflow miliknya.

**Akun:** `pic.<kode_unit>@spmi.test`

**Menu:** `/admin`

**Langkah:**

- [ ] Logout dari Admin LPM.
- [ ] Login sebagai Unit/PIC.
- [ ] Pastikan Dashboard SPMI tampil.
- [ ] Buka **Pelaksanaan > Capaian Indikator Saya**.
- [ ] Buka **Pengendalian > Temuan Saya**.
- [ ] Pastikan menu validasi LPM tidak tersedia atau tidak dapat diakses.

**Expected Result:**

- Unit/PIC bisa melihat tugas unitnya.
- Unit/PIC tidak bisa membuka **Inbox Validasi Capaian**.
- Unit/PIC tidak bisa melihat assignment atau temuan unit lain.

**Status yang dicek:** tidak ada perubahan status.

**Catatan bug:**

- 

## A04 - Login sebagai Auditor

**Tujuan:** memastikan Auditor hanya melihat audit yang ditugaskan.

**Akun:** `auditor1@spmi.test`

**Menu:** **Evaluasi AMI > Audit Saya**

**Langkah:**

- [ ] Logout dari Unit/PIC.
- [ ] Login sebagai Auditor.
- [ ] Buka **Audit Saya**.
- [ ] Pastikan daftar audit tampil.
- [ ] Buka salah satu audit yang ditugaskan.

**Expected Result:**

- Auditor hanya melihat audit yang ditugaskan kepadanya.
- Auditor dapat membuka **Ruang Kerja Audit** untuk audit yang ditugaskan.

**Status yang dicek:** tidak ada perubahan status.

**Catatan bug:**

- 

## A05 - Login sebagai Pimpinan

**Tujuan:** memastikan Pimpinan dapat melihat dashboard, laporan, dan usulan peningkatan.

**Akun:** `pimpinan1@spmi.test`

**Menu:** Dashboard, Pusat Laporan, Usulan Peningkatan Standar.

**Langkah:**

- [ ] Logout dari Auditor.
- [ ] Login sebagai Pimpinan.
- [ ] Buka Dashboard.
- [ ] Buka **Laporan > Pusat Laporan**.
- [ ] Buka **Peningkatan > Usulan Peningkatan Standar**.

**Expected Result:**

- Pimpinan dapat melihat ringkasan dan laporan.
- Pimpinan dapat membuka usulan peningkatan.
- Pimpinan tidak perlu melihat menu operasional Unit/PIC seperti **Capaian Indikator Saya**.

**Status yang dicek:** tidak ada perubahan status.

**Catatan bug:**

- 

## A06 - Login sebagai Viewer

**Tujuan:** memastikan Viewer hanya memiliki akses baca sesuai permission.

**Akun:** `viewer@spmi.test`

**Menu:** Dashboard dan menu yang terlihat.

**Langkah:**

- [ ] Logout dari Pimpinan.
- [ ] Login sebagai Viewer.
- [ ] Buka dashboard.
- [ ] Coba buka beberapa menu yang tersedia.
- [ ] Pastikan tombol aksi create/update/delete/submit tidak tersedia.

**Expected Result:**

- Viewer bisa melihat data yang diizinkan.
- Viewer tidak bisa melakukan aksi operasional.

**Status yang dicek:** tidak ada perubahan status.

**Catatan bug:**

- 

---

# Bagian B - Siklus SPMI dan Master Data Dasar

## B01 - Cek Peta Siklus SPMI

**Tujuan:** memastikan Peta Siklus SPMI menampilkan progres PPEPP.

**Akun:** `admin.lpm1@spmi.test`

**Menu:** **Siklus SPMI > Peta Siklus SPMI**

**Langkah:**

- [ ] Login sebagai Admin LPM.
- [ ] Buka **Peta Siklus SPMI**.
- [ ] Pilih periode SPMI aktif.
- [ ] Cek kartu **Penetapan Standar**.
- [ ] Cek kartu **Pelaksanaan Standar**.
- [ ] Cek kartu **Evaluasi AMI**.
- [ ] Cek kartu **Pengendalian Temuan**.
- [ ] Cek kartu **Peningkatan Standar**.
- [ ] Klik tombol aksi pada salah satu tahap.

**Expected Result:**

- Semua tahap PPEPP tampil.
- Angka ringkasan dan progress tampil.
- Tombol aksi mengarah ke workspace yang benar.

**Status yang dicek:** status periode SPMI Aktif.

**Catatan bug:**

- 

## B02 - Buat Periode SPMI Draf

**Tujuan:** memastikan Admin LPM dapat membuat periode SPMI baru berstatus Draf.

**Akun:** `admin.lpm1@spmi.test`

**Menu:** **Master Data > Periode SPMI**

**Langkah:**

- [ ] Buka **Periode SPMI**.
- [ ] Klik **Tambah** atau aksi create.
- [ ] Isi nama: `SPMI Manual Test 01`.
- [ ] Isi tahun akademik dan semester.
- [ ] Isi tanggal mulai dan selesai.
- [ ] Pilih status **Draf** jika tersedia.
- [ ] Simpan.

**Expected Result:**

- Periode baru tersimpan.
- Status periode adalah **Draf**.
- Periode tampil di daftar.

**Status yang dicek:** Draf.

**Catatan bug:**

- 

## B03 - Ubah Status Periode SPMI menjadi Aktif, Ditutup, dan Diarsipkan

**Tujuan:** memastikan semua status periode SPMI dapat dicapai dari form admin.

**Akun:** `admin.lpm1@spmi.test`

**Menu:** **Master Data > Periode SPMI**

**Langkah:**

- [ ] Buka periode `SPMI Manual Test 01`.
- [ ] Ubah status menjadi **Aktif**.
- [ ] Simpan dan pastikan badge status berubah.
- [ ] Ubah status menjadi **Ditutup**.
- [ ] Simpan dan pastikan badge status berubah.
- [ ] Ubah status menjadi **Diarsipkan**.
- [ ] Simpan dan pastikan badge status berubah.
- [ ] Kembalikan ke **Aktif** jika periode ini akan dipakai untuk test lanjutan.

**Expected Result:**

- Status bisa berubah sesuai pilihan.
- Badge warna/status sesuai.
- Tidak ada error validasi.

**Status yang dicek:** Draf, Aktif, Ditutup, Diarsipkan.

**Catatan bug:**

- 

## B04 - Cek Master Data Unit dan User

**Tujuan:** memastikan data Unit dan User demo tersedia untuk seluruh workflow.

**Akun:** `admin.lpm1@spmi.test`

**Menu:** **Master Data > Unit**, **Master Data > User**

**Langkah:**

- [ ] Buka **Unit**.
- [ ] Pastikan unit demo tersedia.
- [ ] Catat minimal satu kode unit untuk test Unit/PIC.
- [ ] Buka **User**.
- [ ] Cari akun `admin.lpm1@spmi.test`.
- [ ] Cari akun `auditor1@spmi.test`.
- [ ] Cari akun `pimpinan1@spmi.test`.
- [ ] Cari akun `pic.<kode_unit>@spmi.test`.

**Expected Result:**

- Unit demo tersedia.
- User demo tersedia dan aktif.
- User memiliki role sesuai kebutuhan.

**Status yang dicek:** user aktif.

**Catatan bug:**

- 

---

# Bagian C - Penetapan Standar, Indikator, Dokumen, dan Assignment

## C01 - Buat Standar Mutu Draf

**Tujuan:** memastikan standar baru dapat dibuat dalam status Draf.

**Akun:** `admin.lpm1@spmi.test`

**Menu:** **Penetapan > Standar Mutu**

**Langkah:**

- [ ] Buka **Standar Mutu**.
- [ ] Klik **Tambah**.
- [ ] Isi kode: `STD-MT-01`.
- [ ] Isi nama: `Standar Manual Test 01`.
- [ ] Pilih kategori standar.
- [ ] Pilih periode SPMI aktif.
- [ ] Isi deskripsi.
- [ ] Pilih status **Draf**.
- [ ] Simpan.

**Expected Result:**

- Standar tersimpan.
- Status tampil **Draf**.
- Standar bisa dibuka di halaman detail.

**Status yang dicek:** Draf.

**Catatan bug:**

- 

## C02 - Ajukan dan Approve Standar Mutu

**Tujuan:** memastikan status standar dapat berjalan dari Draf ke Dikirim lalu Disetujui/Aktif.

**Akun:** `admin.lpm1@spmi.test`

**Menu:** **Penetapan > Standar Mutu**

**Langkah:**

- [ ] Buka standar `STD-MT-01`.
- [ ] Klik aksi **Ajukan** atau **Submit** jika tersedia.
- [ ] Pastikan status berubah menjadi **Dikirim**.
- [ ] Klik aksi **Setujui** atau **Approve** jika tersedia.
- [ ] Pastikan status berubah menjadi **Disetujui** atau **Aktif**.
- [ ] Jika tidak ada tombol aksi, ubah status lewat form edit sesuai pilihan yang tersedia.

**Expected Result:**

- Status standar dapat mencapai **Dikirim**.
- Status standar dapat mencapai **Disetujui** atau **Aktif**.
- Jika approval mencatat approver, nama approver atau tanggal approval terisi.

**Status yang dicek:** Draf, Dikirim, Disetujui, Aktif.

**Catatan bug:**

- 

## C03 - Tambah Indikator Standar

**Tujuan:** memastikan standar memiliki indikator untuk assignment dan capaian.

**Akun:** `admin.lpm1@spmi.test`

**Menu:** **Penetapan > Indikator Standar** atau detail **Standar Mutu**

**Langkah:**

- [ ] Buka **Indikator Standar**.
- [ ] Klik **Tambah**.
- [ ] Pilih standar `STD-MT-01`.
- [ ] Isi kode: `IKU-MT-01`.
- [ ] Isi pernyataan indikator.
- [ ] Pilih jenis **Persentase**.
- [ ] Isi target operator `>=`.
- [ ] Isi target nilai `80`.
- [ ] Isi satuan `%`.
- [ ] Aktifkan kebutuhan bukti jika ada opsi.
- [ ] Simpan.

**Expected Result:**

- Indikator tersimpan.
- Kode `IKU-MT-01` tampil di daftar.
- Indikator terkait dengan standar `STD-MT-01`.

**Status yang dicek:** jenis indikator Persentase, target `>= 80 %`.

**Catatan bug:**

- 

## C04 - Cek Semua Jenis Indikator

**Tujuan:** memastikan tipe indikator yang tersedia dapat dipilih.

**Akun:** `admin.lpm1@spmi.test`

**Menu:** **Penetapan > Indikator Standar**

**Langkah:**

- [ ] Buat atau edit indikator.
- [ ] Cek pilihan jenis indikator.
- [ ] Pastikan pilihan **Persentase** tersedia.
- [ ] Pastikan pilihan **Angka** tersedia.
- [ ] Pastikan pilihan **Ya/Tidak** tersedia.
- [ ] Pastikan pilihan **Checklist** tersedia.
- [ ] Pastikan pilihan **Teks** tersedia.

**Expected Result:**

- Semua jenis indikator tersedia di form.
- Form tetap bisa disimpan untuk jenis indikator yang dipilih.

**Status yang dicek:** tipe indikator.

**Catatan bug:**

- 

## C05 - Buat Dokumen Mutu Draf dan Aktif

**Tujuan:** memastikan dokumen mutu bisa dibuat dan statusnya berubah.

**Akun:** `admin.lpm1@spmi.test`

**Menu:** **Penetapan > Dokumen Mutu**

**Langkah:**

- [ ] Buka **Dokumen Mutu**.
- [ ] Klik **Tambah**.
- [ ] Pilih standar `STD-MT-01` jika field tersedia.
- [ ] Isi judul dokumen: `Dokumen Manual Test 01`.
- [ ] Pilih jenis dokumen, misalnya SOP atau Pedoman.
- [ ] Pilih status **Draf**.
- [ ] Simpan.
- [ ] Edit dokumen.
- [ ] Ubah status menjadi **Aktif**.
- [ ] Simpan.
- [ ] Jika tersedia, coba status **Diarsipkan**.

**Expected Result:**

- Dokumen tersimpan.
- Status bisa berubah dari **Draf** ke **Aktif**.
- Status **Diarsipkan** dapat dipilih jika tersedia.

**Status yang dicek:** Draf, Aktif, Diarsipkan.

**Catatan bug:**

- 

## C06 - Assign Indikator ke Unit

**Tujuan:** memastikan indikator dapat ditugaskan ke Unit/PIC.

**Akun:** `admin.lpm1@spmi.test`

**Menu:** **Penetapan > Assign Indikator**

**Langkah:**

- [ ] Buka **Assign Indikator**.
- [ ] Pilih periode SPMI aktif.
- [ ] Pilih indikator `IKU-MT-01`.
- [ ] Pilih minimal satu unit yang memiliki PIC.
- [ ] Isi tenggat.
- [ ] Pilih prioritas **Normal**.
- [ ] Simpan assignment.
- [ ] Cek ringkasan assignment.

**Expected Result:**

- Assignment berhasil dibuat.
- Status assignment awal adalah **Ditugaskan**.
- Assignment muncul di unit yang dipilih.

**Status yang dicek:** Ditugaskan.

**Catatan bug:**

- 

## C07 - Cek Assignment Tidak Duplikat

**Tujuan:** memastikan assignment indikator-unit-periode tidak dibuat ganda.

**Akun:** `admin.lpm1@spmi.test`

**Menu:** **Penetapan > Assign Indikator**

**Langkah:**

- [ ] Ulangi assignment `IKU-MT-01` ke unit dan periode yang sama.
- [ ] Simpan.
- [ ] Periksa notifikasi atau ringkasan hasil.
- [ ] Buka daftar penugasan atau detail standar.

**Expected Result:**

- Aplikasi tidak membuat assignment duplikat.
- Jika sudah ada, sistem menampilkan informasi existing/skipped.

**Status yang dicek:** Ditugaskan tetap satu record per indikator-unit-periode.

**Catatan bug:**

- 

---

# Bagian D - Pelaksanaan Capaian Indikator oleh Unit/PIC

## D01 - Unit/PIC Melihat Assignment Sendiri

**Tujuan:** memastikan Unit/PIC melihat indikator yang ditugaskan ke unitnya.

**Akun:** `pic.<kode_unit>@spmi.test`

**Menu:** **Pelaksanaan > Capaian Indikator Saya**

**Langkah:**

- [ ] Login sebagai Unit/PIC dari unit yang dipilih di C06.
- [ ] Buka **Capaian Indikator Saya**.
- [ ] Pilih periode SPMI aktif.
- [ ] Cari `IKU-MT-01`.
- [ ] Pastikan indikator tampil.
- [ ] Cari kode indikator dari unit lain jika diketahui.

**Expected Result:**

- `IKU-MT-01` tampil untuk unit yang ditugaskan.
- Indikator unit lain tidak tampil.

**Status yang dicek:** Ditugaskan.

**Catatan bug:**

- 

## D02 - Simpan Draf Capaian

**Tujuan:** memastikan Unit/PIC bisa menyimpan capaian sebagai Draf.

**Akun:** `pic.<kode_unit>@spmi.test`

**Menu:** **Pelaksanaan > Capaian Indikator Saya**

**Langkah:**

- [ ] Buka indikator `IKU-MT-01`.
- [ ] Klik tombol untuk isi atau edit capaian.
- [ ] Isi nilai realisasi `70`.
- [ ] Isi catatan: `Draf capaian manual test`.
- [ ] Simpan sebagai draf.
- [ ] Tutup modal atau kembali ke daftar.
- [ ] Buka tab **Draf** atau **Dalam Proses**.

**Expected Result:**

- Capaian tersimpan.
- Status capaian menjadi **Draf**.
- Status assignment menjadi **Dalam Proses**.

**Status yang dicek:** Dalam Proses, Draf.

**Catatan bug:**

- 

## D03 - Submit Capaian tanpa Bukti Wajib

**Tujuan:** memastikan indikator yang wajib bukti tidak bisa dikirim tanpa bukti.

**Akun:** `pic.<kode_unit>@spmi.test`

**Menu:** **Pelaksanaan > Capaian Indikator Saya**

**Langkah:**

- [ ] Buka indikator `IKU-MT-01`.
- [ ] Hapus bukti jika sebelumnya ada.
- [ ] Isi nilai realisasi.
- [ ] Klik submit/kirim ke LPM.

**Expected Result:**

- Sistem menolak submit.
- Pesan validasi muncul bahwa bukti wajib dilampirkan.
- Status tetap **Draf** atau **Dalam Proses**.

**Status yang dicek:** Draf, Dalam Proses.

**Catatan bug:**

- 

## D04 - Submit Capaian dengan Bukti Link

**Tujuan:** memastikan Unit/PIC dapat mengirim capaian lengkap ke LPM.

**Akun:** `pic.<kode_unit>@spmi.test`

**Menu:** **Pelaksanaan > Capaian Indikator Saya**

**Langkah:**

- [ ] Buka indikator `IKU-MT-01`.
- [ ] Isi nilai realisasi `85`.
- [ ] Isi tautan bukti, misalnya `https://example.com/bukti-capaian`.
- [ ] Isi deskripsi bukti.
- [ ] Klik submit/kirim ke LPM.
- [ ] Buka tab **Menunggu Validasi**.

**Expected Result:**

- Submit berhasil.
- Status capaian menjadi **Dikirim** atau **Menunggu Validasi**.
- Assignment menjadi **Dikirim**.
- Bukti link tampil di detail capaian.

**Status yang dicek:** Dikirim, Menunggu Review.

**Catatan bug:**

- 

## D05 - Unit/PIC Tidak Bisa Mengubah Capaian yang Sudah Dikirim

**Tujuan:** memastikan capaian submitted tidak dapat diedit sembarangan.

**Akun:** `pic.<kode_unit>@spmi.test`

**Menu:** **Pelaksanaan > Capaian Indikator Saya**

**Langkah:**

- [ ] Cari indikator yang sudah dikirim.
- [ ] Buka detail atau modal capaian.
- [ ] Periksa apakah tombol edit/simpan masih tersedia.
- [ ] Jika tombol ada, coba ubah nilai dan simpan.

**Expected Result:**

- Capaian yang sudah dikirim tidak dapat diedit, atau perubahan ditolak.
- Status tetap **Dikirim** sampai LPM melakukan review.

**Status yang dicek:** Dikirim.

**Catatan bug:**

- 

---

# Bagian E - Validasi Capaian oleh Admin LPM

## E01 - Admin LPM Melihat Capaian Menunggu Validasi

**Tujuan:** memastikan capaian yang dikirim Unit/PIC masuk ke inbox LPM.

**Akun:** `admin.lpm1@spmi.test`

**Menu:** **Pelaksanaan > Inbox Validasi Capaian**

**Langkah:**

- [ ] Logout dari Unit/PIC.
- [ ] Login sebagai Admin LPM.
- [ ] Buka **Inbox Validasi Capaian**.
- [ ] Pilih periode SPMI aktif.
- [ ] Cari `IKU-MT-01`.
- [ ] Pastikan capaian dari unit tampil.
- [ ] Buka detail review.

**Expected Result:**

- Capaian tampil di tab **Menunggu Validasi**.
- Nilai realisasi, catatan, dan bukti terlihat.

**Status yang dicek:** Dikirim, Menunggu Review.

**Catatan bug:**

- 

## E02 - Kembalikan Capaian untuk Perbaikan

**Tujuan:** memastikan LPM dapat mengembalikan capaian dan Unit/PIC bisa merevisi.

**Akun:** `admin.lpm1@spmi.test`

**Menu:** **Pelaksanaan > Inbox Validasi Capaian**

**Langkah:**

- [ ] Buka capaian `IKU-MT-01`.
- [ ] Klik tindakan **Kembalikan**.
- [ ] Kosongkan catatan review.
- [ ] Simpan.
- [ ] Pastikan sistem menolak karena catatan wajib.
- [ ] Isi catatan: `Lengkapi bukti pendukung`.
- [ ] Klik **Kembalikan** lagi.

**Expected Result:**

- Tanpa catatan, sistem menampilkan validasi.
- Dengan catatan, capaian berubah menjadi **Dikembalikan**.
- Review tercatat sebagai **Perlu Perbaikan**.

**Status yang dicek:** Dikembalikan, Perlu Perbaikan.

**Catatan bug:**

- 

## E03 - Unit/PIC Revisi Capaian Dikembalikan

**Tujuan:** memastikan capaian returned dapat dikirim ulang.

**Akun:** `pic.<kode_unit>@spmi.test`

**Menu:** **Pelaksanaan > Capaian Indikator Saya**

**Langkah:**

- [ ] Login sebagai Unit/PIC.
- [ ] Buka tab **Dikembalikan**.
- [ ] Cari `IKU-MT-01`.
- [ ] Buka form capaian.
- [ ] Baca catatan review.
- [ ] Perbaiki nilai atau bukti.
- [ ] Submit ulang ke LPM.

**Expected Result:**

- Capaian dapat diedit setelah dikembalikan.
- Status kembali menjadi **Dikirim**.
- Review history lama tetap ada dan review baru menunggu.

**Status yang dicek:** Dikembalikan ke Dikirim, Menunggu Review.

**Catatan bug:**

- 

## E04 - Validasi Capaian

**Tujuan:** memastikan LPM dapat menerima capaian.

**Akun:** `admin.lpm1@spmi.test`

**Menu:** **Pelaksanaan > Inbox Validasi Capaian**

**Langkah:**

- [ ] Login sebagai Admin LPM.
- [ ] Buka **Inbox Validasi Capaian**.
- [ ] Cari `IKU-MT-01`.
- [ ] Buka review.
- [ ] Klik **Validasi**.
- [ ] Cek tab **Tervalidasi**.

**Expected Result:**

- Capaian berubah menjadi **Tervalidasi**.
- Assignment berubah menjadi **Tervalidasi**.
- Review tercatat sebagai **Tervalidasi**.

**Status yang dicek:** Tervalidasi.

**Catatan bug:**

- 

## E05 - Tolak Capaian

**Tujuan:** memastikan LPM dapat menolak capaian pada data lain.

**Akun:** `admin.lpm1@spmi.test`

**Menu:** **Pelaksanaan > Inbox Validasi Capaian**

**Langkah:**

- [ ] Cari capaian lain yang masih **Dikirim** dari data demo.
- [ ] Buka review.
- [ ] Klik **Tolak**.
- [ ] Kosongkan catatan.
- [ ] Pastikan sistem meminta catatan.
- [ ] Isi alasan penolakan.
- [ ] Simpan.

**Expected Result:**

- Review tercatat **Ditolak**.
- Capaian kembali ke status yang perlu diperbaiki oleh unit.
- Catatan penolakan tampil di history.

**Status yang dicek:** Ditolak, Dikembalikan.

**Catatan bug:**

- 

## E06 - Unit/PIC Tidak Bisa Membuka Inbox Validasi

**Tujuan:** memastikan batas akses validasi.

**Akun:** `pic.<kode_unit>@spmi.test`

**Menu:** **Pelaksanaan > Inbox Validasi Capaian**

**Langkah:**

- [ ] Login sebagai Unit/PIC.
- [ ] Coba akses menu **Inbox Validasi Capaian** dari sidebar.
- [ ] Jika menu tidak ada, coba akses URL langsung `/admin/inbox-validasi-capaian` jika diketahui.

**Expected Result:**

- Menu tidak tampil atau akses ditolak.
- Unit/PIC tidak bisa melakukan validasi.

**Status yang dicek:** permission/access denied.

**Catatan bug:**

- 

---

# Bagian F - Evaluasi AMI dan Ruang Kerja Auditor

## F01 - Buat atau Cek Periode AMI

**Tujuan:** memastikan periode AMI tersedia untuk audit.

**Akun:** `admin.lpm1@spmi.test`

**Menu:** **Master Data > Periode AMI**

**Langkah:**

- [ ] Login sebagai Admin LPM.
- [ ] Buka **Periode AMI**.
- [ ] Cek periode AMI dari data demo.
- [ ] Jika ingin membuat baru, klik **Tambah**.
- [ ] Isi nama `AMI Manual Test 01`.
- [ ] Pilih periode SPMI.
- [ ] Isi tanggal mulai dan selesai.
- [ ] Simpan sebagai **Draf** atau **Terjadwal** jika tersedia.
- [ ] Ubah status ke **Berjalan** jika akan dipakai untuk audit.

**Expected Result:**

- Periode AMI tersedia.
- Status dapat dilihat dengan jelas.

**Status yang dicek:** Draf, Terjadwal, Berjalan.

**Catatan bug:**

- 

## F02 - Buat Jadwal AMI

**Tujuan:** memastikan Admin LPM dapat membuat audit untuk unit.

**Akun:** `admin.lpm1@spmi.test`

**Menu:** **Evaluasi AMI > Jadwal AMI**

**Langkah:**

- [ ] Buka **Jadwal AMI**.
- [ ] Klik **Tambah**.
- [ ] Pilih periode AMI.
- [ ] Pilih unit auditee.
- [ ] Isi tanggal audit.
- [ ] Pilih status awal **Direncanakan** atau **Terjadwal**.
- [ ] Simpan.

**Expected Result:**

- Jadwal audit tersimpan.
- Status audit awal tampil.
- Detail audit dapat dibuka.

**Status yang dicek:** Direncanakan, Terjadwal.

**Catatan bug:**

- 

## F03 - Assign Auditor ke Jadwal AMI

**Tujuan:** memastikan audit memiliki auditor.

**Akun:** `admin.lpm1@spmi.test`

**Menu:** Detail **Jadwal AMI**

**Langkah:**

- [ ] Buka detail audit dari F02.
- [ ] Buka bagian **Auditor**.
- [ ] Tambahkan `auditor1@spmi.test` sebagai **Ketua**.
- [ ] Tambahkan auditor lain sebagai **Anggota** jika perlu.
- [ ] Simpan.

**Expected Result:**

- Auditor tersimpan.
- Role auditor tampil sebagai **Ketua** atau **Anggota**.
- Audit muncul di akun auditor yang ditugaskan.

**Status yang dicek:** role auditor Ketua, Anggota.

**Catatan bug:**

- 

## F04 - Auditor Melihat Audit Saya

**Tujuan:** memastikan auditor hanya melihat audit yang ditugaskan.

**Akun:** `auditor1@spmi.test`

**Menu:** **Evaluasi AMI > Audit Saya**

**Langkah:**

- [ ] Login sebagai Auditor.
- [ ] Buka **Audit Saya**.
- [ ] Cari audit yang dibuat di F02.
- [ ] Buka audit.

**Expected Result:**

- Audit tampil untuk auditor yang ditugaskan.
- Audit yang tidak ditugaskan tidak tampil.
- Tombol buka workspace tersedia.

**Status yang dicek:** audit Terjadwal/Berjalan.

**Catatan bug:**

- 

## F05 - Isi Checklist dengan Semua Hasil Assessment

**Tujuan:** memastikan semua jenis hasil checklist dapat dicoba.

**Akun:** `auditor1@spmi.test`

**Menu:** **Audit Saya > Ruang Kerja Audit**

**Langkah:**

- [ ] Buka **Ruang Kerja Audit**.
- [ ] Pilih checklist pertama.
- [ ] Isi hasil **Sesuai** dan simpan.
- [ ] Pilih checklist berikutnya.
- [ ] Isi hasil **Observasi** dan simpan.
- [ ] Pilih checklist berikutnya.
- [ ] Isi hasil **Minor** dan simpan.
- [ ] Pilih checklist berikutnya.
- [ ] Isi hasil **Mayor** dan simpan.
- [ ] Pilih checklist berikutnya.
- [ ] Isi hasil **OFI** dan simpan.
- [ ] Pilih checklist berikutnya.
- [ ] Isi hasil **Tidak Berlaku** dan simpan.

**Expected Result:**

- Semua hasil assessment dapat disimpan.
- Badge hasil assessment tampil benar.
- Progress checklist berubah.

**Status yang dicek:** Sesuai, Observasi, Minor, Mayor, OFI, Tidak Berlaku.

**Catatan bug:**

- 

## F06 - Finalisasi Audit Ditolak Jika Checklist Belum Lengkap

**Tujuan:** memastikan audit tidak bisa final jika checklist belum selesai.

**Akun:** `auditor1@spmi.test`

**Menu:** **Ruang Kerja Audit**

**Langkah:**

- [ ] Pilih audit yang masih memiliki checklist kosong.
- [ ] Klik finalisasi audit.

**Expected Result:**

- Sistem menolak finalisasi.
- Pesan validasi menyebut checklist belum lengkap.
- Status audit tidak berubah menjadi Final.

**Status yang dicek:** Berjalan tetap Berjalan.

**Catatan bug:**

- 

## F07 - Buat Temuan dari Checklist

**Tujuan:** memastikan auditor dapat membuat temuan audit.

**Akun:** `auditor1@spmi.test`

**Menu:** **Ruang Kerja Audit**

**Langkah:**

- [ ] Pilih checklist dengan hasil **Minor** atau **Mayor**.
- [ ] Klik tindakan buat temuan.
- [ ] Isi deskripsi temuan.
- [ ] Isi rekomendasi.
- [ ] Isi tenggat tindak lanjut.
- [ ] Simpan temuan.

**Expected Result:**

- Temuan tersimpan.
- Kategori temuan sesuai hasil checklist.
- Status temuan awal **Terbuka**.

**Status yang dicek:** Terbuka, Minor/Major.

**Catatan bug:**

- 

## F08 - Finalisasi Audit Berhasil

**Tujuan:** memastikan audit bisa difinalisasi setelah checklist lengkap.

**Akun:** `auditor1@spmi.test`

**Menu:** **Ruang Kerja Audit**

**Langkah:**

- [ ] Pastikan semua checklist sudah memiliki hasil assessment.
- [ ] Pastikan temuan penting sudah dibuat.
- [ ] Klik finalisasi audit.
- [ ] Konfirmasi.

**Expected Result:**

- Audit berubah menjadi **Final**.
- Tanggal finalisasi dan finalizer tercatat jika ditampilkan.
- Temuan audit bisa dilihat oleh Unit/PIC setelah final.

**Status yang dicek:** Final.

**Catatan bug:**

- 

## F09 - Auditor Observer Tidak Bisa Mengisi Assessment

**Tujuan:** memastikan role auditor observer hanya dapat melihat.

**Akun:** auditor yang ditugaskan sebagai Observer jika tersedia.

**Menu:** **Audit Saya > Ruang Kerja Audit**

**Langkah:**

- [ ] Login sebagai auditor observer.
- [ ] Buka audit yang ditugaskan.
- [ ] Coba buka form assessment.
- [ ] Coba simpan assessment.

**Expected Result:**

- Observer dapat melihat audit.
- Observer tidak dapat menyimpan assessment.

**Status yang dicek:** permission/access denied.

**Catatan bug:**

- 

---

# Bagian G - Temuan dan Tindak Lanjut oleh Unit/PIC

## G01 - Unit/PIC Melihat Temuan Audit Final

**Tujuan:** memastikan Unit/PIC melihat temuan dari audit unitnya setelah audit final.

**Akun:** `pic.<kode_unit>@spmi.test`

**Menu:** **Pengendalian > Temuan Saya**

**Langkah:**

- [ ] Login sebagai Unit/PIC dari unit auditee audit final.
- [ ] Buka **Temuan Saya**.
- [ ] Cari temuan yang dibuat pada F07.
- [ ] Buka detail temuan.

**Expected Result:**

- Temuan tampil untuk unit terkait.
- Temuan unit lain tidak tampil.
- Status temuan awal **Terbuka**.

**Status yang dicek:** Terbuka.

**Catatan bug:**

- 

## G02 - Simpan Draf Tindak Lanjut

**Tujuan:** memastikan Unit/PIC bisa menyimpan draf tindak lanjut.

**Akun:** `pic.<kode_unit>@spmi.test`

**Menu:** **Pengendalian > Temuan Saya**

**Langkah:**

- [ ] Buka temuan dari G01.
- [ ] Klik buka/submit tindak lanjut.
- [ ] Isi rencana tindakan.
- [ ] Isi PIC jika tersedia.
- [ ] Isi target tanggal.
- [ ] Simpan sebagai draf.

**Expected Result:**

- Tindak lanjut tersimpan.
- Status tindak lanjut **Draf**.
- Status temuan berubah menjadi **Dalam Proses**.

**Status yang dicek:** Draf, Dalam Proses.

**Catatan bug:**

- 

## G03 - Submit Tindak Lanjut Minor/Major tanpa Bukti

**Tujuan:** memastikan temuan Minor/Major wajib memiliki bukti sebelum submit.

**Akun:** `pic.<kode_unit>@spmi.test`

**Menu:** **Pengendalian > Temuan Saya**

**Langkah:**

- [ ] Buka temuan Minor atau Mayor.
- [ ] Isi akar masalah.
- [ ] Isi rencana tindakan.
- [ ] Pilih PIC.
- [ ] Isi target tanggal.
- [ ] Jangan lampirkan bukti.
- [ ] Klik **Submit Tindak Lanjut**.

**Expected Result:**

- Sistem menolak submit.
- Pesan validasi bukti wajib tampil.
- Status tetap **Draf** atau **Dalam Proses**.

**Status yang dicek:** Draf, Dalam Proses.

**Catatan bug:**

- 

## G04 - Submit Tindak Lanjut dengan Bukti Link

**Tujuan:** memastikan tindak lanjut dapat dikirim untuk verifikasi.

**Akun:** `pic.<kode_unit>@spmi.test`

**Menu:** **Pengendalian > Temuan Saya**

**Langkah:**

- [ ] Buka temuan dari G03.
- [ ] Isi akar masalah.
- [ ] Isi rencana tindakan.
- [ ] Pilih PIC.
- [ ] Isi target tanggal.
- [ ] Tambahkan tautan bukti `https://example.com/bukti-tindak-lanjut`.
- [ ] Isi deskripsi bukti.
- [ ] Klik **Submit Tindak Lanjut**.

**Expected Result:**

- Tindak lanjut terkirim.
- Status tindak lanjut **Dikirim**.
- Status temuan **Menunggu Verifikasi**.

**Status yang dicek:** Dikirim, Menunggu Verifikasi.

**Catatan bug:**

- 

## G05 - Admin LPM Monitoring Temuan

**Tujuan:** memastikan Admin LPM dapat melihat temuan seluruh unit.

**Akun:** `admin.lpm1@spmi.test`

**Menu:** **Pengendalian > Monitoring Temuan**

**Langkah:**

- [ ] Login sebagai Admin LPM.
- [ ] Buka **Monitoring Temuan**.
- [ ] Filter unit sesuai temuan dari G04.
- [ ] Filter status **Menunggu Verifikasi**.
- [ ] Cari kode atau deskripsi temuan.

**Expected Result:**

- Temuan tampil di monitoring.
- Ringkasan angka berubah sesuai status.
- Filter dan search bekerja.

**Status yang dicek:** Menunggu Verifikasi.

**Catatan bug:**

- 

---

# Bagian H - Verifikasi Tindak Lanjut

## H01 - Verifikator Membuka Tindak Lanjut Submitted

**Tujuan:** memastikan auditor/Admin LPM dapat membuka tindak lanjut yang dikirim.

**Akun:** `auditor1@spmi.test` atau `admin.lpm1@spmi.test`

**Menu:** **Pengendalian > Verifikasi Tindak Lanjut**

**Langkah:**

- [ ] Login sebagai auditor yang ditugaskan atau Admin LPM.
- [ ] Buka **Verifikasi Tindak Lanjut**.
- [ ] Cari tindak lanjut dari G04.
- [ ] Buka detail.

**Expected Result:**

- Tindak lanjut tampil di tab **Menunggu Verifikasi**.
- Setelah dibuka, status dapat menjadi **Ditinjau** jika aplikasi menandai review sedang berjalan.

**Status yang dicek:** Dikirim, Ditinjau.

**Catatan bug:**

- 

## H02 - Minta Revisi Tindak Lanjut

**Tujuan:** memastikan verifikator dapat meminta revisi.

**Akun:** `auditor1@spmi.test` atau `admin.lpm1@spmi.test`

**Menu:** **Pengendalian > Verifikasi Tindak Lanjut**

**Langkah:**

- [ ] Buka tindak lanjut yang sedang diverifikasi.
- [ ] Pilih tindakan **Minta Revisi** atau sejenisnya.
- [ ] Kosongkan catatan.
- [ ] Pastikan sistem meminta catatan jika diwajibkan.
- [ ] Isi catatan: `Bukti belum cukup jelas`.
- [ ] Simpan.

**Expected Result:**

- Tindak lanjut berubah menjadi **Perlu Revisi**.
- Temuan berubah menjadi **Perlu Revisi**.
- Catatan review tampil untuk Unit/PIC.

**Status yang dicek:** Perlu Revisi.

**Catatan bug:**

- 

## H03 - Unit/PIC Revisi Tindak Lanjut

**Tujuan:** memastikan Unit/PIC dapat memperbaiki tindak lanjut yang perlu revisi.

**Akun:** `pic.<kode_unit>@spmi.test`

**Menu:** **Pengendalian > Temuan Saya**

**Langkah:**

- [ ] Login sebagai Unit/PIC.
- [ ] Buka tab **Perlu Revisi**.
- [ ] Buka temuan.
- [ ] Baca catatan verifikasi.
- [ ] Perbaiki akar masalah, rencana tindakan, atau bukti.
- [ ] Submit ulang.

**Expected Result:**

- Status kembali menjadi **Dikirim** atau **Menunggu Verifikasi**.
- Catatan review sebelumnya tetap dapat dilacak.

**Status yang dicek:** Perlu Revisi ke Dikirim/Menunggu Verifikasi.

**Catatan bug:**

- 

## H04 - Terima Perbaikan

**Tujuan:** memastikan tindak lanjut dapat diterima dan temuan ditutup.

**Akun:** `auditor1@spmi.test` atau `admin.lpm1@spmi.test`

**Menu:** **Pengendalian > Verifikasi Tindak Lanjut**

**Langkah:**

- [ ] Login sebagai verifikator.
- [ ] Buka tindak lanjut yang sudah dikirim ulang.
- [ ] Klik **Terima Perbaikan** atau tindakan accept.
- [ ] Konfirmasi.

**Expected Result:**

- Tindak lanjut berubah menjadi **Diterima**.
- Temuan berubah menjadi **Ditutup**.
- Item tidak lagi muncul sebagai menunggu verifikasi.

**Status yang dicek:** Diterima, Ditutup.

**Catatan bug:**

- 

## H05 - Tolak Tindak Lanjut

**Tujuan:** memastikan skenario penolakan dapat dicatat.

**Akun:** `auditor1@spmi.test` atau `admin.lpm1@spmi.test`

**Menu:** **Pengendalian > Verifikasi Tindak Lanjut**

**Langkah:**

- [ ] Cari tindak lanjut lain berstatus **Dikirim** dari data demo.
- [ ] Buka detail.
- [ ] Pilih tindakan **Tolak** jika tersedia.
- [ ] Isi alasan penolakan.
- [ ] Simpan.

**Expected Result:**

- Review tindak lanjut tercatat **Ditolak**.
- Status tindak lanjut atau temuan menunjukkan perlu tindakan lanjutan sesuai desain aplikasi.
- Catatan alasan tampil.

**Status yang dicek:** Ditolak.

**Catatan bug:**

- 

---

# Bagian I - RTM dan Peningkatan Standar

## I01 - Buat RTM Draf

**Tujuan:** memastikan Admin LPM dapat membuat Rapat Tinjauan Manajemen.

**Akun:** `admin.lpm1@spmi.test`

**Menu:** **Peningkatan > Rapat Tinjauan Manajemen**

**Langkah:**

- [ ] Login sebagai Admin LPM.
- [ ] Buka **Rapat Tinjauan Manajemen**.
- [ ] Klik tambah/buat RTM.
- [ ] Pilih periode SPMI.
- [ ] Isi judul: `RTM Manual Test 01`.
- [ ] Isi ringkasan dan kesimpulan awal.
- [ ] Simpan sebagai **Draf**.

**Expected Result:**

- RTM tersimpan.
- Status awal **Draf**.
- Detail RTM dapat dibuka.

**Status yang dicek:** Draf.

**Catatan bug:**

- 

## I02 - Jadwalkan dan Selesaikan RTM

**Tujuan:** memastikan status RTM dapat berubah sesuai tahap.

**Akun:** `admin.lpm1@spmi.test`

**Menu:** **Peningkatan > Rapat Tinjauan Manajemen**

**Langkah:**

- [ ] Buka RTM `RTM Manual Test 01`.
- [ ] Ubah status menjadi **Terjadwal** jika field tersedia.
- [ ] Simpan.
- [ ] Tambahkan peserta jika tersedia.
- [ ] Tambahkan item pembahasan jika tersedia.
- [ ] Ubah status menjadi **Selesai**.
- [ ] Simpan.
- [ ] Jika tersedia, ubah status menjadi **Ditutup**.

**Expected Result:**

- Status RTM dapat mencapai Terjadwal, Selesai, dan Ditutup.
- Peserta dan item pembahasan tampil di detail.

**Status yang dicek:** Terjadwal, Selesai, Ditutup.

**Catatan bug:**

- 

## I03 - Buat Usulan Peningkatan Draf

**Tujuan:** memastikan Admin LPM dapat membuat draf usulan peningkatan.

**Akun:** `admin.lpm1@spmi.test`

**Menu:** **Peningkatan > Usulan Peningkatan Standar**

**Langkah:**

- [ ] Buka **Usulan Peningkatan Standar**.
- [ ] Klik buat draf jika tersedia.
- [ ] Pilih jenis **Revisi Standar**.
- [ ] Pilih standar `STD-MT-01` atau standar demo.
- [ ] Isi judul: `Usulan Manual Test 01`.
- [ ] Isi latar belakang, kondisi saat ini, perubahan yang diusulkan, alasan, dan dampak.
- [ ] Simpan sebagai draf.

**Expected Result:**

- Usulan tersimpan.
- Status **Draf**.
- Usulan bisa dibuka di detail.

**Status yang dicek:** Draf.

**Catatan bug:**

- 

## I04 - Ajukan Usulan Peningkatan

**Tujuan:** memastikan usulan dapat diajukan ke Pimpinan.

**Akun:** `admin.lpm1@spmi.test`

**Menu:** Detail **Usulan Peningkatan Standar**

**Langkah:**

- [ ] Buka usulan `Usulan Manual Test 01`.
- [ ] Klik **Ajukan**.
- [ ] Konfirmasi.

**Expected Result:**

- Status berubah menjadi **Diajukan**.
- Usulan muncul untuk Pimpinan.

**Status yang dicek:** Diajukan.

**Catatan bug:**

- 

## I05 - Pimpinan Menolak Usulan

**Tujuan:** memastikan skenario reject usulan berjalan.

**Akun:** `pimpinan1@spmi.test`

**Menu:** **Peningkatan > Usulan Peningkatan Standar**

**Langkah:**

- [ ] Login sebagai Pimpinan.
- [ ] Buka usulan lain yang berstatus **Diajukan** dari data demo atau buat usulan baru.
- [ ] Buka detail usulan.
- [ ] Klik **Tolak**.
- [ ] Kosongkan alasan jika form mewajibkan alasan.
- [ ] Pastikan validasi muncul.
- [ ] Isi alasan penolakan.
- [ ] Simpan.

**Expected Result:**

- Status usulan berubah menjadi **Ditolak**.
- Reviewer dan catatan review tercatat.

**Status yang dicek:** Ditolak.

**Catatan bug:**

- 

## I06 - Pimpinan Menyetujui Usulan

**Tujuan:** memastikan approval usulan berjalan.

**Akun:** `pimpinan1@spmi.test`

**Menu:** **Peningkatan > Usulan Peningkatan Standar**

**Langkah:**

- [ ] Buka usulan `Usulan Manual Test 01`.
- [ ] Klik **Setujui**.
- [ ] Isi catatan review jika tersedia.
- [ ] Simpan.

**Expected Result:**

- Status usulan berubah menjadi **Disetujui**.
- Reviewer adalah Pimpinan yang login.
- Tanggal review tercatat jika ditampilkan.

**Status yang dicek:** Disetujui.

**Catatan bug:**

- 

## I07 - Admin LPM Implementasikan Usulan

**Tujuan:** memastikan usulan yang disetujui dapat diimplementasikan dan mencatat riwayat revisi.

**Akun:** `admin.lpm1@spmi.test`

**Menu:** Detail **Usulan Peningkatan Standar**

**Langkah:**

- [ ] Login sebagai Admin LPM.
- [ ] Buka usulan `Usulan Manual Test 01`.
- [ ] Klik **Implementasikan**.
- [ ] Konfirmasi.
- [ ] Buka **Peningkatan > Riwayat Revisi Standar**.
- [ ] Cari riwayat terkait usulan.

**Expected Result:**

- Status usulan berubah menjadi **Diimplementasikan**.
- Standar terkait berubah sesuai jenis usulan, misalnya menjadi **Direvisi** dan versi bertambah.
- Riwayat revisi tercatat.

**Status yang dicek:** Diimplementasikan, Direvisi.

**Catatan bug:**

- 

## I08 - Cek Semua Jenis Usulan Peningkatan

**Tujuan:** memastikan semua tipe usulan tersedia.

**Akun:** `admin.lpm1@spmi.test`

**Menu:** **Peningkatan > Usulan Peningkatan Standar**

**Langkah:**

- [ ] Buka form buat usulan.
- [ ] Cek pilihan **Revisi Standar**.
- [ ] Cek pilihan **Standar Baru**.
- [ ] Cek pilihan **Revisi Indikator**.
- [ ] Cek pilihan **Indikator Baru**.
- [ ] Cek pilihan **Hapus Indikator**.
- [ ] Cek pilihan **Revisi Target**.
- [ ] Cek pilihan **Revisi Dokumen**.
- [ ] Pilih beberapa tipe dan pastikan field terkait muncul sesuai kebutuhan.

**Expected Result:**

- Semua tipe usulan tersedia.
- Field form berubah sesuai tipe.
- Form tidak menampilkan field wajib yang tidak relevan.

**Status yang dicek:** tipe usulan.

**Catatan bug:**

- 

---

# Bagian J - Laporan

## J01 - Cek Pusat Laporan Admin LPM

**Tujuan:** memastikan Admin LPM dapat melihat dan mengekspor laporan.

**Akun:** `admin.lpm1@spmi.test`

**Menu:** **Laporan > Pusat Laporan**

**Langkah:**

- [ ] Login sebagai Admin LPM.
- [ ] Buka **Pusat Laporan**.
- [ ] Pilih **Laporan Capaian Indikator per Periode**.
- [ ] Pilih periode.
- [ ] Cek preview data.
- [ ] Coba export PDF jika tombol tersedia.
- [ ] Coba export Excel jika tombol tersedia.

**Expected Result:**

- Preview laporan tampil.
- Export berjalan tanpa error.
- File hasil export terunduh.

**Status yang dicek:** laporan capaian.

**Catatan bug:**

- 

## J02 - Cek Semua Jenis Laporan

**Tujuan:** memastikan semua tipe laporan utama tersedia.

**Akun:** `admin.lpm1@spmi.test`

**Menu:** **Laporan > Pusat Laporan**

**Langkah:**

- [ ] Pilih **Laporan Capaian Indikator per Periode**.
- [ ] Pilih **Laporan Capaian Indikator per Unit**.
- [ ] Pilih **Laporan Validasi LPM**.
- [ ] Pilih **Laporan AMI per Periode**.
- [ ] Pilih **Laporan Temuan Audit**.
- [ ] Pilih **Laporan Tindak Lanjut Temuan**.
- [ ] Pilih **Laporan RTM**.
- [ ] Pilih **Laporan Peningkatan Standar**.
- [ ] Pastikan setiap pilihan menampilkan preview atau empty state yang jelas.

**Expected Result:**

- Semua jenis laporan tersedia.
- Tidak ada error saat mengganti tipe laporan.
- Empty state jelas jika data tidak ada.

**Status yang dicek:** semua jenis laporan.

**Catatan bug:**

- 

## J03 - Cek Laporan sebagai Pimpinan

**Tujuan:** memastikan Pimpinan dapat membaca laporan.

**Akun:** `pimpinan1@spmi.test`

**Menu:** **Laporan > Pusat Laporan**

**Langkah:**

- [ ] Login sebagai Pimpinan.
- [ ] Buka **Pusat Laporan**.
- [ ] Pilih beberapa tipe laporan.
- [ ] Coba filter periode atau unit.
- [ ] Coba export jika tombol tersedia.

**Expected Result:**

- Pimpinan dapat melihat laporan.
- Pimpinan dapat export jika permission tersedia.

**Status yang dicek:** permission laporan.

**Catatan bug:**

- 

## J04 - Cek Laporan sebagai Auditor

**Tujuan:** memastikan Auditor hanya melihat data terkait audit yang ditugaskan.

**Akun:** `auditor1@spmi.test`

**Menu:** **Laporan > Pusat Laporan**

**Langkah:**

- [ ] Login sebagai Auditor.
- [ ] Buka **Pusat Laporan**.
- [ ] Pilih laporan AMI, temuan, atau tindak lanjut.
- [ ] Periksa data yang tampil.

**Expected Result:**

- Auditor hanya melihat data yang terkait auditnya.
- Data audit yang tidak ditugaskan tidak tampil.

**Status yang dicek:** scope data auditor.

**Catatan bug:**

- 

---

# Bagian K - Permission Negatif dan Keamanan Workflow

## K01 - Unit/PIC Tidak Bisa Melihat Assignment Unit Lain

**Tujuan:** memastikan data unit terisolasi.

**Akun:** `pic.<kode_unit>@spmi.test`

**Menu:** **Pelaksanaan > Capaian Indikator Saya**

**Langkah:**

- [ ] Login sebagai Unit/PIC.
- [ ] Buka **Capaian Indikator Saya**.
- [ ] Cari indikator/unit lain yang diketahui dari Admin LPM.
- [ ] Coba ubah filter atau search.

**Expected Result:**

- Unit/PIC hanya melihat assignment unitnya sendiri.
- Data unit lain tidak tampil.

**Status yang dicek:** data isolation.

**Catatan bug:**

- 

## K02 - Unit/PIC Tidak Bisa Validasi Capaian

**Tujuan:** memastikan validasi hanya dilakukan role LPM/authorized.

**Akun:** `pic.<kode_unit>@spmi.test`

**Menu:** **Pelaksanaan > Inbox Validasi Capaian**

**Langkah:**

- [ ] Login sebagai Unit/PIC.
- [ ] Cari menu **Inbox Validasi Capaian**.
- [ ] Jika menu tidak ada, coba akses URL langsung jika diketahui.

**Expected Result:**

- Akses ditolak atau menu tidak tampil.
- Unit/PIC tidak dapat menjalankan aksi Validasi/Kembalikan/Tolak.

**Status yang dicek:** access denied.

**Catatan bug:**

- 

## K03 - Auditor Tidak Bisa Membuka Audit yang Tidak Ditugaskan

**Tujuan:** memastikan auditor hanya mengakses audit miliknya.

**Akun:** `auditor1@spmi.test`

**Menu:** **Evaluasi AMI > Audit Saya**

**Langkah:**

- [ ] Login sebagai Admin LPM dan catat satu audit yang tidak ditugaskan ke `auditor1@spmi.test`.
- [ ] Login sebagai `auditor1@spmi.test`.
- [ ] Buka **Audit Saya**.
- [ ] Pastikan audit tersebut tidak tampil.
- [ ] Jika memiliki URL detail audit, coba akses langsung.

**Expected Result:**

- Audit yang tidak ditugaskan tidak tampil.
- Akses langsung ditolak.

**Status yang dicek:** access denied.

**Catatan bug:**

- 

## K04 - Unit/PIC Tidak Bisa Melihat Temuan Unit Lain

**Tujuan:** memastikan temuan audit ter-scope ke unit.

**Akun:** `pic.<kode_unit>@spmi.test`

**Menu:** **Pengendalian > Temuan Saya**

**Langkah:**

- [ ] Login sebagai Unit/PIC.
- [ ] Buka **Temuan Saya**.
- [ ] Cari temuan unit lain yang diketahui dari Monitoring Temuan.
- [ ] Coba search dengan nama unit lain.

**Expected Result:**

- Temuan unit lain tidak tampil.
- Search tidak membocorkan data unit lain.

**Status yang dicek:** data isolation.

**Catatan bug:**

- 

## K05 - Viewer Tidak Bisa Melakukan Aksi Mutasi

**Tujuan:** memastikan viewer tidak dapat mengubah data.

**Akun:** `viewer@spmi.test`

**Menu:** semua menu yang terlihat.

**Langkah:**

- [ ] Login sebagai Viewer.
- [ ] Buka standar, capaian, audit, laporan, atau menu lain yang terlihat.
- [ ] Cari tombol tambah/edit/delete/submit/approve.
- [ ] Jika tombol muncul, coba klik.

**Expected Result:**

- Viewer tidak melihat aksi mutasi.
- Jika mencoba akses langsung, sistem menolak.

**Status yang dicek:** read-only.

**Catatan bug:**

- 

---

# Bagian L - Search, Filter, Pagination, dan Empty State

## L01 - Search Capaian Indikator Saya

**Tujuan:** memastikan search di halaman capaian tidak error dan menghasilkan data benar.

**Akun:** `pic.<kode_unit>@spmi.test`

**Menu:** **Pelaksanaan > Capaian Indikator Saya**

**Langkah:**

- [ ] Login sebagai Unit/PIC.
- [ ] Buka **Capaian Indikator Saya**.
- [ ] Input kode indikator yang ada.
- [ ] Pastikan hasil tampil.
- [ ] Input teks acak yang tidak ada.
- [ ] Pastikan empty state tampil.
- [ ] Hapus search.

**Expected Result:**

- Search berjalan tanpa error.
- Tidak muncul error pagination.
- Empty state jelas saat data tidak ditemukan.

**Status yang dicek:** search, empty state.

**Catatan bug:**

- 

## L02 - Search Audit Saya

**Tujuan:** memastikan search audit auditor berjalan.

**Akun:** `auditor1@spmi.test`

**Menu:** **Evaluasi AMI > Audit Saya**

**Langkah:**

- [ ] Login sebagai Auditor.
- [ ] Buka **Audit Saya**.
- [ ] Cari nama unit atau periode.
- [ ] Coba search teks acak.

**Expected Result:**

- Search memfilter audit.
- Empty state tampil jika tidak ada hasil.
- Tidak ada error pagination.

**Status yang dicek:** search, empty state.

**Catatan bug:**

- 

## L03 - Search Inbox Validasi Capaian

**Tujuan:** memastikan search inbox LPM berjalan.

**Akun:** `admin.lpm1@spmi.test`

**Menu:** **Pelaksanaan > Inbox Validasi Capaian**

**Langkah:**

- [ ] Login sebagai Admin LPM.
- [ ] Buka **Inbox Validasi Capaian**.
- [ ] Cari kode indikator.
- [ ] Cari nama unit.
- [ ] Cari teks acak.

**Expected Result:**

- Search memfilter capaian.
- Empty state jelas.
- Tidak ada error pagination.

**Status yang dicek:** search, empty state.

**Catatan bug:**

- 

## L04 - Search Temuan Saya, Monitoring Temuan, dan Verifikasi Tindak Lanjut

**Tujuan:** memastikan semua search list-card Pengendalian berjalan.

**Akun:** Unit/PIC, Admin LPM, Auditor.

**Menu:** **Temuan Saya**, **Monitoring Temuan**, **Verifikasi Tindak Lanjut**

**Langkah:**

- [ ] Login sebagai Unit/PIC dan buka **Temuan Saya**.
- [ ] Search nomor/deskripsi temuan.
- [ ] Login sebagai Admin LPM dan buka **Monitoring Temuan**.
- [ ] Search nomor/unit/indikator.
- [ ] Login sebagai Auditor atau Admin LPM dan buka **Verifikasi Tindak Lanjut**.
- [ ] Search deskripsi atau action plan.

**Expected Result:**

- Semua search berjalan tanpa error.
- Empty state tampil saat tidak ada hasil.
- Pagination tetap normal.

**Status yang dicek:** search, empty state, pagination.

**Catatan bug:**

- 

---

# Bagian M - Checklist Penutupan Test

## M01 - Rekap Status yang Sudah Tercakup

Tandai status yang berhasil diuji:

- [ ] Periode SPMI: Draf.
- [ ] Periode SPMI: Aktif.
- [ ] Periode SPMI: Ditutup.
- [ ] Periode SPMI: Diarsipkan.
- [ ] Periode AMI: Draf.
- [ ] Periode AMI: Terjadwal.
- [ ] Periode AMI: Berjalan.
- [ ] Periode AMI: Selesai.
- [ ] Periode AMI: Ditutup.
- [ ] Standar Mutu: Draf.
- [ ] Standar Mutu: Dikirim.
- [ ] Standar Mutu: Disetujui.
- [ ] Standar Mutu: Aktif.
- [ ] Standar Mutu: Direvisi.
- [ ] Standar Mutu: Diarsipkan.
- [ ] Dokumen Mutu: Draf.
- [ ] Dokumen Mutu: Dikirim.
- [ ] Dokumen Mutu: Disetujui.
- [ ] Dokumen Mutu: Aktif.
- [ ] Dokumen Mutu: Diarsipkan.
- [ ] Assignment: Ditugaskan.
- [ ] Assignment: Dalam Proses.
- [ ] Assignment: Dikirim.
- [ ] Assignment: Dikembalikan.
- [ ] Assignment: Tervalidasi.
- [ ] Capaian: Draf.
- [ ] Capaian: Dikirim.
- [ ] Capaian: Dikembalikan.
- [ ] Capaian: Tervalidasi.
- [ ] Review capaian: Menunggu Review.
- [ ] Review capaian: Tervalidasi.
- [ ] Review capaian: Perlu Perbaikan.
- [ ] Review capaian: Ditolak.
- [ ] Audit: Direncanakan.
- [ ] Audit: Terjadwal.
- [ ] Audit: Berjalan.
- [ ] Audit: Selesai.
- [ ] Audit: Final.
- [ ] Checklist: Sesuai.
- [ ] Checklist: Observasi.
- [ ] Checklist: Minor.
- [ ] Checklist: Mayor.
- [ ] Checklist: OFI.
- [ ] Checklist: Tidak Berlaku.
- [ ] Temuan: Terbuka.
- [ ] Temuan: Dalam Proses.
- [ ] Temuan: Menunggu Verifikasi.
- [ ] Temuan: Perlu Revisi.
- [ ] Temuan: Ditutup.
- [ ] Tindak lanjut: Draf.
- [ ] Tindak lanjut: Dikirim.
- [ ] Tindak lanjut: Ditinjau.
- [ ] Tindak lanjut: Perlu Revisi.
- [ ] Tindak lanjut: Diterima.
- [ ] Usulan peningkatan: Draf.
- [ ] Usulan peningkatan: Diajukan.
- [ ] Usulan peningkatan: Disetujui.
- [ ] Usulan peningkatan: Ditolak.
- [ ] Usulan peningkatan: Diimplementasikan.

## M02 - Rekap Role yang Sudah Diuji

- [ ] Super Admin.
- [ ] Admin LPM.
- [ ] Unit/PIC.
- [ ] Auditor.
- [ ] Pimpinan.
- [ ] Viewer.

## M03 - Rekap Workflow End-to-End

- [ ] Login dan role topbar.
- [ ] Dashboard.
- [ ] Peta Siklus SPMI.
- [ ] Standar Mutu.
- [ ] Indikator Standar.
- [ ] Dokumen Mutu.
- [ ] Assign Indikator.
- [ ] Capaian Indikator Saya.
- [ ] Inbox Validasi Capaian.
- [ ] Jadwal AMI.
- [ ] Audit Saya.
- [ ] Ruang Kerja Audit.
- [ ] Temuan Saya.
- [ ] Monitoring Temuan.
- [ ] Verifikasi Tindak Lanjut.
- [ ] Rapat Tinjauan Manajemen.
- [ ] Usulan Peningkatan Standar.
- [ ] Riwayat Revisi Standar.
- [ ] Pusat Laporan.
- [ ] Permission negatif.
- [ ] Search/filter/pagination/empty state.

## M04 - Template Laporan Bug

Gunakan template ini untuk setiap bug:

```text
Judul:

Role:

Akun:

Menu/URL:

Data yang digunakan:

Langkah reproduksi:
1.
2.
3.

Expected result:

Actual result:

Status yang terdampak:

Screenshot/log:

Catatan tambahan:
```
