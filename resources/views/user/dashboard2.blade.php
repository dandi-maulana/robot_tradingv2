<!DOCTYPE html>
<html lang="id" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RODIS - Pattern Scanner</title>
    <meta name="description" content="RODIS Pattern Scanner - Monitor pola candle C1-C5 secara real-time">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                }
            }
        }

        var API_BASE = window.location.hostname === "127.0.0.1" ?
            window.location.protocol + "//" + window.location.hostname + ":5000/api" :
            "/api";
    </script>

    <style>
        * {
            box-sizing: border-box;
        }

        /* ===== THEME VARIABLES ===== */
        :root {
            --transition-speed: 0.35s;
        }

        [data-theme="dark"] {
            --bg-main: #0b0e1a;
            --bg-card: rgba(255, 255, 255, 0.03);
            --bg-card-hover: rgba(255, 255, 255, 0.06);
            --bg-nav: rgba(11, 14, 26, 0.88);
            --border-card: rgba(255, 255, 255, 0.06);
            --border-subtle: rgba(255, 255, 255, 0.04);
            --text-primary: #f1f5f9;
            --text-secondary: rgba(255, 255, 255, 0.55);
            --text-muted: rgba(255, 255, 255, 0.25);
            --text-ghost: rgba(255, 255, 255, 0.12);
            --green-accent: #34d399;
            --green-bg: rgba(16, 185, 129, 0.1);
            --green-border: rgba(16, 185, 129, 0.18);
            --green-glow: rgba(16, 185, 129, 0.15);
            --red-accent: #fca5a5;
            --red-bg: rgba(239, 68, 68, 0.1);
            --red-border: rgba(239, 68, 68, 0.18);
            --red-glow: rgba(239, 68, 68, 0.15);
            --badge-green-bg: rgba(16, 185, 129, 0.12);
            --badge-green-text: #6ee7b7;
            --badge-green-border: rgba(16, 185, 129, 0.22);
            --badge-red-bg: rgba(239, 68, 68, 0.12);
            --badge-red-text: #fca5a5;
            --badge-red-border: rgba(239, 68, 68, 0.22);
            --badge-empty-bg: rgba(255, 255, 255, 0.04);
            --badge-empty-text: rgba(255, 255, 255, 0.2);
            --badge-empty-border: rgba(255, 255, 255, 0.06);
            --input-bg: rgba(255, 255, 255, 0.05);
            --input-border: rgba(255, 255, 255, 0.08);
            --input-focus: rgba(16, 185, 129, 0.3);
            --stat-indigo: #818cf8;
            --stat-green: #34d399;
            --stat-red: #f87171;
            --stat-amber: #fbbf24;
            --scrollbar-thumb: rgba(255, 255, 255, 0.08);
        }

        [data-theme="light"] {
            --bg-main: #f0f2f5;
            --bg-card: #ffffff;
            --bg-card-hover: #f8fafc;
            --bg-nav: rgba(255, 255, 255, 0.92);
            --border-card: #e2e8f0;
            --border-subtle: #f1f5f9;
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --text-muted: #94a3b8;
            --text-ghost: #cbd5e1;
            --green-accent: #059669;
            --green-bg: #ecfdf5;
            --green-border: #a7f3d0;
            --green-glow: rgba(16, 185, 129, 0.08);
            --red-accent: #dc2626;
            --red-bg: #fef2f2;
            --red-border: #fecaca;
            --red-glow: rgba(239, 68, 68, 0.08);
            --badge-green-bg: #d1fae5;
            --badge-green-text: #065f46;
            --badge-green-border: #a7f3d0;
            --badge-red-bg: #fee2e2;
            --badge-red-text: #991b1b;
            --badge-red-border: #fca5a5;
            --badge-empty-bg: #f1f5f9;
            --badge-empty-text: #94a3b8;
            --badge-empty-border: #e2e8f0;
            --input-bg: #ffffff;
            --input-border: #e2e8f0;
            --input-focus: #10b981;
            --stat-indigo: #6366f1;
            --stat-green: #10b981;
            --stat-red: #ef4444;
            --stat-amber: #f59e0b;
            --scrollbar-thumb: #cbd5e1;
        }

        body {
            background: var(--bg-main);
            color: var(--text-primary);
            transition: background var(--transition-speed), color var(--transition-speed);
        }

        /* ===== SCROLLBAR ===== */
        ::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--scrollbar-thumb);
            border-radius: 10px;
        }

        /* ===== ANIMATIONS ===== */
        .fade-in {
            animation: fadeIn .3s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(6px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes shimmer {
            0% {
                background-position: -200% 0;
            }

            100% {
                background-position: 200% 0;
            }
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-3px);
            }
        }

        /* ===== GLASS CARD ===== */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            transition: background var(--transition-speed), border-color var(--transition-speed), box-shadow 0.2s;
        }

        .card-subtle {
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            transition: background var(--transition-speed), border-color var(--transition-speed);
        }

        /* ===== NAV ===== */
        .nav-bar {
            background: var(--bg-nav);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-card);
            transition: background var(--transition-speed), border-color var(--transition-speed);
        }

        /* ===== BADGES ===== */
        .badge-hijau {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            background: var(--badge-green-bg);
            color: var(--badge-green-text);
            border: 1px solid var(--badge-green-border);
            white-space: nowrap;
            transition: background var(--transition-speed), color var(--transition-speed), border-color var(--transition-speed);
        }

        .badge-merah {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            background: var(--badge-red-bg);
            color: var(--badge-red-text);
            border: 1px solid var(--badge-red-border);
            white-space: nowrap;
            transition: background var(--transition-speed), color var(--transition-speed), border-color var(--transition-speed);
        }

        .badge-empty {
            display: inline-flex;
            align-items: center;
            padding: 3px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            background: var(--badge-empty-bg);
            color: var(--badge-empty-text);
            border: 1px solid var(--badge-empty-border);
            transition: background var(--transition-speed), color var(--transition-speed), border-color var(--transition-speed);
        }

        /* ===== PILLS ===== */
        .pill-up {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.3px;
            background: var(--green-bg);
            color: var(--green-accent);
            border: 1px solid var(--green-border);
            box-shadow: 0 0 12px var(--green-glow);
            animation: pulseUp 2.5s ease-in-out infinite;
            transition: background var(--transition-speed), color var(--transition-speed), border-color var(--transition-speed);
        }

        .pill-down {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.3px;
            background: var(--red-bg);
            color: var(--red-accent);
            border: 1px solid var(--red-border);
            box-shadow: 0 0 12px var(--red-glow);
            animation: pulseDown 2.5s ease-in-out infinite;
            transition: background var(--transition-speed), color var(--transition-speed), border-color var(--transition-speed);
        }

        .pill-none {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            color: var(--text-ghost);
        }

        @keyframes pulseUp {

            0%,
            100% {
                box-shadow: 0 0 6px var(--green-glow);
            }

            50% {
                box-shadow: 0 0 22px var(--green-glow);
            }
        }

        @keyframes pulseDown {

            0%,
            100% {
                box-shadow: 0 0 6px var(--red-glow);
            }

            50% {
                box-shadow: 0 0 22px var(--red-glow);
            }
        }

        /* ===== LIVE DOTS ===== */
        .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }

        .dot-green {
            background: #10b981;
            animation: dotG 2s ease-in-out infinite;
        }

        .dot-red {
            background: #ef4444;
            animation: dotR 1.5s ease-in-out infinite;
        }

        .dot-muted {
            background: var(--text-ghost);
        }

        @keyframes dotG {

            0%,
            100% {
                box-shadow: 0 0 6px rgba(16, 185, 129, 0.5);
                opacity: 1;
            }

            50% {
                box-shadow: 0 0 2px rgba(16, 185, 129, 0.2);
                opacity: 0.4;
            }
        }

        @keyframes dotR {

            0%,
            100% {
                box-shadow: 0 0 6px rgba(239, 68, 68, 0.5);
                opacity: 1;
            }

            50% {
                box-shadow: 0 0 2px rgba(239, 68, 68, 0.2);
                opacity: 0.4;
            }
        }

        /* ===== MINUTE LABEL ===== */
        .mnt-label {
            font-size: 9px;
            color: var(--text-muted);
            font-weight: 500;
            margin-top: 2px;
            transition: color var(--transition-speed);
        }

        /* ===== SIGNAL CARD ===== */
        .signal-card {
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .signal-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
        }

        .signal-card.card-up::before {
            background: linear-gradient(90deg, transparent, var(--green-accent), transparent);
            background-size: 200%;
            animation: shimmer 3s linear infinite;
        }

        .signal-card.card-down::before {
            background: linear-gradient(90deg, transparent, var(--red-accent), transparent);
            background-size: 200%;
            animation: shimmer 3s linear infinite;
        }

        .signal-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }

        /* ===== CANDLE GRID ===== */
        .candle-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 6px;
        }

        .candle-cell {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 3px;
        }

        .candle-cell .c-label {
            font-size: 9px;
            font-weight: 700;
            color: var(--text-muted);
            letter-spacing: 0.5px;
            transition: color var(--transition-speed);
        }

        /* ===== PANEL HEADERS ===== */
        .panel-up-header {
            background: linear-gradient(135deg, var(--green-bg) 0%, transparent 100%);
            border-bottom: 1px solid var(--green-border);
            transition: background var(--transition-speed), border-color var(--transition-speed);
        }

        .panel-down-header {
            background: linear-gradient(135deg, var(--red-bg) 0%, transparent 100%);
            border-bottom: 1px solid var(--red-border);
            transition: background var(--transition-speed), border-color var(--transition-speed);
        }

        /* ===== STAT GLOW ===== */
        .stat-card {
            position: relative;
            overflow: hidden;
        }

        .stat-card::after {
            content: '';
            position: absolute;
            top: -10px;
            right: -10px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            filter: blur(20px);
            opacity: 0.2;
            pointer-events: none;
            transition: opacity var(--transition-speed);
        }

        [data-theme="light"] .stat-card::after {
            opacity: 0.1;
        }

        .stat-indigo::after {
            background: var(--stat-indigo);
        }

        .stat-green::after {
            background: var(--stat-green);
        }

        .stat-red::after {
            background: var(--stat-red);
        }

        .stat-amber::after {
            background: var(--stat-amber);
        }

        /* ===== TABLE ===== */
        .dark-table thead th {
            background: var(--bg-card);
            border-bottom: 1px solid var(--border-card);
            transition: background var(--transition-speed), border-color var(--transition-speed);
        }

        .dark-table tbody tr {
            border-bottom: 1px solid var(--border-subtle);
            transition: background 0.15s, border-color var(--transition-speed);
        }

        .dark-table tbody tr:hover {
            background: var(--bg-card-hover);
        }

        /* ===== THEME TOGGLE ===== */
        .theme-toggle {
            width: 52px;
            height: 28px;
            border-radius: 14px;
            cursor: pointer;
            position: relative;
            transition: background 0.4s ease;
            border: none;
            outline: none;
            padding: 0;
        }

        [data-theme="dark"] .theme-toggle {
            background: linear-gradient(135deg, #1e293b, #334155);
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.3), 0 0 8px rgba(99, 102, 241, 0.1);
        }

        [data-theme="light"] .theme-toggle {
            background: linear-gradient(135deg, #bfdbfe, #93c5fd);
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.08), 0 0 8px rgba(59, 130, 246, 0.1);
        }

        .theme-toggle .toggle-knob {
            position: absolute;
            top: 3px;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            transition: left 0.4s cubic-bezier(0.68, -0.15, 0.32, 1.15), background 0.4s, box-shadow 0.4s;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        [data-theme="dark"] .theme-toggle .toggle-knob {
            left: 3px;
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.4);
        }

        [data-theme="light"] .theme-toggle .toggle-knob {
            left: 27px;
            background: linear-gradient(135deg, #f59e0b, #fbbf24);
            box-shadow: 0 2px 8px rgba(245, 158, 11, 0.4);
        }
    </style>
</head>

<body class="antialiased min-h-screen flex flex-col">

    <!-- ============================================================
         NAVBAR
    ============================================================ -->
    <nav class="nav-bar sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2.5 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center"
                    style="background: linear-gradient(135deg, #10b981, #059669);">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                    </svg>
                </div>
                <h1 class="text-sm sm:text-base font-bold tracking-tight">
                    RODIS <span style="color: var(--green-accent);" class="font-semibold">Pattern Scanner</span>
                </h1>
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                <!-- Theme Toggle -->
                <button class="theme-toggle" onclick="toggleTheme()" title="Ubah tema">
                    <span class="toggle-knob">
                        <span id="theme-icon">🌙</span>
                    </span>
                </button>

                <span id="clock" class="text-[11px] font-mono" style="color: var(--text-muted);">00:00:00</span>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-[11px] font-semibold px-2.5 py-1.5 rounded-lg transition-all"
                        style="color: var(--red-accent); background: var(--red-bg); border: 1px solid var(--red-border);"
                        onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- ============================================================
         MAIN
    ============================================================ -->
    <main class="flex-grow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-5">

            <!-- HEADER + STATS — Single compact row -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-4 fade-in">
                <!-- Left: Title -->
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <span class="dot dot-red"></span>
                        <span class="text-[9px] font-extrabold uppercase tracking-[0.2em]"
                            style="color: var(--red-accent);">Live</span>
                    </div>
                    <div>
                        <h2 class="text-base sm:text-lg font-extrabold">Monitor C1–C5</h2>
                        <p class="text-[10px]" style="color: var(--text-muted);">Auto-refresh 4s · Notif Telegram
                            dikirim di candle C3 jika pola cocok</p>
                    </div>
                </div>

                <!-- Right: Stats inline + Search -->
                <div class="flex items-center gap-2 flex-wrap">
                    <div class="card stat-card stat-indigo rounded-lg px-3 py-1.5 flex items-center gap-2">
                        <span class="text-[10px] font-semibold" style="color: var(--text-muted);">Bot Aktif, market
                            berjalan :</span>
                        <span id="stat-bot" class="text-sm font-extrabold" style="color: var(--stat-indigo);">0</span>
                    </div>
                    <div class="card stat-card stat-green rounded-lg px-3 py-1.5 flex items-center gap-2">
                        <span class="text-[10px] font-semibold" style="color: var(--text-muted);">UP</span>
                        <span id="stat-up" class="text-sm font-extrabold" style="color: var(--stat-green);">0</span>
                    </div>
                    <div class="card stat-card stat-red rounded-lg px-3 py-1.5 flex items-center gap-2">
                        <span class="text-[10px] font-semibold" style="color: var(--text-muted);">DOWN</span>
                        <span id="stat-down" class="text-sm font-extrabold" style="color: var(--stat-red);">0</span>
                    </div>
                    <div class="card stat-card stat-amber rounded-lg px-3 py-1.5 flex items-center gap-2"
                        title="Notif terkirim ke RODIS NOTIFIKASI Telegram">
                        <span class="text-[10px] font-semibold" style="color: var(--text-muted);">Notif telegram</span>
                        <span id="stat-notif" class="text-sm font-extrabold" style="color: var(--stat-amber);">0</span>
                    </div>

                    <div class="relative ml-1">
                        <svg class="w-3.5 h-3.5 absolute left-2.5 top-1/2 -translate-y-1/2"
                            style="color: var(--text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text" id="search-input"
                            class="text-[11px] font-medium rounded-lg pl-8 pr-3 py-1.5 outline-none transition-all w-36 sm:w-44"
                            style="background: var(--input-bg); border: 1px solid var(--input-border); color: var(--text-primary);"
                            onfocus="this.style.borderColor='var(--input-focus)'"
                            onblur="this.style.borderColor='var(--input-border)'" placeholder="Cari pair..."
                            oninput="filterCards()">
                    </div>
                </div>
            </div>

            <!-- ============================================================
                 SPLIT SIGNAL PANELS
            ============================================================ -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4 mb-4 fade-in">

                <!-- ===== UP PANEL ===== -->
                <div class="card rounded-2xl overflow-hidden" style="border-color: var(--green-border);">
                    <div class="panel-up-header px-4 py-2.5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="dot dot-green"></span>
                                <h3 class="text-sm font-bold" style="color: var(--green-accent);">Sinyal UP</h3>
                                <span class="text-[9px] font-semibold" style="color: var(--text-muted);">BELI</span>
                            </div>
                            <span id="up-count-badge" class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                                style="color: var(--green-accent); background: var(--green-bg); border: 1px solid var(--green-border);">0</span>
                        </div>
                        <div class="flex items-center gap-1.5 mt-2 flex-wrap">
                            <span class="text-[9px] font-semibold" style="color: var(--text-muted);">Pola:</span>
                            <span class="badge-merah" style="font-size:9px;padding:1px 5px;">C1 Merah</span>
                            <span class="badge-hijau" style="font-size:9px;padding:1px 5px;">C2 Hijau</span>
                            <span class="badge-merah" style="font-size:9px;padding:1px 5px;">C3 Merah</span>
                            <span class="badge-merah" style="font-size:9px;padding:1px 5px;">C4 Merah</span>
                            <span class="badge-merah" style="font-size:9px;padding:1px 5px;">C5 Merah</span>
                        </div>
                        <p class="text-[10px] mt-1.5 flex items-center gap-1"
                            style="color: var(--green-accent); opacity: 0.7;">
                            ⚡ Notif Telegram dikirim saat <b>C1+C2+C3</b> cocok pola
                        </p>
                    </div>
                    <div id="up-panel-body" class="p-2.5 space-y-2 max-h-[380px] overflow-y-auto">
                        <div class="text-center py-8 text-xs" style="color: var(--text-ghost);">Belum ada sinyal UP
                        </div>
                    </div>
                </div>

                <!-- ===== DOWN PANEL ===== -->
                <div class="card rounded-2xl overflow-hidden" style="border-color: var(--red-border);">
                    <div class="panel-down-header px-4 py-2.5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="dot dot-red"></span>
                                <h3 class="text-sm font-bold" style="color: var(--red-accent);">Sinyal DOWN</h3>
                                <span class="text-[9px] font-semibold" style="color: var(--text-muted);">JUAL</span>
                            </div>
                            <span id="down-count-badge" class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                                style="color: var(--red-accent); background: var(--red-bg); border: 1px solid var(--red-border);">0</span>
                        </div>
                        <div class="flex items-center gap-1.5 mt-2 flex-wrap">
                            <span class="text-[9px] font-semibold" style="color: var(--text-muted);">Pola:</span>
                            <span class="badge-hijau" style="font-size:9px;padding:1px 5px;">C1 Hijau</span>
                            <span class="badge-merah" style="font-size:9px;padding:1px 5px;">C2 Merah</span>
                            <span class="badge-hijau" style="font-size:9px;padding:1px 5px;">C3 Hijau</span>
                            <span class="badge-hijau" style="font-size:9px;padding:1px 5px;">C4 Hijau</span>
                            <span class="badge-hijau" style="font-size:9px;padding:1px 5px;">C5 Hijau</span>
                        </div>
                        <p class="text-[10px] mt-1.5 flex items-center gap-1"
                            style="color: var(--red-accent); opacity: 0.7;">
                            ⚡ Notif Telegram dikirim saat <b>C1+C2+C3</b> cocok pola
                        </p>
                    </div>
                    <div id="down-panel-body" class="p-2.5 space-y-2 max-h-[380px] overflow-y-auto">
                        <div class="text-center py-8 text-xs" style="color: var(--text-ghost);">Belum ada sinyal DOWN
                        </div>
                    </div>
                </div>

            </div>

            <!-- ============================================================
                 WAITING TABLE
            ============================================================ -->
            <div class="card rounded-2xl overflow-hidden fade-in">
                <div class="px-4 py-2.5 flex items-center justify-between"
                    style="border-bottom: 1px solid var(--border-subtle);">
                    <div class="flex items-center gap-2">
                        <span class="dot dot-muted"></span>
                        <h3 class="text-[11px] font-bold" style="color: var(--text-muted);">Menunggu Pola — Semua Market
                        </h3>
                    </div>
                    <span id="waiting-count-badge" class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                        style="color: var(--text-muted); background: var(--badge-empty-bg);">0</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full dark-table">
                        <thead>
                            <tr>
                                <th class="px-3 py-2 text-left text-[10px] font-bold uppercase tracking-wider"
                                    style="color: var(--text-muted);">#</th>
                                <th class="px-3 py-2 text-left text-[10px] font-bold uppercase tracking-wider"
                                    style="color: var(--text-muted);">Pair</th>
                                <th class="px-3 py-2 text-center text-[10px] font-bold uppercase tracking-wider"
                                    style="color: var(--text-muted);">C1</th>
                                <th class="px-3 py-2 text-center text-[10px] font-bold uppercase tracking-wider"
                                    style="color: var(--text-muted);">C2</th>
                                <th class="px-3 py-2 text-center text-[10px] font-bold uppercase tracking-wider"
                                    style="color: var(--text-muted);">C3</th>
                                <th class="px-3 py-2 text-center text-[10px] font-bold uppercase tracking-wider"
                                    style="color: var(--text-muted);">C4</th>
                                <th class="px-3 py-2 text-center text-[10px] font-bold uppercase tracking-wider"
                                    style="color: var(--text-muted);">C5</th>
                                <th class="px-3 py-2 text-center text-[10px] font-bold uppercase tracking-wider"
                                    style="color: var(--text-muted);">Waktu</th>
                            </tr>
                        </thead>
                        <tbody id="waiting-tbody">
                            <tr>
                                <td colspan="8" class="px-4 py-6 text-center text-xs" style="color: var(--text-ghost);">
                                    Menunggu data dari bot trading...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Footer info -->
            <div class="mt-3 text-[10px] flex justify-between items-center" style="color: var(--text-ghost);">
                <span id="rows-info">0 pasang ditampilkan</span>
                <span>Update: <span id="updated-at">-</span></span>
            </div>

        </div>
    </main>

    <!-- ============================================================
         FOOTER
    ============================================================ -->
    <footer style="border-top: 1px solid var(--border-subtle);" class="py-3 mt-2">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-[11px]" style="color: var(--text-ghost);">
            <span style="color: var(--text-muted);" class="font-semibold">RODIS</span> · Pattern Scanner C1-C5
        </div>
    </footer>

    <!-- ============================================================
         SCRIPTS
    ============================================================ -->
    <script>
        // ============================================================
        // THEME TOGGLE
        // ============================================================
        function toggleTheme() {
            const html = document.documentElement;
            const icon = document.getElementById('theme-icon');
            if (html.getAttribute('data-theme') === 'dark') {
                html.setAttribute('data-theme', 'light');
                icon.textContent = '☀️';
                localStorage.setItem('rodis-theme', 'light');
            } else {
                html.setAttribute('data-theme', 'dark');
                icon.textContent = '🌙';
                localStorage.setItem('rodis-theme', 'dark');
            }
        }

        // Restore saved theme
        (function () {
            const saved = localStorage.getItem('rodis-theme');
            if (saved) {
                document.documentElement.setAttribute('data-theme', saved);
                const icon = document.getElementById('theme-icon');
                if (icon) icon.textContent = saved === 'dark' ? '🌙' : '☀️';
            }
        })();

        // ============================================================
        // CLOCK
        // ============================================================
        function startRealtimeClock() {
            setInterval(() => {
                const now = new Date();
                document.getElementById('clock').textContent =
                    [now.getHours(), now.getMinutes(), now.getSeconds()]
                        .map(n => String(n).padStart(2, '0')).join(':');
            }, 1000);
        }

        // ============================================================
        // CANDLE BADGE
        // ============================================================
        function candleBadge(val) {
            if (!val || val === '-') return `<span class="badge-empty">—</span>`;
            if (val.includes('Hijau')) return `<span class="badge-hijau">🟢 Hijau</span>`;
            return `<span class="badge-merah">🔴 Merah</span>`;
        }

        function candleBadgeWithMinute(val, waktuBlock, offset) {
            let mntLabel = '';
            if (waktuBlock && waktuBlock !== '-') {
                const parts = waktuBlock.split(':');
                if (parts.length === 2) {
                    const baseMm = parseInt(parts[1], 10);
                    const totalMm = baseMm + offset;
                    const actualMm = totalMm % 60;
                    mntLabel = `<div class="mnt-label">Menit ${String(actualMm).padStart(2, '0')}</div>`;
                }
            }
            return `<div class="flex flex-col items-center gap-0.5">${candleBadge(val)}${mntLabel}</div>`;
        }

        function patternPill(type) {
            if (type === 'UP') return `<span class="pill-up">▲ UP</span>`;
            if (type === 'DOWN') return `<span class="pill-down">▼ DOWN</span>`;
            return `<span class="pill-none">—</span>`;
        }

        // ============================================================
        // SIGNAL CARD
        // ============================================================
        function buildSignalCard(item, type) {
            const cardClass = type === 'UP' ? 'card-up' : 'card-down';
            const borderVar = type === 'UP' ? 'var(--green-border)' : 'var(--red-border)';

            const blokLabel = item.waktu_block && item.waktu_block !== '-'
                ? item.waktu_block : '—';

            return `
            <div class="signal-card ${cardClass} card-subtle rounded-xl px-3.5 py-3" style="border-color: ${borderVar};" data-market="${item.market}">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        ${patternPill(type)}
                        <span class="text-xs font-bold">${item.market}</span>
                    </div>
                    <span class="text-[10px] font-mono" style="color: var(--text-muted);">${blokLabel}</span>
                </div>
                <div class="candle-grid">
                    <div class="candle-cell"><span class="c-label">C1</span>${candleBadgeWithMinute(item.c1, item.waktu_block, 0)}</div>
                    <div class="candle-cell"><span class="c-label">C2</span>${candleBadgeWithMinute(item.c2, item.waktu_block, 1)}</div>
                    <div class="candle-cell"><span class="c-label">C3</span>${candleBadgeWithMinute(item.c3, item.waktu_block, 2)}</div>
                    <div class="candle-cell"><span class="c-label">C4</span>${candleBadgeWithMinute(item.c4, item.waktu_block, 3)}</div>
                    <div class="candle-cell"><span class="c-label">C5</span>${candleBadgeWithMinute(item.c5, item.waktu_block, 4)}</div>
                </div>
            </div>`;
        }

        // ============================================================
        // FILTER
        // ============================================================
        function filterCards() {
            const q = document.getElementById('search-input').value.toLowerCase();
            let total = 0;
            document.querySelectorAll('.signal-card[data-market]').forEach(card => {
                const mkt = (card.getAttribute('data-market') || '').toLowerCase();
                if (mkt.includes(q)) { card.style.display = ''; total++; }
                else card.style.display = 'none';
            });
            document.querySelectorAll('#waiting-tbody tr[data-market]').forEach(row => {
                const mkt = (row.getAttribute('data-market') || '').toLowerCase();
                if (mkt.includes(q)) { row.style.display = ''; total++; }
                else row.style.display = 'none';
            });
            document.getElementById('rows-info').textContent = `${total} pasang ditampilkan`;
        }

        // ============================================================
        // RENDER
        // ============================================================
        function renderTable(data) {
            const upBody = document.getElementById('up-panel-body');
            const downBody = document.getElementById('down-panel-body');
            const waitingBody = document.getElementById('waiting-tbody');
            const statBot = document.getElementById('stat-bot');
            const statUp = document.getElementById('stat-up');
            const statDown = document.getElementById('stat-down');
            const statNotif = document.getElementById('stat-notif');

            if (!data || data.length === 0) {
                upBody.innerHTML = `<div class="text-center py-8 text-xs" style="color:var(--text-ghost)">Belum ada sinyal UP</div>`;
                downBody.innerHTML = `<div class="text-center py-8 text-xs" style="color:var(--text-ghost)">Belum ada sinyal DOWN</div>`;
                waitingBody.innerHTML = `<tr><td colspan="8" class="px-4 py-6 text-center text-xs" style="color:var(--text-ghost)">Menunggu data...</td></tr>`;
                if (statBot) statBot.textContent = '0';
                document.getElementById('rows-info').textContent = '0 pasang ditampilkan';
                return;
            }

            const upItems = data.filter(d => d.pattern_type === 'UP');
            const downItems = data.filter(d => d.pattern_type === 'DOWN');
            const waitingItems = data.filter(d => d.pattern_type === 'NONE');

            if (statBot) statBot.textContent = data.length;
            if (statUp) statUp.textContent = upItems.length;
            if (statDown) statDown.textContent = downItems.length;
            if (statNotif) statNotif.textContent = data.filter(d => d.notif_sent).length;

            document.getElementById('up-count-badge').textContent = upItems.length;
            document.getElementById('down-count-badge').textContent = downItems.length;
            document.getElementById('waiting-count-badge').textContent = waitingItems.length;

            upItems.sort((a, b) => a.market.localeCompare(b.market));
            downItems.sort((a, b) => a.market.localeCompare(b.market));
            waitingItems.sort((a, b) => a.market.localeCompare(b.market));

            upBody.innerHTML = upItems.length === 0
                ? `<div class="text-center py-8 text-xs" style="color:var(--text-ghost)">Belum ada sinyal UP</div>`
                : upItems.map(item => buildSignalCard(item, 'UP')).join('');

            downBody.innerHTML = downItems.length === 0
                ? `<div class="text-center py-8 text-xs" style="color:var(--text-ghost)">Belum ada sinyal DOWN</div>`
                : downItems.map(item => buildSignalCard(item, 'DOWN')).join('');

            if (waitingItems.length === 0) {
                waitingBody.innerHTML = `<tr><td colspan="8" class="px-4 py-4 text-center text-xs" style="color:var(--text-muted)">Semua market memiliki pola aktif 🎉</td></tr>`;
            } else {
                let html = '';
                waitingItems.forEach((item, idx) => {
                    const blokLabel = item.waktu_block && item.waktu_block !== '-'
                        ? `<span class="font-semibold" style="color:var(--text-secondary)">${item.waktu_block}</span>`
                        : `<span style="color:var(--text-ghost)">—</span>`;
                    html += `
                    <tr data-market="${item.market}">
                        <td class="px-3 py-2 text-[11px]" style="color:var(--text-ghost)">${idx + 1}</td>
                        <td class="px-3 py-2"><span class="text-[11px] font-medium" style="color:var(--text-secondary)">${item.market}</span></td>
                        <td class="px-3 py-2 text-center">${candleBadgeWithMinute(item.c1, item.waktu_block, 0)}</td>
                        <td class="px-3 py-2 text-center">${candleBadgeWithMinute(item.c2, item.waktu_block, 1)}</td>
                        <td class="px-3 py-2 text-center">${candleBadgeWithMinute(item.c3, item.waktu_block, 2)}</td>
                        <td class="px-3 py-2 text-center">${candleBadgeWithMinute(item.c4, item.waktu_block, 3)}</td>
                        <td class="px-3 py-2 text-center">${candleBadgeWithMinute(item.c5, item.waktu_block, 4)}</td>
                        <td class="px-3 py-2 text-center text-[11px]">${blokLabel}</td>
                    </tr>`;
                });
                waitingBody.innerHTML = html;
            }

            document.getElementById('rows-info').textContent = `${data.length} pasang ditampilkan`;
            document.getElementById('updated-at').textContent = new Date().toLocaleTimeString('id-ID');
            const q = document.getElementById('search-input').value;
            if (q) filterCards();
        }

        // ============================================================
        // FETCH DATA
        // ============================================================
        let pollingInterval;

        function fetchData() {
            fetch(`${API_BASE}/user2_data`)
                .then(res => res.json())
                .then(json => { if (json.success) renderTable(json.data || []); })
                .catch(err => console.warn('[User2] Fetch error:', err));
        }

        function startPolling() {
            fetchData();
            pollingInterval = setInterval(fetchData, 4000);
        }

        // ============================================================
        // SESSION CHECK
        // ============================================================
        function startSessionCheck() {
            setInterval(() => {
                fetch('/user2/check-session', { credentials: 'same-origin' })
                    .then(res => res.json())
                    .then(data => {
                        if (!data.valid) {
                            clearInterval(pollingInterval);
                            alert('⚠️ Sesi Anda telah berakhir karena akun ini login di perangkat lain.');
                            window.location.href = '/login';
                        }
                    })
                    .catch(() => { });
            }, 5000);
        }

        // ============================================================
        // INIT
        // ============================================================
        window.onload = function () {
            startRealtimeClock();
            startPolling();
            startSessionCheck();
        };
    </script>

</body>

</html>