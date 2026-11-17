import sqlite3

def check_db(path, label):
    conn = sqlite3.connect(path)
    cur = conn.cursor()
    tables = cur.execute("SELECT name FROM sqlite_master WHERE type='table' AND (name LIKE 'b2b_%' OR name LIKE 'wfm_%')").fetchall()
    print(f"\n=== {label} ({path}) ===")
    for (t,) in sorted(tables):
        cnt = cur.execute(f'SELECT count(*) FROM "{t}"').fetchone()[0]
        print(f"  {t}: {cnt} rows")
    conn.close()

check_db('C:/Users/renzo/grocy/grocy/data/grocy_es.db', 'LOCAL (data/grocy_es.db)')
check_db('C:/Users/renzo/grocy/grocy/demo-seed/grocy_es.db', 'SEED (demo-seed/grocy_es.db)')
