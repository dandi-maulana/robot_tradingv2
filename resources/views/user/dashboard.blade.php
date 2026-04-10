<!DOCTYPE html>
<html lang="id">

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
                    fontFamily: {
                        sans: ['Inter', 'sans-serif']
                    },
                    colors: {
                        gojek: {
                            DEFAULT: '#00aa13',
                            dark: '#00880f',
                            light: '#e6f6e8'
                        },
                        red: {
                            DEFAULT: '#ee2737',
                            dark: '#c81d28',
                            light: '#fdedee'
                        },
                        dark: '#1c1c1c',
                        graybg: '#f4f5f7'
                    }
                }
            }
        }

        // API_BASE selalu mengarah ke Python service di port 5000
        var API_BASE = window.location.hostname === "127.0.0.1" ?
            window.location.protocol + "//" + window.location.hostname + ":5000/api" :
            "/api";
    </script>

    <style>
        body {
            background-color: #f4f5f7;
        }

        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }

        /* ===== FADE IN ===== */
        .fade-in {
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ===== STREAK ITEMS ===== */
        .connected-glow {
            animation: greenPulse 2.2s infinite ease-in-out;
        }

        .danger-glow {
            animation: redPulse 1.4s infinite ease-in-out;
        }

        @keyframes redPulse {
            0%   { box-shadow: 0 0 0 rgba(239, 68, 68, 0.2); }
            50%  { box-shadow: 0 0 16px rgba(239, 68, 68, 0.6); }
            100% { box-shadow: 0 0 0 rgba(239, 68, 68, 0.2); }
        }

        @keyframes greenPulse {
            0%   { box-shadow: 0 0 0 rgba(0, 170, 19, 0.2); }
            50%  { box-shadow: 0 0 12px rgba(0, 170, 19, 0.4); }
            100% { box-shadow: 0 0 0 rgba(0, 170, 19, 0.2); }
        }
    </style>
</head>

<body class="text-dark antialiased flex flex-col min-h-screen">

    <!-- ============================================================
         NAVBAR (sama dengan admin, tapi tanpa kontrol)
    ============================================================ -->
    <nav class="bg-white sticky top-0 z-50 shadow-sm px-4 sm:px-6 py-3 border-b border-gray-100">
        <div class="flex items-center justify-between">

            {{-- LOGO & BRAND --}}
            <div class="flex items-center gap-2 sm:gap-3">
                <div class="bg-gojek text-white p-1.5 sm:p-2 rounded-xl shadow-sm">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                        </path>
                    </svg>
                </div>
                <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-dark">
                    RODIS <span class="text-gojek font-semibold text-sm sm:text-lg ml-1 hidden lg:inline">(RObot DISana)</span>
                </h1>
            </div>

            {{-- RIGHT SIDE: User Info + Logout --}}
            <div class="flex items-center gap-2 sm:gap-3">


                {{-- Logout --}}
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="bg-red-50 hover:bg-red-100 text-red-600 px-3 py-1.5 rounded-xl text-xs font-bold transition-colors flex items-center gap-1.5 border border-red-100 shadow-sm">
                        🚪 <span class="hidden sm:inline">Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- ============================================================
         MAIN CONTENT
    ============================================================ -->
    <main class="flex-grow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8 w-full relative z-20 pt-6">

            {{-- INFO BANNER --}}
            <div class="mb-6 gap-4 border-b border-gray-100 pb-5 fade-in">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h3 class="text-xl font-bold text-dark hidden md:block mb-2">Dashboard</h3>
                        <div class="flex flex-wrap items-center gap-2 text-xs font-medium">
                            <span class="px-2.5 py-1.5 bg-indigo-50 border border-indigo-100 text-indigo-800 rounded-lg flex items-center gap-1.5 shadow-sm">
                                🤖 Bot Berjalan: <b id="lbl-bot-count" class="text-indigo-600 text-sm">0/27</b>
                            </span>
                            <span class="px-2.5 py-1.5 bg-gray-50 border border-gray-200 text-gray-600 rounded-lg flex items-center gap-1.5 shadow-sm">
                                📲 Sinyal Massal: <b id="lbl-tg-count" class="text-gray-400 font-bold">OFF</b>
                            </span>
                        </div>
                    </div>

                    <div class="text-xs font-bold text-blue-700 bg-blue-50 border border-blue-200 px-3 py-1.5 rounded-lg flex items-center gap-1.5 shadow-sm">
                        ⏰ <span id="realtime-clock">Memuat Waktu...</span>
                    </div>
                </div>
            </div>

            {{-- LIVE FALSE STREAK SECTION --}}
            <div class="grid grid-cols-1 gap-5 mb-6 w-full items-stretch fade-in">
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 w-full flex flex-col"
                    id="live-streak-container">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-3 border-b border-gray-50 pb-3 gap-2">
                        <h3 class="text-sm font-extrabold text-dark flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-red animate-pulse shadow-[0_0_8px_#ef4444]"></span>
                            Live False Streak (Backtest Monitor)
                        </h3>
                        <span class="px-2.5 py-1 bg-gray-50 border border-gray-200 text-gray-500 rounded-lg text-[10px] font-bold shadow-sm flex items-center gap-1">
                            <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            READ-ONLY
                        </span>
                    </div>
                    <div id="streak-list" class="
                        grid
                        grid-cols-2
                        sm:grid-cols-3
                        md:grid-cols-4
                        lg:grid-cols-5
                        xl:grid-cols-6
                        gap-2
                        pt-1
                        min-h-[30px]
                    ">
                        <span class="text-xs text-gray-400 font-medium italic col-span-full py-8 text-center">
                            ⏳ Menunggu Admin mengaktifkan bot trading...
                        </span>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- ============================================================
         FOOTER (sama dengan admin)
    ============================================================ -->
    <footer class="bg-white border-t border-gray-200 py-6 mt-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center">
            <p class="text-gray-500 text-sm font-medium">
                &copy; <script>document.write(new Date().getFullYear())</script>
                <strong>RODIS</strong> - Robot Trading. All rights reserved.
            </p>
            <div class="flex space-x-6 mt-4 md:mt-0 text-sm font-medium text-gray-400">
            </div>
        </div>
    </footer>

    <!-- ============================================================
         SCRIPTS
    ============================================================ -->
    <script>
        // ============================================================
        // MARKET DEFINITIONS (sama persis dengan admin)
        // ============================================================
        const allMarkets = [
            { id: "Asia Composite Index", name: "Asia Index", icon: "🌏", cat: "24 Jam FTT" },
            { id: "Europe Composite Index", name: "Europe Index", icon: "🌍", cat: "24 Jam FTT" },
            { id: "Commodity Composite", name: "Commodity", icon: "🌾", cat: "24 Jam FTT" },
            { id: "Crypto Composite Index", name: "Crypto Index", icon: "₿", cat: "24 Jam FTT" },
            { id: "EUR/USD OTC", name: "EUR/USD OTC", icon: "🇪🇺", cat: "OTC" },
            { id: "GBP/USD OTC", name: "GBP/USD OTC", icon: "🇬🇧", cat: "OTC" },
            { id: "USD/JPY OTC", name: "USD/JPY OTC", icon: "🇯🇵", cat: "OTC" },
            { id: "AUD/USD OTC", name: "AUD/USD OTC", icon: "🇦🇺", cat: "OTC" },
            { id: "NZD/USD OTC", name: "NZD/USD OTC", icon: "🇳🇿", cat: "OTC" },
            { id: "USD/CAD OTC", name: "USD/CAD OTC", icon: "🇨🇦", cat: "OTC" },
            { id: "USD/CHF OTC", name: "USD/CHF OTC", icon: "🇨🇭", cat: "OTC" },
            { id: "EUR/JPY OTC", name: "EUR/JPY OTC", icon: "💶", cat: "OTC" },
            { id: "GBP/JPY OTC", name: "GBP/JPY OTC", icon: "💷", cat: "OTC" },
            { id: "AUD/JPY OTC", name: "AUD/JPY OTC", icon: "🇦🇺", cat: "OTC" },
            { id: "CAD/JPY OTC", name: "CAD/JPY OTC", icon: "🇨🇦", cat: "OTC" },
            { id: "NZD/JPY OTC", name: "NZD/JPY OTC", icon: "🇳🇿", cat: "OTC" },
            { id: "CHF/JPY OTC", name: "CHF/JPY OTC", icon: "🇨🇭", cat: "OTC" },
            { id: "EUR/GBP OTC", name: "EUR/GBP OTC", icon: "💶", cat: "OTC" },
            { id: "EUR/AUD OTC", name: "EUR/AUD OTC", icon: "💶", cat: "OTC" },
            { id: "EUR/CAD OTC", name: "EUR/CAD OTC", icon: "💶", cat: "OTC" },
            { id: "EUR/CHF OTC", name: "EUR/CHF OTC", icon: "💶", cat: "OTC" },
            { id: "GBP/AUD OTC", name: "GBP/AUD OTC", icon: "💷", cat: "OTC" },
            { id: "GBP/CAD OTC", name: "GBP/CAD OTC", icon: "💷", cat: "OTC" },
            { id: "GBP/CHF OTC", name: "GBP/CHF OTC", icon: "💷", cat: "OTC" },
            { id: "AUD/CAD OTC", name: "AUD/CAD OTC", icon: "🇦🇺", cat: "OTC" },
            { id: "AUD/CHF OTC", name: "AUD/CHF OTC", icon: "🇦🇺", cat: "OTC" },
            { id: "CAD/CHF OTC", name: "CAD/CHF OTC", icon: "🇨🇦", cat: "OTC" },
        ];

        // ============================================================
        // REALTIME CLOCK
        // ============================================================
        function startRealtimeClock() {
            setInterval(() => {
                const clockEl = document.getElementById('realtime-clock');
                if (clockEl) {
                    const now = new Date();
                    const hh = String(now.getHours()).padStart(2, '0');
                    const mm = String(now.getMinutes()).padStart(2, '0');
                    const ss = String(now.getSeconds()).padStart(2, '0');
                    clockEl.innerText = `${hh}:${mm}:${ss} WIB`;
                }
            }, 1000);
        }

        // ============================================================
        // DASHBOARD POLLING (READ-ONLY — sama format dengan admin)
        // ============================================================
        let pollingInterval;

        function startPolling() {
            refreshData();
            pollingInterval = setInterval(refreshData, 3000);
        }

        function refreshData() {
            fetch(`${API_BASE}/status_all`)
                .then(res => res.json())
                .then(data => {
                    const activeMarkets = data.active_markets || [];
                    const streakList = document.getElementById('streak-list');
                    const botCountEl = document.getElementById('lbl-bot-count');

                    if (botCountEl) botCountEl.innerText = `${activeMarkets.length}/27`;

                    // Sync telegram count
                    const tgCountEl = document.getElementById('lbl-tg-count');
                    if (tgCountEl) {
                        let tgCount = data.tg_active_count || 0;
                        if (tgCount > 0) {
                            tgCountEl.innerText = `ON (${tgCount} Market)`;
                            tgCountEl.className = 'text-blue-600 font-extrabold';
                        } else {
                            tgCountEl.innerText = 'OFF';
                            tgCountEl.className = 'text-gray-400 font-bold';
                        }
                    }

                    if (!streakList) return;

                    if (!data.market_streaks || Object.keys(data.market_streaks).length === 0) {
                        streakList.innerHTML = `
                            <span class="text-xs text-gray-400 font-medium italic col-span-full py-8 text-center">
                                ⏳ Menunggu Admin mengaktifkan bot trading...
                            </span>`;
                        return;
                    }

                    // Sort by streak descending (sama persis format admin)
                    let sortedMarkets = Object.keys(data.market_streaks)
                        .sort((a, b) => data.market_streaks[b] - data.market_streaks[a]);

                    const highestStreak = sortedMarkets.length > 0 ?
                        data.market_streaks[sortedMarkets[0]] : 0;

                    // Hitung apakah semua market sudah terkoneksi (sama dengan admin)
                    const totalMarket = allMarkets.length;
                    const activeCount = activeMarkets.length;
                    const allConnected = (activeCount === totalMarket && totalMarket > 0);

                    streakList.innerHTML = '';

                    sortedMarkets.forEach(mkt => {
                        let streak = data.market_streaks[mkt];
                        let mktObj = allMarkets.find(x => x.id === mkt);
                        let mktName = mktObj ? mktObj.name : mkt;

                        // Warna sama persis dengan admin dashboard
                        let colorClass = 'bg-gray-50 text-gray-500 border-gray-200';

                        if (streak >= 7)
                            colorClass = 'bg-red-100 text-red-700 border-red-300 font-extrabold';
                        else if (streak >= 5)
                            colorClass = 'bg-orange-100 text-orange-700 border-orange-300 font-bold';
                        else if (streak >= 3)
                            colorClass = 'bg-yellow-100 text-yellow-700 border-yellow-300 font-bold';
                        else if (streak >= 1)
                            colorClass = 'bg-blue-50 text-blue-600 border-blue-200';

                        // Warna hijau saat semua market terkoneksi (sama dengan admin)
                        let connectedClass = allConnected ?
                            'bg-green-100 text-green-800 border-green-300 font-bold' :
                            '';

                        let dangerGlow = (streak === highestStreak && streak >= 7) ? 'danger-glow' : '';

                        streakList.innerHTML += `
                            <div class="w-full px-3 py-1.5 rounded-lg border text-[11px] flex items-center justify-between
                                        ${colorClass} ${connectedClass} ${dangerGlow}
                                        transition-all duration-300 cursor-default hover:scale-[1.02] hover:shadow-md">
                                <span class="truncate font-semibold">${mktName}</span>
                                <span class="bg-white/90 px-2 py-0.5 rounded text-[10px] uppercase tracking-wider border border-white/50">False: ${streak}</span>
                            </div>`;
                    });
                })
                .catch(err => {
                    console.error("Polling error:", err);
                });
        }

        // ============================================================
        // INIT
        // ============================================================
        window.onload = function () {
            startRealtimeClock();
            startPolling();
        };
    </script>
</body>

</html>
