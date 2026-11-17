import sqlite3
import os

src_path = 'C:/Users/renzo/grocy/grocy/data/grocy_es.db'
dst_path = 'C:/Users/renzo/grocy/grocy/demo-seed/grocy_es.db'

conn_src = sqlite3.connect(src_path)
conn_dst = sqlite3.connect(dst_path)

cur_src = conn_src.cursor()
cur_dst = conn_dst.cursor()

tables_to_sync = [
    'customers',
    'customer_establishments',
    'customer_orders',
    'customer_order_items',
    'customer_order_picks',
    'customer_returns_exchanges',
    'customer_audit_log',
    'customer_loyalty_ledger',
    'supplier_invoices',
    'supplier_invoice_items',
    'supplier_returns',
    'stock_transfers',
    'stock_transfer_items',
    'daily_credit_notes',
    'daily_credit_note_movements',
    'daily_pos_refunds',
    'daily_pos_returns',
    'daily_pos_authorizations',
    'daily_demo_case',
    'migrations'
]

# Add fin_ tables
fin_tables = [t[0] for t in cur_src.execute("SELECT name FROM sqlite_master WHERE type='table' AND name LIKE 'fin_%'").fetchall()]
tables_to_sync.extend(fin_tables)

print(f"Syncing {len(tables_to_sync)} operational & financial tables...")

for table in tables_to_sync:
    try:
        # Schema
        schema_row = cur_src.execute(f"SELECT sql FROM sqlite_master WHERE type='table' AND name='{table}'").fetchone()
        if not schema_row:
            continue
        schema = schema_row[0]
        
        cur_dst.execute(f'DROP TABLE IF EXISTS "{table}"')
        cur_dst.execute(schema)
        
        # Copy rows
        rows = cur_src.execute(f'SELECT * FROM "{table}"').fetchall()
        if rows:
            placeholders = ','.join(['?'] * len(rows[0]))
            cur_dst.executemany(f'INSERT INTO "{table}" VALUES ({placeholders})', rows)
            
        # Copy indices
        indices = cur_src.execute(f"SELECT sql FROM sqlite_master WHERE type='index' AND tbl_name='{table}' AND sql IS NOT NULL").fetchall()
        for (idx_sql,) in indices:
            try:
                cur_dst.execute(idx_sql)
            except Exception:
                pass
                
        print(f"  Synced {table}: {len(rows)} rows")
    except Exception as e:
        print(f"  Error syncing {table}: {e}")

# Now ensure wfm_employees has all 20 employees
EMPLOYEES_20 = [
    (101, 'EMP-ADM-01', 'Gonzalo Javier Núñez', 'admin', 'Gerente General', 'Administración & Finanzas', 'gnunez@princedistributors.com', '+51 999 101 001', 'full_time', 48.0, 12.00, 48.0, 'Directorio Corporativo', 'Centro Corporativo San Borja', 'Lima Centro'),
    (102, 'EMP-SUP-01', 'Carlos Mendoza', 'supervisor', 'Supervisor de Ventas Norte / Centro', 'Fuerza de Ventas B2B', 'cmendoza@princedistributors.com', '+51 999 102 002', 'full_time', 48.0, 10.50, 48.0, 'Gonzalo Javier Núñez', 'Almacén Central Norte', 'Lima Norte'),
    (103, 'EMP-VEN-01', 'Jorge Luis Quispe', 'sales_rep', 'Vendedor Comercial Mayorista', 'Fuerza de Ventas B2B', 'jquispe@princedistributors.com', '+51 984 103 003', 'full_time', 48.0, 9.00, 48.0, 'Carlos Mendoza', 'Almacén Central Norte', 'Lima Norte'),
    (104, 'EMP-VEN-02', 'Ana María Vásquez', 'sales_rep', 'Ejecutiva Cuentas Clave Retail', 'Fuerza de Ventas B2B', 'avasquez@princedistributors.com', '+51 984 104 004', 'full_time', 48.0, 9.00, 48.0, 'Carlos Mendoza', 'Supermercados Los Andes', 'Lima Centro'),
    (105, 'EMP-REP-01', 'Marcos Huamán', 'merchandiser', 'Reponedor Especialista Abarrotes', 'Reposición & Merchandising', 'mhuaman@princedistributors.com', '+51 984 105 005', 'full_time', 48.0, 7.50, 48.0, 'María Elena Torres', 'Almacén Central Norte', 'Lima Norte'),
    (106, 'EMP-VEN-03', 'Andrea Morales', 'sales_rep', 'Asesora Comercial Distribuidores', 'Fuerza de Ventas B2B', 'amorales@princedistributors.com', '+51 992 106 006', 'full_time', 48.0, 9.00, 48.0, 'Carlos Mendoza', 'Distribuidora San Juan', 'Lima Sur'),
    (107, 'EMP-VEN-04', 'Luis Fernando Castro', 'sales_rep', 'Asesor Comercial Mayorista Callao', 'Fuerza de Ventas B2B', 'lcastro@princedistributors.com', '+51 987 107 007', 'full_time', 48.0, 9.00, 48.0, 'Carlos Mendoza', 'Mega Market Colonial', 'Callao'),
    (108, 'EMP-VEN-05', 'Valeria Rojas', 'sales_rep', 'Ejecutiva Ventas Horeca & Institucional', 'Fuerza de Ventas B2B', 'vrojas@princedistributors.com', '+51 981 108 008', 'full_time', 48.0, 9.00, 48.0, 'Carlos Mendoza', 'Hipermercado Metro Plaza', 'Lima Este'),
    (109, 'EMP-REP-02', 'Rosa Luz Huamán', 'merchandiser', 'Reponedora Góndola & Lácteos', 'Reposición & Merchandising', 'rhuaman@princedistributors.com', '+51 984 109 009', 'full_time', 48.0, 7.50, 48.0, 'María Elena Torres', 'Supermercados Los Andes', 'Lima Centro'),
    (110, 'EMP-REP-03', 'Paola Cárdenas', 'merchandiser', 'Mercaderista Ruta Mayorista', 'Reposición & Merchandising', 'pcardenas@princedistributors.com', '+51 984 110 010', 'full_time', 48.0, 7.50, 48.0, 'María Elena Torres', 'Distribuidora San Juan', 'Lima Sur'),
    (111, 'EMP-REP-04', 'Renato Benítez', 'merchandiser', 'Reponedor Nocturno Descarga', 'Reposición & Merchandising', 'rbenitez@princedistributors.com', '+51 984 111 011', 'full_time', 48.0, 8.00, 48.0, 'María Elena Torres', 'Cash & Carry Fiori', 'Lima Norte'),
    (112, 'EMP-REP-05', 'Manuel Flores', 'warehouse', 'Jefe de Picking & Despacho Almacén', 'Operaciones & Logística', 'mflores@princedistributors.com', '+51 993 112 012', 'full_time', 48.0, 8.50, 48.0, 'María Elena Torres', 'Almacén Central Norte', 'Lima Norte'),
    (113, 'EMP-REP-06', 'Christian Vega', 'warehouse', 'Operador Montacargas & Racks', 'Operaciones & Logística', 'cvega@princedistributors.com', '+51 994 113 013', 'full_time', 48.0, 8.00, 48.0, 'María Elena Torres', 'Centro Logístico Huachipa', 'Lima Este'),
    (114, 'EMP-REP-07', 'Héctor Saldaña', 'warehouse', 'Auxiliar de Inventarios Cíclicos', 'Operaciones & Logística', 'hsaldana@princedistributors.com', '+51 995 114 014', 'full_time', 48.0, 7.50, 48.0, 'María Elena Torres', 'Tiendas Mass Express', 'Lima Sur'),
    (115, 'EMP-SUP-02', 'María Elena Torres', 'supervisor', 'Supervisora Operaciones & Almacenes', 'Operaciones & Logística', 'mtorres@princedistributors.com', '+51 999 115 015', 'full_time', 48.0, 10.50, 48.0, 'Gonzalo Javier Núñez', 'Centro Logístico Huachipa', 'Lima Este'),
    (116, 'EMP-SUP-03', 'David Alarcón', 'supervisor', 'Supervisor Ventas Lima Sur & Callao', 'Fuerza de Ventas B2B', 'dalarcon@princedistributors.com', '+51 999 116 016', 'full_time', 48.0, 10.50, 48.0, 'Gonzalo Javier Núñez', 'Mega Market Colonial', 'Callao'),
    (117, 'EMP-VEN-06', 'Diana Paredes', 'sales_rep', 'Preventista Ruta Lima Este', 'Fuerza de Ventas B2B', 'dparedes@princedistributors.com', '+51 987 117 017', 'full_time', 48.0, 8.50, 48.0, 'Carlos Mendoza', 'Hipermercado Metro Plaza', 'Lima Este'),
    (118, 'EMP-VEN-07', 'Javier Silva', 'sales_rep', 'Preventista Cobertura Lima Sur', 'Fuerza de Ventas B2B', 'jsilva@princedistributors.com', '+51 988 118 018', 'full_time', 48.0, 8.50, 48.0, 'David Alarcón', 'Tiendas Mass Express', 'Lima Sur'),
    (119, 'EMP-REP-08', 'Carmen Gómez', 'merchandiser', 'Mercaderista Tiendas de Conveniencia', 'Reposición & Merchandising', 'cgomez@princedistributors.com', '+51 989 119 019', 'full_time', 48.0, 7.50, 48.0, 'María Elena Torres', 'Cash & Carry Fiori', 'Lima Norte'),
    (120, 'EMP-REP-09', 'Roberto Farfán', 'merchandiser', 'Reponedor Estibas & Granel', 'Reposición & Merchandising', 'rfarfan@princedistributors.com', '+51 990 120 020', 'full_time', 48.0, 7.50, 48.0, 'María Elena Torres', 'Centro Logístico Huachipa', 'Lima Este')
]

cur_dst.execute("DELETE FROM wfm_employees")
cur_dst.executemany("""
    INSERT INTO wfm_employees (id, code, name, role, role_label, department, email, phone, contract_type, contracted_hours, hourly_cost, max_weekly_hours, supervisor_name, establishment_name, territory)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
""", EMPLOYEES_20)
print(f"Inserted {len(EMPLOYEES_20)} complete employees into wfm_employees.")

conn_dst.commit()
conn_src.close()

# Vacuum destination
print("\nRunning VACUUM on demo-seed/grocy_es.db...")
cur_dst.execute("VACUUM")
conn_dst.close()

size_mb = os.path.getsize(dst_path) / (1024 * 1024)
print(f"\nFinal demo-seed/grocy_es.db size: {size_mb:.2f} MB")
if size_mb < 99.0:
    print("✅ PERFECT: Under GitHub 100MB limit!")
else:
    print("⚠️ WARNING: Over 100MB limit!")
