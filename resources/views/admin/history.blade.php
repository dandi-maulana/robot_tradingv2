<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RODIS - Trade History</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body {
            background: #f4f5f7;
        }

        .fade-in {
            animation: fadeIn .25s ease-in-out;
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
    </style>
</head>

<body class="text-slate-800 antialiased min-h-screen flex flex-col">
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="bg-gojek text-white rounded-xl p-2 shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                        </path>
                    </svg>
                </div>
                <h1 class="text-xl font-extrabold tracking-tight">RODIS <span
                        class="text-gojek font-semibold">History</span></h1>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('user.dashboard') }}"
                    class="text-sm font-bold text-gojek hover:text-gojek-dark">Dashboard</a>
                <span class="w-px h-5 bg-slate-200"></span>
                <a href="{{ route('user.history') }}"
                    class="text-sm font-bold text-gojek bg-gojek-light px-3 py-1 rounded-lg border border-gojek">History</a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="text-xs font-bold text-red-600 bg-red-50 border border-red-100 px-3 py-1 rounded-lg">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <main class="flex-grow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 fade-in">
                <div class="bg-white border border-slate-200 rounded-xl p-4">
                    <div class="text-xs font-semibold text-slate-500">Akurasi Bulan Ini</div>
                    <div id="month-accuracy" class="text-2xl font-extrabold text-blue-600 mt-1">0.00%</div>
                </div>
                <div class="bg-white border border-slate-200 rounded-xl p-4">
                    <div class="text-xs font-semibold text-slate-500">Signal Selesai Hari Ini</div>
                    <div id="today-total" class="text-2xl font-extrabold text-slate-700 mt-1">0</div>
                </div>
                <div class="bg-white border border-slate-200 rounded-xl p-4">
                    <div class="text-xs font-semibold text-slate-500">Signal Selesai Bulan Ini</div>
                    <div id="month-total" class="text-2xl font-extrabold text-slate-700 mt-1">0</div>
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-2xl p-5 fade-in">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                    <div>
                        <h2 class="text-sm font-extrabold text-slate-800">History Fase Bot (1 - 7)</h2>
                        <p class="text-xs text-slate-500 mt-1">Row baru muncul saat ticker sudah mencapai FALSE KE yang
                            aktif, lalu hasil berikutnya masuk mulai dari fase setelahnya.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <select id="ticker-filter"
                            class="text-xs font-semibold border border-slate-300 rounded-lg px-3 py-1.5 bg-white">
                            <option value="ALL">Semua Ticker</option>
                        </select>
                        <div
                            class="text-xs font-semibold text-blue-700 bg-blue-50 border border-blue-100 rounded-lg px-3 py-1.5">
                            <span id="clock">00:00:00 WIB</span>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto border border-slate-200 rounded-xl">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-bold text-slate-600">Tanggal</th>
                                <th class="px-3 py-2 text-left text-xs font-bold text-slate-600">Jam</th>
                                <th class="px-3 py-2 text-left text-xs font-bold text-slate-600">Ticker</th>
                                <th class="px-3 py-2 text-center text-xs font-bold text-slate-600">Fase 1</th>
                                <th class="px-3 py-2 text-center text-xs font-bold text-slate-600">Fase 2</th>
                                <th class="px-3 py-2 text-center text-xs font-bold text-slate-600">Fase 3</th>
                                <th class="px-3 py-2 text-center text-xs font-bold text-slate-600">Fase 4</th>
                                <th class="px-3 py-2 text-center text-xs font-bold text-slate-600">Fase 5</th>
                                <th class="px-3 py-2 text-center text-xs font-bold text-slate-600">Fase 6</th>
                                <th class="px-3 py-2 text-center text-xs font-bold text-slate-600">Fase 7</th>
                            </tr>
                        </thead>
                        <tbody id="history-table" class="divide-y divide-slate-100">
                            <tr>
                                <td colspan="10" class="px-4 py-8 text-center text-slate-400 font-medium">Memuat data...
                                </td>
                            </tr>
                        </tbody>
                        <tfoot id="history-footer" class="bg-slate-50 border-t border-slate-200">
                            <tr>
                                <td colspan="3" class="px-3 py-3 text-xs font-extrabold text-slate-700">Persentase Hari
                                    Ini</td>
                                <td class="px-3 py-3 text-center text-xs font-bold text-slate-500">-</td>
                                <td class="px-3 py-3 text-center text-xs font-bold text-slate-500">-</td>
                                <td class="px-3 py-3 text-center text-xs font-bold text-slate-500">-</td>
                                <td class="px-3 py-3 text-center text-xs font-bold text-slate-500">-</td>
                                <td class="px-3 py-3 text-center text-xs font-bold text-slate-500">-</td>
                                <td class="px-3 py-3 text-center text-xs font-bold text-slate-500">-</td>
                                <td class="px-3 py-3 text-center text-xs font-bold text-slate-500">-</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div id="pagination" class="mt-4 flex justify-center gap-2"></div>
                <div class="mt-4 text-xs text-slate-500 flex justify-between items-center">
                    <span id="rows-info">0 baris ditampilkan</span>
                    <span>Update terakhir: <span id="updated-at">-</span></span>
                </div>

                <div class="mt-5 border-t border-slate-200 pt-4">
                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                        <div class="text-xs font-semibold text-blue-700">Persentase Benar Bulan Ini</div>
                        <div id="bottom-month-accuracy" class="text-xl font-extrabold text-blue-700 mt-1">0.00%</div>
                        <div id="bottom-month-breakdown" class="text-xs text-blue-700 mt-1">Win 0 / Loss 0</div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-white border-t border-slate-200 py-5 mt-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-sm text-slate-500">
            <strong>RODIS</strong> - Robot Trading
        </div>
    </footer>
    <script>
        let historyData = [];
        let currentTicker = 'ALL';
        let currentApiDate = '';
        let currentPage = 1;
        const rowsPerPage = 10;
        const MASS_TG_LOSS_STORAGE_KEY = 'rodis_mass_tg_loss';

        function startRealtimeClock() {
            setInterval(() => {
                const now = new Date();
                const hh = String(now.getHours()).padStart(2, '0');
                const mm = String(now.getMinutes()).padStart(2, '0');
                const ss = String(now.getSeconds()).padStart(2, '0');
                document.getElementById('clock').textContent = `${hh}:${mm}:${ss} WIB`;
            }, 1000);
        }

        function parseTriggerDateTime(dateTimeValue) {
            if (!dateTimeValue || !String(dateTimeValue).includes(' ')) return null;

            const [datePart, timePart] = String(dateTimeValue).split(' ');
            const d = datePart.split('-').map(Number);
            const t = timePart.split(':').map(Number);

            return new Date(d[0], d[1] - 1, d[2], t[0], t[1], 0);
        }

        function getHistoryTargetLoss() {
            const saved = localStorage.getItem(MASS_TG_LOSS_STORAGE_KEY);
            const parsed = Number.parseInt(saved || '2', 10);
            return Number.isFinite(parsed) && parsed > 0 ? parsed : 2;
        }

        function getPhaseTimeLabel(row, phaseNumber) {
            const val = String(row[`phase_${phaseNumber}`] || '').toUpperCase();
            if (val !== 'TRUE' && val !== 'FALSE') return '';

            const targetLoss = getHistoryTargetLoss();
            if (phaseNumber <= targetLoss) return '';

            const base = parseTriggerDateTime(row.trigger_at);
            if (!base) return '';

            base.setMinutes(base.getMinutes() + (phaseNumber - targetLoss) * 5);

            return `${String(base.getHours()).padStart(2,'0')}:${String(base.getMinutes()).padStart(2,'0')}`;
        }

        function badgePhase(value, row, phaseNumber) {
            const val = (value || '-').toUpperCase();

            if (val === 'TRUE' || val === 'FALSE') {
                const color = val === 'TRUE' ? 'emerald' : 'red';
                const time = getPhaseTimeLabel(row, phaseNumber);

                return `
            <div class="flex flex-col items-center gap-1">
                <span class="inline-block min-w-12 px-2 py-1 rounded text-xs font-bold bg-${color}-100 text-${color}-700">${val}</span>
                ${time ? `<span class="text-[11px] font-semibold text-slate-500">${time}</span>` : ''}
            </div>`;
            }

            return `<span class="inline-block min-w-12 px-2 py-1 rounded text-xs font-bold bg-slate-100 text-slate-500">-</span>`;
        }

        function fillTickerOptions(rows) {
            const select = document.getElementById('ticker-filter');
            const uniqueTickers = [...new Set(rows.map(r => r.ticker).filter(Boolean))].sort();

            select.innerHTML = '<option value="ALL">Semua Ticker</option>';
            uniqueTickers.forEach(ticker => {
                const opt = document.createElement('option');
                opt.value = ticker;
                opt.textContent = ticker;
                select.appendChild(opt);
            });

            select.value = currentTicker;
        }

        function getDisplayedRows() {
            return currentTicker === 'ALL' ?
                historyData :
                historyData.filter(r => r.ticker === currentTicker);
        }

        function renderTable() {
            const tbody = document.getElementById('history-table');
            const rows = getDisplayedRows();

            const totalRows = rows.length;
            const totalPages = Math.ceil(totalRows / rowsPerPage);
            const start = (currentPage - 1) * rowsPerPage;
            const paginatedRows = rows.slice(start, start + rowsPerPage);

            if (!rows.length) {
                tbody.innerHTML =
                    '<tr><td colspan="10" class="px-4 py-8 text-center text-slate-400 font-medium">Belum ada data history fase</td></tr>';
                document.getElementById('rows-info').textContent = '0 baris ditampilkan';
                return;
            }

            tbody.innerHTML = paginatedRows.map(row => `
            <tr class="hover:bg-slate-50">
                <td class="px-3 py-2 text-xs font-semibold text-slate-700">${row.tanggal || '-'}</td>
                <td class="px-3 py-2 text-xs font-semibold text-slate-700">${row.waktu || '-'}</td>
                <td class="px-3 py-2 text-xs font-semibold text-slate-700">${row.ticker || '-'}</td>
                ${[1,2,3,4,5,6,7].map(p => `<td class="px-3 py-2 text-center">${badgePhase(row[`phase_${p}`], row, p)}</td>`).join('')}
            </tr>
        `).join('');

            document.getElementById('rows-info').textContent =
                `${start + 1}-${Math.min(start + rowsPerPage, totalRows)} dari ${totalRows} baris`;

            renderPagination(totalPages); // ✅ DIPINDAH KE SINI (FIX)
        }

        function renderPagination(totalPages) {
            const container = document.getElementById('pagination');

            if (totalPages <= 1) {
                container.innerHTML = '';
                return;
            }

            let html = '';

            for (let i = 1; i <= totalPages; i++) {
                html += `
                <button onclick="goToPage(${i})"
                    class="px-3 py-1 text-xs font-bold rounded-lg border
                    ${i === currentPage
                        ? 'bg-gojek text-white border-gojek'
                        : 'bg-white text-slate-600 border-slate-300'}">
                    ${i}
                </button>
            `;
            }

            container.innerHTML = html;
        }

        function goToPage(page) {
            currentPage = page;
            renderTable();
        }

        function formatPercent(value) {
            return `${value.toFixed(2)}%`;
        }

        function renderTodayPhaseFooter() {
            const footer = document.getElementById('history-footer');
            const todayRows = getDisplayedRows().filter(row => row.tanggal === currentApiDate);
            const phases = [1, 2, 3, 4, 5, 6, 7];

            const phaseCells = phases.map(phase => {
                let t = 0,
                    f = 0;

                todayRows.forEach(row => {
                    const val = String(row[`phase_${phase}`] || '').toUpperCase();
                    if (val === 'TRUE') t++;
                    else if (val === 'FALSE') f++;
                });

                const total = t + f;
                const percent = total > 0 ? formatPercent((t / total) * 100) : '-';

                return `<td class="px-3 py-3 text-center"><div class="text-sm font-extrabold text-slate-800">${percent}</div></td>`;
            }).join('');

            footer.innerHTML = `
            <tr>
                <td colspan="3" class="px-3 py-3 text-xs font-extrabold text-slate-700">Persentase Hari Ini</td>
                ${phaseCells}
            </tr>`;
        }

        function renderSummary(summary) {
            const today = summary?.today || {};
            const month = summary?.month || {};

            document.getElementById('month-accuracy').textContent = month.accuracy_label || '0.00%';
            document.getElementById('today-total').textContent = today.total_signals || 0;
            document.getElementById('month-total').textContent = month.total_signals || 0;

            document.getElementById('bottom-month-accuracy').textContent = month.accuracy_label || '0.00%';
            document.getElementById('bottom-month-breakdown').textContent =
                `Win ${month.wins || 0} / Loss ${month.losses || 0}`;
        }

        function loadTradeHistory() {
            fetch(`/api/trade-history?target_loss=${getHistoryTargetLoss()}`)
                .then(res => res.json())
                .then(data => {
                    if (!data.success) throw new Error();

                    historyData = data.data || [];
                    currentApiDate = data.date || '';

                    fillTickerOptions(historyData);
                    renderTable(); // ✅ cukup ini

                    renderTodayPhaseFooter();
                    renderSummary(data.summary || {});
                    document.getElementById('updated-at').textContent = data.generated_at || '-';
                })
                .catch(() => {
                    document.getElementById('history-table').innerHTML =
                        '<tr><td colspan="10" class="px-4 py-8 text-center text-red-500 font-medium">Gagal memuat data history</td></tr>';
                    document.getElementById('history-footer').innerHTML =
                        '<tr><td colspan="10" class="px-3 py-3 text-center text-xs font-bold text-red-500">Gagal memuat statistik fase.</td></tr>';
                });
        }

        document.getElementById('ticker-filter').addEventListener('change', (e) => {
            currentTicker = e.target.value;
            currentPage = 1;
            renderTable();
            renderTodayPhaseFooter();
        });

        window.onload = function() {
            startRealtimeClock();
            loadTradeHistory();
        };
    </script>
</body>

</html>