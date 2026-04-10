<div id="view-history" class="fade-in hidden">
    <div class="bg-white rounded-3xl p-6 md:p-8 shadow-sm border border-gray-100 mb-8 relative overflow-hidden">
        <h2 class="text-2xl font-extrabold mb-2">Riwayat <span class="text-gojek">Trade (Order)</span></h2>
        <p class="text-gray-500 mb-6 text-sm md:text-base">Semua riwayat eksekusi order (BUY/SELL) baik secara manual maupun dari sistem
            RODIS Auto-Trade akan tercatat di sini.</p>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden min-h-[300px]">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse" id="history-log-table">
                    <thead>
                        <tr>
                            <th
                                class="py-4 px-4 sm:px-8 bg-gray-50 text-[10px] sm:text-xs font-bold text-gray-400 uppercase border-b border-gray-100 whitespace-nowrap">
                                Waktu Order</th>
                            <th
                                class="py-4 px-4 sm:px-8 bg-gray-50 text-[10px] sm:text-xs font-bold text-gray-400 uppercase border-b border-gray-100 whitespace-nowrap">
                                Market</th>
                            <th
                                class="py-4 px-4 sm:px-8 bg-gray-50 text-[10px] sm:text-xs font-bold text-gray-400 uppercase border-b border-gray-100 whitespace-nowrap">
                                Investasi</th>
                            <th
                                class="py-4 px-4 sm:px-8 bg-gray-50 text-[10px] sm:text-xs font-bold text-gray-400 uppercase border-b border-gray-100 whitespace-nowrap">
                                Arah Eksekusi</th>
                        </tr>
                    </thead>
                    <tbody id="history-table-body" class="divide-y divide-gray-100">
                        <tr>
                            <td colspan="4" class="py-20 text-center text-gray-500">Memuat data riwayat...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div id="history-pagination-controls"
                class="flex justify-center items-center gap-1 sm:gap-2 p-3 sm:p-4 border-t border-gray-100 flex-wrap"></div>
        </div>
    </div>
</div>
