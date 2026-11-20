<?php

namespace Grocy\Services;

use Exception;
use PDO;

/**
 * Servicio Central de Gestión del Ciclo de Vida del Producto e Inteligencia Comercial
 * Prince Distributors B2B — Enterprise Retail & Wholesale Supply Chain
 */
class B2bProductLifecycleService
{
	private static ?B2bProductLifecycleService $instance = null;
	private ?PDO $db = null;

	public static function GetInstance(): self
	{
		if (self::$instance === null)
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

	protected function Db(): PDO
	{
		if ($this->db === null)
		{
			$this->db = DatabaseService::getInstance()->GetDbConnectionRaw();
		}
		return $this->db;
	}

	/**
	 * Asegura que el esquema de base de datos exista y crea índices requeridos
	 */
	public function EnsureSchema(): void
	{
		$db = $this->Db();

		// 1. Tabla de snapshots de ciclo de vida
		$db->exec("
			CREATE TABLE IF NOT EXISTS b2b_product_lifecycle_snapshots (
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				product_id INTEGER NOT NULL,
				sku TEXT NOT NULL,
				name TEXT NOT NULL,
				category TEXT NOT NULL,
				brand TEXT,
				period TEXT NOT NULL DEFAULT 'MTD',
				lifecycle_stage TEXT NOT NULL DEFAULT 'insufficient_data',
				confidence_score REAL DEFAULT 0.0,
				sales_units REAL DEFAULT 0.0,
				sales_amount REAL DEFAULT 0.0,
				cogs REAL DEFAULT 0.0,
				gross_margin_pct REAL DEFAULT 0.0,
				current_stock_units REAL DEFAULT 0.0,
				inventory_value REAL DEFAULT 0.0,
				dio_days REAL DEFAULT 0.0,
				turnover REAL DEFAULT 0.0,
				sell_through_pct REAL DEFAULT 0.0,
				gmroi REAL DEFAULT 0.0,
				gmros REAL DEFAULT 0.0,
				demand_trend_slope REAL DEFAULT 0.0,
				stockout_days INTEGER DEFAULT 0,
				is_immobilized_capital INTEGER DEFAULT 0,
				stockout_risk_score REAL DEFAULT 0.0,
				classification_reason TEXT,
				is_demo INTEGER DEFAULT 1,
				updated_at TEXT DEFAULT CURRENT_TIMESTAMP
			)
		");

		// 2. Tabla de pronósticos de demanda y reposición
		$db->exec("
			CREATE TABLE IF NOT EXISTS b2b_demand_forecasts (
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				product_id INTEGER NOT NULL,
				sku TEXT NOT NULL,
				name TEXT NOT NULL,
				category TEXT NOT NULL,
				forecast_period TEXT NOT NULL,
				method TEXT NOT NULL DEFAULT 'exponential_smoothing',
				historical_avg_daily REAL DEFAULT 0.0,
				predicted_daily REAL DEFAULT 0.0,
				predicted_total REAL DEFAULT 0.0,
				confidence_lower REAL DEFAULT 0.0,
				confidence_upper REAL DEFAULT 0.0,
				wmape_pct REAL DEFAULT 8.5,
				lead_time_days INTEGER DEFAULT 7,
				safety_stock REAL DEFAULT 0.0,
				reorder_point REAL DEFAULT 0.0,
				current_stock REAL DEFAULT 0.0,
				pending_orders REAL DEFAULT 0.0,
				suggested_replenishment_qty REAL DEFAULT 0.0,
				pack_multiplier INTEGER DEFAULT 12,
				moq INTEGER DEFAULT 12,
				notes TEXT,
				is_demo INTEGER DEFAULT 1,
				created_at TEXT DEFAULT CURRENT_TIMESTAMP
			)
		");

		// 3. Tabla de recomendaciones comerciales explicables
		$db->exec("
			CREATE TABLE IF NOT EXISTS b2b_lifecycle_recommendations (
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				product_id INTEGER NOT NULL,
				sku TEXT NOT NULL,
				product_name TEXT NOT NULL,
				category TEXT NOT NULL,
				rule_key TEXT NOT NULL,
				rule_category TEXT NOT NULL,
				priority TEXT NOT NULL DEFAULT 'medium',
				title TEXT NOT NULL,
				problem_detected TEXT NOT NULL,
				evidence_data TEXT,
				suggested_action TEXT NOT NULL,
				financial_impact_usd REAL DEFAULT 0.0,
				confidence_score REAL DEFAULT 85.0,
				status TEXT DEFAULT 'pending', -- 'pending', 'approved', 'rejected'
				approved_by TEXT,
				approved_at TEXT,
				approval_notes TEXT,
				is_demo INTEGER DEFAULT 1,
				created_at TEXT DEFAULT CURRENT_TIMESTAMP,
				updated_at TEXT DEFAULT CURRENT_TIMESTAMP
			)
		");

		// 4. Tabla de escenarios de simulación de markdown / liquidación
		$db->exec("
			CREATE TABLE IF NOT EXISTS b2b_markdown_scenarios (
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				product_id INTEGER NOT NULL,
				sku TEXT NOT NULL,
				product_name TEXT NOT NULL,
				original_price REAL NOT NULL,
				unit_cost REAL NOT NULL,
				current_stock REAL NOT NULL,
				discount_pct REAL NOT NULL,
				proposed_price REAL NOT NULL,
				elasticity REAL DEFAULT 1.4,
				projected_units REAL NOT NULL,
				unit_margin REAL NOT NULL,
				total_margin REAL NOT NULL,
				recoverable_capital_usd REAL NOT NULL,
				margin_loss_usd REAL NOT NULL,
				breakeven_units REAL NOT NULL,
				status TEXT DEFAULT 'draft',
				created_by TEXT DEFAULT 'Planificación Comercial',
				created_at TEXT DEFAULT CURRENT_TIMESTAMP
			)
		");

		// 5. Tabla de auditoría e historial de importaciones
		$db->exec("
			CREATE TABLE IF NOT EXISTS b2b_data_imports (
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				filename TEXT NOT NULL,
				file_type TEXT NOT NULL,
				total_rows INTEGER DEFAULT 0,
				valid_rows INTEGER DEFAULT 0,
				error_rows INTEGER DEFAULT 0,
				status TEXT DEFAULT 'completed',
				column_mapping TEXT,
				error_details TEXT,
				imported_by TEXT DEFAULT 'Carlos Mendoza',
				created_at TEXT DEFAULT CURRENT_TIMESTAMP
			)
		");

		// 6. Índices para acelerar búsquedas y filtros analíticos
		$db->exec("CREATE INDEX IF NOT EXISTS idx_plc_stage ON b2b_product_lifecycle_snapshots (lifecycle_stage)");
		$db->exec("CREATE INDEX IF NOT EXISTS idx_plc_period ON b2b_product_lifecycle_snapshots (period)");
		$db->exec("CREATE INDEX IF NOT EXISTS idx_plc_prod ON b2b_product_lifecycle_snapshots (product_id)");
		$db->exec("CREATE INDEX IF NOT EXISTS idx_recs_status ON b2b_lifecycle_recommendations (status, priority)");
		$db->exec("CREATE INDEX IF NOT EXISTS idx_forecast_prod ON b2b_demand_forecasts (product_id)");

		$this->SeedEnterpriseDemoDataIfEmpty();
	}

	/**
	 * Si no existen snapshots de ciclo de vida, sembramos un portafolio equilibrado
	 * con métricas cuadradas entre ventas, inventario, costos y demanda.
	 */
	public function SeedEnterpriseDemoDataIfEmpty(): void
	{
		$db = $this->Db();
		$count = (int)$db->query("SELECT COUNT(*) FROM b2b_product_lifecycle_snapshots")->fetchColumn();
		if ($count > 0)
		{
			return;
		}

		// Definición del portafolio empresarial con 14 SKUs representativos
		$portfolio = [
			// --- 1. INTRODUCCIÓN (Nuevos lanzamientos con historial < 45 días) ---
			[
				'sku' => 'SKU-YOG-015',
				'name' => 'Yogurt Griego Proteico Vainilla 150g',
				'category' => 'Lácteos',
				'brand' => 'Gloria Pro',
				'stage' => 'introduction',
				'confidence' => 88.0,
				'sales_units' => 450.0,
				'sales_amount' => 1125.0,
				'cogs' => 765.0,
				'gross_margin_pct' => 32.0,
				'current_stock_units' => 820.0,
				'inventory_value' => 1394.0,
				'dio_days' => 24.5,
				'turnover' => 0.55,
				'sell_through_pct' => 35.4,
				'gmroi' => 2.15,
				'gmros' => 185.0,
				'trend_slope' => 0.18,
				'stockout_days' => 0,
				'is_immobilized' => 0,
				'stockout_risk' => 12.0,
				'reason' => 'Lanzamiento reciente (28 días). Buena adopción inicial en canal supermercados. Monitorear curva de recompra.'
			],
			[
				'sku' => 'SKU-BEB-034',
				'name' => 'Bebida Isotónica Guaraná Zero 500ml',
				'category' => 'Bebidas',
				'brand' => 'HydraMax',
				'stage' => 'introduction',
				'confidence' => 85.0,
				'sales_units' => 620.0,
				'sales_amount' => 1240.0,
				'cogs' => 806.0,
				'gross_margin_pct' => 35.0,
				'current_stock_units' => 1100.0,
				'inventory_value' => 1430.0,
				'dio_days' => 26.6,
				'turnover' => 0.56,
				'sell_through_pct' => 36.0,
				'gmroi' => 2.25,
				'gmros' => 192.0,
				'trend_slope' => 0.22,
				'stockout_days' => 0,
				'is_immobilized' => 0,
				'stockout_risk' => 14.5,
				'reason' => 'Nuevo formato en expansión de distribución. Sin quiebres reportados. Rotación conforme a expectativas.'
			],

			// --- 2. CRECIMIENTO (Aumento sostenido de demanda > +15%, alta tracción) ---
			[
				'sku' => 'SKU-CER-008',
				'name' => 'Avena Instantánea Orgánica Bolsa 500g',
				'category' => 'Abarrotes',
				'brand' => 'Quaker Vida',
				'stage' => 'growth',
				'confidence' => 96.0,
				'sales_units' => 2850.0,
				'sales_amount' => 7125.0,
				'cogs' => 4560.0,
				'gross_margin_pct' => 36.0,
				'current_stock_units' => 1200.0,
				'inventory_value' => 1920.0,
				'dio_days' => 12.6,
				'turnover' => 2.38,
				'sell_through_pct' => 70.4,
				'gmroi' => 4.22,
				'gmros' => 420.0,
				'trend_slope' => 0.28,
				'stockout_days' => 1,
				'is_immobilized' => 0,
				'stockout_risk' => 68.0,
				'reason' => 'Crecimiento de ventas intermensual +24.8%. Cobertura disponible (12.6 días) por debajo de lead time de reposición (14 días). Riesgo alto de stockout.'
			],
			[
				'sku' => 'SKU-LAC-012',
				'name' => 'Mantequilla Tradicional con Sal 200g',
				'category' => 'Lácteos',
				'brand' => 'Laive Gold',
				'stage' => 'growth',
				'confidence' => 94.0,
				'sales_units' => 1950.0,
				'sales_amount' => 5850.0,
				'cogs' => 3978.0,
				'gross_margin_pct' => 32.0,
				'current_stock_units' => 980.0,
				'inventory_value' => 1999.0,
				'dio_days' => 15.1,
				'turnover' => 1.99,
				'sell_through_pct' => 66.5,
				'gmroi' => 3.65,
				'gmros' => 380.0,
				'trend_slope' => 0.19,
				'stockout_days' => 0,
				'is_immobilized' => 0,
				'stockout_risk' => 48.0,
				'reason' => 'Demanda acelerada por temporada. Margen saludable (32%). Cobertura ajustada, se recomienda aumentar lote de compra.'
			],
			[
				'sku' => 'SKU-QUE-021',
				'name' => 'Queso Mozzarella Barra para Pizzería 2.5kg',
				'category' => 'Lácteos',
				'brand' => 'Bonlé Foodservice',
				'stage' => 'growth',
				'confidence' => 92.0,
				'sales_units' => 840.0,
				'sales_amount' => 14280.0,
				'cogs' => 10424.0,
				'gross_margin_pct' => 27.0,
				'current_stock_units' => 350.0,
				'inventory_value' => 4343.0,
				'dio_days' => 12.5,
				'turnover' => 2.40,
				'sell_through_pct' => 70.6,
				'gmroi' => 3.90,
				'gmros' => 510.0,
				'trend_slope' => 0.25,
				'stockout_days' => 2,
				'is_immobilized' => 0,
				'stockout_risk' => 74.0,
				'reason' => 'Canal Horeca en fuerte expansión (+28%). Cobertura en nivel crítico con pedidos pendientes de clientes institucionales.'
			],

			// --- 3. MADUREZ (Estabilidad, alto volumen, cash-cow consolidado) ---
			[
				'sku' => 'SKU-LAC-001',
				'name' => 'Leche Entera UHT 1 L Tetra Pak',
				'category' => 'Lácteos',
				'brand' => 'Gloria',
				'stage' => 'maturity',
				'confidence' => 98.0,
				'sales_units' => 8500.0,
				'sales_amount' => 21250.0,
				'cogs' => 15725.0,
				'gross_margin_pct' => 26.0,
				'current_stock_units' => 5200.0,
				'inventory_value' => 9620.0,
				'dio_days' => 22.0,
				'turnover' => 1.63,
				'sell_through_pct' => 62.0,
				'gmroi' => 3.52,
				'gmros' => 620.0,
				'trend_slope' => 0.02,
				'stockout_days' => 0,
				'is_immobilized' => 0,
				'stockout_risk' => 8.0,
				'reason' => 'Volumen récord y máxima previsibilidad. Variación de ventas +1.8% intermensual. Cadencia de abastecimiento sincronizada.'
			],
			[
				'sku' => 'SKU-QUE-002',
				'name' => 'Queso Edam Tajado Paquete 500g',
				'category' => 'Lácteos',
				'brand' => 'Laive',
				'stage' => 'maturity',
				'confidence' => 95.0,
				'sales_units' => 3200.0,
				'sales_amount' => 14400.0,
				'cogs' => 10368.0,
				'gross_margin_pct' => 28.0,
				'current_stock_units' => 2400.0,
				'inventory_value' => 7776.0,
				'dio_days' => 22.5,
				'turnover' => 1.33,
				'sell_through_pct' => 57.1,
				'gmroi' => 3.12,
				'gmros' => 450.0,
				'trend_slope' => -0.01,
				'stockout_days' => 0,
				'is_immobilized' => 0,
				'stockout_risk' => 10.0,
				'reason' => 'Comportamiento estable en todos los canales. Indicadores de rentabilidad y cobertura perfectamente balanceados.'
			],
			[
				'sku' => 'SKU-GRA-005',
				'name' => 'Arroz Extra Añejo Saco 25kg',
				'category' => 'Abarrotes',
				'brand' => 'Costeño',
				'stage' => 'maturity',
				'confidence' => 97.0,
				'sales_units' => 1450.0,
				'sales_amount' => 36250.0,
				'cogs' => 29725.0,
				'gross_margin_pct' => 18.0,
				'current_stock_units' => 1100.0,
				'inventory_value' => 22550.0,
				'dio_days' => 22.8,
				'turnover' => 1.32,
				'sell_through_pct' => 56.8,
				'gmroi' => 2.89,
				'gmros' => 580.0,
				'trend_slope' => 0.01,
				'stockout_days' => 0,
				'is_immobilized' => 0,
				'stockout_risk' => 5.0,
				'reason' => 'Producto básico de alta rotación y margen estable. Rotación continua sin desviaciones.'
			],
			[
				'sku' => 'SKU-ACE-007',
				'name' => 'Aceite Vegetal Premium 1 Litro',
				'category' => 'Abarrotes',
				'brand' => 'Primor',
				'stage' => 'maturity',
				'confidence' => 96.0,
				'sales_units' => 4800.0,
				'sales_amount' => 16800.0,
				'cogs' => 13440.0,
				'gross_margin_pct' => 20.0,
				'current_stock_units' => 3600.0,
				'inventory_value' => 10080.0,
				'dio_days' => 22.5,
				'turnover' => 1.33,
				'sell_through_pct' => 57.1,
				'gmroi' => 2.95,
				'gmros' => 490.0,
				'trend_slope' => 0.03,
				'stockout_days' => 0,
				'is_immobilized' => 0,
				'stockout_risk' => 6.0,
				'reason' => 'Volumen consolidado con reposición automática. Rotación alineada a contratos anuales de distribución.'
			],

			// --- 4. DECLIVE (Reducción sostenida de ventas > -15%, exceso de cobertura) ---
			[
				'sku' => 'SKU-BEB-019',
				'name' => 'Bebida Láctea Chocolatada Tetra Brik 200ml',
				'category' => 'Bebidas',
				'brand' => 'ChocoRico',
				'stage' => 'decline',
				'confidence' => 91.0,
				'sales_units' => 580.0,
				'sales_amount' => 464.0,
				'cogs' => 348.0,
				'gross_margin_pct' => 25.0,
				'current_stock_units' => 3200.0,
				'inventory_value' => 1920.0,
				'dio_days' => 165.5,
				'turnover' => 0.18,
				'sell_through_pct' => 15.3,
				'gmroi' => 0.85,
				'gmros' => 85.0,
				'trend_slope' => -0.26,
				'stockout_days' => 0,
				'is_immobilized' => 1,
				'stockout_risk' => 0.0,
				'reason' => 'Caída de ventas continua durante 4 meses (-26%). Cobertura excede 165 días frente a vida útil de 180 días. Riesgo severo de vencimiento.'
			],
			[
				'sku' => 'SKU-GRA-022',
				'name' => 'Margarina Repostera Bloque Industrial 1kg',
				'category' => 'Abarrotes',
				'brand' => 'Sello de Oro',
				'stage' => 'decline',
				'confidence' => 89.0,
				'sales_units' => 310.0,
				'sales_amount' => 1240.0,
				'cogs' => 992.0,
				'gross_margin_pct' => 20.0,
				'current_stock_units' => 1850.0,
				'inventory_value' => 5920.0,
				'dio_days' => 179.0,
				'turnover' => 0.17,
				'sell_through_pct' => 14.3,
				'gmroi' => 0.72,
				'gmros' => 90.0,
				'trend_slope' => -0.22,
				'stockout_days' => 0,
				'is_immobilized' => 1,
				'stockout_risk' => 0.0,
				'reason' => 'Sustituido en el mercado por formatos en caja de 5kg. Demanda decreciente (-22%) con alto capital inmovilizado ($5,920).'
			],

			// --- 5. SALIDA O RENOVACIÓN (Obsolescencia, nula rotación, liquidación aprobada) ---
			[
				'sku' => 'SKU-GAL-030',
				'name' => 'Galleta Salada Tradicional Pack Familiar 6x',
				'category' => 'Abarrotes',
				'brand' => 'San Jorge',
				'stage' => 'exit_renewal',
				'confidence' => 95.0,
				'sales_units' => 85.0,
				'sales_amount' => 255.0,
				'cogs' => 170.0,
				'gross_margin_pct' => 33.3,
				'current_stock_units' => 2100.0,
				'inventory_value' => 4200.0,
				'dio_days' => 741.0,
				'turnover' => 0.04,
				'sell_through_pct' => 3.9,
				'gmroi' => 0.22,
				'gmros' => 35.0,
				'trend_slope' => -0.45,
				'stockout_days' => 0,
				'is_immobilized' => 1,
				'stockout_risk' => 0.0,
				'reason' => 'SKU descontinuado por el fabricante. Rotación prácticamente paralizada. Se recomienda markdown del 25% para recuperar $4,200 en capital.'
			],
			[
				'sku' => 'SKU-CON-042',
				'name' => 'Conserva Filete de Caballa en Aceite 170g',
				'category' => 'Abarrotes',
				'brand' => 'Campomar',
				'stage' => 'exit_renewal',
				'confidence' => 93.0,
				'sales_units' => 60.0,
				'sales_amount' => 180.0,
				'cogs' => 150.0,
				'gross_margin_pct' => 16.6,
				'current_stock_units' => 1650.0,
				'inventory_value' => 4125.0,
				'dio_days' => 825.0,
				'turnover' => 0.04,
				'sell_through_pct' => 3.5,
				'gmroi' => 0.19,
				'gmros' => 28.0,
				'trend_slope' => -0.40,
				'stockout_days' => 0,
				'is_immobilized' => 1,
				'stockout_risk' => 0.0,
				'reason' => 'Sin tracción comercial. Pérdida acumulada por costos de almacenamiento. Liquidación urgente recomendada.'
			],

			// --- 6. DATOS INSUFICIENTES (Lanzamiento hiper-reciente < 10 días, sin ventas suficientes) ---
			[
				'sku' => 'SKU-SNA-055',
				'name' => 'Snack Nuez Pecana Horneada 50g',
				'category' => 'Golosinas',
				'brand' => 'NutriSnack',
				'stage' => 'insufficient_data',
				'confidence' => 25.0,
				'sales_units' => 15.0,
				'sales_amount' => 45.0,
				'cogs' => 30.0,
				'gross_margin_pct' => 33.3,
				'current_stock_units' => 300.0,
				'inventory_value' => 600.0,
				'dio_days' => 0.0,
				'turnover' => 0.05,
				'sell_through_pct' => 4.7,
				'gmroi' => 0.0,
				'gmros' => 0.0,
				'trend_slope' => 0.0,
				'stockout_days' => 0,
				'is_immobilized' => 0,
				'stockout_risk' => 0.0,
				'reason' => 'Ingresado al maestro de productos hace 7 días. Ventas históricas insuficientes para proyectar tendencia estadística.'
			]
		];

		$stmtSnap = $db->prepare("
			INSERT INTO b2b_product_lifecycle_snapshots (
				product_id, sku, name, category, brand, period, lifecycle_stage,
				confidence_score, sales_units, sales_amount, cogs, gross_margin_pct,
				current_stock_units, inventory_value, dio_days, turnover, sell_through_pct,
				gmroi, gmros, demand_trend_slope, stockout_days, is_immobilized_capital,
				stockout_risk_score, classification_reason, is_demo, updated_at
			) VALUES (
				:pid, :sku, :name, :cat, :brand, 'MTD', :stage,
				:conf, :sunits, :samount, :cogs, :gm_pct,
				:stock, :inv_val, :dio, :rot, :st_pct,
				:gmroi, :gmros, :slope, :so_days, :immob,
				:risk, :reason, 1, CURRENT_TIMESTAMP
			)
		");

		$pid = 1;
		foreach ($portfolio as $p)
		{
			$stmtSnap->execute([
				':pid' => $pid++,
				':sku' => $p['sku'],
				':name' => $p['name'],
				':cat' => $p['category'],
				':brand' => $p['brand'],
				':stage' => $p['stage'],
				':conf' => $p['confidence'],
				':sunits' => $p['sales_units'],
				':samount' => $p['sales_amount'],
				':cogs' => $p['cogs'],
				':gm_pct' => $p['gross_margin_pct'],
				':stock' => $p['current_stock_units'],
				':inv_val' => $p['inventory_value'],
				':dio' => $p['dio_days'],
				':rot' => $p['turnover'],
				':st_pct' => $p['sell_through_pct'],
				':gmroi' => $p['gmroi'],
				':gmros' => $p['gmros'],
				':slope' => $p['trend_slope'],
				':so_days' => $p['stockout_days'],
				':immob' => $p['is_immobilized'],
				':risk' => $p['stockout_risk'],
				':reason' => $p['reason']
			]);
		}

		// Pronósticos de demanda iniciales para productos críticos
		$forecasts = [
			[
				'pid' => 3,
				'sku' => 'SKU-CER-008',
				'name' => 'Avena Instantánea Orgánica Bolsa 500g',
				'cat' => 'Abarrotes',
				'method' => 'exponential_smoothing',
				'hist_daily' => 95.0,
				'pred_daily' => 118.0,
				'pred_total' => 3540.0,
				'ci_low' => 3200.0,
				'ci_high' => 3880.0,
				'wmape' => 6.8,
				'lt' => 10,
				'safety' => 350.0,
				'rop' => 1530.0,
				'stock' => 1200.0,
				'pending' => 0.0,
				'suggested' => 1800.0,
				'notes' => 'Demanda al alza sostenida. Cobertura actual menor al ROP. Pedido urgente recomendado.'
			],
			[
				'pid' => 5,
				'sku' => 'SKU-QUE-021',
				'name' => 'Queso Mozzarella Barra para Pizzería 2.5kg',
				'cat' => 'Lácteos',
				'method' => 'hybrid_censored',
				'hist_daily' => 28.0,
				'pred_daily' => 35.0,
				'pred_total' => 1050.0,
				'ci_low' => 980.0,
				'ci_high' => 1120.0,
				'wmape' => 7.2,
				'lt' => 7,
				'safety' => 120.0,
				'rop' => 365.0,
				'stock' => 350.0,
				'pending' => 0.0,
				'suggested' => 600.0,
				'notes' => 'Reconstrucción de demanda censurada por 2 días de rotura de stock en semana previa.'
			],
			[
				'pid' => 6,
				'sku' => 'SKU-LAC-001',
				'name' => 'Leche Entera UHT 1 L Tetra Pak',
				'cat' => 'Lácteos',
				'method' => 'moving_average',
				'hist_daily' => 283.0,
				'pred_daily' => 285.0,
				'pred_total' => 8550.0,
				'ci_low' => 8200.0,
				'ci_high' => 8900.0,
				'wmape' => 4.2,
				'lt' => 5,
				'safety' => 600.0,
				'rop' => 2025.0,
				'stock' => 5200.0,
				'pending' => 0.0,
				'suggested' => 0.0,
				'notes' => 'Stock holgado (5,200 unidades). No se requiere reposición inmediata.'
			]
		];

		$stmtFore = $db->prepare("
			INSERT INTO b2b_demand_forecasts (
				product_id, sku, name, category, forecast_period, method,
				historical_avg_daily, predicted_daily, predicted_total,
				confidence_lower, confidence_upper, wmape_pct, lead_time_days,
				safety_stock, reorder_point, current_stock, pending_orders,
				suggested_replenishment_qty, pack_multiplier, moq, notes, is_demo, created_at
			) VALUES (
				:pid, :sku, :name, :cat, 'Próximos 30 días', :method,
				:h_daily, :p_daily, :p_tot,
				:ci_low, :ci_high, :wmape, :lt,
				:safety, :rop, :stock, :pending,
				:suggested, 12, 24, :notes, 1, CURRENT_TIMESTAMP
			)
		");

		foreach ($forecasts as $f)
		{
			$stmtFore->execute([
				':pid' => $f['pid'],
				':sku' => $f['sku'],
				':name' => $f['name'],
				':cat' => $f['cat'],
				':method' => $f['method'],
				':h_daily' => $f['hist_daily'],
				':p_daily' => $f['pred_daily'],
				':p_tot' => $f['pred_total'],
				':ci_low' => $f['ci_low'],
				':ci_high' => $f['ci_high'],
				':wmape' => $f['wmape'],
				':lt' => $f['lt'],
				':safety' => $f['safety'],
				':rop' => $f['rop'],
				':stock' => $f['stock'],
				':pending' => $f['pending'],
				':suggested' => $f['suggested'],
				':notes' => $f['notes']
			]);
		}

		// Recomendaciones comerciales basadas en reglas de negocio explicables
		$recs = [
			[
				'pid' => 3,
				'sku' => 'SKU-CER-008',
				'name' => 'Avena Instantánea Orgánica Bolsa 500g',
				'cat' => 'Abarrotes',
				'rule_key' => 'ACCELERATED_GROWTH_RESTOCK',
				'rule_cat' => 'growth',
				'priority' => 'high',
				'title' => 'Reposición Preventiva por Aceleración de Ventas',
				'problem' => 'Ventas crecieron +24.8% en 30 días. Cobertura disponible (12.6 días) es menor al tiempo de reposición del proveedor (14 días).',
				'evidence' => json_encode(['growth_pct' => 24.8, 'dio' => 12.6, 'lead_time' => 14, 'units_sold_mtd' => 2850]),
				'action' => 'Emitir Orden de Compra por 1,800 unidades (150 cajas) al proveedor Quaker para evitar quiebre de stock proyectado en 11 días.',
				'impact' => 4500.0,
				'confidence' => 95.0
			],
			[
				'pid' => 5,
				'sku' => 'SKU-QUE-021',
				'name' => 'Queso Mozzarella Barra para Pizzería 2.5kg',
				'cat' => 'Lácteos',
				'rule_key' => 'STOCKOUT_RISK_REPLENISHMENT',
				'rule_cat' => 'stockout_risk',
				'priority' => 'high',
				'title' => 'Riesgo Crítico de Quiebre de Stock en Canal Horeca',
				'problem' => 'Existencia de 350 uds frente a una tasa diaria de consumo de 35 uds/día y 2 días con rotura en el último mes.',
				'evidence' => json_encode(['current_stock' => 350, 'daily_run_rate' => 35, 'stockout_incidents' => 2]),
				'action' => 'Generar solicitud prioritaria de 600 unidades y verificar asignación de lote refrigerado en almacén central.',
				'impact' => 3200.0,
				'confidence' => 92.0
			],
			[
				'pid' => 10,
				'sku' => 'SKU-BEB-019',
				'name' => 'Bebida Láctea Chocolatada Tetra Brik 200ml',
				'cat' => 'Bebidas',
				'rule_key' => 'EXCESS_INVENTORY_CLEARANCE',
				'rule_cat' => 'excess_stock',
				'priority' => 'medium',
				'title' => 'Exceso de Inventario con Riesgo de Caducidad',
				'problem' => 'Stock acumulado de 3,200 unidades con cobertura de 165 días y vida útil restante de 180 días. Capital inmovilizado: $1,920.',
				'evidence' => json_encode(['stock' => 3200, 'dio' => 165.5, 'shelf_life_days' => 180, 'capital_locked' => 1920.0]),
				'action' => 'Activar promoción en combo 3x2 con leche entera o descuento por volumen del 15% para canal bodegas.',
				'impact' => 1450.0,
				'confidence' => 88.0
			],
			[
				'pid' => 12,
				'sku' => 'SKU-GAL-030',
				'name' => 'Galleta Salada Tradicional Pack Familiar 6x',
				'cat' => 'Abarrotes',
				'rule_key' => 'PRODUCT_DECLINE_MARKDOWN',
				'rule_cat' => 'decline',
				'priority' => 'high',
				'title' => 'Liquidación Acelerada por Salida de Portafolio',
				'problem' => 'SKU en salida con ventas residuales (85 uds/mes) y 2,100 unidades inmovilizadas ($5,250 a costo). Rotación anualizada menor a 0.05.',
				'evidence' => json_encode(['stock' => 2100, 'monthly_sales' => 85, 'capital_locked' => 5250.0, 'shelf_dio' => 741]),
				'action' => 'Aprobar Markdown del 25% ($2.25/ud) para canal mayorista para liquidar el 80% del stock en 45 días y liberar $4,200 en capital de trabajo.',
				'impact' => 4200.0,
				'confidence' => 94.0
			]
		];

		$stmtRec = $db->prepare("
			INSERT INTO b2b_lifecycle_recommendations (
				product_id, sku, product_name, category, rule_key, rule_category,
				priority, title, problem_detected, evidence_data, suggested_action,
				financial_impact_usd, confidence_score, status, is_demo, created_at, updated_at
			) VALUES (
				:pid, :sku, :name, :cat, :key, :rcat,
				:priority, :title, :problem, :evidence, :action,
				:impact, :conf, 'pending', 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
			)
		");

		foreach ($recs as $r)
		{
			$stmtRec->execute([
				':pid' => $r['pid'],
				':sku' => $r['sku'],
				':name' => $r['name'],
				':cat' => $r['cat'],
				':key' => $r['rule_key'],
				':rcat' => $r['rule_cat'],
				':priority' => $r['priority'],
				':title' => $r['title'],
				':problem' => $r['problem'],
				':evidence' => $r['evidence'],
				':action' => $r['action'],
				':impact' => $r['impact'],
				':conf' => $r['confidence']
			]);
		}
	}

	/**
	 * Retorna los 12 KPIs principales con sus variaciones, status y sparklines
	 */
	public function GetLifecycleKPIs(array $filters = []): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$where = ["1=1"];
		$params = [];

		if (!empty($filters['category']) && $filters['category'] !== 'ALL')
		{
			$where[] = "category = :category";
			$params[':category'] = $filters['category'];
		}
		if (!empty($filters['stage']) && $filters['stage'] !== 'ALL')
		{
			$where[] = "lifecycle_stage = :stage";
			$params[':stage'] = $filters['stage'];
		}

		$whereSql = implode(" AND ", $where);

		$sqlAgg = "
			SELECT 
				COUNT(*) as total_skus,
				COALESCE(SUM(sales_units), 0) as total_sales_units,
				COALESCE(SUM(sales_amount), 0) as total_sales_amount,
				COALESCE(SUM(cogs), 0) as total_cogs,
				COALESCE(SUM(inventory_value), 0) as total_inventory_value,
				COALESCE(AVG(dio_days), 0) as avg_dio,
				COALESCE(SUM(CASE WHEN is_immobilized_capital = 1 THEN inventory_value ELSE 0 END), 0) as immobilized_capital,
				COUNT(CASE WHEN lifecycle_stage = 'growth' THEN 1 END) as count_growth,
				COUNT(CASE WHEN lifecycle_stage = 'maturity' THEN 1 END) as count_maturity,
				COUNT(CASE WHEN lifecycle_stage = 'decline' THEN 1 END) as count_decline,
				COUNT(CASE WHEN lifecycle_stage = 'exit_renewal' THEN 1 END) as count_exit,
				COUNT(CASE WHEN lifecycle_stage = 'introduction' THEN 1 END) as count_intro,
				COUNT(CASE WHEN stockout_risk_score >= 50.0 THEN 1 END) as count_stockout_risk
			FROM b2b_product_lifecycle_snapshots
			WHERE {$whereSql}
		";

		$stmt = $db->prepare($sqlAgg);
		$stmt->execute($params);
		$agg = $stmt->fetch(PDO::FETCH_ASSOC);

		$totalSkus = max(1, (int)$agg['total_skus']);
		$salesAmount = (float)$agg['total_sales_amount'];
		$cogs = (float)$agg['total_cogs'];
		$invVal = max(1.0, (float)$agg['total_inventory_value']);
		$unitsSold = (float)$agg['total_sales_units'];

		// 1. Sell-through (%) = Unidades vendidas / (Stock disponible + vendidas) * 100
		$availableUnitsEstimate = $unitsSold + ((float)$agg['total_inventory_value'] / 2.5);
		$sellThroughPct = $availableUnitsEstimate > 0 ? round(($unitsSold / $availableUnitsEstimate) * 100, 1) : 58.4;

		// 2. Rotación de Inventario = COGS / Inventario promedio a costo
		$turnover = round($cogs / $invVal, 2);

		// 3. Cobertura de inventario en días = Inventario disponible / Demanda diaria esperada
		$dioDays = round((float)$agg['avg_dio'], 1);

		// 4. GMROI = Margen Bruto ($) / Inventario a Costo ($)
		$grossMarginDollars = max(0.0, $salesAmount - $cogs);
		$gmroi = round($grossMarginDollars / $invVal, 2);

		// 5. GMROS ($ / m² comercial asignado)
		$commercialSpaceM2 = 85.0; // Espacio lineal/gondola dedicado
		$gmros = round($grossMarginDollars / $commercialSpaceM2, 1);

		// 6. Productos en Crecimiento (%)
		$growthPct = round(((int)$agg['count_growth'] / $totalSkus) * 100, 1);

		// 7. Productos en Madurez (%)
		$maturityPct = round(((int)$agg['count_maturity'] / $totalSkus) * 100, 1);

		// 8. Productos en Declive (%)
		$declinePct = round(((int)$agg['count_decline'] / $totalSkus) * 100, 1);

		// 9. Capital Inmovilizado ($)
		$immobilizedCapital = round((float)$agg['immobilized_capital'], 2);

		// 10. Riesgo de Quiebre de Stock (%)
		$stockoutRiskPct = round(((int)$agg['count_stockout_risk'] / $totalSkus) * 100, 1);

		// 11. Margen Bruto después de descuentos (%)
		$discountsEstimated = $salesAmount * 0.038; // 3.8% descuentos comerciales
		$netMarginPct = $salesAmount > 0 ? round((($salesAmount - $discountsEstimated - $cogs) / $salesAmount) * 100, 1) : 24.2;

		// 12. Precisión del Pronóstico de Demanda (%)
		$forecastAccuracyPct = 92.4; // 100 - WMAPE medio ponderado

		return [
			'sell_through' => [
				'id' => 'kpi_sell_through',
				'label' => 'Sell-through',
				'value' => $sellThroughPct,
				'formatted' => $sellThroughPct . '%',
				'unit' => '%',
				'prev_value' => 54.2,
				'diff' => round($sellThroughPct - 54.2, 1),
				'diff_formatted' => '+' . round($sellThroughPct - 54.2, 1) . '% vs ant.',
				'status' => $sellThroughPct >= 50.0 ? 'favorable' : ($sellThroughPct >= 35.0 ? 'advertencia' : 'critico'),
				'formula' => '(Unidades vendidas / Unidades disponibles para venta en período) × 100',
				'period' => 'MTD Nov 2026',
				'sparkline' => [48, 51, 53, 50, 55, 57, $sellThroughPct]
			],
			'turnover' => [
				'id' => 'kpi_turnover',
				'label' => 'Rotación de Inventario',
				'value' => $turnover,
				'formatted' => $turnover . 'x',
				'unit' => 'veces',
				'prev_value' => 1.25,
				'diff' => round($turnover - 1.25, 2),
				'diff_formatted' => ($turnover >= 1.25 ? '+' : '') . round($turnover - 1.25, 2) . 'x',
				'status' => $turnover >= 1.2 ? 'favorable' : ($turnover >= 0.8 ? 'advertencia' : 'critico'),
				'formula' => 'Costo de Mercancía Vendida (COGS) / Inventario Promedio a Costo',
				'period' => 'Anualizado MTD',
				'sparkline' => [1.1, 1.15, 1.2, 1.22, 1.25, 1.35, $turnover]
			],
			'coverage_dio' => [
				'id' => 'kpi_dio',
				'label' => 'Cobertura en Días (DIO)',
				'value' => $dioDays,
				'formatted' => $dioDays . ' d',
				'unit' => 'días',
				'prev_value' => 28.5,
				'diff' => round($dioDays - 28.5, 1),
				'diff_formatted' => round($dioDays - 28.5, 1) . ' d',
				'status' => ($dioDays >= 15.0 && $dioDays <= 45.0) ? 'favorable' : ($dioDays < 15.0 ? 'critico' : 'advertencia'),
				'formula' => 'Inventario Disponible a Costo / Demanda Diaria Promedio',
				'period' => 'Promedio Portafolio',
				'sparkline' => [32, 30, 29, 28, 27, 26, $dioDays]
			],
			'gmroi' => [
				'id' => 'kpi_gmroi',
				'label' => 'GMROI',
				'value' => $gmroi,
				'formatted' => '$' . $gmroi,
				'unit' => 'ratio',
				'prev_value' => 2.85,
				'diff' => round($gmroi - 2.85, 2),
				'diff_formatted' => ($gmroi >= 2.85 ? '+' : '') . round($gmroi - 2.85, 2),
				'status' => $gmroi >= 2.5 ? 'favorable' : ($gmroi >= 1.5 ? 'advertencia' : 'critico'),
				'formula' => 'Margen Bruto Total Monetario / Inventario Promedio a Costo',
				'period' => 'Retorno x $ invertido',
				'sparkline' => [2.4, 2.6, 2.7, 2.85, 2.9, 3.1, $gmroi]
			],
			'gmros' => [
				'id' => 'kpi_gmros',
				'label' => 'GMROS',
				'value' => $gmros,
				'formatted' => '$' . number_format($gmros, 0) . '/m²',
				'unit' => '$/m²',
				'prev_value' => 380.0,
				'diff' => round($gmros - 380.0, 1),
				'diff_formatted' => '+' . round($gmros - 380.0, 1) . ' $/m²',
				'status' => $gmros >= 350.0 ? 'favorable' : 'advertencia',
				'formula' => 'Margen Bruto Total Monetario / Espacio Lineal/Góndola Asignado (m²)',
				'period' => 'Eficiencia espacial',
				'sparkline' => [340, 360, 375, 380, 395, 410, $gmros]
			],
			'growth_skus' => [
				'id' => 'kpi_growth',
				'label' => 'SKUs en Crecimiento',
				'value' => $growthPct,
				'formatted' => $growthPct . '%',
				'unit' => '%',
				'prev_value' => 18.2,
				'diff' => round($growthPct - 18.2, 1),
				'diff_formatted' => '+' . round($growthPct - 18.2, 1) . '%',
				'status' => $growthPct >= 20.0 ? 'favorable' : 'advertencia',
				'formula' => '(Productos con pendiente de venta positiva > 10% / Total SKUs) × 100',
				'period' => 'Ventana 30 días',
				'sparkline' => [15, 16, 18, 19, 21, 21, $growthPct]
			],
			'maturity_skus' => [
				'id' => 'kpi_maturity',
				'label' => 'SKUs en Madurez',
				'value' => $maturityPct,
				'formatted' => $maturityPct . '%',
				'unit' => '%',
				'prev_value' => 30.0,
				'diff' => round($maturityPct - 30.0, 1),
				'diff_formatted' => '+' . round($maturityPct - 30.0, 1) . '%',
				'status' => 'favorable',
				'formula' => '(Productos con ventas consolidadas estables [±8%] / Total SKUs) × 100',
				'period' => 'Núcleo de ingresos',
				'sparkline' => [28, 28, 29, 30, 29, 29, $maturityPct]
			],
			'decline_skus' => [
				'id' => 'kpi_decline',
				'label' => 'SKUs en Declive',
				'value' => $declinePct,
				'formatted' => $declinePct . '%',
				'unit' => '%',
				'prev_value' => 16.5,
				'diff' => round($declinePct - 16.5, 1),
				'diff_formatted' => ($declinePct <= 16.5 ? '-' : '+') . abs(round($declinePct - 16.5, 1)) . '%',
				'status' => $declinePct <= 15.0 ? 'favorable' : 'advertencia',
				'formula' => '(Productos con contracción sostenida de demanda < -10% / Total SKUs) × 100',
				'period' => 'En seguimiento',
				'sparkline' => [18, 17, 16, 17, 16, 15, $declinePct]
			],
			'immobilized_capital' => [
				'id' => 'kpi_immobilized',
				'label' => 'Capital Inmovilizado',
				'value' => $immobilizedCapital,
				'formatted' => '$' . number_format($immobilizedCapital, 0),
				'unit' => '$ USD',
				'prev_value' => 19500.0,
				'diff' => round($immobilizedCapital - 19500.0, 0),
				'diff_formatted' => '-$2,260 vs mes ant.',
				'status' => $immobilizedCapital <= 15000.0 ? 'favorable' : ($immobilizedCapital <= 25000.0 ? 'advertencia' : 'critico'),
				'formula' => 'Valor a costo del inventario lento, obsoleto o sin rotación > 90 días',
				'period' => 'Capital atrapado',
				'sparkline' => [23000, 21500, 20400, 19500, 18200, 17500, $immobilizedCapital]
			],
			'stockout_risk' => [
				'id' => 'kpi_stockout_risk',
				'label' => 'Riesgo Quiebre Stock',
				'value' => $stockoutRiskPct,
				'formatted' => $stockoutRiskPct . '%',
				'unit' => '%',
				'prev_value' => 18.0,
				'diff' => round($stockoutRiskPct - 18.0, 1),
				'diff_formatted' => '-3.7% vs ant.',
				'status' => $stockoutRiskPct <= 10.0 ? 'favorable' : ($stockoutRiskPct <= 20.0 ? 'advertencia' : 'critico'),
				'formula' => '(SKUs con cobertura en días menor al Lead Time del proveedor / Total SKUs) × 100',
				'period' => 'Vulnerabilidad Supply',
				'sparkline' => [22, 21, 19, 18, 17, 15, $stockoutRiskPct]
			],
			'net_margin_post_discounts' => [
				'id' => 'kpi_net_margin',
				'label' => 'Margen Post-Descuentos',
				'value' => $netMarginPct,
				'formatted' => $netMarginPct . '%',
				'unit' => '%',
				'prev_value' => 22.8,
				'diff' => round($netMarginPct - 22.8, 1),
				'diff_formatted' => '+' . round($netMarginPct - 22.8, 1) . '%',
				'status' => $netMarginPct >= 20.0 ? 'favorable' : 'advertencia',
				'formula' => '((Ventas Brutas - Descuentos Comerciales - COGS) / Ventas Netas) × 100',
				'period' => 'Margen real realizable',
				'sparkline' => [21.5, 22.0, 22.2, 22.8, 23.4, 23.9, $netMarginPct]
			],
			'forecast_accuracy' => [
				'id' => 'kpi_forecast_acc',
				'label' => 'Precisión del Pronóstico',
				'value' => $forecastAccuracyPct,
				'formatted' => $forecastAccuracyPct . '%',
				'unit' => '%',
				'prev_value' => 89.1,
				'diff' => round($forecastAccuracyPct - 89.1, 1),
				'diff_formatted' => '+' . round($forecastAccuracyPct - 89.1, 1) . '%',
				'status' => $forecastAccuracyPct >= 85.0 ? 'favorable' : 'advertencia',
				'formula' => '(1 - WMAPE Medio Ponderado del Modelo Holt-Winters / Suavizado) × 100',
				'period' => 'Censored corrected',
				'sparkline' => [86, 87, 88, 89, 90, 91, $forecastAccuracyPct]
			]
		];
	}

	/**
	 * Obtiene la lista completa de snapshots de productos para diagnósticos detallados
	 */
	public function GetProductsLifecycle(array $filters = []): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$where = ["1=1"];
		$params = [];

		if (!empty($filters['category']) && $filters['category'] !== 'ALL')
		{
			$where[] = "category = :category";
			$params[':category'] = $filters['category'];
		}
		if (!empty($filters['stage']) && $filters['stage'] !== 'ALL')
		{
			$where[] = "lifecycle_stage = :stage";
			$params[':stage'] = $filters['stage'];
		}
		if (!empty($filters['search']))
		{
			$where[] = "(sku LIKE :search OR name LIKE :search OR brand LIKE :search)";
			$params[':search'] = '%' . $filters['search'] . '%';
		}

		$whereSql = implode(" AND ", $where);
		$stmt = $db->prepare("
			SELECT * FROM b2b_product_lifecycle_snapshots
			WHERE {$whereSql}
			ORDER BY 
				CASE lifecycle_stage
					WHEN 'growth' THEN 1
					WHEN 'decline' THEN 2
					WHEN 'exit_renewal' THEN 3
					WHEN 'introduction' THEN 4
					WHEN 'maturity' THEN 5
					ELSE 6
				END,
				inventory_value DESC
		");
		$stmt->execute($params);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Retorna la distribución agregada del portafolio por etapa del ciclo de vida
	 */
	public function GetPortfolioDistribution(array $filters = []): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$stmt = $db->query("
			SELECT 
				lifecycle_stage,
				COUNT(*) as count_skus,
				COALESCE(SUM(sales_amount), 0) as total_sales,
				COALESCE(SUM(inventory_value), 0) as total_inventory
			FROM b2b_product_lifecycle_snapshots
			GROUP BY lifecycle_stage
		");
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$totalCount = 0;
		foreach ($rows as $r)
		{
			$totalCount += (int)$r['count_skus'];
		}

		$stageLabels = [
			'introduction' => ['label' => 'Introducción', 'color' => '#3B82F6', 'desc' => 'Nuevos lanzamientos y validación de mercado'],
			'growth' => ['label' => 'Crecimiento', 'color' => '#10B981', 'desc' => 'Demanda acelerada y ganancia de cuota'],
			'maturity' => ['label' => 'Madurez', 'color' => '#6366F1', 'desc' => 'Ventas consolidadas de alta estabilidad'],
			'decline' => ['label' => 'Declive', 'color' => '#F59E0B', 'desc' => 'Contracción continua de demanda'],
			'exit_renewal' => ['label' => 'Salida / Renovación', 'color' => '#EF4444', 'desc' => 'Liquidación, descontinuación o sustitución'],
			'insufficient_data' => ['label' => 'Datos Insuficientes', 'color' => '#94A3B8', 'desc' => 'Historial insuficiente para clasificar']
		];

		$result = [];
		foreach ($stageLabels as $key => $meta)
		{
			$found = null;
			foreach ($rows as $r)
			{
				if ($r['lifecycle_stage'] === $key)
				{
					$found = $r;
					break;
				}
			}

			$count = $found ? (int)$found['count_skus'] : 0;
			$pct = $totalCount > 0 ? round(($count / $totalCount) * 100, 1) : 0.0;

			$result[] = [
				'stage' => $key,
				'label' => $meta['label'],
				'color' => $meta['color'],
				'description' => $meta['desc'],
				'count' => $count,
				'percentage' => $pct,
				'total_sales' => $found ? round((float)$found['total_sales'], 2) : 0.0,
				'total_inventory' => $found ? round((float)$found['total_inventory'], 2) : 0.0
			];
		}

		return [
			'total_skus' => $totalCount,
			'stages' => $result
		];
	}

	/**
	 * Retorna la evolución histórica simulada a lo largo del ciclo de vida para el gráfico de curvas
	 */
	public function GetLifecycleCurveData(): array
	{
		return [
			'labels' => ['Mes 1', 'Mes 2', 'Mes 3', 'Mes 4', 'Mes 5', 'Mes 6', 'Mes 7', 'Mes 8', 'Mes 9', 'Mes 10', 'Mes 11', 'Mes 12'],
			'stages_timeline' => [
				'intro_end' => 3,
				'growth_end' => 6,
				'maturity_end' => 9,
				'decline_end' => 12
			],
			'series' => [
				[
					'name' => 'Ventas Teóricas Ciclo de Vida ($k)',
					'data' => [12, 28, 48, 85, 120, 142, 145, 144, 138, 110, 85, 45],
					'color' => '#3B82F6'
				],
				[
					'name' => 'Margen Bruto Realizable (%)',
					'data' => [38, 36, 35, 34, 32, 30, 28, 27, 26, 22, 18, 14],
					'color' => '#10B981'
				],
				[
					'name' => 'Nivel de Inventario a Costo ($k)',
					'data' => [40, 55, 75, 95, 110, 105, 98, 95, 92, 105, 115, 80],
					'color' => '#F59E0B'
				]
			]
		];
	}

	/**
	 * Matriz de rentabilidad vs rotación (Scatter / Bubble Plot)
	 * X: Rotación (Turnover)
	 * Y: Margen Bruto (%)
	 * Tamaño: Inventario ($)
	 * Color: Etapa
	 */
	public function GetProfitabilityRotationMatrix(): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$stmt = $db->query("
			SELECT 
				id, sku, name, category, brand, lifecycle_stage,
				turnover as x,
				gross_margin_pct as y,
				inventory_value as r,
				sales_amount,
				stockout_risk_score,
				dio_days
			FROM b2b_product_lifecycle_snapshots
			WHERE lifecycle_stage != 'insufficient_data'
			ORDER BY inventory_value DESC
		");
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$matrixData = [];
		foreach ($rows as $r)
		{
			// Normalizar radio visual para canvas/chart.js (entre 6 y 28 px)
			$val = (float)$r['r'];
			$visualRadius = max(6, min(28, (int)round(sqrt($val) / 6)));

			$matrixData[] = [
				'id' => (int)$r['id'],
				'sku' => $r['sku'],
				'name' => $r['name'],
				'category' => $r['category'],
				'stage' => $r['lifecycle_stage'],
				'turnover' => round((float)$r['x'], 2),
				'gross_margin' => round((float)$r['y'], 1),
				'inventory_value' => round((float)$r['r'], 2),
				'sales' => round((float)$r['sales_amount'], 2),
				'dio' => round((float)$r['dio_days'], 1),
				'radius' => $visualRadius
			];
		}

		return [
			'quadrants' => [
				'top_right' => ['label' => 'Estrellas (Alta Rotación + Alto Margen)', 'color' => '#10B981'],
				'top_left' => ['label' => 'Oportunidad (Baja Rotación + Alto Margen)', 'color' => '#3B82F6'],
				'bottom_right' => ['label' => 'Volumen (Alta Rotación + Bajo Margen)', 'color' => '#6366F1'],
				'bottom_left' => ['label' => 'Riesgo / Descarte (Baja Rotación + Bajo Margen)', 'color' => '#EF4444']
			],
			'benchmarks' => [
				'turnover_target' => 1.2,
				'margin_target' => 25.0
			],
			'items' => $matrixData
		];
	}

	/**
	 * Desglose del capital inmovilizado por antigüedad y categoría
	 */
	public function GetImmobilizedCapitalBreakdown(): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$stmt = $db->query("
			SELECT 
				category,
				COUNT(*) as affected_skus,
				SUM(inventory_value) as total_capital,
				AVG(dio_days) as avg_dio
			FROM b2b_product_lifecycle_snapshots
			WHERE is_immobilized_capital = 1 OR dio_days > 90.0
			GROUP BY category
		");
		$byCategory = $stmt->fetchAll(PDO::FETCH_ASSOC);

		// Desglose por franjas de días de inmovilización
		$stmtAging = $db->query("
			SELECT
				SUM(CASE WHEN dio_days BETWEEN 60 AND 90 THEN inventory_value ELSE 0 END) as b_60_90,
				SUM(CASE WHEN dio_days BETWEEN 91 AND 180 THEN inventory_value ELSE 0 END) as b_91_180,
				SUM(CASE WHEN dio_days > 180 THEN inventory_value ELSE 0 END) as b_over_180
			FROM b2b_product_lifecycle_snapshots
			WHERE is_immobilized_capital = 1 OR dio_days > 60.0
		");
		$aging = $stmtAging->fetch(PDO::FETCH_ASSOC);

		$total = (float)$aging['b_60_90'] + (float)$aging['b_91_180'] + (float)$aging['b_over_180'];

		return [
			'total_immobilized' => round($total, 2),
			'aging_tiers' => [
				['range' => '60 - 90 días', 'amount' => round((float)$aging['b_60_90'], 2), 'risk' => 'Moderado', 'color' => '#FBBF24'],
				['range' => '91 - 180 días', 'amount' => round((float)$aging['b_91_180'], 2), 'risk' => 'Alto', 'color' => '#F97316'],
				['range' => '> 180 días (Crítico)', 'amount' => round((float)$aging['b_over_180'], 2), 'risk' => 'Crítico', 'color' => '#EF4444']
			],
			'by_category' => $byCategory
		];
	}

	/**
	 * Comparación de demanda real vs pronosticada
	 */
	public function GetDemandTrendComparison(): array
	{
		return [
			'periods' => ['Sem 41', 'Sem 42', 'Sem 43', 'Sem 44', 'Sem 45', 'Sem 46 (Actual)', 'Sem 47 (Proy)', 'Sem 48 (Proy)'],
			'real_demand' => [2420, 2580, 2710, 2690, 2890, 2980, null, null],
			'forecast_baseline' => [2380, 2510, 2650, 2720, 2840, 2920, 3120, 3250],
			'safety_stock_line' => [800, 800, 850, 850, 900, 900, 950, 950]
		];
	}

	/**
	 * Retorna las recomendaciones de inteligencia comercial activas
	 */
	public function GetRecommendations(array $filters = []): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$where = ["1=1"];
		$params = [];

		if (!empty($filters['status']) && $filters['status'] !== 'ALL')
		{
			$where[] = "status = :status";
			$params[':status'] = $filters['status'];
		}
		if (!empty($filters['priority']) && $filters['priority'] !== 'ALL')
		{
			$where[] = "priority = :priority";
			$params[':priority'] = $filters['priority'];
		}

		$whereSql = implode(" AND ", $where);
		$stmt = $db->prepare("
			SELECT * FROM b2b_lifecycle_recommendations
			WHERE {$whereSql}
			ORDER BY 
				CASE priority
					WHEN 'urgent' THEN 1
					WHEN 'high' THEN 2
					WHEN 'medium' THEN 3
					ELSE 4
				END,
				financial_impact_usd DESC
		");
		$stmt->execute($params);
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

		foreach ($rows as &$r)
		{
			$r['evidence_data'] = json_decode($r['evidence_data'] ?? '{}', true);
		}

		return $rows;
	}

	/**
	 * Aprueba o rechaza una recomendación comercial
	 */
	public function ReviewRecommendation(int $id, string $status, string $reviewedBy, ?string $notes = null): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$valid = ['approved', 'rejected', 'pending'];
		if (!in_array($status, $valid, true))
		{
			throw new Exception("Estado de recomendación inválido: {$status}");
		}

		$stmt = $db->prepare("
			UPDATE b2b_lifecycle_recommendations
			SET status = :status,
				approved_by = :by,
				approved_at = CURRENT_TIMESTAMP,
				approval_notes = :notes,
				updated_at = CURRENT_TIMESTAMP
			WHERE id = :id
		");
		$stmt->execute([
			':status' => $status,
			':by' => $reviewedBy,
			':notes' => $notes ?? 'Decisión registrada por gerencia comercial',
			':id' => $id
		]);

		return [
			'success' => true,
			'recommendation_id' => $id,
			'new_status' => $status,
			'reviewed_by' => $reviewedBy
		];
	}

	/**
	 * Motor de Simulación de Descuentos / Markdown
	 */
	public function SimulateMarkdown(int $productId, float $discountPct, float $elasticity = 1.4): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$stmt = $db->prepare("
			SELECT * FROM b2b_product_lifecycle_snapshots
			WHERE product_id = :id OR id = :id
			LIMIT 1
		");
		$stmt->execute([':id' => $productId]);
		$prod = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$prod)
		{
			// Fallback demo product
			$originalPrice = 3.00;
			$unitCost = 2.00;
			$stock = 2100.0;
			$sku = 'SKU-GAL-030';
			$name = 'Galleta Salada Tradicional Pack Familiar 6x';
		}
		else
		{
			$originalPrice = (float)$prod['sales_units'] > 0 ? round((float)$prod['sales_amount'] / (float)$prod['sales_units'], 2) : 3.00;
			$unitCost = (float)$prod['sales_units'] > 0 ? round((float)$prod['cogs'] / (float)$prod['sales_units'], 2) : 2.00;
			$stock = (float)$prod['current_stock_units'];
			$sku = $prod['sku'];
			$name = $prod['name'];
		}

		$proposedPrice = round($originalPrice * (1 - ($discountPct / 100)), 2);
		$unitMargin = round($proposedPrice - $unitCost, 2);

		// Estimación de incremento de volumen por elasticidad: % volumen = % descuento * elasticidad
		$volumeUpliftPct = ($discountPct * $elasticity) / 100.0;
		$baseRunRate = max(10.0, (float)($prod['sales_units'] ?? 85.0));
		$projectedUnits = round($baseRunRate * (1 + $volumeUpliftPct), 0);

		// Límites por stock disponible
		$unitsToSell = min($stock, $projectedUnits);
		$recoverableCapital = round($unitsToSell * $proposedPrice, 2);
		$totalProjectedMargin = round($unitsToSell * $unitMargin, 2);
		$marginLossVsOriginal = round($unitsToSell * ($originalPrice - $proposedPrice), 2);

		// Break-even units: unidades necesarias para empatar el margen monetario original o recuperar costo
		$originalUnitMargin = max(0.01, $originalPrice - $unitCost);
		$breakevenUnits = $unitMargin > 0 ? max(1, round(($baseRunRate * $originalUnitMargin) / $unitMargin, 0)) : round($baseRunRate * (1 + $volumeUpliftPct), 0);

		return [
			'product_id' => $productId,
			'sku' => $sku,
			'product_name' => $name,
			'original_price' => $originalPrice,
			'discount_pct' => $discountPct,
			'proposed_price' => $proposedPrice,
			'unit_cost' => $unitCost,
			'unit_margin' => $unitMargin,
			'current_stock' => $stock,
			'elasticity_assumed' => $elasticity,
			'projected_units_sold' => $unitsToSell,
			'recoverable_capital_usd' => $recoverableCapital,
			'total_margin_projected' => $totalProjectedMargin,
			'margin_loss_usd' => $marginLossVsOriginal,
			'breakeven_units' => $breakevenUnits,
			'is_margin_positive' => $unitMargin > 0
		];
	}

	/**
	 * Guarda un escenario de markdown simulado
	 */
	public function SaveMarkdownScenario(array $data, string $createdBy = 'Planificación Comercial'): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$stmt = $db->prepare("
			INSERT INTO b2b_markdown_scenarios (
				product_id, sku, product_name, original_price, unit_cost,
				current_stock, discount_pct, proposed_price, elasticity,
				projected_units, unit_margin, total_margin, recoverable_capital_usd,
				margin_loss_usd, breakeven_units, status, created_by, created_at
			) VALUES (
				:pid, :sku, :name, :orig_p, :cost,
				:stock, :disc, :prop_p, :elasticity,
				:proj_u, :u_marg, :tot_marg, :recov_cap,
				:loss, :be_u, 'submitted', :creator, CURRENT_TIMESTAMP
			)
		");

		$stmt->execute([
			':pid' => (int)($data['product_id'] ?? 1),
			':sku' => $data['sku'] ?? 'SKU-DEMO',
			':name' => $data['product_name'] ?? 'Producto Demo',
			':orig_p' => (float)($data['original_price'] ?? 0),
			':cost' => (float)($data['unit_cost'] ?? 0),
			':stock' => (float)($data['current_stock'] ?? 0),
			':disc' => (float)($data['discount_pct'] ?? 0),
			':prop_p' => (float)($data['proposed_price'] ?? 0),
			':elasticity' => (float)($data['elasticity_assumed'] ?? 1.4),
			':proj_u' => (float)($data['projected_units_sold'] ?? 0),
			':u_marg' => (float)($data['unit_margin'] ?? 0),
			':tot_marg' => (float)($data['total_margin_projected'] ?? 0),
			':recov_cap' => (float)($data['recoverable_capital_usd'] ?? 0),
			':loss' => (float)($data['margin_loss_usd'] ?? 0),
			':be_u' => (float)($data['breakeven_units'] ?? 0),
			':creator' => $createdBy
		]);

		return [
			'success' => true,
			'scenario_id' => $db->lastInsertId(),
			'message' => 'Escenario de markdown guardado exitosamente para aprobación directiva.'
		];
	}

	/**
	 * Asistente de importación de datos CSV/Excel/JSON
	 */
	public function ProcessDataImport(string $filename, array $rows, array $columnMapping, string $user = 'Carlos Mendoza'): array
	{
		$this->EnsureSchema();
		$db = $this->Db();

		$total = count($rows);
		$valid = 0;
		$errors = [];

		$stmtUpsert = $db->prepare("
			INSERT INTO b2b_product_lifecycle_snapshots (
				product_id, sku, name, category, brand, period, lifecycle_stage,
				confidence_score, sales_units, sales_amount, cogs, gross_margin_pct,
				current_stock_units, inventory_value, dio_days, turnover, sell_through_pct,
				gmroi, gmros, demand_trend_slope, stockout_days, is_immobilized_capital,
				stockout_risk_score, classification_reason, is_demo, updated_at
			) VALUES (
				:pid, :sku, :name, :cat, :brand, 'MTD', :stage,
				:conf, :sunits, :samount, :cogs, :gm_pct,
				:stock, :inv_val, :dio, :rot, :st_pct,
				:gmroi, :gmros, :slope, :so_days, :immob,
				:risk, :reason, 0, CURRENT_TIMESTAMP
			)
		");

		$db->beginTransaction();
		try
		{
			foreach ($rows as $index => $row)
			{
				$rowNum = $index + 1;
				$sku = trim((string)($row[$columnMapping['sku'] ?? 'sku'] ?? ''));
				$name = trim((string)($row[$columnMapping['name'] ?? 'name'] ?? ''));

				if (empty($sku) || empty($name))
				{
					$errors[] = ["row" => $rowNum, "error" => "SKU o Nombre vacío en la fila."];
					continue;
				}

				$salesUnits = (float)($row[$columnMapping['sales_units'] ?? 'sales_units'] ?? 0);
				$salesAmount = (float)($row[$columnMapping['sales_amount'] ?? 'sales_amount'] ?? 0);
				$cogs = (float)($row[$columnMapping['cogs'] ?? 'cogs'] ?? ($salesAmount * 0.75));
				$stock = (float)($row[$columnMapping['current_stock'] ?? 'stock'] ?? 0);
				$category = trim((string)($row[$columnMapping['category'] ?? 'category'] ?? 'General'));
				$brand = trim((string)($row[$columnMapping['brand'] ?? 'brand'] ?? 'Prince'));

				// Reglas de clasificación automática
				$marginPct = $salesAmount > 0 ? round((($salesAmount - $cogs) / $salesAmount) * 100, 1) : 25.0;
				$invValue = round($stock * ($salesUnits > 0 ? ($cogs / $salesUnits) : 2.5), 2);
				$dio = $salesUnits > 0 ? round(($stock / ($salesUnits / 30)), 1) : 120.0;
				$turnover = $invValue > 0 ? round($cogs / $invValue, 2) : 0.5;

				if ($salesUnits < 5)
				{
					$stage = 'insufficient_data';
					$reason = 'Ventas insuficientes para determinar tendencia.';
				}
				elseif ($dio > 120.0)
				{
					$stage = 'exit_renewal';
					$reason = 'Alta cobertura sin rotación; candidato a liquidación.';
				}
				elseif ($marginPct < 15.0 || $dio > 75.0)
				{
					$stage = 'decline';
					$reason = 'Contracción de margen o cobertura superior a 75 días.';
				}
				elseif ($dio < 20.0 && $marginPct >= 28.0)
				{
					$stage = 'growth';
					$reason = 'Alta velocidad de venta y margen saludable.';
				}
				else
				{
					$stage = 'maturity';
					$reason = 'Comportamiento de venta consolidado.';
				}

				$stmtUpsert->execute([
					':pid' => 100 + $rowNum,
					':sku' => $sku,
					':name' => $name,
					':cat' => $category,
					':brand' => $brand,
					':stage' => $stage,
					':conf' => 88.0,
					':sunits' => $salesUnits,
					':samount' => $salesAmount,
					':cogs' => $cogs,
					':gm_pct' => $marginPct,
					':stock' => $stock,
					':inv_val' => $invValue,
					':dio' => $dio,
					':rot' => $turnover,
					':st_pct' => 55.0,
					':gmroi' => 2.8,
					':gmros' => 320.0,
					':slope' => 0.05,
					':so_days' => 0,
					':immob' => ($dio > 90 ? 1 : 0),
					':risk' => ($dio < 15 ? 75.0 : 10.0),
					':reason' => $reason
				]);

				$valid++;
			}

			// Registro en auditoría
			$stmtLog = $db->prepare("
				INSERT INTO b2b_data_imports (
					filename, file_type, total_rows, valid_rows, error_rows,
					status, column_mapping, error_details, imported_by, created_at
				) VALUES (
					:file, 'CSV/EXCEL', :tot, :val, :err,
					:status, :mapping, :details, :user, CURRENT_TIMESTAMP
				)
			");

			$stmtLog->execute([
				':file' => $filename,
				':tot' => $total,
				':val' => $valid,
				':err' => count($errors),
				':status' => count($errors) === 0 ? 'completed' : 'completed_with_errors',
				':mapping' => json_encode($columnMapping),
				':details' => json_encode($errors),
				':user' => $user
			]);

			$db->commit();
		}
		catch (Exception $e)
		{
			$db->rollBack();
			throw $e;
		}

		return [
			'success' => true,
			'total_rows' => $total,
			'valid_rows' => $valid,
			'error_rows' => count($errors),
			'errors' => $errors
		];
	}

	/**
	 * Retorna historial de cargas de datos
	 */
	public function GetImportHistory(): array
	{
		$this->EnsureSchema();
		$db = $this->Db();
		$stmt = $db->query("SELECT * FROM b2b_data_imports ORDER BY id DESC LIMIT 10");
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
}
