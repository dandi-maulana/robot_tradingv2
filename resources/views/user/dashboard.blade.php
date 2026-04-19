<!DOCTYPE html>
<html lang="id" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RODIS - User Dashboard</title>
    <meta name="description" content="RODIS User Dashboard - Monitoring Live False Streak real-time">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
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
        * { box-sizing: border-box; }

        :root { --t: 0.35s; }

        [data-theme="dark"] {
            --bg: #0b0e1a; --bg-card: rgba(255,255,255,0.03); --bg-card-hover: rgba(255,255,255,0.06);
            --bg-nav: rgba(11,14,26,0.88); --border: rgba(255,255,255,0.06); --border-s: rgba(255,255,255,0.04);
            --t1: #f1f5f9; --t2: rgba(255,255,255,0.55); --t3: rgba(255,255,255,0.25); --t4: rgba(255,255,255,0.12);
            --green: #34d399; --green-bg: rgba(16,185,129,0.1); --green-bd: rgba(16,185,129,0.18);
            --red: #fca5a5; --red-bg: rgba(239,68,68,0.1); --red-bd: rgba(239,68,68,0.18);
            --indigo: #818cf8; --amber: #fbbf24; --blue: #60a5fa;
            --badge-green-bg: rgba(16,185,129,0.12); --badge-green-t: #6ee7b7; --badge-green-bd: rgba(16,185,129,0.22);
            --badge-red-bg: rgba(239,68,68,0.12); --badge-red-t: #fca5a5; --badge-red-bd: rgba(239,68,68,0.22);
            --input-bg: rgba(255,255,255,0.05); --input-bd: rgba(255,255,255,0.08);
            --scroll: rgba(255,255,255,0.08);
            --streak-0: rgba(255,255,255,0.04); --streak-0-t: rgba(255,255,255,0.4); --streak-0-bd: rgba(255,255,255,0.08);
            --streak-1: rgba(59,130,246,0.1); --streak-1-t: #93c5fd; --streak-1-bd: rgba(59,130,246,0.2);
            --streak-3: rgba(234,179,8,0.1); --streak-3-t: #fde047; --streak-3-bd: rgba(234,179,8,0.2);
            --streak-5: rgba(249,115,22,0.1); --streak-5-t: #fdba74; --streak-5-bd: rgba(249,115,22,0.2);
            --streak-7: rgba(239,68,68,0.12); --streak-7-t: #fca5a5; --streak-7-bd: rgba(239,68,68,0.25);
            --streak-false-bg: rgba(255,255,255,0.06); --streak-false-t: rgba(255,255,255,0.5);
        }

        [data-theme="light"] {
            --bg: #f0f2f5; --bg-card: #ffffff; --bg-card-hover: #f8fafc;
            --bg-nav: rgba(255,255,255,0.92); --border: #e2e8f0; --border-s: #f1f5f9;
            --t1: #0f172a; --t2: #64748b; --t3: #94a3b8; --t4: #cbd5e1;
            --green: #059669; --green-bg: #ecfdf5; --green-bd: #a7f3d0;
            --red: #dc2626; --red-bg: #fef2f2; --red-bd: #fecaca;
            --indigo: #6366f1; --amber: #f59e0b; --blue: #3b82f6;
            --badge-green-bg: #d1fae5; --badge-green-t: #065f46; --badge-green-bd: #a7f3d0;
            --badge-red-bg: #fee2e2; --badge-red-t: #991b1b; --badge-red-bd: #fca5a5;
            --input-bg: #ffffff; --input-bd: #e2e8f0;
            --scroll: #cbd5e1;
            --streak-0: #f8fafc; --streak-0-t: #64748b; --streak-0-bd: #e2e8f0;
            --streak-1: #eff6ff; --streak-1-t: #2563eb; --streak-1-bd: #bfdbfe;
            --streak-3: #fefce8; --streak-3-t: #a16207; --streak-3-bd: #fde68a;
            --streak-5: #fff7ed; --streak-5-t: #c2410c; --streak-5-bd: #fed7aa;
            --streak-7: #fef2f2; --streak-7-t: #dc2626; --streak-7-bd: #fecaca;
            --streak-false-bg: rgba(0,0,0,0.04); --streak-false-t: #64748b;
        }

        body { background: var(--bg); color: var(--t1); transition: background var(--t), color var(--t); }

        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--scroll); border-radius: 10px; }

        .fade-in { animation: fadeIn .3s ease-out; }
        @keyframes fadeIn { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:translateY(0); } }

        .card { background: var(--bg-card); border: 1px solid var(--border); transition: background var(--t), border-color var(--t); }
        .nav-bar { background: var(--bg-nav); backdrop-filter: blur(20px); border-bottom: 1px solid var(--border); transition: background var(--t), border-color var(--t); }

        .danger-glow { animation: redPulse 1.4s infinite ease-in-out; }
        @keyframes redPulse {
            0%, 100% { box-shadow: 0 0 0 rgba(239,68,68,0.2); }
            50% { box-shadow: 0 0 16px rgba(239,68,68,0.5); }
        }

        .connected-glow { animation: greenPulse 2.2s infinite ease-in-out; }
        @keyframes greenPulse {
            0%, 100% { box-shadow: 0 0 0 rgba(16,185,129,0.2); }
            50% { box-shadow: 0 0 12px rgba(16,185,129,0.4); }
        }

        .dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
        .dot-red { background: #ef4444; animation: dotR 1.5s ease-in-out infinite; }
        @keyframes dotR {
            0%, 100% { box-shadow: 0 0 6px rgba(239,68,68,0.5); opacity: 1; }
            50% { box-shadow: 0 0 2px rgba(239,68,68,0.2); opacity: 0.4; }
        }

        /* Theme toggle */
        .theme-toggle { width:52px; height:28px; border-radius:14px; cursor:pointer; position:relative; transition:background 0.4s; border:none; outline:none; padding:0; }
        [data-theme="dark"] .theme-toggle { background: linear-gradient(135deg, #1e293b, #334155); box-shadow: inset 0 1px 3px rgba(0,0,0,0.3); }
        [data-theme="light"] .theme-toggle { background: linear-gradient(135deg, #bfdbfe, #93c5fd); box-shadow: inset 0 1px 3px rgba(0,0,0,0.08); }
        .toggle-knob { position:absolute; top:3px; width:22px; height:22px; border-radius:50%; transition: left 0.4s cubic-bezier(0.68,-0.15,0.32,1.15), background 0.4s, box-shadow 0.4s; display:flex; align-items:center; justify-content:center; font-size:12px; }
        [data-theme="dark"] .toggle-knob { left:3px; background:linear-gradient(135deg,#4f46e5,#6366f1); box-shadow:0 2px 8px rgba(99,102,241,0.4); }
        [data-theme="light"] .toggle-knob { left:27px; background:linear-gradient(135deg,#f59e0b,#fbbf24); box-shadow:0 2px 8px rgba(245,158,11,0.4); }

        /* Streak item */
        .streak-item {
            transition: all 0.2s ease;
            cursor: default;
        }
        .streak-item:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
    </style>
</head>

<body class="antialiased flex flex-col min-h-screen">

    <!-- NAVBAR -->
    <nav class="nav-bar sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2.5 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background: linear-gradient(135deg, #10b981, #059669);">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <h1 class="text-sm sm:text-base font-bold tracking-tight">
                    RODIS <span style="color: var(--green);" class="font-semibold hidden lg:inline">(RObot DISana)</span>
                </h1>
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                <button class="theme-toggle" onclick="toggleTheme()" title="Ubah tema">
                    <span class="toggle-knob"><span id="theme-icon">🌙</span></span>
                </button>

                <a href="{{ route('user.history') }}"
                    class="text-[11px] sm:text-xs font-bold px-2.5 py-1.5 rounded-lg transition-all"
                    style="color: var(--green); background: var(--green-bg); border: 1px solid var(--green-bd);">
                    📈 History
                </a>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-[11px] sm:text-xs font-semibold px-2.5 py-1.5 rounded-lg transition-all"
                        style="color: var(--red); background: var(--red-bg); border: 1px solid var(--red-bd);">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- MAIN -->
    <main class="flex-grow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 space-y-4">

            <!-- HEADER + STATS -->
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 fade-in">
                <div>
                    <h2 class="text-lg sm:text-xl font-extrabold">Pusat Kendali Market</h2>
                    <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                        <span class="card rounded-lg px-2.5 py-1 text-[11px] font-semibold flex items-center gap-1.5"
                              style="color: var(--indigo);">
                            🤖 Bot Berjalan: <b id="lbl-bot-count" class="text-sm">0/36</b>
                        </span>
                        <span class="card rounded-lg px-2.5 py-1 text-[11px] font-semibold flex items-center gap-1.5"
                              style="color: var(--t3);">
                            📲 Sinyal Massal: <b id="lbl-tg-count" style="color: var(--t3);">OFF</b>
                        </span>
                    </div>
                </div>
                <span id="realtime-clock" class="text-xs font-mono" style="color: var(--t3);">Memuat Waktu...</span>
            </div>

            <!-- LIVE FALSE STREAK -->
            <div class="card rounded-2xl p-4 sm:p-5 fade-in" id="live-streak-container">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-3 pb-3 gap-2"
                     style="border-bottom: 1px solid var(--border-s);">
                    <h3 class="text-sm font-extrabold flex items-center gap-2">
                        <span class="dot dot-red"></span>
                        Live False Streak (Backtest Monitor)
                    </h3>
                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold flex items-center gap-1"
                          style="color: var(--t3); background: var(--bg-card); border: 1px solid var(--border);">
                        👁 READ-ONLY
                    </span>
                </div>
                <div id="streak-list" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-2 pt-1 min-h-[30px]">
                    <span class="text-xs font-medium italic col-span-full py-8 text-center" style="color: var(--t4);">
                        ⏳ Menunggu Admin mengaktifkan bot trading...
                    </span>
                </div>
            </div>

        </div>
    </main>

    <!-- FOOTER -->
    <footer style="border-top: 1px solid var(--border-s);" class="py-4 mt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center">
            <p class="text-[11px]" style="color: var(--t4);">
                &copy; <script>document.write(new Date().getFullYear())</script>
                <span style="color: var(--t3);" class="font-semibold">RODIS</span> - Robot Trading. All rights reserved.
            </p>
        </div>
    </footer>

    <script>
        // THEME
        function toggleTheme() {
            const html = document.documentElement;
            const icon = document.getElementById('theme-icon');
            if (html.getAttribute('data-theme') === 'dark') {
                html.setAttribute('data-theme', 'light'); icon.textContent = '☀️';
                localStorage.setItem('rodis-theme', 'light');
            } else {
                html.setAttribute('data-theme', 'dark'); icon.textContent = '🌙';
                localStorage.setItem('rodis-theme', 'dark');
            }
        }
        (function() {
            const saved = localStorage.getItem('rodis-theme');
            if (saved) {
                document.documentElement.setAttribute('data-theme', saved);
                const icon = document.getElementById('theme-icon');
                if (icon) icon.textContent = saved === 'dark' ? '🌙' : '☀️';
            }
        })();

        // MARKETS
        const allMarkets = [
            {id:"Asia Composite Index",name:"Asia Index",icon:"🌏",cat:"24 Jam FTT"},
            {id:"Europe Composite Index",name:"Europe Index",icon:"🌍",cat:"24 Jam FTT"},
            {id:"Commodity Composite",name:"Commodity",icon:"🌾",cat:"24 Jam FTT"},
            {id:"Crypto Composite Index",name:"Crypto Index",icon:"₿",cat:"24 Jam FTT"},
            {id:"EUR/USD OTC",name:"EUR/USD OTC",icon:"🇪🇺",cat:"OTC"},{id:"GBP/USD OTC",name:"GBP/USD OTC",icon:"🇬🇧",cat:"OTC"},
            {id:"USD/JPY OTC",name:"USD/JPY OTC",icon:"🇯🇵",cat:"OTC"},{id:"AUD/USD OTC",name:"AUD/USD OTC",icon:"🇦🇺",cat:"OTC"},
            {id:"NZD/USD OTC",name:"NZD/USD OTC",icon:"🇳🇿",cat:"OTC"},{id:"USD/CAD OTC",name:"USD/CAD OTC",icon:"🇨🇦",cat:"OTC"},
            {id:"USD/CHF OTC",name:"USD/CHF OTC",icon:"🇨🇭",cat:"OTC"},{id:"EUR/JPY OTC",name:"EUR/JPY OTC",icon:"💶",cat:"OTC"},
            {id:"GBP/JPY OTC",name:"GBP/JPY OTC",icon:"💷",cat:"OTC"},{id:"AUD/JPY OTC",name:"AUD/JPY OTC",icon:"🇦🇺",cat:"OTC"},
            {id:"CAD/JPY OTC",name:"CAD/JPY OTC",icon:"🇨🇦",cat:"OTC"},{id:"NZD/JPY OTC",name:"NZD/JPY OTC",icon:"🇳🇿",cat:"OTC"},
            {id:"CHF/JPY OTC",name:"CHF/JPY OTC",icon:"🇨🇭",cat:"OTC"},{id:"EUR/GBP OTC",name:"EUR/GBP OTC",icon:"💶",cat:"OTC"},
            {id:"EUR/AUD OTC",name:"EUR/AUD OTC",icon:"💶",cat:"OTC"},{id:"EUR/CAD OTC",name:"EUR/CAD OTC",icon:"💶",cat:"OTC"},
            {id:"EUR/CHF OTC",name:"EUR/CHF OTC",icon:"💶",cat:"OTC"},{id:"GBP/AUD OTC",name:"GBP/AUD OTC",icon:"💷",cat:"OTC"},
            {id:"GBP/CAD OTC",name:"GBP/CAD OTC",icon:"💷",cat:"OTC"},{id:"GBP/CHF OTC",name:"GBP/CHF OTC",icon:"💷",cat:"OTC"},
            {id:"AUD/CAD OTC",name:"AUD/CAD OTC",icon:"🇦🇺",cat:"OTC"},{id:"AUD/CHF OTC",name:"AUD/CHF OTC",icon:"🇦🇺",cat:"OTC"},
            {id:"CAD/CHF OTC",name:"CAD/CHF OTC",icon:"🇨🇦",cat:"OTC"},
        ];

        // CLOCK
        function startRealtimeClock() {
            setInterval(() => {
                const now = new Date();
                document.getElementById('realtime-clock').innerText =
                    [now.getHours(), now.getMinutes(), now.getSeconds()].map(n => String(n).padStart(2,'0')).join(':') + ' WIB';
            }, 1000);
        }

        // POLLING
        let pollingInterval;
        function startPolling() { refreshData(); pollingInterval = setInterval(refreshData, 3000); }

        function refreshData() {
            fetch(`${API_BASE}/status_all`).then(res => res.json()).then(data => {
                const activeMarkets = data.active_markets || [];
                const streakList = document.getElementById('streak-list');
                const botCountEl = document.getElementById('lbl-bot-count');

                if (botCountEl) botCountEl.innerText = `${activeMarkets.length}/36`;

                const tgCountEl = document.getElementById('lbl-tg-count');
                if (tgCountEl) {
                    let tgCount = data.tg_active_count || 0;
                    if (tgCount > 0) {
                        tgCountEl.innerText = `ON (${tgCount} Market)`;
                        tgCountEl.style.color = 'var(--blue)';
                    } else {
                        tgCountEl.innerText = 'OFF';
                        tgCountEl.style.color = 'var(--t3)';
                    }
                }

                if (!streakList) return;

                if (!data.market_streaks || Object.keys(data.market_streaks).length === 0) {
                    streakList.innerHTML = `<span class="text-xs font-medium italic col-span-full py-8 text-center" style="color:var(--t4);">⏳ Menunggu Admin mengaktifkan bot trading...</span>`;
                    return;
                }

                let sortedMarkets = Object.keys(data.market_streaks).sort((a,b) => data.market_streaks[b] - data.market_streaks[a]);
                const highestStreak = sortedMarkets.length > 0 ? data.market_streaks[sortedMarkets[0]] : 0;
                const totalMarket = allMarkets.length;
                const activeCount = activeMarkets.length;
                const allConnected = (activeCount === totalMarket && totalMarket > 0);

                streakList.innerHTML = '';

                sortedMarkets.forEach(mkt => {
                    let streak = data.market_streaks[mkt];
                    let mktObj = allMarkets.find(x => x.id === mkt);
                    let mktName = mktObj ? mktObj.name : mkt;

                    let bgVar, tVar, bdVar;
                    if (streak >= 7) { bgVar = '--streak-7'; tVar = '--streak-7-t'; bdVar = '--streak-7-bd'; }
                    else if (streak >= 5) { bgVar = '--streak-5'; tVar = '--streak-5-t'; bdVar = '--streak-5-bd'; }
                    else if (streak >= 3) { bgVar = '--streak-3'; tVar = '--streak-3-t'; bdVar = '--streak-3-bd'; }
                    else if (streak >= 1) { bgVar = '--streak-1'; tVar = '--streak-1-t'; bdVar = '--streak-1-bd'; }
                    else { bgVar = '--streak-0'; tVar = '--streak-0-t'; bdVar = '--streak-0-bd'; }

                    let dangerGlow = (streak === highestStreak && streak >= 7) ? 'danger-glow' : '';

                    if (allConnected) {
                        bgVar = '--green-bg'; tVar = '--green'; bdVar = '--green-bd';
                        dangerGlow = '';
                    }

                    streakList.innerHTML += `
                        <div class="streak-item w-full px-3 py-1.5 rounded-lg text-[11px] flex items-center justify-between ${dangerGlow}"
                             style="background: var(${bgVar}); color: var(${tVar}); border: 1px solid var(${bdVar}); transition: background var(--t), color var(--t), border-color var(--t);">
                            <span class="truncate font-semibold">${mktName}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] uppercase tracking-wider font-bold"
                                  style="background: var(--streak-false-bg); color: var(--streak-false-t);">False: ${streak}</span>
                        </div>`;
                });
            }).catch(err => console.error("Polling error:", err));
        }

        // SESSION CHECK
        function startSessionCheck() {
            setInterval(() => {
                fetch('/user/check-session', { credentials: 'same-origin' })
                    .then(res => res.json())
                    .then(data => {
                        if (!data.valid) {
                            clearInterval(pollingInterval);
                            alert('⚠️ Sesi Anda telah berakhir karena akun ini login di perangkat lain.');
                            window.location.href = '/login';
                        }
                    }).catch(() => {});
            }, 5000);
        }

        window.onload = function() {
            startRealtimeClock();
            startPolling();
            startSessionCheck();
        };
    </script>
</body>
</html>