-- =========================================================================
-- PRINCE DISTRIBUTORS — MIGRACIÓN 007: GESTIÓN DEL CICLO DE VIDA DEL PRODUCTO
-- Inteligencia Comercial, Pronósticos, Recomendaciones, Markdown y Clasificación
-- Version: 20261120000001
-- Reversible: Ver 20261120000002_product_lifecycle_rollback.sql
-- =========================================================================

-- 1. TIPOS ENUMERADOS PARA CICLO DE VIDA
DO $$ BEGIN
    CREATE TYPE prince_lifecycle_stage AS ENUM ('introduction', 'growth', 'maturity', 'decline', 'exit_renewal', 'insufficient_data');
EXCEPTION
    WHEN duplicate_object THEN NULL;
END $$;

DO $$ BEGIN
    CREATE TYPE prince_recommendation_priority AS ENUM ('low', 'medium', 'high', 'urgent');
EXCEPTION
    WHEN duplicate_object THEN NULL;
END $$;

DO $$ BEGIN
    CREATE TYPE prince_recommendation_status AS ENUM ('pending', 'approved', 'rejected', 'executed');
EXCEPTION
    WHEN duplicate_object THEN NULL;
END $$;

DO $$ BEGIN
    CREATE TYPE prince_forecast_method AS ENUM ('moving_average', 'exponential_smoothing', 'linear_trend', 'hybrid_censored');
EXCEPTION
    WHEN duplicate_object THEN NULL;
END $$;

-- 2. SNAPSHOTS HISTÓRICOS DEL CICLO DE VIDA (MÉTRICAS & CLASIFICACIÓN)
CREATE TABLE IF NOT EXISTS prince_product_lifecycle_snapshots (
    id BIGSERIAL PRIMARY KEY,
    organization_id INT NOT NULL REFERENCES prince_organizations(id) ON DELETE CASCADE,
    branch_id INT REFERENCES prince_branches(id) ON DELETE CASCADE,
    product_id INT NOT NULL REFERENCES prince_products(id) ON DELETE CASCADE,
    sku VARCHAR(50) NOT NULL,
    snapshot_date DATE NOT NULL DEFAULT CURRENT_DATE,
    period_type VARCHAR(20) NOT NULL DEFAULT 'MTD', -- '7D', 'MTD', 'QTD', 'YTD'
    lifecycle_stage prince_lifecycle_stage NOT NULL DEFAULT 'insufficient_data',
    confidence_score NUMERIC(5,2) DEFAULT 0.00, -- 0 to 100%
    sales_units NUMERIC(12,2) DEFAULT 0.00,
    sales_amount NUMERIC(14,2) DEFAULT 0.00,
    cogs_amount NUMERIC(14,2) DEFAULT 0.00,
    gross_margin_pct NUMERIC(5,2) DEFAULT 0.00,
    current_stock_units NUMERIC(12,2) DEFAULT 0.00,
    inventory_value_cost NUMERIC(14,2) DEFAULT 0.00,
    dio_coverage_days NUMERIC(8,1) DEFAULT 0.0,
    turnover_ratio NUMERIC(8,2) DEFAULT 0.00,
    sell_through_pct NUMERIC(5,2) DEFAULT 0.00,
    gmroi NUMERIC(8,2) DEFAULT 0.00,
    gmros NUMERIC(10,2) DEFAULT 0.00, -- Gross Margin Return on Space ($ / linear meter)
    demand_trend_slope NUMERIC(8,4) DEFAULT 0.0000,
    stockout_days_in_period INT DEFAULT 0,
    is_immobilized_capital BOOLEAN DEFAULT FALSE,
    stockout_risk_score NUMERIC(5,2) DEFAULT 0.00,
    classification_reason TEXT,
    is_demo BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    CONSTRAINT uq_product_period UNIQUE (product_id, branch_id, snapshot_date, period_type)
);

-- 3. PRONÓSTICOS DE DEMANDA Y REPOSICIÓN
CREATE TABLE IF NOT EXISTS prince_demand_forecasts (
    id BIGSERIAL PRIMARY KEY,
    organization_id INT NOT NULL REFERENCES prince_organizations(id) ON DELETE CASCADE,
    branch_id INT REFERENCES prince_branches(id) ON DELETE CASCADE,
    product_id INT NOT NULL REFERENCES prince_products(id) ON DELETE CASCADE,
    sku VARCHAR(50) NOT NULL,
    forecast_date DATE NOT NULL DEFAULT CURRENT_DATE,
    forecast_horizon_days INT NOT NULL DEFAULT 30,
    method prince_forecast_method NOT NULL DEFAULT 'exponential_smoothing',
    historical_avg_daily_demand NUMERIC(10,2) NOT NULL DEFAULT 0.00,
    predicted_daily_demand NUMERIC(10,2) NOT NULL DEFAULT 0.00,
    predicted_total_demand NUMERIC(12,2) NOT NULL DEFAULT 0.00,
    confidence_interval_lower NUMERIC(12,2) DEFAULT 0.00,
    confidence_interval_upper NUMERIC(12,2) DEFAULT 0.00,
    wmape_accuracy_pct NUMERIC(5,2) DEFAULT 88.50,
    lead_time_days INT NOT NULL DEFAULT 7,
    safety_stock_units NUMERIC(10,2) NOT NULL DEFAULT 0.00,
    reorder_point NUMERIC(10,2) NOT NULL DEFAULT 0.00,
    current_inventory_position NUMERIC(10,2) NOT NULL DEFAULT 0.00,
    pending_orders_units NUMERIC(10,2) NOT NULL DEFAULT 0.00,
    suggested_replenishment_qty NUMERIC(10,2) NOT NULL DEFAULT 0.00,
    moq_units INT DEFAULT 12,
    case_pack_units INT DEFAULT 12,
    notes TEXT,
    is_demo BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- 4. RECOMENDACIONES DE INTELIGENCIA COMERCIAL
CREATE TABLE IF NOT EXISTS prince_commercial_recommendations (
    id BIGSERIAL PRIMARY KEY,
    uuid UUID DEFAULT uuid_generate_v4() UNIQUE,
    organization_id INT NOT NULL REFERENCES prince_organizations(id) ON DELETE CASCADE,
    branch_id INT REFERENCES prince_branches(id) ON DELETE CASCADE,
    product_id INT NOT NULL REFERENCES prince_products(id) ON DELETE CASCADE,
    sku VARCHAR(50) NOT NULL,
    rule_key VARCHAR(100) NOT NULL, -- e.g. 'ACCELERATED_GROWTH_RESTOCK', 'EXCESS_INVENTORY_LIQUIDATE'
    rule_category VARCHAR(50) NOT NULL, -- 'growth', 'decline', 'excess_stock', 'stockout_risk'
    priority prince_recommendation_priority NOT NULL DEFAULT 'medium',
    title VARCHAR(255) NOT NULL,
    problem_detected TEXT NOT NULL,
    evidence_data JSONB NOT NULL DEFAULT '{}'::jsonb,
    suggested_action TEXT NOT NULL,
    economic_impact_usd NUMERIC(12,2) DEFAULT 0.00,
    confidence_score NUMERIC(5,2) DEFAULT 85.00,
    status prince_recommendation_status NOT NULL DEFAULT 'pending',
    approved_by VARCHAR(255),
    approved_at TIMESTAMPTZ,
    approval_notes TEXT,
    outcome_result TEXT,
    is_demo BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- 5. ESCENARIOS Y SIMULACIONES DE MARKDOWN / LIQUIDACIÓN
CREATE TABLE IF NOT EXISTS prince_markdown_scenarios (
    id BIGSERIAL PRIMARY KEY,
    organization_id INT NOT NULL REFERENCES prince_organizations(id) ON DELETE CASCADE,
    branch_id INT REFERENCES prince_branches(id) ON DELETE CASCADE,
    product_id INT NOT NULL REFERENCES prince_products(id) ON DELETE CASCADE,
    sku VARCHAR(50) NOT NULL,
    original_price NUMERIC(10,2) NOT NULL,
    unit_cost NUMERIC(10,2) NOT NULL,
    current_stock NUMERIC(10,2) NOT NULL,
    proposed_discount_pct NUMERIC(5,2) NOT NULL,
    proposed_price NUMERIC(10,2) NOT NULL,
    estimated_elasticity NUMERIC(4,2) DEFAULT 1.50,
    projected_units_sold NUMERIC(10,2) NOT NULL,
    unit_margin_expected NUMERIC(10,2) NOT NULL,
    total_margin_projected NUMERIC(12,2) NOT NULL,
    recoverable_capital_usd NUMERIC(12,2) NOT NULL,
    margin_loss_usd NUMERIC(12,2) NOT NULL,
    breakeven_units NUMERIC(10,2) NOT NULL,
    status VARCHAR(50) DEFAULT 'draft', -- 'draft', 'submitted', 'approved', 'applied'
    created_by VARCHAR(255) DEFAULT 'Planificación Comercial',
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- 6. HISTORIAL DE CARGAS E IMPORTACIONES DE DATOS
CREATE TABLE IF NOT EXISTS prince_data_imports (
    id BIGSERIAL PRIMARY KEY,
    organization_id INT NOT NULL REFERENCES prince_organizations(id) ON DELETE CASCADE,
    filename VARCHAR(255) NOT NULL,
    file_type VARCHAR(50) NOT NULL, -- 'CSV', 'EXCEL', 'API'
    target_entity VARCHAR(100) NOT NULL DEFAULT 'product_lifecycle',
    total_rows INT NOT NULL DEFAULT 0,
    valid_rows INT NOT NULL DEFAULT 0,
    error_rows INT NOT NULL DEFAULT 0,
    status VARCHAR(50) NOT NULL DEFAULT 'completed', -- 'processing', 'completed', 'failed'
    column_mapping JSONB DEFAULT '{}'::jsonb,
    error_details JSONB DEFAULT '[]'::jsonb,
    imported_by VARCHAR(255) DEFAULT 'Carlos Mendoza',
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- 7. ESTADO DE ACTUALIZACIÓN ANALÍTICA INCREMENTAL
CREATE TABLE IF NOT EXISTS prince_analytics_refresh_status (
    id SERIAL PRIMARY KEY,
    organization_id INT NOT NULL REFERENCES prince_organizations(id) ON DELETE CASCADE,
    module_name VARCHAR(100) NOT NULL UNIQUE,
    last_refresh_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    refresh_duration_ms INT DEFAULT 0,
    status VARCHAR(50) DEFAULT 'idle',
    rows_processed INT DEFAULT 0,
    error_message TEXT,
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- 8. ÍNDICES DE ALTO RENDIMIENTO
CREATE INDEX IF NOT EXISTS idx_lifecycle_product ON prince_product_lifecycle_snapshots(product_id);
CREATE INDEX IF NOT EXISTS idx_lifecycle_stage ON prince_product_lifecycle_snapshots(lifecycle_stage);
CREATE INDEX IF NOT EXISTS idx_lifecycle_period ON prince_product_lifecycle_snapshots(period_type, snapshot_date);
CREATE INDEX IF NOT EXISTS idx_forecast_product ON prince_demand_forecasts(product_id, forecast_date);
CREATE INDEX IF NOT EXISTS idx_recs_status ON prince_commercial_recommendations(status, priority);
CREATE INDEX IF NOT EXISTS idx_recs_product ON prince_commercial_recommendations(product_id);
CREATE INDEX IF NOT EXISTS idx_markdown_product ON prince_markdown_scenarios(product_id);

-- 9. POLÍTICAS RLS (ROW LEVEL SECURITY)
ALTER TABLE prince_product_lifecycle_snapshots ENABLE ROW LEVEL SECURITY;
ALTER TABLE prince_demand_forecasts ENABLE ROW LEVEL SECURITY;
ALTER TABLE prince_commercial_recommendations ENABLE ROW LEVEL SECURITY;
ALTER TABLE prince_markdown_scenarios ENABLE ROW LEVEL SECURITY;
ALTER TABLE prince_data_imports ENABLE ROW LEVEL SECURITY;
ALTER TABLE prince_analytics_refresh_status ENABLE ROW LEVEL SECURITY;

-- Políticas de lectura y escritura para usuarios autorizados
DO $$ BEGIN
    CREATE POLICY p_lifecycle_select ON prince_product_lifecycle_snapshots
        FOR SELECT USING (true);
EXCEPTION WHEN duplicate_object THEN NULL; END $$;

DO $$ BEGIN
    CREATE POLICY p_lifecycle_modify ON prince_product_lifecycle_snapshots
        FOR ALL USING (auth.role() = 'authenticated');
EXCEPTION WHEN duplicate_object THEN NULL; END $$;

DO $$ BEGIN
    CREATE POLICY p_recs_select ON prince_commercial_recommendations
        FOR SELECT USING (true);
EXCEPTION WHEN duplicate_object THEN NULL; END $$;

DO $$ BEGIN
    CREATE POLICY p_recs_modify ON prince_commercial_recommendations
        FOR ALL USING (auth.role() = 'authenticated');
EXCEPTION WHEN duplicate_object THEN NULL; END $$;
