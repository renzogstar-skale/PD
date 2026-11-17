import sqlite3

conn = sqlite3.connect('C:/Users/renzo/grocy/grocy/data/grocy_es.db')
cursor = conn.cursor()

tables = cursor.execute("SELECT name FROM sqlite_master WHERE type='table'").fetchall()
table_info = []

for (t,) in tables:
    try:
        cnt = cursor.execute(f'SELECT count(*) FROM "{t}"').fetchone()[0]
        table_info.append((t, cnt))
    except Exception as e:
        pass

table_info.sort(key=lambda x: x[1], reverse=True)
print("Top 30 tables by row count in data/grocy_es.db:")
for t, cnt in table_info[:30]:
    print(f"{t}: {cnt} rows")

# Also check size per table using dbstat if available
try:
    sizes = cursor.execute("SELECT name, sum(pgsize) as bytes FROM dbstat GROUP BY name ORDER BY bytes DESC LIMIT 15").fetchall()
    print("\nTop 15 tables by storage size (dbstat):")
    for name, b in sizes:
        print(f"{name}: {b / 1024 / 1024:.2f} MB")
except Exception as e:
    print("\ndbstat not enabled:", e)

conn.close()
