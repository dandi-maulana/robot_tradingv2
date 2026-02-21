@extends('layouts.app')

@section('title', 'RODIS - Multi-Market Dashboard')

@section('styles')
<style>
    .fade-in { animation: fadeIn 0.3s ease-in-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .market-card { transition: all 0.2s ease; cursor: pointer; border: 2px solid transparent; position: relative;}
    .market-card:hover { transform: translateY(-4px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); border-color: #e4e7e9; }
    .market-card.is-active { border-color: #00aa13; background-color: #fafdff; }
    .active-badge { display: none; }
    .market-card.is-active .active-badge { display: block; position: absolute; top: 10px; right: 10px; }
    .pill { padding: 4px 12px; border-radius: 9999px; font-weight: 700; font-size: 12px; display: inline-flex; align-items: center; gap: 4px; }
    .pill-hijau { background-color: #e6f6e8; color: #00aa13; }
    .pill-hijau::before { content: '▲'; font-size: 9px; }
    .pill-merah { background-color: #fdedee; color: #ee2737; }
    .pill-merah::before { content: '▼'; font-size: 9px; }
    .pill-abu { background-color: #f3f4f6; color: #6b7280; }
    .pill-manual-up { background-color: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd;}
    .pill-manual-down { background-color: #ffedd5; color: #c2410c; border: 1px solid #fed7aa;}
    .pill-error { background-color: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
</style>
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">

    <div id="view-dashboard" class="fade-in block">
        <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 mb-8 relative overflow-hidden">
            <h2 class="text-2xl font-extrabold mb-2">Pusat Kendali <span class="text-gojek">Trading</span></h2>
            <p class="text-gray-500 mb-6">Masukkan Token, lalu klik "Cek Akun" untuk melihat daftar ID Akun Anda secara otomatis.</p>
            
            <div class="max-w-3xl">
                <div class="flex flex-col md:flex-row gap-3 items-end mb-4">
                    <div class="relative w-full">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Access Token</label>
                        <input type="password" id="token" placeholder="Paste Token..." class="w-full pl-5 pr-12 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-gojek font-mono text-sm transition-all">
                        <p class="text-xs text-red-500 mt-2 font-bold">⚠️ Jangan mengubah atau memasukkan Access Token baru jika Target Account ID di bawah ini sudah terisi dan terhubung!</p>
                    </div>
                    <button id="btn-cek-akun" onclick="checkAccounts()" class="w-full md:w-auto px-6 py-3 bg-dark text-white font-bold rounded-xl hover:bg-gray-800 transition-colors shadow-sm shrink-0">
                        🔍 Cek Akun
                    </button>
                </div>
                <div id="account-list-container" class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6 hidden"></div>
                <div class="relative w-full md:w-1/2">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Target Account ID</label>
                    <input type="text" id="account-id" placeholder="Klik kotak akun di atas..." class="w-full pl-5 py-3 bg-blue-50 border border-blue-200 rounded-xl focus:outline-none focus:border-blue-500 font-bold text-sm text-blue-800 transition-all">
                </div>
            </div>
        </div>

        <div class="flex flex-col md:flex-row justify-between items-start mb-6 gap-4 border-b border-gray-100 pb-6">
            <h3 class="text-xl font-bold text-dark whitespace-nowrap">Pilih Market <br><span class="text-sm font-normal text-gray-400">(Monitoring)</span></h3>
            
            <div class="flex flex-col w-full md:w-auto gap-3">
                <div class="flex flex-wrap gap-2 items-center md:justify-end">
                    <button onclick="startAllMarkets(event)" class="px-4 py-2 bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-700 shadow-sm text-sm transition-all">
                        🌍 Hubungkan Semua
                    </button>
                    <button onclick="stopAllMarkets(event)" class="px-4 py-2 bg-red text-white font-bold rounded-lg hover:bg-red-dark shadow-sm text-sm transition-all">
                        ⏹ Hentikan Semua
                    </button>
                    <button onclick="resetAllMarkets()" class="px-4 py-2 bg-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-300 shadow-sm text-sm transition-all">
                        🔄 Reset Data
                    </button>
                </div>
                
                <div class="flex flex-wrap gap-2 items-center md:justify-end">
                    <div class="flex border border-blue-200 rounded-lg overflow-hidden bg-white shadow-sm">
                        <div class="bg-blue-50 px-3 py-1.5 border-r border-blue-200 flex items-center">
                            <span class="text-[11px] font-bold text-blue-800 uppercase">Loss Ke:</span>
                        </div>
                        <input type="number" id="mass-tg-loss" value="7" min="1" class="w-16 text-center text-sm font-bold outline-none text-blue-900 border-r border-blue-200">
                        <button onclick="activateMassTelegram(event)" class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm transition-colors flex items-center gap-1">
                            📲 Sinyal Massal
                        </button>
                    </div>
                    <button onclick="stopMassTelegram(event)" class="px-4 py-1.5 bg-red-light text-red font-bold border border-red-200 rounded-lg hover:bg-red-200 shadow-sm text-sm transition-all">
                        🔕 Matikan Telegram Semua
                    </button>
                </div>
            </div>
        </div>
        
        <div id="market-grid-container" class="grid grid-cols-2 md:grid-cols-4 gap-5 min-h-[300px]"></div>
        <div id="pagination-controls" class="flex justify-center items-center gap-2 mt-8"></div>
    </div>

    <div id="view-detail" class="fade-in hidden">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <div>
                <button onclick="showView('dashboard')" class="flex items-center gap-2 text-sm font-bold text-gray-500 hover:text-dark transition-colors mb-2 bg-white px-4 py-2 rounded-xl shadow-sm border border-gray-200">
                    ⬅ Kembali ke Dashboard
                </button>
                <h2 class="text-3xl font-extrabold text-dark mt-2 flex items-center gap-3">
                    Analisis: <span id="detail-market-name" class="text-gojek">Market</span>
                </h2>
                <p id="detail-status" class="text-sm text-gray-500 font-medium mt-1">Menunggu instruksi...</p>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                <button id="btn-start-bot" onclick="startCurrentMarketBot()" class="px-6 py-3 bg-gojek text-white font-bold rounded-xl hover:bg-gojek-dark transition-colors shadow-sm text-sm w-full sm:w-auto">
                    ▶ Hubungkan Bot
                </button>
                <button id="btn-stop-bot" onclick="stopCurrentMarketBot()" class="px-6 py-3 bg-red-light text-red font-bold rounded-xl hover:bg-red-200 transition-colors shadow-sm text-sm w-full sm:w-auto hidden">
                    ⏹ Hentikan Bot
                </button>
                <button id="btn-reset-data" onclick="resetCurrentMarket()" class="px-6 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-300 transition-colors shadow-sm text-sm w-full sm:w-auto">
                    🔄 Reset Data
                </button>
            </div>
        </div>

        <div class="bg-blue-50 rounded-2xl p-6 mb-6 shadow-sm border border-blue-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <h3 class="text-lg font-bold text-blue-900 mb-1">📲 Sinyal Telegram Otomatis (Monitoring)</h3>
                <p class="text-xs text-blue-700">Berjalan 24 Jam Nonstop dari Server untuk market ini.</p>
                <p id="tg-status-text" class="text-sm font-bold text-gray-500 mt-2">Status: NONAKTIF</p>
            </div>
            <div class="flex gap-3 items-end">
                <div>
                    <label class="block text-xs font-bold text-blue-800 uppercase mb-1 text-center">Kirim Saat Loss Ke:</label>
                    <input type="number" id="tg-target-loss" value="7" class="w-32 px-4 py-3 bg-white border border-blue-200 rounded-xl font-bold text-xl outline-none text-center shadow-sm">
                </div>
                <button onclick="toggleTelegramServer()" id="btn-tg-toggle" class="px-6 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 shadow-md transition-colors">Aktifkan Telegram</button>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm text-center bg-gradient-to-br from-indigo-50 to-white">
                <p class="text-[10px] font-bold text-indigo-500 uppercase tracking-widest mb-1">Target Open Posisi Ke</p>
                <p class="text-2xl font-extrabold text-indigo-900" id="val-target-op">0</p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm text-center">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Total Analisis</p>
                <p class="text-2xl font-extrabold text-dark" id="val-total">0</p>
            </div>

            <div class="bg-green-50 p-5 rounded-2xl border border-green-200 shadow-sm text-center relative overflow-hidden">
                <div class="absolute bottom-0 left-0 w-full h-1 bg-green-500"></div>
                <p class="text-[10px] font-bold text-green-600 uppercase tracking-widest mb-1">Signal WIN (Warna Sama)</p>
                <p class="text-2xl font-extrabold text-green-600" id="val-sig-win">0</p>
            </div>
            
            <div class="bg-red-50 p-5 rounded-2xl border border-red-200 shadow-sm text-center relative overflow-hidden">
                <div class="absolute bottom-0 left-0 w-full h-1 bg-red-500"></div>
                <p class="text-[10px] font-bold text-red-600 uppercase tracking-widest mb-1">Signal LOSS (Warna Beda)</p>
                <p class="text-2xl font-extrabold text-red-600" id="val-sig-loss">0</p>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-gojek/20 shadow-sm text-center relative overflow-hidden">
                <div class="absolute bottom-0 left-0 w-full h-1 bg-gojek"></div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Total Hijau</p>
                <p class="text-2xl font-extrabold text-gojek" id="val-hijau">0</p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-red/20 shadow-sm text-center relative overflow-hidden">
                <div class="absolute bottom-0 left-0 w-full h-1 bg-red"></div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Total Merah</p>
                <p class="text-2xl font-extrabold text-red" id="val-merah">0</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden min-h-[300px]">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse" id="log-table">
                    <thead>
                        <tr>
                            <th class="py-4 px-8 bg-gray-50 text-xs font-bold text-gray-400 uppercase border-b border-gray-100">Waktu Candle</th>
                            <th class="py-4 px-8 bg-gray-50 text-xs font-bold text-gray-400 uppercase border-b border-gray-100">Market</th>
                            <th class="py-4 px-8 bg-gray-50 text-xs font-bold text-gray-400 uppercase border-b border-gray-100">Arah / Warna</th>
                        </tr>
                    </thead>
                    <tbody id="table-body" class="divide-y divide-gray-100">
                        <tr><td colspan="3" class="py-20 text-center text-gray-500">Silakan klik "Hubungkan Bot" terlebih dahulu.</td></tr>
                    </tbody>
                </table>
            </div>
            <div id="detail-pagination-controls" class="flex justify-center items-center gap-2 p-4 border-t border-gray-100"></div>
        </div>
    </div>

    <div id="view-rodis" class="fade-in hidden">
        <div class="bg-gradient-to-br from-indigo-900 to-slate-900 rounded-3xl p-8 shadow-lg border border-indigo-500/30 mb-8 relative overflow-hidden text-white">
            <h2 class="text-3xl font-extrabold mb-2 text-white">Robot <span class="text-indigo-400">RODIS</span> (Auto-Trade)</h2>
            <p class="text-indigo-200 mb-8 max-w-2xl">Atur target Signal Loss Anda. Robot akan otomatis bersiaga, membaca candle, dan <b>mengeksekusi order Buy/Sell langsung ke broker</b> tanpa campur tangan Anda.</p>
            
            <div class="bg-slate-800/50 backdrop-blur border border-indigo-500/20 p-6 rounded-2xl mb-8 relative z-10">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div>
                        <label class="block text-xs font-bold text-indigo-300 uppercase mb-2">Pilih Market Aktif</label>
                        <select id="rodis-market-select" class="w-full px-4 py-3 bg-slate-900 border border-indigo-500/50 rounded-xl font-bold text-white outline-none focus:border-indigo-400"></select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-indigo-300 uppercase mb-2">Target Signal Loss</label>
                        <input type="number" id="rodis-target-loss" value="7" min="1" class="w-full px-4 py-3 bg-slate-900 border border-indigo-500/50 rounded-xl font-bold text-xl text-white outline-none text-center">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-indigo-300 uppercase mb-2">Investasi ($)</label>
                        <input type="number" id="rodis-amount" value="10" min="1" class="w-full px-4 py-3 bg-slate-900 border border-indigo-500/50 rounded-xl font-bold text-xl text-white outline-none text-center">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-indigo-300 uppercase mb-2">Durasi Order</label>
                        <select id="rodis-duration" class="w-full px-4 py-3 bg-slate-900 border border-indigo-500/50 rounded-xl font-bold text-white outline-none">
                            <option value="60">60 Detik</option>
                            <option value="120">120 Detik</option>
                            <option value="180">180 Detik</option>
                            <option value="300">300 Detik</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6 flex justify-between items-center border-t border-indigo-500/20 pt-6">
                    <div class="flex items-center gap-4">
                        <div class="text-center">
                            <p class="text-[10px] text-indigo-300 font-bold uppercase">Loss Berjalan</p>
                            <p class="text-3xl font-extrabold text-white" id="rodis-current-loss">0</p>
                        </div>
                        <div class="h-10 w-px bg-indigo-500/30"></div>
                        <div class="text-center">
                            <p class="text-[10px] text-indigo-300 font-bold uppercase">Target Open Ke</p>
                            <p class="text-3xl font-extrabold text-indigo-400" id="rodis-target-op">0</p>
                        </div>
                    </div>
                    <button id="btn-rodis-toggle" onclick="toggleRodisBot()" class="px-8 py-4 bg-indigo-500 hover:bg-indigo-400 text-white font-extrabold rounded-xl shadow-[0_0_20px_rgba(99,102,241,0.4)] transition-all text-lg tracking-wide">
                        ▶ NYALAKAN RODIS
                    </button>
                </div>
            </div>

            <h3 class="text-xs font-bold text-indigo-300 uppercase mb-2 tracking-widest flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></div> Log Eksekusi RODIS
            </h3>
            <div id="rodis-terminal" class="shadow-inner" style="font-family: 'Courier New', Courier, monospace; background-color: #0f172a; color: #4ade80; height: 250px; overflow-y: auto; padding: 1rem; border-radius: 1rem;">
                <div class="text-indigo-400 opacity-50 mb-2">Sistem siap. Hubungkan Market di Monitor, atur form di atas, lalu klik Nyalakan RODIS.</div>
            </div>
        </div>
    </div>

    <div id="view-trade" class="fade-in hidden">
        <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 mb-8 relative overflow-hidden">
            <h2 class="text-2xl font-extrabold mb-2">Pusat Eksekusi <span class="text-gojek">Trade Manual</span></h2>
            <p class="text-gray-500 mb-6">Pilih salah satu market yang sudah berjalan (dihubungkan di menu Monitor) untuk mengeksekusi order.</p>
            
            <h3 class="text-sm font-bold text-gray-500 uppercase mb-4">1. Pilih Market Yang Sedang Aktif</h3>
            <div id="trade-market-container" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                </div>

            <div id="trade-panel" class="hidden">
                <h3 class="text-sm font-bold text-gray-500 uppercase mb-4 border-t pt-6">2. Eksekusi Order Terpusat <span id="trade-selected-market" class="text-gojek font-extrabold"></span></h3>
                <div class="flex flex-col md:flex-row gap-4 items-end bg-gray-50 p-6 rounded-2xl border border-gray-200 shadow-inner">
                    <div class="w-full md:w-1/4">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Investasi ($)</label>
                        <input type="number" id="trade-amount" value="10" min="1" class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl font-bold text-lg outline-none">
                    </div>
                    <div class="w-full md:w-1/4">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Durasi Order</label>
                        <select id="trade-duration" class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl font-bold text-base outline-none">
                            <option value="60">60 Detik</option>
                            <option value="120">120 Detik</option>
                            <option value="180">180 Detik</option>
                            <option value="300">300 Detik</option>
                        </select>
                    </div>
                    <div class="w-full md:w-2/4 flex gap-3">
                        <button onclick="executeTradeFromPanel('up')" class="flex-1 bg-gojek hover:bg-gojek-dark text-white font-bold py-3 rounded-xl shadow-md text-lg">▲ BUY NAIK</button>
                        <button onclick="executeTradeFromPanel('down')" class="flex-1 bg-red hover:bg-red-dark text-white font-bold py-3 rounded-xl shadow-md text-lg">▼ SELL TURUN</button>
                    </div>
                </div>
            </div>
            
            <div class="bg-indigo-50 border border-indigo-200 rounded-2xl p-6 mt-10 shadow-sm">
                <h3 class="text-sm font-extrabold text-indigo-900 uppercase mb-2">💡 Daftar Market Fixed Time (Buka 24 Jam)</h3>
                <p class="text-xs text-indigo-700 mb-4">
                    Jika Anda mengalami error <i>"pair_unavailable"</i>, artinya bursa reguler sedang tutup/dikunci. Silakan kembali ke menu Monitor dan pilih market di bawah ini yang <strong>selalu buka 24 jam nonstop untuk Fixed Time Trade:</strong>
                </p>
                <div class="flex flex-wrap gap-2">
                    <span class="px-3 py-1.5 bg-white border border-indigo-200 text-indigo-800 rounded-lg text-xs font-bold shadow-sm">🌏 Asia Composite Index</span>
                    <span class="px-3 py-1.5 bg-white border border-indigo-200 text-indigo-800 rounded-lg text-xs font-bold shadow-sm">🌍 Europe Composite Index</span>
                    <span class="px-3 py-1.5 bg-white border border-indigo-200 text-indigo-800 rounded-lg text-xs font-bold shadow-sm">🌾 Commodity Composite</span>
                    <span class="px-3 py-1.5 bg-white border border-indigo-200 text-indigo-800 rounded-lg text-xs font-bold shadow-sm">🇯🇵 USD/JPY OTC</span>
                    <span class="px-3 py-1.5 bg-white border border-indigo-200 text-indigo-800 rounded-lg text-xs font-bold shadow-sm">🇪🇺 EUR/USD OTC</span>
                    <span class="px-3 py-1.5 bg-white border border-indigo-200 text-indigo-800 rounded-lg text-xs font-bold shadow-sm">🇬🇧 GBP/USD OTC</span>
                </div>
            </div>
        </div>
    </div>

    <div id="view-history" class="fade-in hidden">
        <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 mb-8 relative overflow-hidden">
            <h2 class="text-2xl font-extrabold mb-2">Riwayat <span class="text-gojek">Trade (Order)</span></h2>
            <p class="text-gray-500 mb-6">Semua riwayat eksekusi order (BUY/SELL) baik secara manual maupun dari sistem RODIS Auto-Trade akan tercatat di sini.</p>
            
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden min-h-[300px]">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse" id="history-log-table">
                        <thead>
                            <tr>
                                <th class="py-4 px-8 bg-gray-50 text-xs font-bold text-gray-400 uppercase border-b border-gray-100">Waktu Order</th>
                                <th class="py-4 px-8 bg-gray-50 text-xs font-bold text-gray-400 uppercase border-b border-gray-100">Market</th>
                                <th class="py-4 px-8 bg-gray-50 text-xs font-bold text-gray-400 uppercase border-b border-gray-100">Investasi</th>
                                <th class="py-4 px-8 bg-gray-50 text-xs font-bold text-gray-400 uppercase border-b border-gray-100">Arah Eksekusi</th>
                            </tr>
                        </thead>
                        <tbody id="history-table-body" class="divide-y divide-gray-100">
                            <tr><td colspan="4" class="py-20 text-center text-gray-500">Memuat data riwayat...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="history-pagination-controls" class="flex justify-center items-center gap-2 p-4 border-t border-gray-100"></div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
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
        active: false, market: "", targetLoss: 7, amount: 10, duration: 60,
        phase: 'IDLE', tradeCounter: 0, lastProcessedCandle: null, direction: ''
    };

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

    let currentPage = 1; const itemsPerPage = 8;

    function formatCurrency(amount) { return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount); }

    window.onload = function() {
        fetch(`${API_BASE}/get_settings`).then(res => res.json()).then(data => { 
            if(data.token) document.getElementById('token').value = data.token; 
            if(data.account_id) document.getElementById('account-id').value = data.account_id; 
        });
        renderMarketCards(); startDashboardPolling();

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

    function clearAllIntervals() {
        if(dashboardInterval) clearInterval(dashboardInterval);
        if(detailInterval) clearInterval(detailInterval);
        if(historyInterval) clearInterval(historyInterval);
    }

    function showView(viewName) {
        document.getElementById('view-dashboard').classList.add('hidden');
        document.getElementById('view-detail').classList.add('hidden');
        document.getElementById('view-trade').classList.add('hidden');
        document.getElementById('view-history').classList.add('hidden');
        document.getElementById('view-rodis').classList.add('hidden');
        
        document.getElementById('view-' + viewName).classList.remove('hidden');

        const navIds = ['nav-link-dashboard', 'nav-link-trade', 'nav-link-history', 'nav-link-rodis', 'nav-link-dashboard-mob', 'nav-link-trade-mob', 'nav-link-history-mob', 'nav-link-rodis-mob'];
        navIds.forEach(id => {
            let el = document.getElementById(id);
            if(el) { el.classList.remove('text-indigo-600', 'text-gojek'); el.classList.add('text-gray-500'); }
        });

        let activeBase = viewName === 'detail' ? 'dashboard' : viewName;
        let deskNav = document.getElementById('nav-link-' + activeBase);
        let mobNav = document.getElementById('nav-link-' + activeBase + '-mob');
        
        let colorClass = viewName === 'rodis' ? 'text-indigo-600' : 'text-gojek';
        if(deskNav) { deskNav.classList.remove('text-gray-500'); deskNav.classList.add(colorClass); }
        if(mobNav) { mobNav.classList.remove('text-gray-500'); mobNav.classList.add(colorClass); }

        clearAllIntervals();
        
        if(viewName === 'dashboard') {
            currentMarket = ""; startDashboardPolling();
        } else if (viewName === 'detail') {
            detailCurrentPage = 1;
            refreshDetailData(); detailInterval = setInterval(refreshDetailData, 1500);
        } else if (viewName === 'trade') {
            fetch(`${API_BASE}/status_all`).then(res => res.json()).then(data => {
                if(data.balance !== undefined && data.balance !== null) document.getElementById('nav-balance').innerText = formatCurrency(data.balance);
                activeMarketsList = data.active_markets || []; renderTradeMarkets();
            });
        } else if (viewName === 'history') {
            historyCurrentPage = 1;
            refreshHistoryData(); historyInterval = setInterval(refreshHistoryData, 2000);
        } else if (viewName === 'rodis') {
            fetch(`${API_BASE}/status_all`).then(res => res.json()).then(data => {
                activeMarketsList = data.active_markets || [];
                const select = document.getElementById('rodis-market-select');
                select.innerHTML = '';
                if(activeMarketsList.length === 0) select.innerHTML = `<option value="">(Belum ada market aktif)</option>`;
                else activeMarketsList.forEach(m => { select.innerHTML += `<option value="${m}" ${m === rodisState.market ? 'selected' : ''}>${m}</option>`; });
            });
        }
    }

    // ==========================================
    // FITUR: KONTROL SEMUA MARKET (START / STOP)
    // ==========================================
    function startAllMarkets(event) {
        const token = document.getElementById('token').value;
        const accountId = document.getElementById('account-id').value;
        if (!token || !accountId) return alert("Harap isi Access Token & Target Account ID di Dashboard terlebih dahulu!");
        
        const btn = event.currentTarget || event.target;
        let originalText = btn.innerHTML;
        btn.innerHTML = '⏳ Menghubungkan...'; btn.disabled = true;

        fetch(`${API_BASE}/start_all`, {
            method: 'POST', headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ token: token, account_id: accountId })
        }).then(res => res.json()).then(data => {
            btn.innerHTML = originalText; btn.disabled = false;
            alert(`✅ ${data.message}`); refreshDashboardStatus();
        }).catch(err => { btn.innerHTML = originalText; btn.disabled = false; });
    }

    function stopAllMarkets(event) {
        if(!confirm("Apakah Anda yakin ingin MENGHENTIKAN SEMUA bot market yang sedang berjalan?")) return;
        
        const btn = event.currentTarget || event.target;
        let originalText = btn.innerHTML;
        btn.innerHTML = '⏳ Memproses...'; btn.disabled = true;

        fetch(`${API_BASE}/stop_all`, { method: 'POST' })
        .then(res => res.json()).then(data => {
            btn.innerHTML = originalText; btn.disabled = false;
            alert(`✅ ${data.message}`); refreshDashboardStatus();
        }).catch(err => { btn.innerHTML = originalText; btn.disabled = false; });
    }

    function resetAllMarkets() {
        if(!confirm("Apakah Anda yakin ingin MERESET SEMUA data history market? Semua hitungan candle akan dimulai dari 0 kembali.")) return;
        fetch(`${API_BASE}/reset_all`, { method: 'POST' })
        .then(res => res.json()).then(data => { alert(`✅ ${data.message}`); refreshDashboardStatus(); });
    }

    // ==========================================
    // FITUR: KONTROL TELEGRAM MASSAL (START / STOP)
    // ==========================================
    function activateMassTelegram(event) {
        const targetLoss = document.getElementById('mass-tg-loss').value;
        if(!confirm(`Aktifkan Telegram otomatis di SEMUA market aktif dengan Target Loss ${targetLoss}?`)) return;
        
        const btn = event.currentTarget || event.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = '⏳ Memproses...'; btn.disabled = true;

        fetch(`${API_BASE}/toggle_telegram_all`, {
            method: 'POST', headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ target_loss: targetLoss })
        }).then(res => res.json()).then(data => {
            btn.innerHTML = originalText; btn.disabled = false;
            if(data.status === 'success') { alert(`✅ ${data.message}`); refreshDashboardStatus(); refreshDetailData(); } 
            else { alert(`❌ ${data.message}`); }
        }).catch(err => { btn.innerHTML = originalText; btn.disabled = false; alert("Gagal terhubung ke server."); });
    }

    function stopMassTelegram(event) {
        if(!confirm("Apakah Anda yakin ingin MEMATIKAN sinyal Telegram di SEMUA market?")) return;
        
        const btn = event.currentTarget || event.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = '⏳ Memproses...'; btn.disabled = true;

        fetch(`${API_BASE}/stop_telegram_all`, { method: 'POST' })
        .then(res => res.json()).then(data => {
            btn.innerHTML = originalText; btn.disabled = false;
            if(data.status === 'success') { alert(`✅ ${data.message}`); refreshDetailData(); } 
            else { alert(`❌ ${data.message}`); }
        }).catch(err => { btn.innerHTML = originalText; btn.disabled = false; alert("Gagal terhubung ke server."); });
    }

    function resetCurrentMarket() {
        if(!currentMarket) return;
        if(!confirm(`Apakah Anda yakin ingin mereset semua data analisis untuk market ${currentMarket}?\nData historis candle dan perhitungan akan diulang dari nol.`)) return;

        fetch(`${API_BASE}/reset_market`, {
            method: 'POST', headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ market: currentMarket })
        }).then(res => res.json()).then(data => {
            if(data.status === 'success') {
                if(rodisState.active && rodisState.market === currentMarket) {
                    rodisState.tradeCounter = 0; rodisState.lastProcessedCandle = null; rodisState.phase = 'IDLE';
                    document.getElementById('rodis-target-op').innerText = '0'; document.getElementById('rodis-current-loss').innerText = '0';
                    logRodis(`🔄 [RESET MANUAL] Data market ${currentMarket} telah dibersihkan. Memulai penghitungan target dari 0 kembali.`, "#fbbf24");
                }
                refreshDetailData(); alert(`✅ Berhasil! Semua data analisis untuk market ${currentMarket} telah direset dari awal.`);
            } else { alert(`❌ ${data.message}`); }
        });
    }

    function toggleTelegramServer() {
        if(!currentMarket) return;
        const inLoss = document.getElementById('tg-target-loss').value;
        fetch(`${API_BASE}/toggle_telegram`, {
            method: 'POST', headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ market: currentMarket, target_loss: inLoss })
        }).then(res => res.json()).then(data => {
            if(data.status === 'success') {
                refreshDetailData();
                if(data.active) alert(`✅ Sinyal Telegram diaktifkan di Server untuk market ${currentMarket}!`);
            } else { alert(`❌ ${data.message}`); }
        });
    }

    function logRodis(msg, color="#4ade80") {
        const term = document.getElementById('rodis-terminal'); let timeStr = new Date().toLocaleTimeString('id-ID');
        term.innerHTML = `<div style="margin-bottom:6px;"><span style="color:#64748b;font-size:0.75rem;">[${timeStr}]</span> <span style="color:${color}">${msg}</span></div>` + term.innerHTML;
    }

    function toggleRodisBot() {
        const btn = document.getElementById('btn-rodis-toggle'); const selMkt = document.getElementById('rodis-market-select');
        const inLoss = document.getElementById('rodis-target-loss'); const inAmt = document.getElementById('rodis-amount');
        const inDur = document.getElementById('rodis-duration');

        if(!rodisState.active) {
            if(!selMkt.value) return alert("Silakan hubungkan minimal 1 market di menu Monitor!");
            
            rodisState.active = true; rodisState.market = selMkt.value; rodisState.targetLoss = parseInt(inLoss.value) || 7;
            rodisState.amount = parseFloat(inAmt.value) || 10; rodisState.duration = parseInt(inDur.value) || 60; rodisState.phase = 'IDLE';

            selMkt.disabled = true; inLoss.disabled = true; inAmt.disabled = true; inDur.disabled = true;
            btn.innerHTML = '⏹ MATIKAN RODIS';
            btn.classList.replace('bg-indigo-500', 'bg-red-500'); btn.classList.replace('hover:bg-indigo-400', 'hover:bg-red-400');
            btn.classList.replace('shadow-[0_0_20px_rgba(99,102,241,0.4)]', 'shadow-[0_0_20px_rgba(239,68,68,0.4)]');

            logRodis(`🚀 RODIS DIAKTIFKAN! Memantau ${rodisState.market}. Target Loss: ${rodisState.targetLoss}.`, "#22c55e");

            fetch(`${API_BASE}/data?market=${encodeURIComponent(rodisState.market)}`).then(res=>res.json()).then(data => {
                let sl = calculateSigLoss(data.history); rodisState.tradeCounter = Math.floor(sl / rodisState.targetLoss);
                document.getElementById('rodis-current-loss').innerText = sl; document.getElementById('rodis-target-op').innerText = rodisState.tradeCounter + 1;
                logRodis(`Sistem bersiaga membaca lilin. Target selanjutnya: Loss ke-${(rodisState.tradeCounter * rodisState.targetLoss) + rodisState.targetLoss}.`, "#60a5fa");
                rodisInterval = setInterval(runRodisLoop, 2000);
            });
        } else {
            rodisState.active = false; clearInterval(rodisInterval);
            selMkt.disabled = false; inLoss.disabled = false; inAmt.disabled = false; inDur.disabled = false;
            btn.innerHTML = '▶ NYALAKAN RODIS';
            btn.classList.replace('bg-red-500', 'bg-indigo-500'); btn.classList.replace('hover:bg-red-400', 'hover:bg-indigo-400');
            btn.classList.replace('shadow-[0_0_20px_rgba(239,68,68,0.4)]', 'shadow-[0_0_20px_rgba(99,102,241,0.4)]');
            logRodis(`🛑 RODIS DIMATIKAN. Robot Auto-Trade telah dihentikan.`, "#f87171");
        }
    }

    function runRodisLoop() {
        if(!rodisState.active) return;
        fetch(`${API_BASE}/data?market=${encodeURIComponent(rodisState.market)}`)
        .then(res => res.json()).then(data => {
            if(!data.is_running) return;

            let sigLoss = calculateSigLoss(data.history); document.getElementById('rodis-current-loss').innerText = sigLoss;

            if (data.history && data.history.length > 0) {
                let latestC = data.history.filter(item => item.warna === "Hijau" || item.warna === "Merah")[0];
                if (latestC) {
                    let mm = parseInt(latestC.waktu.split(':')[1]); let candleId = latestC.tanggal + "_" + latestC.waktu;
    
                    if (rodisState.lastProcessedCandle !== candleId) {
                        if (rodisState.phase === 'IDLE' && (mm % 5 === 2)) {
                            let expectedTrades = Math.floor(sigLoss / rodisState.targetLoss);
                            if (expectedTrades > rodisState.tradeCounter && sigLoss > 0) {
                                rodisState.tradeCounter++; rodisState.phase = 'WAIT_CONF'; rodisState.lastProcessedCandle = candleId;
                                document.getElementById('rodis-target-op').innerText = rodisState.tradeCounter;
                                let nextMin = (mm + 3).toString().padStart(2, '0');
                                logRodis(`⏳ [STANDBY] Target Loss ke-${sigLoss} tercapai! Membaca arah di penutupan menit ${nextMin}...`, "#fbbf24");
                                let msg = `⏳ *RODIS AUTO-TRADE: STANDBY* ⏳\n\n📈 *Market:* ${rodisState.market}\n🗓 *Waktu:* ${latestC.tanggal} | ${latestC.waktu} WIB\n\nSistem mendeteksi bahwa *Target Signal Loss ke-${sigLoss}* telah tercapai!\nRODIS saat ini sedang bersiaga (loading) membaca arah market.\nEksekusi Open Posisi akan ditentukan pada penutupan candle menit ke-${nextMin}.\n\nMohon bersabar, sistem berjalan otomatis... 🤖`;
                                fetch(`${API_BASE}/send_wa`, { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({ message: msg }) });
                            }
                        }
                        else if (rodisState.phase === 'WAIT_CONF' && (mm % 5 === 0)) {
                            rodisState.phase = 'WAIT_RES'; rodisState.direction = (latestC.warna === 'Hijau') ? 'up' : 'down'; rodisState.lastProcessedCandle = candleId;
                            let dirStr = (latestC.warna === 'Hijau') ? 'BUY NAIK 🟢' : 'SELL TURUN 🔴'; let nextMin = (mm + 2).toString().padStart(2, '0');
                            logRodis(`🔥 [EKSEKUSI] Candle penentu menit ${mm} berwarna ${latestC.warna.toUpperCase()}. RODIS otomatis mengeksekusi order: ${dirStr}! Menunggu hasil...`, "#c084fc");
                            let msg = `🚀 *RODIS AUTO-TRADE: EKSEKUSI* 🚀\n\n📈 *Market:* ${rodisState.market}\n🗓 *Waktu:* ${latestC.tanggal} | ${latestC.waktu} WIB\n\nCandle penentu telah selesai dengan warna *${latestC.warna.toUpperCase()}*.\nRODIS secara otomatis mengeksekusi order:\n👉 *${dirStr}* senilai $${rodisState.amount}\n\nSistem sedang memproses (loading) hasil trading. Hasil akan diumumkan setelah penutupan candle menit ke-${nextMin}.`;
                            fetch(`${API_BASE}/send_wa`, { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({ message: msg }) });

                            fetch(`${API_BASE}/manual_trade`, {
                                method: 'POST', headers: {'Content-Type': 'application/json'},
                                body: JSON.stringify({ market: rodisState.market, direction: rodisState.direction, amount: rodisState.amount, duration: rodisState.duration })
                            });
                        }
                        else if (rodisState.phase === 'WAIT_RES' && (mm % 5 === 2)) {
                            rodisState.phase = 'IDLE'; rodisState.lastProcessedCandle = candleId;
                            let requiredColor = rodisState.direction === 'up' ? 'Hijau' : 'Merah'; let isWin = (latestC.warna === requiredColor);
                            let resMsg = isWin ? 'PROFIT / WIN ✅' : 'LOSS ❌'; let resColor = isWin ? '#22c55e' : '#f87171';
                            let nextTargetLoss = (rodisState.tradeCounter * rodisState.targetLoss) + rodisState.targetLoss;
                            
                            logRodis(`🎯 [HASIL] Auto-Trade ke-${rodisState.tradeCounter} selesai. Hasil Akhir: ${resMsg}. Kembali bersiaga menunggu Loss ke-${nextTargetLoss}.`, resColor);
                            document.getElementById('rodis-target-op').innerText = rodisState.tradeCounter + 1;

                            let msg = `🎯 *RODIS AUTO-TRADE: HASIL* 🎯\n\nTarget Open Posisi Ke: ${rodisState.tradeCounter}\n📈 *Market:* ${rodisState.market}\n🗓 *Waktu:* ${latestC.tanggal} | ${latestC.waktu} WIB\n\nArah Eksekusi Tadi: *${rodisState.direction === 'up' ? 'BUY 🟢' : 'SELL 🔴'}*\nWarna Candle Hasil: *${latestC.warna.toUpperCase()}*\n\nStatus Hasil Akhir: *${resMsg}*\n\nRODIS kembali bersiaga memantau market untuk Target Open Posisi ke-${rodisState.tradeCounter + 1} (Menunggu Loss ke-${nextTargetLoss}).`;
                            fetch(`${API_BASE}/send_wa`, { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({ message: msg }) });
                        }
                    }
                }
            }
        }).catch(err => {});
    }

    function calculateSigLoss(historyArr) {
        let sigLoss = 0;
        if(historyArr && historyArr.length > 0) {
            const candles = historyArr.filter(item => item.warna === "Hijau" || item.warna === "Merah");
            let blocks = {};
            candles.forEach(c => {
                if(c.waktu && c.waktu.includes(':')) {
                    let parts = c.waktu.split(':'); let hh = parts[0]; let mm = parseInt(parts[1]);
                    let baseMm = Math.floor(mm / 5) * 5; let key = c.tanggal + '_' + hh + ':' + baseMm.toString().padStart(2, '0');
                    if(!blocks[key]) blocks[key] = {};
                    if(mm % 5 === 0) blocks[key].c1 = c.warna; 
                    if(mm % 5 === 2) blocks[key].c2 = c.warna; 
                }
            });
            for(let k in blocks) { let b = blocks[k]; if(b.c1 && b.c2 && b.c1 !== b.c2) sigLoss++; }
        }
        return sigLoss;
    }

    function checkAccounts() {
        const token = document.getElementById('token').value;
        if(!token) return alert('Silakan isi Access Token!');
        const btn = document.getElementById('btn-cek-akun'); btn.innerHTML = '⏳ Menyadap...'; btn.disabled = true;
        fetch(`${API_BASE}/check_accounts`, { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({token: token})
        }).then(res => res.json()).then(data => {
            btn.innerHTML = '🔍 Cek Akun'; btn.disabled = false;
            if(data.status === 'success') {
                const container = document.getElementById('account-list-container'); container.innerHTML = ''; container.classList.remove('hidden');
                data.accounts.forEach(acc => {
                    const isDemo = acc.type === 'Demo'; const bgClass = isDemo ? 'bg-green-50 border-green-300' : 'bg-gray-50 border-gray-300';
                    const icon = isDemo ? '🎮' : '💼'; const textColor = isDemo ? 'text-green-700' : 'text-gray-600';
                    container.innerHTML += `<div onclick="selectAccount('${acc.id}')" class="p-4 border-2 rounded-xl cursor-pointer hover:shadow-md transition-all ${bgClass}"><div class="flex justify-between items-center mb-1"><span class="font-bold text-sm ${textColor}">${icon} Akun ${acc.type}</span><span class="text-xs font-mono text-gray-500">ID: ${acc.id}</span></div><div class="text-xl font-extrabold ${textColor}">${formatCurrency(acc.balance)}</div><div class="text-[10px] text-gray-400 mt-2 uppercase tracking-wider text-center">Klik untuk menggunakan ID ini</div></div>`;
                });
            } else { alert(data.message); }
        }).catch(err => { btn.innerHTML = '🔍 Cek Akun'; btn.disabled = false; });
    }

    function selectAccount(id) { document.getElementById('account-id').value = id; alert('✅ Account ID ' + id + ' berhasil dipilih!'); }

    function renderMarketCards() {
        const start = (currentPage - 1) * itemsPerPage; const container = document.getElementById('market-grid-container'); container.innerHTML = '';
        allMarkets.slice(start, start + itemsPerPage).forEach(market => {
            const isActive = activeMarketsList.includes(market.id) ? 'is-active' : '';
            const catBadge = market.cat === "24 Jam FTT" ? `<span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded-md shadow-sm">${market.cat}</span>` : market.cat;
            container.innerHTML += `<div onclick="openMarketDetail('${market.id}')" data-market="${market.id}" class="market-card ${isActive} bg-white rounded-2xl p-6 shadow-sm flex flex-col items-center"><div class="active-badge w-3 h-3 bg-gojek rounded-full animate-pulse shadow-[0_0_8px_#00aa13]"></div><div class="text-4xl mb-3">${market.icon}</div><h4 class="font-bold text-dark text-center text-sm">${market.name}</h4><p class="text-[10px] text-gray-400 font-bold uppercase mt-1">${catBadge}</p></div>`;
        });
        renderPagination();
    }

    function renderTradeMarkets() {
        const container = document.getElementById('trade-market-container'); container.innerHTML = '';
        if (activeMarketsList.length === 0) {
            container.innerHTML = `<div class="col-span-full text-center text-gray-400 py-10 font-bold">⚠️ Belum ada market aktif.</div>`;
            document.getElementById('trade-panel').classList.add('hidden'); return;
        }
        activeMarketsList.forEach(m => {
            const marketObj = allMarkets.find(x => x.id === m) || {id: m, name: m, icon: '📈', cat: 'Aktif'};
            const isSelected = (selectedTradeMarket === m) ? 'border-gojek bg-green-50 shadow-md' : 'border-gray-100 bg-white hover:border-gray-300';
            container.innerHTML += `<div onclick="selectTradeMarket('${m}')" class="cursor-pointer border-2 rounded-2xl p-4 flex flex-col items-center transition-all ${isSelected}"><div class="text-3xl mb-2">${marketObj.icon}</div><h4 class="font-bold text-dark text-sm">${marketObj.name}</h4><div class="w-2 h-2 bg-gojek rounded-full mt-2 shadow-[0_0_8px_#00aa13] animate-pulse"></div></div>`;
        });
    }

    function selectTradeMarket(marketId) { selectedTradeMarket = marketId; renderTradeMarkets(); document.getElementById('trade-panel').classList.remove('hidden'); document.getElementById('trade-selected-market').innerText = `(${marketId})`; }

    function renderPagination() {
        const totalPages = Math.ceil(allMarkets.length / itemsPerPage); const container = document.getElementById('pagination-controls'); container.innerHTML = '';
        const prevDisabled = currentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100';
        container.innerHTML += `<button onclick="changePage(${currentPage - 1})" class="px-4 py-2 bg-white border rounded-xl text-sm font-bold ${prevDisabled}">Prev</button>`;
        for(let i = 1; i <= totalPages; i++) {
            const activeClass = i === currentPage ? 'bg-gojek text-white' : 'bg-white text-dark hover:bg-gray-100';
            container.innerHTML += `<button onclick="changePage(${i})" class="w-10 h-10 border rounded-xl text-sm font-bold ${activeClass}">${i}</button>`;
        }
        const nextDisabled = currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100';
        container.innerHTML += `<button onclick="changePage(${currentPage + 1})" class="px-4 py-2 bg-white border rounded-xl text-sm font-bold ${nextDisabled}">Next</button>`;
    }
    function changePage(page) { if(page < 1 || page > Math.ceil(allMarkets.length / itemsPerPage)) return; currentPage = page; renderMarketCards(); }

    function startDashboardPolling() { refreshDashboardStatus(); dashboardInterval = setInterval(refreshDashboardStatus, 3000); }

    function refreshDashboardStatus() {
        fetch(`${API_BASE}/status_all`).then(res => res.json()).then(data => {
            if(data.balance !== undefined && data.balance !== null) document.getElementById('nav-balance').innerText = formatCurrency(data.balance);
            activeMarketsList = data.active_markets || [];
            document.querySelectorAll('.market-card').forEach(card => {
                if (activeMarketsList.includes(card.getAttribute('data-market'))) card.classList.add('is-active'); else card.classList.remove('is-active');
            });
        });
    }

    function openMarketDetail(marketName) { currentMarket = marketName; document.getElementById('detail-market-name').innerText = marketName; showView('detail'); }

    function startCurrentMarketBot() {
        const token = document.getElementById('token').value; const accountId = document.getElementById('account-id').value; 
        if (!token || !accountId) return alert("Harap isi Access Token & Target Account ID di Dashboard!");
        document.getElementById('table-body').innerHTML = `<tr><td colspan="3" class="py-20 text-center">⏳ Membangun koneksi ke broker...</td></tr>`;
        fetch(`${API_BASE}/start`, { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({ token: token, market: currentMarket, account_id: accountId })
        }).then(res => res.json()).then(data => { if(data.status === 'error') { alert(data.message); showView('dashboard'); } else refreshDetailData(); }); 
    }

    function stopCurrentMarketBot() { fetch(`${API_BASE}/stop`, { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({ market: currentMarket }) }).then(() => refreshDetailData()); }

    function executeTradeFromPanel(direction) {
        if(!selectedTradeMarket) return alert('Pilih market yang aktif terlebih dahulu!');
        const amount = document.getElementById('trade-amount').value; const duration = document.getElementById('trade-duration').value;
        fetch(`${API_BASE}/manual_trade`, { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({ market: selectedTradeMarket, direction: direction, amount: amount, duration: duration })
        }).then(res => res.json()).then(data => { if(data.status === 'error') alert(`❌ ${data.message}`); else alert(`✅ Perintah dikirim! Cek Riwayat.`); });
    }

    function refreshHistoryData() {
        fetch(`${API_BASE}/trade_history`).then(res => res.json()).then(data => {
            currentTradeHistory = data.trade_history || []; renderHistoryTable();
        });
    }

    function renderHistoryTable() {
        const tbody = document.getElementById('history-table-body');
        const start = (historyCurrentPage - 1) * historyItemsPerPage; const paginated = currentTradeHistory.slice(start, start + historyItemsPerPage);
        
        tbody.innerHTML = '';
        if(paginated.length > 0) {
            paginated.forEach(item => {
                let pillClass = "pill-abu"; let label = item.warna;
                if(item.warna.includes("GAGAL")) pillClass = "pill-error";
                else if(item.warna.includes("UP")) { pillClass = "pill-manual-up"; label = "BUY NAIK"; }
                else if(item.warna.includes("DOWN")) { pillClass = "pill-manual-down"; label = "SELL TURUN"; }
                let amountStr = item.amount ? `$${item.amount}` : '-';
                tbody.innerHTML += `<tr class="hover:bg-gray-50/50"><td class="py-4 px-8"><span class="text-base font-bold text-dark">${item.waktu}</span><span class="block text-xs text-gray-400">${item.tanggal}</span></td><td class="py-4 px-8 font-bold text-dark">${item.market}</td><td class="py-4 px-8 font-bold text-indigo-600">${amountStr}</td><td class="py-4 px-8"><span class="pill ${pillClass}">${label}</span></td></tr>`;
            });
        } else { tbody.innerHTML = `<tr><td colspan="4" class="py-20 text-center text-gray-500">Belum ada riwayat trade.</td></tr>`; }
        renderHistoryPagination();
    }

    function renderHistoryPagination() {
        const container = document.getElementById('history-pagination-controls'); container.innerHTML = '';
        if(currentTradeHistory.length === 0) return;
        const totalPages = Math.ceil(currentTradeHistory.length / historyItemsPerPage) || 1;
        
        const prevDisabled = historyCurrentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100 cursor-pointer';
        container.innerHTML += `<button onclick="changeHistoryPage(${historyCurrentPage - 1})" class="px-3 py-1 bg-white border rounded-lg text-xs font-bold ${prevDisabled}">Prev</button>`;
        
        let startPage = Math.max(1, historyCurrentPage - 2); let endPage = Math.min(totalPages, historyCurrentPage + 2);
        for(let i = startPage; i <= endPage; i++) {
            const activeClass = i === historyCurrentPage ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 cursor-pointer';
            container.innerHTML += `<button onclick="changeHistoryPage(${i})" class="w-8 h-8 border rounded-lg text-xs font-bold ${activeClass}"> ${i} </button>`;
        }
        
        const nextDisabled = historyCurrentPage === totalPages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100 cursor-pointer';
        container.innerHTML += `<button onclick="changeHistoryPage(${historyCurrentPage + 1})" class="px-3 py-1 bg-white border rounded-lg text-xs font-bold ${nextDisabled}">Next</button>`;
    }
    function changeHistoryPage(page) {
        const totalPages = Math.ceil(currentTradeHistory.length / historyItemsPerPage);
        if(page < 1 || page > totalPages) return;
        historyCurrentPage = page; renderHistoryTable();
    }

    function refreshDetailData() {
        if(!currentMarket) return;
        fetch(`${API_BASE}/data?market=${encodeURIComponent(currentMarket)}`).then(res => res.json()).then(data => {
            if(data.balance !== undefined && data.balance !== null) document.getElementById('nav-balance').innerText = formatCurrency(data.balance);
            if (data.is_running) {
                document.getElementById('btn-start-bot').classList.add('hidden'); document.getElementById('btn-stop-bot').classList.remove('hidden'); document.getElementById('detail-status').innerHTML = `<span class="text-gojek font-bold">🟢 Bot Aktif.</span> Memonitor pergerakan harga.`;
            } else {
                document.getElementById('btn-start-bot').classList.remove('hidden'); document.getElementById('btn-stop-bot').classList.add('hidden'); document.getElementById('detail-status').innerHTML = `<span class="text-red font-bold">🔴 Offline.</span> Bot terhenti.`;
            }
            document.getElementById('val-total').innerText = data.stats.total_trade;

            let sigLoss = calculateSigLoss(data.history); document.getElementById('val-sig-loss').innerText = sigLoss;
            
            let sigWin = 0;
            if(data.history && data.history.length > 0) {
                const candles = data.history.filter(item => item.warna === "Hijau" || item.warna === "Merah"); let blocks = {};
                candles.forEach(c => {
                    if(c.waktu && c.waktu.includes(':')) {
                        let parts = c.waktu.split(':'); let hh = parts[0]; let mm = parseInt(parts[1]);
                        let baseMm = Math.floor(mm / 5) * 5; let key = c.tanggal + '_' + hh + ':' + baseMm.toString().padStart(2, '0');
                        if(!blocks[key]) blocks[key] = {};
                        if(mm % 5 === 0) blocks[key].c1 = c.warna; 
                        if(mm % 5 === 2) blocks[key].c2 = c.warna; 
                    }
                });
                for(let k in blocks) { let b = blocks[k]; if(b.c1 && b.c2 && b.c1 === b.c2) sigWin++; }
            }
            document.getElementById('val-sig-win').innerText = sigWin;
            document.getElementById('val-hijau').innerText = data.stats.total_hijau; document.getElementById('val-merah').innerText = data.stats.total_merah;

            if(data.telegram) {
                const btn = document.getElementById('btn-tg-toggle'); const stText = document.getElementById('tg-status-text'); const inLoss = document.getElementById('tg-target-loss');
                if(data.telegram.active) {
                    btn.classList.replace('bg-blue-600', 'bg-red-600'); btn.innerText = '⏹ Hentikan Telegram';
                    stText.innerText = 'Status: AKTIF (Server 24 Jam)'; stText.classList.replace('text-gray-500', 'text-gojek');
                    if(document.activeElement !== inLoss) inLoss.value = data.telegram.target_loss;
                    inLoss.disabled = true; if(sigLoss > 0) document.getElementById('val-target-op').innerText = data.telegram.trade_counter + 1;
                } else {
                    btn.classList.replace('bg-red-600', 'bg-blue-600'); btn.innerText = 'Aktifkan Telegram';
                    stText.innerText = 'Status: NONAKTIF'; stText.classList.replace('text-gojek', 'text-gray-500');
                    inLoss.disabled = false; document.getElementById('val-target-op').innerText = 0;
                }
            }

            currentDetailHistory = data.history || [];
            if(!data.is_running && currentDetailHistory.length === 0) {
                document.getElementById('table-body').innerHTML = `<tr><td colspan="3" class="py-20 text-center text-gray-500">Silakan klik "Hubungkan Bot" terlebih dahulu.</td></tr>`;
                document.getElementById('detail-pagination-controls').innerHTML = '';
            } else { renderDetailTable(); }
        });
    }

    function renderDetailTable() {
        const tbody = document.getElementById('table-body');
        const start = (detailCurrentPage - 1) * detailItemsPerPage; const paginated = currentDetailHistory.slice(start, start + detailItemsPerPage);
        
        tbody.innerHTML = '';
        if(paginated.length > 0) {
            paginated.forEach(item => {
                let pillClass = "pill-abu"; let label = item.warna;
                if(item.warna.includes("Hijau")) pillClass = "pill-hijau"; else if(item.warna.includes("Merah")) pillClass = "pill-merah";
                
                let marketName = item.market ? item.market : currentMarket;

                tbody.innerHTML += `
                <tr class="hover:bg-gray-50/50">
                    <td class="py-4 px-8">
                        <div class="text-base font-bold text-dark">${item.waktu}</div>
                        <div class="text-xs text-gray-400 mt-0.5">${item.tanggal}</div>
                    </td>
                    <td class="py-4 px-8 font-bold text-dark">${marketName}</td>
                    <td class="py-4 px-8"><span class="pill ${pillClass}">${label}</span></td>
                </tr>`;
            });
        }
        renderDetailPagination();
    }

    function renderDetailPagination() {
        const container = document.getElementById('detail-pagination-controls'); container.innerHTML = '';
        if(currentDetailHistory.length === 0) return;
        const totalPages = Math.ceil(currentDetailHistory.length / detailItemsPerPage) || 1;
        
        const prevDisabled = detailCurrentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100 cursor-pointer';
        container.innerHTML += `<button onclick="changeDetailPage(${detailCurrentPage - 1})" class="px-3 py-1 bg-white border rounded-lg text-xs font-bold ${prevDisabled}">Prev</button>`;
        
        let startPage = Math.max(1, detailCurrentPage - 2); let endPage = Math.min(totalPages, detailCurrentPage + 2);
        for(let i = startPage; i <= endPage; i++) {
            const activeClass = i === detailCurrentPage ? 'bg-gojek text-white' : 'bg-white text-gray-600 hover:bg-gray-100 cursor-pointer';
            container.innerHTML += `<button onclick="changeDetailPage(${i})" class="w-8 h-8 border rounded-lg text-xs font-bold ${activeClass}">${i}</button>`;
        }
        
        const nextDisabled = detailCurrentPage === totalPages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100 cursor-pointer';
        container.innerHTML += `<button onclick="changeDetailPage(${detailCurrentPage + 1})" class="px-3 py-1 bg-white border rounded-lg text-xs font-bold ${nextDisabled}">Next</button>`;
    }

    function changeDetailPage(page) {
        const totalPages = Math.ceil(currentDetailHistory.length / detailItemsPerPage);
        if(page < 1 || page > totalPages) return;
        detailCurrentPage = page; renderDetailTable();
    }
</script>
@endsection