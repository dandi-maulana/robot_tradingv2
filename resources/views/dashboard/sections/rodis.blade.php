<div id="view-rodis" class="fade-in hidden">
    <div
        class="bg-gradient-to-br from-indigo-900 to-slate-900 rounded-3xl p-6 md:p-8 shadow-lg border border-indigo-500/30 mb-8 relative overflow-hidden text-white">
        <h2 class="text-2xl md:text-3xl font-extrabold mb-2 text-white">Robot <span class="text-indigo-400">RODIS</span>
            (Auto-Trade)</h2>
        <p class="text-indigo-200 mb-8 max-w-2xl text-sm md:text-base leading-relaxed">Atur Target Signal False Anda.
            Robot akan otomatis bersiaga, membaca candle, dan <b>mengeksekusi order Buy/Sell langsung ke broker</b>
            tanpa campur tangan Anda.</p>

        <div class="bg-slate-800/50 backdrop-blur border border-indigo-500/20 p-5 md:p-6 rounded-2xl mb-8 relative z-10">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                <div class="w-full">
                    <label class="block text-xs font-bold text-indigo-300 uppercase mb-2">Pilih Market Aktif</label>
                    <select id="rodis-market-select"
                        class="w-full px-4 py-3 bg-slate-900 border border-indigo-500/50 rounded-xl font-bold text-white outline-none focus:border-indigo-400 appearance-none"></select>
                </div>
                <div class="w-full">
                    <label class="block text-xs font-bold text-indigo-300 uppercase mb-2">Target Signal False</label>
                    <input type="number" id="rodis-target-loss" value="7" min="1"
                        class="w-full px-4 py-3 bg-slate-900 border border-indigo-500/50 rounded-xl font-bold text-xl text-white outline-none text-center">
                </div>
                <div class="w-full">
                    <label class="block text-xs font-bold text-indigo-300 uppercase mb-2">Investasi ($)</label>
                    <input type="number" id="rodis-amount" value="10" min="1"
                        class="w-full px-4 py-3 bg-slate-900 border border-indigo-500/50 rounded-xl font-bold text-xl text-white outline-none text-center">
                </div>
                <div class="w-full">
                    <label class="block text-xs font-bold text-indigo-300 uppercase mb-2">Durasi Order</label>
                    <select id="rodis-duration"
                        class="w-full px-4 py-3 bg-slate-900 border border-indigo-500/50 rounded-xl font-bold text-white outline-none appearance-none">
                        <option value="60">60 Detik</option>
                        <option value="120">120 Detik</option>
                        <option value="180">180 Detik</option>
                        <option value="300">300 Detik</option>
                    </select>
                </div>
            </div>

            <div
                class="mt-8 flex flex-col lg:flex-row justify-between items-center border-t border-indigo-500/20 pt-6 gap-6">
                <div class="flex items-center gap-6 w-full lg:w-auto justify-center">
                    <div class="text-center">
                        <p class="text-[10px] text-indigo-300 font-bold uppercase tracking-wider">False Berjalan</p>
                        <p class="text-4xl font-extrabold text-white mt-1" id="rodis-current-loss">0</p>
                    </div>
                    <div class="h-12 w-px bg-indigo-500/30"></div>
                    <div class="text-center">
                        <p class="text-[10px] text-indigo-300 font-bold uppercase tracking-wider">Target Open Ke</p>
                        <p class="text-4xl font-extrabold text-indigo-400 mt-1" id="rodis-target-op">0</p>
                    </div>
                </div>
                <button id="btn-rodis-toggle" onclick="toggleRodisBot()"
                    class="w-full lg:w-auto px-8 py-4 bg-indigo-500 hover:bg-indigo-400 text-white font-extrabold rounded-xl shadow-[0_0_20px_rgba(99,102,241,0.4)] transition-all text-lg tracking-wide">
                    ▶ NYALAKAN RODIS
                </button>
            </div>
        </div>

        <h3 class="text-xs font-bold text-indigo-300 uppercase mb-2 tracking-widest flex items-center gap-2">
            <div class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></div> Log Eksekusi RODIS
        </h3>
        <div id="rodis-terminal" class="shadow-inner text-xs md:text-sm custom-scrollbar"
            style="font-family: 'Courier New', Courier, monospace; background-color: #0f172a; color: #4ade80; height: 250px; overflow-y: auto; padding: 1rem; border-radius: 1rem;">
            <div class="text-indigo-400 opacity-50 mb-2">Sistem siap. Hubungkan Market di Monitor, atur form di atas,
                lalu klik Nyalakan RODIS.</div>
        </div>
    </div>
</div>
