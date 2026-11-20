-- =========================================================================
-- PRINCE DISTRIBUTORS — MIGRACIÓN 008: ROLLBACK CICLO DE VIDA DEL PRODUCTO
-- Reversible clean-up script
-- =========================================================================

DROP TABLE IF EXISTS prince_analytics_refresh_status CASCADE;
DROP TABLE IF EXISTS prince_data_imports CASCADE;
DROP TABLE IF EXISTS prince_markdown_scenarios CASCADE;
DROP TABLE IF EXISTS prince_commercial_recommendations CASCADE;
DROP TABLE IF EXISTS prince_demand_forecasts CASCADE;
DROP TABLE IF EXISTS prince_product_lifecycle_snapshots CASCADE;

DROP TYPE IF EXISTS prince_forecast_method;
DROP TYPE IF EXISTS prince_recommendation_status;
DROP TYPE IF EXISTS prince_recommendation_priority;
DROP TYPE IF EXISTS prince_lifecycle_stage;
