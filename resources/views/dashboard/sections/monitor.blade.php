<div id="view-dashboard" class="fade-in block w-full max-w-none">
    <div class="mb-6 gap-4 border-b border-gray-100 pb-5">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
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

            <div class="flex flex-col sm:flex-row sm:items-center gap-3 w-full md:w-auto">
                <!-- ================= LEFT CONTROL ================= -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
                    <button id="btn-toggle" onclick="toggleMarkets(event)"
                        class="control-btn bg-emerald-600 hover:bg-emerald-700 text-white">
                        <span id="toggle-icon" class="btn-icon">▶</span>
                        <span id="toggle-text">PLAY</span>
                    </button>

                    <!-- RESET -->
                    <button onclick="resetAllMarkets()" class="control-btn
                        bg-gray-100 hover:bg-gray-200
                        text-gray-800
                        border border-gray-200
                        shadow-sm hover:shadow
                        transition-all duration-200">
                        🔄 Reset Data
                    </button>
                </div>

                <!-- ================= RIGHT CONTROL ================= -->
                <div class="w-full sm:w-auto">
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center border border-blue-200 rounded-lg overflow-hidden bg-white shadow-sm w-full sm:w-auto">
                        <div class="bg-blue-50 px-3 py-2 sm:py-0 sm:h-[42px] flex items-center border-b sm:border-b-0 sm:border-r border-blue-200">
                            <span class="text-xs font-bold text-blue-800 uppercase whitespace-nowrap">
                                False Ke:
                            </span>
                        </div>

                        <input type="number" id="mass-tg-loss" value="7" min="1"
                            class="w-full sm:w-16 px-3 py-2 sm:px-0 sm:py-0 text-center text-sm font-bold outline-none text-blue-900">

                        <button id="btn-mass-tg" onclick="activateMassTelegram(event)"
                            class="control-btn bg-blue-600 hover:bg-blue-700 text-white">
                            📲 Sinyal Massal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 mb-6 w-full items-stretch">
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 w-full flex flex-col"
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
            width: auto;
            min-width: 140px;
            height: 44px;
            /* FIX HEIGHT */
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;

            font-weight: 700;
            border-radius: 10px;
            padding: 0 14px;

            transition: all .25s ease;
        }

        @media (max-width: 640px) {
            .control-btn {
                width: 100%;
                min-width: 0;
            }
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
</div>
