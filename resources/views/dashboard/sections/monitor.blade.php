<div id="view-dashboard" class="fade-in block">
    <div
        class="flex flex-col xl:flex-row justify-between items-start xl:items-center mb-6 gap-4 border-b border-gray-100 pb-5">

        <div>
            <h3 class="text-xl font-bold text-dark hidden md:block mb-2">Pusat Kendali Market</h3>
            <div id="monitor-status-badge" class="flex flex-wrap items-center gap-2 text-xs font-medium">
                <span
                    class="px-2.5 py-1.5 bg-indigo-50 border border-indigo-100 text-indigo-800 rounded-lg flex items-center gap-1.5 shadow-sm">
                    🤖 Bot Berjalan: <b id="lbl-bot-count" class="text-indigo-600 text-sm">0/27</b>
                </span>
                <span
                    class="px-2.5 py-1.5 bg-gray-50 border border-gray-200 text-gray-600 rounded-lg flex items-center gap-1.5 shadow-sm">
                    📲 Sinyal Massal: <b id="lbl-tg-count" class="text-gray-400 font-bold">OFF</b>
                </span>
            </div>
        </div>

        <div class="flex flex-col md:flex-row gap-3 w-full xl:w-auto">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 w-full md:w-auto">
                <button onclick="startAllMarkets()"
                    class="px-4 py-2.5 bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-700 shadow-sm text-sm transition-all flex items-center justify-center gap-1 w-full">
                    🤖 Hubungkan Semua
                </button>
                <button onclick="stopAllMarkets(event)"
                    class="px-4 py-2.5 bg-red-600 text-white font-bold rounded-lg hover:bg-red-700 shadow-sm text-sm transition-all flex items-center justify-center gap-1 w-full">
                    ⏹ Hentikan Semua
                </button>
                <button onclick="resetAllMarkets()"
                    class="px-4 py-2.5 bg-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-300 shadow-sm text-sm transition-all flex items-center justify-center gap-1 w-full">
                    🔄 Reset Data
                </button>
            </div>

            <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
                <div class="flex flex-1 border border-blue-200 rounded-lg overflow-hidden bg-white shadow-sm w-full">
                    <div class="bg-blue-50 px-3 py-2 border-r border-blue-200 flex items-center">
                        <span class="text-xs font-bold text-blue-800 uppercase">False Ke:</span>
                    </div>
                    <input type="number" id="mass-tg-loss" value="7" min="1"
                        class="w-16 flex-1 text-center text-sm font-bold outline-none text-blue-900 border-r border-blue-200">
                    <button onclick="activateMassTelegram(event)"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm transition-colors flex items-center justify-center gap-1">
                        📲 Sinyal Massal
                    </button>
                </div>
                <button onclick="deactivateMassTelegram(event)"
                    class="w-full sm:w-auto px-4 py-2 bg-red-50 text-red-600 border border-red-200 font-bold rounded-lg hover:bg-red-100 shadow-sm text-sm transition-all flex items-center justify-center gap-1">
                    🔕 Matikan Semua
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 md:col-span-2" id="live-streak-container">
            <div
                class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-3 border-b border-gray-50 pb-3 gap-2">
                <h3 class="text-sm font-extrabold text-dark flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-red animate-pulse shadow-[0_0_8px_#ef4444]"></span>
                    Live False Streak (Backtest Monitor)
                </h3>

                <div
                    class="text-xs font-bold text-blue-700 bg-blue-50 border border-blue-200 px-3 py-1.5 rounded-lg flex items-center gap-1.5 shadow-sm">
                    ⏰ <span id="realtime-clock">Memuat Waktu...</span>
                </div>
            </div>
            <div id="streak-list" class="flex flex-wrap gap-2.5 pt-1 min-h-[30px] items-center">
                <span class="text-xs text-gray-400 font-medium italic">Belum ada market yang berjalan...</span>
            </div>
        </div>

        <div
            class="bg-slate-50 rounded-2xl p-5 shadow-inner border border-slate-200 flex flex-col h-56 md:h-auto overflow-hidden">
            <h3
                class="text-sm font-extrabold text-slate-700 mb-3 flex items-center gap-2 border-b border-slate-200 pb-2">
                🎯 Riwayat Target Tercapai
            </h3>
            <div id="streak-history-list" class="flex-1 overflow-y-auto space-y-2 pr-1 custom-scrollbar">
                <div class="text-xs text-slate-400 italic text-center mt-4">Belum ada notifikasi...</div>
            </div>
        </div>
    </div>

    <div id="market-grid-container" class="grid grid-cols-2 md:grid-cols-4 gap-5 min-h-[300px]"></div>
    <div id="pagination-controls" class="flex justify-center items-center gap-2 mt-8 flex-wrap"></div>
</div>

<style>
    /* Styling khusus untuk scrollbar riwayat */
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
</style>
