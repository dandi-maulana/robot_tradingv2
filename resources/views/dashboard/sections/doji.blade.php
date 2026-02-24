<div id="view-doji" class="hidden animate-fade-in pb-16">
    <div class="mb-6 bg-white p-5 rounded-3xl shadow-sm border border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4 relative overflow-hidden">
        {{-- Background Pattern --}}
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-indigo-50 rounded-full blur-3xl opacity-50 z-0"></div>
        <div class="absolute right-20 -bottom-10 w-32 h-32 bg-blue-50 rounded-full blur-2xl opacity-50 z-0"></div>

        <div class="relative z-10 flex flex-col gap-1">
            <h2 class="text-xl sm:text-2xl font-black text-dark tracking-tight flex items-center gap-2">
                <span class="bg-indigo-100 text-indigo-600 p-2 rounded-xl shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </span>
                Analisa Doji (Tingkat Tinggi)
            </h2>
            <p class="text-gray-500 text-xs sm:text-sm font-medium">Hanya merender Market yang sedang menemui <span class="text-red-500 font-bold bg-red-50 px-2 py-0.5 rounded-md">1 s/d 9 False</span> berturut-turut.</p>
        </div>
        
        <div class="relative z-10 flex items-center bg-gray-50 p-1.5 rounded-xl border border-gray-100 shadow-inner max-w-max">
            <div class="flex items-center gap-2 px-3 py-1.5">
                <div class="w-2.5 h-2.5 rounded-full bg-indigo-500 animate-pulse shadow-[0_0_8px_rgba(99,102,241,0.6)]"></div>
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Pemantauan 50 Candle</span>
            </div>
        </div>
    </div>

    {{-- Tabel Analisa Doji --}}
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden relative">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left" id="table-doji-analytics">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100 text-xs uppercase tracking-wider text-gray-400 font-bold">
                        <th class="px-6 py-4 rounded-tl-3xl">Market (Pair)</th>
                        <th class="px-6 py-4 text-center">Status Sinyal</th>
                        <th class="px-6 py-4 text-center">Data Candle</th>
                        <th class="px-6 py-4 text-center">Jumlah Doji</th>
                        <th class="px-6 py-4 text-center rounded-tr-3xl">Winrate Doji</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-50" id="doji-tbody">
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <span class="bg-gray-50 p-4 rounded-full">
                                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                </span>
                                <p class="text-sm font-bold text-gray-500">Mencari Data Histori...</p>
                                <p class="text-[11px] text-gray-400">Tabel ini akan otomatis terisi saat market menyentuh 1 - 9 False berturut-turut.</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
