from flask import Flask, request, jsonify
from flask_cors import CORS
import threading
import time
from datetime import datetime
import asyncio
import mysql.connector
import urllib.request
import urllib.parse

try:
    from olymptrade_ws import OlympTradeClient
except ImportError:
    OlympTradeClient = None

app = Flask(__name__)
# Izinkan CORS untuk semua origin dan semua metode
CORS(app, resources={r"/api/*": {"origins": "*"}}, supports_credentials=True)

# Variabel RAM (hanya untuk antrian manual trade)
markets_data = {}
global_demo_balance = 0.0

# --- KONFIGURASI MYSQL ---
DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': '@Nightmare02',
    'database': 'robot_trading'
}

def get_db_connection():
    try:
        return mysql.connector.connect(**DB_CONFIG)
    except mysql.connector.Error as err:
        print(f"Error Database: {err}")
        return None

# --- FUNGSI DATABASE HELPER ---
def save_settings(token, account_id):
    conn = get_db_connection()
    if not conn: return
    c = conn.cursor()
    c.execute("SELECT id FROM settings WHERE id = 1")
    if c.fetchone():
        c.execute("UPDATE settings SET token = %s, account_id = %s, updated_at = NOW() WHERE id = 1", (token, account_id))
    else:
        c.execute("INSERT INTO settings (id, token, account_id, created_at, updated_at) VALUES (1, %s, %s, NOW(), NOW())", (token, account_id))
    conn.commit()
    c.close()
    conn.close()

def get_settings():
    conn = get_db_connection()
    if not conn: return {"token": "", "account_id": ""}
    c = conn.cursor()
    c.execute("SELECT token, account_id FROM settings WHERE id = 1")
    res = c.fetchone()
    c.close()
    conn.close()
    return {"token": res[0], "account_id": res[1]} if res else {"token": "", "account_id": ""}

def init_market_state(market_name):
    conn = get_db_connection()
    if not conn: return
    c = conn.cursor()
    c.execute("SELECT id FROM market_states WHERE market = %s", (market_name,))
    if not c.fetchone():
        c.execute("INSERT INTO market_states (market, created_at, updated_at) VALUES (%s, NOW(), NOW())", (market_name,))
    c.execute("UPDATE market_states SET is_running = 1, updated_at = NOW() WHERE market = %s", (market_name,))
    conn.commit()
    c.close()
    conn.close()

# --- FUNGSI BARU LOGIKA WARNA & DOJI ---
def get_candle_color(o, h, l, c):
    """Logika sesuai dokumen: Menentukan warna berdasarkan OHLC dan deteksi Doji"""
    body = abs(c - o)
    total_range = h - l
    
    # 1. Deteksi Doji (Badan < 10% dari total range)
    is_doji = False
    if total_range > 0:
        is_doji = (body / total_range) < 0.10
    elif body == 0:
        is_doji = True

    # 2. Tentukan Warna Dasar
    if c > o:
        base_color = "Hijau"
    elif c < o:
        base_color = "Merah"
    else:
        # Open == Close (Doji murni) -> Lihat dominasi ekor
        upper_wick = h - max(o, c)
        lower_wick = min(o, c) - l
        base_color = "Hijau" if upper_wick >= lower_wick else "Merah"

    return f"Doji/{base_color}" if is_doji else base_color

# --- UPDATE: FUNGSI SIMPAN ANALISIS DENGAN OHLC ---
def save_analysis_db(market, tanggal, waktu, warna, o=0.0, h=0.0, l=0.0, c_pr=0.0, vol=0):
    conn = get_db_connection()
    if not conn: return
    cursor = conn.cursor()
    
    # Simpan ke market_histories dengan detail lengkap OHLCV
    sql = """INSERT INTO market_histories 
             (market, tanggal, waktu, warna, open_price, high_price, low_price, close_price, tick_volume, created_at, updated_at) 
             VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, NOW(), NOW())"""
    cursor.execute(sql, (market, tanggal, waktu, warna, o, h, l, c_pr, vol))

    # Update state (tetap gunakan warna dasar untuk data statistik dashboard)
    base_color = "Hijau" if "Hijau" in warna else "Merah"
    if base_color == "Hijau":
        cursor.execute("UPDATE market_states SET total_trade = total_trade + 1, total_hijau = total_hijau + 1 WHERE market = %s", (market,))
    else:
        cursor.execute("UPDATE market_states SET total_trade = total_trade + 1, total_merah = total_merah + 1 WHERE market = %s", (market,))
    
    conn.commit()
    cursor.close()
    conn.close()

def get_history_db(market, limit=100):
    conn = get_db_connection()
    if not conn: return []
    c = conn.cursor(dictionary=True)
    c.execute("SELECT market, tanggal, waktu, warna FROM market_histories WHERE market = %s ORDER BY id DESC LIMIT %s", (market, limit))
    res = c.fetchall()
    c.close()
    conn.close()
    return res

def save_trade_db(tanggal, waktu, market, warna, amount):
    conn = get_db_connection()
    if not conn: return
    c = conn.cursor()
    c.execute("INSERT INTO trade_histories (tanggal, waktu, market, warna, amount, created_at, updated_at) VALUES (%s, %s, %s, %s, %s, NOW(), NOW())",
            (tanggal, waktu, market, warna, amount))
    conn.commit()
    c.close()
    conn.close()

ASSET_MAPPING = {
    "Asia Composite Index": "ASIA_X", "Europe Composite Index": "EUROPE_X",
    "Commodity Composite": "CMDTY_X", "Crypto Composite Index": "CRYPTO_X",
    "EUR/USD OTC": "EURUSD_OTC", "GBP/USD OTC": "GBPUSD_OTC", "USD/JPY OTC": "USDJPY_OTC",
    "AUD/USD OTC": "AUDUSD_OTC", "NZD/USD OTC": "NZDUSD_OTC", "USD/CAD OTC": "USDCAD_OTC",
    "USD/CHF OTC": "USDCHF_OTC", "EUR/JPY OTC": "EURJPY_OTC", "GBP/JPY OTC": "GBPJPY_OTC",
    "AUD/JPY OTC": "AUDJPY_OTC", "CAD/JPY OTC": "CADJPY_OTC", "NZD/JPY OTC": "NZDJPY_OTC",
    "CHF/JPY OTC": "CHFJPY_OTC", "EUR/GBP OTC": "EURGBP_OTC", "EUR/AUD OTC": "EURAUD_OTC",
    "EUR/CAD OTC": "EURCAD_OTC", "EUR/CHF OTC": "EURCHF_OTC", "GBP/AUD OTC": "GBPAUD_OTC",
    "GBP/CAD OTC": "GBPCAD_OTC", "GBP/CHF OTC": "GBPCHF_OTC", "AUD/CAD OTC": "AUDCAD_OTC",
    "AUD/CHF OTC": "AUDCHF_OTC", "CAD/CHF OTC": "CADCHF_OTC",
}

# --- UPDATE: TELEGRAM DENGAN USER AGENT UNTUK VPS ---
def send_telegram_internal(message):
    def send_task():
        bot_token = "7863925068:AAFb8sDZFpBaczKXCtyh6SHwyQ693xejNQo"
        chat_id = "-5164724293"
        try:
            encoded_msg = urllib.parse.quote(message)
            url = f"https://api.telegram.org/bot{bot_token}/sendMessage?chat_id={chat_id}&text={encoded_msg}&parse_mode=Markdown"
            
            # Tambahkan Header User-Agent agar tidak dianggap bot ilegal oleh Cloudflare/Telegram
            req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'})
            urllib.request.urlopen(req, timeout=10)
        except Exception as e:
            print(f"❌ Gagal mengirim Telegram di VPS: {e}")

    threading.Thread(target=send_task, daemon=True).start()

def calc_sig_loss(history_list):
    sig_loss = 0
    blocks = {}
    for c in history_list:
        if c.get("waktu") and ":" in c["waktu"]:
            parts = c["waktu"].split(":")
            hh, mm = parts[0], int(parts[1])
            base_mm = (mm // 5) * 5
            key = f"{c['tanggal']}_{hh}:{base_mm:02d}"
            if key not in blocks: blocks[key] = {}
            if mm % 5 == 0: blocks[key]['c1'] = c['warna']
            if mm % 5 == 2: blocks[key]['c2'] = c['warna']

    # Diurutkan agar deteksi reset ke 0 dari candle terbaru berjalan akurat
    sorted_keys = sorted(blocks.keys(), reverse=True)
    for k in sorted_keys:
        b = blocks[k]
        if 'c1' in b and 'c2' in b:
            # Karena format warna sekarang bisa berisi Doji/Hijau, kita ambil base_color nya saja
            c1_base = "Hijau" if "Hijau" in b['c1'] else "Merah"
            c2_base = "Hijau" if "Hijau" in b['c2'] else "Merah"
            
            if c1_base != c2_base:
                sig_loss += 1
            else:
                break # Reset ke 0 jika mendeteksi ada 1 TRUE (Win)

    return sig_loss

async def fetch_accounts(token):
    accounts_info = []
    try:
        client = OlympTradeClient(access_token=token)
        await client.start()
        await asyncio.sleep(2)
        try:
            balance_data = await client.balance.get_balance()
            if isinstance(balance_data, dict) and 'd' in balance_data:
                for acc in balance_data['d']:
                    acc_id = str(acc.get('account_id') or acc.get('id'))
                    group = str(acc.get('group', 'unknown')).lower()
                    curr = str(acc.get('currency', 'unknown')).lower()
                    bal = float(acc.get('amount', 0))
                    is_demo = acc.get('is_demo', False)
                    tipe_akun = "Demo" if (is_demo or group == 'demo' or curr == 'demo') else "Real"
                    if not any(a['id'] == acc_id for a in accounts_info):
                        accounts_info.append({"id": acc_id, "type": tipe_akun, "balance": bal})
        except Exception: pass
        if hasattr(client, 'close'): await client.close()
        elif hasattr(client, 'disconnect'): await client.disconnect()
    except Exception: pass
    return accounts_info

# --- FUNGSI BARU: UPDATE PROFITABILITAS KE DATABASE ---
async def update_profitability_db(client, account_id):
    """Mengambil data profit dari API dan simpan ke MySQL"""
    try:
        if not account_id: return
        profits = await client.market.get_profitability(account_id)
        if profits:
            conn = get_db_connection()
            if not conn: return
            cursor = conn.cursor()
            for item in profits:
                pair = item.get('pair')
                payout = item.get('payout', 0)
                if pair and payout:
                    cursor.execute("""
                        INSERT INTO asset_profitabilities (market, payout, updated_at) 
                        VALUES (%s, %s, NOW()) 
                        ON DUPLICATE KEY UPDATE payout=%s, updated_at=NOW()
                    """, (pair, payout, payout))
            conn.commit()
            cursor.close()
            conn.close()
    except Exception as e:
        print(f"Error update profitability: {e}")

async def async_bot_task(market_name, token, user_account_id):
    global global_demo_balance
    actual_asset_id = ASSET_MAPPING.get(market_name, market_name)
    last_raw_candles = []

    try: target_account_id = int(str(user_account_id).strip()) if user_account_id else None
    except ValueError: target_account_id = None

    try:
        client = OlympTradeClient(access_token=token)
        original_dispatch = client._dispatch_message
        async def custom_dispatch(message):
            nonlocal last_raw_candles
            global global_demo_balance
            if isinstance(message, dict):
                msg_event = message.get('e')
                msg_data = message.get('d', [])
                if isinstance(msg_data, list):
                    for item in msg_data:
                        if isinstance(item, dict):
                            if 'amount' in item and ('id' in item or 'account_id' in item):
                                acc_id_loop = str(item.get('account_id') or item.get('id'))
                                if str(acc_id_loop) == str(target_account_id):
                                    global_demo_balance = float(item.get('amount', 0))
                            if msg_event == 10 and item.get('p') == actual_asset_id and 'candles' in item:
                                last_raw_candles = item.get('candles', [])
            if asyncio.iscoroutinefunction(original_dispatch): await original_dispatch(message)
            else: original_dispatch(message)

        client._dispatch_message = custom_dispatch
        await client.start()
        await asyncio.sleep(2)

    except Exception as e:
        print(f"Error start client {market_name}: {e}")
        return

    last_minute_checked = -1

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

        now = datetime.now()

        # EKSEKUSI MANUAL TRADE
        if market_name in markets_data and len(markets_data[market_name]["manual_queue"]) > 0:
            cmd = markets_data[market_name]["manual_queue"].pop(0)
            try:
                amount_int = int(float(cmd['amount']))
                duration_raw = int(cmd['duration'])
                direction_str = str(cmd['direction'])

                try: await client.trade.place_order(actual_asset_id, amount_int, direction_str, duration_raw, target_account_id)
                except TypeError: await client.trade.place_order(asset=actual_asset_id, amount=amount_int, dir=direction_str, duration=duration_raw, account_id=target_account_id)

                save_trade_db(now.strftime("%Y-%m-%d"), now.strftime("%H:%M:%S"), market_name, f"MANUAL {direction_str.upper()}", amount_int)
            except Exception as e:
                amt = locals().get('amount_int', 0)
                save_trade_db(now.strftime("%Y-%m-%d"), now.strftime("%H:%M:%S"), market_name, f"GAGAL: Script Error", amt)

        # TRIGGER UPDATE PROFITABILITAS PER 5 MENIT
        if now.minute % 5 == 0 and now.second < 2:
            try:
                await update_profitability_db(client, target_account_id)
            except Exception: pass

        # PING SERVER
        if now.second % 15 == 0 and now.microsecond < 500000:
            try: await client.send_message({"e": 98, "d": []})
            except Exception: pass

        # ANALISIS CANDLE PER 5 MENIT DENGAN TOLERANSI VPS LAG (2-15 Detik)
        if 2 <= now.second <= 15 and last_minute_checked != now.minute:
            prev_minute = (now.minute - 1) % 60

            if prev_minute % 5 == 0 or prev_minute % 5 == 2:
                waktu_laporan = f"{now.hour if now.minute != 0 else (now.hour - 1) % 24:02d}:{prev_minute:02d}"
                last_raw_candles = []
                try:
                    await client.market.get_candles(actual_asset_id, 60, 2)
                    await asyncio.sleep(1)
                except Exception:
                    pass

                if len(last_raw_candles) > 0:
                    last_minute_checked = now.minute
                    target_candle = last_raw_candles[1] if len(last_raw_candles) >= 2 else last_raw_candles[0]
                    
                    # --- EKSTRAKSI DATA OHLC & VOL BARU ---
                    o_pr = float(target_candle.get('open', 0))
                    h_pr = float(target_candle.get('high', 0))
                    l_pr = float(target_candle.get('low', 0))
                    c_pr = float(target_candle.get('close', 0))
                    vol  = int(target_candle.get('vol', 0))
                    
                    # --- TENTUKAN WARNA LOGIKA DOJI ---
                    warna_label = get_candle_color(o_pr, h_pr, l_pr, c_pr)
                    base_warna = "Hijau" if "Hijau" in warna_label else "Merah"

                    # Simpan data dengan lengkap
                    save_analysis_db(market_name, now.strftime("%Y-%m-%d"), waktu_laporan, warna_label, o_pr, h_pr, l_pr, c_pr, vol)

                    # LOGIKA TELEGRAM SERVER
                    if state['tg_active']:
                        hist = get_history_db(market_name, 100)
                        sig_loss = calc_sig_loss(hist)
                        mm = prev_minute
                        candle_id = f"{now.strftime('%Y-%m-%d')}_{waktu_laporan}"

                        if state["tg_last_candle"] != candle_id:
                            tg_phase = state['tg_phase']
                            tg_trade_counter = state['tg_trade_counter']
                            tg_direction = state['tg_direction']

                            if tg_phase == "IDLE" and (mm % 5 == 2):
                                if state["tg_target_loss"] > 0:
                                    expected_trades = sig_loss // state["tg_target_loss"]

                                    # LOGIKA PINTAR AUTO-RESET SIKLUS
                                    if expected_trades < tg_trade_counter:
                                        tg_trade_counter = expected_trades
                                        c.execute("UPDATE market_states SET tg_trade_counter=%s WHERE market=%s", (tg_trade_counter, market_name))

                                    if expected_trades > tg_trade_counter and sig_loss > 0:
                                        tg_trade_counter += 1
                                        tg_phase = "WAIT_CONF"
                                        next_min = f"{(mm + 3) % 60:02d}"
                                        msg = f"⚠️ *SERVER: PERSIAPAN OP* ⚠️\n\n📈 *Market:* {market_name}\n🗓 *Waktu:* {waktu_laporan} WIB\n\nTarget *FALSE ke-{sig_loss}* tercapai.\nStandby arah menit ke-{next_min}.\n"
                                        send_telegram_internal(msg)
                                        c.execute("UPDATE market_states SET tg_trade_counter=%s, tg_phase=%s, tg_last_candle=%s WHERE market=%s", (tg_trade_counter, tg_phase, candle_id, market_name))

                            elif tg_phase == "WAIT_CONF" and (mm % 5 == 0):
                                tg_phase = "WAIT_RES"
                                # Menggunakan base_warna untuk signal (Buy bila hijau, Sell bila merah)
                                tg_direction = "BUY 🟢" if base_warna == "Hijau" else "SELL 🔴"
                                next_min = f"{(mm + 2) % 60:02d}"
                                msg = f"🚀 *SERVER: SINYAL EKSEKUSI* 🚀\n\n📈 *Market:* {market_name}\n🗓 *Waktu:* {waktu_laporan} WIB\n\n🚨 Eksekusi Manual:\n👉 *{tg_direction}*\n🗓 *Hasil Menit {next_min}*\n"
                                send_telegram_internal(msg)
                                c.execute("UPDATE market_states SET tg_phase=%s, tg_direction=%s, tg_last_candle=%s WHERE market=%s", (tg_phase, tg_direction, candle_id, market_name))

                            elif tg_phase == "WAIT_RES" and (mm % 5 == 2):
                                tg_phase = "IDLE"
                                required_color = "Hijau" if "BUY" in tg_direction else "Merah"
                                is_win = (base_warna == required_color)
                                status_emoji = "✅" if is_win else "❌"
                                hasil_teks = "TRUE" if is_win else "FALSE"
                                msg = f"{status_emoji} *SERVER: HASIL TRADE* {status_emoji}\n\n📈 *Market:* {market_name}\nArah Tadi: *{tg_direction}*\nCandle Hasil: *{warna_label.upper()}*\nHasil Akhir: *{hasil_teks}*\n"
                                send_telegram_internal(msg)
                                c.execute("UPDATE market_states SET tg_phase=%s, tg_last_candle=%s WHERE market=%s", (tg_phase, candle_id, market_name))
                    conn.commit()
            else:
                # Kunci menit agar loop toleransi tidak menarik data berulang kali di detik 2-15
                last_minute_checked = now.minute

        c.close()
        conn.close()
        await asyncio.sleep(0.5)

def run_trading_bot_thread(market_name, token, account_id):
    loop = asyncio.new_event_loop()
    asyncio.set_event_loop(loop)
    loop.run_until_complete(async_bot_task(market_name, token, account_id))


# ==========================================
# ENDPOINT FLASK API
# ==========================================

@app.route('/api/get_settings', methods=['GET'])
def api_get_settings():
    return jsonify(get_settings())

@app.route('/api/check_accounts', methods=['POST', 'OPTIONS'])
def api_check_accounts():
    if request.method == 'OPTIONS':
        return jsonify({}), 200
    token = request.json.get('token')
    if not token: return jsonify({"status": "error", "message": "Harap masukkan Access Token!"})
    try:
        accounts = asyncio.run(fetch_accounts(token))
        if accounts: return jsonify({"status": "success", "accounts": accounts})
        else: return jsonify({"status": "error", "message": "Gagal menarik data. Token salah / expired."})
    except Exception as e: return jsonify({"status": "error", "message": "Koneksi terputus dari server."})

@app.route('/api/start', methods=['POST'])
def start_bot():
    data = request.json
    market = data.get('market')
    token = data.get('token')
    account_id = data.get('account_id')
    save_settings(token, account_id)

    if market not in markets_data: markets_data[market] = {"manual_queue": []}

    init_market_state(market)
    threading.Thread(target=run_trading_bot_thread, args=(market, token, account_id), daemon=True).start()
    return jsonify({"status": "success", "message": f"Koneksi {market} berhasil dibuka!"})

@app.route('/api/start_all', methods=['POST'])
def start_all():
    data = request.json
    token = data.get('token')
    account_id = data.get('account_id')
    save_settings(token, account_id)

    started = 0
    for market_name in ASSET_MAPPING.keys():
        if market_name not in markets_data: markets_data[market_name] = {"manual_queue": []}
        init_market_state(market_name)
        threading.Thread(target=run_trading_bot_thread, args=(market_name, token, account_id), daemon=True).start()
        started += 1
    return jsonify({"status": "success", "message": f"Berhasil menghidupkan {started} market!"})

@app.route('/api/stop', methods=['POST'])
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
    return jsonify({"status": "success", "message": "Semua bot market berhasil dihentikan!"})

@app.route('/api/reset_market', methods=['POST'])
def reset_market():
    market = request.json.get('market')
    conn = get_db_connection()
    c = conn.cursor()
    c.execute("DELETE FROM market_histories WHERE market = %s", (market,))
    c.execute("UPDATE market_states SET total_trade=0, total_hijau=0, total_merah=0, tg_trade_counter=0, tg_phase='IDLE' WHERE market = %s", (market,))
    conn.commit()
    conn.close()
    return jsonify({"status": "success", "message": f"Data {market} berhasil direset."})

@app.route('/api/reset_all', methods=['POST'])
def reset_all():
    conn = get_db_connection()
    c = conn.cursor()
    c.execute("TRUNCATE TABLE market_histories")
    c.execute("UPDATE market_states SET total_trade=0, total_hijau=0, total_merah=0, tg_trade_counter=0, tg_phase='IDLE'")
    conn.commit()
    conn.close()
    return jsonify({"status": "success", "message": "Semua data berhasil direset!"})

@app.route('/api/toggle_telegram', methods=['POST'])
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
    return jsonify({"status": "success", "message": "Sinyal Telegram di SEMUA market berhasil dimatikan!"})

@app.route('/api/manual_trade', methods=['POST'])
def manual_trade():
    data = request.json
    market = data.get('market')
    if market in markets_data:
        markets_data[market]['manual_queue'].append({
            "direction": data.get('direction'), "amount": data.get('amount', 1), "duration": data.get('duration', 60)
        })
        return jsonify({"status": "success", "message": f"Sinyal dikirim!"})
    return jsonify({"status": "error", "message": "Bot belum jalan!"})

@app.route('/api/data', methods=['GET'])
def get_data():
    market = request.args.get('market')
    conn = get_db_connection()
    c = conn.cursor(dictionary=True)
    c.execute("SELECT * FROM market_states WHERE market = %s", (market,))
    state = c.fetchone()

    if state:
        histories = get_history_db(market, 500)
        conn.close()
        return jsonify({
            "is_running": bool(state['is_running']),
            "stats": {"total_trade": state['total_trade'], "total_hijau": state['total_hijau'], "total_merah": state['total_merah']},
            "history": histories,
            "telegram": {"active": bool(state['tg_active']), "target_loss": state['tg_target_loss'], "trade_counter": state['tg_trade_counter']},
            "balance": global_demo_balance
        })
    conn.close()
    return jsonify({"is_running": False, "stats": {"total_trade": 0, "total_hijau": 0, "total_merah": 0}, "history": [], "balance": global_demo_balance})

@app.route('/api/status_all', methods=['GET'])
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
    })

@app.route('/api/trade_history', methods=['GET'])
def trade_history():
    conn = get_db_connection()
    if conn:
        c = conn.cursor(dictionary=True)
        c.execute("SELECT tanggal, waktu, market, warna, amount FROM trade_histories ORDER BY id DESC LIMIT 500")
        results = c.fetchall()
        conn.close()
        return jsonify({"trade_history": results})
    return jsonify({"trade_history": []})

@app.route('/api/send_wa', methods=['POST'])
def send_telegram():
    data = request.json
    send_telegram_internal(data.get('message', ''))
    return jsonify({"status": "success"})

if __name__ == '__main__':
    app.run(debug=True, port=5000, host='0.0.0.0')
