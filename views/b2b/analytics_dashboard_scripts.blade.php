<script>
/**
 * Prince Distributors B2B — Executive Dashboards Controller & Analytics Engine
 * Connects 12 Executive Dashboards to the Unified Analytics API (/api/b2b/analytics/{module})
 * Renders 8 KPI Cards, Secondary Indicators, Trends, Distributions, and Progress bars.
 */

window.b2bAnalyticsCharts = window.b2bAnalyticsCharts || {};
window.b2bAnalyticsState = window.b2bAnalyticsState || {
	periods: {}
};

// ==========================================
// UTILITY FUNCTIONS & DOM HELPERS
// ==========================================

function setSafeText(id, text) {
	const el = document.getElementById(id);
	if (el) {
		el.textContent = (text !== null && text !== undefined) ? text : '—';
	}
}

function setSafeHtml(id, html) {
	const el = document.getElementById(id);
	if (el) {
		el.innerHTML = html;
	}
}

function setSafeWidth(id, pct) {
	const el = document.getElementById(id);
	if (el) {
		const clamped = Math.max(0, Math.min(100, parseFloat(pct) || 0));
		el.style.width = clamped + '%';
	}
}

function renderOrCreateChart(canvasId, chartType, data, customOptions) {
	const canvas = document.getElementById(canvasId);
	if (!canvas) return null;

	if (window.b2bAnalyticsCharts[canvasId]) {
		try {
			window.b2bAnalyticsCharts[canvasId].destroy();
		} catch (e) {
			console.warn('Error destroying chart', canvasId, e);
		}
		delete window.b2bAnalyticsCharts[canvasId];
	}

	const isDark = document.documentElement.classList.contains('dark');
	const textColor = isDark ? '#94a3b8' : '#64748b';
	const gridColor = isDark ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.05)';

	const baseOptions = {
		responsive: true,
		maintainAspectRatio: false,
		legend: {
			display: (data.datasets && data.datasets.length > 1) || chartType === 'doughnut' || chartType === 'pie',
			position: 'top',
			labels: {
				boxWidth: 10,
				fontSize: 10,
				fontColor: textColor,
				fontFamily: 'Inter, system-ui, sans-serif'
			}
		},
		tooltips: {
			mode: 'index',
			intersect: false,
			backgroundColor: isDark ? 'rgba(15, 23, 42, 0.95)' : 'rgba(15, 23, 42, 0.9)',
			titleFontSize: 11,
			bodyFontSize: 10,
			cornerRadius: 6,
			bodySpacing: 4,
			xPadding: 10,
			yPadding: 8
		}
	};

	if (chartType !== 'doughnut' && chartType !== 'pie' && chartType !== 'radar') {
		baseOptions.scales = {
			xAxes: [{
				gridLines: { display: false, color: gridColor },
				ticks: { fontSize: 10, fontColor: textColor, maxRotation: 45, minRotation: 0 }
			}],
			yAxes: [{
				gridLines: { color: gridColor, drawBorder: false },
				ticks: { fontSize: 10, fontColor: textColor, beginAtZero: true }
			}]
		};
	}

	const finalOptions = Object.assign({}, baseOptions, customOptions || {});

	try {
		const ctx = canvas.getContext('2d');
		window.b2bAnalyticsCharts[canvasId] = new Chart(ctx, {
			type: chartType,
			data: data,
			options: finalOptions
		});
		return window.b2bAnalyticsCharts[canvasId];
	} catch (err) {
		console.error('Failed to create chart on canvas ' + canvasId, err);
		return null;
	}
}

// ==========================================
// SUB-TAB VIEW SWITCHERS (EXECUTIVE VS OPERATIONAL)
// ==========================================

function updateSubTabButtons(activeBtnId, inactiveBtnId) {
	const activeBtn = document.getElementById(activeBtnId);
	const inactiveBtn = document.getElementById(inactiveBtnId);
	if (activeBtn) {
		activeBtn.className = 'pb-3 border-b-2 border-brand-800 text-brand-800 dark:text-white dark:border-white transition-all flex items-center gap-2 font-semibold text-xs sm:text-sm';
	}
	if (inactiveBtn) {
		inactiveBtn.className = 'pb-3 border-b-2 border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition-all flex items-center gap-2 text-xs sm:text-sm';
	}
}

function switchCustomerSubTab(tab) {
	const execView = document.getElementById('cust-view-exec');
	const manageView = document.getElementById('cust-view-manage');
	if (tab === 'exec') {
		if (execView) execView.classList.remove('hidden');
		if (manageView) manageView.classList.add('hidden');
		updateSubTabButtons('cust-tab-btn-exec', 'cust-tab-btn-manage');
		loadModuleAnalytics('customers');
	} else {
		if (execView) execView.classList.add('hidden');
		if (manageView) manageView.classList.remove('hidden');
		updateSubTabButtons('cust-tab-btn-manage', 'cust-tab-btn-exec');
		if (typeof fetchCustomersList === 'function') fetchCustomersList();
	}
}

function switchProductsSubTab(tab) {
	const execView = document.getElementById('prod-view-exec');
	const manageView = document.getElementById('prod-view-manage');
	if (tab === 'exec') {
		if (execView) execView.classList.remove('hidden');
		if (manageView) manageView.classList.add('hidden');
		updateSubTabButtons('prod-tab-btn-exec', 'prod-tab-btn-manage');
		loadModuleAnalytics('products');
	} else {
		if (execView) execView.classList.add('hidden');
		if (manageView) manageView.classList.remove('hidden');
		updateSubTabButtons('prod-tab-btn-manage', 'prod-tab-btn-exec');
		if (typeof fetchProductsList === 'function') fetchProductsList();
	}
}

function switchOrdersSubTab(tab) {
	const execView = document.getElementById('orders-view-exec');
	const manageView = document.getElementById('orders-view-manage');
	if (tab === 'exec') {
		if (execView) execView.classList.remove('hidden');
		if (manageView) manageView.classList.add('hidden');
		updateSubTabButtons('orders-tab-btn-exec', 'orders-tab-btn-manage');
		loadModuleAnalytics('orders');
	} else {
		if (execView) execView.classList.add('hidden');
		if (manageView) manageView.classList.remove('hidden');
		updateSubTabButtons('orders-tab-btn-manage', 'orders-tab-btn-exec');
		if (typeof fetchOrdersModule === 'function') fetchOrdersModule();
	}
}

// Invoices wrapper to handle both top-level (exec/manage) and internal table sub-tabs
const originalSwitchInvoicesSubTab = window.switchInvoicesSubTab;
window.switchInvoicesSubTab = function(tab) {
	if (tab === 'exec' || tab === 'manage') {
		const execView = document.getElementById('inv-view-exec');
		const manageView = document.getElementById('inv-view-manage');
		if (tab === 'exec') {
			if (execView) execView.classList.remove('hidden');
			if (manageView) manageView.classList.add('hidden');
			updateSubTabButtons('inv-tab-btn-exec', 'inv-tab-btn-manage');
			loadModuleAnalytics('invoices');
		} else {
			if (execView) execView.classList.add('hidden');
			if (manageView) manageView.classList.remove('hidden');
			updateSubTabButtons('inv-tab-btn-manage', 'inv-tab-btn-exec');
			if (typeof fetchInvoicesModule === 'function') fetchInvoicesModule();
		}
	} else {
		if (typeof originalSwitchInvoicesSubTab === 'function') {
			originalSwitchInvoicesSubTab(tab);
		}
	}
};

// Sales Reps wrapper to handle both top-level (exec/manage) and internal tabs
const originalSwitchSalesRepsSubTab = window.switchSalesRepsSubTab;
window.switchSalesRepsSubTab = function(tab) {
	if (tab === 'exec' || tab === 'manage') {
		const execView = document.getElementById('reps-view-exec');
		const manageView = document.getElementById('reps-view-manage');
		if (tab === 'exec') {
			if (execView) execView.classList.remove('hidden');
			if (manageView) manageView.classList.add('hidden');
			updateSubTabButtons('reps-tab-btn-exec', 'reps-tab-btn-manage');
			loadModuleAnalytics('sales-reps');
		} else {
			if (execView) execView.classList.add('hidden');
			if (manageView) manageView.classList.remove('hidden');
			updateSubTabButtons('reps-tab-btn-manage', 'reps-tab-btn-exec');
			if (typeof fetchSalesRepsModule === 'function') fetchSalesRepsModule();
		}
	} else {
		if (typeof originalSwitchSalesRepsSubTab === 'function') {
			originalSwitchSalesRepsSubTab(tab);
		}
	}
};

function switchProfitabilitySubTabMain(tab) {
	const execView = document.getElementById('prof-view-exec');
	const manageView = document.getElementById('prof-view-manage');
	if (tab === 'exec') {
		if (execView) execView.classList.remove('hidden');
		if (manageView) manageView.classList.add('hidden');
		updateSubTabButtons('prof-tab-btn-exec', 'prof-tab-btn-manage');
		loadModuleAnalytics('profitability');
	} else {
		if (execView) execView.classList.add('hidden');
		if (manageView) manageView.classList.remove('hidden');
		updateSubTabButtons('prof-tab-btn-manage', 'prof-tab-btn-exec');
		if (typeof fetchProfitabilityModule === 'function') fetchProfitabilityModule();
	}
}

function switchReportsSubTab(tab) {
	const execView = document.getElementById('intel-view-exec');
	const manageView = document.getElementById('intel-view-manage');
	if (tab === 'exec') {
		if (execView) execView.classList.remove('hidden');
		if (manageView) manageView.classList.add('hidden');
		updateSubTabButtons('intel-tab-btn-exec', 'intel-tab-btn-manage');
		loadModuleAnalytics('reports');
	} else {
		if (execView) execView.classList.add('hidden');
		if (manageView) manageView.classList.remove('hidden');
		updateSubTabButtons('intel-tab-btn-manage', 'intel-tab-btn-exec');
		if (typeof fetchIntelligenceModule === 'function') fetchIntelligenceModule();
	}
}

function switchOperationsSubTab(tab) {
	const execView = document.getElementById('ops-view-exec');
	const manageView = document.getElementById('ops-view-manage');
	if (tab === 'exec') {
		if (execView) execView.classList.remove('hidden');
		if (manageView) manageView.classList.add('hidden');
		updateSubTabButtons('ops-tab-btn-exec', 'ops-tab-btn-manage');
		loadModuleAnalytics('operations');
	} else {
		if (execView) execView.classList.add('hidden');
		if (manageView) manageView.classList.remove('hidden');
		updateSubTabButtons('ops-tab-btn-manage', 'ops-tab-btn-exec');
		if (typeof refreshOperationsConsole === 'function') refreshOperationsConsole();
	}
}

function switchEmployeesSubTab(tab) {
	const execView = document.getElementById('emp-view-exec');
	const manageView = document.getElementById('emp-view-manage');
	if (tab === 'exec') {
		if (execView) execView.classList.remove('hidden');
		if (manageView) manageView.classList.add('hidden');
		updateSubTabButtons('emp-tab-btn-exec', 'emp-tab-btn-manage');
		loadModuleAnalytics('employees');
	} else {
		if (execView) execView.classList.add('hidden');
		if (manageView) manageView.classList.remove('hidden');
		updateSubTabButtons('emp-tab-btn-manage', 'emp-tab-btn-exec');
		if (typeof loadWfmEmployees === 'function') loadWfmEmployees();
	}
}

function switchAttendanceSubTab(tab) {
	const execView = document.getElementById('att-view-exec');
	const manageView = document.getElementById('att-view-manage');
	if (tab === 'exec') {
		if (execView) execView.classList.remove('hidden');
		if (manageView) manageView.classList.add('hidden');
		updateSubTabButtons('att-tab-btn-exec', 'att-tab-btn-manage');
		loadModuleAnalytics('attendance');
	} else {
		if (execView) execView.classList.add('hidden');
		if (manageView) manageView.classList.remove('hidden');
		updateSubTabButtons('att-tab-btn-manage', 'att-tab-btn-exec');
	}
}

function switchRequestsSubTab(tab) {
	const execView = document.getElementById('req-view-exec');
	const manageView = document.getElementById('req-view-manage');
	if (tab === 'exec') {
		if (execView) execView.classList.remove('hidden');
		if (manageView) manageView.classList.add('hidden');
		updateSubTabButtons('req-tab-btn-exec', 'req-tab-btn-manage');
		loadModuleAnalytics('shift-requests');
	} else {
		if (execView) execView.classList.add('hidden');
		if (manageView) manageView.classList.remove('hidden');
		updateSubTabButtons('req-tab-btn-manage', 'req-tab-btn-exec');
		if (typeof loadWfmRequests === 'function') loadWfmRequests();
	}
}

// ==========================================
// CORE ANALYTICS LOADER & DISPATCHER
// ==========================================

async function loadModuleAnalytics(moduleName, forcedPeriod) {
	// Determinar período
	let period = forcedPeriod;
	if (!period) {
		const periodSelectIds = {
			'customers': 'cust-analytics-period',
			'products': 'prod-analytics-period',
			'orders': 'orders-analytics-period',
			'invoices': 'inv-analytics-period',
			'sales-reps': 'reps-analytics-period',
			'profitability': 'prof-analytics-period',
			'reports': 'intel-analytics-period',
			'schedules': 'wfm-sch-analytics-period',
			'operations': 'oper-analytics-period',
			'employees': 'emp-analytics-period',
			'attendance': 'att-analytics-period',
			'shift-requests': 'req-analytics-period'
		};
		const selectId = periodSelectIds[moduleName];
		if (selectId) {
			const selectEl = document.getElementById(selectId);
			if (selectEl) period = selectEl.value;
		}
	}
	period = period || window.b2bAnalyticsState.periods[moduleName] || 'MTD';
	window.b2bAnalyticsState.periods[moduleName] = period;

	try {
		const url = `/api/b2b/analytics/${encodeURIComponent(moduleName)}?period=${encodeURIComponent(period)}`;
		const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
		if (!res.ok) throw new Error(`HTTP ${res.status}: ${res.statusText}`);
		const json = await res.json();
		if (!json || !json.kpis) throw new Error('Respuesta analítica inválida');

		// Despachar renderizado específico
		switch (moduleName) {
			case 'customers': renderCustomersAnalytics(json); break;
			case 'products': renderProductsAnalytics(json); break;
			case 'orders': renderOrdersAnalytics(json); break;
			case 'invoices': renderInvoicesAnalytics(json); break;
			case 'sales-reps': renderSalesRepsAnalytics(json); break;
			case 'profitability': renderProfitabilityAnalytics(json); break;
			case 'reports': renderReportsAnalytics(json); break;
			case 'schedules': renderSchedulesAnalytics(json); break;
			case 'operations': renderOperationsAnalytics(json); break;
			case 'employees': renderEmployeesAnalytics(json); break;
			case 'attendance': renderAttendanceAnalytics(json); break;
			case 'shift-requests': renderShiftRequestsAnalytics(json); break;
		}

		if (window.lucide && typeof lucide.createIcons === 'function') {
			lucide.createIcons();
		}
	} catch (err) {
		console.error(`Error cargando analítica de ${moduleName}:`, err);
	}
}

// ==========================================
// 1. CLIENTES Y ALMACENES
// ==========================================
function renderCustomersAnalytics(data) {
	const k = data.kpis || {};
	const c = data.charts || {};

	setSafeText('kpi-cust-active-val', k.active_customers?.formatted);
	setSafeText('kpi-cust-active', k.active_customers?.formatted);
	setSafeHtml('kpi-cust-active-var', `<span class="${k.active_customers?.variance?.class || ''}">${k.active_customers?.variance?.text || 'N/D'}</span>`);

	setSafeText('kpi-cust-new-val', k.new_customers?.formatted);
	setSafeHtml('kpi-cust-new-var', `<span class="${k.new_customers?.variance?.class || ''}">${k.new_customers?.variance?.text || 'N/D'}</span>`);

	setSafeText('kpi-cust-inactive-val', k.churn_rate?.formatted);
	setSafeText('kpi-cust-inactive', k.churn_rate?.formatted);
	setSafeHtml('kpi-cust-inactive-var', `<span class="${k.churn_rate?.variance?.class || ''}">${k.churn_rate?.variance?.text || 'N/D'}</span>`);

	setSafeText('kpi-cust-retention-val', k.retention_rate?.formatted);
	setSafeHtml('kpi-cust-retention-var', `<span class="${k.retention_rate?.variance?.class || ''}">${k.retention_rate?.variance?.text || 'N/D'}</span>`);

	setSafeText('kpi-cust-est-val', k.total_customer_revenue?.formatted);
	setSafeHtml('kpi-cust-est-var', `<span class="${k.total_customer_revenue?.variance?.class || ''}">${k.total_customer_revenue?.variance?.text || 'N/D'}</span>`);

	setSafeText('kpi-cust-freq-val', k.purchase_frequency?.formatted);
	setSafeHtml('kpi-cust-freq-var', `<span class="${k.purchase_frequency?.variance?.class || ''}">${k.purchase_frequency?.variance?.text || 'N/D'}</span>`);

	setSafeText('kpi-cust-overdue-val', k.portfolio_growth?.formatted);
	setSafeHtml('kpi-cust-overdue-var', `<span class="${k.portfolio_growth?.variance?.class || ''}">${k.portfolio_growth?.variance?.text || 'N/D'}</span>`);

	setSafeText('kpi-cust-avg-sales-val', k.avg_revenue_per_customer?.formatted);
	setSafeHtml('kpi-cust-avg-sales-var', `<span class="${k.avg_revenue_per_customer?.variance?.class || ''}">${k.avg_revenue_per_customer?.variance?.text || 'N/D'}</span>`);

	// Secundarios
	setSafeWidth('cust-sec-retention-bar', k.retention_rate?.value);
	setSafeWidth('cust-sec-churn-bar', k.churn_rate?.value);
	setSafeText('cust-sec-rev-target', k.total_customer_revenue?.formatted);
	setSafeText('cust-sec-growth-trend', k.portfolio_growth?.formatted);

	// Gráficos
	if (c.evolution_trend) {
		renderOrCreateChart('chart-cust-evolution', 'line', {
			labels: c.evolution_trend.labels || [],
			datasets: [{
				label: 'Clientes Activos',
				data: c.evolution_trend.values || [],
				borderColor: '#10b981',
				backgroundColor: 'rgba(16, 185, 129, 0.1)',
				fill: true,
				tension: 0.3
			}]
		});
	}

	if (c.segment_distribution) {
		renderOrCreateChart('chart-cust-segmentation', 'doughnut', {
			labels: c.segment_distribution.labels || [],
			datasets: [{
				data: c.segment_distribution.values || [],
				backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#8b5cf6', '#64748b']
			}]
		});
	}

	if (c.revenue_by_type) {
		renderOrCreateChart('chart-cust-territory', 'bar', {
			labels: c.revenue_by_type.labels || [],
			datasets: [{
				label: 'Facturación ($)',
				data: c.revenue_by_type.values || [],
				backgroundColor: '#3b82f6',
				borderRadius: 4
			}]
		});
	}

	if (c.retention_trend) {
		renderOrCreateChart('chart-cust-retention', 'line', {
			labels: c.retention_trend.labels || [],
			datasets: [{
				label: 'Tasa de Retención (%)',
				data: c.retention_trend.values || [],
				borderColor: '#8b5cf6',
				backgroundColor: 'rgba(139, 92, 246, 0.1)',
				fill: true,
				tension: 0.3
			}]
		});
	}
}

// ==========================================
// 2. CATÁLOGO Y PRECIOS
// ==========================================
function renderProductsAnalytics(data) {
	const k = data.kpis || {};
	const c = data.charts || {};

	setSafeText('kpi-prod-active', k.active_products?.formatted);
	setSafeText('kpi-prod-penetration', k.products_with_sales_pct?.formatted);
	setSafeText('kpi-prod-avg-margin', k.avg_gross_margin_pct?.formatted);
	setSafeText('kpi-prod-revenue', k.net_catalog_sales?.formatted);
	setSafeText('kpi-prod-profit', k.total_gross_profit?.formatted);
	setSafeText('kpi-prod-price-var', k.avg_price_variation_pct?.formatted);
	setSafeText('kpi-prod-deadstock', k.zero_movement_products_pct?.formatted);
	setSafeText('kpi-prod-compliance', k.margin_target_compliance_pct?.formatted);

	// Secundarios
	setSafeWidth('prod-sec-active-bar', k.products_with_sales_pct?.value);
	setSafeWidth('prod-sec-zero-mov-bar', k.zero_movement_products_pct?.value);
	setSafeText('prod-sec-target-margin', k.avg_gross_margin_pct?.formatted);
	setSafeText('prod-sec-price-stability', k.avg_price_variation_pct?.formatted);

	if (c.sales_margin_evolution) {
		renderOrCreateChart('chart-prod-category-margin', 'bar', {
			labels: c.sales_margin_evolution.labels || [],
			datasets: [
				{
					label: 'Ventas ($)',
					data: c.sales_margin_evolution.sales || [],
					backgroundColor: '#3b82f6',
					yAxisID: 'y-axis-sales'
				},
				{
					label: 'Margen (%)',
					type: 'line',
					data: c.sales_margin_evolution.margins || [],
					borderColor: '#10b981',
					fill: false,
					yAxisID: 'y-axis-margin'
				}
			]
		}, {
			scales: {
				xAxes: [{ gridLines: { display: false } }],
				yAxes: [
					{ id: 'y-axis-sales', position: 'left', ticks: { beginAtZero: true } },
					{ id: 'y-axis-margin', position: 'right', gridLines: { display: false }, ticks: { beginAtZero: true } }
				]
			}
		});
	}

	if (c.category_distribution) {
		renderOrCreateChart('chart-prod-margin-dist', 'doughnut', {
			labels: c.category_distribution.labels || [],
			datasets: [{
				data: c.category_distribution.values || [],
				backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ec4899', '#8b5cf6']
			}]
		});
	}

	if (c.margin_range_distribution) {
		renderOrCreateChart('chart-prod-price-behavior', 'bar', {
			labels: c.margin_range_distribution.labels || [],
			datasets: [{
				label: 'Distribución de Productos',
				data: c.margin_range_distribution.values || [],
				backgroundColor: '#8b5cf6'
			}]
		});
	}

	if (c.active_vs_inactive_trend) {
		renderOrCreateChart('chart-prod-commercial-activity', 'line', {
			labels: c.active_vs_inactive_trend.labels || [],
			datasets: [
				{
					label: 'SKU Activos',
					data: c.active_vs_inactive_trend.active || [],
					borderColor: '#10b981',
					fill: false
				},
				{
					label: 'Sin Movimiento',
					data: c.active_vs_inactive_trend.inactive || [],
					borderColor: '#f43f5e',
					fill: false
				}
			]
		});
	}
}

// ==========================================
// 3. COTIZACIONES Y PEDIDOS
// ==========================================
function renderOrdersAnalytics(data) {
	const k = data.kpis || {};
	const c = data.charts || {};

	setSafeText('kpi-ord-quotes-issued', k.quotes_issued?.formatted);
	setSafeText('kpi-ord-quotes-val', k.quotes_total_value?.formatted);
	setSafeText('kpi-ord-conversion-rate', k.conversion_rate_pct?.formatted);
	setSafeText('kpi-ord-orders-confirmed', k.orders_received?.formatted);
	setSafeText('kpi-ord-orders-val', k.orders_total_value?.formatted);
	setSafeText('kpi-ord-avg-ticket', k.avg_ticket_order?.formatted);
	setSafeText('kpi-ord-pending-pct', k.pending_orders_pct?.formatted);
	setSafeText('kpi-ord-completed-pct', k.completed_orders_pct?.formatted);

	// Secundarios
	setSafeWidth('ord-sec-conv-bar', k.conversion_rate_pct?.value);
	setSafeWidth('ord-sec-fulfillment-bar', k.completed_orders_pct?.value);
	setSafeText('ord-sec-pending-lead', k.pending_orders_pct?.formatted);
	setSafeText('ord-sec-cancel-rate', k.orders_received?.formatted);

	if (c.commercial_funnel) {
		renderOrCreateChart('chart-ord-funnel', 'horizontalBar', {
			labels: c.commercial_funnel.labels || [],
			datasets: [{
				label: 'Volumen de Órdenes',
				data: c.commercial_funnel.values || [],
				backgroundColor: ['#6366f1', '#3b82f6', '#10b981', '#059669']
			}]
		});
	}

	if (c.orders_evolution) {
		renderOrCreateChart('chart-ord-evolution', 'line', {
			labels: c.orders_evolution.labels || [],
			datasets: [{
				label: 'Monto de Pedidos ($)',
				data: c.orders_evolution.values || [],
				borderColor: '#3b82f6',
				backgroundColor: 'rgba(59, 130, 246, 0.1)',
				fill: true
			}]
		});
	}

	if (c.status_distribution) {
		renderOrCreateChart('chart-ord-status-dist', 'doughnut', {
			labels: c.status_distribution.labels || [],
			datasets: [{
				data: c.status_distribution.values || [],
				backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#f43f5e']
			}]
		});
	}

	if (c.monthly_conversion) {
		renderOrCreateChart('chart-ord-monthly-conversion', 'bar', {
			labels: c.monthly_conversion.labels || [],
			datasets: [{
				label: 'Tasa Conversión (%)',
				data: c.monthly_conversion.values || [],
				backgroundColor: '#10b981'
			}]
		});
	}
}

// ==========================================
// 4. FACTURACIÓN Y COBRANZAS
// ==========================================
function renderInvoicesAnalytics(data) {
	const k = data.kpis || {};
	const c = data.charts || {};

	setSafeText('kpi-inv-net-sales-val', k.total_invoiced?.formatted);
	setSafeText('kpi-inv-net-sales', k.total_invoiced?.formatted);
	setSafeText('kpi-inv-count-val', k.invoices_issued?.formatted);
	setSafeText('kpi-inv-collected-val', k.total_collected?.formatted);
	setSafeText('kpi-inv-collected', k.total_collected?.formatted);
	setSafeText('kpi-inv-receivables-val', k.outstanding_balance?.formatted);
	setSafeText('kpi-inv-receivables', k.outstanding_balance?.formatted);
	setSafeText('kpi-inv-efficiency-val', k.collection_efficiency_pct?.formatted);
	setSafeText('kpi-inv-overdue-val', k.overdue_amount?.formatted);
	setSafeText('kpi-inv-overdue', k.overdue_amount?.formatted);
	setSafeText('kpi-inv-overdue-pct-val', k.overdue_invoices_pct?.formatted);
	setSafeText('kpi-inv-dso-val', k.dso_days?.formatted);

	// Secundarios
	setSafeWidth('inv-sec-eff-bar', k.collection_efficiency_pct?.value);
	setSafeWidth('inv-sec-mora-bar', k.overdue_invoices_pct?.value);
	setSafeText('inv-sec-dso-badge', k.dso_days?.formatted);
	setSafeText('inv-sec-coverage-badge', k.outstanding_balance?.formatted);

	if (c.billed_vs_collected) {
		renderOrCreateChart('chart-inv-billed-vs-collected', 'bar', {
			labels: c.billed_vs_collected.labels || [],
			datasets: [
				{
					label: 'Facturado ($)',
					data: c.billed_vs_collected.billed || [],
					backgroundColor: '#3b82f6'
				},
				{
					label: 'Cobrado ($)',
					data: c.billed_vs_collected.collected || [],
					backgroundColor: '#10b981'
				}
			]
		});
	}

	if (c.revenue_evolution) {
		renderOrCreateChart('chart-inv-revenue-evolution', 'line', {
			labels: c.revenue_evolution.labels || [],
			datasets: [{
				label: 'Evolución Ingresos ($)',
				data: c.revenue_evolution.values || [],
				borderColor: '#6366f1',
				backgroundColor: 'rgba(99, 102, 241, 0.1)',
				fill: true
			}]
		});
	}

	if (c.aging_distribution) {
		renderOrCreateChart('chart-inv-aging', 'bar', {
			labels: c.aging_distribution.labels || [],
			datasets: [{
				label: 'Monto en Cartera ($)',
				data: c.aging_distribution.values || [],
				backgroundColor: ['#10b981', '#f59e0b', '#f97316', '#f43f5e']
			}]
		});
	}

	if (c.invoice_status_pie) {
		renderOrCreateChart('chart-inv-status-pie', 'doughnut', {
			labels: c.invoice_status_pie.labels || [],
			datasets: [{
				data: c.invoice_status_pie.values || [],
				backgroundColor: ['#10b981', '#f59e0b', '#3b82f6', '#f43f5e']
			}]
		});
	}
}

// ==========================================
// 5. VENDEDORES Y TERRITORIOS (CERO NOMBRES INDIVIDUALES)
// ==========================================
function renderSalesRepsAnalytics(data) {
	const k = data.kpis || {};
	const c = data.charts || {};

	setSafeText('kpi-reps-active-count-exec', k.active_sales_reps?.formatted);
	setSafeText('kpi-reps-active-count', k.active_sales_reps?.formatted);
	setSafeText('kpi-reps-team-sales-exec', k.total_sales?.formatted);
	setSafeText('kpi-reps-team-sales', k.total_sales?.formatted);
	setSafeText('kpi-reps-avg-sales-exec', k.avg_sales_per_rep?.formatted);
	setSafeText('kpi-reps-hourly-sales-exec', k.sales_per_hour_worked?.formatted);
	setSafeText('kpi-reps-achievement-val-exec', k.quota_achievement_pct?.formatted);
	setSafeText('kpi-reps-achievement-val', k.quota_achievement_pct?.formatted);
	setSafeText('kpi-reps-profit-exec', k.gross_profit_generated?.formatted);
	setSafeText('kpi-reps-conversion-exec', k.sales_conversion_pct?.formatted);
	setSafeText('kpi-reps-growth-exec', k.sales_growth_pct?.formatted);

	// Secundarios
	setSafeWidth('reps-sec-quota-bar', k.quota_achievement_pct?.value);
	setSafeWidth('reps-sec-active-bar', k.sales_conversion_pct?.value);
	setSafeText('reps-sec-hourly-badge', k.sales_per_hour_worked?.formatted);
	setSafeText('reps-sec-margin-badge', k.gross_profit_generated?.formatted);

	if (c.productivity_trend) {
		renderOrCreateChart('chart-reps-productivity-trend', 'line', {
			labels: c.productivity_trend.labels || [],
			datasets: [{
				label: 'Ventas por Hora ($/h)',
				data: c.productivity_trend.values || [],
				borderColor: '#10b981',
				backgroundColor: 'rgba(16, 185, 129, 0.1)',
				fill: true
			}]
		});
	}

	if (c.sales_vs_hours) {
		renderOrCreateChart('chart-reps-sales-vs-hours', 'bar', {
			labels: c.sales_vs_hours.labels || [],
			datasets: [
				{
					label: 'Ventas Totales ($)',
					data: c.sales_vs_hours.sales || [],
					backgroundColor: '#3b82f6',
					yAxisID: 'y-sales'
				},
				{
					label: 'Horas Dedicadas (h)',
					type: 'line',
					data: c.sales_vs_hours.hours || [],
					borderColor: '#f59e0b',
					fill: false,
					yAxisID: 'y-hours'
				}
			]
		}, {
			scales: {
				xAxes: [{ gridLines: { display: false } }],
				yAxes: [
					{ id: 'y-sales', position: 'left', ticks: { beginAtZero: true } },
					{ id: 'y-hours', position: 'right', gridLines: { display: false }, ticks: { beginAtZero: true } }
				]
			}
		});
	}

	if (c.territory_distribution) {
		renderOrCreateChart('chart-reps-territory-dist', 'doughnut', {
			labels: c.territory_distribution.labels || [],
			datasets: [{
				data: c.territory_distribution.values || [],
				backgroundColor: ['#6366f1', '#10b981', '#3b82f6', '#f59e0b']
			}]
		});
	}

	if (c.quota_tiers) {
		renderOrCreateChart('chart-reps-quota-tiers', 'bar', {
			labels: c.quota_tiers.labels || [],
			datasets: [{
				label: '% del Equipo',
				data: c.quota_tiers.percentage || [],
				backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#f43f5e']
			}]
		});
	}
}

// ==========================================
// 6. RENTABILIDAD COMERCIAL (CERO NOMBRES INDIVIDUALES)
// ==========================================
function renderProfitabilityAnalytics(data) {
	const k = data.kpis || {};
	const c = data.charts || {};

	setSafeText('kpi-prof-net-sales', k.net_sales?.formatted);
	setSafeText('kpi-prof-cogs', k.cost_of_goods_sold?.formatted);
	setSafeText('kpi-prof-gross-profit', k.gross_profit?.formatted);
	setSafeText('kpi-prof-margin-val', k.gross_margin_pct?.formatted);
	setSafeText('kpi-prof-discounts-val', k.commercial_expenses?.formatted);
	setSafeText('kpi-prof-contribution-val', k.commercial_contribution?.formatted);
	setSafeText('kpi-prof-contribution-pct', k.contribution_margin_pct?.formatted);
	setSafeText('kpi-prof-growth-pct', k.profitability_growth_pct?.formatted);

	// Secundarios
	setSafeWidth('prof-sec-gross-bar', k.gross_margin_pct?.value);
	setSafeWidth('prof-sec-contrib-bar', k.contribution_margin_pct?.value);
	setSafeText('prof-sec-cogs-ratio', k.cost_of_goods_sold?.formatted);
	setSafeText('prof-sec-margin-spread', k.profitability_growth_pct?.formatted);

	if (c.sales_costs_margin) {
		renderOrCreateChart('chart-prof-trend', 'bar', {
			labels: c.sales_costs_margin.labels || [],
			datasets: [
				{
					label: 'Ventas Netas ($)',
					data: c.sales_costs_margin.sales || [],
					backgroundColor: '#3b82f6'
				},
				{
					label: 'COGS ($)',
					data: c.sales_costs_margin.cogs || [],
					backgroundColor: '#64748b'
				},
				{
					label: 'Utilidad Bruta ($)',
					data: c.sales_costs_margin.profit || [],
					backgroundColor: '#10b981'
				}
			]
		});
	}

	if (c.margin_by_category) {
		renderOrCreateChart('chart-prof-categories', 'horizontalBar', {
			labels: c.margin_by_category.labels || [],
			datasets: [{
				label: 'Margen Promedio (%)',
				data: c.margin_by_category.margins || [],
				backgroundColor: '#10b981'
			}]
		});
	}

	if (c.segment_profitability) {
		renderOrCreateChart('chart-prof-segment-prof', 'doughnut', {
			labels: c.segment_profitability.labels || [],
			datasets: [{
				data: c.segment_profitability.values || [],
				backgroundColor: ['#6366f1', '#10b981', '#3b82f6', '#f59e0b']
			}]
		});
	}

	if (c.margin_evolution) {
		renderOrCreateChart('chart-prof-health', 'line', {
			labels: c.margin_evolution.labels || [],
			datasets: [{
				label: 'Margen Bruto (%)',
				data: c.margin_evolution.values || [],
				borderColor: '#10b981',
				backgroundColor: 'rgba(16, 185, 129, 0.1)',
				fill: true
			}]
		});
	}
}

// ==========================================
// 7. INTELIGENCIA COMERCIAL (REPORTES)
// ==========================================
function renderReportsAnalytics(data) {
	const k = data.kpis || {};
	const c = data.charts || {};

	setSafeText('kpi-intel-sales-exec', k.consolidated_b2b_sales?.formatted);
	setSafeText('kpi-intel-growth-exec', k.sales_growth_pct?.formatted);
	setSafeText('kpi-intel-margin-exec', k.total_gross_margin_pct?.formatted);
	setSafeText('kpi-intel-customers-exec', k.active_customers_count?.formatted);
	setSafeText('kpi-intel-quotes-conv-exec', k.quote_conversion_pct?.formatted);
	setSafeText('kpi-intel-fulfillment-exec', k.order_fulfillment_pct?.formatted);
	setSafeText('kpi-intel-collection-exec', k.collection_efficiency_pct?.formatted);
	setSafeText('kpi-intel-prod-hourly-exec', k.commercial_productivity_hourly?.formatted);

	// Secundarios
	setSafeWidth('intel-sec-margin-bar', k.total_gross_margin_pct?.value);
	setSafeWidth('intel-sec-coll-bar', k.collection_efficiency_pct?.value);
	setSafeText('intel-sec-conv-badge', k.quote_conversion_pct?.formatted);
	setSafeText('intel-sec-otif-badge', k.order_fulfillment_pct?.formatted);

	if (c.consolidated_sales_trend) {
		renderOrCreateChart('chart-intel-sales-trend', 'line', {
			labels: c.consolidated_sales_trend.labels || [],
			datasets: [
				{
					label: 'Ventas Reales ($)',
					data: c.consolidated_sales_trend.actual || [],
					borderColor: '#3b82f6',
					backgroundColor: 'rgba(59, 130, 246, 0.1)',
					fill: true
				},
				{
					label: 'Meta Presupuestada ($)',
					data: c.consolidated_sales_trend.target || [],
					borderColor: '#94a3b8',
					borderDash: [5, 5],
					fill: false
				}
			]
		});
	}

	if (c.sales_vs_profitability) {
		renderOrCreateChart('chart-intel-sales-vs-prof', 'bar', {
			labels: c.sales_vs_profitability.labels || [],
			datasets: [
				{
					label: 'Ventas ($)',
					data: c.sales_vs_profitability.sales || [],
					backgroundColor: '#3b82f6',
					yAxisID: 'y-s'
				},
				{
					label: 'Margen (%)',
					type: 'line',
					data: c.sales_vs_profitability.margin_pct || [],
					borderColor: '#10b981',
					fill: false,
					yAxisID: 'y-m'
				}
			]
		}, {
			scales: {
				xAxes: [{ gridLines: { display: false } }],
				yAxes: [
					{ id: 'y-s', position: 'left', ticks: { beginAtZero: true } },
					{ id: 'y-m', position: 'right', gridLines: { display: false }, ticks: { beginAtZero: true } }
				]
			}
		});
	}

	if (c.commercial_funnel) {
		renderOrCreateChart('chart-intel-funnel', 'bar', {
			labels: c.commercial_funnel.labels || [],
			datasets: [{
				label: 'Monto en Funnel ($)',
				data: c.commercial_funnel.values || [],
				backgroundColor: ['#6366f1', '#3b82f6', '#10b981', '#059669']
			}]
		});
	}

	if (c.objective_radar) {
		renderOrCreateChart('chart-intel-radar', 'radar', {
			labels: c.objective_radar.labels || [],
			datasets: [{
				label: 'Cumplimiento Integral (%)',
				data: c.objective_radar.values || [],
				backgroundColor: 'rgba(99, 102, 241, 0.2)',
				borderColor: '#6366f1',
				pointBackgroundColor: '#6366f1'
			}]
		});
	}
}

// ==========================================
// 8. HORARIOS Y COBERTURA (WFM)
// ==========================================
function renderSchedulesAnalytics(data) {
	const k = data.kpis || {};
	const c = data.charts || {};

	setSafeText('kpi-wfm-hours-required', k.hours_required?.formatted);
	setSafeText('kpi-wfm-hours-scheduled', k.hours_scheduled?.formatted);
	setSafeText('kpi-wfm-coverage-score', k.coverage_score_pct?.formatted);
	setSafeText('kpi-wfm-labor-cost', k.estimated_labor_cost?.formatted);
	setSafeText('kpi-wfm-labor-prod', k.labor_productivity?.formatted);
	setSafeText('kpi-wfm-deficit', k.hours_deficit?.formatted);
	setSafeText('kpi-wfm-overtime', k.projected_overtime?.formatted);
	setSafeText('kpi-wfm-budget-used', k.budget_used_pct?.formatted);

	if (c.demand_vs_staffing) {
		renderOrCreateChart('wfmSalesVsHoursChart', 'line', {
			labels: c.demand_vs_staffing.labels || [],
			datasets: [
				{
					label: 'Horas Requeridas',
					data: c.demand_vs_staffing.required_hours || [],
					borderColor: '#3b82f6',
					fill: false
				},
				{
					label: 'Horas Programadas',
					data: c.demand_vs_staffing.scheduled_hours || [],
					borderColor: '#10b981',
					fill: false
				}
			]
		});
	}

	if (c.hourly_coverage) {
		renderOrCreateChart('chart-sch-hourly-coverage', 'bar', {
			labels: c.hourly_coverage.labels || [],
			datasets: [{
				label: 'Cobertura (%)',
				data: c.hourly_coverage.coverage_pct || [],
				backgroundColor: '#10b981'
			}]
		});
	}

	if (c.cost_by_function) {
		renderOrCreateChart('chart-sch-cost-function', 'doughnut', {
			labels: c.cost_by_function.labels || [],
			datasets: [{
				data: c.cost_by_function.values || [],
				backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6']
			}]
		});
	}
}

// ==========================================
// 9. OPERACIONES Y TAREAS
// ==========================================
function renderOperationsAnalytics(data) {
	const k = data.kpis || {};
	const c = data.charts || {};

	setSafeText('kpi-ops-pending', k.pending_tasks?.formatted);
	setSafeText('kpi-ops-completed', k.completed_tasks?.formatted);
	setSafeText('kpi-ops-overdue', k.overdue_tasks?.formatted);
	setSafeText('kpi-ops-compliance', k.operational_compliance_pct?.formatted);
	setSafeText('kpi-ops-prod', k.restock_productivity?.formatted);
	setSafeText('kpi-ops-alerts', k.restock_alerts?.formatted);
	setSafeText('kpi-ops-incidents', k.operational_incidents?.formatted);
	setSafeText('kpi-ops-fefo', k.fefo_compliance_pct?.formatted);

	// Secundarios
	setSafeWidth('ops-sec-compliance-bar', k.operational_compliance_pct?.value);
	setSafeWidth('ops-sec-fefo-bar', k.fefo_compliance_pct?.value);
	setSafeText('ops-sec-overdue-rate', k.overdue_tasks?.formatted);
	setSafeText('ops-sec-incident-rate', k.operational_incidents?.formatted);

	if (c.tasks_by_status) {
		renderOrCreateChart('chart-ops-compliance-shift', 'doughnut', {
			labels: c.tasks_by_status.labels || [],
			datasets: [{
				data: c.tasks_by_status.values || [],
				backgroundColor: ['#10b981', '#f59e0b', '#f43f5e']
			}]
		});
	}

	if (c.tasks_by_department) {
		renderOrCreateChart('chart-ops-status-dist', 'bar', {
			labels: c.tasks_by_department.labels || [],
			datasets: [
				{
					label: 'Completadas',
					data: c.tasks_by_department.completed || [],
					backgroundColor: '#10b981'
				},
				{
					label: 'Pendientes',
					data: c.tasks_by_department.pending || [],
					backgroundColor: '#f59e0b'
				}
			]
		});
	}

	if (c.incidents_evolution) {
		renderOrCreateChart('chart-ops-incidents-trend', 'line', {
			labels: c.incidents_evolution.labels || [],
			datasets: [
				{
					label: 'Incidencias Operativas',
					data: c.incidents_evolution.incidents || [],
					borderColor: '#f43f5e',
					fill: false
				},
				{
					label: 'Cumplimiento FEFO (%)',
					data: c.incidents_evolution.fefo_compliance || [],
					borderColor: '#10b981',
					fill: false
				}
			]
		});
	}

	if (c.tasks_by_status) {
		renderOrCreateChart('chart-ops-fefo-trend', 'bar', {
			labels: c.tasks_by_status.labels || [],
			datasets: [{
				label: 'Volumen',
				data: c.tasks_by_status.values || [],
				backgroundColor: '#3b82f6'
			}]
		});
	}
}

// ==========================================
// 10. PERSONAL Y DOTACIÓN
// ==========================================
function renderEmployeesAnalytics(data) {
	const k = data.kpis || {};
	const c = data.charts || {};

	setSafeText('kpi-emp-active', k.active_employees?.formatted);
	setSafeText('kpi-emp-absenteeism', k.absenteeism_rate_pct?.formatted);
	setSafeText('kpi-emp-turnover', k.turnover_rate_pct?.formatted);
	setSafeText('kpi-emp-hours', k.contracted_hours_total?.formatted);
	setSafeText('kpi-emp-hourly-cost', k.avg_hourly_cost?.formatted);
	setSafeText('kpi-emp-prod', k.productivity_per_collab?.formatted);
	setSafeText('kpi-emp-key-roles', k.key_roles_coverage_pct?.formatted);
	setSafeText('kpi-emp-vacancies', k.open_vacancies?.formatted);

	// Secundarios
	setSafeWidth('emp-sec-coverage-bar', k.key_roles_coverage_pct?.value);
	setSafeWidth('emp-sec-absenteeism-bar', k.absenteeism_rate_pct?.value);
	setSafeText('emp-sec-turnover-badge', k.turnover_rate_pct?.formatted);
	setSafeText('emp-sec-hourly-badge', k.avg_hourly_cost?.formatted);

	if (c.staff_by_role) {
		renderOrCreateChart('chart-emp-role-dist', 'doughnut', {
			labels: c.staff_by_role.labels || [],
			datasets: [{
				data: c.staff_by_role.counts || [],
				backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6']
			}]
		});
	}

	if (c.payroll_by_location) {
		renderOrCreateChart('chart-emp-payroll-loc', 'bar', {
			labels: c.payroll_by_location.labels || [],
			datasets: [{
				label: 'Costo Laboral ($)',
				data: c.payroll_by_location.costs || [],
				backgroundColor: '#6366f1'
			}]
		});
	}

	if (c.contract_compliance) {
		renderOrCreateChart('chart-emp-contract-types', 'bar', {
			labels: c.contract_compliance.labels || [],
			datasets: [{
				label: '% del Personal',
				data: c.contract_compliance.percentages || [],
				backgroundColor: '#10b981'
			}]
		});

		renderOrCreateChart('chart-emp-availability', 'line', {
			labels: c.contract_compliance.labels || [],
			datasets: [{
				label: 'Disponibilidad Programada (%)',
				data: c.contract_compliance.percentages || [],
				borderColor: '#3b82f6',
				fill: true,
				backgroundColor: 'rgba(59, 130, 246, 0.1)'
			}]
		});
	}
}

// ==========================================
// 11. ASISTENCIA Y PUNTUALIDAD
// ==========================================
function renderAttendanceAnalytics(data) {
	const k = data.kpis || {};
	const c = data.charts || {};

	setSafeText('kpi-att-rate', k.attendance_rate_pct?.formatted);
	setSafeText('kpi-att-punctuality', k.punctuality_rate_pct?.formatted);
	setSafeText('kpi-att-hours-worked', k.hours_worked_actual?.formatted);
	setSafeText('kpi-att-hours-target', k.hours_scheduled_target?.formatted);
	setSafeText('kpi-att-hours-prod', k.hours_productive?.formatted);
	setSafeText('kpi-att-absences', k.unexcused_absences?.formatted);
	setSafeText('kpi-att-tardiness', k.tardiness_count?.formatted);
	setSafeText('kpi-att-overtime', k.overtime_hours_actual?.formatted);

	// Secundarios
	setSafeWidth('att-sec-rate-bar', k.attendance_rate_pct?.value);
	setSafeWidth('att-sec-punctuality-bar', k.punctuality_rate_pct?.value);
	setSafeText('att-sec-overtime-badge', k.overtime_hours_actual?.formatted);
	setSafeText('att-sec-tardiness-badge', k.tardiness_count?.formatted);

	if (c.attendance_trend) {
		renderOrCreateChart('chart-att-trend', 'line', {
			labels: c.attendance_trend.labels || [],
			datasets: [
				{
					label: 'Asistencia (%)',
					data: c.attendance_trend.attendance_pct || [],
					borderColor: '#10b981',
					fill: false
				},
				{
					label: 'Puntualidad (%)',
					data: c.attendance_trend.punctuality_pct || [],
					borderColor: '#3b82f6',
					fill: false
				}
			]
		});

		renderOrCreateChart('chart-att-weekday-compliance', 'bar', {
			labels: c.attendance_trend.labels || [],
			datasets: [{
				label: 'Cumplimiento Diario (%)',
				data: c.attendance_trend.attendance_pct || [],
				backgroundColor: '#6366f1'
			}]
		});
	}

	if (c.hours_comparison) {
		renderOrCreateChart('chart-att-hours-compare', 'bar', {
			labels: c.hours_comparison.labels || [],
			datasets: [{
				label: 'Horas Totales (h)',
				data: c.hours_comparison.values || [],
				backgroundColor: ['#3b82f6', '#10b981', '#8b5cf6']
			}]
		});
	}

	if (c.incidents_by_location) {
		renderOrCreateChart('chart-att-incidents-loc', 'bar', {
			labels: c.incidents_by_location.labels || [],
			datasets: [
				{
					label: 'Tardanzas',
					data: c.incidents_by_location.tardiness || [],
					backgroundColor: '#f59e0b'
				},
				{
					label: 'Ausencias Injustificadas',
					data: c.incidents_by_location.absences || [],
					backgroundColor: '#f43f5e'
				}
			]
		});
	}
}

// ==========================================
// 12. SOLICITUDES Y PERMISOS
// ==========================================
function renderShiftRequestsAnalytics(data) {
	const k = data.kpis || {};
	const c = data.charts || {};

	setSafeText('kpi-req-received', k.requests_received?.formatted);
	setSafeText('kpi-req-pending', k.requests_pending?.formatted);
	setSafeText('kpi-req-approved', k.requests_approved?.formatted);
	setSafeText('kpi-req-rejected', k.requests_rejected?.formatted);
	setSafeText('kpi-req-resolution', k.avg_resolution_hours?.formatted);
	setSafeText('kpi-req-active-leaves', k.active_leaves_week?.formatted);
	setSafeText('kpi-req-swaps', k.shift_swaps_requested?.formatted);
	setSafeText('kpi-req-expired', k.expired_requests?.formatted);

	// Secundarios
	setSafeWidth('req-sec-approval-bar', k.requests_approved?.value);
	setSafeWidth('req-sec-pending-bar', k.requests_pending?.value);
	setSafeText('req-sec-resolution-badge', k.avg_resolution_hours?.formatted);
	setSafeText('req-sec-expired-badge', k.expired_requests?.formatted);

	if (c.requests_by_type) {
		renderOrCreateChart('chart-req-type-dist', 'doughnut', {
			labels: c.requests_by_type.labels || [],
			datasets: [{
				data: c.requests_by_type.counts || [],
				backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6']
			}]
		});

		renderOrCreateChart('chart-req-reasons', 'bar', {
			labels: c.requests_by_type.labels || [],
			datasets: [{
				label: 'Solicitudes',
				data: c.requests_by_type.counts || [],
				backgroundColor: '#8b5cf6'
			}]
		});
	}

	if (c.resolution_rate) {
		renderOrCreateChart('chart-req-resolution', 'bar', {
			labels: c.resolution_rate.labels || [],
			datasets: [{
				label: 'Resolución',
				data: c.resolution_rate.counts || [],
				backgroundColor: ['#10b981', '#f59e0b', '#f43f5e']
			}]
		});
	}

	if (c.requests_evolution) {
		renderOrCreateChart('chart-req-evolution', 'line', {
			labels: c.requests_evolution.labels || [],
			datasets: [
				{
					label: 'Recibidas',
					data: c.requests_evolution.received || [],
					borderColor: '#3b82f6',
					fill: false
				},
				{
					label: 'Dictaminadas',
					data: c.requests_evolution.resolved || [],
					borderColor: '#10b981',
					fill: false
				}
			]
		});
	}
}

// Inicialización automática cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
	if (typeof currentSection !== 'undefined' && currentSection !== 'sales' && currentSection !== 'settings') {
		loadModuleAnalytics(currentSection);
	}
});
</script>
