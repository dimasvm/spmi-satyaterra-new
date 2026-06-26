<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Audit Mutu Internal (AMI)</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            color: #1e293b;
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9px;
            line-height: 1.4;
            margin: 15px;
        }

        .header {
            border-bottom: 2px solid #0f172a;
            padding-bottom: 10px;
            margin-bottom: 18px;
        }

        .header table {
            width: 100%;
            border: none;
        }

        .header td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }

        .logo-title h2 {
            color: #1e3a8a;
            font-size: 14px;
            font-weight: 700;
            margin: 0;
            letter-spacing: 0.5px;
        }

        .logo-title p {
            color: #64748b;
            font-size: 8px;
            margin: 2px 0 0 0;
        }

        .doc-title {
            text-align: right;
        }

        .doc-title h1 {
            color: #0f172a;
            font-size: 12px;
            font-weight: 800;
            margin: 0;
        }

        .doc-title h3 {
            color: #475569;
            font-size: 9px;
            font-weight: 600;
            margin: 4px 0 0 0;
        }

        .section-title {
            color: #1e3a8a;
            font-size: 10px;
            font-weight: 700;
            margin: 15px 0 6px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Tables styling */
        table.data-table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 14px;
        }

        table.data-table th,
        table.data-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }

        table.data-table th {
            background-color: #f1f5f9;
            color: #1e293b;
            font-weight: 700;
            font-size: 8px;
            text-transform: uppercase;
        }

        /* Metadata table */
        table.meta-table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 14px;
        }

        table.meta-table td {
            border: 1px solid #e2e8f0;
            padding: 5px 10px;
            vertical-align: top;
        }

        table.meta-table td.label {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 600;
            width: 25%;
        }

        table.meta-table td.value {
            color: #0f172a;
            font-weight: 500;
        }

        .text-center {
            text-align: center !important;
        }

        .text-right {
            text-align: right !important;
        }

        .font-bold {
            font-weight: bold;
        }

        .text-primary { color: #1e3a8a; }
        .text-success { color: #10b981; }
        .text-warning { color: #f59e0b; }
        .text-danger { color: #ef4444; }

        /* Signature Section */
        .signature-section {
            margin-top: 30px;
            width: 100%;
            page-break-inside: avoid;
        }

        .signature-table {
            width: 100%;
            border: none;
        }

        .signature-table td {
            border: none;
            width: 50%;
            vertical-align: top;
        }

        .signature-space {
            height: 60px;
        }

        /* Footer note */
        .footer-note {
            color: #64748b;
            font-size: 7.5px;
            font-style: italic;
            margin-top: 20px;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
        }

        .zebra tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .empty {
            color: #64748b;
            text-align: center;
            padding: 15px;
            font-style: italic;
        }
        
        .narrative-box {
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            padding: 10px 12px;
            border-radius: 4px;
            text-align: justify;
            font-size: 8.5px;
            color: #334155;
            line-height: 1.5;
        }

        .text-note {
            font-size: 8px;
            color: #64748b;
            margin-bottom: 6px;
            font-style: italic;
        }

        ol {
            margin: 0;
            padding-left: 15px;
        }

        li {
            margin-bottom: 4px;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <div class="header">
        <table>
            <tr>
                <td>
                    <div class="logo-title">
                        <h2>UNIVERSITAS {{ strtoupper($namaUniversitas) }}</h2>
                        <p>Lembaga Penjaminan Mutu (LPM)</p>
                    </div>
                </td>
                <td>
                    <div class="doc-title">
                        <h1>LAPORAN AUDIT MUTU INTERNAL (AMI)</h1>
                        <h3>SIKLUS: {{ $siklusAmi }} / TAHUN: {{ $tahunAmi }}</h3>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- I. IDENTITAS UNIT KERJA & TIM AUDITOR -->
    <div class="section-title">I. Identitas Unit Kerja & Tim Auditor</div>
    <table class="meta-table">
        <tr>
            <td class="label">Nama Unit Kerja (Auditee)</td>
            <td class="value font-bold">{{ $namaUnitKerja }}</td>
        </tr>
        <tr>
            <td class="label">Ketua / Kepala Unit Kerja</td>
            <td class="value">{{ $namaKetuaUnitKerja }} (Jabatan: {{ $jabatanKetuaUnitKerja }})</td>
        </tr>
        <tr>
            <td class="label">Ketua Tim Auditor</td>
            <td class="value font-bold">{{ $namaKetuaAuditor }}</td>
        </tr>
        <tr>
            <td class="label">Anggota Auditor</td>
            <td class="value">
                @if(!empty($anggotaAuditors))
                    <ol>
                        @foreach($anggotaAuditors as $anggota)
                            <li>{{ $anggota }}</li>
                        @endforeach
                    </ol>
                @else
                    -
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">Tanggal Pelaksanaan</td>
            <td class="value">{{ $tanggalPelaksanaanAmi }}</td>
        </tr>
    </table>

    <!-- II. PENDAHULUAN & TUJUAN AUDIT -->
    <div class="section-title">II. Pendahuluan & Tujuan Audit</div>
    <table class="meta-table">
        <tr>
            <td class="label">Tujuan Audit</td>
            <td class="value">
                Memetakan ketersediaan, kesiapan dokumen unit kerja, ketercapaian indikator mutu, serta memastikan bahwa temuan audit pada periode sebelumnya telah ditindaklanjuti dan tidak terjadi kembali pada siklus berjalan.
            </td>
        </tr>
        <tr>
            <td class="label">Lingkup Audit</td>
            <td class="value font-bold text-primary">{{ $lingkupStandarMutuAudit }}</td>
        </tr>
    </table>

    <!-- III. JADWAL PELAKSANAAN AUDIT -->
    <div class="section-title">III. Jadwal Pelaksanaan Audit</div>
    <table class="data-table zebra text-center">
        <thead>
            <tr>
                <th style="width: 8%;" class="text-center">No.</th>
                <th style="width: 25%;" class="text-center">Waktu / Jam</th>
                <th style="width: 67%;">Agenda / Kegiatan Audit</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td class="text-center">14.00 - 14.15</td>
                <td style="text-align: left;">Pembukaan Audit dan Penjelasan Ruang Lingkup</td>
            </tr>
            <tr>
                <td class="text-center">2</td>
                <td class="text-center">14.15 - 14.45</td>
                <td style="text-align: left;">Pengumpulan Dokumen Bukti Fisik / Pemeriksaan Sistem</td>
            </tr>
            <tr>
                <td class="text-center">3</td>
                <td class="text-center">14.45 - 15.15</td>
                <td style="text-align: left;">Proses Verifikasi, Konfirmasi, dan Wawancara Auditee</td>
            </tr>
            <tr>
                <td class="text-center">4</td>
                <td class="text-center">15.15 - 15.35</td>
                <td style="text-align: left;">Proses Perumusan Hasil Temuan KTS / OB oleh Tim Auditor</td>
            </tr>
            <tr>
                <td class="text-center">5</td>
                <td class="text-center">15.35 - 16.00</td>
                <td style="text-align: left;">Penyampaian Hasil Temuan dan Permintaan Tindakan Koreksi (PTK)</td>
            </tr>
            <tr>
                <td class="text-center">6</td>
                <td class="text-center">16.00 - 16.10</td>
                <td style="text-align: left;">Penutupan Pelaksanaan Audit Mutu Internal</td>
            </tr>
        </tbody>
    </table>

    <div style="page-break-before: always;"></div>

    <!-- IV. TEMUAN KETIDAKSESUAIAN AUDIT -->
    <div class="section-title">IV. Temuan Ketidaksesuaian Audit</div>
    <div class="text-note">*KTS = Ketidaksesuaian | DJ = Dropped Justification | OB = Observasi</div>
    <table class="data-table zebra">
        <thead>
            <tr>
                <th style="width: 15%;" class="text-center">Kode Ref</th>
                <th style="width: 10%;" class="text-center">Level</th>
                <th style="width: 30%;">Pernyataan Temuan (KTS / OB)</th>
                <th style="width: 25%;">Referensi Kriteria / Standar</th>
                <th style="width: 20%;">Analisis Akar Permasalahan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($findings as $finding)
                <tr>
                    <td class="text-center font-bold">{{ $finding->finding_number ?? '-' }}</td>
                    <td class="text-center font-bold">
                        @if($finding->category === \App\Enums\AmiFindingCategory::Major)
                            <span class="text-danger">Mayor</span>
                        @elseif($finding->category === \App\Enums\AmiFindingCategory::Minor)
                            <span class="text-warning">Minor</span>
                        @elseif($finding->category === \App\Enums\AmiFindingCategory::Observation)
                            <span class="text-primary">Saran</span>
                        @else
                            {{ $finding->category->getLabel() ?? '-' }}
                        @endif
                    </td>
                    <td>{{ $finding->description }}</td>
                    <td>
                        <strong>{{ $finding->standardIndicator?->qualityStandard?->code ?? '' }}</strong>
                        @if($finding->standardIndicator?->qualityStandard?->code && $finding->standardIndicator?->statement)
                            <br>
                        @endif
                        {{ $finding->standardIndicator?->statement ?? '' }}
                    </td>
                    <td>{{ $finding->root_cause ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="empty">Tidak ditemukan temuan ketidaksesuaian (KTS) maupun observasi (OB) pada audit ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- V. PELUANG UNTUK PENINGKATAN (OFI - OPPORTUNITY FOR IMPROVEMENT) -->
    <div class="section-title">V. Peluang Untuk Peningkatan (OFI - Opportunity For Improvement)</div>
    @if($ofiFindings->isNotEmpty())
        <div class="narrative-box">
            <ol>
                @foreach($ofiFindings as $ofi)
                    <li>
                        <strong>{{ $ofi->standardIndicator?->qualityStandard?->code ?? '' }} - {{ $ofi->standardIndicator?->code ?? '' }}</strong>:
                        {{ $ofi->description }}
                        @if($ofi->recommendation)
                            <br><span style="font-style: italic; color: #475569;">Rekomendasi: {{ $ofi->recommendation }}</span>
                        @endif
                    </li>
                @endforeach
            </ol>
        </div>
    @else
        <div class="narrative-box" style="font-style: italic; color: #64748b;">
            Tidak ada peluang peningkatan (OFI) yang diidentifikasi pada audit ini.
        </div>
    @endif

    <!-- VI. KESIMPULAN AUDIT -->
    <div class="section-title">VI. Kesimpulan Audit</div>
    <div class="narrative-box">
        {{ $kesimpulanUmumHasilAudit }}
    </div>

    <!-- Signature Section -->
    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td class="text-center">
                    <p>Ketua / Kepala Unit Kerja,</p>
                    <div class="signature-space"></div>
                    <p class="font-bold">( {{ $namaKetuaUnitKerja }} )</p>
                    <p style="font-size: 8px; color: #64748b;">{{ $jabatanKetuaUnitKerja }}</p>
                </td>
                <td class="text-center">
                    <p>Ketua Tim Auditor,</p>
                    <div class="signature-space"></div>
                    <p class="font-bold">( {{ $namaKetuaAuditor }} )</p>
                    <p style="font-size: 8px; color: #64748b;">Auditor Internal</p>
                </td>
            </tr>
        </table>
    </div>

    <!-- Footer Note -->
    <div class="footer-note">
        *Laporan ini dihasilkan secara otomatis oleh Sistem Penjaminan Mutu Internal (SPMI) {{ $namaUniversitas }} berdasarkan hasil verifikasi lapangan audit mutu internal terunggah.
    </div>

</body>
</html>
