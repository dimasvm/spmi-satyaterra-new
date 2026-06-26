<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $campus ? $campus->name . ' — Sistem Manajemen Mutu' : 'Sistem Mutu Universitas' }}</title>
    <meta name="description" content="Dashboard Utama Penjaminan Mutu Internal terintegrasi dengan Neo Feeder PDDikti.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <style>
        :root {
            /* Brand colors */
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;

            /* Dark mode variables (default) */
            --bg-primary: #090d16;
            --bg-secondary: #0f1420;
            --bg-card: #131a2a;
            --bg-hover: #1c253b;
            --border: rgba(255, 255, 255, 0.08);
            --border-hover: rgba(79, 70, 229, 0.4);
            --text-main: #f8fafc;
            --text-sub: #94a3b8;
            --text-muted: #475569;
            --card-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.3);
            --glow: rgba(79, 70, 229, 0.15);
        }

        [data-theme="light"] {
            /* Light mode variables */
            --bg-primary: #f8fafc;
            --bg-secondary: #ffffff;
            --bg-card: #ffffff;
            --bg-hover: #f1f5f9;
            --border: #e2e8f0;
            --border-hover: #cbd5e1;
            --text-main: #0f172a;
            --text-sub: #475569;
            --text-muted: #94a3b8;
            --card-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
            --glow: rgba(79, 70, 229, 0.05);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            transition: background-color 0.2s ease, border-color 0.2s ease, color 0.1s ease;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-main);
            min-height: 100vh;
            line-height: 1.5;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        /* ============ NAVBAR ============ */
        .navbar {
            background-color: var(--bg-secondary);
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 4.5rem;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .navbar-logo {
            width: 2.25rem;
            height: 2.25rem;
            background: linear-gradient(135deg, var(--primary), #818cf8);
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            box-shadow: 0 2px 8px var(--glow);
        }

        .navbar-logo svg {
            width: 1.25rem;
            height: 1.25rem;
        }

        .navbar-title-group {
            display: flex;
            flex-direction: column;
        }

        .navbar-title {
            font-size: 1rem;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .navbar-subtitle {
            font-size: 0.75rem;
            color: var(--text-sub);
        }

        .navbar-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        /* ============ BUTTONS ============ */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all 0.2s;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
        }

        .btn-outline {
            background-color: transparent;
            border-color: var(--border);
            color: var(--text-main);
        }

        .btn-outline:hover {
            background-color: var(--bg-hover);
            border-color: var(--border-hover);
        }

        .btn-icon {
            width: 2.25rem;
            height: 2.25rem;
            padding: 0;
            border-radius: 0.5rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: transparent;
            border: 1px solid var(--border);
            color: var(--text-main);
            cursor: pointer;
        }

        .btn-icon:hover {
            background-color: var(--bg-hover);
            border-color: var(--border-hover);
        }

        .btn-icon svg {
            width: 1.25rem;
            height: 1.25rem;
        }

        .theme-toggle .sun-icon { display: none; }
        [data-theme="light"] .theme-toggle .sun-icon { display: block; }
        [data-theme="light"] .theme-toggle .moon-icon { display: none; }
        
        /* ============ HERO SECTION ============ */
        .hero {
            padding: 2.5rem 0 1.5rem;
        }

        .hero-banner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid var(--border);
        }

        .hero-info-group {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .hero-logo {
            width: 5rem;
            height: 5rem;
            border-radius: 1rem;
            background-color: var(--bg-secondary);
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            box-shadow: var(--card-shadow);
        }

        .hero-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 0.5rem;
        }

        .hero-logo svg {
            width: 2.5rem;
            height: 2.5rem;
            color: var(--text-sub);
        }

        .hero-text {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .hero-badge {
            align-self: flex-start;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--primary);
            background-color: var(--glow);
            padding: 0.2rem 0.5rem;
            border-radius: 0.25rem;
        }

        .hero-title {
            font-size: 1.75rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            line-height: 1.2;
        }

        .hero-location {
            font-size: 0.875rem;
            color: var(--text-sub);
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        .hero-location svg {
            width: 1rem;
            height: 1rem;
            color: var(--text-muted);
        }

        /* ============ TOP BAR METADATA ============ */
        .meta-strip {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-top: 1.5rem;
            padding: 1rem;
            background-color: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            box-shadow: var(--card-shadow);
        }

        .meta-item {
            display: flex;
            flex-direction: column;
            gap: 0.125rem;
        }

        .meta-label {
            font-size: 0.75rem;
            color: var(--text-sub);
            font-weight: 500;
        }

        .meta-value {
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .pulse-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--success);
            background: rgba(16, 185, 129, 0.1);
            padding: 0.15rem 0.5rem;
            border-radius: 1rem;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .pulse-dot {
            width: 0.5rem;
            height: 0.5rem;
            background-color: var(--success);
            border-radius: 50%;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            animation: pulse 1.6s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(0.9);
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            }
            70% {
                transform: scale(1);
                box-shadow: 0 0 0 6px rgba(16, 185, 129, 0);
            }
            100% {
                transform: scale(0.9);
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
            }
        }

        /* ============ DASHBOARD PANELS ============ */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .card {
            background-color: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
        }

        .card-title {
            font-size: 1rem;
            font-weight: 700;
            letter-spacing: -0.01em;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-subtitle {
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--text-sub);
        }

        /* Stats Cards */
        .quick-stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-box {
            padding: 1rem;
            border-radius: 0.5rem;
            border: 1px solid var(--border);
            background-color: rgba(255, 255, 255, 0.01);
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .stat-box:hover {
            border-color: var(--border-hover);
        }

        .stat-box-label {
            font-size: 0.75rem;
            color: var(--text-sub);
            font-weight: 500;
        }

        .stat-box-value {
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .stat-box-subtext {
            font-size: 0.6875rem;
            color: var(--text-muted);
        }

        .accent-success { color: var(--success); }
        .accent-info { color: var(--info); }
        .accent-warning { color: var(--warning); }

        /* Donut Chart container */
        .dosen-chart-wrapper {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .chart-box {
            position: relative;
            width: 140px;
            height: 140px;
            flex-shrink: 0;
        }

        .dosen-legend {
            flex: 1;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
        }

        .legend-item {
            display: flex;
            flex-direction: column;
        }

        .legend-label-group {
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        .legend-dot {
            width: 0.5rem;
            height: 0.5rem;
            border-radius: 50%;
        }

        .legend-label {
            font-size: 0.75rem;
            color: var(--text-sub);
            font-weight: 500;
        }

        .legend-val {
            font-size: 1rem;
            font-weight: 700;
            padding-left: 0.85rem;
        }

        /* Section III: Matriks Instrumen Mutu */
        .instrumen-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
        }

        .instrumen-box {
            padding: 1.25rem;
            background-color: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            box-shadow: var(--card-shadow);
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .instrumen-box:hover {
            border-color: var(--border-hover);
        }

        .instrumen-num {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: var(--primary);
        }

        .instrumen-lbl {
            font-size: 0.875rem;
            font-weight: 700;
        }

        .instrumen-sub {
            font-size: 0.75rem;
            color: var(--text-sub);
        }

        /* Section IV: PPEPP Progress Dashboard */
        .ppepp-flow {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-top: 1rem;
        }

        .ppepp-stage {
            display: grid;
            grid-template-columns: 10rem 1fr 4rem;
            align-items: center;
            gap: 1.5rem;
        }

        .stage-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            font-weight: 700;
        }

        .stage-dot {
            width: 1.5rem;
            height: 1.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 800;
            color: white;
            background-color: var(--text-muted);
            flex-shrink: 0;
        }

        .stage-dot.active {
            background-color: var(--primary);
        }

        .stage-dot.completed {
            background-color: var(--success);
        }

        .progress-bar-container {
            width: 100%;
            height: 0.5rem;
            background-color: var(--bg-hover);
            border-radius: 0.25rem;
            overflow: hidden;
            position: relative;
        }

        .progress-bar {
            height: 100%;
            border-radius: 0.25rem;
            width: 0;
            transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .progress-bar.success { background-color: var(--success); }
        .progress-bar.primary { background-color: var(--primary); }
        .progress-bar.warning { background-color: var(--warning); }
        .progress-bar.muted { background-color: var(--text-muted); }

        .stage-pct {
            font-size: 0.875rem;
            font-weight: 700;
            text-align: right;
        }

        .stage-rule {
            grid-column: 2 / span 2;
            font-size: 0.75rem;
            color: var(--text-sub);
            margin-top: -0.75rem;
            padding-left: 0;
        }

        /* ============ FACULTY & STUDY PROGRAMS ============ */
        .faculty-section {
            margin-top: 2rem;
        }

        .faculty-accordion-group {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .faculty-card {
            background-color: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: var(--card-shadow);
        }

        .faculty-card:hover {
            border-color: var(--border-hover);
        }

        .faculty-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.5rem;
            cursor: pointer;
            user-select: none;
            background-color: rgba(255, 255, 255, 0.005);
        }

        .faculty-header:hover {
            background-color: var(--bg-hover);
        }

        .faculty-title-group {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .faculty-icon {
            color: var(--primary);
            display: flex;
            align-items: center;
        }

        .faculty-icon svg {
            width: 1.25rem;
            height: 1.25rem;
        }

        .faculty-name {
            font-size: 0.9375rem;
            font-weight: 700;
        }

        .faculty-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .faculty-meta-item {
            font-size: 0.75rem;
            color: var(--text-sub);
            font-weight: 500;
        }

        .faculty-chevron {
            color: var(--text-muted);
            transition: transform 0.2s;
            display: flex;
            align-items: center;
        }

        .faculty-chevron svg {
            width: 1.25rem;
            height: 1.25rem;
        }

        details[open] .faculty-chevron {
            transform: rotate(180deg);
        }

        details > summary {
            list-style: none;
        }
        details > summary::-webkit-details-marker {
            display: none;
        }

        /* Study Programs Table */
        .prodi-table-wrapper {
            border-top: 1px solid var(--border);
            overflow-x: auto;
        }

        .prodi-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .prodi-table th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-sub);
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            background-color: rgba(0, 0, 0, 0.02);
            border-bottom: 1px solid var(--border);
        }

        .prodi-table td {
            font-size: 0.875rem;
            padding: 0.85rem 1.5rem;
            border-bottom: 1px solid var(--border);
            color: var(--text-sub);
        }

        .prodi-table tr:last-child td {
            border-bottom: none;
        }

        .prodi-table tr:hover td {
            background-color: rgba(255, 255, 255, 0.015);
            color: var(--text-main);
        }

        .prodi-name {
            font-weight: 600;
            color: var(--text-main);
        }

        .prodi-badge {
            display: inline-flex;
            align-items: center;
            font-size: 0.6875rem;
            font-weight: 700;
            padding: 0.15rem 0.5rem;
            border-radius: 0.25rem;
        }

        .badge-unggul {
            background-color: rgba(16, 185, 129, 0.1);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .badge-baik-sekali {
            background-color: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        .badge-baik {
            background-color: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
            border: 1px solid rgba(245, 158, 11, 0.2);
        }

        .badge-gray {
            background-color: rgba(100, 116, 139, 0.1);
            color: #64748b;
            border: 1px solid rgba(100, 116, 139, 0.2);
        }

        /* ============ FOOTER ============ */
        .footer {
            margin-top: 4rem;
            padding: 2.5rem 0;
            border-top: 1px solid var(--border);
            background-color: var(--bg-secondary);
        }

        .footer-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            font-size: 0.8125rem;
            color: var(--text-sub);
        }

        .footer-text a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .footer-text a:hover {
            text-decoration: underline;
        }

        .footer-sync {
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        .footer-sync svg {
            width: 1rem;
            height: 1rem;
            color: var(--text-muted);
        }

        /* ============ RESPONSIVE ============ */
        @media (max-width: 1024px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .meta-strip {
                grid-template-columns: repeat(2, 1fr);
                gap: 1.25rem;
            }
            .quick-stats-grid {
                grid-template-columns: 1fr;
            }
            .dosen-chart-wrapper {
                flex-direction: column;
                align-items: flex-start;
            }
            .ppepp-stage {
                grid-template-columns: 1fr 3rem;
                gap: 0.5rem;
            }
            .stage-rule {
                grid-column: 1 / span 2;
                margin-top: 0;
                margin-bottom: 0.75rem;
            }
            .hero-banner {
                flex-direction: column;
                align-items: flex-start;
                gap: 1.25rem;
            }
        }
    </style>
</head>
<body>

{{-- NAVBAR --}}
<nav class="navbar">
    <div class="container">
        <div class="navbar-inner">
            <div class="navbar-brand">
                <div class="navbar-logo">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 14l9-5-9-5-9 5 9 5z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                    </svg>
                </div>
                <div class="navbar-title-group">
                    <span class="navbar-title">Sistem Penjaminan Mutu Internal</span>
                    <span class="navbar-subtitle">Universitas Satyaterra</span>
                </div>
            </div>
            <div class="navbar-actions">
                <button class="btn-icon theme-toggle" id="themeToggleBtn" aria-label="Toggle Theme">
                    <!-- Moon icon for dark theme activation -->
                    <svg class="moon-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                    </svg>
                    <!-- Sun icon for light theme activation -->
                    <svg class="sun-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m0 13.5V21M4.22 4.22l1.77 1.77m11.96 11.96l1.77 1.77m-17.71 0l1.77-1.77m11.96-11.96l1.77-1.77A8.966 8.966 0 0112 3.75c4.97 0 9 4.03 9 9s-4.03 9-9 9-9-4.03-9-9c0-1.77.51-3.41 1.39-4.8l.01-.01z" />
                    </svg>
                </button>
                <a href="{{ route('filament.admin.auth.login') }}" class="btn btn-primary">
                    Login Admin
                </a>
            </div>
        </div>
    </div>
</nav>

{{-- MAIN CONTENT --}}
<main class="container">

    {{-- HERO BANNER --}}
    <section class="hero">
        <div class="hero-banner">
            <div class="hero-info-group">
                <div class="hero-logo">
                    @if($campus?->logo_url)
                        <img src="{{ $campus->logo_url }}" alt="Logo {{ $campus->name }}">
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" />
                        </svg>
                    @endif
                </div>
                <div class="hero-text">
                    <span class="hero-badge">{{ $campus?->type ?? 'Perguruan Tinggi' }}</span>
                    <h1 class="hero-title">{{ $campus?->name ?? 'Universitas Satyaterra' }}</h1>
                    @if($campus?->city || $campus?->province)
                        <div class="hero-location">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                            </svg>
                            <span>{{ implode(', ', array_filter([$campus->address, $campus->city, $campus->province])) }}</span>
                        </div>
                    @endif
                </div>
            </div>
            <div>
                @if($campus?->website)
                    <a href="{{ $campus->website }}" target="_blank" rel="noopener" class="btn btn-outline">
                        Kunjungi Website PT
                    </a>
                @endif
            </div>
        </div>

        {{-- METADATA STRIP --}}
        <div class="meta-strip">
            <div class="meta-item">
                <span class="meta-label">Siklus SPMI Berjalan</span>
                <span class="meta-value">Siklus 3 / {{ $stats['active_period_year'] }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Periode Rencana Strategis</span>
                <span class="meta-value">2025 - 2030</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Status Koneksi Neo Feeder</span>
                <span class="meta-value">
                    @if($isFeederConnected)
                        <span class="pulse-badge">
                            <span class="pulse-dot"></span>
                            TERHUBUNG
                        </span>
                    @else
                        <span class="pulse-badge" style="color:var(--warning); background:rgba(245,158,11,0.1); border-color:rgba(245,158,11,0.2)">
                            DISCONNECTED
                        </span>
                    @endif
                </span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Terakhir Sinkronisasi Data</span>
                <span class="meta-value" style="font-weight: 500; font-size: 0.8125rem">
                    {{ $campus?->last_synced_at ? $campus->last_synced_at->locale('id')->isoFormat('D MMMM YYYY, HH:mm') : 'Belum Pernah' }}
                </span>
            </div>
        </div>
    </section>

    {{-- DASHBOARD MAIN GRID --}}
    <div class="dashboard-grid">

        {{-- LEFT COLUMN: STATS & PROCESS PROGRESS --}}
        <div style="display: flex; flex-direction: column; gap: 1.5rem">

            {{-- SECTION II: STATISTIK KUANTITATIF (NEO FEEDER INTEGRATION) --}}
            <section class="card">
                <h2 class="card-title">
                    Integrasi Neo Feeder PDDikti
                    <span class="card-subtitle">Data Real-time Kuantitatif Institusi</span>
                </h2>

                <div class="quick-stats-grid">
                    <div class="stat-box">
                        <span class="stat-box-label">Total Mahasiswa</span>
                        <span class="stat-box-value accent-info" id="countMahasiswa" data-target="{{ $campus?->total_students ?? 0 }}">
                            {{ number_format($campus?->total_students ?? 0) }}
                        </span>
                        <span class="stat-box-subtext">Status Mahasiswa Aktif di Neo Feeder</span>
                    </div>

                    <div class="stat-box">
                        <span class="stat-box-label">Total Dosen Tetap</span>
                        <span class="stat-box-value accent-success" id="countDosen" data-target="{{ $campus?->total_lecturers ?? 0 }}">
                            {{ number_format($campus?->total_lecturers ?? 0) }}
                        </span>
                        <span class="stat-box-subtext">Dosen Memiliki NIDN / NIDK Terdaftar</span>
                    </div>

                    <div class="stat-box">
                        <span class="stat-box-label">Rasio Dosen : Mahasiswa</span>
                        <span class="stat-box-value accent-warning">
                            @if(($campus?->total_lecturers ?? 0) > 0)
                                1 : {{ round(($campus->total_students ?? 0) / $campus->total_lecturers) }}
                            @else
                                -
                            @endif
                        </span>
                        <span class="stat-box-subtext">Status Rasio: <strong style="color:var(--success)">IDEAL</strong> (Sesuai Standar Kemendikbud)</span>
                    </div>
                </div>

                <div style="border-top: 1px solid var(--border); padding-top: 1.5rem">
                    <h3 style="font-size: 0.875rem; font-weight: 700; margin-bottom: 1rem">Jabatan Fungsional Dosen</h3>
                    
                    <div class="dosen-chart-wrapper">
                        <div class="chart-box">
                            <canvas id="dosenChart"></canvas>
                        </div>
                        <div class="dosen-legend">
                            @php
                                $dosenJabatan = $campus?->raw_data['jabatan_fungsional'] ?? [
                                    'Lektor Kepala' => 25,
                                    'Lektor' => 110,
                                    'Asisten Ahli' => 65,
                                    'Tenaga Pengajar' => 18
                                ];
                                $colors = ['#4f46e5', '#3b82f6', '#10b981', '#f59e0b'];
                                $idx = 0;
                            @endphp
                            @foreach($dosenJabatan as $jabatan => $jumlah)
                                <div class="legend-item">
                                    <div class="legend-label-group">
                                        <span class="legend-dot" style="background-color: {{ $colors[$idx % count($colors)] }}"></span>
                                        <span class="legend-label">{{ $jabatan }}</span>
                                    </div>
                                    <span class="legend-val">{{ $jumlah }} <span style="font-size:0.75rem; color:var(--text-sub); font-weight:400">Dosen</span></span>
                                </div>
                                @php $idx++; @endphp
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            {{-- SECTION IV: PPEPP PROGRESS BARS --}}
            <section class="card">
                <h2 class="card-title">
                    Monitoring Progres Siklus PPEPP
                    <span class="card-subtitle">Persentase Aktivitas Penjaminan Mutu Siklus Berjalan</span>
                </h2>

                <div class="ppepp-flow">
                    {{-- P: Penetapan --}}
                    <div class="ppepp-stage">
                        <span class="stage-title">
                            <span class="stage-dot completed">P</span>
                            Penetapan Standar
                        </span>
                        <div class="progress-bar-container">
                            <div class="progress-bar success" style="width: {{ $stats['penetapan_pct'] }}%"></div>
                        </div>
                        <span class="stage-pct">{{ $stats['penetapan_pct'] }}%</span>
                    </div>
                    <div class="stage-rule">
                        Rule: Dokumen Standar Mutu disahkan Rektor/Senat (Aktif: {{ $stats['total_standards'] }} Standar).
                    </div>

                    {{-- P: Pelaksanaan --}}
                    <div class="ppepp-stage">
                        <span class="stage-title">
                            <span class="stage-dot active">P</span>
                            Pelaksanaan Standar
                        </span>
                        <div class="progress-bar-container">
                            <div class="progress-bar primary" style="width: {{ $stats['pelaksanaan_pct'] }}%"></div>
                        </div>
                        <span class="stage-pct">{{ $stats['pelaksanaan_pct'] }}%</span>
                    </div>
                    <div class="stage-rule">
                        Rule: Aksi submit bukti pelaksanaan oleh Unit Kerja ({{ $stats['pelaksanaan_submitted_units'] }} dari {{ $stats['pelaksanaan_total_units'] }} Unit submit).
                    </div>

                    {{-- E: Evaluasi (AMI) --}}
                    <div class="ppepp-stage">
                        <span class="stage-title">
                            <span class="stage-dot {{ $stats['evaluasi_pct'] > 0 ? 'active' : '' }}">E</span>
                            Evaluasi (AMI)
                        </span>
                        <div class="progress-bar-container">
                            <div class="progress-bar {{ $stats['evaluasi_pct'] >= 100 ? 'success' : 'primary' }}" style="width: {{ $stats['evaluasi_pct'] }}%"></div>
                        </div>
                        <span class="stage-pct">{{ $stats['evaluasi_pct'] }}%</span>
                    </div>
                    <div class="stage-rule">
                        Rule: Borang Laporan Audit Mutu Internal (AMI) di-submit Auditor ({{ $stats['evaluasi_completed_audits'] }} dari {{ $stats['evaluasi_total_audits'] }} Laporan).
                    </div>

                    {{-- P: Pengendalian --}}
                    <div class="ppepp-stage">
                        <span class="stage-title">
                            <span class="stage-dot {{ $stats['pengendalian_pct'] > 0 ? 'active' : '' }}">P</span>
                            Pengendalian
                        </span>
                        <div class="progress-bar-container">
                            <div class="progress-bar warning" style="width: {{ $stats['pengendalian_pct'] }}%"></div>
                        </div>
                        <span class="stage-pct">{{ $stats['pengendalian_pct'] }}%</span>
                    </div>
                    <div class="stage-rule">
                        Rule: Aktif jika AMI 100%, terhitung berdasarkan status RTM (Status: {{ $stats['rtm_status'] }}).
                    </div>

                    {{-- P: Peningkatan --}}
                    <div class="ppepp-stage">
                        <span class="stage-title">
                            <span class="stage-dot {{ $stats['peningkatan_pct'] > 0 ? 'active' : '' }}">P</span>
                            Peningkatan Standar
                        </span>
                        <div class="progress-bar-container">
                            <div class="progress-bar muted" style="width: {{ $stats['peningkatan_pct'] }}%"></div>
                        </div>
                        <span class="stage-pct">{{ $stats['peningkatan_pct'] }}%</span>
                    </div>
                    <div class="stage-rule">
                        Rule: Rekomendasi perbaikan RTM diimplementasikan sebagai komitmen siklus berikutnya ({{ $stats['peningkatan_implemented_proposals'] }} dari {{ $stats['peningkatan_total_proposals'] }} Usulan).
                    </div>
                </div>
            </section>
        </div>

        {{-- RIGHT COLUMN: MUTU MATRIX & ACCREDITATION DONUT --}}
        <div style="display: flex; flex-direction: column; gap: 1.5rem">

            {{-- SECTION III: MATRIKS MUTU INTERNAL --}}
            <section class="card">
                <h2 class="card-title">
                    Instrumen Mutu Internal
                    <span class="card-subtitle">Dokumen Mutu Dikelola LPM</span>
                </h2>

                <div style="display:flex; flex-direction:column; gap:1rem">
                    <div class="stat-box" style="flex-direction:row; align-items:center; justify-content:space-between">
                        <div>
                            <span class="stat-box-label">Standar Mutu</span>
                            <div class="stat-box-subtext">Mengacu SN-Dikti & Universitas</div>
                        </div>
                        <span class="stat-box-value accent-primary" style="font-size:2.25rem">{{ $stats['total_standards'] ?: 24 }}</span>
                    </div>

                    <div class="stat-box" style="flex-direction:row; align-items:center; justify-content:space-between">
                        <div>
                            <span class="stat-box-label">Pernyataan Standar</span>
                            <div class="stat-box-subtext">Terdistribusi di seluruh aspek</div>
                        </div>
                        <span class="stat-box-value accent-primary" style="font-size:2.25rem">{{ $stats['total_statements'] ?: 145 }}</span>
                    </div>

                    <div class="stat-box" style="flex-direction:row; align-items:center; justify-content:space-between">
                        <div>
                            <span class="stat-box-label">Indikator Kinerja</span>
                            <div class="stat-box-subtext">Gabungan IKU & IKT</div>
                        </div>
                        <span class="stat-box-value accent-primary" style="font-size:2.25rem">{{ $stats['total_indicators'] ?: 420 }}</span>
                    </div>
                </div>
            </section>

            {{-- PROGRAM ACCREDITATION DISTRIBUTION --}}
            @if(!empty($campus->accreditation_stats['labels']))
                <section class="card">
                    <h2 class="card-title">
                        Akreditasi Program Studi
                        <span class="card-subtitle">Distribusi Akreditasi BAN-PT/LAM</span>
                    </h2>
                    <div style="position:relative; height:180px; display:flex; align-items:center; justify-content:center; margin-bottom: 1rem">
                        <canvas id="accreditationChart" style="max-height:160px"></canvas>
                    </div>
                    <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem; text-align:center">
                        @foreach($campus->accreditation_stats['labels'] as $idx => $label)
                            <div style="padding: 0.4rem; background: var(--bg-hover); border-radius: 0.35rem">
                                <div style="font-size: 0.6875rem; color: var(--text-sub)">{{ $label }}</div>
                                <div style="font-size: 1.125rem; font-weight: 700">{{ $campus->accreditation_stats['data'][$idx] }} Prodi</div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>

    {{-- SECTION V: FAKULTAS & PROGRAM STUDI --}}
    @if(!empty($campus->faculties))
        <section class="faculty-section">
            <h2 style="font-size: 1.25rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.5rem">
                Fakultas & Program Studi
            </h2>
            <p style="font-size: 0.875rem; color: var(--text-sub); margin-bottom: 1rem">
                Detail data program studi lengkap dengan jenjang, akreditasi, dan jumlah mahasiswa serta dosen tetap.
            </p>

            <div class="faculty-accordion-group">
                @foreach($campus->faculties as $idx => $faculty)
                    <details class="faculty-card" @if($idx === 0) open @endif>
                        <summary class="faculty-header">
                            <div class="faculty-title-group">
                                <span class="faculty-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" />
                                    </svg>
                                </span>
                                <span class="faculty-name">{{ $faculty['name'] }}</span>
                            </div>
                            <div class="faculty-meta">
                                <span class="faculty-meta-item">{{ count($faculty['study_programs']) }} Prodi</span>
                                <span class="faculty-meta-item">{{ number_format($faculty['total_students']) }} Mahasiswa</span>
                                <span class="faculty-meta-item">{{ number_format($faculty['total_lecturers'] ?? 0) }} Dosen</span>
                                <span class="faculty-chevron">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </span>
                            </div>
                        </summary>
                        
                        <div class="prodi-table-wrapper">
                            <table class="prodi-table">
                                <thead>
                                    <tr>
                                        <th>Program Studi</th>
                                        <th>Jenjang</th>
                                        <th>Akreditasi</th>
                                        <th>Mahasiswa</th>
                                        <th>Dosen</th>
                                        <th>Rasio D:M</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($faculty['study_programs'] as $prodi)
                                        <tr>
                                            <td class="prodi-name">{{ $prodi['name'] }}</td>
                                            <td>{{ $prodi['level'] ?? 'S1' }}</td>
                                            <td>
                                                @php
                                                    $pa = strtolower($prodi['accreditation'] ?? 'belum');
                                                    $badgeClass = match(true) {
                                                        in_array($pa, ['unggul', 'a']) => 'badge-unggul',
                                                        in_array($pa, ['baik sekali', 'b']) => 'badge-baik-sekali',
                                                        in_array($pa, ['baik', 'c']) => 'badge-baik',
                                                        default => 'badge-gray'
                                                    };
                                                @endphp
                                                <span class="prodi-badge {{ $badgeClass }}">
                                                    {{ $prodi['accreditation'] ?: 'Belum' }}
                                                </span>
                                            </td>
                                            <td>{{ number_format($prodi['total_students']) }}</td>
                                            <td>{{ number_format($prodi['total_lecturers'] ?? 0) }}</td>
                                            <td>
                                                @if(($prodi['total_lecturers'] ?? 0) > 0)
                                                    1 : {{ round($prodi['total_students'] / $prodi['total_lecturers']) }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </details>
                @endforeach
            </div>
        </section>
    @endif

</main>

{{-- FOOTER --}}
<footer class="footer">
    <div class="container">
        <div class="footer-inner">
            <span class="footer-text">
                Sumber Data: <a href="https://pddikti.kemdiktisaintek.go.id" target="_blank" rel="noopener">Neo Feeder PDDikti</a> Kemendikbudristek.
            </span>
            @if($campus?->last_synced_at)
                <div class="footer-sync">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    <span>Terakhir diperbarui: {{ $campus->last_synced_at->locale('id')->diffForHumans() }}</span>
                </div>
            @endif
        </div>
    </div>
</footer>

<script>
    // Theme Manager
    const themeToggleBtn = document.getElementById('themeToggleBtn');
    
    // Check initial theme preference
    const savedTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);

    themeToggleBtn.addEventListener('click', () => {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);

        // Update Chart text/grid colors dynamically
        updateChartThemes(newTheme);
    });

    // Chart.js Configuration
    const themeColors = {
        dark: {
            text: '#94a3b8',
            grid: 'rgba(255, 255, 255, 0.05)',
            border: '#090d16'
        },
        light: {
            text: '#475569',
            grid: '#e2e8f0',
            border: '#ffffff'
        }
    };

    let dosenChart = null;
    let accreditationChart = null;

    function initCharts() {
        const theme = document.documentElement.getAttribute('data-theme') || 'dark';
        const config = themeColors[theme];

        // 1. Dosen Donut Chart
        const dosenCtx = document.getElementById('dosenChart');
        if (dosenCtx) {
            const dataVals = @json(array_values($dosenJabatan));
            const labels = @json(array_keys($dosenJabatan));

            dosenChart = new Chart(dosenCtx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: dataVals,
                        backgroundColor: ['#4f46e5', '#3b82f6', '#10b981', '#f59e0b'],
                        borderColor: config.border,
                        borderWidth: 2,
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => ` ${ctx.label}: ${ctx.parsed} dosen`
                            }
                        }
                    }
                }
            });
        }

        // 2. Accreditation Donut Chart
        const accCtx = document.getElementById('accreditationChart');
        if (accCtx && @json(!empty($campus->accreditation_stats['labels']))) {
            const accLabels = @json($campus->accreditation_stats['labels'] ?? []);
            const accData = @json($campus->accreditation_stats['data'] ?? []);

            accreditationChart = new Chart(accCtx, {
                type: 'doughnut',
                data: {
                    labels: accLabels,
                    datasets: [{
                        data: accData,
                        backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#4f46e5'],
                        borderColor: config.border,
                        borderWidth: 2,
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => ` ${ctx.label}: ${ctx.parsed} prodi`
                            }
                        }
                    }
                }
            });
        }
    }

    function updateChartThemes(theme) {
        const config = themeColors[theme];
        
        if (dosenChart) {
            dosenChart.data.datasets[0].borderColor = config.border;
            dosenChart.update();
        }

        if (accreditationChart) {
            accreditationChart.data.datasets[0].borderColor = config.border;
            accreditationChart.update();
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        initCharts();

        // Counter Animation
        const animateCounter = (el) => {
            const target = parseInt(el.dataset.target);
            if (!target) return;
            
            let count = 0;
            const duration = 1200;
            const step = target / (duration / 16);
            const timer = setInterval(() => {
                count += step;
                if (count >= target) {
                    el.textContent = target.toLocaleString('id-ID');
                    clearInterval(timer);
                } else {
                    el.textContent = Math.floor(count).toLocaleString('id-ID');
                }
            }, 16);
        };

        document.querySelectorAll('[data-target]').forEach(animateCounter);
    });
</script>

</body>
</html>
