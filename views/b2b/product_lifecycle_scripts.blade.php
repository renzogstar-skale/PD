<script>
/**
 * Prince Distributors Wholesale B2B Suite — Ciclo de Vida del Producto e Inteligencia Comercial
 * Lógica Reactiva, Gráficos Chart.js, Simulador de Markdown e Integraciones
 */

(function() {
	// Estado global del módulo de Ciclo de Vida
	window.plcState = {
		period: 'MTD',
		branch: 'ALL',
		category: 'ALL',
		stage: 'ALL',
		search: '',
		recFilter: 'ALL',
		currentSimProduct: 12,
		currentSimDiscount: 25,
		charts: {},
		productsCache: [],
		sampleImportData: []
	};

	// Inicialización al cargar o al navegar a la sección
	document.addEventListener('DOMContentLoaded', function() {
		// Inicializar si la sección activa inicial es product-lifecycle
		const currentSec = document.body.dataset.initialSection || '';
		if (currentSec === 'product-lifecycle' || currentSec === 'lifecycle') {
			initLifecycleDashboard();
		}
	});

	// Hook llamado por navigateToSection('product-lifecycle')
	window.onSectionActivated = window.onSectionActivated || {};
	window.onSectionActivated['product-lifecycle'] = function() {
		initLifecycleDashboard();
	};

	function initLifecycleDashboard() {
		loadLifecycleKPIs();
		loadDistributionAndCurves();
		loadProfitabilityMatrix();
		loadDemandForecastChart();
		loadLifecycleRecommendations();
		if (typeof lucide !== 'undefined') lucide.createIcons();
	}

	window.refreshLifecycleData = function() {
		const icon = document.getElementById('btn-refresh-icon');
		if (icon) icon.classList.add('animate-spin');

		Promise.all([
			loadLifecycleKPIs(),
			loadDistributionAndCurves(),
			loadProfitabilityMatrix(),
			loadDemandForecastChart(),
			loadLifecycleRecommendations()
		]).then(() => {
			setTimeout(() => {
				if (icon) icon.classList.remove('animate-spin');
				const ts = document.getElementById('plc-sync-timestamp');
				if (ts) ts.innerText = 'Actualizado ' + new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
			}, 400);
		}).catch(err => {
			if (icon) icon.classList.remove('animate-spin');
			console.error("Error refreshing lifecycle data:", err);
		});
	};

	// Filtros reactivos
	window.setLifecyclePeriod = function(period) {
		window.plcState.period = period;
		['7D', 'MTD', 'QTD', 'YTD'].forEach(p => {
			const btn = document.getElementById('plc-period-' + p);
			if (btn) {
				if (p === period) {
					btn.className = 'px-3 py-1 font-semibold rounded-md bg-white dark:bg-slate-700 text-indigo-700 dark:text-white shadow-xs transition-all';
				} else {
					btn.className = 'px-3 py-1 font-medium rounded-md text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-all';
				}
			}
		});
		window.refreshLifecycleData();
	};

	window.applyLifecycleFilters = function() {
		const branchSel = document.getElementById('plc-filter-branch');
		const catSel = document.getElementById('plc-filter-category');
		const stageSel = document.getElementById('plc-filter-stage');

		if (branchSel) window.plcState.branch = branchSel.value;
		if (catSel) window.plcState.category = catSel.value;
		if (stageSel) window.plcState.stage = stageSel.value;

		window.refreshLifecycleData();
	};

	window.filterByStageQuick = function(stage) {
		const stageSel = document.getElementById('plc-filter-stage');
		if (stageSel) {
			stageSel.value = stage;
			window.applyLifecycleFilters();
		}
	};

	// 1. Cargar y actualizar los 12 KPIs
	async function loadLifecycleKPIs() {
		try {
			const params = new URLSearchParams({
				period: window.plcState.period,
				branch: window.plcState.branch,
				category: window.plcState.category,
				stage: window.plcState.stage
			});
			const res = await fetch('/api/b2b/lifecycle/kpis?' + params.toString());
			const data = await res.json();
			if (!data.success || !data.kpis) return;

			const k = data.kpis;
			updateKpiElement('val-kpi-sell-through', 'badge-kpi-sell-through', k.sell_through);
			updateKpiElement('val-kpi-turnover', 'badge-kpi-turnover', k.turnover);
			updateKpiElement('val-kpi-coverage', 'badge-kpi-coverage', k.coverage_dio);
			updateKpiElement('val-kpi-gmroi', 'badge-kpi-gmroi', k.gmroi);
			updateKpiElement('val-kpi-gmros', 'badge-kpi-gmros', k.gmros);
			updateKpiElement('val-kpi-growth', 'badge-kpi-growth', k.growth_skus);
			updateKpiElement('val-kpi-maturity', 'badge-kpi-maturity', k.maturity_skus);
			updateKpiElement('val-kpi-decline', 'badge-kpi-decline', k.decline_skus);
			updateKpiElement('val-kpi-immobilized', 'badge-kpi-immobilized', k.immobilized_capital);
			updateKpiElement('val-kpi-stockout', 'badge-kpi-stockout', k.stockout_risk);
			updateKpiElement('val-kpi-margin', 'badge-kpi-margin', k.net_margin_post_discounts);
			updateKpiElement('val-kpi-forecast', 'badge-kpi-forecast', k.forecast_accuracy);
		} catch (e) {
			console.error("Failed to load KPIs:", e);
		}
	}

	function updateKpiElement(valId, badgeId, kpiObj) {
		if (!kpiObj) return;
		const valEl = document.getElementById(valId);
		const badgeEl = document.getElementById(badgeId);
		if (valEl) valEl.innerText = kpiObj.formatted || kpiObj.value;
		if (badgeEl && kpiObj.diff_formatted) badgeEl.innerText = kpiObj.diff_formatted;
	}

	// 2. Gráfico de Curva Canónica del Ciclo de Vida (Chart.js)
	async function loadDistributionAndCurves() {
		try {
			const res = await fetch('/api/b2b/lifecycle/curves');
			const json = await res.json();
			if (!json.success || !json.curves) return;

			const curves = json.curves;
			const ctx = document.getElementById('chart-plc-lifecycle-curve');
			if (!ctx) return;

			if (window.plcState.charts.curve) {
				window.plcState.charts.curve.destroy();
			}

			const isDark = document.documentElement.classList.contains('dark');
			const textColor = isDark ? '#94A3B8' : '#64748B';
			const gridColor = isDark ? 'rgba(51, 65, 85, 0.4)' : 'rgba(226, 232, 240, 0.8)';

			window.plcState.charts.curve = new Chart(ctx.getContext('2d'), {
				type: 'line',
				data: {
					labels: curves.labels,
					datasets: [
						{
							label: 'Ventas ($k)',
							data: curves.series[0].data,
							borderColor: '#3B82F6',
							backgroundColor: 'rgba(59, 130, 246, 0.1)',
							fill: true,
							tension: 0.35,
							pointRadius: 3,
							yAxisID: 'y'
						},
						{
							label: 'Margen Bruto (%)',
							data: curves.series[1].data,
							borderColor: '#10B981',
							backgroundColor: 'transparent',
							borderDash: [4, 4],
							tension: 0.3,
							pointRadius: 3,
							yAxisID: 'y1'
						},
						{
							label: 'Inventario a Costo ($k)',
							data: curves.series[2].data,
							borderColor: '#F59E0B',
							backgroundColor: 'transparent',
							tension: 0.35,
							pointRadius: 2,
							yAxisID: 'y'
						}
					]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					interaction: { mode: 'index', intersect: false },
					scales: {
						x: {
							grid: { color: gridColor },
							ticks: { color: textColor, font: { size: 10 } }
						},
						y: {
							type: 'linear',
							display: true,
							position: 'left',
							grid: { color: gridColor },
							ticks: { color: textColor, font: { size: 10 } }
						},
						y1: {
							type: 'linear',
							display: true,
							position: 'right',
							grid: { drawOnChartArea: false },
							ticks: {
								color: textColor,
								font: { size: 10 },
								callback: function(v) { return v + '%'; }
							}
						}
					},
					plugins: {
						legend: { display: false }
					}
				}
			});
		} catch (e) {
			console.error("Failed to load lifecycle curves:", e);
		}
	}

	// 3. Matriz de Rentabilidad y Rotación (Scatter Plot)
	async function loadProfitabilityMatrix() {
		try {
			const res = await fetch('/api/b2b/lifecycle/matrix');
			const json = await res.json();
			if (!json.success || !json.matrix) return;

			const matrix = json.matrix;
			const ctx = document.getElementById('chart-plc-matrix-scatter');
			if (!ctx) return;

			if (window.plcState.charts.matrix) {
				window.plcState.charts.matrix.destroy();
			}

			const isDark = document.documentElement.classList.contains('dark');
			const textColor = isDark ? '#94A3B8' : '#64748B';
			const gridColor = isDark ? 'rgba(51, 65, 85, 0.4)' : 'rgba(226, 232, 240, 0.8)';

			const stageColors = {
				introduction: '#3B82F6',
				growth: '#10B981',
				maturity: '#6366F1',
				decline: '#F59E0B',
				exit_renewal: '#EF4444'
			};

			const datasets = matrix.items.map(item => ({
				label: item.name,
				data: [{ x: item.turnover, y: item.gross_margin, r: item.radius }],
				backgroundColor: stageColors[item.stage] || '#64748B',
				borderColor: isDark ? '#1E293B' : '#FFFFFF',
				borderWidth: 1.5,
				itemMeta: item
			}));

			window.plcState.charts.matrix = new Chart(ctx.getContext('2d'), {
				type: 'bubble',
				data: { datasets },
				options: {
					responsive: true,
					maintainAspectRatio: false,
					onClick: (evt, activeEls) => {
						if (activeEls.length > 0) {
							const dsIndex = activeEls[0].datasetIndex;
							const meta = datasets[dsIndex].itemMeta;
							openSkuDrilldown(meta);
						}
					},
					scales: {
						x: {
							title: { display: true, text: 'Rotación (Turnover x)', color: textColor, font: { size: 10 } },
							grid: { color: gridColor },
							ticks: { color: textColor, font: { size: 10 } },
							suggestedMin: 0,
							suggestedMax: 2.8
						},
						y: {
							title: { display: true, text: 'Margen Bruto (%)', color: textColor, font: { size: 10 } },
							grid: { color: gridColor },
							ticks: { color: textColor, font: { size: 10 }, callback: v => v + '%' },
							suggestedMin: 10,
							suggestedMax: 42
						}
					},
					plugins: {
						legend: { display: false },
						tooltip: {
							callbacks: {
								title: (items) => {
									const ds = datasets[items[0].datasetIndex];
									return ds.itemMeta.sku + ': ' + ds.itemMeta.name;
								},
								label: (item) => {
									const meta = datasets[item.datasetIndex].itemMeta;
									return [
										'Etapa: ' + meta.stage.toUpperCase(),
										'Rotación: ' + meta.turnover + 'x',
										'Margen Bruto: ' + meta.gross_margin + '%',
										'Inventario: $' + meta.inventory_value.toLocaleString(),
										'Días de inventario: ' + meta.dio
									];
								}
							}
						}
					}
				}
			});
		} catch (e) {
			console.error("Failed to load matrix chart:", e);
		}
	}

	// 4. Gráfico de Demanda Real vs. Pronosticada (Holt-Winters)
	async function loadDemandForecastChart() {
		try {
			const res = await fetch('/api/b2b/lifecycle/demand-trend');
			const json = await res.json();
			if (!json.success || !json.trend) return;

			const trend = json.trend;
			const ctx = document.getElementById('chart-plc-demand-forecast');
			if (!ctx) return;

			if (window.plcState.charts.forecast) {
				window.plcState.charts.forecast.destroy();
			}

			const isDark = document.documentElement.classList.contains('dark');
			const textColor = isDark ? '#94A3B8' : '#64748B';
			const gridColor = isDark ? 'rgba(51, 65, 85, 0.4)' : 'rgba(226, 232, 240, 0.8)';

			window.plcState.charts.forecast = new Chart(ctx.getContext('2d'), {
				type: 'line',
				data: {
					labels: trend.periods,
					datasets: [
						{
							label: 'Demanda Real (Uds)',
							data: trend.real_demand,
							borderColor: '#3B82F6',
							backgroundColor: 'rgba(59, 130, 246, 0.15)',
							fill: true,
							tension: 0.25,
							pointRadius: 4,
							pointBackgroundColor: '#3B82F6'
						},
						{
							label: 'Pronóstico Ajustado (Holt-Winters)',
							data: trend.forecast_baseline,
							borderColor: '#6366F1',
							borderDash: [5, 4],
							tension: 0.25,
							pointRadius: 3
						},
						{
							label: 'Punto de reposición',
							data: trend.safety_stock_line,
							borderColor: '#EF4444',
							borderDash: [2, 2],
							pointRadius: 0
						}
					]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					scales: {
						x: {
							grid: { color: gridColor },
							ticks: { color: textColor, font: { size: 10 } }
						},
						y: {
							grid: { color: gridColor },
							ticks: { color: textColor, font: { size: 10 } }
						}
					},
					plugins: {
						legend: {
							display: true,
							position: 'top',
							labels: { color: textColor, font: { size: 10 }, boxWidth: 12 }
						}
					}
				}
			});
		} catch (e) {
			console.error("Failed to load demand forecast:", e);
		}
	}

	// 5. Cargar Recomendaciones Comerciales y Reposición
	async function loadLifecycleRecommendations() {
		try {
			const container = document.getElementById('plc-recommendations-list');
			if (!container) return;

			const params = new URLSearchParams({
				status: window.plcState.recFilter,
				priority: 'ALL'
			});
			const res = await fetch('/api/b2b/lifecycle/recommendations?' + params.toString());
			const json = await res.json();
			if (!json.success || !json.recommendations) return;

			const list = json.recommendations;
			if (list.length === 0) {
				container.innerHTML = `
					<div class="text-center py-8 text-xs text-slate-500">
						No hay recomendaciones pendientes bajo este filtro.
					</div>
				`;
				return;
			}

			const priorityColors = {
				urgent: 'bg-red-100 text-red-800 dark:bg-red-950/80 dark:text-red-300 border-red-200',
				high: 'bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300 border-amber-200',
				medium: 'bg-blue-100 text-blue-800 dark:bg-blue-950/80 dark:text-blue-300 border-blue-200',
				low: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border-slate-200'
			};

			const priorityLabels = {
				urgent: 'Urgente',
				high: 'Alta Prioridad',
				medium: 'Media',
				low: 'Baja'
			};

			container.innerHTML = list.map(rec => {
				const isPending = rec.status === 'pending';
				const isApproved = rec.status === 'approved';

				return `
					<div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 hover:bg-white dark:hover:bg-slate-800/60 transition-all flex flex-col md:flex-row md:items-center justify-between gap-4">
						<div class="space-y-1.5 max-w-3xl">
							<div class="flex items-center gap-2">
								<span class="px-2 py-0.5 rounded-full text-[10px] font-bold border ${priorityColors[rec.priority] || priorityColors.medium}">
									${priorityLabels[rec.priority] || rec.priority}
								</span>
								<h3 class="text-sm font-bold text-slate-900 dark:text-white">${rec.title}</h3>
								<span class="text-xs font-mono text-slate-400">• ${rec.sku} (${rec.category})</span>
							</div>

							<p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
								<strong>Problema:</strong> ${rec.problem_detected}
							</p>

							<p class="text-xs text-indigo-700 dark:text-indigo-300 leading-relaxed bg-indigo-50/60 dark:bg-indigo-950/40 p-2 rounded-lg border border-indigo-100 dark:border-indigo-900/60">
								<strong>Acción sugerida:</strong> ${rec.suggested_action}
							</p>

							<div class="flex items-center gap-4 text-[11px] text-slate-500 mt-1">
								<span>Impacto Económico: <strong class="text-emerald-600 font-mono">+$${Number(rec.financial_impact_usd).toLocaleString()} USD</strong></span>
								<span>Confianza: <strong class="text-slate-700 dark:text-slate-300 font-mono">${rec.confidence_score}%</strong></span>
								<span>Estado: <strong class="${isApproved ? 'text-emerald-600' : (isPending ? 'text-amber-600' : 'text-slate-400')}">${isApproved ? 'Aprobada' : (isPending ? 'Pendiente' : 'Rechazada')}</strong></span>
							</div>
						</div>

						<div class="flex items-center gap-2 shrink-0">
							${isPending ? `
								<button onclick="reviewRecAction(${rec.id}, 'approved')" class="px-3 py-1.5 text-xs font-bold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition-colors flex items-center gap-1">
									<i data-lucide="check" class="w-3.5 h-3.5"></i>
									<span>Aprobar</span>
								</button>
								<button onclick="reviewRecAction(${rec.id}, 'rejected')" class="px-2.5 py-1.5 text-xs font-medium rounded-lg bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 text-slate-700 dark:text-slate-200 transition-colors">
									Descartar
								</button>
							` : `
								<span class="px-3 py-1 text-xs font-bold rounded-lg bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-300">
									✓ Autorizado por Dirección
								</span>
							`}
						</div>
					</div>
				`;
			}).join('');

			if (typeof lucide !== 'undefined') lucide.createIcons();
		} catch (e) {
			console.error("Failed to load recommendations:", e);
		}
	}

	window.filterRecommendations = function(status) {
		window.plcState.recFilter = status;
		['ALL', 'pending', 'approved'].forEach(s => {
			const btn = document.getElementById('plc-rec-btn-' + s);
			if (btn) {
				if (s === status) {
					btn.className = 'px-2.5 py-1 text-xs font-semibold rounded-md bg-indigo-100 dark:bg-indigo-900/60 text-indigo-800 dark:text-indigo-300';
				} else {
					btn.className = 'px-2.5 py-1 text-xs font-medium rounded-md text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white';
				}
			}
		});
		loadLifecycleRecommendations();
	};

	window.reviewRecAction = async function(id, newStatus) {
		try {
			const res = await fetch(`/api/b2b/lifecycle/recommendations/${id}/review`, {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({
					status: newStatus,
					reviewed_by: 'Carlos Mendoza (Dir. Ventas)',
					notes: 'Autorizado desde el módulo de Ciclo de Vida del Producto'
				})
			});
			const json = await res.json();
			if (json.success) {
				loadLifecycleRecommendations();
			} else {
				alert('Error al registrar la acción.');
			}
		} catch (e) {
			console.error("Failed to review recommendation:", e);
		}
	};

	// 6. Modal de Diagnóstico SKU
	window.openKpiDrilldownModal = async function(kpiKey) {
		try {
			const res = await fetch('/api/b2b/lifecycle/products');
			const json = await res.json();
			if (json.success && json.products.length > 0) {
				// Buscar producto relevante según el KPI
				let selected = json.products[0];
				if (kpiKey === 'growth_skus' || kpiKey === 'stockout_risk') {
					selected = json.products.find(p => p.lifecycle_stage === 'growth') || selected;
				} else if (kpiKey === 'decline_skus' || kpiKey === 'immobilized_capital') {
					selected = json.products.find(p => p.lifecycle_stage === 'decline' || p.lifecycle_stage === 'exit_renewal') || selected;
				}
				openSkuDrilldown(selected);
			}
		} catch (e) {
			console.error("Failed to open KPI drilldown:", e);
		}
	};

	window.openSkuDrilldown = function(prod) {
		const modal = document.getElementById('plc-modal-sku-drilldown');
		if (!modal) return;

		window.plcState.currentSkuDetails = prod;

		document.getElementById('modal-sku-title').innerText = prod.name;
		document.getElementById('modal-sku-code').innerText = `${prod.sku} • ${prod.brand || 'Prince'} • ${prod.category}`;
		document.getElementById('modal-sku-sales').innerText = '$' + Number(prod.sales_amount || 0).toLocaleString();
		document.getElementById('modal-sku-units').innerText = Number(prod.sales_units || 0).toLocaleString() + ' unidades';
		document.getElementById('modal-sku-margin').innerText = Number(prod.gross_margin_pct || prod.gross_margin || 0).toFixed(1) + '%';
		document.getElementById('modal-sku-stock').innerText = Number(prod.current_stock_units || prod.stock || 0).toLocaleString() + ' uds';
		document.getElementById('modal-sku-inv-value').innerText = '$' + Number(prod.inventory_value || 0).toLocaleString() + ' a costo';
		document.getElementById('modal-sku-dio').innerText = Number(prod.dio_days || prod.dio || 0).toFixed(1) + ' días';
		document.getElementById('modal-sku-reason').innerText = prod.classification_reason || 'Producto clasificado por algoritmos de tendencia y cobertura.';

		const badge = document.getElementById('modal-sku-stage-badge');
		if (badge) {
			badge.innerText = (prod.lifecycle_stage || prod.stage || '').toUpperCase();
		}

		modal.classList.remove('hidden');
		if (typeof lucide !== 'undefined') lucide.createIcons();
	};

	window.closeSkuDrilldownModal = function() {
		const modal = document.getElementById('plc-modal-sku-drilldown');
		if (modal) modal.classList.add('hidden');
	};

	window.openMarkdownSimulatorFromCurrentSku = function() {
		closeSkuDrilldownModal();
		const prod = window.plcState.currentSkuDetails;
		if (prod) {
			openMarkdownSimulatorModal(prod.id || prod.product_id);
		} else {
			openMarkdownSimulatorModal(12);
		}
	};

	// 7. Modal de Simulador de Markdown
	window.openMarkdownSimulatorModal = function(productId = 12) {
		const modal = document.getElementById('plc-modal-markdown');
		if (!modal) return;

		window.plcState.currentSimProduct = productId;
		const sel = document.getElementById('sim-product-select');
		if (sel) sel.value = productId;

		setSimDiscount(window.plcState.currentSimDiscount || 25);
		modal.classList.remove('hidden');
		if (typeof lucide !== 'undefined') lucide.createIcons();
	};

	window.closeMarkdownModal = function() {
		const modal = document.getElementById('plc-modal-markdown');
		if (modal) modal.classList.add('hidden');
	};

	window.setSimDiscount = function(pct) {
		window.plcState.currentSimDiscount = pct;
		[5, 10, 15, 20, 25, 30].forEach(p => {
			const b = document.getElementById('sim-btn-' + p);
			if (b) {
				if (p === pct) {
					b.className = 'px-3 py-1.5 text-xs font-semibold rounded-lg bg-indigo-600 text-white shadow-xs';
				} else {
					b.className = 'px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200';
				}
			}
		});
		runMarkdownSimulation();
	};

	window.runMarkdownSimulation = async function() {
		try {
			const sel = document.getElementById('sim-product-select');
			const pid = sel ? parseInt(sel.value) : 12;
			const discount = window.plcState.currentSimDiscount || 25;

			const res = await fetch('/api/b2b/lifecycle/simulate-markdown', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({
					product_id: pid,
					discount_pct: discount,
					elasticity: 1.4
				})
			});
			const json = await res.json();
			if (!json.success || !json.simulation) return;

			const s = json.simulation;
			window.plcState.lastSimResult = s;

			document.getElementById('sim-res-price').innerText = '$' + s.proposed_price.toFixed(2);
			document.getElementById('sim-res-orig-price').innerText = '$' + s.original_price.toFixed(2) + ' original';
			document.getElementById('sim-res-unit-margin').innerText = '$' + s.unit_margin.toFixed(2) + '/ud';
			document.getElementById('sim-res-cost').innerText = 'Costo: $' + s.unit_cost.toFixed(2);
			document.getElementById('sim-res-recov-capital').innerText = '$' + Number(s.recoverable_capital_usd).toLocaleString();
			document.getElementById('sim-res-margin-loss').innerText = '-$' + Number(s.margin_loss_usd).toLocaleString();
			document.getElementById('sim-res-breakeven').innerText = Number(s.breakeven_units).toLocaleString() + ' uds';
		} catch (e) {
			console.error("Simulation error:", e);
		}
	};

	window.saveMarkdownScenarioSubmit = async function() {
		try {
			const s = window.plcState.lastSimResult;
			if (!s) return;

			const res = await fetch('/api/b2b/lifecycle/save-markdown', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify(s)
			});
			const json = await res.json();
			if (json.success) {
				alert("✓ Escenario guardado exitosamente. Sometido a autorización de Dirección Comercial.");
				closeMarkdownModal();
			} else {
				alert("Error al guardar escenario.");
			}
		} catch (e) {
			console.error("Failed to save markdown:", e);
		}
	};

	// 8. Modal de Importación de Datos
	window.openDataImportModal = function() {
		const modal = document.getElementById('plc-modal-import');
		if (modal) {
			modal.classList.remove('hidden');
			if (typeof lucide !== 'undefined') lucide.createIcons();
		}
	};

	window.closeDataImportModal = function() {
		const modal = document.getElementById('plc-modal-import');
		if (modal) modal.classList.add('hidden');
	};

	window.loadSampleCsvData = function() {
		const sampleRows = [
			{ sku: 'SKU-IMP-001', name: 'Bebida de Té Verde Limón 450ml', category: 'Bebidas', brand: 'Prince Tea', sales_units: 320, sales_amount: 640.0, cogs: 440.0, stock: 1500 },
			{ sku: 'SKU-IMP-002', name: 'Queso Crema Untable Pote 220g', category: 'Lácteos', brand: 'Laive Fresh', sales_units: 890, sales_amount: 2670.0, cogs: 1800.0, stock: 420 },
			{ sku: 'SKU-IMP-003', name: 'Galleta Vainilla Rellena 12pk', category: 'Abarrotes', brand: 'San Jorge', sales_units: 45, sales_amount: 135.0, cogs: 110.0, stock: 2400 }
		];
		window.plcState.sampleImportData = sampleRows;

		const tbody = document.getElementById('import-preview-tbody');
		const countEl = document.getElementById('import-preview-count');
		const box = document.getElementById('import-preview-box');
		const btnConfirm = document.getElementById('btn-execute-import');

		if (tbody && countEl && box && btnConfirm) {
			tbody.innerHTML = sampleRows.map(r => `
				<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
					<td class="p-2 font-mono font-bold text-indigo-600">${r.sku}</td>
					<td class="p-2 text-slate-800 dark:text-slate-200">${r.name}</td>
					<td class="p-2 font-mono text-slate-600">$${r.sales_amount.toFixed(2)}</td>
					<td class="p-2 font-mono text-slate-600">${r.stock} uds</td>
					<td class="p-2 text-center">
						<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Válido</span>
					</td>
				</tr>
			`).join('');
			countEl.innerText = `${sampleRows.length} filas detectadas correctamente`;
			box.classList.remove('hidden');
			btnConfirm.disabled = false;
		}
	};

	window.confirmExecuteImport = async function() {
		try {
			const rows = window.plcState.sampleImportData;
			if (!rows || rows.length === 0) return;

			const res = await fetch('/api/b2b/lifecycle/import', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({
					filename: 'catalogo_retail_sample.csv',
					rows: rows,
					column_mapping: {
						sku: 'sku',
						name: 'name',
						category: 'category',
						brand: 'brand',
						sales_units: 'sales_units',
						sales_amount: 'sales_amount',
						cogs: 'cogs',
						current_stock: 'stock'
					}
				})
			});
			const json = await res.json();
			if (json.success) {
				alert(`✓ Importación completada: ${json.valid_rows} productos procesados y clasificados.`);
				closeDataImportModal();
				refreshLifecycleData();
			} else {
				alert('Error en importación: ' + (json.error || 'Verifique el archivo'));
			}
		} catch (e) {
			console.error("Failed to execute import:", e);
		}
	};

})();
</script>
