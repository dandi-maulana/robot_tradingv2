import sys

fpath = "D:\laragon\www\jokowi\robot_tradingv2\python_service\app.py"
with open(fpath, "r", encoding="utf-8") as f:
    c = f.read()

# 1. Telegram Blocks - Memory Cache
tg_old = """                                    # LOGIKA PINTAR AUTO-RESET SIKLUS
                                    if expected_trades < tg_trade_counter:
                                        tg_trade_counter = expected_trades
                                        c.execute("UPDATE market_states SET tg_trade_counter=%s WHERE market=%s", (tg_trade_counter, market_name))

                                    if expected_trades > tg_trade_counter and sig_loss > 0:
                                        tg_trade_counter += 1
                                        tg_phase = "WAIT_CONF"
                                        next_min = f"{(mm + 3) % 60:02d}"
                                        msg = f"⚠️ *SERVER: PERSIAPAN OP* ⚠️\\n\\n📈 *Market:* {market_name}\\n🗓 *Waktu:* {waktu_laporan} WIB\\n\\nTarget *FALSE ke-{sig_loss}* tercapai.\\nStandby arah menit ke-{next_min}.\\n"
                                        send_telegram_internal(msg)
                                        c.execute("UPDATE market_states SET tg_trade_counter=%s, tg_phase=%s, tg_last_candle=%s WHERE market=%s", (tg_trade_counter, tg_phase, candle_id, market_name))

                            elif tg_phase == "WAIT_CONF" and (mm % 5 == 0):
                                tg_phase = "WAIT_RES"
                                # Menggunakan base_warna untuk signal (Buy bila hijau, Sell bila merah)
                                tg_direction = "BUY 🟢" if base_warna == "Hijau" else "SELL 🔴"
                                next_min = f"{(mm + 2) % 60:02d}"
                                msg = f"🚀 *SERVER: SINYAL EKSEKUSI* 🚀\\n\\n📈 *Market:* {market_name}\\n🗓 *Waktu:* {waktu_laporan} WIB\\n\\n🚨 Eksekusi Manual:\\n👉 *{tg_direction}*\\n🗓 *Hasil Menit {next_min}*\\n"
                                send_telegram_internal(msg)
                                c.execute("UPDATE market_states SET tg_phase=%s, tg_direction=%s, tg_last_candle=%s WHERE market=%s", (tg_phase, tg_direction, candle_id, market_name))

                            elif tg_phase == "WAIT_RES" and (mm % 5 == 2):
                                tg_phase = "IDLE"
                                required_color = "Hijau" if "BUY" in tg_direction else "Merah"
                                is_win = (base_warna == required_color)
                                status_emoji = "✅" if is_win else "❌"
                                hasil_teks = "TRUE" if is_win else "FALSE"
                                msg = f"{status_emoji} *SERVER: HASIL TRADE* {status_emoji}\\n\\n📈 *Market:* {market_name}\\nArah Tadi: *{tg_direction}*\\nCandle Hasil: *{warna_label.upper()}*\\nHasil Akhir: *{hasil_teks}*\\n"
                                send_telegram_internal(msg)
                                c.execute("UPDATE market_states SET tg_phase=%s, tg_last_candle=%s WHERE market=%s", (tg_phase, candle_id, market_name))
                    conn.commit()
            else:
                # Kunci menit agar loop toleransi tidak menarik data berulang kali di detik 2-15
                last_minute_checked = now.minute

        c.close()
        conn.close()
        await asyncio.sleep(0.5)"""

tg_new = """                                    # LOGIKA PINTAR AUTO-RESET SIKLUS
                                    if expected_trades < tg_trade_counter:
                                        tg_trade_counter = expected_trades
                                        state['tg_trade_counter'] = tg_trade_counter
                                        conn2 = get_db_connection()
                                        if conn2:
                                            conn2.cursor().execute("UPDATE market_states SET tg_trade_counter=%s WHERE market=%s", (tg_trade_counter, market_name))
                                            conn2.commit(); conn2.close()

                                    if expected_trades > tg_trade_counter and sig_loss > 0:
                                        tg_trade_counter += 1
                                        tg_phase = "WAIT_CONF"
                                        state['tg_trade_counter'] = tg_trade_counter; state['tg_phase'] = tg_phase; state['tg_last_candle'] = candle_id
                                        next_min = f"{(mm + 3) % 60:02d}"
                                        msg = f"⚠️ *SERVER: PERSIAPAN OP* ⚠️\\n\\n📈 *Market:* {market_name}\\n🗓 *Waktu:* {waktu_laporan} WIB\\n\\nTarget *FALSE ke-{sig_loss}* tercapai.\\nStandby arah menit ke-{next_min}.\\n"
                                        send_telegram_internal(msg)
                                        conn2 = get_db_connection()
                                        if conn2:
                                            conn2.cursor().execute("UPDATE market_states SET tg_trade_counter=%s, tg_phase=%s, tg_last_candle=%s WHERE market=%s", (tg_trade_counter, tg_phase, candle_id, market_name))
                                            conn2.commit(); conn2.close()

                            elif tg_phase == "WAIT_CONF" and (mm % 5 == 0):
                                tg_phase = "WAIT_RES"
                                state['tg_phase'] = tg_phase
                                state['tg_direction'] = "BUY 🟢" if base_warna == "Hijau" else "SELL 🔴"
                                state['tg_last_candle'] = candle_id
                                tg_direction = state['tg_direction']
                                next_min = f"{(mm + 2) % 60:02d}"
                                msg = f"🚀 *SERVER: SINYAL EKSEKUSI* 🚀\\n\\n📈 *Market:* {market_name}\\n🗓 *Waktu:* {waktu_laporan} WIB\\n\\n🚨 Eksekusi Manual:\\n👉 *{tg_direction}*\\n🗓 *Hasil Menit {next_min}*\\n"
                                send_telegram_internal(msg)
                                conn2 = get_db_connection()
                                if conn2:
                                    conn2.cursor().execute("UPDATE market_states SET tg_phase=%s, tg_direction=%s, tg_last_candle=%s WHERE market=%s", (tg_phase, tg_direction, candle_id, market_name))
                                    conn2.commit(); conn2.close()

                            elif tg_phase == "WAIT_RES" and (mm % 5 == 2):
                                tg_phase = "IDLE"
                                state['tg_phase'] = tg_phase; state['tg_last_candle'] = candle_id
                                required_color = "Hijau" if "BUY" in tg_direction else "Merah"
                                is_win = (base_warna == required_color)
                                status_emoji = "✅" if is_win else "❌"
                                hasil_teks = "TRUE" if is_win else "FALSE"
                                msg = f"{status_emoji} *SERVER: HASIL TRADE* {status_emoji}\\n\\n📈 *Market:* {market_name}\\nArah Tadi: *{tg_direction}*\\nCandle Hasil: *{warna_label.upper()}*\\nHasil Akhir: *{hasil_teks}*\\n"
                                send_telegram_internal(msg)
                                conn2 = get_db_connection()
                                if conn2:
                                    conn2.cursor().execute("UPDATE market_states SET tg_phase=%s, tg_last_candle=%s WHERE market=%s", (tg_phase, candle_id, market_name))
                                    conn2.commit(); conn2.close()
            else:
                last_minute_checked = now.minute

        await asyncio.sleep(0.5)"""
c = c.replace(tg_old, tg_new)

with open(fpath, "w", encoding="utf-8") as f:
    f.write(c)
