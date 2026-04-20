<!DOCTYPE html>
<html lang="id" data-theme="dark">

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
            --badge-true-bg: rgba(16,185,129,0.12); --badge-true-t: #6ee7b7;
            --badge-false-bg: rgba(239,68,68,0.12); --badge-false-t: #fca5a5;
            --badge-empty-bg: rgba(255,255,255,0.04); --badge-empty-t: rgba(255,255,255,0.2);
            --input-bg: rgba(255,255,255,0.05); --input-bd: rgba(255,255,255,0.08); --input-focus: rgba(16,185,129,0.3);
            --scroll: rgba(255,255,255,0.08);
            --tfoot-bg: rgba(255,255,255,0.02);
            --info-bg: rgba(59,130,246,0.08); --info-bd: rgba(59,130,246,0.18); --info-t: #93c5fd;
        }

        [data-theme="light"] {
            --bg: #f0f2f5; --bg-card: #ffffff; --bg-card-hover: #f8fafc;
            --bg-nav: rgba(255,255,255,0.92); --border: #e2e8f0; --border-s: #f1f5f9;
            --t1: #0f172a; --t2: #64748b; --t3: #94a3b8; --t4: #cbd5e1;
            --green: #059669; --green-bg: #ecfdf5; --green-bd: #a7f3d0;
            --red: #dc2626; --red-bg: #fef2f2; --red-bd: #fecaca;
            --indigo: #6366f1; --amber: #f59e0b; --blue: #3b82f6;
            --badge-true-bg: #d1fae5; --badge-true-t: #065f46;
            --badge-false-bg: #fee2e2; --badge-false-t: #991b1b;
            --badge-empty-bg: #f1f5f9; --badge-empty-t: #94a3b8;
            --input-bg: #ffffff; --input-bd: #e2e8f0; --input-focus: #10b981;
            --scroll: #cbd5e1;
            --tfoot-bg: #f8fafc;
            --info-bg: #eff6ff; --info-bd: #bfdbfe; --info-t: #1d4ed8;
        }

        body { background: var(--bg); color: var(--t1); transition: background var(--t), color var(--t); }

        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--scroll); border-radius: 10px; }

        .fade-in { animation: fadeIn .3s ease-out; }
        @keyframes fadeIn { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:translateY(0); } }

        .card { background: var(--bg-card); border: 1px solid var(--border); transition: background var(--t), border-color var(--t); }
        .nav-bar { background: var(--bg-nav); backdrop-filter: blur(20px); border-bottom: 1px solid var(--border); transition: background var(--t), border-color var(--t); }

        .htable thead th { background: var(--bg-card); border-bottom: 1px solid var(--border); transition: background var(--t), border-color var(--t); }
        .htable tbody tr { border-bottom: 1px solid var(--border-s); transition: background 0.15s, border-color var(--t); }
        .htable tbody tr:hover { background: var(--bg-card-hover); }
        .htable tfoot { background: var(--tfoot-bg); border-top: 1px solid var(--border); transition: background var(--t), border-color var(--t); }

        .stat-card { position: relative; overflow: hidden; }
        .stat-card::after { content:''; position:absolute; top:-10px; right:-10px; width:50px; height:50px; border-radius:50%; filter:blur(20px); opacity:0.2; pointer-events:none; }
        [data-theme="light"] .stat-card::after { opacity: 0.1; }
        .stat-blue::after { background: var(--blue); }
        .stat-slate::after { background: var(--t3); }

        .theme-toggle { width:52px; height:28px; border-radius:14px; cursor:pointer; position:relative; transition:background 0.4s; border:none; outline:none; padding:0; }
        [data-theme="dark"] .theme-toggle { background: linear-gradient(135deg, #1e293b, #334155); box-shadow: inset 0 1px 3px rgba(0,0,0,0.3); }
        [data-theme="light"] .theme-toggle { background: linear-gradient(135deg, #bfdbfe, #93c5fd); box-shadow: inset 0 1px 3px rgba(0,0,0,0.08); }
        .toggle-knob { position:absolute; top:3px; width:22px; height:22px; border-radius:50%; transition: left 0.4s cubic-bezier(0.68,-0.15,0.32,1.15), background 0.4s, box-shadow 0.4s; display:flex; align-items:center; justify-content:center; font-size:12px; }
        [data-theme="dark"] .toggle-knob { left:3px; background:linear-gradient(135deg,#4f46e5,#6366f1); box-shadow:0 2px 8px rgba(99,102,241,0.4); }
        [data-theme="light"] .toggle-knob { left:27px; background:linear-gradient(135deg,#f59e0b,#fbbf24); box-shadow:0 2px 8px rgba(245,158,11,0.4); }

        .themed-input {
            background: var(--input-bg); border: 1px solid var(--input-bd); color: var(--t1);
            transition: background var(--t), border-color var(--t), color var(--t);
        }
        .themed-input:focus { border-color: var(--input-focus); }
        .themed-input option { background: var(--bg); color: var(--t1); }
    </style>
</head>

<body class="antialiased min-h-screen flex flex-col">
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
                    RODIS <span style="color: var(--green);" class="font-semibold">History</span>
                </h1>
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                <button class="theme-toggle" onclick="toggleTheme()" title="Ubah tema">
                    <span class="toggle-knob"><span id="theme-icon">🌙</span></span>
                </button>

                <a href="{{ route('dashboard') }}"
                    class="text-[11px] sm:text-xs font-bold px-2.5 py-1.5 rounded-lg transition-all"
                    style="color: var(--green); background: var(--green-bg); border: 1px solid var(--green-bd);">
                    Dashboard
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

    <main class="flex-grow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 fade-in">
                <div class="card stat-card stat-blue rounded-xl p-4">
                    <div class="text-xs font-semibold" style="color: var(--t3);">Akurasi Bulan Ini</div>
                    <div id="month-accuracy" class="text-2xl font-extrabold mt-1" style="color: var(--blue);">0.00%</div>
                </div>
                <div class="card stat-card stat-slate rounded-xl p-4">
                    <div class="text-xs font-semibold" style="color: var(--t3);">Signal Selesai Hari Ini</div>
                    <div id="today-total" class="text-2xl font-extrabold mt-1">0</div>
                </div>
                <div class="card stat-card stat-slate rounded-xl p-4">
                    <div class="text-xs font-semibold" style="color: var(--t3);">Signal Selesai Bulan Ini</div>
                    <div id="month-total" class="text-2xl font-extrabold mt-1">0</div>
                </div>
            </div>

            <div class="card rounded-2xl p-4 sm:p-5 fade-in">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                    <div>
                        <h2 class="text-sm font-extrabold">History Fase Bot (1 - 7)</h2>
                        <p class="text-[10px] mt-1" style="color: var(--t3);">Row baru muncul saat ticker sudah mencapai FALSE KE yang aktif, lalu hasil berikutnya masuk mulai dari fase setelahnya.</p>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <select id="ticker-filter" class="themed-input text-xs font-semibold rounded-lg px-3 py-1.5 outline-none"></select>
                        <input type="date" id="date-filter" class="themed-input text-xs font-semibold rounded-lg px-3 py-1.5 outline-none">
                        <span id="clock" class="text-[11px] font-mono" style="color: var(--t3);">00:00:00 WIB</span>
                    </div>
                </div>

                <div class="overflow-x-auto rounded-xl" style="border: 1px solid var(--border);">
                    <table class="min-w-full text-sm htable">
                        <thead>
                            <tr>
                                <th class="px-3 py-2 text-left text-[10px] font-bold uppercase tracking-wider" style="color: var(--t3);">Tanggal</th>
                                <th class="px-3 py-2 text-left text-[10px] font-bold uppercase tracking-wider" style="color: var(--t3);">Jam</th>
                                <th class="px-3 py-2 text-left text-[10px] font-bold uppercase tracking-wider" style="color: var(--t3);">Ticker</th>
                                <th class="px-3 py-2 text-center text-[10px] font-bold uppercase tracking-wider" style="color: var(--t3);">Fase 1</th>
                                <th class="px-3 py-2 text-center text-[10px] font-bold uppercase tracking-wider" style="color: var(--t3);">Fase 2</th>
                                <th class="px-3 py-2 text-center text-[10px] font-bold uppercase tracking-wider" style="color: var(--t3);">Fase 3</th>
                                <th class="px-3 py-2 text-center text-[10px] font-bold uppercase tracking-wider" style="color: var(--t3);">Fase 4</th>
                                <th class="px-3 py-2 text-center text-[10px] font-bold uppercase tracking-wider" style="color: var(--t3);">Fase 5</th>
                                <th class="px-3 py-2 text-center text-[10px] font-bold uppercase tracking-wider" style="color: var(--t3);">Fase 6</th>
                                <th class="px-3 py-2 text-center text-[10px] font-bold uppercase tracking-wider" style="color: var(--t3);">Fase 7</th>
                            </tr>
                        </thead>
                        <tbody id="history-table">
                            <tr><td colspan="10" class="px-4 py-8 text-center font-medium" style="color: var(--t4);">Memuat data...</td></tr>
                        </tbody>
                        <tfoot id="history-footer">
                            <tr>
                                <td colspan="3" class="px-3 py-3 text-xs font-extrabold">Persentase Hari Ini</td>
                                <td class="px-3 py-3 text-center text-xs font-bold" style="color:var(--t3);">-</td>
                                <td class="px-3 py-3 text-center text-xs font-bold" style="color:var(--t3);">-</td>
                                <td class="px-3 py-3 text-center text-xs font-bold" style="color:var(--t3);">-</td>
                                <td class="px-3 py-3 text-center text-xs font-bold" style="color:var(--t3);">-</td>
                                <td class="px-3 py-3 text-center text-xs font-bold" style="color:var(--t3);">-</td>
                                <td class="px-3 py-3 text-center text-xs font-bold" style="color:var(--t3);">-</td>
                                <td class="px-3 py-3 text-center text-xs font-bold" style="color:var(--t3);">-</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="mt-3 text-[10px] flex justify-between items-center" style="color: var(--t4);">
                    <span id="rows-info">0 baris ditampilkan</span>
                    <span>Update terakhir: <span id="updated-at">-</span></span>
                </div>

                <div class="mt-4 pt-4" style="border-top: 1px solid var(--border-s);">
                    <div class="rounded-xl p-4" style="background: var(--info-bg); border: 1px solid var(--info-bd); transition: background var(--t), border-color var(--t);">
                        <div class="flex items-center justify-between">
                            <div class="text-xs font-semibold" style="color: var(--info-t);">Persentase Benar Bulan Ini</div>
                            <select id="phase-select" class="themed-input text-xs font-semibold rounded px-2 py-1 outline-none">
                                <option value="ALL">Semua Fase</option>
                                <option value="1">Fase 1</option>
                                <option value="2">Fase 2</option>
                                <option value="3">Fase 3</option>
                                <option value="4">Fase 4</option>
                                <option value="5">Fase 5</option>
                                <option value="6">Fase 6</option>
                                <option value="7">Fase 7</option>
                            </select>
                        </div>
                        <div id="bottom-month-accuracy" class="text-xl font-extrabold mt-2" style="color: var(--info-t);">0.00%</div>
                        <div id="bottom-month-breakdown" class="text-xs mt-1" style="color: var(--info-t); opacity: 0.7;">Win 0 / Loss 0</div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer style="border-top: 1px solid var(--border-s);" class="py-4 mt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-[11px]" style="color: var(--t4);">
            <span style="color: var(--t3);" class="font-semibold">RODIS</span> - Robot Trading
        </div>
    </footer>

    <script>
        let historyData = [];
        let currentTicker = 'ALL';
        let currentApiDate = '';
        let currentDate = '';
        let currentPage = 1;
        const rowsPerPage = 5;
        const DEFAULT_HISTORY_TARGET_LOSS = 2;
        let historyTargetLoss = DEFAULT_HISTORY_TARGET_LOSS;

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
            if (!dateTimeValue || !String(dateTimeValue).includes(' ')) {
                return null;
            }

            const [datePart, timePart] = String(dateTimeValue).split(' ');
            const datePieces = datePart.split('-').map(Number);
            const timePieces = timePart.split(':').map(Number);

            if (datePieces.length !== 3 || timePieces.length < 2) {
                return null;
            }

            return new Date(
                datePieces[0],
                datePieces[1] - 1,
                datePieces[2],
                timePieces[0],
                timePieces[1],
                0
            );
        }

        function normalizePhaseValue(value) {
            const normalized = String(value ?? '').trim().toUpperCase();
            if (normalized === 'TRUE' || normalized === 'FALSE') {
                return normalized;
            }
            return '-';
        }

        function getPhaseTimeLabel(row, phaseNumber) {
            const phaseValue = normalizePhaseValue(row[`phase_${phaseNumber}`]);
            if (phaseValue !== 'TRUE' && phaseValue !== 'FALSE') {
                return '';
            }

            const targetLoss = Number.parseInt(row.target_loss || historyTargetLoss || DEFAULT_HISTORY_TARGET_LOSS, 10);
            if (!Number.isFinite(targetLoss) || phaseNumber <= targetLoss) {
                return '';
            }

            const baseDate = parseTriggerDateTime(row.trigger_at);
            if (!baseDate) {
                return '';
            }

            const offsetMinutes = (phaseNumber - targetLoss) * 5;
            baseDate.setMinutes(baseDate.getMinutes() + offsetMinutes);

            const hh = String(baseDate.getHours()).padStart(2, '0');
            const mm = String(baseDate.getMinutes()).padStart(2, '0');
            return `${hh}:${mm}`;
        }

        function badgePhase(value, row, phaseNumber) {
            const val = normalizePhaseValue(value);
            if (val === 'TRUE' || val === 'FALSE') {
                const isTrue = val === 'TRUE';
                const bgVar = isTrue ? '--badge-true-bg' : '--badge-false-bg';
                const tVar = isTrue ? '--badge-true-t' : '--badge-false-t';
                const timeLabel = getPhaseTimeLabel(row, phaseNumber);
                return `
                <div class="flex flex-col items-center gap-1">
                    <span class="inline-block min-w-12 px-2 py-1 rounded text-xs font-bold" style="background:var(${bgVar}); color:var(${tVar}); transition: background var(--t), color var(--t);">${val}</span>
                    ${timeLabel ? `<span class="text-[11px] font-semibold" style="color:var(--t3)">${timeLabel}</span>` : ''}
                </div>`;
            }
            return `<span class="inline-block min-w-12 px-2 py-1 rounded text-xs font-bold" style="background:var(--badge-empty-bg); color:var(--badge-empty-t); transition: background var(--t), color var(--t);">-</span>`;
        }

        function fillTickerOptions(rows) {
            const select = document.getElementById('ticker-filter');
            const uniqueTickers = [...new Set(rows.map(r => r.ticker).filter(Boolean))].sort((a, b) => a.localeCompare(b));

            select.innerHTML = '<option value="ALL">Semua Ticker</option>';
            uniqueTickers.forEach(ticker => {
                const opt = document.createElement('option');
                opt.value = ticker;
                opt.textContent = ticker;
                select.appendChild(opt);
            });

            if (![...select.options].some(opt => opt.value === currentTicker)) {
                currentTicker = 'ALL';
                select.value = 'ALL';
            } else {
                select.value = currentTicker;
            }
        }

        function getDisplayedRows() {
            let rows = historyData;

            // filter ticker
            if (currentTicker !== 'ALL') {
                rows = rows.filter(r => r.ticker === currentTicker);
            }

            // filter tanggal
            if (currentDate) {
                rows = rows.filter(r => r.tanggal === currentDate);
            }

            return rows;
        }

        function renderTable() {
            const tbody = document.getElementById('history-table');
            const rows = getDisplayedRows();
            if (!rows.length) {
                tbody.innerHTML = `<tr><td colspan="10" class="px-4 py-8 text-center font-medium" style="color:var(--t4)">Belum ada data history fase</td></tr>`;
                document.getElementById('rows-info').textContent = '0 baris ditampilkan';
                return;
            }
            tbody.innerHTML = rows.map(row => `
            <tr>
                <td class="px-3 py-2 text-xs font-semibold" style="color:var(--t2)">${row.tanggal || '-'}</td>
                <td class="px-3 py-2 text-xs font-semibold" style="color:var(--t2)">${row.waktu || '-'}</td>
                <td class="px-3 py-2 text-xs font-semibold">${row.ticker || '-'}</td>
                ${[1,2,3,4,5,6,7].map(p => `<td class="px-3 py-2 text-center">${badgePhase(row['phase_' + p], row, p)}</td>`).join('')}
            </tr>`).join('');
            document.getElementById('rows-info').textContent = `${rows.length} baris ditampilkan`;
        }

        function formatPercent(value) {
            return `${value.toFixed(2)}%`;
        }

        function renderTodayPhaseFooter() {
            const footer = document.getElementById('history-footer');
            // const todayRows = getDisplayedRows().filter(row => row.tanggal === currentApiDate);
            const todayRows = getDisplayedRows();
            const phases = [1, 2, 3, 4, 5, 6, 7];
            const phaseCells = phases.map(phase => {
                let trueCount = 0;
                let falseCount = 0;

                todayRows.forEach(row => {
                    const value = normalizePhaseValue(row[`phase_${phase}`]);
                    if (value === 'TRUE') {
                        trueCount++;
                    } else if (value === 'FALSE') {
                        falseCount++;
                    }
                });

                const total = trueCount + falseCount;
                const percentLabel = total > 0 ? formatPercent((trueCount / total) * 100) : '-';

                return `<td class="px-3 py-3 text-center"><div class="text-sm font-extrabold">${percentLabel}</div></td>`;
            }).join('');
            footer.innerHTML = `<tr><td colspan="3" class="px-3 py-3 text-xs font-extrabold">Persentase Hari Ini</td>${phaseCells}</tr>`;
        }

        function renderSummary(summary) {
            const today = summary?.today || {};
            const month = summary?.month || {};

            const phaseStats = calculateMonthlyPhaseStats();
            const selectedPhase = document.getElementById('phase-select')?.value || 'ALL';

            let percent = 0;
            let win = 0;
            let loss = 0;

            if (selectedPhase === 'ALL') {
                Object.values(phaseStats).forEach(p => {
                    win += p.true;
                    loss += p.false;
                });

                const total = win + loss;
                percent = total > 0 ? ((win / total) * 100).toFixed(2) : 0;

            } else {
                const p = phaseStats[selectedPhase];
                win = p.true;
                loss = p.false;
                percent = p.percent;
            }

            document.getElementById('month-accuracy').textContent = percent + '%';
            document.getElementById('today-total').textContent = today.total_signals || 0;
            document.getElementById('month-total').textContent = month.total_signals || 0;

            document.getElementById('bottom-month-accuracy').textContent = percent + '%';
            document.getElementById('bottom-month-breakdown').textContent =
                `Win ${win} / Loss ${loss}`;
        }

        function loadTradeHistory() {
            fetch(`${API_BASE}/trade-history`)
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.message || 'Gagal memuat data');
                    }

                    const targetLossFromApi = Number.parseInt(data.target_loss || DEFAULT_HISTORY_TARGET_LOSS, 10);
                    historyTargetLoss = Number.isFinite(targetLossFromApi) && targetLossFromApi >= 1 && targetLossFromApi <= 6 ?
                        targetLossFromApi :
                        DEFAULT_HISTORY_TARGET_LOSS;

                    historyData = Array.isArray(data.data) ? data.data : [];
                    currentApiDate = data.date || '';
                    fillTickerOptions(historyData);
                    renderTable();
                    renderTodayPhaseFooter();
                    renderSummary(data.summary || {});
                    document.getElementById('updated-at').textContent = data.generated_at || '-';
                })
                .catch(() => {
                    document.getElementById('history-table').innerHTML =
                        `<tr><td colspan="10" class="px-4 py-8 text-center font-medium" style="color:var(--red)">Gagal memuat data history</td></tr>`;
                    document.getElementById('history-footer').innerHTML =
                        `<tr><td colspan="10" class="px-3 py-3 text-center text-xs font-bold" style="color:var(--red)">Gagal memuat statistik fase.</td></tr>`;
                });
        }

        // function renderPagination(totalPages) {
        //     const container = document.getElementById('pagination');

        //     if (totalPages <= 1) {
        //         container.innerHTML = '';
        //         return;
        //     }

        //     let html = '';

        //     // tombol prev
        //     if (currentPage > 1) {
        //         html += `
        //     <button onclick="goToPage(${currentPage - 1})"
        //         class="px-3 py-1 border rounded-lg text-xs font-semibold bg-white hover:bg-slate-100">
        //         Prev
        //     </button>
        // `;
        //     }

        //     // nomor halaman
        //     for (let i = 1; i <= totalPages; i++) {
        //         html += `
        //     <button onclick="goToPage(${i})"
        //         class="px-3 py-1 border rounded-lg text-xs font-semibold
        //         ${i === currentPage ? 'bg-blue-500 text-white' : 'bg-white hover:bg-slate-100'}">
        //         ${i}
        //     </button>
        // `;
        //     }

        //     // tombol next
        //     if (currentPage < totalPages) {
        //         html += `
        //     <button onclick="goToPage(${currentPage + 1})"
        //         class="px-3 py-1 border rounded-lg text-xs font-semibold bg-white hover:bg-slate-100">
        //         Next
        //     </button>
        // `;
        //     }

        //     container.innerHTML = html;
        // }

        function goToPage(page) {
            currentPage = page;
            renderTable();
        }

        function calculateMonthlyPhaseStats() {
            let result = {};

            for (let i = 1; i <= 7; i++) {
                let trueCount = 0;
                let falseCount = 0;

                historyData.forEach(row => {
                    const val = normalizePhaseValue(row[`phase_${i}`]);
                    if (val === 'TRUE') trueCount++;
                    if (val === 'FALSE') falseCount++;
                });

                const total = trueCount + falseCount;

                result[i] = {
                    true: trueCount,
                    false: falseCount,
                    percent: total > 0 ? ((trueCount / total) * 100).toFixed(2) : 0
                };
            }

            return result;
        }

        document.getElementById('ticker-filter').addEventListener('change', (e) => {
            currentTicker = e.target.value;
            renderTable();
            renderTodayPhaseFooter();
        });

        document.getElementById('date-filter').addEventListener('change', (e) => {
            currentDate = e.target.value;
            renderTable();
            renderTodayPhaseFooter();
        });
        document.getElementById('phase-select').addEventListener('change', () => {
            renderSummary({});
        });

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

        window.onload = function() {
            startRealtimeClock();
            loadTradeHistory();
            setInterval(() => { loadTradeHistory(); }, 5000);
        };
    </script>
</body>

</html>
