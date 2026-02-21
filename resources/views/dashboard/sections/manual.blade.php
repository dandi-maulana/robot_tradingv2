<div id="view-trade" class="fade-in hidden">
    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 mb-8 relative overflow-hidden">
        <h2 class="text-2xl font-extrabold mb-2">Pusat Eksekusi <span class="text-gojek">Trade Manual</span></h2>
        <p class="text-gray-500 mb-6">Pilih salah satu market yang sudah berjalan (dihubungkan di menu Monitor)
            untuk mengeksekusi order.</p>

        <h3 class="text-sm font-bold text-gray-500 uppercase mb-4">1. Pilih Market Yang Sedang Aktif</h3>
        <div id="trade-market-container" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        </div>

        <div id="trade-panel" class="hidden">
            <h3 class="text-sm font-bold text-gray-500 uppercase mb-4 border-t pt-6">2. Eksekusi Order Terpusat
                <span id="trade-selected-market" class="text-gojek font-extrabold"></span>
            </h3>
            <div
                class="flex flex-col md:flex-row gap-4 items-end bg-gray-50 p-6 rounded-2xl border border-gray-200 shadow-inner">
                <div class="w-full md:w-1/4">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Investasi ($)</label>
                    <input type="number" id="trade-amount" value="10" min="1"
                        class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl font-bold text-lg outline-none">
                </div>
                <div class="w-full md:w-1/4">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Durasi Order</label>
                    <select id="trade-duration"
                        class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl font-bold text-base outline-none">
                        <option value="60">60 Detik</option>
                        <option value="120">120 Detik</option>
                        <option value="180">180 Detik</option>
                        <option value="300">300 Detik</option>
                    </select>
                </div>
                <div class="w-full md:w-2/4 flex gap-3">
                    <button onclick="executeTradeFromPanel('up')"
                        class="flex-1 bg-gojek hover:bg-gojek-dark text-white font-bold py-3 rounded-xl shadow-md text-lg">▲
                        BUY NAIK</button>
                    <button onclick="executeTradeFromPanel('down')"
                        class="flex-1 bg-red hover:bg-red-dark text-white font-bold py-3 rounded-xl shadow-md text-lg">▼
                        SELL TURUN</button>
                </div>
            </div>
        </div>

        <div class="bg-indigo-50 border border-indigo-200 rounded-2xl p-6 mt-10 shadow-sm">
            <h3 class="text-sm font-extrabold text-indigo-900 uppercase mb-2">💡 Daftar Market Fixed Time (Buka 24
                Jam)</h3>
            <p class="text-xs text-indigo-700 mb-4">
                Jika Anda mengalami error <i>"pair_unavailable"</i>, artinya bursa reguler sedang tutup/dikunci.
                Silakan kembali ke menu Monitor dan pilih market di bawah ini yang <strong>selalu buka 24 jam
                    nonstop untuk Fixed Time Trade:</strong>
            </p>
            <div class="flex flex-wrap gap-2">
                <span
                    class="px-3 py-1.5 bg-white border border-indigo-200 text-indigo-800 rounded-lg text-xs font-bold shadow-sm">🌏
                    Asia Composite Index</span>
                <span
                    class="px-3 py-1.5 bg-white border border-indigo-200 text-indigo-800 rounded-lg text-xs font-bold shadow-sm">🌍
                    Europe Composite Index</span>
                <span
                    class="px-3 py-1.5 bg-white border border-indigo-200 text-indigo-800 rounded-lg text-xs font-bold shadow-sm">🌾
                    Commodity Composite</span>
                <span
                    class="px-3 py-1.5 bg-white border border-indigo-200 text-indigo-800 rounded-lg text-xs font-bold shadow-sm">🇯🇵
                    USD/JPY OTC</span>
                <span
                    class="px-3 py-1.5 bg-white border border-indigo-200 text-indigo-800 rounded-lg text-xs font-bold shadow-sm">🇪🇺
                    EUR/USD OTC</span>
                <span
                    class="px-3 py-1.5 bg-white border border-indigo-200 text-indigo-800 rounded-lg text-xs font-bold shadow-sm">🇬🇧
                    GBP/USD OTC</span>
            </div>
        </div>
    </div>
</div>
