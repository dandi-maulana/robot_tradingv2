<div id="view-dashboard" class="fade-in block">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <h3 class="text-xl font-bold text-dark mb-2 md:mb-0 hidden md:block">Pilih Market (Monitoring)</h3>

        <div class="flex flex-col gap-3 w-full md:w-auto">
            <div class="flex flex-wrap gap-2 w-full items-center justify-start md:justify-end">
                <button onclick="startAllMarkets()"
                    class="px-4 py-2 bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-700 shadow-sm text-sm transition-all flex items-center gap-1">
                    🌍 Hubungkan Semua
                </button>
                <button onclick="stopAllMarkets(event)"
                    class="px-4 py-2 bg-red-600 text-white font-bold rounded-lg hover:bg-red-700 shadow-sm text-sm transition-all flex items-center gap-1">
                    ⏹ Hentikan Semua
                </button>
                <button onclick="resetAllMarkets()"
                    class="px-4 py-2 bg-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-300 shadow-sm text-sm transition-all flex items-center gap-1">
                    🔄 Reset Data
                </button>
            </div>

            <div class="flex flex-wrap gap-2 w-full items-center justify-start md:justify-end">
                <div class="flex border border-blue-200 rounded-lg overflow-hidden bg-white shadow-sm">
                    <div class="bg-blue-50 px-3 py-2 border-r border-blue-200 flex items-center">
                        <span class="text-xs font-bold text-blue-800 uppercase">Loss Ke:</span>
                    </div>
                    <input type="number" id="mass-tg-loss" value="7" min="1"
                        class="w-16 text-center text-sm font-bold outline-none text-blue-900 border-r border-blue-200">
                    <button onclick="activateMassTelegram(event)"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm transition-colors flex items-center gap-1">
                        📲 Sinyal Massal
                    </button>
                </div>
                <button onclick="deactivateMassTelegram(event)"
                    class="px-4 py-2 bg-red-50 text-red-600 border border-red-200 font-bold rounded-lg hover:bg-red-100 shadow-sm text-sm transition-all flex items-center gap-1">
                    🔕 Matikan Telegram Semua
                </button>
            </div>
        </div>
    </div>
    <div id="market-grid-container" class="grid grid-cols-2 md:grid-cols-4 gap-5 min-h-[300px]"></div>
    <div id="pagination-controls" class="flex justify-center items-center gap-2 mt-8"></div>
</div>
