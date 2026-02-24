import mysql.connector
from datetime import datetime
import time

DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'robot_trading'
}

def inject_test_data():
    conn = mysql.connector.connect(**DB_CONFIG)
    c = conn.cursor()
    
    market = "Asia Composite Index"
    
    # Kosongkan history lama untuk market ini
    c.execute("DELETE FROM market_histories WHERE market = %s", (market,))
    
    dt = datetime.now()
    tgl = dt.strftime("%Y-%m-%d")
    
    # Kita ingin membuat kondisi di mana sig_loss >= 10
    # Cara membuat sig_loss: Kita membuat pasangan candle (menit :00 dan :02) yang warnanya BEDA (False)
    # 10 False = butuh 10 "block" 5 menitan
    
    # Mari kita buat 15 block kebelakang
    # Kita juga sisipkan banyak warna "Doji" agar winrate bisa diuji
    
    start_hour = 10
    doji_counter = 0
    total_candles = 0

    for i in range(20): # 20 block (20 false)
        mm_base = (i * 5) % 60
        hh = start_hour + (i * 5) // 60
        
        warna_1 = "Hijau"
        # Kita buat warna 2 berbeda dari warna 1 agar False, tapi bumbui dengan Doji
        
        warna_2 = "Merah"
        
        if i % 3 == 0:
            warna_2 = "Doji/Merah"
            doji_counter += 1
        elif i % 5 == 0:
            warna_1 = "Doji/Hijau"
            doji_counter += 1
            
        w1 = f"{hh:02}:{mm_base:02}:00"
        w2 = f"{hh:02}:{mm_base+2:02}:00"
        
        # INSERT
        c.execute("INSERT INTO market_histories (market, tanggal, waktu, warna) VALUES (%s, %s, %s, %s)", (market, tgl, w1, warna_1))
        c.execute("INSERT INTO market_histories (market, tanggal, waktu, warna) VALUES (%s, %s, %s, %s)", (market, tgl, w2, warna_2))
        total_candles += 2
        
    conn.commit()
    print(f"Selesai menyuntikkan data! Total candles = {total_candles}. Doji disuntikkan = {doji_counter}.")
    
    c.close()
    conn.close()

if __name__ == "__main__":
    inject_test_data()
