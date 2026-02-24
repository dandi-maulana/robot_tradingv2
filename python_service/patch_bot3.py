import sys

fpath = "d:/Robot Trading terbaru/robot_tradingv2/python_service/app.py"
with open(fpath, "r", encoding="utf-8") as f:
    c = f.read()

# Flask Endpoints cache update
stop_old = """@app.route('/api/stop', methods=['POST'])
def stop_bot():
    market = request.json.get('market')
    conn = get_db_connection()
    c = conn.cursor()
    c.execute("UPDATE market_states SET is_running = 0 WHERE market = %s", (market,))
    conn.commit()
    conn.close()
    return jsonify({"status": "success"})

@app.route('/api/stop_all', methods=['POST'])
def stop_all():
    conn = get_db_connection()
    if not conn: return jsonify({"status": "error"})
    c = conn.cursor()
    c.execute("UPDATE market_states SET is_running = 0")
    conn.commit()
    conn.close()
    return jsonify({"status": "success", "message": "Semua bot market berhasil dihentikan!"})"""

stop_new = """@app.route('/api/stop', methods=['POST'])
def stop_bot():
    market = request.json.get('market')
    if market in markets_data: markets_data[market]['is_running'] = 0
    conn = get_db_connection()
    if conn:
        conn.cursor().execute("UPDATE market_states SET is_running = 0 WHERE market = %s", (market,))
        conn.commit(); conn.close()
    return jsonify({"status": "success"})

@app.route('/api/stop_all', methods=['POST'])
def stop_all():
    for m in markets_data.values(): m['is_running'] = 0
    conn = get_db_connection()
    if not conn: return jsonify({"status": "error"})
    conn.cursor().execute("UPDATE market_states SET is_running = 0")
    conn.commit(); conn.close()
    return jsonify({"status": "success", "message": "Semua bot market berhasil dihentikan!"})"""
c = c.replace(stop_old, stop_new)

tg_toggle_old = """@app.route('/api/toggle_telegram', methods=['POST'])
def toggle_telegram():
    data = request.json
    market = data.get('market')
    target_loss = int(data.get('target_loss', 7))

    conn = get_db_connection()
    c = conn.cursor(dictionary=True)
    c.execute("SELECT tg_active FROM market_states WHERE market = %s", (market,))
    state = c.fetchone()
    if state:
        new_active = 0 if state['tg_active'] else 1
        c.execute("UPDATE market_states SET tg_active=%s, tg_target_loss=%s, tg_phase='IDLE' WHERE market=%s", (new_active, target_loss, market))
        conn.commit()
        conn.close()
        return jsonify({"status": "success", "active": bool(new_active)})
    return jsonify({"status": "error", "message": "Market belum aktif!"})

@app.route('/api/toggle_telegram_all', methods=['POST'])
def toggle_telegram_all():
    data = request.json
    target_loss = int(data.get('target_loss', 7))

    conn = get_db_connection()
    c = conn.cursor(dictionary=True)
    c.execute("SELECT market FROM market_states WHERE is_running = 1")
    running_markets = c.fetchall()

    if not running_markets:
        conn.close()
        return jsonify({"status": "error", "message": "Tidak ada market yang sedang berjalan."})

    active_count = 0
    for row in running_markets:
        c.execute("UPDATE market_states SET tg_active=1, tg_target_loss=%s, tg_phase='IDLE' WHERE market=%s", (target_loss, row['market']))
        active_count += 1

    conn.commit()
    conn.close()
    return jsonify({"status": "success", "message": f"Sinyal Telegram DIAKTIFKAN di {active_count} market aktif!"})

@app.route('/api/stop_telegram_all', methods=['POST'])
def stop_telegram_all():
    conn = get_db_connection()
    if not conn: return jsonify({"status": "error"})
    c = conn.cursor()
    c.execute("UPDATE market_states SET tg_active = 0, tg_phase = 'IDLE'")
    conn.commit()
    conn.close()
    return jsonify({"status": "success", "message": "Sinyal Telegram di SEMUA market berhasil dimatikan!"})"""

tg_toggle_new = """@app.route('/api/toggle_telegram', methods=['POST'])
def toggle_telegram():
    data = request.json
    market = data.get('market')
    target_loss = int(data.get('target_loss', 7))

    if market in markets_data:
        new_active = 0 if markets_data[market]['tg_active'] else 1
        markets_data[market].update({'tg_active': new_active, 'tg_target_loss': target_loss, 'tg_phase': 'IDLE'})
        conn = get_db_connection()
        if conn:
            conn.cursor().execute("UPDATE market_states SET tg_active=%s, tg_target_loss=%s, tg_phase='IDLE' WHERE market=%s", (new_active, target_loss, market))
            conn.commit(); conn.close()
        return jsonify({"status": "success", "active": bool(new_active)})
    return jsonify({"status": "error", "message": "Market belum aktif!"})

@app.route('/api/toggle_telegram_all', methods=['POST'])
def toggle_telegram_all():
    data = request.json
    target_loss = int(data.get('target_loss', 7))
    active_count = 0
    conn = get_db_connection()
    c = conn.cursor()
    for m, state in markets_data.items():
        if state.get('is_running'):
            state.update({'tg_active': 1, 'tg_target_loss': target_loss, 'tg_phase': 'IDLE'})
            c.execute("UPDATE market_states SET tg_active=1, tg_target_loss=%s, tg_phase='IDLE' WHERE market=%s", (target_loss, m))
            active_count += 1
    conn.commit(); conn.close()
    return jsonify({"status": "success", "message": f"Sinyal Telegram DIAKTIFKAN di {active_count} market aktif!"})

@app.route('/api/stop_telegram_all', methods=['POST'])
def stop_telegram_all():
    for state in markets_data.values():
        state.update({'tg_active': 0, 'tg_phase': 'IDLE'})
    conn = get_db_connection()
    conn.cursor().execute("UPDATE market_states SET tg_active=0, tg_phase='IDLE'")
    conn.commit(); conn.close()
    return jsonify({"status": "success", "message": "Sinyal Telegram di SEMUA market berhasil dimatikan!"})"""
c = c.replace(tg_toggle_old, tg_toggle_new)

with open(fpath, "w", encoding="utf-8") as f:
    f.write(c)
