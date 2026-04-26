<div id="view-dashboard" class="fade-in block w-full max-w-none">

    {{-- ===== BOT STOPPED BANNER ===== --}}
    <div id="bot-stopped-banner"
        class="hidden mb-4 flex items-center gap-3 px-4 py-3 bg-red-600 text-white rounded-xl shadow-lg font-bold text-sm animate-pulse">
        <span class="text-lg">🔴</span>
        <span>BOT STOPPED — Tidak ada data masuk lebih dari 60 detik. Periksa koneksi / token OlympTrade.</span>
        <button onclick="document.getElementById('bot-stopped-banner').classList.add('hidden')"
            class="ml-auto text-white/80 hover:text-white text-xs border border-white/30 rounded px-2 py-1">Tutup</button>
    </div>

    {{-- ===== HEADER + KONTROL ===== --}}
    <div class="mb-5 gap-4 border-b border-gray-100 pb-5">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h3 class="text-xl font-bold text-dark hidden md:block mb-2">Pusat Kendali Market</h3>
                <div id="monitor-status-badge" class="flex flex-wrap items-center gap-2 text-xs font-medium">
                    <span
                        class="px-2.5 py-1.5 bg-indigo-50 border border-indigo-100 text-indigo-800 rounded-lg flex items-center gap-1.5 shadow-sm">
                        🤖 Bot Berjalan: <b id="lbl-bot-count" class="text-indigo-600 text-sm">0/36</b>
                    </span>
                    <span
                        class="px-2.5 py-1.5 bg-gray-50 border border-gray-200 text-gray-600 rounded-lg flex items-center gap-1.5 shadow-sm">
                        📲 Sinyal Massal: <b id="lbl-tg-count" class="text-gray-400 font-bold">OFF</b>
                    </span>
                    {{-- Indikator Koneksi --}}
                    <span id="connection-badge"
                        class="px-2.5 py-1.5 bg-green-50 border border-green-200 text-green-700 rounded-lg flex items-center gap-1.5 shadow-sm">
                        <span id="connection-dot" class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                        <span id="connection-text">Live</span>
                    </span>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center gap-3 w-full md:w-auto">
                {{-- ===== LEFT CONTROL: PLAY/STOP + RESET ===== --}}
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
                    <button id="btn-toggle" onclick="toggleMarkets(event)"
                        class="control-btn bg-emerald-600 hover:bg-emerald-700 text-white">
                        <span id="toggle-icon" class="btn-icon">▶</span>
                        <span id="toggle-text">PLAY</span>
                    </button>

                    <button onclick="resetAllMarkets()" class="control-btn
                        bg-gray-100 hover:bg-gray-200
                        text-gray-800
                        border border-gray-200
                        shadow-sm hover:shadow
                        transition-all duration-200">
                        🔄 Reset Data
                    </button>
                </div>

                {{-- ===== RIGHT CONTROL: SINYAL MASSAL ===== --}}
                <div class="w-full sm:w-auto">
                    <div
                        class="flex flex-col sm:flex-row items-stretch sm:items-center border border-blue-200 rounded-lg overflow-hidden bg-white shadow-sm w-full sm:w-auto">
                        <div
                            class="bg-blue-50 px-3 py-2 sm:py-0 sm:h-[42px] flex items-center border-b sm:border-b-0 sm:border-r border-blue-200">
                            <span class="text-xs font-bold text-blue-800 uppercase whitespace-nowrap">
                                False Ke:
                            </span>
                        </div>

                        <input type="number" id="mass-tg-loss" value="7" min="1"
                            class="w-full sm:w-16 px-3 py-2 sm:px-0 sm:py-0 text-center text-sm font-bold outline-none text-blue-900">

                        <button id="btn-mass-tg" onclick="activateMassTelegram(event)"
                            class="control-btn bg-blue-600 hover:bg-blue-700 text-white">
                            📲 Sinyal Massal
                        </button>
                    </div>
                </div>

                {{-- ===== BOT ALERT TG TOGGLE (TERPISAH DARI SINYAL MASSAL) ===== --}}
                <div class="w-full sm:w-auto">
                    <button id="btn-bot-alert-tg" onclick="toggleBotAlertTg()"
                        class="control-btn w-full sm:w-auto text-white border-0 shadow-sm bg-gray-400 hover:bg-gray-500"
                        title="Toggle: aktifkan/nonaktifkan notifikasi Telegram jika bot berhenti">
                        <span id="bot-alert-icon">🔕</span>
                        <span id="bot-alert-label">Alert Bot: OFF</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- ===== STATUS BAR: Last Update + Koneksi + Alert Status ===== --}}
        <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1.5 text-[11px] text-gray-500">
            <span>🕐 Last Update: <b id="global-last-update" class="text-gray-700">–</b></span>
            <span class="hidden sm:inline text-gray-300">|</span>
            <span>📡 Status: <b id="global-conn-status" class="text-green-600">Menunggu data...</b></span>
            <span class="hidden sm:inline text-gray-300">|</span>
            <span id="bot-alert-tg-status" class="text-gray-400">
                🔔 Alert Bot: <b id="bot-alert-status-text">OFF</b>
                <span id="bot-alert-cooldown-info" class="hidden text-[10px] text-orange-500 ml-1"></span>
            </span>
        </div>
    </div>

    {{-- ===== LIVE FALSE STREAK ===== --}}
    <div class="grid grid-cols-1 gap-5 mb-6 w-full items-stretch">
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 w-full flex flex-col"
            id="live-streak-container">
            <div
                class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 border-b border-gray-50 pb-3 gap-2">
                <h3 class="text-sm font-extrabold text-dark flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-red animate-pulse shadow-[0_0_8px_#ef4444]"></span>
                    Live False Streak — Backtest Monitor
                </h3>

                <div
                    class="text-xs font-bold text-blue-700 bg-blue-50 border border-blue-200 px-3 py-1.5 rounded-lg flex items-center gap-1.5 shadow-sm">
                    ⏰ <span id="realtime-clock">Memuat Waktu...</span>
                </div>
            </div>

            {{-- Grouped streak list (diisi JS) --}}
            <div id="streak-list">
                <span class="text-xs text-gray-400 font-medium italic">Belum ada market yang berjalan...</span>
            </div>
        </div>
    </div>

    <style>
        /* Bot-stopped banner pulse */
        #bot-stopped-banner:not(.hidden) {
            animation: bannerPulse 2s infinite ease-in-out;
        }

        @keyframes bannerPulse {
            0%,
            100% {
                background-color: #dc2626;
            }

            50% {
                background-color: #b91c1c;
            }
        }

        /* Group section header */
        .streak-group-header {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #6b7280;
            margin-bottom: 6px;
        }

        .streak-group-header::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e5e7eb;
        }

        /* ============================================================
           STREAK LIST — Grid kolom per grup
        ============================================================ */
        #streak-list {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            align-items: start;
        }

        @media (max-width: 1023px) {
            #streak-list {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 639px) {
            #streak-list {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Grup section: satu kolom penuh */
        .streak-section {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        /* Group grid — vertikal satu kolom */
        .streak-group-grid {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        /* ============================================================
           CARD — Horizontal layout: nama kiri, nilai kanan
        ============================================================ */
        .streak-card {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            padding: 10px 14px 10px 18px;
            border-radius: 10px;
            border: 1.5px solid #e5e7eb;
            background: #f9fafb;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
            width: 100%;
            gap: 8px;
        }

        /* Aksen warna di sisi kiri card */
        .streak-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; bottom: 0;
            width: 4px;
            background: #d1d5db;
            border-radius: 10px 0 0 10px;
        }

        .streak-card.streak-ok::before   { background: #22c55e; }
        .streak-card.streak-warn::before { background: #f59e0b; }
        .streak-card.streak-danger::before { background: #ef4444; }

        .streak-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
        }

        .streak-card.streak-ok {
            border-color: #86efac;
            background: #f0fdf4;
        }

        .streak-card.streak-warn {
            border-color: #fcd34d;
            background: #fffbeb;
        }

        .streak-card.streak-danger {
            border-color: #fca5a5;
            background: #fff1f2;
            animation: redPulse 1.4s infinite ease-in-out;
        }

        /* Nama ticker — sisi kiri */
        .streak-name {
            font-size: 11px;
            font-weight: 700;
            color: #374151;
            line-height: 1.3;
            flex: 1;
            min-width: 0;
            word-break: break-word;
        }

        /* Sisi kanan: angka + label */
        .streak-right {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            flex-shrink: 0;
        }

        /* Angka False Streak — elemen utama */
        .streak-value {
            font-size: 20px;
            font-weight: 900;
            line-height: 1;
            transition: color 0.2s;
        }

        .streak-value.val-ok     { color: #16a34a; }
        .streak-value.val-warn   { color: #d97706; }
        .streak-value.val-danger { color: #dc2626; }

        /* Label "False Streak" kecil */
        .streak-label {
            font-size: 8px;
            font-weight: 600;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-top: 1px;
        }

        .control-btn {
            width: auto;
            min-width: 120px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-weight: 700;
            border-radius: 10px;
            padding: 0 14px;
            transition: all .25s ease;
        }

        @media (max-width: 640px) {
            .control-btn {
                width: 100%;
                min-width: 0;
            }
        }

        .control-btn:disabled {
            opacity: .5;
            cursor: not-allowed;
        }

        .btn-icon {
            width: 18px;
            text-align: center;
        }

        @keyframes redPulse {
            0% {
                box-shadow: 0 0 0 rgba(239, 68, 68, 0.2);
            }

            50% {
                box-shadow: 0 0 16px rgba(239, 68, 68, 0.6);
            }

            100% {
                box-shadow: 0 0 0 rgba(239, 68, 68, 0.2);
            }
        }

        /* ============================================================
           DARK MODE
        ============================================================ */
        .dark .streak-group-header {
            color: #8b949e;
        }

        .dark .streak-group-header::after {
            background: #30363d;
        }

        .dark .streak-card {
            background: #161b22;
            border-color: #30363d;
        }

        .dark .streak-card.streak-ok {
            background: #0d2318;
            border-color: #14532d;
        }

        .dark .streak-card.streak-warn {
            background: #2d1f06;
            border-color: #78350f;
        }

        .dark .streak-card.streak-danger {
            background: #2d0a0a;
            border-color: #7f1d1d;
        }

        .dark .streak-name {
            color: #c9d1d9;
        }

        .dark .streak-label {
            color: #4b5563;
        }

        .dark .streak-value.val-ok     { color: #3fb950; }
        .dark .streak-value.val-warn   { color: #fcd34d; }
        .dark .streak-value.val-danger { color: #fca5a5; }

        .dark .streak-card::before {
            opacity: 0.9;
        }

        /* Mobile: compact card */
        @media (max-width: 639px) {
            .streak-card {
                padding: 9px 12px 9px 16px;
            }
            .streak-name { font-size: 10px; }
            .streak-value { font-size: 18px; }
            .streak-label { font-size: 7px; }
        }
    </style>
</div>
