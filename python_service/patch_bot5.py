import sys

fpath = "d:/Robot Trading terbaru/robot_tradingv2/python_service/app.py"
with open(fpath, "r", encoding="utf-8") as f:
    c = f.read()

doji_api_old = """        # LOGIKA ANALISA DOJI KETIKA FALSE >= 10
        if sig_loss >= 10:"""

doji_api_new = """        # LOGIKA ANALISA DOJI KETIKA FALSE MULAI 1 SAMPAI 9
        if sig_loss >= 1 and sig_loss <= 9:"""

c = c.replace(doji_api_old, doji_api_new)

with open(fpath, "w", encoding="utf-8") as f:
    f.write(c)
