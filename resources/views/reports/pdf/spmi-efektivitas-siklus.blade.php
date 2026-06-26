<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Efektivitas Siklus PPEPP</title>
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
            vertical-align: middle;
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
            vertical-align: middle;
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

        .bg-gray-light {
            background-color: #f8fafc;
        }

        .text-primary { color: #1e3a8a; }
        .text-success { color: #10b981; }
        .text-warning { color: #f59e0b; }
        .text-danger { color: #ef4444; }

        /* Badge elements */
        .badge {
            display: inline-block;
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: 700;
            text-transform: uppercase;
            text-align: center;
        }

        .badge-success {
            background-color: #d1fae5;
            color: #065f46;
        }

        .badge-warning {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }

        /* Signature Section */
        .signature-section {
            margin-top: 25px;
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
            height: 50px;
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
    </style>
</head>
<body>

    <!-- Header Section -->
    <div class="header">
        <table>
            <tr>
                <td>
                    <div class="logo-title">
                        <h2>{{ strtoupper($namaUniversitas) }}</h2>
                        <p>Lembaga Penjaminan Mutu (LPM)</p>
                    </div>
                </td>
                <td>
                    <div class="doc-title">
                        <h1>LAPORAN EFEKTIVITAS SIKLUS PPEPP (TAHUNAN)</h1>
                        <h3>TAHUN AKADEMIK: {{ $tahunAkademik }} / SIKLUS: {{ $nomorSiklus }}</h3>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- I. METADATA LAPORAN -->
    <div class="section-title">I. Metadata Laporan</div>
    <table class="meta-table">
        <tr>
            <td class="label">Tanggal Pembuatan</td>
            <td class="value">{{ $tanggalDihasilkanSistem }}</td>
        </tr>
        <tr>
            <td class="label">Otorisator Pengesahan</td>
            <td class="value">{{ $namaKetuaLpm }} (Ketua Lembaga Penjaminan Mutu)</td>
        </tr>
        <tr>
            <td class="label">Status Siklus</td>
            <td class="value font-bold text-success">{{ $statusSiklus }}</td>
        </tr>
    </table>

    <!-- II. MATRIKS AGREGASI KUANTITATIF EFEKTIVITAS SIKLUS PPEPP -->
    <div class="section-title">II. Matriks Agregasi Kuantitatif Efektivitas Siklus PPEPP</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 15%;">Tahapan PPEPP</th>
                <th style="width: 45%;">Indikator Kuantitatif Aktivitas Mutu</th>
                <th style="width: 13%;" class="text-center">Target / Total</th>
                <th style="width: 13%;" class="text-center">Realisasi di Sistem</th>
                <th style="width: 14%;" class="text-center">Capaian Efektivitas</th>
            </tr>
        </thead>
        <tbody>
            <!-- P - Penetapan -->
            <tr>
                <td rowspan="3" class="font-bold bg-gray-light text-center text-primary">P - Penetapan</td>
                <td>Jumlah Standar Mutu Berlaku</td>
                <td class="text-center">{{ $tarStandar }}</td>
                <td class="text-center">{{ $realStandar }}</td>
                <td class="text-center font-bold">{{ $pctP1_1 }}%</td>
            </tr>
            <tr>
                <td>Jumlah Pernyataan Standar</td>
                <td class="text-center">{{ $tarPernyataan }}</td>
                <td class="text-center">{{ $realPernyataan }}</td>
                <td class="text-center font-bold">{{ $pctP1_2 }}%</td>
            </tr>
            <tr>
                <td>Jumlah Indikator Kinerja (IKU/IKT)</td>
                <td class="text-center">{{ $tarIndikator }}</td>
                <td class="text-center">{{ $realIndikator }}</td>
                <td class="text-center font-bold">{{ $pctP1_3 }}%</td>
            </tr>
            <!-- P - Pelaksanaan -->
            <tr>
                <td rowspan="2" class="font-bold bg-gray-light text-center text-primary">P - Pelaksanaan</td>
                <td>Jumlah Unit Kerja yang Mengisi</td>
                <td class="text-center">{{ $totalUnit }}</td>
                <td class="text-center">{{ $unitSubmit }}</td>
                <td class="text-center font-bold">{{ $pctP2_1 }}%</td>
            </tr>
            <tr>
                <td>Rasio Bukti Fisik Terunggah (Submitted)</td>
                <td class="text-center">{{ $totalReqBukti }}</td>
                <td class="text-center">{{ $realBuktiUp }}</td>
                <td class="text-center font-bold">{{ $pctP2_2 }}%</td>
            </tr>
            <!-- E - Evaluasi -->
            <tr>
                <td rowspan="3" class="font-bold bg-gray-light text-center text-primary">E - Evaluasi</td>
                <td>Unit Kerja Selesai Diaudit AMI</td>
                <td class="text-center">{{ $totalUnit }}</td>
                <td class="text-center">{{ $unitSelesaiAmi }}</td>
                <td class="text-center font-bold">{{ $pctE_1 }}%</td>
            </tr>
            <tr>
                <td>Jumlah Temuan Ketidaksesuaian (KTS)</td>
                <td class="text-center">-</td>
                <td class="text-center text-danger">{{ $totalKts }}</td>
                <td rowspan="2" class="text-center font-bold text-danger bg-gray-light" style="vertical-align: middle;">
                    Total Temuan:<br>{{ $sumTemuan }}
                </td>
            </tr>
            <tr>
                <td>Jumlah Temuan Observasi (OB)</td>
                <td class="text-center">-</td>
                <td class="text-center text-warning">{{ $totalOb }}</td>
            </tr>
            <!-- P - Pengendalian -->
            <tr>
                <td rowspan="2" class="font-bold bg-gray-light text-center text-primary">P - Pengendalian</td>
                <td>Temuan AMI yang Ditindaklanjuti di RTM</td>
                <td class="text-center">{{ $sumTemuan }}</td>
                <td class="text-center">{{ $temuanClosed }}</td>
                <td class="text-center font-bold text-success">{{ $pctP3_1 }}% (Efektivitas)</td>
            </tr>
            <tr>
                <td>Rekomendasi RTM yang Terlaksana</td>
                <td class="text-center">{{ $totalRekomendasi }}</td>
                <td class="text-center">{{ $rekomDone }}</td>
                <td class="text-center font-bold">{{ $pctP3_2 }}%</td>
            </tr>
            <!-- P - Peningkatan -->
            <tr>
                <td class="font-bold bg-gray-light text-center text-primary">P - Peningkatan</td>
                <td>Jumlah Standar yang Ditingkatkan (Upgrade)</td>
                <td class="text-center">{{ $rencanaUpgrade }}</td>
                <td class="text-center">{{ $realUpgrade }}</td>
                <td class="text-center font-bold">{{ $pctP4_1 }}%</td>
            </tr>
        </tbody>
    </table>

    <div style="page-break-before: always;"></div>

    <!-- III. RINCIAN BREAKDOWN CAPAIAN PER STANDAR MUTU -->
    <div class="section-title">III. Rincian Breakdown Capaian Per Standar Mutu</div>
    <table class="data-table zebra">
        <thead>
            <tr>
                <th style="width: 4%;" class="text-center">No.</th>
                <th style="width: 32%;">Kode & Nama Standar Mutu</th>
                <th style="width: 14%;">Sub Standar</th>
                <th style="width: 8%;" class="text-center">Jumlah Pernyataan</th>
                <th style="width: 8%;" class="text-center">Jumlah Indikator</th>
                <th style="width: 12%;" class="text-center">Unit Kerja Patuh (Submit)</th>
                <th style="width: 6%;" class="text-center">Temuan KTS</th>
                <th style="width: 6%;" class="text-center">Temuan OB</th>
                <th style="width: 10%;" class="text-center">Status Tindak Lanjut (RTM)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($standards as $index => $std)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-bold">{{ $std['kode_nama'] }}</td>
                    <td>{{ $std['sub_standar'] }}</td>
                    <td class="text-center">{{ $std['pernyataan_count'] }}</td>
                    <td class="text-center">{{ $std['indikator_count'] }}</td>
                    <td class="text-center">{{ $std['submitted_units_ratio'] }}</td>
                    <td class="text-center text-danger">{{ $std['kts_count'] }}</td>
                    <td class="text-center text-warning">{{ $std['ob_count'] }}</td>
                    <td class="text-center font-bold {{ $std['rtm_completion'] >= 80 ? 'text-success' : ($std['rtm_completion'] >= 40 ? 'text-warning' : 'text-danger') }}">
                        [ {{ $std['rtm_completion'] }}% Selesai ]
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="empty">Tidak ada data standar mutu pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- IV. KESIMPULAN & REKOMENDASI TAHUNAN -->
    <div class="section-title">IV. Kesimpulan & Rekomendasi Tahunan</div>
    <div class="narrative-box">
        {{ $narasiKesimpulanOtomatisLpm }}
    </div>

    <!-- Signature area -->
    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td></td>
                <td class="text-center">
                    <p>Mengesahkan,</p>
                    <p class="font-bold">Ketua Lembaga Penjaminan Mutu (LPM)</p>
                    <div class="signature-space"></div>
                    <p class="font-bold" style="text-decoration: underline;">{{ $namaKetuaLpm }}</p>
                    <p>Universitas {{ $namaUniversitas }}</p>
                </td>
            </tr>
        </table>
    </div>

    <!-- Footer Note -->
    <div class="footer-note">
        *Laporan ini dihasilkan secara otomatis oleh Sistem Penjaminan Mutu Internal (SPMI) {{ $namaUniversitas }} berdasarkan akumulasi data PPEPP tahunan terverifikasi pada tanggal {{ $tanggalDihasilkanSistem }}.
    </div>

</body>
</html>
