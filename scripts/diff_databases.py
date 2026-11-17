import sqlite3

c_local = sqlite3.connect('C:/Users/renzo/grocy/grocy/data/grocy_es.db').cursor()
c_seed = sqlite3.connect('C:/Users/renzo/grocy/grocy/demo-seed/grocy_es.db').cursor()

local_tables = [t[0] for t in c_local.execute("SELECT name FROM sqlite_master WHERE type='table'").fetchall()]
seed_tables = set(t[0] for t in c_seed.execute("SELECT name FROM sqlite_master WHERE type='table'").fetchall())

missing_in_seed = []
different_counts = []

for t in sorted(local_tables):
    if t.startswith('sqlite_') or t == 'dbstat':
        continue
    cnt_local = c_local.execute(f'SELECT count(*) FROM "{t}"').fetchone()[0]
    if t not in seed_tables:
        missing_in_seed.append((t, cnt_local))
    else:
        cnt_seed = c_seed.execute(f'SELECT count(*) FROM "{t}"').fetchone()[0]
        if cnt_local != cnt_seed:
            different_counts.append((t, cnt_local, cnt_seed))

print("=== TABLES MISSING IN SEED DB ===")
for t, cnt in missing_in_seed:
    print(f"  {t}: {cnt} rows")

print("\n=== TABLES WITH DIFFERENT ROW COUNTS ===")
for t, cnt_local, cnt_seed in different_counts:
    print(f"  {t} -> Local: {cnt_local} | Seed: {cnt_seed}")
