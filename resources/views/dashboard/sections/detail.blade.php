<div id="view-detail" class="fade-in hidden">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <button onclick="showView('dashboard')"
                class="flex items-center gap-2 text-sm font-bold text-gray-500 hover:text-dark transition-colors mb-2 bg-white px-4 py-2 rounded-xl shadow-sm border border-gray-200">
                ⬅ Kembali ke Dashboard
            </button>
            <h2 class="text-3xl font-extrabold text-dark mt-2 flex items-center gap-3">
                Analisis: <span id="detail-market-name" class="text-gojek">Market</span>
            </h2>
            <p id="detail-status" class="text-sm text-gray-500 font-medium mt-1">Menunggu instruksi...</p>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
            <button id="btn-start-bot" onclick="startCurrentMarketBot()"
                class="px-6 py-3 bg-gojek text-white font-bold rounded-xl hover:bg-gojek-dark transition-colors shadow-sm text-sm w-full sm:w-auto">
                ▶ Hubungkan Bot
            </button>
            <button id="btn-stop-bot" onclick="stopCurrentMarketBot()"
                class="px-6 py-3 bg-red-light text-red font-bold rounded-xl hover:bg-red-200 transition-colors shadow-sm text-sm w-full sm:w-auto hidden">
                ⏹ Hentikan Bot
            </button>
            <button id="btn-reset-data" onclick="resetCurrentMarket()"
                class="px-6 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-300 transition-colors shadow-sm text-sm w-full sm:w-auto">
                🔄 Reset Data
            </button>
        </div>
    </div>

    <div
        class="bg-blue-50 rounded-2xl p-6 mb-6 shadow-sm border border-blue-100 flex flex-col md:flex-row justify-between items-center gap-4">
        <div>
            <h3 class="text-lg font-bold text-blue-900 mb-1">📲 Sinyal Telegram Otomatis (Monitoring)</h3>
            <p class="text-xs text-blue-700">Berjalan 24 Jam Nonstop dari Server untuk market ini.</p>
            <p id="tg-status-text" class="text-sm font-bold text-gray-500 mt-2">Status: NONAKTIF</p>
        </div>
        <div class="flex gap-3 items-end">
            <div>
                <label class="block text-xs font-bold text-blue-800 uppercase mb-1 text-center">Kirim Saat Loss
                    Ke:</label>
                <input type="number" id="tg-target-loss" value="7"
                    class="w-32 px-4 py-3 bg-white border border-blue-200 rounded-xl font-bold text-xl outline-none text-center shadow-sm">
            </div>
            <button onclick="toggleTelegramServer()" id="btn-tg-toggle"
                class="px-6 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 shadow-md transition-colors">Aktifkan
                Telegram</button>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
        <div
            class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm text-center bg-gradient-to-br from-indigo-50 to-white">
            <p class="text-[10px] font-bold text-indigo-500 uppercase tracking-widest mb-1">Target Open Posisi Ke
            </p>
            <p class="text-2xl font-extrabold text-indigo-900" id="val-target-op">0</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm text-center">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Total Analisis</p>
            <p class="text-2xl font-extrabold text-dark" id="val-total">0</p>
        </div>

        <div class="bg-green-50 p-5 rounded-2xl border border-green-200 shadow-sm text-center relative overflow-hidden">
            <div class="absolute bottom-0 left-0 w-full h-1 bg-green-500"></div>
            <p class="text-[10px] font-bold text-green-600 uppercase tracking-widest mb-1">Signal WIN (Warna Sama)
            </p>
            <p class="text-2xl font-extrabold text-green-600" id="val-sig-win">0</p>
        </div>

        <div class="bg-red-50 p-5 rounded-2xl border border-red-200 shadow-sm text-center relative overflow-hidden">
            <div class="absolute bottom-0 left-0 w-full h-1 bg-red-500"></div>
            <p class="text-[10px] font-bold text-red-600 uppercase tracking-widest mb-1">Signal LOSS (Warna Beda)
            </p>
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
                        <th
                            class="py-4 px-8 bg-gray-50 text-xs font-bold text-gray-400 uppercase border-b border-gray-100">
                            Waktu Candle</th>
                        <th
                            class="py-4 px-8 bg-gray-50 text-xs font-bold text-gray-400 uppercase border-b border-gray-100">
                            Market</th>
                        <th
                            class="py-4 px-8 bg-gray-50 text-xs font-bold text-gray-400 uppercase border-b border-gray-100">
                            Arah / Warna</th>
                    </tr>
                </thead>
                <tbody id="table-body" class="divide-y divide-gray-100">
                    <tr>
                        <td colspan="3" class="py-20 text-center text-gray-500">Silakan klik "Hubungkan Bot"
                            terlebih dahulu.</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div id="detail-pagination-controls"
            class="flex justify-center items-center gap-2 p-4 border-t border-gray-100"></div>
    </div>
</div>
