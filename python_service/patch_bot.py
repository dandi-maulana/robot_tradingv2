import sys

fpath = "d:/Robot Trading terbaru/robot_tradingv2/python_service/app.py"
with open(fpath, "r", encoding="utf-8") as f:
    c = f.read()

# 1. Connection Pooling
c = c.replace("import mysql.connector", "import mysql.connector\nfrom mysql.connector import pooling")
pool_code = """
DB_CONFIG = {'host': 'localhost', 'user': 'root', 'password': '', 'database': 'robot_trading'}
db_pool = None
try:
    db_pool = mysql.connector.pooling.MySQLConnectionPool(pool_name="mypool", pool_size=16, wsrep_sync_wait=0, **DB_CONFIG)
except Exception as e: print("Pool Error:", e)

def get_db_connection():
    try:
        if db_pool: return db_pool.get_connection()
        return mysql.connector.connect(**DB_CONFIG)
    except: return None
"""
c = c.replace("""DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'robot_trading'
}

def get_db_connection():
    try:
        return mysql.connector.connect(**DB_CONFIG)
    except mysql.connector.Error as err:
        print(f"Error Database: {err}")
        return None""", pool_code.strip())

# 2. init_market_state
init_old = """def init_market_state(market_name):
    conn = get_db_connection()
    if not conn: return
    c = conn.cursor()
    c.execute("SELECT id FROM market_states WHERE market = %s", (market_name,))
    if not c.fetchone():
        c.execute("INSERT INTO market_states (market, created_at, updated_at) VALUES (%s, NOW(), NOW())", (market_name,))
    c.execute("UPDATE market_states SET is_running = 1, updated_at = NOW() WHERE market = %s", (market_name,))
    conn.commit()
    c.close()
    conn.close()"""
init_new = """def init_market_state(market_name):
    if market_name not in markets_data: markets_data[market_name] = {"manual_queue": []}
    conn = get_db_connection()
    if not conn: return
    c = conn.cursor(dictionary=True)
    c.execute("SELECT is_running, tg_active, tg_target_loss, tg_phase, tg_trade_counter, tg_last_candle, tg_direction FROM market_states WHERE market = %s", (market_name,))
    row = c.fetchone()
    c.close()
    
    if not row:
        c = conn.cursor()
        c.execute("INSERT INTO market_states (market, created_at, updated_at) VALUES (%s, NOW(), NOW())", (market_name,))
        conn.commit(); c.close()
        row = {'is_running': 1, 'tg_active': 0, 'tg_target_loss': 7, 'tg_phase': 'IDLE', 'tg_trade_counter': 0, 'tg_last_candle': '', 'tg_direction': ''}
    else:
        c = conn.cursor()
        c.execute("UPDATE market_states SET is_running = 1, updated_at = NOW() WHERE market = %s", (market_name,))
        conn.commit(); c.close(); row['is_running'] = 1
        
    markets_data[market_name].update({
        'is_running': row.get('is_running', 1), 'tg_active': row.get('tg_active', 0),
        'tg_target_loss': row.get('tg_target_loss', 7), 'tg_phase': row.get('tg_phase', 'IDLE'),
        'tg_trade_counter': row.get('tg_trade_counter', 0), 'tg_last_candle': row.get('tg_last_candle', ''),
        'tg_direction': row.get('tg_direction', '')
    })
    conn.close()"""
c = c.replace(init_old, init_new)

# 3. Main Loop
loop_old = """    last_minute_checked = -1

    while True:
        conn = get_db_connection()
        if not conn: break
        c = conn.cursor(dictionary=True)
        c.execute("SELECT is_running, tg_active, tg_target_loss, tg_phase, tg_trade_counter, tg_last_candle, tg_direction FROM market_states WHERE market = %s", (market_name,))
        state = c.fetchone()

        if not state or state['is_running'] == 0:
            c.close()
            conn.close()
            if hasattr(client, 'close'): await client.close()
            elif hasattr(client, 'disconnect'): await client.disconnect()
            break

        now = datetime.now()"""
loop_new = """    last_minute_checked = -1

    while True:
        if market_name not in markets_data: break
        state = markets_data[market_name]
        if state.get('is_running', 0) == 0:
            if hasattr(client, 'close'): await client.close()
            elif hasattr(client, 'disconnect'): await client.disconnect()
            break
        now = datetime.now()"""
c = c.replace(loop_old, loop_new)

# 4. Ping Server Reconnect
ping_old = """        # PING SERVER
        if now.second % 15 == 0 and now.microsecond < 500000:
            try: await client.send_message({"e": 98, "d": []})
            except Exception: pass"""
ping_new = """        # PING SERVER
        if now.second % 15 == 0 and now.microsecond < 500000:
            try: await client.send_message({"e": 98, "d": []})
            except Exception:
                try:
                    if hasattr(client, 'close'): await client.close()
                    elif hasattr(client, 'disconnect'): await client.disconnect()
                except: pass
                await asyncio.sleep(1)
                try: await client.start()
                except: pass"""
c = c.replace(ping_old, ping_new)

# 5. API start_all stagger
start_old = """    started = 0
    for market_name in ASSET_MAPPING.keys():
        if market_name not in markets_data: markets_data[market_name] = {"manual_queue": []}
        init_market_state(market_name)
        threading.Thread(target=run_trading_bot_thread, args=(market_name, token, account_id), daemon=True).start()
        started += 1
    return jsonify({"status": "success", "message": f"Berhasil menghidupkan {started} market!"})"""
start_new = """    def start_all_bg():
        for m in ASSET_MAPPING.keys():
            if m not in markets_data: markets_data[m] = {"manual_queue": []}
            init_market_state(m)
            threading.Thread(target=run_trading_bot_thread, args=(m, token, account_id), daemon=True).start()
            time.sleep(1.5)
    threading.Thread(target=start_all_bg, daemon=True).start()
    return jsonify({"status": "success", "message": f"Memulai {len(ASSET_MAPPING)} market secara bertahap!"})"""
c = c.replace(start_old, start_new)

with open(fpath, "w", encoding="utf-8") as f:
    f.write(c)
