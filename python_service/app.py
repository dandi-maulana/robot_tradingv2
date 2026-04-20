from flask import Flask, request, jsonify
from flask_cors import CORS
import threading
import time
from datetime import datetime, timedelta
import asyncio
import mysql.connector
from mysql.connector import pooling
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

# --- KONTROL START_ALL (agar STOP tidak auto-start lagi) ---
_start_all_lock = threading.Lock()
_start_all_job_id = 0


def _bump_start_all_job_id():
    global _start_all_job_id
    with _start_all_lock:
        _start_all_job_id += 1
        return _start_all_job_id


def _get_start_all_job_id():
    with _start_all_lock:
        return _start_all_job_id


# --- KONFIGURASI MYSQL ---
# DB_CONFIG = {
#     "host": "localhost",
#     "user": "root",
#     "password": "",
#     "database": "robot_trading5",
# }
DB_CONFIG = {
    "host": "localhost",
    "user": "rodis_admin",
    "password": "@Nightmare02",
    "database": "robot_trading",
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
    if not conn:
        return
    c = conn.cursor()
    c.execute("SELECT id FROM settings WHERE id = 1")
    if c.fetchone():
        c.execute(
            "UPDATE settings SET token = %s, account_id = %s, updated_at = NOW() WHERE id = 1",
            (token, account_id),
        )
    else:
        c.execute(
            "INSERT INTO settings (id, token, account_id, created_at, updated_at) VALUES (1, %s, %s, NOW(), NOW())",
            (token, account_id),
        )
    conn.commit()
    c.close()
    conn.close()


def get_settings():
    conn = get_db_connection()
    if not conn:
        return {"token": "", "account_id": ""}
    c = conn.cursor()
    c.execute("SELECT token, account_id FROM settings WHERE id = 1")
    res = c.fetchone()
    c.close()
    conn.close()
    return (
        {"token": res[0], "account_id": res[1]}
        if res
        else {"token": "", "account_id": ""}
    )


def init_market_state(market_name):
    if market_name not in markets_data:
        markets_data[market_name] = {"manual_queue": []}
    conn = get_db_connection()
    if not conn:
        return
    c = conn.cursor(dictionary=True)
    c.execute(
        "SELECT is_running, tg_active, tg_target_loss, tg_phase, tg_trade_counter, tg_last_candle, tg_direction FROM market_states WHERE market = %s",
        (market_name,),
    )
    row = c.fetchone()
    c.close()

    if not row:
        c = conn.cursor()
        c.execute(
            "INSERT INTO market_states (market, is_running, created_at, updated_at) VALUES (%s, 1, NOW(), NOW())",
            (market_name,),
        )
        conn.commit()
        c.close()
        row = {
            "is_running": 1,
            "tg_active": 0,
            "tg_target_loss": 7,
            "tg_phase": "IDLE",
            "tg_trade_counter": 0,
            "tg_last_candle": "",
            "tg_direction": "",
        }
    else:
        c = conn.cursor()
        c.execute(
            "UPDATE market_states SET is_running = 1, updated_at = NOW() WHERE market = %s",
            (market_name,),
        )
        conn.commit()
        c.close()
        row["is_running"] = 1

    markets_data[market_name].update(
        {
            "is_running": row.get("is_running", 1),
            "tg_active": row.get("tg_active", 0),
            "tg_target_loss": row.get("tg_target_loss", 7),
            "tg_phase": row.get("tg_phase", "IDLE"),
            "tg_trade_counter": row.get("tg_trade_counter", 0),
            "tg_last_candle": row.get("tg_last_candle", ""),
            "tg_direction": row.get("tg_direction", ""),
            "tg_notif_sent_for_level": -1,  # Track level berapa notif sudah dikirim (-1 = belum)
        }
    )
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
def save_analysis_db(
    market, tanggal, waktu, warna, o=0.0, h=0.0, l=0.0, c_pr=0.0, vol=0
):
    conn = get_db_connection()
    if not conn:
        return
    cursor = conn.cursor()

    # Cek apakah data di menit ini sudah ada (Mencegah Duplicate Insert dari VPS / Multi-Worker)
    cursor.execute(
        "SELECT id FROM market_histories WHERE market=%s AND tanggal=%s AND waktu=%s",
        (market, tanggal, waktu),
    )
    if cursor.fetchone():
        cursor.close()
        conn.close()
        return

    # Simpan ke market_histories dengan detail lengkap OHLCV
    sql = """INSERT INTO market_histories
             (market, tanggal, waktu, warna, open_price, high_price, low_price, close_price, tick_volume, created_at, updated_at)
             VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, NOW(), NOW())"""
    cursor.execute(sql, (market, tanggal, waktu, warna, o, h, l, c_pr, vol))

    # Update state (tetap gunakan warna dasar untuk data statistik dashboard)
    base_color = "Hijau" if "Hijau" in warna else "Merah"
    if base_color == "Hijau":
        cursor.execute(
            "UPDATE market_states SET total_trade = total_trade + 1, total_hijau = total_hijau + 1 WHERE market = %s",
            (market,),
        )
    else:
        cursor.execute(
            "UPDATE market_states SET total_trade = total_trade + 1, total_merah = total_merah + 1 WHERE market = %s",
            (market,),
        )

    conn.commit()
    cursor.close()

    # ✅ SETELAH INSERT SELESAI: Recalculate open_positions_today dan update ke DB
    update_open_positions_to_db(market, tanggal)
    sync_phase_histories_to_db(market)

    conn.close()


def build_phase_outcomes(candles):
    blocks = {}

    for candle in candles:
        tanggal = str(candle.get("tanggal") or "")
        waktu = str(candle.get("waktu") or "")
        warna = str(candle.get("warna") or "")

        if not tanggal or not waktu or ":" not in waktu:
            continue

        parts = waktu.split(":")
        if len(parts) < 2:
            continue

        try:
            hh = int(parts[0])
            mm = int(parts[1])
        except ValueError:
            continue

        if mm < 0 or mm > 59:
            continue

        hour = f"{hh:02d}"
        base_mm = (mm // 5) * 5
        key = f"{tanggal}_{hour}:{base_mm:02d}"

        if key not in blocks:
            blocks[key] = {
                "tanggal": tanggal,
                "waktu": f"{hour}:{base_mm:02d}",
                "candles": {},
            }

        offset = mm % 5
        base_color = "Hijau" if "Hijau" in warna else "Merah"
        blocks[key]["candles"][f"c{offset}"] = base_color

    outcomes = []
    for key in sorted(blocks.keys()):
        block = blocks[key]
        c = block["candles"]
        if "c0" not in c or "c2" not in c or "c3" not in c or "c4" not in c:
            continue

        c0 = c["c0"]
        is_true = c["c2"] == c0 or c["c3"] == c0 or c["c4"] == c0
        outcomes.append(
            {
                "tanggal": block["tanggal"],
                "waktu": block["waktu"],
                "datetime": f"{block['tanggal']} {block['waktu']}",
                "result": "TRUE" if is_true else "FALSE",
            }
        )

    return outcomes


def build_phase_history_rows(market, candles, target_loss):
    outcomes = build_phase_outcomes(candles)
    if not outcomes:
        return []

    start_phase = target_loss + 1
    if start_phase > 7:
        return []

    rows = []
    consecutive_false = 0
    sequence_locked = False
    active_signal = None

    for outcome in outcomes:
        result = outcome["result"]

        if active_signal is not None:
            phase = active_signal["next_phase"]
            active_signal[f"phase_{phase}"] = result

            if result == "TRUE":
                for fill in range(phase + 1, 8):
                    active_signal[f"phase_{fill}"] = "-"

                active_signal["resolved_result"] = "TRUE"
                active_signal["resolved_phase"] = phase
                active_signal["resolved_at"] = outcome["datetime"]
                del active_signal["next_phase"]

                rows.append(active_signal)
                active_signal = None
                consecutive_false = 0
                sequence_locked = False
            else:
                consecutive_false += 1

                if phase >= 7:
                    active_signal["resolved_result"] = "FALSE"
                    active_signal["resolved_phase"] = 7
                    active_signal["resolved_at"] = outcome["datetime"]
                    del active_signal["next_phase"]

                    rows.append(active_signal)
                    active_signal = None
                    sequence_locked = True
                else:
                    active_signal["next_phase"] = phase + 1

            continue

        if result == "TRUE":
            consecutive_false = 0
            sequence_locked = False
            continue

        consecutive_false += 1

        if sequence_locked:
            continue

        if consecutive_false != target_loss:
            continue

        active_signal = {
            "tanggal": outcome["tanggal"],
            "waktu": outcome["waktu"],
            "ticker": market,
            "phase_1": "-",
            "phase_2": "-",
            "phase_3": "-",
            "phase_4": "-",
            "phase_5": "-",
            "phase_6": "-",
            "phase_7": "-",
            "next_phase": start_phase,
            "resolved_result": "PENDING",
            "resolved_phase": None,
            "resolved_at": None,
            "trigger_at": outcome["datetime"],
            "target_loss": target_loss,
        }

    if active_signal is not None:
        del active_signal["next_phase"]
        rows.append(active_signal)

    return rows


def sync_phase_histories_to_db(market):
    try:
        conn = get_db_connection()
        if not conn:
            return

        cursor = conn.cursor(dictionary=True)
        cursor.execute(
            """
            SELECT tanggal, waktu, warna
            FROM market_histories
            WHERE market = %s
            ORDER BY tanggal ASC, waktu ASC, id ASC
        """,
            (market,),
        )
        candles = cursor.fetchall() or []

        write_cursor = conn.cursor()

        for target_loss in range(1, 7):
            rows = build_phase_history_rows(market, candles, target_loss)
            if not rows:
                continue

            payload = []
            for row in rows:
                payload.append(
                    (
                        market,
                        target_loss,
                        row.get("tanggal", ""),
                        row.get("waktu", ""),
                        row.get("phase_1", "-"),
                        row.get("phase_2", "-"),
                        row.get("phase_3", "-"),
                        row.get("phase_4", "-"),
                        row.get("phase_5", "-"),
                        row.get("phase_6", "-"),
                        row.get("phase_7", "-"),
                        row.get("resolved_result", "PENDING"),
                        row.get("resolved_phase"),
                        row.get("trigger_at", ""),
                        row.get("resolved_at"),
                    )
                )

            write_cursor.executemany(
                """
                INSERT INTO phase_histories
                (market, target_loss, tanggal, waktu, phase_1, phase_2, phase_3, phase_4, phase_5, phase_6, phase_7, resolved_result, resolved_phase, trigger_at, resolved_at, created_at, updated_at)
                VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NOW(), NOW())
                ON DUPLICATE KEY UPDATE
                    tanggal = VALUES(tanggal),
                    waktu = VALUES(waktu),
                    phase_1 = VALUES(phase_1),
                    phase_2 = VALUES(phase_2),
                    phase_3 = VALUES(phase_3),
                    phase_4 = VALUES(phase_4),
                    phase_5 = VALUES(phase_5),
                    phase_6 = VALUES(phase_6),
                    phase_7 = VALUES(phase_7),
                    resolved_result = VALUES(resolved_result),
                    resolved_phase = VALUES(resolved_phase),
                    resolved_at = VALUES(resolved_at),
                    updated_at = NOW()
            """,
                payload,
            )

        conn.commit()
        write_cursor.close()
        cursor.close()
        conn.close()
    except Exception as e:
        print(f"[DB] Error syncing phase histories for {market}: {str(e)}")


def update_open_positions_to_db(market, tanggal):
    """
    Recalculate open_positions untuk market hari ini dan simpan ke DB
    Dipanggil setiap kali ada candle baru masuk
    """
    try:
        conn = get_db_connection()
        if not conn:
            return

        cursor = conn.cursor(dictionary=True)

        # 1. Cek apakah perlu reset (ganti tanggal)?
        cursor.execute(
            "SELECT last_positions_reset_date FROM market_states WHERE market = %s",
            (market,),
        )
        state_row = cursor.fetchone()

        if state_row:
            last_reset_date = state_row.get("last_positions_reset_date")
            if last_reset_date and str(last_reset_date) != tanggal:
                # Tanggal berubah → Reset counter
                print(
                    f"[DB] {market}: Reset open_positions (tanggal berubah dari {last_reset_date} ke {tanggal})"
                )
                cursor.execute(
                    "UPDATE market_states SET open_positions_today = 0, last_positions_reset_date = %s WHERE market = %s",
                    (tanggal, market),
                )
                conn.commit()

        # 2. Ambil semua candles hari ini
        cursor.execute(
            """
            SELECT tanggal, waktu, warna FROM market_histories 
            WHERE market = %s AND tanggal = %s 
            ORDER BY waktu ASC
        """,
            (market, tanggal),
        )
        today_candles = cursor.fetchall()

        # 3. Recalculate open_positions
        open_positions = (
            count_open_positions_today(today_candles) if today_candles else 0
        )

        # 4. Update DB
        cursor.execute(
            "UPDATE market_states SET open_positions_today = %s, last_positions_reset_date = %s WHERE market = %s",
            (open_positions, tanggal, market),
        )
        conn.commit()

        print(
            f"[DB] {market} ({tanggal}): Updated open_positions_today = {open_positions}"
        )

        cursor.close()
        conn.close()
    except Exception as e:
        print(f"[DB] Error updating open_positions for {market}: {str(e)}")


def get_history_db(market, limit=100):
    conn = get_db_connection()
    if not conn:
        return []
    c = conn.cursor(dictionary=True)
    c.execute(
        "SELECT market, tanggal, waktu, warna FROM market_histories WHERE market = %s ORDER BY id DESC LIMIT %s",
        (market, limit),
    )
    res = c.fetchall()
    c.close()
    conn.close()
    return res


def save_trade_db(tanggal, waktu, market, warna, amount):
    conn = get_db_connection()
    if not conn:
        return
    c = conn.cursor()
    c.execute(
        "INSERT INTO trade_histories (tanggal, waktu, market, warna, amount, created_at, updated_at) VALUES (%s, %s, %s, %s, %s, NOW(), NOW())",
        (tanggal, waktu, market, warna, amount),
    )
    conn.commit()
    c.close()
    conn.close()


ASSET_MAPPING = {
    "Asia Composite Index": "ASIA_X",
    "Europe Composite Index": "EUROPE_X",
    "Commodity Composite": "CMDTY_X",
    "Crypto Composite Index": "CRYPTO_X",
    "EUR/USD OTC": "EURUSD_OTC",
    "GBP/USD OTC": "GBPUSD_OTC",
    "USD/JPY OTC": "USDJPY_OTC",
    "AUD/USD OTC": "AUDUSD_OTC",
    "NZD/USD OTC": "NZDUSD_OTC",
    "USD/CAD OTC": "USDCAD_OTC",
    "USD/CHF OTC": "USDCHF_OTC",
    "EUR/JPY OTC": "EURJPY_OTC",
    "GBP/JPY OTC": "GBPJPY_OTC",
    "AUD/JPY OTC": "AUDJPY_OTC",
    "CAD/JPY OTC": "CADJPY_OTC",
    "NZD/JPY OTC": "NZDJPY_OTC",
    "CHF/JPY OTC": "CHFJPY_OTC",
    "EUR/GBP OTC": "EURGBP_OTC",
    "EUR/AUD OTC": "EURAUD_OTC",
    "EUR/CAD OTC": "EURCAD_OTC",
    "EUR/CHF OTC": "EURCHF_OTC",
    "GBP/AUD OTC": "GBPAUD_OTC",
    "GBP/CAD OTC": "GBPCAD_OTC",
    "GBP/CHF OTC": "GBPCHF_OTC",
    "AUD/CAD OTC": "AUDCAD_OTC",
    "AUD/CHF OTC": "AUDCHF_OTC",
    "CAD/CHF OTC": "CADCHF_OTC",
    "Bitcoin OTC": "BTCUSD_OTC",
    "Ethereum OTC": "ETHUSD_OTC",
    "Ripple OTC": "XRPUSD_OTC",
    "Litecoin OTC": "LTCUSD_OTC",
    "Solana OTC": "SOLUSD_OTC",
    "Stablecoin Composite": "STABLE_X",
    "Halal Index": "NHI_X",
    "Silver OTC": "XAGUSD_OTC",
    "Gold OTC": "XAUUSD_OTC",
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
            req = urllib.request.Request(
                url, headers={"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64)"}
            )
            urllib.request.urlopen(req, timeout=10)
        except Exception as e:
            print(f"❌ Gagal mengirim Telegram di VPS: {e}")

    threading.Thread(target=send_task, daemon=True).start()


# --- USER2: TELEGRAM KE GRUP RODIS NOTIFIKASI ---
def send_telegram_user2(message):
    """Kirim notifikasi ke grup RODIS NOTIFIKASI (khusus User2)"""

    def send_task():
        bot_token = "8762488972:AAHzdICqLME-9MuMh1aZevOpc0TyNHceES8"
        chat_id = "-1003801360218"  # Grup: RODIS NOTIFIKASI
        try:
            encoded_msg = urllib.parse.quote(message)
            url = f"https://api.telegram.org/bot{bot_token}/sendMessage?chat_id={chat_id}&text={encoded_msg}&parse_mode=Markdown"
            req = urllib.request.Request(
                url, headers={"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64)"}
            )
            urllib.request.urlopen(req, timeout=10)
            print(f"✅ [USER2] Notif Telegram terkirim ke RODIS NOTIFIKASI")
        except Exception as e:
            print(f"❌ [USER2] Gagal mengirim Telegram: {e}")

    threading.Thread(target=send_task, daemon=True).start()


# --- USER2: LOGIKA DETEKSI POLA C1-C8 ---
def check_user2_pattern(market, tanggal, waktu_block):
    """
    Mengecek pola C1-C8 untuk sistem User2.

    Pola UP   : C1=Merah, C2=Hijau, C3=Merah, C4=Merah, C5=Merah, C6=Merah, C7=Merah, C8=Merah
    Pola DOWN : C1=Hijau, C2=Merah, C3=Hijau, C4=Hijau, C5=Hijau, C6=Hijau, C7=Hijau, C8=Hijau

    Notif Telegram dikirim saat C3 selesai (menit ke-02) jika C1,C2,C3 cocok.
    """
    try:
        conn = get_db_connection()
        if not conn:
            return

        cursor = conn.cursor(dictionary=True)

        # Ambil candle menit pada blok ini dari market_histories
        # waktu_block = "HH:MM" contoh 10:00 → ambil candle 10:00-10:07
        parts = waktu_block.split(":")
        hh = int(parts[0])
        base_mm = int(parts[1])  # menit pertama blok (xx:00, xx:05, ...)

        # Mapping:
        #   c1 = base_mm + 0, c2 = base_mm + 1, ... c8 = base_mm + 7
        # CATATAN: Data disimpan sebagai menit aktual (00-59)
        candle_times = []
        for offset in range(8):  # c1=offset 0 s/d c8=offset 7
            mm = base_mm + offset
            if mm > 59:
                actual_hh = (hh + 1) % 24
                actual_mm = mm - 60
            else:
                actual_hh = hh
                actual_mm = mm
            candle_times.append(f"{actual_hh:02d}:{actual_mm:02d}")

        # Ambil semua candle di blok ini
        placeholders = ",".join(["%s"] * len(candle_times))
        cursor.execute(
            f"""
            SELECT waktu, warna FROM market_histories
            WHERE market = %s AND tanggal = %s AND waktu IN ({placeholders})
            ORDER BY waktu ASC
        """,
            [market, tanggal] + candle_times,
        )
        rows = cursor.fetchall()

        # Map waktu ke warna dasar
        candle_map = {}
        for row in rows:
            base_color = "Hijau" if "Hijau" in str(row["warna"]) else "Merah"
            candle_map[row["waktu"]] = base_color

        # Ambil warna C1 s/d C8
        c1 = candle_map.get(candle_times[0])
        c2 = candle_map.get(candle_times[1])
        c3 = candle_map.get(candle_times[2])
        c4 = candle_map.get(candle_times[3])
        c5 = candle_map.get(candle_times[4])
        c6 = candle_map.get(candle_times[5])
        c7 = candle_map.get(candle_times[6])
        c8 = candle_map.get(candle_times[7])

        # Tentukan tipe pola berdasarkan semua data yang tersedia
        pattern_type = "NONE"
        if c1 and c2 and c3:
            # Cek pola UP: C1=M, C2=H, C3+=M
            if c1 == "Merah" and c2 == "Hijau" and c3 == "Merah":
                # Validasi progresif: cek candle lanjutan jika sudah tersedia
                extra_candles = [c4, c5, c6, c7, c8]
                available = [c for c in extra_candles if c is not None]
                if len(available) > 0:
                    # Semua candle lanjutan yang tersedia harus Merah
                    pattern_type = (
                        "UP" if all(c == "Merah" for c in available) else "NONE"
                    )
                else:
                    pattern_type = "UP"  # Sementara cocok (c4-c8 belum ada)
            # Cek pola DOWN: C1=H, C2=M, C3+=H
            elif c1 == "Hijau" and c2 == "Merah" and c3 == "Hijau":
                extra_candles = [c4, c5, c6, c7, c8]
                available = [c for c in extra_candles if c is not None]
                if len(available) > 0:
                    pattern_type = (
                        "DOWN" if all(c == "Hijau" for c in available) else "NONE"
                    )
                else:
                    pattern_type = "DOWN"  # Sementara cocok

        # Cek apakah notif sudah dikirim untuk blok ini
        cursor.execute(
            """
            SELECT id, notif_sent, pattern_type FROM user2_patterns
            WHERE market = %s AND tanggal = %s AND waktu_block = %s
        """,
            (market, tanggal, waktu_block),
        )
        existing = cursor.fetchone()

        write_cursor = conn.cursor()

        if existing:
            # Update data candle
            write_cursor.execute(
                """
                UPDATE user2_patterns
                SET c1=%s, c2=%s, c3=%s, c4=%s, c5=%s, c6=%s, c7=%s, c8=%s, pattern_type=%s, updated_at=NOW()
                WHERE market=%s AND tanggal=%s AND waktu_block=%s
            """,
                (
                    c1,
                    c2,
                    c3,
                    c4,
                    c5,
                    c6,
                    c7,
                    c8,
                    pattern_type,
                    market,
                    tanggal,
                    waktu_block,
                ),
            )
            notif_already_sent = existing["notif_sent"]
        else:
            # Insert baru
            write_cursor.execute(
                """
                INSERT INTO user2_patterns
                (market, tanggal, waktu_block, c1, c2, c3, c4, c5, c6, c7, c8, pattern_type, notif_sent, created_at, updated_at)
                VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,0,NOW(),NOW())
            """,
                (
                    market,
                    tanggal,
                    waktu_block,
                    c1,
                    c2,
                    c3,
                    c4,
                    c5,
                    c6,
                    c7,
                    c8,
                    pattern_type,
                ),
            )
            notif_already_sent = False

        conn.commit()

        # Kirim notif Telegram saat C3 sudah ada, pola cocok, dan notif belum pernah dikirim
        if c3 and pattern_type in ("UP", "DOWN") and not notif_already_sent:
            arah = "📈 UP (BELI)" if pattern_type == "UP" else "📉 DOWN (JUAL)"
            emoji = "🟢" if pattern_type == "UP" else "🔴"
            msg = f"""🚨 *SINYAL POLA TERDETEKSI* 🚨

{emoji} Arah   : *{arah}*
📊 Market : *{market}*
🗓 Blok   : {waktu_block} WIB

Pola Candle:
  C1: {'🟢 Hijau' if c1=='Hijau' else '🔴 Merah'}
  C2: {'🟢 Hijau' if c2=='Hijau' else '🔴 Merah'}
  C3: {'🟢 Hijau' if c3=='Hijau' else '🔴 Merah'}

⚡ Siap eksekusi pada C4-C8!"""

            send_telegram_user2(msg)

            # Tandai notif sudah dikirim
            write_cursor.execute(
                """
                UPDATE user2_patterns SET notif_sent=1
                WHERE market=%s AND tanggal=%s AND waktu_block=%s
            """,
                (market, tanggal, waktu_block),
            )
            conn.commit()
            print(
                f"[USER2] ✅ Notif terkirim: {market} | {waktu_block} | {pattern_type}"
            )

        write_cursor.close()
        cursor.close()
        conn.close()

    except Exception as e:
        print(f"[USER2] ❌ Error check_user2_pattern {market}: {e}")


# --- DIUBAH: LOGIKA FALSE/TRUE BLOK 5 MENIT ---
def calc_sig_loss(history_list):
    sig_loss = 0
    blocks = {}
    for c in history_list:
        if c.get("waktu") and ":" in c["waktu"]:
            parts = c["waktu"].split(":")
            hh, mm = parts[0], int(parts[1])
            base_mm = (mm // 5) * 5
            key = f"{c['tanggal']}_{hh}:{base_mm:02d}"

            if key not in blocks:
                blocks[key] = {}
            offset = mm % 5
            base_color = "Hijau" if "Hijau" in c["warna"] else "Merah"
            blocks[key][f"c{offset}"] = base_color

    # Diurutkan agar deteksi reset ke 0 dari candle terbaru berjalan akurat
    sorted_keys = sorted(blocks.keys(), reverse=True)
    for k in sorted_keys:
        b = blocks[k]
        if "c0" in b:
            c0 = b["c0"]

            # Kondisi TRUE: salah satu dari c2, c3, atau c4 SAMA dengan c0
            is_true = False
            if "c2" in b and b["c2"] == c0:
                is_true = True
            if "c3" in b and b["c3"] == c0:
                is_true = True
            if "c4" in b and b["c4"] == c0:
                is_true = True

            if is_true:
                break  # Reset ke 0 jika mendeteksi ada 1 TRUE (Win)

            # Kondisi FALSE: Siklus lengkap (0,2,3,4) tapi tidak ada yang sama
            elif "c2" in b and "c3" in b and "c4" in b:
                sig_loss += 1

    return sig_loss


async def fetch_accounts(token):
    accounts_info = []
    try:
        client = OlympTradeClient(access_token=token)
        await client.start()
        await asyncio.sleep(2)
        try:
            balance_data = await client.balance.get_balance()
            if isinstance(balance_data, dict) and "d" in balance_data:
                for acc in balance_data["d"]:
                    acc_id = str(acc.get("account_id") or acc.get("id"))
                    group = str(acc.get("group", "unknown")).lower()
                    curr = str(acc.get("currency", "unknown")).lower()
                    bal = float(acc.get("amount", 0))
                    is_demo = acc.get("is_demo", False)
                    tipe_akun = (
                        "Demo"
                        if (is_demo or group == "demo" or curr == "demo")
                        else "Real"
                    )
                    if not any(a["id"] == acc_id for a in accounts_info):
                        accounts_info.append(
                            {"id": acc_id, "type": tipe_akun, "balance": bal}
                        )
        except Exception:
            pass
        if hasattr(client, "stop"):
            await client.stop()
        elif hasattr(client, "close"):
            await client.close()
        elif hasattr(client, "disconnect"):
            await client.disconnect()
    except Exception:
        pass
    return accounts_info


# --- FUNGSI BARU: UPDATE PROFITABILITAS KE DATABASE ---
async def update_profitability_db(client, account_id):
    """Mengambil data profit dari API dan simpan ke MySQL"""
    try:
        if not account_id:
            return
        profits = await client.market.get_profitability(account_id)
        if profits:
            conn = get_db_connection()
            if not conn:
                return
            cursor = conn.cursor()
            for item in profits:
                pair = item.get("pair")
                payout = item.get("payout", 0)
                if pair and payout:
                    cursor.execute(
                        """
                        INSERT INTO asset_profitabilities (market, payout, updated_at)
                        VALUES (%s, %s, NOW())
                        ON DUPLICATE KEY UPDATE payout=%s, updated_at=NOW()
                    """,
                        (pair, payout, payout),
                    )
            conn.commit()
            cursor.close()
            conn.close()
    except Exception as e:
        print(f"Error update profitability: {e}")


async def async_bot_task(market_name, token, user_account_id):
    global global_demo_balance
    actual_asset_id = ASSET_MAPPING.get(market_name, market_name)
    last_raw_candles = []

    try:
        target_account_id = (
            int(str(user_account_id).strip()) if user_account_id else None
        )
    except ValueError:
        target_account_id = None

    try:
        client = OlympTradeClient(access_token=token)
        original_dispatch = client._dispatch_message

        async def custom_dispatch(message):
            nonlocal last_raw_candles
            global global_demo_balance
            if isinstance(message, dict):
                msg_event = message.get("e")
                msg_data = message.get("d", [])
                if isinstance(msg_data, list):
                    for item in msg_data:
                        if isinstance(item, dict):
                            if "amount" in item and (
                                "id" in item or "account_id" in item
                            ):
                                acc_id_loop = str(
                                    item.get("account_id") or item.get("id")
                                )
                                if str(acc_id_loop) == str(target_account_id):
                                    global_demo_balance = float(item.get("amount", 0))
                            if (
                                msg_event == 10
                                and item.get("p") == actual_asset_id
                                and "candles" in item
                            ):
                                last_raw_candles = item.get("candles", [])
            if asyncio.iscoroutinefunction(original_dispatch):
                await original_dispatch(message)
            else:
                original_dispatch(message)

        client._dispatch_message = custom_dispatch
        await client.start()
        await asyncio.sleep(2)

    except Exception as e:
        print(f"Error start client {market_name}: {e}")
        return

    last_minute_checked = -1

    # Logika Lama
    # while True:
    #     if market_name not in markets_data: break
    #     state = markets_data[market_name]
    #     if state.get('is_running', 0) == 0:
    #         if hasattr(client, 'close'): await client.close()
    #         elif hasattr(client, 'disconnect'): await client.disconnect()
    #         break
    #     now = datetime.now()

    #     # EKSEKUSI MANUAL TRADE
    #     if market_name in markets_data and len(markets_data[market_name]["manual_queue"]) > 0:
    #         cmd = markets_data[market_name]["manual_queue"].pop(0)
    #         try:
    #             amount_int = int(float(cmd['amount']))
    #             duration_raw = int(cmd['duration'])
    #             direction_str = str(cmd['direction'])

    #             try: await client.trade.place_order(actual_asset_id, amount_int, direction_str, duration_raw, target_account_id)
    #             except TypeError: await client.trade.place_order(asset=actual_asset_id, amount=amount_int, dir=direction_str, duration=duration_raw, account_id=target_account_id)

    #             save_trade_db(now.strftime("%Y-%m-%d"), now.strftime("%H:%M:%S"), market_name, f"MANUAL {direction_str.upper()}", amount_int)
    #         except Exception as e:
    #             amt = locals().get('amount_int', 0)
    #             save_trade_db(now.strftime("%Y-%m-%d"), now.strftime("%H:%M:%S"), market_name, f"GAGAL: Script Error", amt)

    #     # TRIGGER UPDATE PROFITABILITAS PER 5 MENIT
    #     if now.minute % 5 == 0 and now.second < 2:
    #         try:
    #             await update_profitability_db(client, target_account_id)
    #         except Exception: pass

    #     # PING SERVER
    #     if now.second % 15 == 0 and now.microsecond < 500000:
    #         try: await client.send_message({"e": 98, "d": []})
    #         except Exception:
    #             try:
    #                 if hasattr(client, 'close'): await client.close()
    #                 elif hasattr(client, 'disconnect'): await client.disconnect()
    #             except: pass
    #             await asyncio.sleep(1)
    #             try: await client.start()
    #             except: pass

    #     # --- DIUBAH: SIMPAN DATA SETIAP MENIT AGAR LOGIKA c0, c2, c3, c4 BEKERJA ---
    #     if 2 <= now.second <= 15 and last_minute_checked != now.minute:
    #         prev_minute = (now.minute - 1) % 60
    #         waktu_laporan = f"{now.hour if now.minute != 0 else (now.hour - 1) % 24:02d}:{prev_minute:02d}"

    #         last_raw_candles = []
    #         try:
    #             await client.market.get_candles(actual_asset_id, 60, 2)
    #             await asyncio.sleep(1)
    #         except Exception:
    #             pass

    #         if len(last_raw_candles) > 0:
    #             last_minute_checked = now.minute
    #             target_candle = last_raw_candles[1] if len(last_raw_candles) >= 2 else last_raw_candles[0]

    #             # --- EKSTRAKSI DATA OHLC & VOL BARU ---
    #             o_pr = float(target_candle.get('open', 0))
    #             h_pr = float(target_candle.get('high', 0))
    #             l_pr = float(target_candle.get('low', 0))
    #             c_pr = float(target_candle.get('close', 0))
    #             vol  = int(target_candle.get('vol', 0))

    #             # --- TENTUKAN WARNA LOGIKA DOJI ---
    #             warna_label = get_candle_color(o_pr, h_pr, l_pr, c_pr)
    #             base_warna = "Hijau" if "Hijau" in warna_label else "Merah"

    #             # Simpan data dengan lengkap
    #             save_analysis_db(market_name, now.strftime("%Y-%m-%d"), waktu_laporan, warna_label, o_pr, h_pr, l_pr, c_pr, vol)

    #             # LOGIKA TELEGRAM SERVER
    #             # if state['tg_active']:
    #             #     hist = get_history_db(market_name, 100)
    #             #     sig_loss = calc_sig_loss(hist)
    #             #     mm = prev_minute
    #             #     candle_id = f"{now.strftime('%Y-%m-%d')}_{waktu_laporan}"

    #             #     if state["tg_last_candle"] != candle_id:
    #             #         tg_phase = state['tg_phase']
    #             #         tg_trade_counter = state['tg_trade_counter']
    #             #         tg_direction = state['tg_direction']

    #             #         # --- FITUR TAMBAHAN: NOTIFIKASI STREAK TERDETEKSI (DI MENIT KE-4) ---
    #             #         if sig_loss == state['tg_target_loss'] and (mm % 5 == 4):
    #             #             msg_alert = f"🚨 *ALERT STREAK TERDETEKSI* 🚨\n\n📈 *Market:* {market_name}\n📊 *False Beruntun:* {sig_loss}x\n⚠️ Target tercapai! Silahkan pantau untuk Open Posisi selanjutnya."
    #             #             send_telegram_internal(msg_alert)
    #             #             state['tg_last_candle'] = candle_id # Tandai agar tidak spam

    #             #         elif tg_phase == "IDLE" and (mm % 5 == 2):
    #             #             if state["tg_target_loss"] > 0:
    #             #                 expected_trades = sig_loss // state["tg_target_loss"]

    #             #                 # LOGIKA PINTAR AUTO-RESET SIKLUS
    #             #                 if expected_trades < tg_trade_counter:
    #             #                     tg_trade_counter = expected_trades
    #             #                     state['tg_trade_counter'] = tg_trade_counter
    #             #                     conn2 = get_db_connection()
    #             #                     if conn2:
    #             #                         conn2.cursor().execute("UPDATE market_states SET tg_trade_counter=%s WHERE market=%s", (tg_trade_counter, market_name))
    #             #                         conn2.commit(); conn2.close()

    #             #                 if expected_trades > tg_trade_counter and sig_loss > 0:
    #             #                     tg_trade_counter += 1
    #             #                     tg_phase = "WAIT_CONF"
    #             #                     state['tg_trade_counter'] = tg_trade_counter; state['tg_phase'] = tg_phase; state['tg_last_candle'] = candle_id
    #             #                     next_min = f"{(mm + 3) % 60:02d}"
    #             #                     msg = f"⚠️ *SERVER: PERSIAPAN OP* ⚠️\n\n📈 *Market:* {market_name}\n🗓 *Waktu:* {waktu_laporan} WIB\n\nTarget *FALSE ke-{sig_loss}* tercapai.\nStandby arah menit ke-{next_min}.\n"
    #             #                     send_telegram_internal(msg)
    #             #                     conn2 = get_db_connection()
    #             #                     if conn2:
    #             #                         conn2.cursor().execute("UPDATE market_states SET tg_trade_counter=%s, tg_phase=%s, tg_last_candle=%s WHERE market=%s", (tg_trade_counter, tg_phase, candle_id, market_name))
    #             #                         conn2.commit(); conn2.close()

    #             #         elif tg_phase == "WAIT_CONF" and (mm % 5 == 0):
    #             #             tg_phase = "WAIT_RES"
    #             #             state['tg_phase'] = tg_phase
    #             #             state['tg_direction'] = "BUY 🟢" if base_warna == "Hijau" else "SELL 🔴"
    #             #             state['tg_last_candle'] = candle_id
    #             #             tg_direction = state['tg_direction']
    #             #             next_min = f"{(mm + 2) % 60:02d}"
    #             #             msg = f"🚀 *SERVER: SINYAL EKSEKUSI* 🚀\n\n📈 *Market:* {market_name}\n🗓 *Waktu:* {waktu_laporan} WIB\n\n🚨 Eksekusi Manual:\n👉 *{tg_direction}*\n🗓 *Hasil Menit {next_min}*\n"
    #             #             send_telegram_internal(msg)
    #             #             conn2 = get_db_connection()
    #             #             if conn2:
    #             #                 conn2.cursor().execute("UPDATE market_states SET tg_phase=%s, tg_direction=%s, tg_last_candle=%s WHERE market=%s", (tg_phase, tg_direction, candle_id, market_name))
    #             #                 conn2.commit(); conn2.close()

    #             #         elif tg_phase == "WAIT_RES" and (mm % 5 == 2):
    #             #             tg_phase = "IDLE"
    #             #             state['tg_phase'] = tg_phase; state['tg_last_candle'] = candle_id
    #             #             required_color = "Hijau" if "BUY" in tg_direction else "Merah"
    #             #             is_win = (base_warna == required_color)
    #             #             status_emoji = "✅" if is_win else "❌"
    #             #             hasil_teks = "TRUE" if is_win else "FALSE"
    #             #             msg = f"{status_emoji} *SERVER: HASIL TRADE* {status_emoji}\n\n📈 *Market:* {market_name}\nArah Tadi: *{tg_direction}*\nCandle Hasil: *{warna_label.upper()}*\nHasil Akhir: *{hasil_teks}*\n"
    #             #             send_telegram_internal(msg)
    #             #             conn2 = get_db_connection()
    #             #             if conn2:
    #             #                 conn2.cursor().execute("UPDATE market_states SET tg_phase=%s, tg_last_candle=%s WHERE market=%s", (tg_phase, candle_id, market_name))
    #             #                 conn2.commit(); conn2.close()
    #         else:
    #             last_minute_checked = now.minute

    #     await asyncio.sleep(0.5)

    # Logika Baru
    while True:
        if market_name not in markets_data:
            break
        state = markets_data[market_name]
        if state.get("is_running", 0) == 0:
            if hasattr(client, "stop"):
                await client.stop()
            elif hasattr(client, "close"):
                await client.close()
            elif hasattr(client, "disconnect"):
                await client.disconnect()
            break

        now = datetime.now()

        # EKSEKUSI MANUAL TRADE
        if (
            market_name in markets_data
            and len(markets_data[market_name]["manual_queue"]) > 0
        ):
            cmd = markets_data[market_name]["manual_queue"].pop(0)
            try:
                amount_int = int(float(cmd["amount"]))
                duration_raw = int(cmd["duration"])
                direction_str = str(cmd["direction"])

                try:
                    await client.trade.place_order(
                        actual_asset_id,
                        amount_int,
                        direction_str,
                        duration_raw,
                        target_account_id,
                    )
                except TypeError:
                    await client.trade.place_order(
                        asset=actual_asset_id,
                        amount=amount_int,
                        dir=direction_str,
                        duration=duration_raw,
                        account_id=target_account_id,
                    )

                save_trade_db(
                    now.strftime("%Y-%m-%d"),
                    now.strftime("%H:%M:%S"),
                    market_name,
                    f"MANUAL {direction_str.upper()}",
                    amount_int,
                )

            except Exception:
                amt = locals().get("amount_int", 0)
                save_trade_db(
                    now.strftime("%Y-%m-%d"),
                    now.strftime("%H:%M:%S"),
                    market_name,
                    f"GAGAL: Script Error",
                    amt,
                )

        # UPDATE PROFITABILITAS
        if now.minute % 5 == 0 and now.second < 2:
            try:
                await update_profitability_db(client, target_account_id)
            except:
                pass

        # PING SERVER
        if now.second % 15 == 0 and now.microsecond < 500000:
            try:
                await client.send_message({"e": 98, "d": []})
            except:
                try:
                    if hasattr(client, "stop"):
                        await client.stop()
                    elif hasattr(client, "close"):
                        await client.close()
                    elif hasattr(client, "disconnect"):
                        await client.disconnect()
                except:
                    pass
                await asyncio.sleep(1)
                try:
                    await client.start()
                except:
                    pass

        # SIMPAN DATA CANDLE
        if 2 <= now.second <= 15 and last_minute_checked != now.minute:

            prev_minute = (now.minute - 1) % 60
            waktu_laporan = f"{now.hour if now.minute != 0 else (now.hour - 1) % 24:02d}:{prev_minute:02d}"

            last_raw_candles = []

            try:
                await client.market.get_candles(actual_asset_id, 60, 2)
                await asyncio.sleep(1)
            except:
                pass

            if len(last_raw_candles) > 0:
                try:
                    last_minute_checked = now.minute
                    target_candle = (
                        last_raw_candles[1]
                        if len(last_raw_candles) >= 2
                        else last_raw_candles[0]
                    )

                    o_pr = float(target_candle.get("open", 0))
                    h_pr = float(target_candle.get("high", 0))
                    l_pr = float(target_candle.get("low", 0))
                    c_pr = float(target_candle.get("close", 0))
                    vol = int(target_candle.get("vol", 0))

                    warna_label = get_candle_color(o_pr, h_pr, l_pr, c_pr)
                    base_warna = "Hijau" if "Hijau" in warna_label else "Merah"

                    save_analysis_db(
                        market_name,
                        now.strftime("%Y-%m-%d"),
                        waktu_laporan,
                        warna_label,
                        o_pr,
                        h_pr,
                        l_pr,
                        c_pr,
                        vol,
                    )

                    # Tampilkan status di terminal biar user tahu bot jalan
                    symbol_color = "🟢" if base_warna == "Hijau" else "🔴"
                    print(
                        f"[{now.strftime('%H:%M:%S')}] {market_name} | {waktu_laporan} | {symbol_color} {warna_label} | {c_pr}",
                        flush=True,
                    )

                    # =========================================
                    # ✅ USER2: CEK POLA C1-C8 SETELAH SIMPAN
                    # =========================================
                    try:
                        tanggal_str = now.strftime("%Y-%m-%d")
                        menit_laporan = int(waktu_laporan.split(":")[1])
                        base_block_mm = (menit_laporan // 5) * 5
                        jam_laporan = waktu_laporan.split(":")[0]
                        waktu_block = f"{jam_laporan}:{base_block_mm:02d}"
                        check_user2_pattern(market_name, tanggal_str, waktu_block)
                    except Exception as e_u2:
                        print(f"[USER2] Error trigger: {e_u2}", flush=True)

                    # =========================================
                    # ✅ LOGIKA TELEGRAM BARU (ANTI SPAM - 1x NOTIF)
                    # =========================================
                    if state.get("tg_active"):
                        hist = get_history_db(market_name, 100)
                        sig_loss = calc_sig_loss(hist)
                        candle_id = f"{now.strftime('%Y-%m-%d')}_{waktu_laporan}"

                        if state.get("tg_last_candle") != candle_id:
                            trigger_target = state.get("tg_target_loss", 7)
                            last_notif_level = state.get("tg_notif_sent_for_level", -1)

                            if sig_loss < last_notif_level:
                                state["tg_notif_sent_for_level"] = -1
                                last_notif_level = -1

                            if (
                                sig_loss == trigger_target
                                and sig_loss != last_notif_level
                            ):
                                open_time = now + timedelta(minutes=5)
                                open_jam = open_time.strftime("%H:%M")
                                msg = f"🚨 ALERT FALSE STREAK TERCAPAI\n\n📈 Market: {market_name}\n📊 False Beruntun: {sig_loss}x\n⏰ Target: {trigger_target}x\n\n⏱ Open Posisi: {open_jam} WIB\n✅ Siap untuk entry!"
                                send_telegram_internal(msg)
                                state["tg_notif_sent_for_level"] = sig_loss

                            state["tg_last_candle"] = candle_id
                except Exception as e_proc:
                    print(
                        f"❌ Error processing candle {market_name}: {e_proc}",
                        flush=True,
                    )

            else:
                last_minute_checked = now.minute

        await asyncio.sleep(0.5)


def run_trading_bot_thread(market_name, token, account_id):
    loop = asyncio.new_event_loop()
    asyncio.set_event_loop(loop)
    loop.run_until_complete(async_bot_task(market_name, token, account_id))


# ==========================================
# ENDPOINT FLASK API
# ==========================================


@app.route("/api/get_settings", methods=["GET"])
def api_get_settings():
    return jsonify(get_settings())


@app.route("/api/check_accounts", methods=["POST", "OPTIONS"])
def api_check_accounts():
    if request.method == "OPTIONS":
        return jsonify({}), 200
    token = request.json.get("token")
    if not token:
        return jsonify({"status": "error", "message": "Harap masukkan Access Token!"})
    try:
        accounts = asyncio.run(fetch_accounts(token))
        if accounts:
            return jsonify({"status": "success", "accounts": accounts})
        else:
            return jsonify(
                {
                    "status": "error",
                    "message": "Gagal menarik data. Token salah / expired.",
                }
            )
    except Exception as e:
        return jsonify({"status": "error", "message": "Koneksi terputus dari server."})


@app.route("/api/start", methods=["POST"])
def start_bot():
    data = request.json
    market = data.get("market")
    token = data.get("token")
    account_id = data.get("account_id")
    save_settings(token, account_id)

    if market not in markets_data:
        markets_data[market] = {"manual_queue": []}
    elif markets_data[market].get("is_running") == 1:
        return jsonify({"status": "success", "message": f"{market} sudah berjalan!"})

    init_market_state(market)
    threading.Thread(
        target=run_trading_bot_thread, args=(market, token, account_id), daemon=True
    ).start()
    return jsonify(
        {"status": "success", "message": f"Koneksi {market} berhasil dibuka!"}
    )


@app.route("/api/start_all", methods=["POST"])
def start_all():
    data = request.json
    token = data.get("token")
    account_id = data.get("account_id")
    save_settings(token, account_id)

    job_id = _bump_start_all_job_id()

    def start_all_bg(my_job_id):
        for m in ASSET_MAPPING.keys():
            # Jika ada STOP atau START_ALL baru, batalkan job lama
            if my_job_id != _get_start_all_job_id():
                break

            if m not in markets_data:
                markets_data[m] = {"manual_queue": []}
            elif markets_data[m].get("is_running") == 1:
                continue  # Skip if already running to prevent double threads

            init_market_state(m)
            threading.Thread(
                target=run_trading_bot_thread, args=(m, token, account_id), daemon=True
            ).start()

            # Sleep bertahap supaya responsif saat dibatalkan
            for _ in range(15):
                if my_job_id != _get_start_all_job_id():
                    break
                time.sleep(0.1)

    threading.Thread(target=start_all_bg, args=(job_id,), daemon=True).start()
    return jsonify(
        {
            "status": "success",
            "message": f"Memulai {len(ASSET_MAPPING)} market secara bertahap!",
        }
    )


@app.route("/api/stop", methods=["POST"])
def stop_bot():
    market = request.json.get("market")
    if market in markets_data:
        markets_data[market]["is_running"] = 0
    conn = get_db_connection()
    if conn:
        conn.cursor().execute(
            "UPDATE market_states SET is_running = 0 WHERE market = %s", (market,)
        )
        conn.commit()
        conn.close()
    return jsonify({"status": "success"})


@app.route("/api/stop_all", methods=["POST"])
def stop_all():
    # Batalkan job start_all yang sedang berjalan (kalau ada)
    _bump_start_all_job_id()

    for m in markets_data.values():
        m["is_running"] = 0
    conn = get_db_connection()
    if not conn:
        return jsonify({"status": "error"})
    conn.cursor().execute("UPDATE market_states SET is_running = 0")
    conn.commit()
    conn.close()
    return jsonify(
        {"status": "success", "message": "Semua bot market berhasil dihentikan!"}
    )


@app.route("/api/reset_market", methods=["POST"])
def reset_market():
    market = request.json.get("market")
    if market in markets_data:
        markets_data[market]["tg_notif_sent_for_level"] = -1  # Reset flag
    conn = get_db_connection()
    c = conn.cursor()
    c.execute("DELETE FROM market_histories WHERE market = %s", (market,))
    c.execute(
        "UPDATE market_states SET total_trade=0, total_hijau=0, total_merah=0, tg_trade_counter=0, tg_phase='IDLE' WHERE market = %s",
        (market,),
    )
    conn.commit()
    conn.close()
    return jsonify({"status": "success", "message": f"Data {market} berhasil direset."})


@app.route("/api/reset_all", methods=["POST"])
def reset_all():
    for state in markets_data.values():
        state["tg_notif_sent_for_level"] = -1  # Reset flag di semua market
    conn = get_db_connection()
    c = conn.cursor()
    c.execute("TRUNCATE TABLE market_histories")
    c.execute(
        "UPDATE market_states SET total_trade=0, total_hijau=0, total_merah=0, tg_trade_counter=0, tg_phase='IDLE'"
    )
    conn.commit()
    conn.close()
    return jsonify({"status": "success", "message": "Semua data berhasil direset!"})


@app.route("/api/toggle_telegram", methods=["POST"])
def toggle_telegram():
    data = request.json
    market = data.get("market")
    target_loss = int(data.get("target_loss", 7))

    if market in markets_data:
        new_active = 0 if markets_data[market]["tg_active"] else 1
        markets_data[market].update(
            {
                "tg_active": new_active,
                "tg_target_loss": target_loss,
                "tg_phase": "IDLE",
                "tg_notif_sent_for_level": -1,
            }
        )
        conn = get_db_connection()
        if conn:
            conn.cursor().execute(
                "UPDATE market_states SET tg_active=%s, tg_target_loss=%s, tg_phase='IDLE' WHERE market=%s",
                (new_active, target_loss, market),
            )
            conn.commit()
            conn.close()
        return jsonify({"status": "success", "active": bool(new_active)})
    return jsonify({"status": "error", "message": "Market belum aktif!"})


@app.route("/api/toggle_telegram_all", methods=["POST"])
def toggle_telegram_all():
    data = request.json
    target_loss = int(data.get("target_loss", 7))

    conn = get_db_connection()
    c = conn.cursor()

    # 🔥 CEK STATUS SEKARANG DARI DB (BIAR VALID)
    c.execute("SELECT COUNT(*) FROM market_states WHERE tg_active = 1")
    active_now = c.fetchone()[0]

    active_count = 0

    if active_now > 0:
        # 🔴 MODE: MATIKAN SEMUA
        for state in markets_data.values():
            state.update(
                {"tg_active": 0, "tg_phase": "IDLE", "tg_notif_sent_for_level": -1}
            )

        c.execute("UPDATE market_states SET tg_active=0, tg_phase='IDLE'")

        conn.commit()
        conn.close()

        return jsonify(
            {
                "status": "success",
                "message": "Sinyal Telegram DIMATIKAN di semua market!",
                "active": False,
            }
        )

    else:
        # 🟢 MODE: AKTIFKAN
        for m, state in markets_data.items():
            if state.get("is_running"):
                state.update(
                    {
                        "tg_active": 1,
                        "tg_target_loss": target_loss,
                        "tg_phase": "IDLE",
                        "tg_notif_sent_for_level": -1,
                    }
                )

                c.execute(
                    "UPDATE market_states SET tg_active=1, tg_target_loss=%s, tg_phase='IDLE' WHERE market=%s",
                    (target_loss, m),
                )

                active_count += 1

        conn.commit()
        conn.close()

        return jsonify(
            {
                "status": "success",
                "message": f"Sinyal Telegram DIAKTIFKAN di {active_count} market aktif!",
                "active": True,
            }
        )


@app.route("/api/stop_telegram_all", methods=["POST"])
def stop_telegram_all():
    for state in markets_data.values():
        state.update({"tg_active": 0, "tg_phase": "IDLE"})
    conn = get_db_connection()
    conn.cursor().execute("UPDATE market_states SET tg_active=0, tg_phase='IDLE'")
    conn.commit()
    conn.close()
    return jsonify(
        {
            "status": "success",
            "message": "Sinyal Telegram di SEMUA market berhasil dimatikan!",
        }
    )


@app.route("/api/manual_trade", methods=["POST"])
def manual_trade():
    data = request.json
    market = data.get("market")
    if market in markets_data:
        markets_data[market]["manual_queue"].append(
            {
                "direction": data.get("direction"),
                "amount": data.get("amount", 1),
                "duration": data.get("duration", 60),
            }
        )
        return jsonify({"status": "success", "message": f"Sinyal dikirim!"})
    return jsonify({"status": "error", "message": "Bot belum jalan!"})


@app.route("/api/data", methods=["GET"])
def get_data():
    market = request.args.get("market")
    conn = get_db_connection()
    c = conn.cursor(dictionary=True)
    c.execute("SELECT * FROM market_states WHERE market = %s", (market,))
    state = c.fetchone()

    if state:
        histories = get_history_db(market, 500)
        conn.close()
        return jsonify(
            {
                "is_running": bool(state["is_running"]),
                "stats": {
                    "total_trade": state["total_trade"],
                    "total_hijau": state["total_hijau"],
                    "total_merah": state["total_merah"],
                },
                "history": histories,
                "telegram": {
                    "active": bool(state["tg_active"]),
                    "target_loss": state["tg_target_loss"],
                    "trade_counter": state["tg_trade_counter"],
                },
                "balance": global_demo_balance,
            }
        )
    conn.close()
    return jsonify(
        {
            "is_running": False,
            "stats": {"total_trade": 0, "total_hijau": 0, "total_merah": 0},
            "history": [],
            "balance": global_demo_balance,
        }
    )


@app.route("/api/status_all", methods=["GET"])
def status_all():
    conn = get_db_connection()
    if not conn:
        return jsonify(
            {
                "active_markets": [],
                "market_streaks": {},
                "doji_analytics": [],
                "balance": global_demo_balance,
                "tg_active_count": 0,
                "mass_target_loss": None,
            }
        )

    c_dict = conn.cursor(dictionary=True)
    c_dict.execute(
        "SELECT market, tg_active, tg_target_loss FROM market_states WHERE is_running = 1"
    )
    running_data = c_dict.fetchall()

    active_markets = [row["market"] for row in running_data]
    tg_active_count = sum(1 for row in running_data if row["tg_active"] == 1)
    active_target_losses = [
        int(row.get("tg_target_loss") or 0)
        for row in running_data
        if row.get("tg_active") == 1 and int(row.get("tg_target_loss") or 0) > 0
    ]
    mass_target_loss = active_target_losses[0] if active_target_losses else None

    market_streaks = {}
    doji_analytics = []

    for mkt in active_markets:
        c_dict.execute(
            "SELECT market, tanggal, waktu, warna FROM market_histories WHERE market = %s ORDER BY id DESC LIMIT 100",
            (mkt,),
        )
        raw_hist = c_dict.fetchall()

        # Hitung sig_loss normal
        sig_loss = calc_sig_loss(raw_hist)
        market_streaks[mkt] = sig_loss

        # LOGIKA ANALISA DOJI KETIKA FALSE MULAI 1 SAMPAI 9
        if sig_loss >= 1 and sig_loss <= 9:
            # Mengambil candle sejumlah (sig_loss * 5) dari raw_hist
            # Contoh: Jika 9 False = 45 Candle, Jika 6 False = 30 Candle
            candles_to_check = sig_loss * 5
            hist_target = raw_hist[:candles_to_check]
            doji_count = 0
            for item in hist_target:
                if item["warna"] and "Doji" in item["warna"]:
                    doji_count += 1

            # Hitung Winrate
            winrate = 0.0
            if candles_to_check > 0:
                winrate = (doji_count / float(candles_to_check)) * 100

            doji_analytics.append(
                {
                    "market": mkt,
                    "consecutive_false": sig_loss,
                    "doji_count": doji_count,
                    "total_candles": candles_to_check,
                    "winrate": round(winrate, 1),  # contoh: 12.5%
                }
            )

    c_dict.close()
    conn.close()
    return jsonify(
        {
            "active_markets": active_markets,
            "market_streaks": market_streaks,
            "doji_analytics": doji_analytics,
            "balance": global_demo_balance,
            "tg_active_count": tg_active_count,
            "mass_target_loss": mass_target_loss,
        }
    )


@app.route("/api/trade_history", methods=["GET"])
def trade_history():
    conn = get_db_connection()
    if conn:
        c = conn.cursor(dictionary=True)
        c.execute(
            "SELECT tanggal, waktu, market, warna, amount FROM trade_histories ORDER BY id DESC LIMIT 500"
        )
        results = c.fetchall()
        conn.close()
        return jsonify({"trade_history": results})
    return jsonify({"trade_history": []})


def count_false_streak_triggers(candles):
    """
    Implement logic sama seperti calc_sig_loss() untuk count FALSE patterns dalam 5-minute blocks
    Group candles by 5-minute blocks (c0-c4) per tanggal dan jam
    c0 = menit ke 0-4, c1-c4 = menit ke 5-9, 10-14, dst
    """
    sig_loss = 0
    blocks = {}

    for c in candles:
        if c.get("waktu") and ":" in c["waktu"]:
            try:
                parts = c["waktu"].split(":")
                hh, mm = parts[0], int(parts[1])
                base_mm = (mm // 5) * 5
                key = f"{c['tanggal']}_{hh}:{base_mm:02d}"

                if key not in blocks:
                    blocks[key] = {}

                offset = mm % 5
                base_color = "Hijau" if "Hijau" in c["warna"] else "Merah"
                blocks[key][f"c{offset}"] = base_color
            except Exception as e:
                continue

    # Diurutkan agar deteksi reset ke 0 dari candle terbaru berjalan akurat
    sorted_keys = sorted(blocks.keys(), reverse=True)
    for k in sorted_keys:
        b = blocks[k]
        if "c0" in b:
            c0 = b["c0"]

            # Kondisi TRUE: salah satu dari c2, c3, atau c4 SAMA dengan c0
            is_true = False
            if "c2" in b and b["c2"] == c0:
                is_true = True
            if "c3" in b and b["c3"] == c0:
                is_true = True
            if "c4" in b and b["c4"] == c0:
                is_true = True

            if is_true:
                break  # Reset ke 0 jika mendeteksi ada 1 TRUE (Win)
            # Kondisi FALSE: Siklus lengkap (0,2,3,4) tapi tidak ada yang sama
            elif "c2" in b and "c3" in b and "c4" in b:
                sig_loss += 1

    return sig_loss


def count_open_positions_today(candles):
    """
    Hitung berapa KALI open posisi terjadi (bukan FALSE streak value)

    Logic:
    - Process blok dari OLDEST to NEWEST (chronologically)
    - Track: `open_positions` counter dan `at_2_or_above` flag
    - Ketika FALSE FIRST TIME mencapai >= 2 → Increment counter
    - Counter TIDAK direset ketika FALSE turun kembali ke 0

    Return: Total jumlah open posisi yang terdeteksi hari ini

    Contoh:
    10:00-04: Win → Reset
    10:05-09: FALSE = 1
    10:10-14: FALSE = 2 🚨 open_positions = 1
    10:15-19: Win → Reset (counter TETAP 1, tidak turun)
    10:20-24: FALSE = 1
    10:25-29: FALSE = 2 🚨 open_positions = 2

    Result: 2 (ada 2 kali open posisi hari ini)
    """
    blocks = {}
    open_positions = 0
    at_2_or_above = (
        False  # Flag untuk track: sudah mencapai 2 di sequence ini atau belum?
    )

    # Build blocks dari candles
    for c in candles:
        if c.get("waktu") and ":" in c["waktu"]:
            try:
                parts = c["waktu"].split(":")
                hh, mm = parts[0], int(parts[1])
                base_mm = (mm // 5) * 5
                key = f"{c['tanggal']}_{hh}:{base_mm:02d}"

                if key not in blocks:
                    blocks[key] = {}

                offset = mm % 5
                base_color = "Hijau" if "Hijau" in c["warna"] else "Merah"
                blocks[key][f"c{offset}"] = base_color
            except Exception as e:
                continue

    # Process blocks dari OLDEST to NEWEST (chronologically)
    sorted_keys = sorted(blocks.keys())  # ASC, bukan DESC seperti calc_sig_loss

    sig_loss = 0
    for k in sorted_keys:
        b = blocks[k]
        if "c0" in b:
            c0 = b["c0"]

            # Kondisi TRUE: salah satu dari c2, c3, atau c4 SAMA dengan c0
            is_true = False
            if "c2" in b and b["c2"] == c0:
                is_true = True
            if "c3" in b and b["c3"] == c0:
                is_true = True
            if "c4" in b and b["c4"] == c0:
                is_true = True

            if is_true:
                # WIN → Reset sig_loss DAN reset flag
                sig_loss = 0
                at_2_or_above = False
            # Kondisi FALSE: Siklus lengkap (0,2,3,4) tapi tidak ada yang sama
            elif "c2" in b and "c3" in b and "c4" in b:
                sig_loss += 1

                # 🚨 LOGIC: Ketika sig_loss FIRST TIME mencapai >= 2
                if sig_loss >= 2 and not at_2_or_above:
                    open_positions += 1
                    at_2_or_above = True  # Mark: sudah dihitung untuk sequence ini
            else:
                # Tidak ada candle lengkap, reset flag aja
                at_2_or_above = False

    return open_positions


@app.route("/api/trade-history", methods=["GET"])
def trade_history_calculated():
    try:
        raw_target_loss = request.args.get("target_loss", "2")
        try:
            requested_target_loss = int(raw_target_loss)
        except (TypeError, ValueError):
            requested_target_loss = 2

        if requested_target_loss < 1 or requested_target_loss > 6:
            requested_target_loss = 2

        conn = get_db_connection()
        if not conn:
            return jsonify(
                {
                    "success": True,
                    "data": [],
                    "date": datetime.now().strftime("%Y-%m-%d"),
                    "summary": {},
                }
            )

        c = conn.cursor(dictionary=True)

        # ✅ ambil semua data (terbaru dulu)
        c.execute(
            """
            SELECT 
                tanggal,
                waktu,
                market,
                trigger_at,
                target_loss,
                phase_1,
                phase_2,
                phase_3,
                phase_4,
                phase_5,
                phase_6,
                phase_7
            FROM phase_histories
            WHERE target_loss = %s
            ORDER BY tanggal DESC, waktu DESC, id DESC
            LIMIT 5000
        """,
            (requested_target_loss,),
        )
        rows = c.fetchall()

        # 🔥 FIX 1: GROUP PER TICKER (AMBIL TERBARU SAJA)
        result = []
        total_true = 0
        total_false = 0

        # 🔥 LOOP DATA FINAL (SUDAH UNIQUE)
        for row in rows:

            # hitung TRUE / FALSE
            for i in range(1, 8):
                val = str(row.get(f"phase_{i}", "")).upper()
                if val == "TRUE":
                    total_true += 1
                elif val == "FALSE":
                    total_false += 1

            result.append(
                {
                    "tanggal": row.get("tanggal"),
                    "waktu": row.get("waktu"),
                    "ticker": row.get("market"),
                    # 🔥 penting untuk jam
                    "trigger_at": row.get("trigger_at"),
                    "target_loss": row.get("target_loss", requested_target_loss),
                    "phase_1": row.get("phase_1", "-"),
                    "phase_2": row.get("phase_2", "-"),
                    "phase_3": row.get("phase_3", "-"),
                    "phase_4": row.get("phase_4", "-"),
                    "phase_5": row.get("phase_5", "-"),
                    "phase_6": row.get("phase_6", "-"),
                    "phase_7": row.get("phase_7", "-"),
                }
            )

        # 🔥 HITUNG SUMMARY
        total = total_true + total_false
        accuracy = (total_true / total * 100) if total > 0 else 0

        summary = {
            "today": {"total_signals": len(result)},
            "month": {
                "total_signals": len(result),
                "wins": total_true,
                "losses": total_false,
                "accuracy_label": f"{accuracy:.2f}%",
            },
        }

        c.close()
        conn.close()

        return jsonify(
            {
                "success": True,
                "data": result,
                "date": datetime.now().strftime("%Y-%m-%d"),
                "summary": summary,
                "generated_at": datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
            }
        )

    except Exception as e:
        print("ERROR:", str(e))
        return jsonify({"success": False, "message": str(e), "data": []}), 500


@app.route("/api/user2_data", methods=["GET"])
def user2_data():
    """
    Endpoint untuk halaman User2 Pattern Scanner.
    Mengembalikan status C1-C8 dan pola untuk semua market hari ini.
    """
    try:
        conn = get_db_connection()
        if not conn:
            return jsonify({"success": False, "data": []})

        today = datetime.now().strftime("%Y-%m-%d")
        cursor = conn.cursor(dictionary=True)

        # Ambil semua record user2_patterns untuk hari ini
        cursor.execute(
            """
            SELECT market, waktu_block, c1, c2, c3, c4, c5, c6, c7, c8, pattern_type, notif_sent
            FROM user2_patterns
            WHERE tanggal = %s
            ORDER BY market ASC, waktu_block DESC
        """,
            (today,),
        )
        rows = cursor.fetchall()

        # Grouping: ambil blok terbaru per market
        latest_per_market = {}
        for row in rows:
            mkt = row["market"]
            if mkt not in latest_per_market:
                latest_per_market[mkt] = row

        # Tambahkan market yang running tapi belum ada record hari ini
        cursor.execute("SELECT market FROM market_states WHERE is_running = 1")
        running_markets = [r["market"] for r in cursor.fetchall()]

        result = []
        for mkt in running_markets:
            if mkt in latest_per_market:
                row = latest_per_market[mkt]
                result.append(
                    {
                        "market": mkt,
                        "waktu_block": row["waktu_block"],
                        "c1": row["c1"] or "-",
                        "c2": row["c2"] or "-",
                        "c3": row["c3"] or "-",
                        "c4": row["c4"] or "-",
                        "c5": row["c5"] or "-",
                        "c6": row["c6"] or "-",
                        "c7": row["c7"] or "-",
                        "c8": row["c8"] or "-",
                        "pattern_type": row["pattern_type"] or "NONE",
                        "notif_sent": bool(row["notif_sent"]),
                    }
                )
            else:
                result.append(
                    {
                        "market": mkt,
                        "waktu_block": "-",
                        "c1": "-",
                        "c2": "-",
                        "c3": "-",
                        "c4": "-",
                        "c5": "-",
                        "c6": "-",
                        "c7": "-",
                        "c8": "-",
                        "pattern_type": "NONE",
                        "notif_sent": False,
                    }
                )

        cursor.close()
        conn.close()

        return jsonify(
            {
                "success": True,
                "data": result,
                "date": today,
                "generated_at": datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
            }
        )

    except Exception as e:
        print(f"[USER2 API] Error: {e}")
        return jsonify({"success": False, "data": [], "error": str(e)}), 500


@app.route("/api/send_wa", methods=["POST"])
def send_telegram():
    data = request.json
    send_telegram_internal(data.get("message", ""))
    return jsonify({"status": "success"})


if __name__ == "__main__":
    app.run(debug=True, port=5000, host="0.0.0.0")
