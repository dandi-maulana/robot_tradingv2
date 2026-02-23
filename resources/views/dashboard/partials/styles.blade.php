<style>
    /* ============================================================
       FADE IN ANIMATION
    ============================================================ */
    .fade-in {
        animation: fadeIn 0.3s ease-in-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* ============================================================
       MARKET CARD
    ============================================================ */
    .market-card {
        transition: all 0.2s ease;
        cursor: pointer;
        border: 2px solid transparent;
        position: relative;
    }

    .market-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
        border-color: #e4e7e9;
    }

    .market-card.is-active {
        border-color: #00aa13;
        background-color: #fafdff;
    }

    .active-badge { display: none; }

    .market-card.is-active .active-badge {
        display: block;
        position: absolute;
        top: 10px;
        right: 10px;
    }

    /* ============================================================
       PILLS
    ============================================================ */
    .pill {
        padding: 4px 12px;
        border-radius: 9999px;
        font-weight: 700;
        font-size: 12px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .pill-hijau            { background-color: #e6f6e8; color: #00aa13; }
    .pill-hijau::before    { content: '▲'; font-size: 9px; }
    .pill-merah            { background-color: #fdedee; color: #ee2737; }
    .pill-merah::before    { content: '▼'; font-size: 9px; }
    .pill-abu              { background-color: #f3f4f6; color: #6b7280; }
    .pill-manual-up        { background-color: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; }
    .pill-manual-down      { background-color: #ffedd5; color: #c2410c; border: 1px solid #fed7aa; }
    .pill-error            { background-color: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

    /* ============================================================
       POPUP ANIMATION
    ============================================================ */
    @keyframes popupSlideIn {
        from { opacity: 0; transform: translateX(40px); }
        to   { opacity: 1; transform: translateX(0); }
    }
    .animate-popup {
        animation: popupSlideIn 0.3s ease forwards;
        transition: opacity 0.4s ease, transform 0.4s ease;
    }

    /* ============================================================
       DARK GLOW
    ============================================================ */
    .danger-glow {
        box-shadow: 0 0 12px rgba(239, 68, 68, 0.35);
    }

    /* ============================================================
       THEME TOGGLE BUTTON
    ============================================================ */
    #theme-toggle {
        cursor: pointer;
        background: rgba(255,255,255,0.08);
        border: 1.5px solid #e5e7eb;
        border-radius: 999px;
        padding: 6px 14px;
        font-size: 16px;
        color: #374151;
        transition: all 0.25s ease;
        display: flex;
        align-items: center;
        gap: 6px;
        line-height: 1;
    }

    #theme-toggle:hover {
        background-color: #f1f5f9;
        border-color: #cbd5e1;
    }

    /* ============================================================
       ██████╗  █████╗ ██████╗ ██╗  ██╗    ███╗   ███╗ ██████╗ ██████╗ ███████╗
       ██╔══██╗██╔══██╗██╔══██╗██║ ██╔╝    ████╗ ████║██╔═══██╗██╔══██╗██╔════╝
       ██║  ██║███████║██████╔╝█████╔╝     ██╔████╔██║██║   ██║██║  ██║█████╗
       ██║  ██║██╔══██║██╔══██╗██╔═██╗     ██║╚██╔╝██║██║   ██║██║  ██║██╔══╝
       ██████╔╝██║  ██║██║  ██║██║  ██╗    ██║ ╚═╝ ██║╚██████╔╝██████╔╝███████╗
       ╚═════╝ ╚═╝  ╚═╝╚═╝  ╚═╝╚═╝  ╚═╝   ╚═╝     ╚═╝ ╚═════╝ ╚═════╝ ╚══════╝
    ============================================================ */

    /* --- Base body & html --- */
    .dark body,
    html.dark {
        background-color: #0d1117 !important;
        color: #c9d1d9 !important;
    }

    .dark body {
        background-color: #0d1117 !important;
    }

    /* --- Navbar --- */
    .dark nav {
        background-color: #161b22 !important;
        border-color: #30363d !important;
        box-shadow: 0 1px 0 rgba(48,54,61,0.8) !important;
    }

    /* --- ALL white backgrounds → dark card --- */
    .dark .bg-white {
        background-color: #161b22 !important;
        color: #c9d1d9 !important;
    }

    /* --- Gray backgrounds --- */
    .dark .bg-gray-50   { background-color: #0d1117 !important; }
    .dark .bg-gray-100  { background-color: #21262d !important; }
    .dark .bg-gray-200  { background-color: #30363d !important; }

    /* --- Indigo/Blue tinted backgrounds --- */
    .dark .bg-indigo-50  { background-color: #1a1f3c !important; }
    .dark .bg-blue-50    { background-color: #0d1b2a !important; }
    .dark .bg-green-50   { background-color: #0d2318 !important; }
    .dark .bg-red-50     { background-color: #2a0d0d !important; }
    .dark .bg-orange-50  { background-color: #2a1a0d !important; }
    .dark .bg-yellow-50  { background-color: #2a220d !important; }

    /* --- Text colors --- */
    .dark .text-dark,
    .dark .text-gray-900,
    .dark .text-gray-800,
    .dark .text-gray-700 { color: #e6edf3 !important; }

    .dark .text-gray-600 { color: #8b949e !important; }
    .dark .text-gray-500 { color: #8b949e !important; }
    .dark .text-gray-400 { color: #6e7681 !important; }

    /* --- Borders --- */
    .dark .border-gray-100,
    .dark .border-gray-200 { border-color: #30363d !important; }
    .dark .border-gray-300 { border-color: #484f58 !important; }

    /* --- Indigo colored text/borders --- */
    .dark .border-indigo-200 { border-color: #3b4280 !important; }
    .dark .border-blue-200   { border-color: #1d4ed8 !important; }
    .dark .border-green-200  { border-color: #166534 !important; }

    /* --- Shadows --- */
    .dark .shadow-sm  { box-shadow: 0 1px 2px rgba(0,0,0,0.6) !important; }
    .dark .shadow-md  { box-shadow: 0 4px 6px rgba(0,0,0,0.6) !important; }
    .dark .shadow-xl  { box-shadow: 0 20px 25px rgba(0,0,0,0.7) !important; }

    /* --- Inputs & Selects --- */
    .dark input,
    .dark select,
    .dark textarea {
        background-color: #0d1117 !important;
        border-color: #30363d !important;
        color: #c9d1d9 !important;
    }

    .dark input::placeholder { color: #484f58 !important; }
    .dark input:focus,
    .dark select:focus { border-color: #58a6ff !important; outline: none; }

    /* --- Dropdown Pusat Kendali --- */
    .dark #dropdown-kendali {
        background-color: #161b22 !important;
        border-color: #30363d !important;
        box-shadow: 0 16px 32px rgba(0,0,0,0.8) !important;
    }

    /* --- Table rows --- */
    .dark table thead tr {
        background-color: #161b22 !important;
    }

    .dark table thead th {
        color: #8b949e !important;
    }

    .dark table tbody tr:hover td {
        background-color: #1c2129 !important;
    }

    .dark table tbody td {
        border-color: #21262d !important;
        color: #c9d1d9 !important;
    }

    /* --- Pills in dark mode --- */
    .dark .pill-abu       { background-color: #21262d; color: #8b949e; border: 1px solid #30363d; }
    .dark .pill-hijau     { background-color: #0d2318; color: #3fb950; }
    .dark .pill-merah     { background-color: #2a0d0d; color: #f85149; }
    .dark .pill-manual-up { background-color: #0d1b2a; color: #58a6ff; border-color: #1d4ed8; }
    .dark .pill-manual-down { background-color: #2a1a0d; color: #fb923c; border-color: #92400e; }
    .dark .pill-error     { background-color: #2a0d0d; color: #f85149; border-color: #6e1212; }

    /* --- Cards (rounded-2xl boxes) --- */
    .dark .rounded-2xl,
    .dark .rounded-xl {
        border-color: #30363d;
    }

    /* --- Market streak list items --- */
    .dark .bg-red-100    { background-color: #3d0f0f !important; }
    .dark .bg-orange-100 { background-color: #3d1f0f !important; }
    .dark .bg-yellow-100 { background-color: #3d2f0f !important; }
    .dark .bg-blue-50    { background-color: #0d1b2a !important; }
    .dark .bg-green-100  { background-color: #0d2318 !important; }
    .dark .border-red-300    { border-color: #7f1d1d !important; }
    .dark .border-orange-300 { border-color: #7c2d12 !important; }
    .dark .border-yellow-300 { border-color: #78350f !important; }
    .dark .border-blue-200   { border-color: #1e3a5f !important; }
    .dark .border-green-300  { border-color: #14532d !important; }
    .dark .text-red-700    { color: #fca5a5 !important; }
    .dark .text-orange-700 { color: #fdba74 !important; }
    .dark .text-yellow-700 { color: #fde047 !important; }
    .dark .text-blue-600   { color: #60a5fa !important; }
    .dark .text-green-800  { color: #86efac !important; }

    /* --- Buttons --- */
    .dark .bg-dark {
        background-color: #21262d !important;
        color: #e6edf3 !important;
    }

    .dark .hover\:bg-gray-800:hover { background-color: #30363d !important; }

    /* --- Indigo buttons / rodis --- */
    .dark .bg-indigo-600 { color: #a5b4fc !important; }
    .dark .text-indigo-600 { color: #a5b4fc !important; }
    .dark .text-indigo-400 { color: #818cf8 !important; }

    /* --- Green text (gojek color) stays bright --- */
    .dark .text-gojek { color: #3fb950 !important; }
    .dark .bg-gojek   { background-color: #00aa13 !important; }

    /* --- Online badge --- */
    .dark .bg-green-50 .text-green-500 { color: #3fb950 !important; }

    /* --- Rodis terminal --- */
    .dark #rodis-terminal {
        background-color: #010409 !important;
        border-color: #30363d !important;
    }

    /* --- Confirm modal --- */
    .dark #confirm-cancel {
        background-color: #21262d !important;
        color: #c9d1d9 !important;
    }
    .dark #confirm-cancel:hover { background-color: #30363d !important; }

    /* --- Scrollbar dark --- */
    .dark ::-webkit-scrollbar { width: 6px; height: 6px; }
    .dark ::-webkit-scrollbar-track { background: #161b22; }
    .dark ::-webkit-scrollbar-thumb { background: #30363d; border-radius: 3px; }
    .dark ::-webkit-scrollbar-thumb:hover { background: #484f58; }

    /* --- Theme toggle in dark mode --- */
    .dark #theme-toggle {
        border-color: #30363d;
        color: #c9d1d9;
        background: rgba(255,255,255,0.04);
    }

    .dark #theme-toggle:hover {
        background-color: #21262d;
        border-color: #484f58;
    }

    /* --- Dividers --- */
    .dark .border-t { border-color: #30363d !important; }

    /* --- bg/white inside dropdowns --- */
    .dark .bg-white\/90 { background-color: rgba(22,27,34,0.9) !important; }

    /* --- Pagination buttons --- */
    .dark button.bg-white {
        background-color: #161b22 !important;
        border-color: #30363d !important;
        color: #c9d1d9 !important;
    }

    .dark button.bg-white:hover { background-color: #21262d !important; }

    /* --- Smooth transition for everything --- */
    body, nav, .bg-white, .bg-gray-50, .bg-gray-100,
    input, select, table, td, th, button, .pill,
    .market-card, #dropdown-kendali {
        transition:
            background-color 0.3s ease,
            color 0.3s ease,
            border-color 0.3s ease,
            box-shadow 0.3s ease;
    }
</style>