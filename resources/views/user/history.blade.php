<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RODIS - Trade History</title>
    <meta name="description" content="RODIS Trade History - Melihat history open posisi per market">

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
                        dark: '#1c1c1c',
                        graybg: '#f4f5f7'
                    }
                }
            }
        }
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

        .fade-in {
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .pulse-dot {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }
    </style>
</head>

<body class="text-dark antialiased flex flex-col min-h-screen">

    <!-- NAVBAR -->
    <nav class="bg-white sticky top-0 z-50 shadow-sm px-4 sm:px-6 py-3 border-b border-gray-100">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2 sm:gap-3">
                <div class="bg-gojek text-white p-1.5 sm:p-2 rounded-xl shadow-sm">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                        </path>
                    </svg>
                </div>
                <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-dark">
                    RODIS <span class="text-gojek font-semibold text-sm sm:text-lg ml-1 hidden lg:inline">(RObot
                        DISana)</span>
                </h1>
            </div>

            <div class="flex items-center gap-2 sm:gap-4">
                <a href="{{ route('user.dashboard') }}"
                    class="text-xs sm:text-sm font-bold text-gojek hover:text-gojek-dark px-3 py-1.5 rounded-lg hover:bg-gojek-light transition-colors">📊
                    Dashboard</a>
                <div class="w-px h-6 bg-gray-200"></div>
                <a href="{{ route('user.history') }}"
                    class="text-xs sm:text-sm font-bold text-gojek bg-gojek-light px-3 py-1.5 rounded-lg border border-gojek-dark">📈
                    History</a>
                <form action="{{ route('logout') }}" method="POST" class="ml-2">
                    @csrf
                    <button type="submit"
                        class="bg-red-50 hover:bg-red-100 text-red-600 px-3 py-1.5 rounded-xl text-xs font-bold transition-colors flex items-center gap-1.5 border border-red-100 shadow-sm">🚪
                        <span class="hidden sm:inline">Logout</span></button>
                </form>
            </div>
        </div>
    </nav>

    <!-- MAIN CONTENT -->
    <main class="flex-grow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8 w-full relative z-20 pt-6">

            <!-- INFO BANNER -->
            <div class="fade-in">
            </div>

            <!-- HISTORY SECTION -->
            <div class="grid grid-cols-1 gap-5 mb-6 w-full items-stretch fade-in">
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 w-full flex flex-col">
                    <!-- SUMMARY CARDS (MOVED TO TOP) -->
                    <div class="mb-6 grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="text-xs font-bold text-blue-600 mb-1">Total Market Aktif Hari Ini</div>
                            <div class="text-2xl font-extrabold text-blue-700" id="summary-today">0</div>
                        </div>
                        <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4">
                            <div class="text-xs font-bold text-indigo-600 mb-1">Total Open Posisi Hari Ini</div>
                            <div class="text-2xl font-extrabold text-indigo-700" id="summary-total-today">0</div>
                        </div>
                        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                            <div class="text-xs font-bold text-purple-600 mb-1">Total Open Posisi Bulan Ini</div>
                            <div class="text-2xl font-extrabold text-purple-700" id="summary-total-month">0</div>
                        </div>
                    </div>

                    <div
                        class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 border-b border-gray-50 pb-4 gap-2">
                        <h3 class="text-sm font-extrabold text-dark flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-gojek pulse-dot"></span>
                            Statistik Open Posisi Per Market
                        </h3>
                    </div>

                    <!-- FILTERS -->
                    <div class="mb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                        <div class="relative">
                            <button id="filter-dropdown-btn"
                                class="px-3 py-1.5 bg-gojek text-white rounded-lg text-xs font-bold border border-gojek-dark shadow-sm flex items-center gap-2 hover:bg-gojek-dark transition-colors">
                                <span id="filter-label">📊 Semua Market</span>
                                <span>▼</span>
                            </button>
                            <div id="filter-dropdown-menu"
                                class="hidden absolute top-full left-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-50 min-w-max">
                                <button
                                    class="filter-option w-full text-left px-4 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50 transition-colors border-b border-gray-100"
                                    data-filter="all">📊 Semua Market</button>
                                <button
                                    class="filter-option w-full text-left px-4 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50 transition-colors border-b border-gray-100"
                                    data-filter="high">🔥 High Activity (>5)</button>
                                <button
                                    class="filter-option w-full text-left px-4 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50 transition-colors"
                                    data-filter="low">❄️ Low Activity (1-5)</button>
                            </div>
                        </div>
                        <div
                            class="text-xs font-bold text-blue-700 bg-blue-50 border border-blue-200 px-3 py-1.5 rounded-lg flex items-center gap-1.5 shadow-sm">
                            ⏰ <span id="realtime-clock">Memuat Waktu...</span>
                        </div>
                    </div>

                    <!-- TABLE -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 bg-gray-50">
                                    <th class="px-4 py-3 text-left font-bold text-gray-700">Market</th>
                                    <th class="px-4 py-3 text-center font-bold text-gray-700">Hari Ini</th>
                                    <th class="px-4 py-3 text-center font-bold text-gray-700">Bulan Ini</th>
                                    <th class="px-4 py-3 text-center font-bold text-gray-700">Rata-rata/Hari</th>
                                    <th class="px-4 py-3 text-center font-bold text-gray-700">Status</th>
                                </tr>
                            </thead>
                            <tbody id="history-table" class="divide-y divide-gray-200">
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-400 font-medium">⏳ Memuat
                                        data...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- FOOTER -->
    <footer class="bg-white border-t border-gray-200 py-6 mt-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center">
            <p class="text-gray-500 text-sm font-medium">&copy; <script>
                    document.write(new Date().getFullYear())
                </script> <strong>RODIS</strong> - Robot Trading. All rights reserved.</p>
        </div>
    </footer>

    <!-- SCRIPTS -->
    <script>
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

        let historyData = [];
        let currentFilter = 'all';

        function loadTradeHistory() {
            fetch('/api/trade-history')
                .then(res => res.json())
                .then(data => {
                    console.log('Trade History Data:', data);
                    if (data.success) {
                        historyData = data.data || [];
                        renderTable();
                        updateSummary();
                    } else {
                        console.error('Error loading history:', data.message);
                    }
                })
                .catch(err => console.error('Fetch error:', err));
        }

        function renderTable() {
            const table = document.getElementById('history-table');
            if (!historyData || historyData.length === 0) {
                table.innerHTML =
                    `<tr><td colspan="5" class="px-4 py-8 text-center text-gray-400 font-medium">📭 Belum ada data trade history</td></tr>`;
                return;
            }

            let filtered = historyData;
            if (currentFilter === 'high') filtered = historyData.filter(d => d.today > 5);
            else if (currentFilter === 'low') filtered = historyData.filter(d => d.today >= 1 && d.today <= 5);

            if (filtered.length === 0) {
                table.innerHTML =
                    `<tr><td colspan="5" class="px-4 py-8 text-center text-gray-400 font-medium">📭 Tidak ada market yang sesuai filter</td></tr>`;
                return;
            }

            let html = '';
            filtered.forEach(item => {
                const marketName = item.market;
                const today = item.today || 0;
                const month = item.month || 0;
                const dayOfMonth = new Date().getDate();
                const avgPerDay = dayOfMonth > 0 ? (month / dayOfMonth).toFixed(1) : 0;

                let statusColor = 'bg-gray-100 text-gray-600';
                let statusLabel = 'Tidak Aktif';
                if (today > 5) {
                    statusColor = 'bg-red-100 text-red-700 font-bold';
                    statusLabel = '🔥 Sangat Aktif';
                } else if (today >= 3) {
                    statusColor = 'bg-orange-100 text-orange-700 font-bold';
                    statusLabel = '🟠 Aktif';
                } else if (today >= 1) {
                    statusColor = 'bg-yellow-100 text-yellow-700 font-bold';
                    statusLabel = '🟡 Sedikit Aktif';
                }

                html += `<tr class="hover:bg-gray-50 transition-colors">
                <td class="px-4 py-3 font-semibold text-gray-700">${marketName}</td>
                <td class="px-4 py-3 text-center"><span class="px-2.5 py-1 bg-blue-100 text-blue-700 rounded font-bold">${today}</span></td>
                <td class="px-4 py-3 text-center"><span class="px-2.5 py-1 bg-indigo-100 text-indigo-700 rounded font-bold">${month}</span></td>
                <td class="px-4 py-3 text-center"><span class="px-2.5 py-1 bg-purple-100 text-purple-700 rounded font-semibold">${avgPerDay}</span></td>
                <td class="px-4 py-3 text-center"><span class="px-2.5 py-1 rounded font-bold text-xs ${statusColor}">${statusLabel}</span></td>
            </tr>`;
            });

            table.innerHTML = html;
        }

        function updateSummary() {
            const marketToday = historyData.filter(d => d.today > 0).length;
            const totalToday = historyData.reduce((sum, d) => sum + (d.today || 0), 0);
            const totalMonth = historyData.reduce((sum, d) => sum + (d.month || 0), 0);

            document.getElementById('summary-today').textContent = marketToday;
            document.getElementById('summary-total-today').textContent = totalToday;
            document.getElementById('summary-total-month').textContent = totalMonth;
        }

        const filterDropdownBtn = document.getElementById('filter-dropdown-btn');
        const filterDropdownMenu = document.getElementById('filter-dropdown-menu');
        const filterLabel = document.getElementById('filter-label');
        const filterOptions = document.querySelectorAll('.filter-option');

        filterDropdownBtn.addEventListener('click', () => {
            filterDropdownMenu.classList.toggle('hidden');
        });

        filterOptions.forEach(option => {
            option.addEventListener('click', (e) => {
                currentFilter = e.target.dataset.filter;
                filterLabel.textContent = e.target.textContent;
                filterDropdownMenu.classList.add('hidden');
                renderTable();
            });
        });

        document.addEventListener('click', (e) => {
            if (!e.target.closest('#filter-dropdown-btn') && !e.target.closest('#filter-dropdown-menu')) {
                filterDropdownMenu.classList.add('hidden');
            }
        });

        function startSessionCheck() {
            setInterval(() => {
                fetch('/user/check-session', {
                        credentials: 'same-origin'
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (!data.valid) {
                            alert('⚠️ Sesi Anda telah berakhir karena akun ini login di perangkat lain.');
                            window.location.href = '/login';
                        }
                    })
                    .catch(() => {});
            }, 5000);
        }

        window.onload = function() {
            startRealtimeClock();
            loadTradeHistory();
            startSessionCheck();
            setInterval(loadTradeHistory, 10000);
        };
    </script>
</body>

</html>