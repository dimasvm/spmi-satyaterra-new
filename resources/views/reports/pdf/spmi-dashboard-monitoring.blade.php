<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Monitoring Progres Pelaksanaan Standar</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            color: #1e293b;
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            line-height: 1.5;
            margin: 20px;
        }

        .header {
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 24px;
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
            font-size: 16px;
            font-weight: 700;
            margin: 0;
            letter-spacing: 0.5px;
        }

        .logo-title p {
            color: #64748b;
            font-size: 9px;
            margin: 2px 0 0 0;
        }

        .doc-title {
            text-align: right;
        }

        .doc-title h1 {
            color: #0f172a;
            font-size: 14px;
            font-weight: 800;
            margin: 0;
        }

        .doc-title h3 {
            color: #475569;
            font-size: 10px;
            font-weight: 600;
            margin: 4px 0 0 0;
        }

        .section-title {
            color: #1e3a8a;
            font-size: 11px;
            font-weight: 700;
            margin: 20px 0 8px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Tables styling */
        table.data-table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 16px;
        }

        table.data-table th,
        table.data-table td {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            text-align: left;
            vertical-align: top;
        }

        table.data-table th {
            background-color: #f1f5f9;
            color: #1e293b;
            font-weight: 700;
            font-size: 9px;
            text-transform: uppercase;
        }

        /* Metadata table */
        table.meta-table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 16px;
        }

        table.meta-table td {
            border: 1px solid #e2e8f0;
            padding: 6px 12px;
            vertical-align: middle;
        }

        table.meta-table td.label {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 600;
            width: 30%;
        }

        table.meta-table td.value {
            color: #0f172a;
            font-weight: 500;
        }

        /* KPI / Summary cards table */
        table.kpi-table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 16px;
        }

        table.kpi-table th {
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
            color: #475569;
            font-size: 9px;
            font-weight: 600;
            padding: 6px;
            text-align: center;
            text-transform: uppercase;
        }

        table.kpi-table td {
            border: 1px solid #cbd5e1;
            padding: 12px 6px;
            text-align: center;
            font-size: 16px;
            font-weight: 700;
        }

        .text-primary { color: #1e3a8a; }
        .text-success { color: #10b981; }
        .text-warning { color: #f59e0b; }
        .text-danger { color: #ef4444; }

        /* Badge elements */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            text-align: center;
        }

        .badge-submit {
            background-color: #d1fae5;
            color: #065f46;
        }

        .badge-draft {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge-belum {
            background-color: #fee2e2;
            color: #991b1b;
        }

        /* Footer note */
        .footer-note {
            color: #64748b;
            font-size: 8px;
            font-style: italic;
            margin-top: 24px;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
        }

        .zebra tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .empty {
            color: #64748b;
            text-align: center;
            padding: 20px;
            font-style: italic;
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
                        <h2>SATYATERRA</h2>
                        <p>Sistem Penjaminan Mutu Internal (SPMI)</p>
                    </div>
                </td>
                <td>
                    <div class="doc-title">
                        <h1>LAPORAN MONITORING</h1>
                        <h3>PROGRES PELAKSANAAN STANDAR</h3>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- I. METADATA MONITORING SYSTEM -->
    <div class="section-title">I. Metadata Monitoring System</div>
    <table class="meta-table">
        <tr>
            <td class="label">Periode / Tahun Akademik</td>
            <td class="value">{{ $periodName }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal Export Laporan</td>
            <td class="value">{{ $exportDate }}</td>
        </tr>
        <tr>
            <td class="label">Waktu Sinkronisasi Data</td>
            <td class="value">{{ $syncTime }}</td>
        </tr>
        <tr>
            <td class="label">PIC Monitoring LPM</td>
            <td class="value">{{ $picName }}</td>
        </tr>
    </table>

    <!-- II. RINGKASAN EKSEKUTIF (DASHBOARD SUMMARY) -->
    <div class="section-title">II. Ringkasan Eksekutif (Dashboard Summary)</div>
    <table class="kpi-table">
        <thead>
            <tr>
                <th>Total Unit Kerja</th>
                <th>Total Standar Diisi</th>
                <th class="text-success">Status: SUBMIT</th>
                <th class="text-warning">Status: DRAFT</th>
                <th class="text-danger">Belum Mengisi</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-primary">{{ $totalUnits }}</td>
                <td>{{ $totalStandards }}</td>
                <td class="text-success">{{ $statusSubmit }}</td>
                <td class="text-warning">{{ $statusDraft }}</td>
                <td class="text-danger">{{ $statusBelum }}</td>
            </tr>
        </tbody>
    </table>

    <!-- III. RINCIAN PROGRES PER UNIT KERJA & STANDAR MUTU -->
    <div class="section-title">III. Rincian Progres Per Unit Kerja & Standar Mutu</div>
    <table class="data-table zebra">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No.</th>
                <th style="width: 25%;">Unit Kerja / Fakultas / Prodi</th>
                <th style="width: 30%;">Nama Standar Mutu</th>
                <th style="width: 15%; text-align: center;">Status Pengisian</th>
                <th style="width: 10%; text-align: center;">Progres Bukti (Rasio)</th>
                <th style="width: 15%;">Catatan / Keterangan PIC</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $index => $row)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $row['unit_kerja'] }}</td>
                    <td>{{ $row['nama_standar'] }}</td>
                    <td style="text-align: center;">
                        @if ($row['status_pengisian'] === 'SUBMIT')
                            <span class="badge badge-submit">SUBMIT</span>
                        @elseif ($row['status_pengisian'] === 'DRAFT')
                            <span class="badge badge-draft">DRAFT</span>
                        @else
                            <span class="badge badge-belum">BELUM MENGISI</span>
                        @endif
                    </td>
                    <td style="text-align: center; font-weight: bold;">{{ $row['rasio_bukti'] }}</td>
                    <td>{{ $row['catatan'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="empty">Tidak ada data progres monitoring pelaksanaan standar pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Footer Note -->
    <div class="footer-note">
        *Laporan ini dihasilkan secara otomatis oleh Sistem Manajemen Mutu Internal berdasarkan entri data terupdate dari masing-masing Unit Kerja.
    </div>

</body>
</html>
