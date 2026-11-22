{{-- =========================================================================
     COMPONENTE REUTILIZABLE: RECORRIDO INTERACTIVO GUIADO (GUIDED DASHBOARD TOUR)
     Desarrollado con Driver.js para PRINCES B2B Suite
     ========================================================================= --}}

<!-- Estilos específicos de PRINCES para Driver.js (Borde verde suave, popover flotante compacto y modo oscuro) -->
<style>
	/* Overlay con oscurecimiento suave y backdrop blur */
	.driver-overlay {
		background-color: rgba(9, 13, 22, 0.72) !important;
		backdrop-filter: blur(2px) !important;
		-webkit-backdrop-filter: blur(2px) !important;
		transition: opacity 0.25s ease-in-out !important;
	}

	/* Elemento resaltado con spotlight y borde verde suave */
	.driver-stage {
		border-radius: 12px !important;
		box-shadow: 0 0 0 3px #10b981, 0 0 25px rgba(16, 185, 129, 0.35) !important;
		transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
		outline: none !important;
	}

	/* Tarjeta flotante compacta estilo SaaS Enterprise */
	.driver-popover.princes-tour-popover {
		background: #ffffff !important;
		color: #0f172a !important;
		border: 1px solid #e2e8f0 !important;
		border-radius: 1rem !important;
		box-shadow: 0 20px 25px -5px rgba(15, 23, 42, 0.25), 0 8px 10px -6px rgba(15, 23, 42, 0.15) !important;
		max-width: 380px !important;
		min-width: 290px !important;
		padding: 1.15rem 1.25rem !important;
		font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
		z-index: 1000000 !important;
		animation: princesTourFadeIn 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
	}

	.dark .driver-popover.princes-tour-popover {
		background: #111827 !important;
		color: #f8fafc !important;
		border: 1px solid #1f2937 !important;
		box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.6), 0 8px 10px -6px rgba(0, 0, 0, 0.4) !important;
	}

	/* Título con insignia y peso visual */
	.driver-popover.princes-tour-popover .driver-popover-title {
		font-size: 0.9375rem !important;
		font-weight: 700 !important;
		color: #0f172a !important;
		line-height: 1.35 !important;
		margin-bottom: 0.45rem !important;
		display: flex !important;
		align-items: center !important;
		gap: 0.5rem !important;
	}

	.dark .driver-popover.princes-tour-popover .driver-popover-title {
		color: #ffffff !important;
	}

	/* Descripción breve, clara y legible */
	.driver-popover.princes-tour-popover .driver-popover-description {
		font-size: 0.8125rem !important;
		line-height: 1.55 !important;
		color: #475569 !important;
		margin-bottom: 1rem !important;
	}

	.dark .driver-popover.princes-tour-popover .driver-popover-description {
		color: #cbd5e1 !important;
	}

	/* Pie con indicador de progreso y botones */
	.driver-popover.princes-tour-popover .driver-popover-footer {
		display: flex !important;
		align-items: center !important;
		justify-content: space-between !important;
		gap: 0.5rem !important;
		border-top: 1px solid #f1f5f9 !important;
		padding-top: 0.85rem !important;
		margin-top: 0.5rem !important;
	}

	.dark .driver-popover.princes-tour-popover .driver-popover-footer {
		border-top: 1px solid #1f2937 !important;
	}

	/* Indicador de progreso: Paso X de Y */
	.driver-popover.princes-tour-popover .driver-popover-progress-text {
		font-size: 0.6875rem !important;
		font-weight: 700 !important;
		text-transform: uppercase !important;
		letter-spacing: 0.05em !important;
		color: #059669 !important;
		background: #ecfdf5 !important;
		padding: 0.2rem 0.55rem !important;
		border-radius: 9999px !important;
		border: 1px solid #d1fae5 !important;
	}

	.dark .driver-popover.princes-tour-popover .driver-popover-progress-text {
		color: #34d399 !important;
		background: rgba(6, 78, 59, 0.45) !important;
		border-color: rgba(5, 150, 105, 0.4) !important;
	}

	/* Botón Anterior */
	.driver-popover.princes-tour-popover .driver-popover-prev-btn {
		font-size: 0.75rem !important;
		font-weight: 600 !important;
		padding: 0.4rem 0.75rem !important;
		border-radius: 0.5rem !important;
		background: #f1f5f9 !important;
		color: #475569 !important;
		border: 1px solid #e2e8f0 !important;
		cursor: pointer !important;
		transition: all 0.15s ease !important;
	}

	.driver-popover.princes-tour-popover .driver-popover-prev-btn:hover {
		background: #e2e8f0 !important;
		color: #0f172a !important;
	}

	.dark .driver-popover.princes-tour-popover .driver-popover-prev-btn {
		background: #1f2937 !important;
		color: #cbd5e1 !important;
		border-color: #374151 !important;
	}

	.dark .driver-popover.princes-tour-popover .driver-popover-prev-btn:hover {
		background: #374151 !important;
		color: #ffffff !important;
	}

	/* Botón Siguiente / Finalizar */
	.driver-popover.princes-tour-popover .driver-popover-next-btn {
		font-size: 0.75rem !important;
		font-weight: 700 !important;
		padding: 0.4rem 0.95rem !important;
		border-radius: 0.5rem !important;
		background: #059669 !important;
		color: #ffffff !important;
		border: none !important;
		box-shadow: 0 1px 3px rgba(5, 150, 105, 0.3) !important;
		cursor: pointer !important;
		transition: all 0.15s ease !important;
	}

	.driver-popover.princes-tour-popover .driver-popover-next-btn:hover {
		background: #047857 !important;
		box-shadow: 0 2px 5px rgba(5, 150, 105, 0.4) !important;
	}

	/* Botón Cerrar (X) */
	.driver-popover.princes-tour-popover .driver-popover-close-btn {
		color: #94a3b8 !important;
		font-size: 1rem !important;
		width: 1.5rem !important;
		height: 1.5rem !important;
		display: flex !important;
		align-items: center !important;
		justify-content: center !important;
		border-radius: 0.375rem !important;
		transition: all 0.15s ease !important;
	}

	.driver-popover.princes-tour-popover .driver-popover-close-btn:hover {
		color: #0f172a !important;
		background: #f1f5f9 !important;
	}

	.dark .driver-popover.princes-tour-popover .driver-popover-close-btn:hover {
		color: #ffffff !important;
		background: #1f2937 !important;
	}

	/* Flecha / Apuntador visual */
	.driver-popover.princes-tour-popover .driver-popover-arrow-side-top {
		border-top-color: #ffffff !important;
	}
	.dark .driver-popover.princes-tour-popover .driver-popover-arrow-side-top {
		border-top-color: #111827 !important;
	}
	.driver-popover.princes-tour-popover .driver-popover-arrow-side-bottom {
		border-bottom-color: #ffffff !important;
	}
	.dark .driver-popover.princes-tour-popover .driver-popover-arrow-side-bottom {
		border-bottom-color: #111827 !important;
	}
	.driver-popover.princes-tour-popover .driver-popover-arrow-side-left {
		border-left-color: #ffffff !important;
	}
	.dark .driver-popover.princes-tour-popover .driver-popover-arrow-side-left {
		border-left-color: #111827 !important;
	}
	.driver-popover.princes-tour-popover .driver-popover-arrow-side-right {
		border-right-color: #ffffff !important;
	}
	.dark .driver-popover.princes-tour-popover .driver-popover-arrow-side-right {
		border-right-color: #111827 !important;
	}

	@keyframes princesTourFadeIn {
		from {
			opacity: 0;
			transform: scale(0.96) translateY(4px);
		}
		to {
			opacity: 1;
			transform: scale(1) translateY(0);
		}
	}

	@media (prefers-reduced-motion: reduce) {
		.driver-overlay,
		.driver-stage,
		.driver-popover.princes-tour-popover {
			animation: none !important;
			transition: none !important;
		}
	}
</style>

<!-- Componente JavaScript Central: GuidedDashboardTour -->
<script>
window.GuidedDashboardTour = (function() {
	let activeDriver = null;
	let currentDashboardKey = null;

	// 1. DICCIONARIO DE RECORRIDOS ESPECÍFICOS POR CADA DASHBOARD
	const dashboardToursConfig = {
		// =================================================================
		// 1. HORARIOS Y COBERTURA (WFM) — ESPECIFICACIÓN PRINCIPAL
		// =================================================================
		'schedules': {
			id: 'schedules',
			title: 'Horarios y Cobertura',
			beforeStart: () => {
				// Garantizar que la pestaña activa sea el Calendario
				if (typeof switchWfmTab === 'function') {
					switchWfmTab('schedule');
				}
			},
			steps: [
				{
					element: '[data-tour="wfm-view-switcher"]',
					fallback: '#btn-view-weekly',
					popover: {
						title: '🗓️ Selector Día / Semana / Mes',
						description: 'Desde aquí puedes elegir cómo quieres ver los horarios: por día, semana o mes. La vista semanal es la predeterminada en escritorio y la vista diaria en dispositivos móviles.',
						side: 'bottom',
						align: 'start'
					},
					onHighlighted: () => {
						// Demostración interactiva guiada sin modificar datos reales
						const dBtn = document.getElementById('btn-view-daily');
						const wBtn = document.getElementById('btn-view-weekly');
						const mBtn = document.getElementById('btn-view-monthly');
						if (dBtn && wBtn && mBtn) {
							dBtn.classList.add('ring-2', 'ring-emerald-500');
							setTimeout(() => {
								dBtn.classList.remove('ring-2', 'ring-emerald-500');
								mBtn.classList.add('ring-2', 'ring-emerald-500');
							}, 800);
							setTimeout(() => {
								mBtn.classList.remove('ring-2', 'ring-emerald-500');
								wBtn.classList.add('ring-2', 'ring-emerald-500');
							}, 1600);
							setTimeout(() => {
								wBtn.classList.remove('ring-2', 'ring-emerald-500');
							}, 2400);
						}
					}
				},
				{
					element: '[data-tour="wfm-kpi-staff"]',
					fallback: '#wfm-top-kpi-staff',
					popover: {
						title: '👥 Personal Programado',
						description: 'Aquí ves cuántas personas tienen un turno asignado para el período seleccionado. Se recalcula en tiempo real según la fecha, vista y filtros aplicados.',
						side: 'bottom',
						align: 'center'
					}
				},
				{
					element: '[data-tour="wfm-kpi-coverage"]',
					fallback: '#wfm-top-kpi-coverage',
					popover: {
						title: '🛡️ Cobertura de Turnos (%)',
						description: 'Este porcentaje indica si tienes suficiente personal para cubrir las necesidades de la operación frente a la demanda proyectada de reposición y despacho mayorista.',
						side: 'bottom',
						align: 'center'
					}
				},
				{
					element: '[data-tour="wfm-kpi-uncovered"]',
					fallback: '#wfm-top-kpi-uncovered',
					popover: {
						title: '⚠️ Turnos Sin Cubrir y Ausencias',
						description: 'Te advierte sobre puestos o franjas faltantes antes de que afecten la entrega a clientes mayoristas, así como colaboradores en descanso médico o vacaciones autorizadas.',
						side: 'bottom',
						align: 'center'
					}
				},
				{
					element: '[data-tour="wfm-calendar-container"]',
					fallback: '#wfm-view-weekly',
					popover: {
						title: '📅 Calendario y Matriz Operativa',
						description: 'Aquí puedes consultar quién trabaja hoy, a qué hora entra y sale, quién descansa y qué turnos necesitan atención. Al pulsar sobre cualquier día o columna, puedes abrir su vista diaria.',
						side: 'top',
						align: 'center'
					}
				},
				{
					element: '[data-tour="wfm-btn-add-shift"]',
					fallback: 'button[onclick*="openShiftModal"]',
					popover: {
						title: '➕ Crear y Asignar Turno',
						description: 'Utiliza este botón para asignar un horario a un trabajador, aplicar plantillas estándar (Apertura, Intermedio, Cierre) o programar turnos repetitivos en múltiples días.',
						side: 'left',
						align: 'center'
					}
				},
				{
					element: '[data-tour="wfm-btn-publish"]',
					fallback: 'button[onclick*="publishCurrentSchedule"]',
					popover: {
						title: '🚀 Publicar Horarios Oficiales',
						description: 'Cuando termines de revisar los turnos y comprobar las validaciones laborales (sin solapamientos ni descansos < 11h), puedes publicarlos para que el personal los consulte.',
						side: 'bottom',
						align: 'end'
					}
				}
			]
		},

		// =================================================================
		// 2. TABLERO EJECUTIVO DE VENTAS B2B (OVERVIEW / SALES)
		// =================================================================
		'sales': {
			id: 'sales',
			title: 'Tablero Ejecutivo de Ventas',
			steps: [
				{
					element: '[data-tour="sales-period-filters"]',
					fallback: '#dash-btn-mtd',
					popover: {
						title: '⏱️ Filtros de período (7 días, mes, trimestre y año actual hasta hoy)',
						description: 'Selecciona el período de análisis con un solo clic o define un rango personalizado de fechas. Todo el tablero recalcula ventas netas, ticket y márgenes de forma inmediata.',
						side: 'bottom',
						align: 'start'
					}
				},
				{
					element: '[data-tour="sales-filter-customer"]',
					fallback: '#dash-filter-customer',
					popover: {
						title: '🏢 Filtros por Cliente y Sucursal',
						description: 'Filtra por cadena mayorista, cliente corporativo o establecimiento logístico para evaluar el desempeño puntual de cada punto de distribución.',
						side: 'bottom',
						align: 'start'
					}
				},
				{
					element: '[data-tour="sales-kpi-sales"]',
					fallback: '#kpi-net-sales-card',
					popover: {
						title: '💵 Facturación Neta y Pedidos Confirmados',
						description: 'Supervisa las ventas netas sin impuestos, el número de órdenes cerradas y el ticket promedio facturado en comparación directa con el período anterior.',
						side: 'bottom',
						align: 'center'
					}
				},
				{
					element: '[data-tour="sales-kpi-receivable"]',
					fallback: '#kpi-receivable-card',
					popover: {
						title: '💼 Cuentas por Cobrar y Riesgo de Cartera',
						description: 'Monitorea el saldo vivo exigible y la cartera en mora para anticipar la cobranza comercial y evitar bloqueos de despacho.',
						side: 'bottom',
						align: 'center'
					}
				},
				{
					element: '[data-tour="sales-chart-trend"]',
					fallback: 'canvas#salesTrendChart',
					popover: {
						title: '📈 Evolución de Facturación y Margen %',
						description: 'Interpreta la tendencia diaria de ventas en dólares frente al margen bruto alcanzado. Puedes pulsar "Explicar gráfico" para ver recomendaciones según tu rol.',
						side: 'top',
						align: 'center'
					}
				},
				{
					element: '[data-tour="sales-chart-category"]',
					fallback: 'canvas#channelShareChart',
					popover: {
						title: '📊 Distribución por Categoría de Producto',
						description: 'Muestra qué familias y líneas de producto generan la mayor participación de ingresos en la distribuidora.',
						side: 'top',
						align: 'center'
					}
				},
				{
					element: '[data-tour="sales-recent-orders"]',
					fallback: '#recent-orders-table-body',
					popover: {
						title: '📋 Órdenes de Venta Mayoristas Recientes',
						description: 'Listado en tiempo real de los últimos pedidos ingresados, su estado de preparación y acceso directo para gestionarlos.',
						side: 'top',
						align: 'center'
					}
				},
				{
					element: '[data-tour="sales-btn-new-order"]',
					fallback: 'button[onclick="handleHeaderNewAction()"]',
					popover: {
						title: '🛒 Crear Pedido Mayorista',
						description: 'Desde este botón de acción rápida puedes generar un nuevo pedido B2B o una cotización comercial con cálculo automático de escalas de precio.',
						side: 'left',
						align: 'center'
					}
				}
			]
		},

		// =================================================================
		// 3. CLIENTES Y ALMACENES (CUSTOMERS)
		// =================================================================
		'customers': {
			id: 'customers',
			title: 'Clientes y Almacenes B2B',
			steps: [
				{
					element: '#cust-btn-mtd',
					fallback: '#section-customers h1',
					popover: {
						title: '🏢 Directorio de Clientes y Filtros de Cartera',
						description: 'Filtra clientes mayoristas por territorio, vendedor asignado o período de facturación para auditar la relación comercial.',
						side: 'bottom',
						align: 'start'
					}
				},
				{
					element: '#cust-tab-btn-exec',
					fallback: '#section-customers',
					popover: {
						title: '📊 Tablero Ejecutivo vs Directorio Operativo',
						description: 'Alterna entre la visión financiera de cartera (límite de crédito, saldo en mora) y el listado de empresas con sus almacenes de despacho.',
						side: 'bottom',
						align: 'start'
					}
				},
				{
					element: 'button[onclick*="openCustomerModal"]',
					fallback: '#section-customers button',
					popover: {
						title: '➕ Registrar Nuevo Cliente B2B',
						description: 'Da de alta nuevas cadenas o tiendas mayoristas asignando condiciones comerciales, plazo de pago y lista de precios preferencial.',
						side: 'left',
						align: 'center'
					}
				}
			]
		},

		// =================================================================
		// 4. PRODUCTOS Y PRECIOS (PRODUCTS)
		// =================================================================
		'products': {
			id: 'products',
			title: 'Catálogo y Precios B2B',
			steps: [
				{
					element: '#prod-tab-btn-exec',
					fallback: '#section-products h1',
					popover: {
						title: '🏷️ Tarifario y Métricas de Catálogo',
						description: 'Supervisa la disponibilidad de SKUs, rotación de productos e ingresos por categoría.',
						side: 'bottom',
						align: 'start'
					}
				},
				{
					element: 'button[onclick*="openPricingCalculatorModal"]',
					fallback: '#section-products button',
					popover: {
						title: '🧮 Simulador de Precios y Escalas',
						description: 'Calcula márgenes comerciales aplicando descuentos por volumen (cajas, pallets) antes de negociar con un cliente mayorista.',
						side: 'bottom',
						align: 'center'
					}
				},
				{
					element: 'button[onclick*="openProductModal"]',
					fallback: '#section-products button:last-child',
					popover: {
						title: '➕ Crear Nuevo Producto',
						description: 'Agrega nuevos SKUs con sus factores de conversión, stock de seguridad y condición de almacenamiento.',
						side: 'left',
						align: 'center'
					}
				}
			]
		},

		// =================================================================
		// 5. COTIZACIONES Y PEDIDOS (ORDERS)
		// =================================================================
		'orders': {
			id: 'orders',
			title: 'Cotizaciones y Pedidos B2B',
			steps: [
				{
					element: '#orders-tab-btn-exec',
					fallback: '#section-orders h1',
					popover: {
						title: '📦 Gestión del Ciclo de Pedidos',
						description: 'Supervisa el flujo comercial completo: desde cotizaciones hasta pedidos confirmados, en preparación o despachados.',
						side: 'bottom',
						align: 'start'
					}
				},
				{
					element: '#orders-search',
					fallback: '#section-orders input',
					popover: {
						title: '🔍 Búsqueda Rápida de Órdenes',
						description: 'Localiza pedidos al instante mediante código (PED/COT), nombre de cliente mayorista o vendedor asignado.',
						side: 'bottom',
						align: 'start'
					}
				},
				{
					element: '#orders-filter-status',
					fallback: '#section-orders select',
					popover: {
						title: '🚦 Filtro por Estado de Despacho',
						description: 'Filtra pedidos según su etapa logística para priorizar preparación en almacén y entregas urgentes.',
						side: 'bottom',
						align: 'start'
					}
				}
			]
		},

		// =================================================================
		// 6. FACTURACIÓN Y COBRANZAS (INVOICES)
		// =================================================================
		'invoices': {
			id: 'invoices',
			title: 'Facturación B2B y Cobranzas',
			steps: [
				{
					element: '#invoices-search',
					fallback: '#section-invoices h1',
					popover: {
						title: '🧾 Comprobantes de Pago y Facturas',
						description: 'Consulta facturas emitidas, pagadas, con saldo parcial o vencidas con desglose fiscal exacto.',
						side: 'bottom',
						align: 'start'
					}
				},
				{
					element: '#invoices-filter-status',
					fallback: '#section-invoices select',
					popover: {
						title: '💰 Estado de Cobranza',
						description: 'Identifica qué facturas requieren gestión de cobro inmediata o aplicación de notas de crédito.',
						side: 'bottom',
						align: 'start'
					}
				}
			]
		},

		// =================================================================
		// 7. VENDEDORES Y TERRITORIOS (SALES-REPS)
		// =================================================================
		'sales-reps': {
			id: 'sales-reps',
			title: 'Vendedores y Territorios',
			steps: [
				{
					element: '#reps-tab-btn-exec',
					fallback: '#section-sales-reps h1',
					popover: {
						title: '🎯 Desempeño y Cuota de Ventas',
						description: 'Compara la facturación generada por cada ejecutivo, su porcentaje de cumplimiento de meta y comisiones calculadas.',
						side: 'bottom',
						align: 'start'
					}
				},
				{
					element: '#reps-filter-territory',
					fallback: '#section-sales-reps select',
					popover: {
						title: '📍 Segmentación por Territorio',
						description: 'Filtra la fuerza de ventas por zona geográfica (Broward, Miami-Dade) y supervisor a cargo.',
						side: 'bottom',
						align: 'start'
					}
				}
			]
		},

		// =================================================================
		// 8. RENTABILIDAD COMERCIAL (PROFITABILITY)
		// =================================================================
		'profitability': {
			id: 'profitability',
			title: 'Rentabilidad Comercial y Márgenes',
			steps: [
				{
					element: '#section-profitability h1',
					fallback: '#section-profitability',
					popover: {
						title: '💎 Análisis de Margen y Markup',
						description: 'Evalúa la rentabilidad neta descontando costos de reposición, fletes y descuentos otorgados.',
						side: 'bottom',
						align: 'start'
					}
				}
			]
		},

		// =================================================================
		// 9. CICLO DE VIDA DEL PRODUCTO (PRODUCT-LIFECYCLE)
		// =================================================================
		'product-lifecycle': {
			id: 'product-lifecycle',
			title: 'Ciclo de Vida del Producto (PLM)',
			steps: [
				{
					element: '#section-product-lifecycle h1',
					fallback: '#section-product-lifecycle',
					popover: {
						title: '🔄 Matriz de las 5 Etapas PLM',
						description: 'El sistema clasifica cada producto en Introducción, Crecimiento, Madurez, Declive o Salida según ventas históricas.',
						side: 'bottom',
						align: 'start'
					}
				}
			]
		},

		// =================================================================
		// 10. REPORTES Y ANALÍTICA (REPORTS)
		// =================================================================
		'reports': {
			id: 'reports',
			title: 'Reportes y Analítica B2B',
			steps: [
				{
					element: '#section-reports h1',
					fallback: '#section-reports',
					popover: {
						title: '📑 Centro de Inteligencia y Reportes',
						description: 'Genera informes consolidados de ventas, rotación de inventario y auditoría con exportación a PDF y Excel.',
						side: 'bottom',
						align: 'start'
					}
				}
			]
		},

		// =================================================================
		// 11. CONFIGURACIÓN (SETTINGS)
		// =================================================================
		'settings': {
			id: 'settings',
			title: 'Configuración del Sistema',
			steps: [
				{
					element: '#section-settings h1',
					fallback: '#section-settings',
					popover: {
						title: '⚙️ Parámetros de Negocio y Reglas Laborales',
						description: 'Ajusta la moneda de facturación, plazos de crédito y reglas laborales de descanso y límites de jornada.',
						side: 'bottom',
						align: 'start'
					}
				}
			]
		}
	};

	// 2. FUNCIÓN PARA OBTENER EL INSTANCIADOR DE DRIVER.JS
	function getDriverConstructor() {
		if (typeof window.driver === 'function') {
			return window.driver;
		}
		if (window.driver && typeof window.driver.js?.driver === 'function') {
			return window.driver.js.driver;
		}
		return null;
	}

	// 3. INICIAR TOUR DEL TABLERO ESPECÍFICO
	function startTour(dashboardKey) {
		// Detectar tablero si no se envía como parámetro
		let key = dashboardKey;
		if (!key && typeof currentSection !== 'undefined') {
			key = currentSection;
		}
		if (!key) {
			const path = window.location.pathname;
			if (path.includes('schedules')) key = 'schedules';
			else if (path.includes('customers')) key = 'customers';
			else if (path.includes('products')) key = 'products';
			else if (path.includes('orders')) key = 'orders';
			else if (path.includes('invoices')) key = 'invoices';
			else if (path.includes('sales-reps')) key = 'sales-reps';
			else if (path.includes('profitability')) key = 'profitability';
			else if (path.includes('product-lifecycle')) key = 'product-lifecycle';
			else if (path.includes('reports')) key = 'reports';
			else if (path.includes('settings')) key = 'settings';
			else key = 'sales';
		}

		currentDashboardKey = key;
		const tourConfig = dashboardToursConfig[key] || dashboardToursConfig['sales'];
		if (!tourConfig) return;

		// Hook previo (ej. activar pestaña calendario en WFM)
		if (typeof tourConfig.beforeStart === 'function') {
			try {
				tourConfig.beforeStart();
			} catch (err) {
				console.warn('Tour beforeStart hook error:', err);
			}
		}

		const DriverClass = getDriverConstructor();
		if (!DriverClass) {
			console.warn('Driver.js aún no está cargado. Cargando dinámicamente...');
			loadDriverScript(() => startTour(key));
			return;
		}

		// Filtrar únicamente los pasos cuyos elementos existen y son visibles en el DOM
		const validSteps = [];
		(tourConfig.steps || []).forEach(step => {
			let targetEl = null;
			if (step.element) {
				targetEl = document.querySelector(step.element);
			}
			if (!targetEl && step.fallback) {
				targetEl = document.querySelector(step.fallback);
			}

			if (targetEl) {
				const style = window.getComputedStyle(targetEl);
				// Comprobar que no esté oculto
				if (style.display !== 'none' && style.visibility !== 'hidden' && (targetEl.offsetWidth > 0 || targetEl.offsetHeight > 0 || targetEl.getClientRects().length > 0)) {
					validSteps.push({
						element: targetEl,
						popover: {
							title: step.popover.title,
							description: step.popover.description,
							side: step.popover.side || 'bottom',
							align: step.popover.align || 'start'
						},
						onHighlighted: step.onHighlighted,
						onDeselected: step.onDeselected
					});
				}
			}
		});

		if (validSteps.length === 0) {
			console.info('No se encontraron elementos interactivos visibles para el recorrido en este momento.');
			return;
		}

		const prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

		activeDriver = DriverClass({
			showProgress: true,
			animate: !prefersReducedMotion,
			allowClose: true,
			smoothScroll: !prefersReducedMotion,
			overlayColor: '#090d16',
			overlayOpacity: 0.72,
			stagePadding: 6,
			stageRadius: 10,
			popoverClass: 'princes-tour-popover',
			popoverOffset: 12,
			progressText: 'Paso @{{current}} de @{{total}}',
			nextBtnText: 'Siguiente →',
			prevBtnText: '← Anterior',
			doneBtnText: '✓ Finalizar',
			showButtons: ['next', 'previous', 'close'],
			steps: validSteps,
			onDestroyStarted: () => {
				activeDriver.destroy();
			},
			onDestroyed: () => {
				// Guardar progreso por usuario y tablero
				try {
					localStorage.setItem(`princes_tour_completed_${key}`, String(Date.now()));
				} catch (e) {}

				// Restaurar estado visual si corresponde
				if (key === 'schedules' && typeof switchCalendarView === 'function') {
					switchCalendarView('weekly', false);
				}

				// Notificación toast discreta de finalización
				showTourCompletedToast(tourConfig.title);
			}
		});

		// Despliegue del recorrido visual
		activeDriver.drive();
	}

	// 4. CARGA DINÁMICA DE DRIVER.JS SI FUESE NECESARIO
	function loadDriverScript(callback) {
		const existingScript = document.getElementById('driver-js-script');
		if (existingScript) {
			existingScript.addEventListener('load', callback);
			return;
		}

		const script = document.createElement('script');
		script.id = 'driver-js-script';
		script.src = 'https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.js.iife.js';
		script.onload = () => {
			if (typeof callback === 'function') callback();
		};
		document.head.appendChild(script);

		const existingCss = document.getElementById('driver-js-css');
		if (!existingCss) {
			const link = document.createElement('link');
			link.id = 'driver-js-css';
			link.rel = 'stylesheet';
			link.href = 'https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.css';
			document.head.appendChild(link);
		}
	}

	// 5. TOAST ELEGANTE DE RECORRIDO COMPLETADO
	function showTourCompletedToast(dashboardTitle) {
		let toast = document.getElementById('princes-tour-toast');
		if (!toast) {
			toast = document.createElement('div');
			toast.id = 'princes-tour-toast';
			toast.className = 'fixed bottom-5 right-5 z-[999999] max-w-sm p-4 bg-white dark:bg-slate-900 border border-emerald-500/80 rounded-2xl shadow-2xl flex items-start gap-3 transform translate-y-10 opacity-0 transition-all duration-300 pointer-events-auto';
			toast.innerHTML = `
				<div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-950/80 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
					<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
				</div>
				<div class="text-xs">
					<h4 class="font-bold text-slate-900 dark:text-white text-sm">¡Recorrido completado!</h4>
					<p class="text-slate-600 dark:text-slate-400 mt-0.5" id="princes-tour-toast-msg">Has explorado las herramientas clave de este tablero.</p>
					<span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold block mt-1">Puedes repetir este tour cuando desees desde el botón superior.</span>
				</div>
				<button onclick="document.getElementById('princes-tour-toast').classList.add('opacity-0', 'translate-y-10')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-0.5 -mr-1">✕</button>
			`;
			document.body.appendChild(toast);
		}

		const msgEl = document.getElementById('princes-tour-toast-msg');
		if (msgEl) {
			msgEl.textContent = `Has explorado las herramientas esenciales del tablero de ${dashboardTitle}.`;
		}

		setTimeout(() => {
			toast.classList.remove('opacity-0', 'translate-y-10');
			toast.classList.add('opacity-100', 'translate-y-0');
		}, 50);

		setTimeout(() => {
			if (toast) {
				toast.classList.remove('opacity-100', 'translate-y-0');
				toast.classList.add('opacity-0', 'translate-y-10');
			}
		}, 5500);
	}

	return {
		start: startTour,
		configs: dashboardToursConfig
	};
})();

// Función global del recorrido guiado
function startDashboardTour(dashboardKey) {
	window.GuidedDashboardTour.start(dashboardKey);
}
</script>
