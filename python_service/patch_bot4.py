import sys

fpath = "d:/Robot Trading terbaru/robot_tradingv2/python_service/app.py"
with open(fpath, "r", encoding="utf-8") as f:
    c = f.read()

doji_api_old = """@app.route('/api/status_all', methods=['GET'])
def status_all():
    conn = get_db_connection()
    if not conn: return jsonify({"active_markets": [], "market_streaks": {}, "balance": global_demo_balance, "tg_active_count": 0})

    c_dict = conn.cursor(dictionary=True)
    c_dict.execute("SELECT market, tg_active FROM market_states WHERE is_running = 1")
    running_data = c_dict.fetchall()

    active_markets = [row['market'] for row in running_data]
    tg_active_count = sum(1 for row in running_data if row['tg_active'] == 1)

    market_streaks = {}
    for mkt in active_markets:
        c_dict.execute("SELECT market, tanggal, waktu, warna FROM market_histories WHERE market = %s ORDER BY id DESC LIMIT 50", (mkt,))
        hist = c_dict.fetchall()
        market_streaks[mkt] = calc_sig_loss(hist)

    c_dict.close()
    conn.close()
    return jsonify({
        "active_markets": active_markets,
        "market_streaks": market_streaks,
        "balance": global_demo_balance,
        "tg_active_count": tg_active_count
    })"""

doji_api_new = """@app.route('/api/status_all', methods=['GET'])
def status_all():
    conn = get_db_connection()
    if not conn: return jsonify({"active_markets": [], "market_streaks": {}, "doji_analytics": [], "balance": global_demo_balance, "tg_active_count": 0})

    c_dict = conn.cursor(dictionary=True)
    c_dict.execute("SELECT market, tg_active FROM market_states WHERE is_running = 1")
    running_data = c_dict.fetchall()

    active_markets = [row['market'] for row in running_data]
    tg_active_count = sum(1 for row in running_data if row['tg_active'] == 1)

    market_streaks = {}
    doji_analytics = []
    
    for mkt in active_markets:
        c_dict.execute("SELECT market, tanggal, waktu, warna FROM market_histories WHERE market = %s ORDER BY id DESC LIMIT 100", (mkt,))
        raw_hist = c_dict.fetchall()
        
        # Hitung sig_loss normal
        sig_loss = calc_sig_loss(raw_hist)
        market_streaks[mkt] = sig_loss
        
        # LOGIKA ANALISA DOJI KETIKA FALSE >= 10
        if sig_loss >= 10:
            # Mengambil 50 candle terakhir dari raw_hist
            hist_50 = raw_hist[:50]
            doji_count = 0
            for item in hist_50:
                if item['warna'] and "Doji" in item['warna']:
                    doji_count += 1
            
            # Hitung Winrate
            winrate = (doji_count / 50.0) * 100
            
            doji_analytics.append({
                "market": mkt,
                "consecutive_false": sig_loss,
                "doji_count": doji_count,
                "total_candles": 50,
                "winrate": round(winrate, 1) # contoh: 12.5%
            })

    c_dict.close()
    conn.close()
    return jsonify({
        "active_markets": active_markets,
        "market_streaks": market_streaks,
        "doji_analytics": doji_analytics,
        "balance": global_demo_balance,
        "tg_active_count": tg_active_count
    })"""

c = c.replace(doji_api_old, doji_api_new)

with open(fpath, "w", encoding="utf-8") as f:
    f.write(c)
