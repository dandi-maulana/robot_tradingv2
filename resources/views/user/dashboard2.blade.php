<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RODIS - Pattern Scanner</title>
    <meta name="description" content="RODIS Pattern Scanner - Monitor pola candle C1-C5 secara real-time">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        gojek: {
                            DEFAULT: '#00aa13',
                            dark: '#00880f',
                            light: '#e6f6e8'
                        }
                    }
                }
            }
        }

        var API_BASE = window.location.hostname === "127.0.0.1" ?
            window.location.protocol + "//" + window.location.hostname + ":5000/api" :
            "/api";
    </script>

    <style>
        body { background: #f4f5f7; }

        .fade-in {
            animation: fadeIn .25s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Candle badge styles */
        .badge-hijau {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 5px;
            font-size: 11px;
            font-weight: 700;
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
            white-space: nowrap;
        }

        .badge-merah {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 5px;
            font-size: 11px;
            font-weight: 700;
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
            white-space: nowrap;
        }

        .badge-empty {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 5px;
            font-size: 11px;
            font-weight: 700;
            background: #f1f5f9;
            color: #94a3b8;
            border: 1px solid #e2e8f0;
        }

        /* Pattern pill */
        .pill-up {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: 11px; font-weight: 800;
            background: #d1fae5; color: #065f46;
            border: 1px solid #6ee7b7;
            animation: blink-up 2s ease-in-out infinite;
        }

        .pill-down {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: 11px; font-weight: 800;
            background: #fee2e2; color: #991b1b;
            border: 1px solid #fca5a5;
            animation: blink-down 2s ease-in-out infinite;
        }

        .pill-none {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: 11px; font-weight: 600;
            background: #f1f5f9; color: #94a3b8;
            border: 1px solid #e2e8f0;
        }

        @keyframes blink-up {
            0%, 100% { box-shadow: 0 0 0 rgba(16,185,129,0); }
            50%       { box-shadow: 0 0 8px rgba(16,185,129,0.4); }
        }

        @keyframes blink-down {
            0%, 100% { box-shadow: 0 0 0 rgba(239,68,68,0); }
            50%       { box-shadow: 0 0 8px rgba(239,68,68,0.35); }
        }

        /* Row highlight */
        tr.row-up   { background: #f0fdf4 !important; }
        tr.row-down { background: #fff5f5 !important; }

        /* Live dot */
        .live-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: #ef4444;
            display: inline-block;
            animation: livePulse 1.2s ease-in-out infinite;
        }

        @keyframes livePulse {
            0%, 100% { opacity: 1; box-shadow: 0 0 5px #ef4444; }
            50%       { opacity: 0.3; box-shadow: none; }
        }

        /* Minute sub-label under C1 value */
        .mnt-label {
            font-size: 10px;
            color: #64748b;
            font-weight: 600;
            margin-top: 2px;
        }
    </style>
</head>

<body class="text-slate-800 antialiased min-h-screen flex flex-col">

    <!-- ============================================================
         NAVBAR
    ============================================================ -->
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="bg-gojek text-white rounded-xl p-2 shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                    </svg>
                </div>
                <h1 class="text-xl font-extrabold tracking-tight">RODIS
                    <span class="text-gojek font-semibold">Pattern Scanner</span>
                </h1>
            </div>

            <div class="flex items-center gap-3">
                <div class="text-xs font-bold text-blue-700 bg-blue-50 border border-blue-100 rounded-lg px-3 py-1.5">
                    ⏰ <span id="clock">00:00:00 WIB</span>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="text-xs font-bold text-red-600 bg-red-50 border border-red-100 px-3 py-1.5 rounded-lg hover:bg-red-100 transition-colors">
                        🚪 Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- ============================================================
         MAIN
    ============================================================ -->
    <main class="flex-grow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-5">

            <!-- STAT CARDS -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 fade-in">
                <div class="bg-white border border-slate-200 rounded-xl p-4">
                    <div class="text-xs font-semibold text-slate-500">Bot Aktif</div>
                    <div id="stat-bot" class="text-2xl font-extrabold text-indigo-600 mt-1">0</div>
                    <div class="text-xs text-slate-400 mt-0.5">market berjalan</div>
                </div>
                <div class="bg-white border border-slate-200 rounded-xl p-4">
                    <div class="text-xs font-semibold text-slate-500">Pola UP</div>
                    <div id="stat-up" class="text-2xl font-extrabold text-emerald-600 mt-1">0</div>
                    <div class="text-xs text-slate-400 mt-0.5">terdeteksi hari ini</div>
                </div>
                <div class="bg-white border border-slate-200 rounded-xl p-4">
                    <div class="text-xs font-semibold text-slate-500">Pola DOWN</div>
                    <div id="stat-down" class="text-2xl font-extrabold text-red-500 mt-1">0</div>
                    <div class="text-xs text-slate-400 mt-0.5">terdeteksi hari ini</div>
                </div>
                <div class="bg-white border border-slate-200 rounded-xl p-4">
                    <div class="text-xs font-semibold text-slate-500">Notif Terkirim</div>
                    <div id="stat-notif" class="text-2xl font-extrabold text-amber-500 mt-1">0</div>
                    <div class="text-xs text-slate-400 mt-0.5">ke RODIS NOTIFIKASI</div>
                </div>
            </div>

            <!-- PATTERN LEGEND -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 fade-in">
                <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4">
                    <div class="text-xs font-bold text-emerald-700 mb-2">🟢 Pola UP (Beli)</div>
                    <div class="flex gap-1.5 flex-wrap">
                        <span class="badge-merah">C1 Merah</span>
                        <span class="badge-hijau">C2 Hijau</span>
                        <span class="badge-merah">C3 Merah</span>
                        <span class="badge-merah">C4 Merah</span>
                        <span class="badge-merah">C5 Merah</span>
                    </div>
                    <p class="text-xs text-emerald-600 mt-2">⚡ Notif Telegram dikirim saat C1+C2+C3 cocok</p>
                </div>
                <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                    <div class="text-xs font-bold text-red-700 mb-2">🔴 Pola DOWN (Jual)</div>
                    <div class="flex gap-1.5 flex-wrap">
                        <span class="badge-hijau">C1 Hijau</span>
                        <span class="badge-merah">C2 Merah</span>
                        <span class="badge-hijau">C3 Hijau</span>
                        <span class="badge-hijau">C4 Hijau</span>
                        <span class="badge-hijau">C5 Hijau</span>
                    </div>
                    <p class="text-xs text-red-600 mt-2">⚡ Notif Telegram dikirim saat C1+C2+C3 cocok</p>
                </div>
            </div>

            <!-- MAIN TABLE CARD -->
            <div class="bg-white border border-slate-200 rounded-2xl p-5 fade-in">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                    <div>
                        <h2 class="text-sm font-extrabold text-slate-800 flex items-center gap-2">
                            <span class="live-dot"></span>
                            Live Monitor Candle C1 - C5
                        </h2>
                        <p class="text-xs text-slate-500 mt-1">Update otomatis setiap 4 detik. Notif Telegram dikirim di candle C3 jika pola cocok.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="text" id="search-input"
                            class="text-xs font-semibold border border-slate-300 rounded-lg px-3 py-1.5 bg-white outline-none focus:border-gojek"
                            placeholder="🔍 Cari pair..."
                            oninput="filterTable()">
                    </div>
                </div>

                <div class="overflow-x-auto border border-slate-200 rounded-xl">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-bold text-slate-600">#</th>
                                <th class="px-3 py-2 text-left text-xs font-bold text-slate-600">Pair / Market</th>
                                <th class="px-3 py-2 text-center text-xs font-bold text-slate-600">C1
                                    <div class="font-normal text-slate-400 text-[10px]">Menit ke-00</div>
                                </th>
                                <th class="px-3 py-2 text-center text-xs font-bold text-slate-600">C2
                                    <div class="font-normal text-slate-400 text-[10px]">Menit ke-01</div>
                                </th>
                                <th class="px-3 py-2 text-center text-xs font-bold text-slate-600">C3
                                    <div class="font-normal text-slate-400 text-[10px]">Menit ke-02</div>
                                </th>
                                <th class="px-3 py-2 text-center text-xs font-bold text-slate-600">C4
                                    <div class="font-normal text-slate-400 text-[10px]">Menit ke-03</div>
                                </th>
                                <th class="px-3 py-2 text-center text-xs font-bold text-slate-600">C5
                                    <div class="font-normal text-slate-400 text-[10px]">Menit ke-04</div>
                                </th>
                                <th class="px-3 py-2 text-center text-xs font-bold text-slate-600">Blok Waktu</th>
                                <th class="px-3 py-2 text-center text-xs font-bold text-slate-600">Pola</th>
                            </tr>
                        </thead>
                        <tbody id="pattern-tbody" class="divide-y divide-slate-100">
                            <tr>
                                <td colspan="9" class="px-4 py-8 text-center text-slate-400 font-medium">
                                    ⏳ Menunggu data dari bot trading...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 text-xs text-slate-500 flex justify-between items-center">
                    <span id="rows-info">0 pasang ditampilkan</span>
                    <span>Update: <span id="updated-at">-</span></span>
                </div>
            </div>

        </div>
    </main>

    <!-- ============================================================
         FOOTER
    ============================================================ -->
    <footer class="bg-white border-t border-slate-200 py-5 mt-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-sm text-slate-500">
            <strong>RODIS</strong> - Robot Trading · Pattern Scanner C1-C5
        </div>
    </footer>

    <!-- ============================================================
         SCRIPTS
    ============================================================ -->
    <script>
        // ============================================================
        // CLOCK
        // ============================================================
        function startRealtimeClock() {
            setInterval(() => {
                const now = new Date();
                document.getElementById('clock').textContent =
                    [now.getHours(), now.getMinutes(), now.getSeconds()]
                    .map(n => String(n).padStart(2, '0')).join(':') + ' WIB';
            }, 1000);
        }

        // ============================================================
        // CANDLE BADGE (dengan sub-label menit opsional)
        // ============================================================
        function candleBadge(val) {
            if (!val || val === '-') {
                return `<span class="badge-empty">—</span>`;
            }
            if (val.includes('Hijau')) {
                return `<span class="badge-hijau">🟢 Hijau</span>`;
            }
            return `<span class="badge-merah">🔴 Merah</span>`;
        }

        // C dengan sub-label menit (C1=offset 0, C2=offset 1, dst.)
        function candleBadgeWithMinute(val, waktuBlock, offset) {
            let mntLabel = '';
            if (waktuBlock && waktuBlock !== '-') {
                const parts = waktuBlock.split(':');
                if (parts.length === 2) {
                    const baseMm  = parseInt(parts[1], 10);
                    const baseHh  = parseInt(parts[0], 10);
                    const totalMm = baseMm + offset;
                    const actualMm = totalMm % 60;
                    mntLabel = `<div class="mnt-label">Mnt ${String(actualMm).padStart(2,'0')}</div>`;
                }
            }
            const chip = candleBadge(val);
            return `<div class="flex flex-col items-center gap-0.5">${chip}${mntLabel}</div>`;
        }

        // ============================================================
        // PATTERN PILL
        // ============================================================
        function patternPill(type) {
            if (type === 'UP')   return `<span class="pill-up">📈 UP</span>`;
            if (type === 'DOWN') return `<span class="pill-down">📉 DOWN</span>`;
            return `<span class="pill-none">—</span>`;
        }

        // ============================================================
        // FILTER
        // ============================================================
        function filterTable() {
            const q    = document.getElementById('search-input').value.toLowerCase();
            const rows = document.querySelectorAll('#pattern-tbody tr[data-market]');
            let count  = 0;
            rows.forEach(row => {
                const mkt = (row.getAttribute('data-market') || '').toLowerCase();
                if (mkt.includes(q)) { row.style.display = ''; count++; }
                else row.style.display = 'none';
            });
            document.getElementById('rows-info').textContent = `${count} pasang ditampilkan`;
        }

        // ============================================================
        // RENDER
        // ============================================================
        function renderTable(data) {
            const tbody = document.getElementById('pattern-tbody');

            const statBot   = document.getElementById('stat-bot');
            const statUp    = document.getElementById('stat-up');
            const statDown  = document.getElementById('stat-down');
            const statNotif = document.getElementById('stat-notif');

            if (!data || data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="9" class="px-4 py-8 text-center text-slate-400 font-medium">⏳ Menunggu data dari bot trading...</td></tr>`;
                if (statBot) statBot.textContent = '0';
                document.getElementById('rows-info').textContent = '0 pasang ditampilkan';
                return;
            }

            // Stats
            if (statBot)   statBot.textContent   = data.length;
            if (statUp)    statUp.textContent     = data.filter(d => d.pattern_type === 'UP').length;
            if (statDown)  statDown.textContent   = data.filter(d => d.pattern_type === 'DOWN').length;
            if (statNotif) statNotif.textContent  = data.filter(d => d.notif_sent).length;

            // Sort: pola aktif dulu, lalu alphabetical
            data.sort((a, b) => {
                const prio = { UP: 0, DOWN: 1, NONE: 2 };
                const pa = prio[a.pattern_type] ?? 2;
                const pb = prio[b.pattern_type] ?? 2;
                if (pa !== pb) return pa - pb;
                return a.market.localeCompare(b.market);
            });

            let html = '';
            data.forEach((item, idx) => {
                const hasPattern = item.pattern_type !== 'NONE';
                const rowClass = item.pattern_type === 'UP'   ? 'row-up'   :
                                 item.pattern_type === 'DOWN' ? 'row-down' : '';

                // Blok waktu label
                const blokLabel = item.waktu_block && item.waktu_block !== '-'
                    ? `<span class="font-semibold">${item.waktu_block}</span> WIB`
                    : '<span class="text-slate-400">—</span>';

                html += `
                <tr class="${rowClass} hover:bg-slate-50 transition-colors" data-market="${item.market}">
                    <td class="px-3 py-2 text-xs text-slate-400">${idx + 1}</td>
                    <td class="px-3 py-2">
                        <span class="text-xs font-${hasPattern ? 'bold' : 'semibold'} text-slate-${hasPattern ? '800' : '600'}">
                            ${item.market}
                        </span>
                    </td>
                    <td class="px-3 py-2 text-center">${candleBadgeWithMinute(item.c1, item.waktu_block, 0)}</td>
                    <td class="px-3 py-2 text-center">${candleBadgeWithMinute(item.c2, item.waktu_block, 1)}</td>
                    <td class="px-3 py-2 text-center">${candleBadgeWithMinute(item.c3, item.waktu_block, 2)}</td>
                    <td class="px-3 py-2 text-center">${candleBadgeWithMinute(item.c4, item.waktu_block, 3)}</td>
                    <td class="px-3 py-2 text-center">${candleBadgeWithMinute(item.c5, item.waktu_block, 4)}</td>
                    <td class="px-3 py-2 text-center text-xs text-slate-600">${blokLabel}</td>
                    <td class="px-3 py-2 text-center">${patternPill(item.pattern_type)}</td>
                </tr>`;
            });

            tbody.innerHTML = html;
            document.getElementById('rows-info').textContent = `${data.length} pasang ditampilkan`;
            document.getElementById('updated-at').textContent = new Date().toLocaleTimeString('id-ID');

            // Re-apply search filter
            const q = document.getElementById('search-input').value;
            if (q) filterTable();
        }

        // ============================================================
        // FETCH DATA
        // ============================================================
        let pollingInterval;

        function fetchData() {
            fetch(`${API_BASE}/user2_data`)
                .then(res => res.json())
                .then(json => {
                    if (json.success) renderTable(json.data || []);
                })
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
                    .catch(() => {});
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
