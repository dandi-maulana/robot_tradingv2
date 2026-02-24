<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
    let dashboardInterval, detailInterval, historyInterval, rodisInterval;
    let currentMarket = "";
    let activeMarketsList = [];
    let selectedTradeMarket = "";

    let detailCurrentPage = 1;
    const detailItemsPerPage = 10;
    let currentDetailHistory = [];

    let historyCurrentPage = 1;
    const historyItemsPerPage = 10;
    let currentTradeHistory = [];

    let rodisState = {
        active: false,
        market: "",
        targetLoss: 7,
        amount: 10,
        duration: 60,
        phase: 'IDLE',
        tradeCounter: 0,
        lastProcessedCandle: null,
        direction: ''
    };

    const allMarkets = [
        { id: "Asia Composite Index",   name: "Asia Index",   icon: "🌏", cat: "24 Jam FTT" },
        { id: "Europe Composite Index", name: "Europe Index", icon: "🌍", cat: "24 Jam FTT" },
        { id: "Commodity Composite",    name: "Commodity",    icon: "🌾", cat: "24 Jam FTT" },
        { id: "Crypto Composite Index", name: "Crypto Index", icon: "₿",  cat: "24 Jam FTT" },
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

    let currentPage = 1;
    const itemsPerPage = 8;

    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount);
    }

    // ================================================================
    // CHART (CANDLESTICK LOCAL)
    // ================================================================
    let localChartInstance = null;
    let currentChartTimeframe = '1M';

    function changeChartTimeframe(tf, btnElement) {
        currentChartTimeframe = tf;
        document.querySelectorAll('.tf-btn').forEach(btn => {
            btn.classList.remove('bg-gray-100', 'text-gray-800');
            btn.classList.add('text-gray-500');
        });
        btnElement.classList.remove('text-gray-500');
        btnElement.classList.add('bg-gray-100', 'text-gray-800');
        if (currentMarket) renderLocalChart(currentDetailHistory, currentMarket);
    }

    function getDeterministicSize(strSeed) {
        let hash = 0;
        for (let i = 0; i < strSeed.length; i++) {
            hash = strSeed.charCodeAt(i) + ((hash << 5) - hash);
        }
        let x = Math.abs(Math.sin(hash) * 10000);
        return x - Math.floor(x);
    }

    function renderLocalChart(historyData, marketName) {
        if (typeof ApexCharts === 'undefined') {
            setTimeout(() => renderLocalChart(historyData, marketName), 500);
            return;
        }

        const marketLabel = document.getElementById('local-chart-market');
        if (marketLabel) marketLabel.innerText = `${marketName} (${currentChartTimeframe})`;

        if (!historyData || historyData.length === 0) {
            if (localChartInstance) { localChartInstance.destroy(); localChartInstance = null; }
            document.getElementById('local-chart-container').innerHTML =
                '<div class="flex h-full items-center justify-center text-gray-400 font-bold text-xs sm:text-base py-20">Menunggu bot merekam data pergerakan...</div>';
            return;
        }

        let candleCount = 60;
        if (currentChartTimeframe === '5M')  candleCount = 40;
        if (currentChartTimeframe === '15M') candleCount = 20;

        let chartData = [...historyData].slice(0, candleCount).reverse();
        let basePrice = 1000.50;
        let candlestickData = [];
        let maData = [];

        chartData.forEach((item) => {
            let isGreen = item.warna === 'Hijau';
            let rnd = getDeterministicSize(item.tanggal + item.waktu);
            let bodySize = (rnd * 8) + 3;
            let wickTop = ((rnd * 13) % 4) + 1;
            let wickBot = ((rnd * 17) % 4) + 1;
            let open = basePrice;
            let close, high, low;
            if (isGreen) { close = open + bodySize; high = close + wickTop; low = open - wickBot; }
            else          { close = open - bodySize; high = open + wickTop;  low = close - wickBot; }
            basePrice = close;
            candlestickData.push({ x: item.waktu, y: [open, high, low, close] });
        });

        for (let i = 0; i < candlestickData.length; i++) {
            if (i < 4) { maData.push({ x: candlestickData[i].x, y: null }); }
            else {
                let sum = 0;
                for (let j = 0; j < 5; j++) sum += candlestickData[i - j].y[3];
                maData.push({ x: candlestickData[i].x, y: sum / 5 });
            }
        }

        // Detect dark mode for chart theme
        const isDark = document.documentElement.classList.contains('dark');
        let isMobile = window.innerWidth < 640;

        let options = {
            series: [
                { name: 'Candle Harga', type: 'candlestick', data: candlestickData },
                { name: 'SMA (5)',       type: 'line',         data: maData }
            ],
            chart: {
                height: isMobile ? 300 : 380,
                width: '100%',
                type: 'candlestick',
                fontFamily: 'inherit',
                toolbar: { show: false },
                animations: { enabled: false },
                redrawOnParentResize: true,
                background: isDark ? '#0d1117' : '#ffffff',
            },
            theme: { mode: isDark ? 'dark' : 'light' },
            plotOptions: {
                candlestick: {
                    colors: { upward: '#22c55e', downward: '#ef4444' },
                    wick: { useFillColor: true }
                }
            },
            colors: ['#000000', '#f59e0b'],
            stroke: { width: [1, 2], curve: 'smooth' },
            xaxis: {
                type: 'category',
                labels: {
                    rotate: -45,
                    style: { fontSize: isMobile ? '8px' : '10px', colors: isDark ? '#8b949e' : '#94a3b8' },
                    hideOverlappingLabels: true,
                    trim: true
                },
                tickAmount: isMobile ? 8 : 15,
                tooltip: { enabled: false }
            },
            yaxis: {
                labels: {
                    style: { fontSize: isMobile ? '9px' : '11px', fontWeight: '600', colors: isDark ? '#8b949e' : '#64748b' },
                    formatter: function(val) { return val ? val.toFixed(2) : val; }
                },
                tickAmount: isMobile ? 4 : 6
            },
            grid: {
                borderColor: isDark ? '#21262d' : '#e2e8f0',
                strokeDashArray: 4,
                padding: { left: 5, right: 5, bottom: 0 }
            },
            tooltip: {
                shared: true,
                intersect: false,
                theme: isDark ? 'dark' : 'light',
                y: { formatter: function(val) { return val ? val.toFixed(2) : val; } }
            },
            legend: {
                position: 'top',
                horizontalAlign: isMobile ? 'center' : 'right',
                fontSize: isMobile ? '10px' : '12px',
                labels: { colors: isDark ? '#c9d1d9' : '#374151' }
            }
        };

        if (!localChartInstance) {
            document.getElementById('local-chart-container').innerHTML = '';
            localChartInstance = new ApexCharts(document.querySelector("#local-chart-container"), options);
            localChartInstance.render();
        } else {
            localChartInstance.updateOptions({
                chart: { height: isMobile ? 300 : 380, background: isDark ? '#0d1117' : '#ffffff' },
                theme: { mode: isDark ? 'dark' : 'light' },
                grid: { borderColor: isDark ? '#21262d' : '#e2e8f0' }
            });
            localChartInstance.updateSeries([
                { name: 'Candle Harga', data: candlestickData },
                { name: 'SMA (5)',      data: maData }
            ]);
        }
    }

    window.addEventListener('resize', () => {
        if (currentMarket && currentDetailHistory.length > 0) {
            renderLocalChart(currentDetailHistory, currentMarket);
        }
    });

    // ================================================================
    // DARK / LIGHT MODE TOGGLE
    // ================================================================

    /**
     * Sync icon emoji to both desktop & mobile toggle buttons
     */
    function syncThemeIcons(isDark) {
        const icon    = document.getElementById('theme-icon');
        const iconMob = document.getElementById('theme-icon-mob');
        const emoji   = isDark ? '☀️' : '🌙';
        if (icon)    icon.textContent    = emoji;
        if (iconMob) iconMob.textContent = emoji;
    }

    /**
     * Apply theme to <html> and re-render chart if active
     */
    function applyTheme(theme) {
        const isDark = (theme === 'dark');
        if (isDark) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        syncThemeIcons(isDark);
        localStorage.setItem('theme', theme);

        // Re-render chart with correct colors
        if (localChartInstance && currentMarket && currentDetailHistory.length > 0) {
            renderLocalChart(currentDetailHistory, currentMarket);
        }
    }

    /**
     * Toggle current theme
     */
    function toggleTheme() {
        const isDark = document.documentElement.classList.contains('dark');
        applyTheme(isDark ? 'light' : 'dark');
    }

    // Load saved theme on page load (before DOMContentLoaded flicker)
    (function () {
        const saved = localStorage.getItem('theme') || 'light';
        applyTheme(saved);
    })();

    // Wire up desktop & mobile toggle buttons
    document.addEventListener('DOMContentLoaded', function () {
        const btnDesktop = document.getElementById('theme-toggle');
        const btnMobile  = document.getElementById('theme-toggle-mob');
        if (btnDesktop) btnDesktop.addEventListener('click', toggleTheme);
        if (btnMobile)  btnMobile.addEventListener('click', toggleTheme);
    });

    // ================================================================
    // WINDOW ONLOAD
    // ================================================================
    window.onload = function() {
        fetch(`${API_BASE}/get_settings`).then(res => res.json()).then(data => {
            if (data.token)      document.getElementById('token').value = data.token;
            if (data.account_id) {
                const select = document.getElementById('account-id');
                if (!select.querySelector(`option[value="${data.account_id}"]`)) {
                    select.innerHTML += `<option value="${data.account_id}">ID: ${data.account_id} (Tersimpan)</option>`;
                }
                select.value = data.account_id;
            }
        });

        renderMarketCards();
        startDashboardPolling();
        startRealtimeClock();

        const tradeInput = document.getElementById('trade-amount');
        if (tradeInput) {
            let initialVal = parseFloat(tradeInput.value) || 0;
            document.getElementById('nav-order-val').innerText = '$' + initialVal.toFixed(2);
            tradeInput.addEventListener('input', function(e) {
                let val = parseFloat(e.target.value) || 0;
                document.getElementById('nav-order-val').innerText = '$' + val.toFixed(2);
            });
        }
    }

    // ================================================================
    // VIEW MANAGEMENT
    // ================================================================
    function clearAllIntervals() {
        if (dashboardInterval) clearInterval(dashboardInterval);
        if (detailInterval)    clearInterval(detailInterval);
        if (historyInterval)   clearInterval(historyInterval);
    }

    function showView(viewName) {
        document.getElementById('view-dashboard').classList.add('hidden');
        document.getElementById('view-detail').classList.add('hidden');
        document.getElementById('view-trade').classList.add('hidden');
        document.getElementById('view-history').classList.add('hidden');
        document.getElementById('view-rodis').classList.add('hidden');
        document.getElementById('view-' + viewName).classList.remove('hidden');

        const navIds = [
            'nav-link-dashboard','nav-link-trade','nav-link-history','nav-link-rodis',
            'nav-link-dashboard-mob','nav-link-trade-mob','nav-link-history-mob','nav-link-rodis-mob'
        ];
        navIds.forEach(id => {
            let el = document.getElementById(id);
            if (el) { el.classList.remove('text-indigo-600', 'text-gojek'); el.classList.add('text-gray-500'); }
        });

        let activeBase  = viewName === 'detail' ? 'dashboard' : viewName;
        let deskNav     = document.getElementById('nav-link-' + activeBase);
        let mobNav      = document.getElementById('nav-link-' + activeBase + '-mob');
        let colorClass  = viewName === 'rodis' ? 'text-indigo-600' : 'text-gojek';
        if (deskNav) { deskNav.classList.remove('text-gray-500'); deskNav.classList.add(colorClass); }
        if (mobNav)  { mobNav.classList.remove('text-gray-500');  mobNav.classList.add(colorClass); }

        clearAllIntervals();

        if (viewName === 'dashboard') {
            currentMarket = "";
            startDashboardPolling();
        } else if (viewName === 'detail') {
            detailCurrentPage = 1;
            refreshDetailData();
            detailInterval = setInterval(refreshDetailData, 1500);
        } else if (viewName === 'trade') {
            fetch(`${API_BASE}/status_all`).then(res => res.json()).then(data => {
                if (data.balance !== undefined && data.balance !== null)
                    document.getElementById('nav-balance').innerText = formatCurrency(data.balance);
                activeMarketsList = data.active_markets || [];
                renderTradeMarkets();
            });
        } else if (viewName === 'history') {
            historyCurrentPage = 1;
            refreshHistoryData();
            historyInterval = setInterval(refreshHistoryData, 2000);
        } else if (viewName === 'rodis') {
            fetch(`${API_BASE}/status_all`).then(res => res.json()).then(data => {
                activeMarketsList = data.active_markets || [];
                const select = document.getElementById('rodis-market-select');
                select.innerHTML = '';
                if (activeMarketsList.length === 0)
                    select.innerHTML = `<option value="">(Belum ada market aktif)</option>`;
                else
                    activeMarketsList.forEach(m => {
                        select.innerHTML += `<option value="${m}" ${m === rodisState.market ? 'selected' : ''}>${m}</option>`;
                    });
            });
        }
    }

    // ================================================================
    // ACCOUNT CHECK
    // ================================================================
    function checkAccounts() {
        const token = document.getElementById('token').value;
        if (!token) return alert('Silakan isi Access Token!');
        const btn = document.getElementById('btn-cek-akun');
        btn.innerHTML = '⏳ Cek...';
        btn.disabled = true;
        fetch(`${API_BASE}/check_accounts`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ token: token })
        }).then(res => res.json()).then(data => {
            btn.innerHTML = '🔍 Cek Akun';
            btn.disabled  = false;
            if (data.status === 'success') {
                const select = document.getElementById('account-id');
                const oldVal = select.value;
                select.innerHTML = '<option value="">-- Pilih Akun --</option>';
                data.accounts.forEach(acc => {
                    const typeLabel = acc.type === 'Demo' ? '🎮 Demo' : '💼 Real';
                    const option    = document.createElement('option');
                    option.value    = acc.id;
                    option.text     = `${typeLabel} - ${acc.id} (${formatCurrency(acc.balance)})`;
                    select.appendChild(option);
                });
                if (oldVal) select.value = oldVal;
                alert('✅ Akun berhasil dimuat! Silakan pilih di dropdown.');
            } else {
                alert(data.message);
            }
        }).catch(() => {
            btn.innerHTML = '🔍 Cek Akun';
            btn.disabled  = false;
        });
    }

    // ================================================================
    // PLAY / STOP SYNC
    // ================================================================
    function syncPlayStopButton() {
        const playBtn = document.getElementById('btn-play');
        const stopBtn = document.getElementById('btn-stop');
        if (!playBtn || !stopBtn) return;
        const connected = (activeMarketsList.length > 0);
        if (connected) {
            playBtn.classList.add('opacity-50', 'cursor-not-allowed');    playBtn.disabled = true;
            stopBtn.classList.remove('opacity-50', 'cursor-not-allowed'); stopBtn.disabled = false;
        } else {
            playBtn.classList.remove('opacity-50', 'cursor-not-allowed'); playBtn.disabled = false;
            stopBtn.classList.add('opacity-50', 'cursor-not-allowed');    stopBtn.disabled = true;
        }
    }

    // ================================================================
    // POPUP & CONFIRM
    // ================================================================
    function showPopup(message, type = "success") {
        const container = document.getElementById("popup-container");
        const color = type === "success" ? "bg-emerald-500" : "bg-red-500";
        const popup = document.createElement("div");
        popup.className = `${color} text-white px-5 py-3 rounded-xl shadow-lg animate-popup text-sm font-bold`;
        popup.innerText = message;
        container.appendChild(popup);
        setTimeout(() => {
            popup.style.opacity   = "0";
            popup.style.transform = "translateX(40px)";
            setTimeout(() => popup.remove(), 400);
        }, 2500);
    }

    function showConfirm(message, onYes) {
        const overlay = document.createElement("div");
        overlay.className = "fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-[9999] animate-fadeIn";
        const modal = document.createElement("div");
        modal.className = "bg-white rounded-2xl shadow-2xl p-6 w-[90%] max-w-md animate-scaleIn";
        modal.innerHTML = `
            <div class="text-gray-800 font-semibold text-sm mb-5">${message}</div>
            <div class="flex justify-end gap-3">
                <button id="confirm-cancel" class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 font-semibold text-sm">Batal</button>
                <button id="confirm-yes"    class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white font-bold text-sm">Ya, Reset</button>
            </div>`;
        overlay.appendChild(modal);
        document.body.appendChild(overlay);
        modal.querySelector("#confirm-cancel").onclick = () => overlay.remove();
        modal.querySelector("#confirm-yes").onclick    = () => { overlay.remove(); onYes(); };
        overlay.onclick = (e) => { if (e.target === overlay) overlay.remove(); };
    }

    // ================================================================
    // MARKET ACTIONS
    // ================================================================
    function startAllMarkets() {
        const token     = document.getElementById('token').value;
        const accountId = document.getElementById('account-id').value;
        if (!token || !accountId) { showPopup("⚠️ Harap isi Access Token & Target Account ID terlebih dahulu!", "error"); return; }
        const btn = event.target;
        let originalText = btn.innerHTML;
        btn.innerHTML = '⏳ Starting...';
        btn.disabled  = true;
        fetch(`${API_BASE}/start_all`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ token: token, account_id: accountId })
        }).then(res => res.json()).then(data => {
            btn.innerHTML = originalText;
            btn.disabled  = false;
            showPopup(`✅ ${data.message}`, "success");
            refreshDashboardStatus();
        }).catch(() => {
            btn.innerHTML = originalText;
            btn.disabled  = false;
            showPopup("❌ Gagal menghubungkan market", "error");
        });
    }

    function resetAllMarkets() {
        showConfirm("Apakah Anda yakin ingin MERESET SEMUA data history market?", () => {
            fetch(`${API_BASE}/reset_all`, { method: 'POST' })
                .then(res => res.json())
                .then(data => { showPopup(`🔄 ${data.message}`, "success"); refreshDashboardStatus(); })
                .catch(() => showPopup("❌ Gagal mereset data market", "error"));
        });
    }

    function activateMassTelegram(event) {
        const targetLoss = document.getElementById('mass-tg-loss').value;
        const btn = event.currentTarget;
        const originalText = btn.innerHTML;
        btn.innerHTML = '⏳ Memproses...';
        btn.disabled  = true;
        fetch(`${API_BASE}/toggle_telegram_all`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ target_loss: targetLoss })
        }).then(res => res.json()).then(data => {
            btn.innerHTML = originalText;
            btn.disabled  = false;
            if (data.status === 'success') { showPopup(`📲 ${data.message}`, "success"); refreshDashboardStatus(); }
            else showPopup(`❌ ${data.message}`, "error");
        }).catch(() => {
            btn.innerHTML = originalText;
            btn.disabled  = false;
            showPopup("❌ Gagal terhubung ke server", "error");
        });
    }

    function resetCurrentMarket() {
        if (!currentMarket) return;
        if (!confirm(`Apakah Anda yakin ingin mereset semua data analisis untuk market ${currentMarket}?\nData historis candle dan perhitungan akan diulang dari nol.`)) return;
        fetch(`${API_BASE}/reset_market`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ market: currentMarket })
        }).then(res => res.json()).then(data => {
            if (data.status === 'success') {
                if (rodisState.active && rodisState.market === currentMarket) {
                    rodisState.tradeCounter = 0;
                    rodisState.lastProcessedCandle = null;
                    rodisState.phase = 'IDLE';
                    document.getElementById('rodis-target-op').innerText     = '0';
                    document.getElementById('rodis-current-loss').innerText  = '0';
                    logRodis(`🔄 [RESET MANUAL] Data market ${currentMarket} telah dibersihkan. Memulai penghitungan target dari 0 kembali.`, "#fbbf24");
                }
                if (localChartInstance) {
                    localChartInstance.destroy();
                    localChartInstance = null;
                    document.getElementById('local-chart-container').innerHTML = '';
                }
                refreshDetailData();
                alert(`✅ Berhasil! Semua data analisis untuk market ${currentMarket} telah direset dari awal.`);
            } else {
                alert(`❌ ${data.message}`);
            }
        });
    }

    function toggleTelegramServer() {
        if (!currentMarket) return;
        const inLoss = document.getElementById('tg-target-loss').value;
        fetch(`${API_BASE}/toggle_telegram`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ market: currentMarket, target_loss: inLoss })
        }).then(res => res.json()).then(data => {
            if (data.status === 'success') {
                refreshDetailData();
                if (data.active) alert(`✅ Sinyal Telegram diaktifkan di Server untuk market ${currentMarket}!`);
            } else {
                alert(`❌ ${data.message}`);
            }
        });
    }

    // ================================================================
    // RODIS BOT
    // ================================================================
    function logRodis(msg, color = "#4ade80") {
        const term    = document.getElementById('rodis-terminal');
        let timeStr   = new Date().toLocaleTimeString('id-ID');
        term.innerHTML =
            `<div style="margin-bottom:6px;"><span style="color:#64748b;font-size:0.75rem;">[${timeStr}]</span> <span style="color:${color}">${msg}</span></div>`
            + term.innerHTML;
    }

    function toggleRodisBot() {
        const btn    = document.getElementById('btn-rodis-toggle');
        const selMkt = document.getElementById('rodis-market-select');
        const inLoss = document.getElementById('rodis-target-loss');
        const inAmt  = document.getElementById('rodis-amount');
        const inDur  = document.getElementById('rodis-duration');

        if (!rodisState.active) {
            if (!selMkt.value) return alert("Silakan hubungkan minimal 1 market di menu Monitor!");
            rodisState.active     = true;
            rodisState.market     = selMkt.value;
            rodisState.targetLoss = parseInt(inLoss.value) || 7;
            rodisState.amount     = parseFloat(inAmt.value) || 10;
            rodisState.duration   = parseInt(inDur.value) || 60;
            rodisState.phase      = 'IDLE';
            selMkt.disabled = inLoss.disabled = inAmt.disabled = inDur.disabled = true;
            btn.innerHTML = '⏹ MATIKAN RODIS';
            btn.classList.replace('bg-indigo-500', 'bg-red-500');
            btn.classList.replace('hover:bg-indigo-400', 'hover:bg-red-400');
            btn.classList.replace('shadow-[0_0_20px_rgba(99,102,241,0.4)]', 'shadow-[0_0_20px_rgba(239,68,68,0.4)]');
            logRodis(`🚀 RODIS DIAKTIFKAN! Memantau ${rodisState.market}. Target False: ${rodisState.targetLoss}.`, "#22c55e");
            fetch(`${API_BASE}/data?market=${encodeURIComponent(rodisState.market)}`).then(res => res.json()).then(data => {
                let sl = calculateSigLoss(data.history);
                rodisState.tradeCounter = Math.floor(sl / rodisState.targetLoss);
                document.getElementById('rodis-current-loss').innerText = sl;
                document.getElementById('rodis-target-op').innerText    = rodisState.tradeCounter + 1;
                logRodis(`Sistem bersiaga membaca lilin. Target selanjutnya: False ke-${(rodisState.tradeCounter * rodisState.targetLoss) + rodisState.targetLoss}.`, "#60a5fa");
                rodisInterval = setInterval(runRodisLoop, 2000);
            });
        } else {
            rodisState.active = false;
            clearInterval(rodisInterval);
            selMkt.disabled = inLoss.disabled = inAmt.disabled = inDur.disabled = false;
            btn.innerHTML = '▶ NYALAKAN RODIS';
            btn.classList.replace('bg-red-500', 'bg-indigo-500');
            btn.classList.replace('hover:bg-red-400', 'hover:bg-indigo-400');
            btn.classList.replace('shadow-[0_0_20px_rgba(239,68,68,0.4)]', 'shadow-[0_0_20px_rgba(99,102,241,0.4)]');
            logRodis(`🛑 RODIS DIMATIKAN. Robot Auto-Trade telah dihentikan.`, "#f87171");
        }
    }

    function runRodisLoop() {
        if (!rodisState.active) return;
        fetch(`${API_BASE}/data?market=${encodeURIComponent(rodisState.market)}`)
            .then(res => res.json()).then(data => {
                if (!data.is_running) return;
                let sigLoss = calculateSigLoss(data.history);
                document.getElementById('rodis-current-loss').innerText = sigLoss;
                if (data.history && data.history.length > 0) {
                    let latestC = data.history.filter(item => item.warna === "Hijau" || item.warna === "Merah")[0];
                    if (latestC) {
                        let mm       = parseInt(latestC.waktu.split(':')[1]);
                        let candleId = latestC.tanggal + "_" + latestC.waktu;
                        if (rodisState.lastProcessedCandle !== candleId) {
                            if (rodisState.phase === 'IDLE' && (mm % 5 === 2)) {
                                let expectedTrades = Math.floor(sigLoss / rodisState.targetLoss);
                                if (expectedTrades > rodisState.tradeCounter && sigLoss > 0) {
                                    rodisState.tradeCounter++;
                                    rodisState.phase = 'WAIT_CONF';
                                    rodisState.lastProcessedCandle = candleId;
                                    document.getElementById('rodis-target-op').innerText = rodisState.tradeCounter;
                                    let nextMin = (mm + 3).toString().padStart(2, '0');
                                    logRodis(`⏳ [STANDBY] Target False ke-${sigLoss} tercapai! Membaca arah di penutupan menit ${nextMin}...`, "#fbbf24");
                                    let msg = `⏳ *RODIS AUTO-TRADE: STANDBY* ⏳\n\n📈 *Market:* ${rodisState.market}\n🗓 *Waktu:* ${latestC.tanggal} | ${latestC.waktu} WIB\n\nSistem mendeteksi bahwa *Target Signal False ke-${sigLoss}* telah tercapai!\nRODIS saat ini sedang bersiaga (loading) membaca arah market.\nEksekusi Open Posisi akan ditentukan pada penutupan candle menit ke-${nextMin}.\n\nMohon bersabar, sistem berjalan otomatis... 🤖`;
                                    fetch(`${API_BASE}/send_wa`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ message: msg }) });
                                }
                            } else if (rodisState.phase === 'WAIT_CONF' && (mm % 5 === 0)) {
                                rodisState.phase     = 'WAIT_RES';
                                rodisState.direction = (latestC.warna === 'Hijau') ? 'up' : 'down';
                                rodisState.lastProcessedCandle = candleId;
                                let dirStr  = (latestC.warna === 'Hijau') ? 'BUY NAIK 🟢' : 'SELL TURUN 🔴';
                                let nextMin = (mm + 2).toString().padStart(2, '0');
                                logRodis(`🔥 [EKSEKUSI] Candle penentu menit ${mm} berwarna ${latestC.warna.toUpperCase()}. RODIS otomatis mengeksekusi order: ${dirStr}! Menunggu hasil...`, "#c084fc");
                                let msg = `🚀 *RODIS AUTO-TRADE: EKSEKUSI* 🚀\n\n📈 *Market:* ${rodisState.market}\n🗓 *Waktu:* ${latestC.tanggal} | ${latestC.waktu} WIB\n\nCandle penentu telah selesai dengan warna *${latestC.warna.toUpperCase()}*.\nRODIS secara otomatis mengeksekusi order:\n👉 *${dirStr}* senilai $${rodisState.amount}\n\nSistem sedang memproses (loading) hasil trading. Hasil akan diumumkan setelah penutupan candle menit ke-${nextMin}.`;
                                fetch(`${API_BASE}/send_wa`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ message: msg }) });
                                fetch(`${API_BASE}/manual_trade`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ market: rodisState.market, direction: rodisState.direction, amount: rodisState.amount, duration: rodisState.duration }) });
                            } else if (rodisState.phase === 'WAIT_RES' && (mm % 5 === 2)) {
                                rodisState.phase = 'IDLE';
                                rodisState.lastProcessedCandle = candleId;
                                let requiredColor = rodisState.direction === 'up' ? 'Hijau' : 'Merah';
                                let isWin  = (latestC.warna === requiredColor);
                                let resMsg = isWin ? 'TRUE ✅' : 'FALSE ❌';
                                let resColor = isWin ? '#22c55e' : '#f87171';
                                let nextTargetLoss = (rodisState.tradeCounter * rodisState.targetLoss) + rodisState.targetLoss;
                                logRodis(`🎯 [HASIL] Auto-Trade ke-${rodisState.tradeCounter} selesai. Hasil Akhir: ${resMsg}. Kembali bersiaga menunggu False ke-${nextTargetLoss}.`, resColor);
                                document.getElementById('rodis-target-op').innerText = rodisState.tradeCounter + 1;
                                let msg = `🎯 *RODIS AUTO-TRADE: HASIL* 🎯\n\nTarget Open Posisi Ke: ${rodisState.tradeCounter}\n📈 *Market:* ${rodisState.market}\n🗓 *Waktu:* ${latestC.tanggal} | ${latestC.waktu} WIB\n\nArah Eksekusi Tadi: *${rodisState.direction === 'up' ? 'BUY 🟢' : 'SELL 🔴'}*\nWarna Candle Hasil: *${latestC.warna.toUpperCase()}*\n\nStatus Hasil Akhir: *${resMsg}*\n\nRODIS kembali bersiaga memantau market untuk Target Open Posisi ke-${rodisState.tradeCounter + 1} (Menunggu False ke-${nextTargetLoss}).`;
                                fetch(`${API_BASE}/send_wa`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ message: msg }) });
                            }
                        }
                    }
                }
            }).catch(() => {});
    }

    // ================================================================
    // CALCULATE SIG LOSS
    // ================================================================
    function calculateSigLoss(historyArr) {
        let sigLoss = 0;
        if (historyArr && historyArr.length > 0) {
            const candles = historyArr.filter(item => item.warna === "Hijau" || item.warna === "Merah");
            let blocks = {};
            candles.forEach(c => {
                if (c.waktu && c.waktu.includes(':')) {
                    let parts  = c.waktu.split(':');
                    let hh     = parts[0];
                    let mm     = parseInt(parts[1]);
                    let baseMm = Math.floor(mm / 5) * 5;
                    let key    = c.tanggal + '_' + hh + ':' + baseMm.toString().padStart(2, '0');
                    if (!blocks[key]) blocks[key] = {};
                    if (mm % 5 === 0) blocks[key].c1 = c.warna;
                    if (mm % 5 === 2) blocks[key].c2 = c.warna;
                }
            });
            let sortedKeys = Object.keys(blocks).sort((a, b) => b.localeCompare(a));
            for (let k of sortedKeys) {
                let b = blocks[k];
                if (b.c1 && b.c2) {
                    if (b.c1 !== b.c2) sigLoss++;
                    else break;
                }
            }
        }
        return sigLoss;
    }

    // ================================================================
    // MARKET CARDS & TRADE
    // ================================================================
    function renderMarketCards() {
        const container = document.getElementById('market-grid-container');
        if (container) container.innerHTML = '';
    }

    function renderTradeMarkets() {
        const container = document.getElementById('trade-market-container');
        container.innerHTML = '';
        if (activeMarketsList.length === 0) {
            container.innerHTML = `<div class="col-span-full text-center text-gray-400 py-10 font-bold">⚠️ Belum ada market aktif.</div>`;
            document.getElementById('trade-panel').classList.add('hidden');
            return;
        }
        activeMarketsList.forEach(m => {
            const marketObj   = allMarkets.find(x => x.id === m) || { id: m, name: m, icon: '📈', cat: 'Aktif' };
            const isSelected  = (selectedTradeMarket === m) ? 'border-gojek bg-green-50 shadow-md' : 'border-gray-100 bg-white hover:border-gray-300';
            container.innerHTML += `<div onclick="selectTradeMarket('${m}')" class="cursor-pointer border-2 rounded-2xl p-4 flex flex-col items-center transition-all ${isSelected}"><div class="text-3xl mb-2">${marketObj.icon}</div><h4 class="font-bold text-dark text-sm">${marketObj.name}</h4><div class="w-2 h-2 bg-gojek rounded-full mt-2 shadow-[0_0_8px_#00aa13] animate-pulse"></div></div>`;
        });
    }

    function selectTradeMarket(marketId) {
        selectedTradeMarket = marketId;
        renderTradeMarkets();
        document.getElementById('trade-panel').classList.remove('hidden');
        document.getElementById('trade-selected-market').innerText = `(${marketId})`;
    }

    function renderPagination() {
        const totalPages = Math.ceil(allMarkets.length / itemsPerPage);
        const container  = document.getElementById('pagination-controls');
        container.innerHTML = '';
        const prevDisabled = currentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100';
        container.innerHTML += `<button onclick="changePage(${currentPage - 1})" class="px-4 py-2 bg-white border rounded-xl text-sm font-bold ${prevDisabled}">Prev</button>`;
        for (let i = 1; i <= totalPages; i++) {
            const activeClass = i === currentPage ? 'bg-gojek text-white' : 'bg-white text-dark hover:bg-gray-100';
            container.innerHTML += `<button onclick="changePage(${i})" class="w-10 h-10 border rounded-xl text-sm font-bold ${activeClass}">${i}</button>`;
        }
        const nextDisabled = currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100';
        container.innerHTML += `<button onclick="changePage(${currentPage + 1})" class="px-4 py-2 bg-white border rounded-xl text-sm font-bold ${nextDisabled}">Next</button>`;
    }

    function changePage(page) {
        if (page < 1 || page > Math.ceil(allMarkets.length / itemsPerPage)) return;
        currentPage = page;
        renderMarketCards();
    }

    // ================================================================
    // DASHBOARD POLLING
    // ================================================================
    function startDashboardPolling() {
        refreshDashboardStatus();
        dashboardInterval = setInterval(refreshDashboardStatus, 3000);
    }

    function refreshDashboardStatus() {
        fetch(`${API_BASE}/status_all`).then(res => res.json()).then(data => {
            if (data.balance !== undefined && data.balance !== null)
                document.getElementById('nav-balance').innerText = formatCurrency(data.balance);

            activeMarketsList = data.active_markets || [];
            syncPlayStopButton();

            document.querySelectorAll('.market-card').forEach(card => {
                if (activeMarketsList.includes(card.getAttribute('data-market'))) card.classList.add('is-active');
                else card.classList.remove('is-active');
            });

            let botCountEl = document.getElementById('lbl-bot-count');
            let tgCountEl  = document.getElementById('lbl-tg-count');
            if (botCountEl) botCountEl.innerText = `${activeMarketsList.length}/27`;
            if (tgCountEl) {
                let tgCount = data.tg_active_count || 0;
                if (tgCount > 0) { tgCountEl.innerText = `ON (${tgCount} Market)`; tgCountEl.className = 'text-blue-600 font-extrabold'; }
                else             { tgCountEl.innerText = 'OFF'; tgCountEl.className = 'text-gray-400 font-bold'; }
            }

            // LIVE STREAK
            const streakContainer = document.getElementById('live-streak-container');
            const streakList      = document.getElementById('streak-list');
            if (streakContainer && streakList) {
                streakContainer.classList.remove('hidden');
                streakContainer.classList.add('bg-white');
                if (!data.market_streaks || Object.keys(data.market_streaks).length === 0) {
                    streakList.innerHTML = `<span class="text-xs text-gray-400 italic">Semua market berhenti</span>`;
                    return;
                }
                streakList.innerHTML = '';
                const totalMarket  = allMarkets.length;
                const activeCount  = activeMarketsList.length;
                const allConnected = (activeCount === totalMarket && totalMarket > 0);
                let sortedMarkets  = Object.keys(data.market_streaks).sort((a, b) => data.market_streaks[b] - data.market_streaks[a]);
                const highestStreak = sortedMarkets.length > 0 ? data.market_streaks[sortedMarkets[0]] : 0;

                sortedMarkets.forEach(mkt => {
                    let streak  = data.market_streaks[mkt];
                    let mktObj  = allMarkets.find(x => x.id === mkt);
                    let mktName = mktObj ? mktObj.name : mkt;
                    let colorClass = 'bg-gray-50 text-gray-500 border-gray-200';
                    if (streak >= 7) colorClass = 'bg-red-100 text-red-700 border-red-300 font-extrabold';
                    else if (streak >= 5) colorClass = 'bg-orange-100 text-orange-700 border-orange-300 font-bold';
                    else if (streak >= 3) colorClass = 'bg-yellow-100 text-yellow-700 border-yellow-300 font-bold';
                    else if (streak >= 1) colorClass = 'bg-blue-50 text-blue-600 border-blue-200';
                    let connectedClass = allConnected ? 'bg-green-100 text-green-800 border-green-300 font-bold' : '';
                    let dangerGlow     = (streak === highestStreak && streak >= 7) ? 'danger-glow' : '';
                    streakList.innerHTML += `
                        <div onclick="openMarketDetail('${mkt}')"
                            class="w-full px-3 py-1.5 rounded-lg border text-[11px] flex items-center justify-between
                                   ${colorClass} ${connectedClass} ${dangerGlow}
                                   transition-all duration-300 cursor-pointer hover:scale-[1.04] hover:shadow-md active:scale-95">
                            <span class="truncate font-semibold">${mktName}</span>
                            <span class="bg-white/90 px-2 py-0.5 rounded text-[10px] uppercase tracking-wider border border-white/50">False: ${streak}</span>
                        </div>`;
                });
            }

            // DOJI ANALYTICS RENDER
            renderDojiAnalytics(data.doji_analytics);
        });
    }

    // ================================================================
    // DETAIL VIEW
    // ================================================================
    function openMarketDetail(marketName) {
        currentMarket = marketName;
        document.getElementById('detail-market-name').innerText = marketName;
        if (localChartInstance) {
            localChartInstance.destroy();
            localChartInstance = null;
            document.getElementById('local-chart-container').innerHTML =
                '<div class="flex h-full items-center justify-center text-gray-400 font-bold text-xs sm:text-base py-20">Memproses Data Histori...</div>';
        }
        showView('detail');
    }

    function startCurrentMarketBot() {
        const token     = document.getElementById('token').value;
        const accountId = document.getElementById('account-id').value;
        if (!token || !accountId) return alert("Harap isi Access Token & Target Account ID di Pusat Kendali!");
        document.getElementById('table-body').innerHTML = `<tr><td colspan="3" class="py-20 text-center">⏳ Membangun koneksi ke broker...</td></tr>`;
        fetch(`${API_BASE}/start`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ token: token, market: currentMarket, account_id: accountId })
        }).then(res => res.json()).then(data => {
            if (data.status === 'error') { alert(data.message); showView('dashboard'); }
            else refreshDetailData();
        });
    }

    function stopCurrentMarketBot() {
        fetch(`${API_BASE}/stop`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ market: currentMarket })
        }).then(() => refreshDetailData());
    }

    function executeTradeFromPanel(direction) {
        if (!selectedTradeMarket) return alert('Pilih market yang aktif terlebih dahulu!');
        const amount   = document.getElementById('trade-amount').value;
        const duration = document.getElementById('trade-duration').value;
        fetch(`${API_BASE}/manual_trade`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ market: selectedTradeMarket, direction: direction, amount: amount, duration: duration })
        }).then(res => res.json()).then(data => {
            if (data.status === 'error') alert(`❌ ${data.message}`);
            else alert(`✅ Perintah dikirim! Cek Riwayat.`);
        });
    }

    // ================================================================
    // HISTORY
    // ================================================================
    function refreshHistoryData() {
        fetch(`${API_BASE}/trade_history`).then(res => res.json()).then(data => {
            currentTradeHistory = data.trade_history || [];
            renderHistoryTable();
        });
    }

    function renderHistoryTable() {
        const tbody    = document.getElementById('history-table-body');
        const start    = (historyCurrentPage - 1) * historyItemsPerPage;
        const paginated = currentTradeHistory.slice(start, start + historyItemsPerPage);
        tbody.innerHTML = '';
        if (paginated.length > 0) {
            paginated.forEach(item => {
                let pillClass = "pill-abu";
                let label     = item.warna;
                if (item.warna.includes("GAGAL"))       { pillClass = "pill-error"; }
                else if (item.warna.includes("UP"))     { pillClass = "pill-manual-up";   label = "BUY NAIK"; }
                else if (item.warna.includes("DOWN"))   { pillClass = "pill-manual-down"; label = "SELL TURUN"; }
                let amountStr = item.amount ? `$${item.amount}` : '-';
                tbody.innerHTML += `<tr class="hover:bg-gray-50/50"><td class="py-4 px-8"><span class="text-base font-bold text-dark">${item.waktu}</span><span class="block text-xs text-gray-400">${item.tanggal}</span></td><td class="py-4 px-8 font-bold text-dark">${item.market}</td><td class="py-4 px-8 font-bold text-indigo-600">${amountStr}</td><td class="py-4 px-8"><span class="pill ${pillClass}">${label}</span></td></tr>`;
            });
        } else {
            tbody.innerHTML = `<tr><td colspan="4" class="py-20 text-center text-gray-500">Belum ada riwayat trade.</td></tr>`;
        }
        renderHistoryPagination();
    }

    function renderHistoryPagination() {
        const container  = document.getElementById('history-pagination-controls');
        container.innerHTML = '';
        if (currentTradeHistory.length === 0) return;
        const totalPages = Math.ceil(currentTradeHistory.length / historyItemsPerPage) || 1;
        const prevDisabled = historyCurrentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100 cursor-pointer';
        container.innerHTML += `<button onclick="changeHistoryPage(${historyCurrentPage - 1})" class="px-3 py-1 bg-white border rounded-lg text-xs font-bold ${prevDisabled}">Prev</button>`;
        let startPage = Math.max(1, historyCurrentPage - 2);
        let endPage   = Math.min(totalPages, historyCurrentPage + 2);
        for (let i = startPage; i <= endPage; i++) {
            const activeClass = i === historyCurrentPage ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 cursor-pointer';
            container.innerHTML += `<button onclick="changeHistoryPage(${i})" class="w-8 h-8 border rounded-lg text-xs font-bold ${activeClass}">${i}</button>`;
        }
        const nextDisabled = historyCurrentPage === totalPages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100 cursor-pointer';
        container.innerHTML += `<button onclick="changeHistoryPage(${historyCurrentPage + 1})" class="px-3 py-1 bg-white border rounded-lg text-xs font-bold ${nextDisabled}">Next</button>`;
    }

    function changeHistoryPage(page) {
        const totalPages = Math.ceil(currentTradeHistory.length / historyItemsPerPage);
        if (page < 1 || page > totalPages) return;
        historyCurrentPage = page;
        renderHistoryTable();
    }

    // ================================================================
    // DETAIL DATA & TABLE
    // ================================================================
    function refreshDetailData() {
        if (!currentMarket) return;
        fetch(`${API_BASE}/data?market=${encodeURIComponent(currentMarket)}`).then(res => res.json()).then(data => {
            if (data.balance !== undefined && data.balance !== null)
                document.getElementById('nav-balance').innerText = formatCurrency(data.balance);

            if (data.is_running) {
                document.getElementById('btn-start-bot').classList.add('hidden');
                document.getElementById('btn-stop-bot').classList.remove('hidden');
                document.getElementById('detail-status').innerHTML = `<span class="text-gojek font-bold">🟢 Bot Aktif.</span> Memonitor pergerakan harga.`;
            } else {
                document.getElementById('btn-start-bot').classList.remove('hidden');
                document.getElementById('btn-stop-bot').classList.add('hidden');
                document.getElementById('detail-status').innerHTML = `<span class="text-red font-bold">🔴 Offline.</span> Bot terhenti.`;
            }

            document.getElementById('val-total').innerText = data.stats.total_trade;
            let sigLoss = calculateSigLoss(data.history);
            document.getElementById('val-sig-loss').innerText = sigLoss;

            let sigWin = 0;
            if (data.history && data.history.length > 0) {
                const candles = data.history.filter(item => item.warna === "Hijau" || item.warna === "Merah");
                let blocks = {};
                candles.forEach(c => {
                    if (c.waktu && c.waktu.includes(':')) {
                        let parts  = c.waktu.split(':');
                        let hh     = parts[0];
                        let mm     = parseInt(parts[1]);
                        let baseMm = Math.floor(mm / 5) * 5;
                        let key    = c.tanggal + '_' + hh + ':' + baseMm.toString().padStart(2, '0');
                        if (!blocks[key]) blocks[key] = {};
                        if (mm % 5 === 0) blocks[key].c1 = c.warna;
                        if (mm % 5 === 2) blocks[key].c2 = c.warna;
                    }
                });
                for (let k in blocks) {
                    let b = blocks[k];
                    if (b.c1 && b.c2 && b.c1 === b.c2) sigWin++;
                }
            }
            document.getElementById('val-sig-win').innerText  = sigWin;
            document.getElementById('val-hijau').innerText    = data.stats.total_hijau;
            document.getElementById('val-merah').innerText    = data.stats.total_merah;

            if (data.telegram) {
                const btn    = document.getElementById('btn-tg-toggle');
                const stText = document.getElementById('tg-status-text');
                const inLoss = document.getElementById('tg-target-loss');
                if (data.telegram.active) {
                    btn.classList.replace('bg-blue-600', 'bg-red-600');
                    btn.innerText = '⏹ Hentikan Telegram';
                    stText.innerText = 'Status: AKTIF (Server 24 Jam)';
                    stText.classList.replace('text-gray-500', 'text-gojek');
                    if (document.activeElement !== inLoss) inLoss.value = data.telegram.target_loss;
                    inLoss.disabled = true;
                    if (sigLoss > 0) document.getElementById('val-target-op').innerText = data.telegram.trade_counter + 1;
                } else {
                    btn.classList.replace('bg-red-600', 'bg-blue-600');
                    btn.innerText = 'Aktifkan Telegram';
                    stText.innerText = 'Status: NONAKTIF';
                    stText.classList.replace('text-gojek', 'text-gray-500');
                    inLoss.disabled = false;
                    document.getElementById('val-target-op').innerText = 0;
                }
            }

            currentDetailHistory = data.history || [];
            renderLocalChart(currentDetailHistory, currentMarket);

            if (!data.is_running && currentDetailHistory.length === 0) {
                document.getElementById('table-body').innerHTML = `<tr><td colspan="3" class="py-20 text-center text-gray-500 text-xs sm:text-sm">Silakan klik "Hubungkan Bot" terlebih dahulu.</td></tr>`;
                document.getElementById('detail-pagination-controls').innerHTML = '';
            } else {
                renderDetailTable();
            }
        });
    }

    function renderDetailTable() {
        const tbody     = document.getElementById('table-body');
        const start     = (detailCurrentPage - 1) * detailItemsPerPage;
        const paginated = currentDetailHistory.slice(start, start + detailItemsPerPage);
        tbody.innerHTML = '';
        if (paginated.length > 0) {
            paginated.forEach(item => {
                let pillClass  = "pill-abu";
                let label      = item.warna;
                if (item.warna.includes("Hijau")) pillClass = "pill-hijau";
                else if (item.warna.includes("Merah")) pillClass = "pill-merah";
                let marketName = item.market ? item.market : currentMarket;
                tbody.innerHTML += `
                    <tr class="hover:bg-gray-50/50">
                        <td class="py-4 px-4 sm:px-8">
                            <div class="text-xs sm:text-base font-bold text-dark">${item.waktu}</div>
                            <div class="text-[10px] sm:text-xs text-gray-400 mt-0.5">${item.tanggal}</div>
                        </td>
                        <td class="py-4 px-4 sm:px-8 font-bold text-dark text-xs sm:text-base">${marketName}</td>
                        <td class="py-4 px-4 sm:px-8"><span class="pill ${pillClass} text-[10px] sm:text-xs">${label}</span></td>
                    </tr>`;
            });
        }
        renderDetailPagination();
    }

    function renderDetailPagination() {
        const container  = document.getElementById('detail-pagination-controls');
        container.innerHTML = '';
        if (currentDetailHistory.length === 0) return;
        const totalPages = Math.ceil(currentDetailHistory.length / detailItemsPerPage) || 1;
        const prevDisabled = detailCurrentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100 cursor-pointer';
        container.innerHTML += `<button onclick="changeDetailPage(${detailCurrentPage - 1})" class="px-2 sm:px-3 py-1 bg-white border rounded-lg text-[10px] sm:text-xs font-bold ${prevDisabled}">Prev</button>`;
        let startPage = Math.max(1, detailCurrentPage - 2);
        let endPage   = Math.min(totalPages, detailCurrentPage + 2);
        for (let i = startPage; i <= endPage; i++) {
            const activeClass = i === detailCurrentPage ? 'bg-gojek text-white' : 'bg-white text-gray-600 hover:bg-gray-100 cursor-pointer';
            container.innerHTML += `<button onclick="changeDetailPage(${i})" class="w-6 h-6 sm:w-8 sm:h-8 border rounded-lg text-[10px] sm:text-xs font-bold ${activeClass}">${i}</button>`;
        }
        const nextDisabled = detailCurrentPage === totalPages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100 cursor-pointer';
        container.innerHTML += `<button onclick="changeDetailPage(${detailCurrentPage + 1})" class="px-2 sm:px-3 py-1 bg-white border rounded-lg text-[10px] sm:text-xs font-bold ${nextDisabled}">Next</button>`;
    }

    function changeDetailPage(page) {
        const totalPages = Math.ceil(currentDetailHistory.length / detailItemsPerPage);
        if (page < 1 || page > totalPages) return;
        detailCurrentPage = page;
        renderDetailTable();
    }

    // ================================================================
    // STOP ALL / DEACTIVATE MASS TELEGRAM
    // ================================================================
    function stopAllMarkets(event) {
        const btn = event.target;
        let originalText = btn.innerHTML;
        btn.innerHTML = "⏳ Stopping...";
        btn.disabled  = true;
        fetch(`${API_BASE}/stop_all`, { method: 'POST', headers: { 'Content-Type': 'application/json' } })
            .then(res => res.json()).then(data => {
                btn.innerHTML = originalText;
                btn.disabled  = false;
                showPopup(data.message || "🛑 Semua market berhasil dihentikan", "error");
                refreshDashboardStatus();
            }).catch(() => {
                btn.innerHTML = originalText;
                btn.disabled  = false;
                showPopup("❌ Gagal menghentikan market", "error");
            });
    }

    function deactivateMassTelegram(event) {
        const btn = event.currentTarget;
        let originalText = btn.innerHTML;
        btn.innerHTML = '⏳ Proses...';
        btn.disabled  = true;
        fetch(`${API_BASE}/stop_telegram_all`, { method: 'POST', headers: { 'Content-Type': 'application/json' } })
            .then(res => res.json()).then(data => {
                btn.innerHTML = originalText;
                btn.disabled  = false;
                showPopup(data.message || "🔕 Sinyal Telegram massal berhasil dimatikan", "success");
                refreshDashboardStatus();
            }).catch(() => {
                btn.innerHTML = originalText;
                btn.disabled  = false;
                showPopup("❌ Gagal terhubung ke server", "error");
            });
    }

    // ================================================================
    // REALTIME CLOCK
    // ================================================================
    function startRealtimeClock() {
        setInterval(() => {
            const clockEl = document.getElementById('realtime-clock');
            if (clockEl) {
                const now = new Date();
                const hh  = String(now.getHours()).padStart(2, '0');
                const mm  = String(now.getMinutes()).padStart(2, '0');
                const ss  = String(now.getSeconds()).padStart(2, '0');
                clockEl.innerText = `${hh}:${mm}:${ss} WIB`;
            }
        }, 1000);
    }

    // ================================================================
    // DOJI ANALYTICS
    // ================================================================
    function renderDojiAnalytics(dojiData) {
        const tbody = document.getElementById('doji-tbody');
        if (!tbody) return;

        if (!dojiData || dojiData.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                        <div class="flex flex-col items-center justify-center gap-3">
                            <span class="bg-gray-50 p-4 rounded-full">
                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </span>
                            <p class="text-sm font-bold text-gray-500">Menunggu Data Market...</p>
                            <p class="text-[11px] text-gray-400">Tabel ini akan otomatis terisi saat market menyentuh 1 s/d 9 False berturut-turut.</p>
                        </div>
                    </td>
                </tr>`;
            return;
        }

        tbody.innerHTML = '';
        dojiData.forEach(item => {
            const mktObj   = allMarkets.find(x => x.id === item.market) || { name: item.market, icon: '📈' };
            const mktName  = mktObj.name;
            const mktIcon  = mktObj.icon;
            
            // Pewarnaan Winrate
            let wrColor = 'text-green-600 bg-green-50';
            let wrTextClass = 'text-green-700';
            if (item.winrate < 20) {
                wrColor = 'text-red-600 bg-red-50';
                wrTextClass = 'text-red-700';
            } else if (item.winrate < 50) {
                wrColor = 'text-orange-600 bg-orange-50';
                wrTextClass = 'text-orange-700';
            }

            tbody.innerHTML += `
                <tr class="hover:bg-gray-50/80 transition-colors group cursor-pointer" onclick="openMarketDetail('${item.market}')">
                    <td class="px-6 py-4 flex gap-3 items-center">
                        <span class="text-2xl">${mktIcon}</span>
                        <div>
                            <p class="font-extrabold text-dark tracking-tight text-sm">${mktName}</p>
                            <span class="text-[10px] font-bold text-gray-400 bg-gray-100 px-2 py-0.5 rounded-md">${item.market}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center gap-1 bg-red-50 border border-red-100 text-red-600 px-2.5 py-1 rounded-lg text-xs font-bold shadow-sm">
                            <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                            ${item.consecutive_false} False
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-xs font-bold text-gray-500 bg-gray-50 px-2 py-1 rounded-lg border border-gray-100">${item.total_candles} Candle</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex flex-col items-center">
                            <span class="text-lg font-black text-indigo-600">${item.doji_count}</span>
                            <span class="text-[10px] font-bold text-indigo-400 uppercase">Doji</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="inline-flex flex-col items-center justify-center ${wrColor} px-4 py-2 rounded-xl border border-white/40 shadow-sm transition-transform group-hover:scale-105">
                            <span class="text-lg font-black ${wrTextClass}">${item.winrate}%</span>
                            <span class="text-[9px] uppercase font-bold tracking-wider opacity-80">Winrate Doji</span>
                        </div>
                    </td>
                </tr>`;
        });
    }

</script>