<div id="view-dashboard" class="fade-in block w-full max-w-none">
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

        <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-3 w-full">

            <!-- ================= LEFT CONTROL ================= -->
            <div class="flex flex-wrap gap-2">

                <!-- PLAY -->
                <button id="btn-play" onclick="startAllMarkets(event)"
                    class="control-btn bg-emerald-600 hover:bg-emerald-700">
                    <span class="btn-icon">▶</span>
                    <span>PLAY</span>
                </button>

                <!-- STOP -->
                <button id="btn-stop" onclick="stopAllMarkets(event)" class="control-btn bg-red-600 hover:bg-red-700">
                    <span class="btn-icon">■</span>
                    <span>STOP</span>
                </button>

                <!-- RESET -->
                <button onclick="resetAllMarkets()" class="control-btn
           bg-gray-400 hover:bg-gray-400
           text-gray-800
           border border-gray-300
           shadow-sm hover:shadow
           transition-all duration-200">
                    🔄 Reset Data
                </button>
            </div>


            <!-- ================= RIGHT CONTROL ================= -->
            <div class="flex flex-wrap gap-2 w-full xl:w-auto">

                <!-- FALSE KE -->
                <div
                    class="flex items-center border border-blue-200 rounded-lg overflow-hidden bg-white shadow-sm h-[42px]">

                    <div class="bg-blue-50 px-3 h-full flex items-center border-r border-blue-200">
                        <span class="text-xs font-bold text-blue-800 uppercase whitespace-nowrap">
                            False Ke:
                        </span>
                    </div>

                    <input type="number" id="mass-tg-loss" value="7" min="1"
                        class="w-16 text-center text-sm font-bold outline-none text-blue-900">

                    <button onclick="activateMassTelegram(event)"
                        class="h-full px-4 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm flex items-center gap-1">
                        📲 Sinyal Massal
                    </button>
                </div>

                <!-- MATIKAN -->
                <button onclick="deactivateMassTelegram(event)" class="control-btn
           bg-red-600 hover:bg-red-700
           text-white
           border border-red-600">
                    🔕 Stop All
                </button>

            </div>

        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6 w-full items-stretch">
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 md:col-span-3 w-full flex flex-col flex-1"
            id="live-streak-container">
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
                <span class="text-xs text-gray-400 font-medium italic">Belum ada market yang berjalan...</span>
            </div>
        </div>
    </div>
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

    .connected-glow {
        animation: greenPulse 2.2s infinite ease-in-out;
    }

    /* ===============================
   DANGER MARKET PULSE
================================*/
    .danger-glow {
        animation: redPulse 1.4s infinite ease-in-out;
    }

    .control-btn {
        width: 140px;
        /* FIX WIDTH */
        height: 44px;
        /* FIX HEIGHT */
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;

        font-weight: 700;
        border-radius: 10px;
        color: white;

        transition: all .25s ease;
    }

    .control-btn:disabled {
        opacity: .5;
        cursor: not-allowed;
    }

    .btn-icon {
        width: 18px;
        /* ICON SIZE LOCK */
        text-align: center;
    }

    @keyframes redPulse {
        0% {
            box-shadow: 0 0 0 rgba(239, 68, 68, 0.2);
        }

        50% {
            box-shadow: 0 0 16px rgba(239, 68, 68, 0.6);
        }

        100% {
            box-shadow: 0 0 0 rgba(239, 68, 68, 0.2);
        }
    }
</style>