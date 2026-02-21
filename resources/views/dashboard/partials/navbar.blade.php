<nav class="bg-white sticky top-0 z-50 shadow-sm px-4 sm:px-6 py-3 border-b border-gray-100">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 md:gap-6 relative">

        <div class="flex items-center justify-between md:justify-start w-full md:w-auto gap-4">
            <div class="flex items-center gap-2 sm:gap-3">
                <div class="bg-gojek text-white p-1.5 sm:p-2 rounded-xl shadow-sm">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                        </path>
                    </svg>
                </div>
                <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-dark cursor-pointer"
                    onclick="location.reload()">
                    RODIS <span class="text-gojek font-semibold text-sm sm:text-lg ml-1 hidden lg:inline">(RObot
                        DISana)</span>
                </h1>
            </div>

            <div id="global-status-indicator"
                class="hidden flex items-center gap-1.5 sm:gap-2 text-[10px] sm:text-xs font-bold text-gojek bg-green-50 px-2 sm:px-3 py-1 sm:py-1.5 rounded-full border border-green-200">
                <span class="relative flex h-2 w-2">
                    <span
                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-gojek opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-gojek"></span>
                </span>
                <span class="hidden sm:inline">Analisis</span>
                <span id="nav-active-market"></span>
            </div>
        </div>

        <div class="w-full md:w-auto">
            <div class="hidden md:flex gap-6 font-bold text-sm text-gray-500">
                <a href="#" onclick="showView('dashboard')" class="text-gojek transition hover:text-gojek"
                    id="nav-link-dashboard">Monitor</a>
                <a href="#" onclick="showView('trade')" class="transition hover:text-gojek"
                    id="nav-link-trade">Trade Manual</a>
                <a href="#" onclick="showView('rodis')" class="transition hover:text-gojek text-indigo-600"
                    id="nav-link-rodis">RODIS (Auto)</a>
                <a href="#" onclick="showView('history')" class="transition hover:text-gojek"
                    id="nav-link-history">Riwayat</a>
            </div>

            <div
                class="md:hidden flex gap-5 font-bold text-[11px] text-gray-500 overflow-x-auto no-scrollbar pb-1 w-full">
                <a href="#" onclick="showView('dashboard')" class="text-gojek whitespace-nowrap"
                    id="nav-link-dashboard-mob">Monitor</a>
                <a href="#" onclick="showView('trade')" class="whitespace-nowrap"
                    id="nav-link-trade-mob">Manual</a>
                <a href="#" onclick="showView('rodis')" class="text-indigo-600 whitespace-nowrap"
                    id="nav-link-rodis-mob">RODIS Auto</a>
                <a href="#" onclick="showView('history')" class="whitespace-nowrap"
                    id="nav-link-history-mob">Riwayat</a>
            </div>
        </div>

        <div
            class="flex items-center justify-between md:justify-end gap-2 sm:gap-4 w-full md:w-auto border-t border-gray-100 md:border-t-0 pt-2 md:pt-0">
            <div
                class="bg-indigo-50 border border-indigo-200 px-3 sm:px-4 py-1.5 sm:py-2 rounded-xl flex items-center justify-center gap-1 sm:gap-2 shadow-sm flex-1 md:flex-none">
                <span class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest hidden sm:inline">Order
                    :</span>
                <span class="text-sm sm:text-lg font-extrabold text-indigo-600" id="nav-order-val">$10.00</span>
            </div>

            <div
                class="bg-gray-50 border border-gray-200 px-3 sm:px-4 py-1.5 sm:py-2 rounded-xl flex items-center justify-center gap-1 sm:gap-2 shadow-sm flex-1 md:flex-none">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest hidden sm:inline">Saldo
                    :</span>
                <span class="text-sm sm:text-lg font-extrabold text-gojek" id="nav-balance">$0.00</span>
            </div>

            <div class="relative">
                <button id="btn-kendali"
                    class="bg-dark text-white pl-1.5 pr-3 sm:pr-4 py-1.5 rounded-xl flex items-center justify-center gap-2 shadow-sm font-bold text-xs sm:text-sm hover:bg-gray-800 transition-colors">
                    <img src="{{ asset('assets/images/gibran.jpg') }}"
                        class="w-6 h-6 sm:w-7 sm:h-7 rounded-full object-cover border border-gray-600" alt="Profile">
                    <span class="hidden sm:inline">Akun Trading</span>
                </button>
                <div id="dropdown-kendali"
                    class="hidden absolute right-0 mt-2 w-72 bg-white rounded-2xl shadow-xl border border-gray-100 p-5 z-50">

                    <div class="flex flex-col items-center justify-center mb-4 pb-4 border-b border-gray-100">
                        <img src="{{ asset('assets/images/gibran.jpg') }}"
                            class="w-20 h-20 rounded-full object-cover border-4 border-gray-50 shadow-md mb-2"
                            alt="Profile Dropdown">
                        <h4 class="text-sm font-extrabold text-dark">Administrator</h4>
                        <span class="text-[10px] font-bold text-green-500 bg-green-50 px-2 py-0.5 rounded-full mt-1">●
                            Online</span>
                    </div>

                    <h3 class="text-sm font-extrabold text-dark mb-3">Pusat Kendali</h3>

                    <div class="mb-3">
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Access Token</label>
                        <input type="password" id="token" placeholder="Paste Token..."
                            class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:border-gojek font-mono text-xs transition-all">
                        <p class="text-xs text-red-500 mt-2 font-bold">⚠️ Jangan mengubah atau memasukkan Access Token
                            baru jika Target Account ID di bawah ini sudah terisi dan terhubung!</p>
                    </div>

                    <button id="btn-cek-akun" onclick="checkAccounts()"
                        class="w-full px-4 py-2 mb-3 bg-dark text-white font-bold text-xs rounded-lg hover:bg-gray-800 transition-colors shadow-sm">
                        🔍 Cek Akun
                    </button>

                    <div class="mb-4">
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Target Account
                            ID</label>
                        <select id="account-id"
                            class="w-full px-3 py-2 bg-blue-50 border border-blue-200 rounded-lg focus:outline-none focus:border-blue-500 font-bold text-xs text-blue-800 transition-all cursor-pointer">
                            <option value="">-- Pilih Akun --</option>
                        </select>
                    </div>

                    <div class="border-t border-gray-100 my-3"></div>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="w-full px-4 py-2 bg-red-50 text-red-600 font-bold text-xs rounded-lg hover:bg-red-100 transition-colors flex items-center justify-center gap-2">
                            🚪 Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</nav>

<script>
    // Buka-tutup dropdown
    document.getElementById('btn-kendali').addEventListener('click', function(e) {
        e.stopPropagation();
        document.getElementById('dropdown-kendali').classList.toggle('hidden');
    });

    // Tutup kalau klik di luar area dropdown
    window.addEventListener('click', function(e) {
        if (!document.getElementById('dropdown-kendali').contains(e.target)) {
            document.getElementById('dropdown-kendali').classList.add('hidden');
        }
    });
</script>
