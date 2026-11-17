import sqlite3
import os

src_path = 'C:/Users/renzo/grocy/grocy/data/grocy_es.db'
dst_path = 'C:/Users/renzo/grocy/grocy/demo-seed/grocy_es.db'

conn_src = sqlite3.connect(src_path)
conn_dst = sqlite3.connect(dst_path)

cur_src = conn_src.cursor()
cur_dst = conn_dst.cursor()

# Get all b2b_ tables
b2b_tables = [t[0] for t in cur_src.execute("SELECT name FROM sqlite_master WHERE type='table' AND name LIKE 'b2b_%'").fetchall()]
print(f"Found {len(b2b_tables)} b2b_ tables in source DB.")

for table in b2b_tables:
    # Get CREATE TABLE statement
    schema = cur_src.execute(f"SELECT sql FROM sqlite_master WHERE type='table' AND name='{table}'").fetchone()[0]
    
    # Drop in dst if exists, then create
    cur_dst.execute(f'DROP TABLE IF EXISTS "{table}"')
    cur_dst.execute(schema)
    
    # Copy data
    rows = cur_src.execute(f'SELECT * FROM "{table}"').fetchall()
    if rows:
        placeholders = ','.join(['?'] * len(rows[0]))
        cur_dst.executemany(f'INSERT INTO "{table}" VALUES ({placeholders})', rows)
    
    # Copy indices for this table
    indices = cur_src.execute(f"SELECT sql FROM sqlite_master WHERE type='index' AND tbl_name='{table}' AND sql IS NOT NULL").fetchall()
    for (idx_sql,) in indices:
        try:
            cur_dst.execute(idx_sql)
        except Exception as e:
            pass
            
    print(f"  Copied {table}: {len(rows)} rows")

conn_dst.commit()
conn_src.close()

# Also check migrations table in dst
dst_migs = [m[0] for m in cur_dst.execute("SELECT migration FROM migrations").fetchall()]
src_migs = [m[0] for m in cur_dst.execute("SELECT migration FROM migrations").fetchall()]
print("Total migrations applied in seed DB:", len(dst_migs))

# Vacuum destination
print("\nRunning VACUUM on demo-seed/grocy_es.db...")
cur_dst.execute("VACUUM")
conn_dst.close()

size_mb = os.path.getsize(dst_path) / (1024 * 1024)
print(f"\nFinal demo-seed/grocy_es.db size: {size_mb:.2f} MB")
if size_mb < 99.0:
    print("✅ WELL UNDER GITHUB 100MB LIMIT!")
else:
    print("⚠️ WARNING: Close to or over 100MB limit!")
