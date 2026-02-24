# Laporan Analisis Kinerja VPS pada Bot Trading

Berdasarkan analisis teknis dari kode Python (`app.py`), pemicu utama kenapa bot mendadak berhenti (crash/nge-hang) saat di-deploy ke VPS bersumber dari **Bug Arsitektur** sistem saat menghidupkan 27 *market* secara bersamaan.

## Temuan Masalah (Bug Arsitektur)

### 1. Serangan MySQL Mandiri (DDoS pada Database Sendiri)
Masalah paling fatal ada di dalam *loop* utama bot (`app.py`):
```python
    while True:
        conn = get_db_connection()
        # ... proses query ... 
        c.close()
        conn.close()
        await asyncio.sleep(0.5)
```
Saat Anda menekan tombol **"Start All"**, aplikasi akan menjalankan **27 Thread** (untuk 27 aset/market). Di setiap thread, bot akan mengecek status `is_running` ke database setiap **0.5 detik**.
* **Dampaknya:** Karena tidak menggunakan *Connection Pooling*, aplikasi melakukan perintah "Buka Koneksi -> Tanya DB -> Tutup" sebanyak **54 KALI DALAM 1 DETIK**.
* **Akibatnya:** Penggunaan CPU MySQL meroket 100%. Batas maksimal koneksi MySQL di VPS kecil akan langsung habis dalam hitungan detik. MySQL akan *Crash/Timeout*, dan otomatis semua ke-27 bot Python akan *error* dan mati mendadak bersamaan.

### 2. Sistem 27 WebSockets Tanpa Auto-Reconnect
Saat "Start All", skrip membuka 27 jalur WebSocket tunggal langsung ke Olymp Trade dari **satu IP VPS**.
* **Dampaknya:** Server keamanan Olymp Trade / Cloudflare sangat mungkin mengenali ini sebagai *Spam* atau *DDoS* dan memutuskan paksa koneksi.
* **Akibatnya:** Karena di dalam `while True` tidak ada fungsi untuk "Menyambungkan kembali (Reconnect) jika terputus", bot akan diam dan menjadi *Zombie Process* (berjalan tapi tidak merespon/mengeksekusi apa-apa).

### 3. Benturan Memori / Out of Memory (OOM Killer)
Aplikasi melahirkan **1 Thread + 1 Async Event Loop** untuk setiap market. Di OS Linux (VPS), membuat puluhan objek *Event Loop* secara kasar untuk me-listen WebSockets bisa memicu *Memory Leak* (kebocoran RAM).
* **Akibatnya:** Saat RAM VPS nyaris habis (mendekati 100%), sistem perlindungan darurat Linux bernama **OOM-Killer** akan mematikan proses terberat secara paksa dan diam-diam, sehingga `python app.py` tiba-tiba ditutup sepihak oleh OS Linux.

---

## Seberapa Berat Kode Ini & Kebutuhan Spesifikasi VPS

### 1. Jika Skrip Digunakan "Apa Adanya" (Tanpa Revisi)
Sangat berat. Hanya untuk komputasi membuka-tutup `mysql.connect` puluhan kali per detik, Anda membutuhkan VPS dengan prosesor minimal **4 Core vCPU** dan memori **8 GB RAM**. (Biaya berkisar ±$20-$40 per bulan hanya agar sanggup menahan MySQL agar tak jebol).

### 2. Jika Kode Diperbaiki/Dioptimasi (SANGAT DISARANKAN)
Aplikasi *scrapping* dan *trading automation* seperti ini sejatinya masuk dalam kategori **SANGAT RINGAN**. Jika arsitekturnya dioptimalkan, Anda cukup menggunakan VPS termurah dengan paket **1 Core vCPU & 1 GB RAM** (sekitar $4 - $5 / Rp60.000 per bulan).

---

## Rekomendasi Solusi & Optimasi

Agar bot dapat berjalan lancar tanpa berhenti mendadak di VPS murah, kita perlu merevisi `app.py` pada 3 fondasi utama:

1. **Pemindahan Cek Status ke RAM (Caching)**
   Memindahkan pengecekan status (`is_running`) ke memori RAM Python (variabel dictionary `markets_data`), agar program tidak perlu *query database* setiap 0,5 detik. Database CUKUP dipakai untuk menyimpan histori (insert data) saja.

2. **Pemberian Waktu Jeda (Delay) Saat "Start All"**
   Memberikan delay bertahap saat menyalakan banyak market agar Olymp Trade tidak menangkap adanya indikasi serangan bot akibat puluhan koneksi yang masuk instan di milidetik yang sama.

3. **Penerapan MySQL Connection Pool**
   Menggunakan *Connection Pool* pada MySQL Connector di Python. Dengan ini, MySQL tidak perlu membongkar-pasang koneksi baru secara terus-menerus, melainkan cukup menggunakan dan mendaur-ulang *"pintu koneksi"* yang sudah disediakan.
