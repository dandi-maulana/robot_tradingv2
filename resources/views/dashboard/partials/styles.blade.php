<style>
    .fade-in {
        animation: fadeIn 0.3s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

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

    .active-badge {
        display: none;
    }

    .market-card.is-active .active-badge {
        display: block;
        position: absolute;
        top: 10px;
        right: 10px;
    }

    .pill {
        padding: 4px 12px;
        border-radius: 9999px;
        font-weight: 700;
        font-size: 12px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .pill-hijau {
        background-color: #e6f6e8;
        color: #00aa13;
    }

    .pill-hijau::before {
        content: '▲';
        font-size: 9px;
    }

    .pill-merah {
        background-color: #fdedee;
        color: #ee2737;
    }

    .pill-merah::before {
        content: '▼';
        font-size: 9px;
    }

    .pill-abu {
        background-color: #f3f4f6;
        color: #6b7280;
    }

    .pill-manual-up {
        background-color: #e0f2fe;
        color: #0284c7;
        border: 1px solid #bae6fd;
    }

    .pill-manual-down {
        background-color: #ffedd5;
        color: #c2410c;
        border: 1px solid #fed7aa;
    }

    .pill-error {
        background-color: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }
</style>
