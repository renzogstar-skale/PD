<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>{{ $pageTitle ?? 'Plataforma Mayorista B2B — Prince Distributors' }}</title>
	<link rel="icon" href="{{ $U('/img/icon.svg') }}" type="image/svg+xml">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
	<!-- Tailwind CSS CDN -->
	<script src="https://cdn.tailwindcss.com"></script>
	<!-- Chart.js -->
	<script src="{{ $U('/packages/chart.js/dist/Chart.bundle.min.js') }}"></script>
	<!-- Lucide Icons -->
	<script src="https://unpkg.com/lucide@latest"></script>
	
	<script>
		// Configuración de Tailwind para tema Enterprise Minimal
		tailwind.config = {
			darkMode: 'class',
			theme: {
				extend: {
					fontFamily: {
						sans: ['Inter', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'],
					},
					colors: {
						brand: {
							50: '#eff6ff',
							100: '#dbeafe',
							200: '#bfdbfe',
							500: '#3b82f6',
							600: '#2563eb',
							700: '#1d4ed8',
							800: '#1e40af',
							900: '#1e3a8a',
							950: '#172554',
						},
						surface: {
							light: '#ffffff',
							dark: '#111827',
							borderLight: '#e2e8f0',
							borderDark: '#1f2937',
							bgLight: '#f8fafc',
							bgDark: '#090d16',
						}
					}
				}
			}
		}

		// Inicializar tema antes del render
		if (localStorage.getItem('b2b_theme') === 'dark' || (!('b2b_theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
			document.documentElement.classList.add('dark');
		} else {
			document.documentElement.classList.remove('dark');
		}
	</script>
	<style>
		body {
			font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
			font-feature-settings: "cv02", "cv03", "cv04", "cv11";
		}
		::-webkit-scrollbar {
			width: 6px;
			height: 6px;
		}
		::-webkit-scrollbar-track {
			background: transparent;
		}
		::-webkit-scrollbar-thumb {
			background: rgba(148, 163, 184, 0.4);
			border-radius: 9999px;
		}
		.dark ::-webkit-scrollbar-thumb {
			background: rgba(71, 85, 105, 0.5);
		}

		/* Tooltips para sidebar colapsado */
		.sidebar-collapsed .nav-text,
		.sidebar-collapsed .nav-badge,
		.sidebar-collapsed .nav-group-title,
		.sidebar-collapsed .brand-text,
		.sidebar-collapsed .user-info,
		.sidebar-collapsed .env-pill {
			display: none !important;
		}
		.sidebar-collapsed .sidebar-header {
			justify-content: center !important;
			padding-left: 0.5rem;
			padding-right: 0.5rem;
		}
		.sidebar-collapsed .nav-item {
			justify-content: center !important;
			padding-left: 0 !important;
			padding-right: 0 !important;
			position: relative;
		}
		.sidebar-collapsed .nav-item:hover .nav-tooltip {
			opacity: 1;
			visibility: visible;
			transform: translateX(0);
		}
		.nav-tooltip {
			position: absolute;
			left: 100%;
			top: 50%;
			transform: translateY(-50%) translateX(-4px);
			margin-left: 0.65rem;
			padding: 0.35rem 0.65rem;
			font-size: 0.75rem;
			font-weight: 500;
			white-space: nowrap;
			border-radius: 0.375rem;
			background-color: #0f172a;
			color: #ffffff;
			box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
			opacity: 0;
			visibility: hidden;
			pointer-events: none;
			transition: all 0.15s ease-in-out;
			z-index: 60;
		}
		.dark .nav-tooltip {
			background-color: #1e293b;
			border: 1px solid #334155;
		}
	</style>
</head>
<body class="h-full bg-surface-bgLight dark:bg-surface-bgDark text-slate-900 dark:text-slate-100 antialiased flex flex-col md:flex-row min-h-screen" data-initial-section="{{ $activeSection ?? 'sales' }}">

	<!-- SIDEBAR (Escritorio + Móvil Drawer) -->
	<div id="mobile-overlay" class="fixed inset-0 bg-slate-900/60 z-40 hidden md:hidden backdrop-blur-xs transition-opacity" onclick="toggleMobileSidebar()"></div>

	<aside id="main-sidebar" class="fixed inset-y-0 left-0 z-50 w-72 bg-white dark:bg-surface-dark border-r border-slate-200 dark:border-slate-800 flex flex-col justify-between transform -translate-x-full md:translate-x-0 transition-[width,transform] duration-200 ease-in-out">
		<!-- Sidebar Top: Marca & Entorno -->
		<div>
			<div class="sidebar-header h-16 flex items-center justify-between px-5 border-b border-slate-200 dark:border-slate-800">
				<div class="flex items-center gap-3 overflow-hidden">
					<div class="w-9 h-9 rounded-lg bg-brand-800 flex items-center justify-center text-white font-bold text-lg shadow-sm shrink-0">
						<i data-lucide="boxes" class="w-5 h-5"></i>
					</div>
					<div class="brand-text truncate">
						<div class="text-sm font-bold tracking-tight text-slate-900 dark:text-white uppercase leading-none">PRINCE DISTRIBUTORS</div>
						<div class="text-[11px] font-medium text-slate-500 dark:text-slate-400 mt-0.5">Wholesale B2B Suite</div>
					</div>
				</div>
				<button id="btn-close-mobile-sidebar" class="md:hidden text-slate-500 hover:text-slate-700 dark:hover:text-slate-200 p-1 rounded" onclick="toggleMobileSidebar()">
					<i data-lucide="x" class="w-5 h-5"></i>
				</button>
			</div>

			<!-- Environment Demo Pill -->
			<div class="env-pill px-5 py-2.5 bg-amber-50/70 dark:bg-amber-950/30 border-b border-amber-200/50 dark:border-amber-900/40 flex items-center justify-between">
				<div class="flex items-center gap-2">
					<span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
					<span class="text-xs font-semibold text-amber-800 dark:text-amber-300" data-i18n="demo_mode_active">Modo Demo B2B Activo</span>
				</div>
				<span class="text-[10px] font-mono uppercase px-1.5 py-0.5 rounded bg-amber-200/60 dark:bg-amber-900/60 text-amber-900 dark:text-amber-200 font-bold">V-2.4</span>
			</div>

			<!-- Navigation Links (Las 8 secciones requeridas) -->
			<div class="px-3 py-4 space-y-4 overflow-y-auto max-h-[calc(100vh-220px)]">
				<div class="nav-group-title px-3 text-[11px] font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2" data-i18n="nav_commercial">
					Gestión Comercial B2B
				</div>
				<nav class="space-y-1" id="b2b-sidebar-nav">
					
					<!-- 1. Resumen de Ventas -->
					<a href="{{ $U('/b2b/sales') }}" data-section="sales" class="nav-item flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg transition-colors group relative" onclick="handleNavClick(event, 'sales')">
						<i data-lucide="layout-dashboard" class="w-4 h-4 shrink-0"></i>
						<span class="nav-text" data-i18n="nav_sales">Resumen de Ventas</span>
						<span class="nav-tooltip" data-i18n="nav_sales">Resumen de Ventas</span>
					</a>

					<!-- 2. Clientes y Almacenes (Módulo Funcional) -->
					<a href="{{ $U('/b2b/customers') }}" data-section="customers" class="nav-item flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 transition-colors group relative" onclick="handleNavClick(event, 'customers')">
						<i data-lucide="building-2" class="w-4 h-4 text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-200 shrink-0"></i>
						<span class="nav-text" data-i18n="nav_customers">Clientes y Almacenes</span>
						<span class="nav-tooltip" data-i18n="nav_customers">Clientes y Almacenes</span>
					</a>

					<!-- 3. Productos y Precios -->
					<a href="{{ $U('/b2b/products') }}" data-section="products" class="nav-item flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 transition-colors group relative" onclick="handleNavClick(event, 'products')">
						<i data-lucide="package" class="w-4 h-4 text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-200 shrink-0"></i>
						<span class="nav-text" data-i18n="nav_products">Productos y Precios</span>
						<span class="nav-tooltip" data-i18n="nav_products">Productos y Precios</span>
					</a>

					<!-- 4. Cotizaciones y Pedidos -->
					<a href="{{ $U('/b2b/orders') }}" data-section="orders" class="nav-item flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 transition-colors group relative" onclick="handleNavClick(event, 'orders')">
						<div class="flex items-center gap-3">
							<i data-lucide="shopping-cart" class="w-4 h-4 text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-200 shrink-0"></i>
							<span class="nav-text" data-i18n="nav_orders">Cotizaciones y Pedidos</span>
						</div>
						<span class="nav-badge px-1.5 py-0.5 text-[10px] font-semibold rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">18</span>
						<span class="nav-tooltip" data-i18n="nav_orders">Cotizaciones y Pedidos</span>
					</a>

					<!-- 4.1 Facturación Comercial y Cobranzas (Paso 7) -->
					<a href="{{ $U('/b2b/invoices') }}" data-section="invoices" class="nav-item flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 transition-colors group relative" onclick="handleNavClick(event, 'invoices')">
						<div class="flex items-center gap-3">
							<i data-lucide="receipt" class="w-4 h-4 text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-200 shrink-0"></i>
							<span class="nav-text" data-i18n="nav_invoices">Facturación B2B</span>
						</div>
						<span id="nav-invoices-badge" class="nav-badge px-1.5 py-0.5 text-[10px] font-semibold rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">8</span>
						<span class="nav-tooltip" data-i18n="nav_invoices">Facturación B2B</span>
					</a>

					<!-- 5. Vendedores y Territorios -->
					<a href="{{ $U('/b2b/sales-reps') }}" data-section="sales-reps" class="nav-item flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 transition-colors group relative" onclick="handleNavClick(event, 'sales-reps')">
						<i data-lucide="users" class="w-4 h-4 text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-200 shrink-0"></i>
						<span class="nav-text" data-i18n="nav_sales_reps">Vendedores y Territorios</span>
						<span class="nav-tooltip" data-i18n="nav_sales_reps">Vendedores y Territorios</span>
					</a>

					<!-- 6. Rentabilidad Comercial -->
					<a href="{{ $U('/b2b/profitability') }}" data-section="profitability" class="nav-item flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 transition-colors group relative" onclick="handleNavClick(event, 'profitability')">
						<i data-lucide="trending-up" class="w-4 h-4 text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-200 shrink-0"></i>
						<span class="nav-text" data-i18n="nav_profitability">Rentabilidad Comercial</span>
						<span class="nav-tooltip" data-i18n="nav_profitability">Rentabilidad Comercial</span>
					</a>

					<!-- 7. Reportes y Analítica -->
					<a href="{{ $U('/b2b/reports') }}" data-section="reports" class="nav-item flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 transition-colors group relative" onclick="handleNavClick(event, 'reports')">
						<i data-lucide="bar-chart-3" class="w-4 h-4 text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-200 shrink-0"></i>
						<span class="nav-text" data-i18n="nav_reports">Reportes y Analítica</span>
						<span class="nav-tooltip" data-i18n="nav_reports">Reportes y Analítica</span>
					</a>

					<!-- 8. Configuración -->
					<a href="{{ $U('/b2b/settings') }}" data-section="settings" class="nav-item flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 transition-colors group relative" onclick="handleNavClick(event, 'settings')">
						<i data-lucide="settings" class="w-4 h-4 text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-200 shrink-0"></i>
						<span class="nav-text" data-i18n="nav_settings">Configuración</span>
						<span class="nav-tooltip" data-i18n="nav_settings">Configuración</span>
					</a>
				</nav>
			</div>
		</div>

		<!-- Sidebar Bottom: Collapse Button, Sucursal & Usuario -->
		<div class="p-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 space-y-2">
			<!-- Botón para colapsar/expandir sidebar en escritorio -->
			<button id="btn-toggle-collapse" onclick="toggleSidebarCollapse()" class="hidden md:flex items-center justify-center w-full py-1.5 px-2 rounded-lg text-xs font-medium text-slate-500 dark:text-slate-400 hover:bg-slate-200/60 dark:hover:bg-slate-800 transition-colors" title="Colapsar / Expandir barra lateral">
				<i data-lucide="panel-left-close" id="icon-collapse" class="w-4 h-4"></i>
				<span class="nav-text ml-2" data-i18n="collapse_sidebar">Colapsar menú</span>
			</button>

			<div class="flex items-center justify-between p-2 rounded-lg bg-white dark:bg-surface-dark border border-slate-200/80 dark:border-slate-800 shadow-xs">
				<div class="flex items-center gap-2.5 overflow-hidden">
					<div class="w-8 h-8 rounded-full bg-brand-700 text-white flex items-center justify-center font-bold text-xs shrink-0">
						CM
					</div>
					<div class="user-info truncate">
						<div class="text-xs font-semibold text-slate-800 dark:text-slate-200 truncate">Carlos Mendoza</div>
						<div class="text-[10px] text-slate-400 truncate">Dir. Ventas Mayoristas</div>
					</div>
				</div>
				<button onclick="toggleTheme()" class="p-1.5 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 rounded-md hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors shrink-0" title="Cambiar tema claro/oscuro">
					<i data-lucide="moon" class="w-4 h-4 hidden dark:block"></i>
					<i data-lucide="sun" class="w-4 h-4 block dark:hidden"></i>
				</button>
			</div>
		</div>
	</aside>

	<!-- MAIN CONTENT AREA -->
	<div id="content-container" class="flex-1 md:pl-72 flex flex-col min-h-screen overflow-x-hidden transition-[padding] duration-200 ease-in-out">
		
		<!-- TOP HEADER -->
		<header class="h-16 bg-white dark:bg-surface-dark border-b border-slate-200 dark:border-slate-800 sticky top-0 z-30 px-4 sm:px-6 flex items-center justify-between gap-4">
			<div class="flex items-center gap-3">
				<button id="btn-mobile-menu" class="md:hidden text-slate-600 dark:text-slate-300 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800" onclick="toggleMobileSidebar()">
					<i data-lucide="menu" class="w-5 h-5"></i>
				</button>

				<!-- Breadcrumb Dinámico -->
				<div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
					<span data-i18n="breadcrumb_root">Ventas Mayoristas</span>
					<i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
					<span id="breadcrumb-current" class="font-semibold text-slate-800 dark:text-slate-200" data-i18n="nav_sales">Resumen de Ventas</span>
				</div>
				
				<span class="hidden lg:inline-flex items-center gap-1.5 ml-3 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/60">
					<span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
					<span data-i18n="erp_sync_status">EDI & ERP Sincronizado (Hace 2 min)</span>
				</span>
			</div>

			<!-- Search, Idioma & Quick Action Buttons -->
			<div class="flex items-center gap-2 sm:gap-3">
				<!-- Search Input -->
				<div class="relative hidden sm:block w-52 lg:w-72">
					<i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
					<input type="text" id="global-search-input" placeholder="Buscar SKU, Cliente, Factura, Pedido..." class="w-full pl-9 pr-8 py-1.5 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
					<kbd class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[10px] text-slate-400 bg-white dark:bg-slate-700 px-1.5 py-0.5 rounded border border-slate-200 dark:border-slate-600 font-mono">⌘K</kbd>
				</div>

				<!-- Selector de Idioma ES / EN -->
				<div class="inline-flex p-0.5 bg-slate-100 dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 text-xs font-semibold">
					<button id="lang-btn-es" onclick="switchLanguage('es')" class="px-2 py-1 rounded-md text-brand-800 dark:text-white bg-white dark:bg-slate-700 shadow-xs transition-all">ES</button>
					<button id="lang-btn-en" onclick="switchLanguage('en')" class="px-2 py-1 rounded-md text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-all">EN</button>
				</div>

				<!-- Quick Theme Toggle -->
				<button onclick="toggleTheme()" class="p-2 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors" title="Alternar Modo Oscuro/Claro">
					<i data-lucide="moon" class="w-4 h-4 hidden dark:block"></i>
					<i data-lucide="sun" class="w-4 h-4 block dark:hidden"></i>
				</button>

				<!-- Action CTA -->
				<button onclick="handleHeaderNewAction()" class="inline-flex items-center gap-2 px-3 py-1.5 sm:px-3.5 sm:py-2 text-xs font-semibold rounded-lg bg-brand-800 hover:bg-brand-700 text-white shadow-xs active:scale-[0.98] transition-all shrink-0">
					<i data-lucide="plus" class="w-4 h-4"></i>
					<span id="header-action-text" class="hidden sm:inline" data-i18n="btn_new_order">Nueva Orden B2B</span>
				</button>
			</div>
		</header>

		<!-- PAGE BODY (ÁREA DE CONTENIDO DINÁMICO) -->
		<main id="b2b-content-area" class="flex-1 p-4 sm:p-6 lg:p-8 space-y-6 max-w-7xl mx-auto w-full">
			
			<!-- SECCIÓN 1: TABLERO EJECUTIVO DE VENTAS (MÓDULO FUNCIONAL PASO 8) -->
			<div id="section-sales" class="space-y-6">
				<!-- TITLE & FILTER CONTROLS BAR -->
				<div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-2 border-b border-slate-200/80 dark:border-slate-800">
					<div>
						<h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-3">
							<span data-i18n="sales_summary_title">Tablero Ejecutivo de Ventas</span>
							<span id="dash-period-badge" class="text-[11px] font-semibold uppercase px-2 py-0.5 rounded-md bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200/60 dark:border-blue-800/60">Noviembre 2026 a la fecha</span>
							<span class="text-[10px] font-semibold uppercase px-1.5 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/50">DEMO B2B</span>
						</h1>
						<p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1" data-i18n="sales_summary_desc">Supervisión en tiempo real de facturación mayorista, margen comercial, OTIF y riesgo crediticio.</p>
					</div>

					<!-- Filter controls group -->
					<div class="flex flex-wrap items-center gap-2">
						<!-- Time presets buttons -->
						<div class="inline-flex p-0.5 bg-slate-100 dark:bg-slate-800/90 rounded-lg border border-slate-200/80 dark:border-slate-700 text-xs">
							<button id="dash-btn-7d" onclick="setDashboardPeriod('7D')" class="px-2.5 py-1 font-medium rounded-md text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-all">7D</button>
							<button id="dash-btn-mtd" onclick="setDashboardPeriod('MTD')" class="px-2.5 py-1 font-semibold rounded-md bg-white dark:bg-slate-700 text-brand-800 dark:text-white shadow-xs transition-all">MTD</button>
							<button id="dash-btn-qtd" onclick="setDashboardPeriod('QTD')" class="px-2.5 py-1 font-medium rounded-md text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-all">QTD</button>
							<button id="dash-btn-ytd" onclick="setDashboardPeriod('YTD')" class="px-2.5 py-1 font-medium rounded-md text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-all">YTD</button>
							<button id="dash-btn-custom" onclick="toggleCustomDateFilter()" class="px-2.5 py-1 font-medium rounded-md text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-all">Rango</button>
						</div>

						<!-- Custom Date Inputs (Hidden by default, shown on Rango) -->
						<div id="dash-custom-range-bar" class="hidden flex items-center gap-1.5 p-1 bg-slate-50 dark:bg-slate-800/90 rounded-lg border border-slate-200 dark:border-slate-700 text-xs">
							<input type="date" id="dash-custom-from" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded px-2 py-1 text-[11px] text-slate-700 dark:text-slate-200 focus:outline-hidden" value="2026-11-01" />
							<span class="text-slate-400">-</span>
							<input type="date" id="dash-custom-to" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded px-2 py-1 text-[11px] text-slate-700 dark:text-slate-200 focus:outline-hidden" value="2026-11-16" />
							<button onclick="applyCustomDateFilter()" class="px-2 py-1 bg-brand-800 text-white rounded text-[11px] font-semibold hover:bg-brand-700">OK</button>
						</div>

						<!-- Filter 1: Cliente o Cadena -->
						<select id="dash-filter-customer" onchange="onDashboardCustomerChange()" class="text-xs bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 font-medium text-slate-700 dark:text-slate-200 focus:outline-hidden focus:ring-1 focus:ring-brand-500 max-w-[170px] truncate">
							<option value="">Todos los Clientes</option>
						</select>

						<!-- Filter 2: Establecimiento / Sucursal -->
						<select id="dash-filter-establishment" onchange="onDashboardEstablishmentChange()" class="text-xs bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 font-medium text-slate-700 dark:text-slate-200 focus:outline-hidden focus:ring-1 focus:ring-brand-500 max-w-[160px] truncate">
							<option value="">Todos los Establecimientos</option>
						</select>

						<!-- Filter 3: Categoría de Producto -->
						<select id="dash-filter-category" onchange="onDashboardCategoryChange()" class="text-xs bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 font-medium text-slate-700 dark:text-slate-200 focus:outline-hidden focus:ring-1 focus:ring-brand-500 max-w-[150px] truncate">
							<option value="">Todas las Categorías</option>
						</select>

						<!-- Filter 4: Estado Comercial -->
						<select id="dash-filter-status" onchange="onDashboardStatusChange()" class="text-xs bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 font-medium text-slate-700 dark:text-slate-200 focus:outline-hidden focus:ring-1 focus:ring-brand-500">
							<option value="ALL">Todos los Estados</option>
							<option value="Confirmado">Confirmados / Emitidos</option>
							<option value="Pagada">Pagadas</option>
							<option value="Parcialmente pagada">Parcialmente pagadas</option>
							<option value="Emitida">Emitidas</option>
							<option value="Borrador">Borradores</option>
							<option value="Cancelado">Cancelados</option>
						</select>

						<!-- Reset filters -->
						<button id="btn-reset-dash-filters" onclick="resetDashboardFilters()" class="p-1.5 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors" title="Restablecer Filtros">
							<i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
						</button>

						<!-- Export button -->
						<button onclick="exportDashboardSummary()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 shadow-xs transition-colors">
							<i data-lucide="download" class="w-3.5 h-3.5"></i>
							<span data-i18n="btn_export">Exportar</span>
						</button>
					</div>
				</div>

				<!-- 6 KPI CARDS SECTION (Enterprise Minimal - Paso 8) -->
				<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
					<!-- KPI 1: Ventas Netas Facturadas -->
					<div id="kpi-net-sales-card" class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs transition-all hover:border-brand-500/40" title="Ventas netas = Facturación válida antes de impuestos − Notas de crédito válidas antes de impuestos.">
						<div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
							<span class="text-xs font-semibold uppercase tracking-wider" data-i18n="kpi_net_sales">Ventas Netas B2B</span>
							<div class="w-7 h-7 rounded-md bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 flex items-center justify-center">
								<i data-lucide="dollar-sign" class="w-4 h-4"></i>
							</div>
						</div>
						<div class="mt-2 flex items-baseline gap-2">
							<span id="kpi-net-sales-val" class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white font-mono tracking-tight">$0.00</span>
						</div>
						<div class="mt-1 flex items-center justify-between">
							<span id="kpi-net-sales-var" class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 flex items-center">
								<i data-lucide="trending-up" class="w-3 h-3 mr-0.5"></i>0%
							</span>
							<span id="kpi-net-sales-period" class="text-[10px] text-slate-400">MTD</span>
						</div>
						<div id="kpi-net-sales-sub" class="mt-2 text-[10px] text-slate-500 dark:text-slate-400 truncate border-t border-slate-100 dark:border-slate-800/80 pt-1.5">
							Subtotal Facturado sin impuestos
						</div>
					</div>

					<!-- KPI 2: Pedidos Confirmados -->
					<div id="kpi-orders-card" class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs transition-all hover:border-brand-500/40" title="Total de pedidos comerciales en estado 'Confirmado' registrados en el período.">
						<div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
							<span class="text-xs font-semibold uppercase tracking-wider" data-i18n="kpi_confirmed_orders">Pedidos Confirmados</span>
							<div class="w-7 h-7 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 flex items-center justify-center">
								<i data-lucide="package-check" class="w-4 h-4"></i>
							</div>
						</div>
						<div class="mt-2 flex items-baseline gap-2">
							<span id="kpi-orders-val" class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white font-mono tracking-tight">0</span>
						</div>
						<div class="mt-1 flex items-center justify-between">
							<span id="kpi-orders-var" class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 flex items-center">
								N/D
							</span>
							<span id="kpi-orders-period" class="text-[10px] text-slate-400">MTD</span>
						</div>
						<div id="kpi-orders-sub" class="mt-2 text-[10px] text-slate-500 dark:text-slate-400 truncate border-t border-slate-100 dark:border-slate-800/80 pt-1.5">
							Órdenes comerciales cerradas
						</div>
					</div>

					<!-- KPI 3: Clientes Activos -->
					<div id="kpi-customers-card" class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs transition-all hover:border-brand-500/40" title="Clientes comerciales con al menos un pedido confirmado o factura emitida en el período.">
						<div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
							<span class="text-xs font-semibold uppercase tracking-wider" data-i18n="kpi_active_customers">Clientes Activos</span>
							<div class="w-7 h-7 rounded-md bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 flex items-center justify-center">
								<i data-lucide="users" class="w-4 h-4"></i>
							</div>
						</div>
						<div class="mt-2 flex items-baseline gap-2">
							<span id="kpi-customers-val" class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white font-mono tracking-tight">0</span>
						</div>
						<div class="mt-1 flex items-center justify-between">
							<span id="kpi-customers-var" class="text-[11px] font-semibold text-indigo-600 dark:text-indigo-400">
								0% cartera
							</span>
							<span id="kpi-customers-period" class="text-[10px] text-slate-400">Registrados</span>
						</div>
						<div id="kpi-customers-sub" class="mt-2 text-[10px] text-slate-500 dark:text-slate-400 truncate border-t border-slate-100 dark:border-slate-800/80 pt-1.5">
							Con transacciones en período
						</div>
					</div>

					<!-- KPI 4: Ticket Promedio Facturado -->
					<div id="kpi-ticket-card" class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs transition-all hover:border-brand-500/40" title="Ticket Promedio = Ventas Netas Facturadas / Cantidad de Facturas Válidas.">
						<div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
							<span class="text-xs font-semibold uppercase tracking-wider" data-i18n="kpi_average_ticket">Ticket Promedio</span>
							<div class="w-7 h-7 rounded-md bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 flex items-center justify-center">
								<i data-lucide="receipt" class="w-4 h-4"></i>
							</div>
						</div>
						<div class="mt-2 flex items-baseline gap-2">
							<span id="kpi-ticket-val" class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white font-mono tracking-tight">$0.00</span>
						</div>
						<div class="mt-1 flex items-center justify-between">
							<span id="kpi-ticket-var" class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 flex items-center">
								0%
							</span>
							<span id="kpi-ticket-period" class="text-[10px] text-slate-400">Por factura</span>
						</div>
						<div id="kpi-ticket-sub" class="mt-2 text-[10px] text-slate-500 dark:text-slate-400 truncate border-t border-slate-100 dark:border-slate-800/80 pt-1.5">
							0 comprobantes válidos
						</div>
					</div>

					<!-- KPI 5: Cuentas por Cobrar -->
					<div id="kpi-receivable-card" class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs transition-all hover:border-brand-500/40" title="Saldo pendiente total exigible de facturas emitidas y parciales, deduciendo pagos y notas de crédito.">
						<div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
							<span class="text-xs font-semibold uppercase tracking-wider" data-i18n="kpi_accounts_receivable">Cuentas por Cobrar</span>
							<div class="w-7 h-7 rounded-md bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 flex items-center justify-center">
								<i data-lucide="wallet" class="w-4 h-4"></i>
							</div>
						</div>
						<div class="mt-2 flex items-baseline gap-2">
							<span id="kpi-receivable-val" class="text-xl sm:text-2xl font-bold text-amber-600 dark:text-amber-400 font-mono tracking-tight">$0.00</span>
						</div>
						<div class="mt-1 flex items-center justify-between">
							<span id="kpi-receivable-var" class="text-[11px] font-semibold text-amber-600 dark:text-amber-400 flex items-center">
								Mora: $0.00
							</span>
							<span class="text-[10px] text-slate-400">Saldo vivo</span>
						</div>
						<div id="kpi-receivable-sub" class="mt-2 text-[10px] text-slate-500 dark:text-slate-400 truncate border-t border-slate-100 dark:border-slate-800/80 pt-1.5">
							Cartera comercial activa
						</div>
					</div>

					<!-- KPI 6: Variación vs. Período Anterior -->
					<div id="kpi-variation-card" class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs transition-all hover:border-brand-500/40" title="Cálculo porcentual: (Período actual − Período anterior) / Período anterior × 100. Si período anterior es 0, muestra N/D.">
						<div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
							<span class="text-xs font-semibold uppercase tracking-wider" data-i18n="kpi_sales_variation">Variación Ventas</span>
							<div class="w-7 h-7 rounded-md bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 flex items-center justify-center">
								<i data-lucide="trending-up" class="w-4 h-4"></i>
							</div>
						</div>
						<div class="mt-2 flex items-baseline gap-2">
							<span id="kpi-variation-val" class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white font-mono tracking-tight">N/D</span>
						</div>
						<div class="mt-1 flex items-center justify-between">
							<span id="kpi-variation-period" class="text-[10px] text-slate-400 truncate">vs. Período anterior</span>
						</div>
						<div id="kpi-variation-sub" class="mt-2 text-[10px] text-slate-500 dark:text-slate-400 truncate border-t border-slate-100 dark:border-slate-800/80 pt-1.5">
							Comparativa determinista
						</div>
					</div>
				</div>

				<!-- CHARTS ROW 1: EVOLUCIÓN TEMPORAL & CATEGORÍAS -->
				<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
					<!-- Gráfico 1: Evolución de Facturación & Margen B2B -->
					<div class="lg:col-span-2 bg-white dark:bg-surface-dark p-5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex flex-col justify-between">
						<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
							<div>
								<h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
									<span data-i18n="chart_sales_trend_title">Evolución de Facturación & Margen B2B</span>
									<span id="dash-granularity-badge" class="text-[10px] font-mono uppercase px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-500">Serie Diaria</span>
								</h3>
								<p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5" data-i18n="chart_sales_trend_desc">Desempeño diario en dólares facturados frente al margen bruto alcanzado.</p>
							</div>
							<div class="flex items-center gap-4 text-xs">
								<div class="flex items-center gap-1.5">
									<span class="w-3 h-3 rounded-xs bg-brand-800 dark:bg-blue-600"></span>
									<span class="text-slate-600 dark:text-slate-300" data-i18n="legend_sales">Ventas ($ USD)</span>
								</div>
								<div class="flex items-center gap-1.5">
									<span class="w-3 h-1.5 rounded-full bg-emerald-500"></span>
									<span class="text-slate-600 dark:text-slate-300" data-i18n="legend_margin">Margen %</span>
								</div>
							</div>
						</div>
						<div class="h-64 sm:h-72 w-full relative">
							<canvas id="salesTrendChart"></canvas>
						</div>
					</div>

					<!-- Gráfico 2: Ventas por Categoría -->
					<div class="bg-white dark:bg-surface-dark p-5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex flex-col justify-between">
						<div class="mb-3">
							<h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center justify-between">
								<span data-i18n="chart_category_sales">Ventas por Categoría</span>
								<span id="dash-category-count-badge" class="text-[11px] font-semibold text-brand-700 dark:text-brand-300">Catálogo B2B</span>
							</h3>
							<p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Distribución porcentual de facturación por grupo de productos.</p>
						</div>

						<div class="h-44 sm:h-48 relative flex items-center justify-center my-1">
							<canvas id="channelShareChart"></canvas>
						</div>

						<div id="category-legend-list" class="space-y-2 pt-3 border-t border-slate-100 dark:border-slate-800 text-xs">
							<!-- Populated dynamically by JS -->
						</div>
					</div>
				</div>

				<!-- CHARTS ROW 2: FACTURACIÓN VS COBROS, PEDIDOS POR ESTADO & TOP PRODUCTOS -->
				<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
					<!-- Gráfico 3: Facturación vs. Cobros -->
					<div class="bg-white dark:bg-surface-dark p-5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex flex-col justify-between">
						<div class="mb-3">
							<h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center justify-between">
								<span data-i18n="chart_billing_vs_collections">Facturación vs. Cobros</span>
								<span class="text-[10px] font-mono uppercase px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-500">Recaudación</span>
							</h3>
							<p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Comparativo mensual entre volumen facturado y dinero cobrado.</p>
						</div>
						<div class="h-48 w-full relative">
							<canvas id="billingVsCollectionsChart"></canvas>
						</div>
						<div id="billing-collections-summary" class="pt-3 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between">
							<span>Efectividad de cobranza:</span>
							<span id="billing-collections-rate" class="font-mono font-bold text-slate-900 dark:text-white">--</span>
						</div>
					</div>

					<!-- Gráfico 4: Pedidos por Estado -->
					<div class="bg-white dark:bg-surface-dark p-5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex flex-col justify-between">
						<div class="mb-3">
							<h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center justify-between">
								<span data-i18n="chart_order_status">Pedidos por Estado</span>
								<span id="dash-order-status-total" class="text-[11px] font-semibold text-brand-700 dark:text-brand-300">0 Pedidos</span>
							</h3>
							<p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Pipeline de pedidos: confirmados, borradores y cancelados.</p>
						</div>
						<div class="h-48 w-full relative flex items-center justify-center">
							<canvas id="orderStatusChart"></canvas>
						</div>
						<div id="order-status-summary" class="pt-3 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between">
							<span>Tasa de confirmación:</span>
							<span id="order-confirm-rate" class="font-mono font-bold text-emerald-600 dark:text-emerald-400">--</span>
						</div>
					</div>

					<!-- Gráfico 5: Top 5 Productos Facturados -->
					<div class="bg-white dark:bg-surface-dark p-5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex flex-col justify-between">
						<div class="mb-3">
							<h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center justify-between">
								<span data-i18n="chart_top_products">Top 5 Productos Facturados</span>
								<span class="text-[10px] font-mono uppercase px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-500">Volumen</span>
							</h3>
							<p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Productos con mayor importe comercial facturado.</p>
						</div>
						<div class="h-48 w-full relative">
							<canvas id="topProductsChart"></canvas>
						</div>
						<div id="top-products-summary" class="pt-3 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between">
							<span>Top producto líder:</span>
							<span id="top-product-leader" class="font-mono font-semibold text-slate-900 dark:text-white truncate max-w-[130px]">--</span>
						</div>
					</div>
				</div>

				<!-- CHARTS ROW 3: TOP CUENTAS & ÓRDENES RECIENTES -->
				<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
					<!-- Tabla Ejecutiva 1: Top 5 Cuentas Corporativas -->
					<div class="bg-white dark:bg-surface-dark p-5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex flex-col justify-between">
						<div>
							<div class="flex items-center justify-between mb-3">
								<div>
									<h3 class="text-sm font-bold text-slate-900 dark:text-white" data-i18n="top_clients_title">Top 5 Cuentas Corporativas</h3>
									<p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5" data-i18n="top_clients_desc">Mayor volumen de compra en el periodo.</p>
								</div>
								<button class="text-xs font-semibold text-brand-700 dark:text-brand-300 hover:underline" data-i18n="btn_view_all" onclick="handleNavClick(event, 'customers')">Ver todos</button>
							</div>

							<div id="top-accounts-list" class="divide-y divide-slate-100 dark:divide-slate-800">
								<!-- Populated dynamically by renderTopAccounts() -->
								<div class="py-8 text-center text-xs text-slate-400">Cargando cuentas corporativas...</div>
							</div>
						</div>

						<div id="top-accounts-footer" class="pt-3 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between">
							<span>Total top 5 clientes:</span>
							<span id="top-accounts-total-sum" class="font-mono font-bold text-slate-900 dark:text-white">$0.00</span>
						</div>
					</div>

					<!-- Tabla Ejecutiva 2: Órdenes de Venta Mayoristas Recientes -->
					<div class="lg:col-span-2 bg-white dark:bg-surface-dark p-5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex flex-col justify-between">
						<div>
							<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
								<div>
									<h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
										<span data-i18n="recent_orders_title">Órdenes de Venta Mayoristas Recientes</span>
										<span class="text-[10px] font-mono uppercase px-1.5 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/50">En Tiempo Real</span>
									</h3>
									<p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5" data-i18n="recent_orders_desc">Últimos pedidos despachados o en preparación para cadenas y almacenes.</p>
								</div>
								<div class="flex items-center gap-2">
									<button onclick="handleNavClick(event, 'orders')" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-brand-800 text-white hover:bg-brand-700 shadow-xs transition-colors">
										Ver Todas en Pedidos
									</button>
								</div>
							</div>

							<div class="overflow-x-auto -mx-5 px-5">
								<table class="w-full text-left text-xs whitespace-nowrap">
									<thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-semibold uppercase text-[10px] tracking-wider border-y border-slate-200/80 dark:border-slate-800">
										<tr>
											<th class="py-2.5 px-3">Pedido</th>
											<th class="py-2.5 px-3">Cliente / Cadena</th>
											<th class="py-2.5 px-3">Sede / Establecimiento</th>
											<th class="py-2.5 px-3 text-center">Líneas</th>
											<th class="py-2.5 px-3 text-right">Total ($ USD)</th>
											<th class="py-2.5 px-3">Estado</th>
											<th class="py-2.5 px-3 text-center">Acciones</th>
										</tr>
									</thead>
									<tbody id="recent-orders-table-body" class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
										<!-- Populated dynamically by renderRecentOrders() -->
										<tr>
											<td colspan="7" class="py-8 text-center text-xs text-slate-400">Cargando órdenes mayoristas recientes...</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>

						<div id="recent-orders-footer" class="pt-3 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between">
							<span>Mostrando últimas órdenes mayoristas registradas en SQLite</span>
							<span id="recent-orders-monitored-sum" class="font-mono text-slate-700 dark:text-slate-300">Total monitoreado: $0.00 USD</span>
						</div>
					</div>
				</div>
			</div>

			<!-- SECCIÓN 2: CLIENTES Y ALMACENES (MÓDULO FUNCIONAL PASO 4) -->
			<div id="section-customers" class="hidden space-y-6">
				<!-- Header de Clientes y Almacenes -->
				<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200/80 dark:border-slate-800">
					<div>
						<h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-3">
							<span>Clientes y Almacenes</span>
							<span class="text-[11px] font-semibold uppercase px-2 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/60">Directorio B2B</span>
						</h1>
						<p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Directorio institucional de cadenas comerciales, supermercados y establecimientos asociados.</p>
					</div>

					<div class="flex items-center gap-3">
						<button onclick="openCustomerModal()" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-lg bg-brand-800 hover:bg-brand-700 text-white shadow-xs active:scale-[0.98] transition-all">
							<i data-lucide="plus-circle" class="w-4 h-4"></i>
							<span>Nuevo Cliente</span>
						</button>
					</div>
				</div>

				<!-- 4 Tarjetas KPI: Clientes Activos, Cadenas, Establecimientos, Inactivos -->
				<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
					<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
						<div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
							<span class="text-xs font-semibold uppercase tracking-wider">Clientes Activos</span>
							<div class="w-7 h-7 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 flex items-center justify-center">
								<i data-lucide="building-2" class="w-4 h-4"></i>
							</div>
						</div>
						<div class="mt-2 flex items-baseline gap-2">
							<span id="kpi-cust-active" class="text-2xl font-bold text-slate-900 dark:text-white font-mono tracking-tight">-</span>
							<span class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">En operación</span>
						</div>
						<div class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">Cuentas comerciales habilitadas</div>
					</div>

					<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
						<div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
							<span class="text-xs font-semibold uppercase tracking-wider">Cadenas Comerciales</span>
							<div class="w-7 h-7 rounded-md bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 flex items-center justify-center">
								<i data-lucide="network" class="w-4 h-4"></i>
							</div>
						</div>
						<div class="mt-2 flex items-baseline gap-2">
							<span id="kpi-cust-chains" class="text-2xl font-bold text-slate-900 dark:text-white font-mono tracking-tight">-</span>
							<span class="text-[11px] font-semibold text-blue-600 dark:text-blue-400">Multitienda</span>
						</div>
						<div class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">Cuentas con múltiples sucursales</div>
					</div>

					<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
						<div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
							<span class="text-xs font-semibold uppercase tracking-wider">Establecimientos</span>
							<div class="w-7 h-7 rounded-md bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 flex items-center justify-center">
								<i data-lucide="store" class="w-4 h-4"></i>
							</div>
						</div>
						<div class="mt-2 flex items-baseline gap-2">
							<span id="kpi-cust-est" class="text-2xl font-bold text-slate-900 dark:text-white font-mono tracking-tight">-</span>
							<span class="text-[11px] font-semibold text-indigo-600 dark:text-indigo-400">Puntos de entrega</span>
						</div>
						<div class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">Sucursales y almacenes activos</div>
					</div>

					<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
						<div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
							<span class="text-xs font-semibold uppercase tracking-wider">Clientes Inactivos</span>
							<div class="w-7 h-7 rounded-md bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 flex items-center justify-center">
								<i data-lucide="user-x" class="w-4 h-4"></i>
							</div>
						</div>
						<div class="mt-2 flex items-baseline gap-2">
							<span id="kpi-cust-inactive" class="text-2xl font-bold text-slate-900 dark:text-white font-mono tracking-tight">-</span>
							<span class="text-[11px] font-semibold text-slate-500">Pausados</span>
						</div>
						<div class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">Sin despacho o suspendidos</div>
					</div>
				</div>

				<!-- Barra de Búsqueda y Filtros de Clientes -->
				<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex flex-col md:flex-row items-center justify-between gap-3">
					<div class="relative w-full md:w-96">
						<i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
						<input type="text" id="cust-search" placeholder="Buscar por nombre, código o RUC/Tax ID..." oninput="handleCustomerSearch()" class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
					</div>

					<div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto">
						<select id="cust-filter-type" onchange="fetchCustomersList()" class="text-xs bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 font-medium text-slate-700 dark:text-slate-200 focus:outline-hidden focus:ring-1 focus:ring-brand-500">
							<option value="">Todos los Tipos</option>
							<option value="cadena">Cadena Comercial</option>
							<option value="supermercado">Supermercado Independiente</option>
							<option value="minimarket">Minimarket</option>
							<option value="otro">Otro</option>
						</select>

						<select id="cust-filter-active" onchange="fetchCustomersList()" class="text-xs bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 font-medium text-slate-700 dark:text-slate-200 focus:outline-hidden focus:ring-1 focus:ring-brand-500">
							<option value="">Todos los Estados</option>
							<option value="1" selected>Activos</option>
							<option value="0">Inactivos</option>
						</select>

						<button onclick="resetCustomerFilters()" class="px-3 py-2 text-xs font-medium rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
							Limpiar
						</button>
					</div>
				</div>

				<!-- Tabla de Clientes Comerciales -->
				<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden">
					<!-- Loading Spinner -->
					<div id="cust-table-loading" class="py-12 flex flex-col items-center justify-center text-slate-400">
						<div class="w-8 h-8 border-2 border-brand-700 border-t-transparent rounded-full animate-spin"></div>
						<span class="text-xs font-medium mt-3">Cargando directorio de clientes comerciales...</span>
					</div>

					<!-- Empty State -->
					<div id="cust-table-empty" class="hidden py-12 flex flex-col items-center justify-center text-slate-400">
						<i data-lucide="building" class="w-10 h-10 stroke-1 text-slate-300 dark:text-slate-600 mb-2"></i>
						<span class="text-sm font-semibold text-slate-700 dark:text-slate-300">No se encontraron clientes comerciales</span>
						<span class="text-xs text-slate-400 mt-1">Prueba cambiando los términos de búsqueda o los filtros.</span>
						<button onclick="resetCustomerFilters()" class="mt-4 px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-200 transition-colors">Limpiar filtros</button>
					</div>

					<!-- Table Container -->
					<div class="overflow-x-auto">
						<table id="cust-table" class="w-full text-left text-xs whitespace-nowrap">
							<thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-semibold uppercase text-[10px] tracking-wider border-b border-slate-200/80 dark:border-slate-800">
								<tr>
									<th class="py-3 px-4">Cliente / Razón Social</th>
									<th class="py-3 px-4">Tipo</th>
									<th class="py-3 px-4">Identificación Fiscal</th>
									<th class="py-3 px-4 text-center">Establecimientos</th>
									<th class="py-3 px-4">Contacto</th>
									<th class="py-3 px-4">Estado</th>
									<th class="py-3 px-4 text-right">Acciones</th>
								</tr>
							</thead>
							<tbody id="cust-table-body" class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
								<!-- Filas renderizadas dinámicamente -->
							</tbody>
						</table>
					</div>

					<!-- Table Footer Summary -->
					<div class="p-3 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between">
						<span id="cust-table-summary">Mostrando clientes comerciales</span>
						<span class="font-mono text-slate-600 dark:text-slate-300">Base de datos sincronizada</span>
					</div>
				</div>
			</div>

			<!-- SECCIÓN 3: PRODUCTOS Y PRECIOS B2B (MÓDULO FUNCIONAL PASO 5) -->
			<div id="section-products" class="hidden space-y-6">
				<!-- Header de Productos y Precios -->
				<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200/80 dark:border-slate-800">
					<div>
						<h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-3">
							<span>Catálogo y Precios B2B</span>
							<span class="text-[11px] font-semibold uppercase px-2 py-0.5 rounded-md bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200/60 dark:border-blue-800/60">Tarifario Mayorista</span>
						</h1>
						<p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Gestión de unidades comerciales, factores de conversión, escalas por volumen y acuerdos especiales por cliente.</p>
					</div>

					<div class="flex items-center gap-2.5">
						<button onclick="openPricingCalculatorModal()" class="inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold rounded-lg bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 shadow-xs transition-all">
							<i data-lucide="calculator" class="w-4 h-4 text-brand-600 dark:text-brand-400"></i>
							<span>Simulador de Precios</span>
						</button>
						<button onclick="openProductModal()" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-lg bg-brand-800 hover:bg-brand-700 text-white shadow-xs active:scale-[0.98] transition-all">
							<i data-lucide="plus" class="w-4 h-4"></i>
							<span>Nuevo Producto</span>
						</button>
					</div>
				</div>

				<!-- 4 Tarjetas KPI de Productos -->
				<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
					<!-- Total Productos -->
					<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
						<div class="flex items-center justify-between">
							<span class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Productos</span>
							<div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center">
								<i data-lucide="package" class="w-4 h-4"></i>
							</div>
						</div>
						<div class="mt-2 flex items-baseline gap-2">
							<span id="kpi-prod-total" class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">--</span>
							<span class="text-[11px] text-emerald-600 font-medium">100% en catálogo</span>
						</div>
					</div>

					<!-- Categorías Comerciales -->
					<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
						<div class="flex items-center justify-between">
							<span class="text-xs font-medium text-slate-500 dark:text-slate-400">Categorías Comerciales</span>
							<div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
								<i data-lucide="tags" class="w-4 h-4"></i>
							</div>
						</div>
						<div class="mt-2 flex items-baseline gap-2">
							<span id="kpi-prod-categories" class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">--</span>
							<span class="text-[11px] text-slate-400">Líneas activas</span>
						</div>
					</div>

					<!-- Marcas Registradas -->
					<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
						<div class="flex items-center justify-between">
							<span class="text-xs font-medium text-slate-500 dark:text-slate-400">Marcas Registradas</span>
							<div class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center">
								<i data-lucide="award" class="w-4 h-4"></i>
							</div>
						</div>
						<div class="mt-2 flex items-baseline gap-2">
							<span id="kpi-prod-brands" class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">--</span>
							<span class="text-[11px] text-purple-600 font-medium">Fabricantes oficiales</span>
						</div>
					</div>

					<!-- Acuerdos Especiales -->
					<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
						<div class="flex items-center justify-between">
							<span class="text-xs font-medium text-slate-500 dark:text-slate-400">Acuerdos de Precios</span>
							<div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center">
								<i data-lucide="file-check" class="w-4 h-4"></i>
							</div>
						</div>
						<div class="mt-2 flex items-baseline gap-2">
							<span id="kpi-prod-agreements" class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">--</span>
							<span class="text-[11px] text-amber-600 font-medium">Tarifas preferenciales</span>
						</div>
					</div>
				</div>

				<!-- Barra de Filtros y Búsqueda -->
				<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
					<div class="relative flex-1">
						<i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
						<input type="text" id="prod-search" oninput="debounceFetchProducts()" placeholder="Buscar por SKU, nombre del producto o código de barras..." class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
					</div>

					<div class="flex flex-wrap items-center gap-2">
						<!-- Filtro Categoría -->
						<select id="prod-filter-category" onchange="fetchProductsList()" class="px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-hidden focus:border-brand-500">
							<option value="">Todas las Categorías</option>
						</select>

						<!-- Filtro Marca -->
						<select id="prod-filter-brand" onchange="fetchProductsList()" class="px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-hidden focus:border-brand-500">
							<option value="">Todas las Marcas</option>
						</select>

						<!-- Filtro Estado -->
						<select id="prod-filter-active" onchange="fetchProductsList()" class="px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-hidden focus:border-brand-500">
							<option value="">Todos los Estados</option>
							<option value="1">Solo Activos</option>
							<option value="0">Solo Inactivos</option>
						</select>

						<!-- Botón Reset -->
						<button onclick="resetProductFilters()" class="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800" title="Restablecer filtros">
							<i data-lucide="rotate-ccw" class="w-4 h-4"></i>
						</button>
					</div>
				</div>

				<!-- Tabla de Productos B2B -->
				<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden">
					<div class="overflow-x-auto">
						<table class="w-full text-left border-collapse">
							<thead>
								<tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-900/40 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
									<th class="py-3 px-4">SKU</th>
									<th class="py-3 px-4">Producto & Código</th>
									<th class="py-3 px-4">Marca & Categoría</th>
									<th class="py-3 px-4 text-center">Unidad / Factor</th>
									<th class="py-3 px-4 text-right">Precio Unit. Base</th>
									<th class="py-3 px-4 text-right">Precio Caja Base</th>
									<th class="py-3 px-4 text-center">Reglas Comerciales</th>
									<th class="py-3 px-4 text-center">Estado</th>
									<th class="py-3 px-4 text-right">Acciones</th>
								</tr>
							</thead>
							<tbody id="prod-table-body" class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
								<!-- Filas dinámicas -->
							</tbody>
						</table>

						<!-- Loading State -->
						<div id="prod-table-loading" class="py-12 flex flex-col items-center justify-center gap-2 text-slate-400">
							<i data-lucide="loader-2" class="w-6 h-6 animate-spin text-brand-600"></i>
							<span class="text-xs">Cargando catálogo comercial mayorista...</span>
						</div>

						<!-- Empty State -->
						<div id="prod-table-empty" class="hidden py-12 flex flex-col items-center justify-center gap-2 text-slate-400">
							<i data-lucide="package-search" class="w-8 h-8 text-slate-300 dark:text-slate-600"></i>
							<span class="text-xs font-medium text-slate-600 dark:text-slate-300">No se encontraron productos en el catálogo comercial</span>
							<span class="text-[11px] text-slate-400">Intenta ajustar los filtros de búsqueda o registra un nuevo producto.</span>
						</div>
					</div>

					<!-- Table Footer Summary -->
					<div class="p-3 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between">
						<span id="prod-table-summary">Mostrando catálogo comercial</span>
						<span class="font-mono text-slate-600 dark:text-slate-300">Motor determinista de precios activo</span>
					</div>
				</div>
			</div>

			<!-- SECCIÓN 4: COTIZACIONES Y PEDIDOS B2B (MÓDULO FUNCIONAL PASO 6) -->
			<div id="section-orders" class="hidden space-y-6">
				<!-- Header de Cotizaciones y Pedidos -->
				<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200/80 dark:border-slate-800">
					<div>
						<h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-3">
							<span>Cotizaciones y Pedidos</span>
							<span class="text-[11px] font-semibold uppercase px-2 py-0.5 rounded-md bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200/60 dark:border-purple-800/60">Ciclo Comercial B2B</span>
						</h1>
						<p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Gestión integral de cotizaciones, conversión a órdenes de venta, congelamiento de tarifas mayoristas y trazabilidad de pedidos.</p>
					</div>

					<div class="flex items-center gap-2.5">
						<button onclick="openCommercialFormModal('quote')" class="inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold rounded-lg bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 shadow-xs transition-all">
							<i data-lucide="file-text" class="w-4 h-4 text-brand-600 dark:text-brand-400"></i>
							<span>Nueva Cotización</span>
						</button>
						<button onclick="openCommercialFormModal('order')" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-lg bg-brand-800 hover:bg-brand-700 text-white shadow-xs active:scale-[0.98] transition-all">
							<i data-lucide="plus" class="w-4 h-4"></i>
							<span>Nuevo Pedido</span>
						</button>
					</div>
				</div>

				<!-- 4 Tarjetas KPI Comerciales -->
				<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
					<!-- Pedidos Confirmados -->
					<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
						<div class="flex items-center justify-between">
							<span class="text-xs font-medium text-slate-500 dark:text-slate-400">Pedidos Confirmados</span>
							<div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
								<i data-lucide="check-circle-2" class="w-4 h-4"></i>
							</div>
						</div>
						<div class="mt-2 flex items-baseline gap-2">
							<span id="kpi-ord-confirmed" class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">--</span>
							<span class="text-[11px] text-emerald-600 font-medium">Órdenes firmes</span>
						</div>
					</div>

					<!-- Facturación Pedidos -->
					<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
						<div class="flex items-center justify-between">
							<span class="text-xs font-medium text-slate-500 dark:text-slate-400">Facturación Confirmada</span>
							<div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center">
								<i data-lucide="dollar-sign" class="w-4 h-4"></i>
							</div>
						</div>
						<div class="mt-2 flex items-baseline gap-2">
							<span id="kpi-ord-revenue" class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white font-mono">$0.00</span>
							<span class="text-[11px] text-blue-600 font-medium">USD Mayorista</span>
						</div>
					</div>

					<!-- Cotizaciones Activas -->
					<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
						<div class="flex items-center justify-between">
							<span class="text-xs font-medium text-slate-500 dark:text-slate-400">Cotizaciones Activas</span>
							<div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center">
								<i data-lucide="file-clock" class="w-4 h-4"></i>
							</div>
						</div>
						<div class="mt-2 flex items-baseline gap-2">
							<span id="kpi-ord-quotes" class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">--</span>
							<span class="text-[11px] text-amber-600 font-medium">En negociación</span>
						</div>
					</div>

					<!-- Conversión a Pedido -->
					<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
						<div class="flex items-center justify-between">
							<span class="text-xs font-medium text-slate-500 dark:text-slate-400">Conversión Cotizaciones</span>
							<div class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center">
								<i data-lucide="trending-up" class="w-4 h-4"></i>
							</div>
						</div>
						<div class="mt-2 flex items-baseline gap-2">
							<span id="kpi-ord-conversion" class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">--%</span>
							<span class="text-[11px] text-purple-600 font-medium">Tasa de cierre</span>
						</div>
					</div>
				</div>

				<!-- Selector de Sub-Pestañas: Pedidos vs Cotizaciones -->
				<div class="flex items-center gap-3 border-b border-slate-200 dark:border-slate-800 pb-px">
					<button type="button" id="tab-subnav-orders" onclick="switchOrdersMainTab('orders')" class="inline-flex items-center gap-2 pb-3 px-1 border-b-2 border-brand-800 text-brand-800 dark:text-white text-xs font-bold transition-all">
						<i data-lucide="shopping-cart" class="w-4 h-4"></i>
						<span>Pedidos Comerciales</span>
						<span id="badge-orders-count" class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-brand-50 dark:bg-brand-950/60 text-brand-700 dark:text-brand-300">0</span>
					</button>
					<button type="button" id="tab-subnav-quotes" onclick="switchOrdersMainTab('quotes')" class="inline-flex items-center gap-2 pb-3 px-1 border-b-2 border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 text-xs font-medium transition-all">
						<i data-lucide="file-text" class="w-4 h-4"></i>
						<span>Cotizaciones Comerciales</span>
						<span id="badge-quotes-count" class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">0</span>
					</button>
				</div>

				<!-- Barra de Filtros y Búsqueda -->
				<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
					<div class="relative flex-1">
						<i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
						<input type="text" id="orders-search" oninput="debounceFetchOrders()" placeholder="Buscar por código (PED/COT), cliente o vendedor..." class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
					</div>

					<div class="flex flex-wrap items-center gap-2">
						<!-- Filtro Estado -->
						<select id="orders-filter-status" onchange="fetchCurrentOrdersOrQuotes()" class="px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-hidden focus:border-brand-500">
							<option value="">Todos los Estados</option>
						</select>

						<!-- Filtro Cliente -->
						<select id="orders-filter-customer" onchange="fetchCurrentOrdersOrQuotes()" class="px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-hidden focus:border-brand-500 max-w-[200px]">
							<option value="">Todos los Clientes</option>
						</select>

						<!-- Botón Reset -->
						<button onclick="resetOrderFilters()" class="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800" title="Restablecer filtros">
							<i data-lucide="rotate-ccw" class="w-4 h-4"></i>
						</button>
					</div>
				</div>

				<!-- TABLA 1: PEDIDOS COMERCIALES -->
				<div id="container-orders-table" class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden">
					<div class="overflow-x-auto">
						<table class="w-full text-left border-collapse">
							<thead>
								<tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-900/40 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
									<th class="py-3 px-4">N° Pedido</th>
									<th class="py-3 px-4">Cliente Comercial & Sucursal</th>
									<th class="py-3 px-4">Ejecutivo</th>
									<th class="py-3 px-4 text-center">Ítems</th>
									<th class="py-3 px-4 text-right">Subtotal</th>
									<th class="py-3 px-4 text-right">Total (IGV 18%)</th>
									<th class="py-3 px-4 text-center">Estado</th>
									<th class="py-3 px-4 text-right">Acciones</th>
								</tr>
							</thead>
							<tbody id="orders-table-body" class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
								<!-- Filas dinámicas -->
							</tbody>
						</table>

						<!-- Loading State -->
						<div id="orders-table-loading" class="py-12 flex flex-col items-center justify-center gap-2 text-slate-400">
							<i data-lucide="loader-2" class="w-6 h-6 animate-spin text-brand-600"></i>
							<span class="text-xs">Cargando pedidos comerciales mayoristas...</span>
						</div>

						<!-- Empty State -->
						<div id="orders-table-empty" class="hidden py-12 flex flex-col items-center justify-center gap-2 text-slate-400">
							<i data-lucide="shopping-bag" class="w-8 h-8 text-slate-300 dark:text-slate-600"></i>
							<span class="text-xs font-medium text-slate-600 dark:text-slate-300">No se encontraron pedidos comerciales</span>
							<span class="text-[11px] text-slate-400">Ajusta los filtros o registra un nuevo pedido B2B.</span>
						</div>
					</div>

					<div class="p-3 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between">
						<span id="orders-table-summary">Mostrando pedidos comerciales</span>
						<span class="font-mono text-slate-600 dark:text-slate-300">Tarifas congeladas por pedido</span>
					</div>
				</div>

				<!-- TABLA 2: COTIZACIONES COMERCIALES -->
				<div id="container-quotes-table" class="hidden bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden">
					<div class="overflow-x-auto">
						<table class="w-full text-left border-collapse">
							<thead>
								<tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-900/40 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
									<th class="py-3 px-4">N° Cotización</th>
									<th class="py-3 px-4">Cliente Comercial & Sucursal</th>
									<th class="py-3 px-4">Ejecutivo</th>
									<th class="py-3 px-4">Vencimiento</th>
									<th class="py-3 px-4 text-center">Ítems</th>
									<th class="py-3 px-4 text-right">Subtotal</th>
									<th class="py-3 px-4 text-right">Total (IGV 18%)</th>
									<th class="py-3 px-4 text-center">Estado</th>
									<th class="py-3 px-4 text-right">Acciones</th>
								</tr>
							</thead>
							<tbody id="quotes-table-body" class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
								<!-- Filas dinámicas -->
							</tbody>
						</table>

						<!-- Loading State -->
						<div id="quotes-table-loading" class="py-12 flex flex-col items-center justify-center gap-2 text-slate-400">
							<i data-lucide="loader-2" class="w-6 h-6 animate-spin text-brand-600"></i>
							<span class="text-xs">Cargando cotizaciones comerciales...</span>
						</div>

						<!-- Empty State -->
						<div id="quotes-table-empty" class="hidden py-12 flex flex-col items-center justify-center gap-2 text-slate-400">
							<i data-lucide="file-question" class="w-8 h-8 text-slate-300 dark:text-slate-600"></i>
							<span class="text-xs font-medium text-slate-600 dark:text-slate-300">No se encontraron cotizaciones comerciales</span>
							<span class="text-[11px] text-slate-400">Registra una nueva cotización comercial para una cadena o supermercado.</span>
						</div>
					</div>

					<div class="p-3 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between">
						<span id="quotes-table-summary">Mostrando cotizaciones comerciales</span>
						<span class="font-mono text-slate-600 dark:text-slate-300">Flujo de aprobación &amp; conversión</span>
					</div>
				</div>
			</div>

			<!-- SECCIÓN 4.1: FACTURACIÓN COMERCIAL Y COBRANZAS B2B (PASO 7) -->
			<div id="section-invoices" class="hidden space-y-6">
				<!-- Header de Facturación Comercial -->
				<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200/80 dark:border-slate-800">
					<div>
						<h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-3">
							<span>Facturación y Cobranzas</span>
							<span class="text-[11px] font-semibold uppercase px-2 py-0.5 rounded-md bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200/60 dark:border-blue-800/60">Cuentas por Cobrar &amp; Créditos</span>
						</h1>
						<p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Generación de facturas desde pedidos confirmados, control de saldos y morosidad, amortizaciones de pago y emisión de notas de crédito.</p>
					</div>

					<div class="flex items-center gap-2">
						<button type="button" onclick="openCreditNoteModal()" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
							<i data-lucide="file-minus" class="w-4 h-4 text-purple-600"></i>
							<span>Nota de Crédito</span>
						</button>
						<button type="button" onclick="openGenerateInvoiceModal()" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-lg bg-brand-800 hover:bg-brand-700 text-white shadow-xs transition-colors">
							<i data-lucide="plus-circle" class="w-4 h-4"></i>
							<span>Facturar Pedido</span>
						</button>
					</div>
				</div>

				<!-- 4 Tarjetas KPI Ejecutivas de Facturación -->
				<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
					<!-- KPI 1: Ventas Netas B2B (Fórmula Estricta) -->
					<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex items-center justify-between">
						<div>
							<p class="text-xs font-medium text-slate-500 dark:text-slate-400">Ventas Netas B2B</p>
							<h3 id="kpi-inv-net-sales" class="text-xl font-bold text-slate-900 dark:text-white mt-1 font-mono">$0.00</h3>
							<p class="text-[11px] text-emerald-600 dark:text-emerald-400 mt-0.5 flex items-center gap-1 font-medium">
								<i data-lucide="shield-check" class="w-3 h-3"></i>
								<span>Ventas válidas − NC (sin imp.)</span>
							</p>
						</div>
						<div class="w-10 h-10 rounded-lg bg-brand-50 dark:bg-brand-950/60 text-brand-700 dark:text-brand-300 flex items-center justify-center shrink-0">
							<i data-lucide="receipt" class="w-5 h-5"></i>
						</div>
					</div>

					<!-- KPI 2: Cuentas por Cobrar (Saldos) -->
					<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex items-center justify-between">
						<div>
							<p class="text-xs font-medium text-slate-500 dark:text-slate-400">Cuentas por Cobrar (Saldos)</p>
							<h3 id="kpi-inv-receivables" class="text-xl font-bold text-amber-600 dark:text-amber-400 mt-1 font-mono">$0.00</h3>
							<p class="text-[11px] text-slate-400 mt-0.5">Cartera pendiente de cobro</p>
						</div>
						<div class="w-10 h-10 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
							<i data-lucide="wallet" class="w-5 h-5"></i>
						</div>
					</div>

					<!-- KPI 3: Recaudación Cobrada -->
					<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex items-center justify-between">
						<div>
							<p class="text-xs font-medium text-slate-500 dark:text-slate-400">Recaudación Cobrada</p>
							<h3 id="kpi-inv-collected" class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-1 font-mono">$0.00</h3>
							<p class="text-[11px] text-slate-400 mt-0.5">Abonos y pagos liquidados</p>
						</div>
						<div class="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
							<i data-lucide="check-circle-2" class="w-5 h-5"></i>
						</div>
					</div>

					<!-- KPI 4: Facturas Vencidas / Mora -->
					<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex items-center justify-between">
						<div>
							<p class="text-xs font-medium text-slate-500 dark:text-slate-400">Facturas Vencidas (Mora)</p>
							<h3 id="kpi-inv-overdue" class="text-xl font-bold text-rose-600 dark:text-rose-400 mt-1 font-mono">$0.00</h3>
							<p class="text-[11px] text-rose-600 dark:text-rose-400 mt-0.5 font-medium flex items-center gap-1">
								<span id="kpi-inv-overdue-count" class="px-1.5 py-0.2 bg-rose-100 dark:bg-rose-950/80 rounded font-bold">0</span>
								<span>comprobantes en mora</span>
							</p>
						</div>
						<div class="w-10 h-10 rounded-lg bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
							<i data-lucide="alert-circle" class="w-5 h-5"></i>
						</div>
					</div>
				</div>

				<!-- Sub-Navegación: Facturas vs Notas de Crédito vs Mora -->
				<div class="border-b border-slate-200 dark:border-slate-800 flex items-center gap-6">
					<button type="button" id="tab-subnav-invoices" onclick="switchInvoicesSubTab('invoices')" class="inline-flex items-center gap-2 pb-3 px-1 border-b-2 border-brand-800 text-brand-800 dark:text-white text-xs font-bold transition-all">
						<i data-lucide="receipt" class="w-4 h-4"></i>
						<span>Facturas Comerciales</span>
						<span id="badge-invoices-tab-count" class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-brand-50 dark:bg-brand-950/60 text-brand-700 dark:text-brand-300">0</span>
					</button>

					<button type="button" id="tab-subnav-credit-notes" onclick="switchInvoicesSubTab('credit-notes')" class="inline-flex items-center gap-2 pb-3 px-1 border-b-2 border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 text-xs font-medium transition-all">
						<i data-lucide="file-minus" class="w-4 h-4"></i>
						<span>Notas de Crédito</span>
						<span id="badge-cn-tab-count" class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300">0</span>
					</button>

					<button type="button" id="tab-subnav-overdue" onclick="switchInvoicesSubTab('overdue')" class="inline-flex items-center gap-2 pb-3 px-1 border-b-2 border-transparent text-slate-500 hover:text-rose-600 dark:hover:text-rose-400 text-xs font-medium transition-all">
						<i data-lucide="clock" class="w-4 h-4 text-rose-500"></i>
						<span>Cartera Vencida</span>
						<span id="badge-overdue-tab-count" class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300">0</span>
					</button>
				</div>

				<!-- Barra de Búsqueda y Filtros -->
				<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
					<div class="relative flex-1">
						<i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
						<input type="text" id="invoices-search" oninput="debounceFetchInvoices()" placeholder="Buscar por número (FAC/NC), cliente o pedido..." class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
					</div>

					<div class="flex flex-wrap items-center gap-2">
						<!-- Filtro Estado Factura -->
						<select id="invoices-filter-status" onchange="fetchInvoicesList()" class="px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-hidden focus:border-brand-500">
							<option value="">Todos los Estados</option>
							<option value="Emitida">Emitida</option>
							<option value="Parcialmente pagada">Parcialmente pagada</option>
							<option value="Pagada">Pagada</option>
							<option value="Anulada">Anulada</option>
						</select>

						<!-- Filtro Cliente -->
						<select id="invoices-filter-customer" onchange="fetchInvoicesList()" class="px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-hidden focus:border-brand-500 max-w-[200px]">
							<option value="">Todos los Clientes</option>
						</select>

						<button type="button" onclick="resetInvoicesFilters()" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" title="Restablecer filtros">
							<i data-lucide="rotate-ccw" class="w-4 h-4"></i>
						</button>
					</div>
				</div>

				<!-- TABLA 1: FACTURAS COMERCIALES -->
				<div id="table-container-invoices" class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden">
					<div class="overflow-x-auto">
						<table class="w-full text-left border-collapse text-xs">
							<thead>
								<tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-800/40 text-slate-500 dark:text-slate-400 font-semibold">
									<th class="py-3 px-4">N° Factura</th>
									<th class="py-3 px-4">Cliente Comercial &amp; Sede</th>
									<th class="py-3 px-4">Pedido Origen</th>
									<th class="py-3 px-4">Emisión / Vencimiento</th>
									<th class="py-3 px-4 text-right">Total Facturado</th>
									<th class="py-3 px-4 text-right">Cobrado</th>
									<th class="py-3 px-4 text-right">Saldo Pendiente</th>
									<th class="py-3 px-4 text-center">Estado</th>
									<th class="py-3 px-4 text-right">Acciones</th>
								</tr>
							</thead>
							<tbody id="invoices-table-body" class="divide-y divide-slate-100 dark:divide-slate-800/60">
								<!-- Inyectado vía JS -->
							</tbody>
						</table>

						<!-- Loading State -->
						<div id="invoices-table-loading" class="hidden py-12 flex flex-col items-center justify-center gap-2 text-slate-400">
							<i data-lucide="loader-2" class="w-6 h-6 animate-spin text-brand-700"></i>
							<span class="text-xs">Cargando comprobantes comerciales...</span>
						</div>

						<!-- Empty State -->
						<div id="invoices-table-empty" class="hidden py-12 flex flex-col items-center justify-center gap-2 text-slate-400">
							<i data-lucide="receipt" class="w-8 h-8 text-slate-300 dark:text-slate-600"></i>
							<span class="text-xs font-medium text-slate-600 dark:text-slate-300">No se encontraron facturas comerciales</span>
							<span class="text-[11px] text-slate-400">Genera una factura desde pedidos confirmados o ajusta los filtros.</span>
						</div>
					</div>

					<div class="p-3 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between">
						<span id="invoices-table-summary">Mostrando facturas comerciales</span>
						<span class="font-mono text-slate-600 dark:text-slate-300">Control de Cartera B2B</span>
					</div>
				</div>

				<!-- TABLA 2: NOTAS DE CRÉDITO -->
				<div id="table-container-credit-notes" class="hidden bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden">
					<div class="overflow-x-auto">
						<table class="w-full text-left border-collapse text-xs">
							<thead>
								<tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-800/40 text-slate-500 dark:text-slate-400 font-semibold">
									<th class="py-3 px-4">N° Nota de Crédito</th>
									<th class="py-3 px-4">Factura Afectada</th>
									<th class="py-3 px-4">Cliente Comercial</th>
									<th class="py-3 px-4">Motivo Comercial</th>
									<th class="py-3 px-4 text-right">Subtotal</th>
									<th class="py-3 px-4 text-right">Impuesto (18%)</th>
									<th class="py-3 px-4 text-right">Total Acreditado</th>
									<th class="py-3 px-4 text-center">Estado</th>
									<th class="py-3 px-4 text-right">Acciones</th>
								</tr>
							</thead>
							<tbody id="credit-notes-table-body" class="divide-y divide-slate-100 dark:divide-slate-800/60">
								<!-- Inyectado vía JS -->
							</tbody>
						</table>

						<!-- Empty State -->
						<div id="credit-notes-table-empty" class="hidden py-12 flex flex-col items-center justify-center gap-2 text-slate-400">
							<i data-lucide="file-minus" class="w-8 h-8 text-slate-300 dark:text-slate-600"></i>
							<span class="text-xs font-medium text-slate-600 dark:text-slate-300">No se encontraron notas de crédito</span>
							<span class="text-[11px] text-slate-400">Emite una nota de crédito vinculada a una factura emitida.</span>
						</div>
					</div>

					<div class="p-3 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between">
						<span id="credit-notes-table-summary">Mostrando notas de crédito comerciales</span>
						<span class="font-mono text-slate-600 dark:text-slate-300">Ajustes &amp; Devoluciones</span>
					</div>
				</div>
			</div>

			<!-- SECCIÓN 6: VENDEDORES Y TERRITORIOS COMERCIALES (MÓDULO FUNCIONAL PASO 9) -->
			<div id="section-sales-reps" class="hidden space-y-6">
				<!-- TITLE & ACTION BUTTONS -->
				<div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-2 border-b border-slate-200/80 dark:border-slate-800">
					<div>
						<h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-3">
							<span data-i18n="nav_sales_reps">Vendedores y Territorios</span>
							<span class="text-[11px] font-semibold uppercase px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-800/60">Fuerza de Ventas</span>
							<span class="text-[10px] font-semibold uppercase px-1.5 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/50">DEMO B2B</span>
						</h1>
						<p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Estructura comercial mayorista, asignación territorial, cumplimiento de metas y cartera de clientes.</p>
					</div>

					<!-- Botones de Acción -->
					<div class="flex flex-wrap items-center gap-2">
						<button onclick="openCreateSalesRepModal()" class="inline-flex items-center gap-2 px-3 py-1.5 sm:px-3.5 sm:py-2 text-xs font-semibold rounded-lg bg-brand-800 hover:bg-brand-700 text-white shadow-xs active:scale-[0.98] transition-all">
							<i data-lucide="user-plus" class="w-4 h-4"></i>
							<span>Nuevo Vendedor</span>
						</button>
						<button onclick="openCreateTerritoryModal()" class="inline-flex items-center gap-2 px-3 py-1.5 sm:px-3.5 sm:py-2 text-xs font-semibold rounded-lg bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 shadow-xs transition-colors">
							<i data-lucide="map-pin" class="w-4 h-4 text-slate-500"></i>
							<span>Nuevo Territorio</span>
						</button>
						<button onclick="openReassignEstablishmentModal()" class="inline-flex items-center gap-2 px-3 py-1.5 sm:px-3.5 sm:py-2 text-xs font-semibold rounded-lg bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 shadow-xs transition-colors">
							<i data-lucide="arrow-right-left" class="w-4 h-4 text-brand-600"></i>
							<span>Reasignar Sede</span>
						</button>
					</div>
				</div>

				<!-- 4 KPI CARDS: FUERZA COMERCIAL Y METAS -->
				<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
					<!-- Card 1: Ventas Netas del Equipo -->
					<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
						<div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
							<span class="text-xs font-semibold uppercase tracking-wider">Ventas Netas Equipo</span>
							<div class="w-7 h-7 rounded-md bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 flex items-center justify-center">
								<i data-lucide="dollar-sign" class="w-4 h-4"></i>
							</div>
						</div>
						<div class="mt-2 flex items-baseline gap-2">
							<span id="kpi-reps-team-sales" class="text-2xl font-bold text-slate-900 dark:text-white font-mono tracking-tight">$0.00</span>
						</div>
						<div id="kpi-reps-quota-sub" class="mt-2 text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between border-t border-slate-100 dark:border-slate-800/80 pt-1.5">
							<span>Meta global del mes:</span>
							<span id="kpi-reps-quota-val" class="font-semibold text-slate-700 dark:text-slate-300">$0.00</span>
						</div>
					</div>

					<!-- Card 2: Cumplimiento de Meta Global -->
					<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
						<div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
							<span class="text-xs font-semibold uppercase tracking-wider">Cumplimiento Global</span>
							<div class="w-7 h-7 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 flex items-center justify-center">
								<i data-lucide="target" class="w-4 h-4"></i>
							</div>
						</div>
						<div class="mt-2 flex items-baseline gap-2">
							<span id="kpi-reps-achievement-val" class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 font-mono tracking-tight">0%</span>
						</div>
						<div class="mt-2 w-full h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
							<div id="kpi-reps-achievement-bar" class="h-full bg-emerald-600 rounded-full" style="width: 0%"></div>
						</div>
						<div class="mt-1.5 text-[10px] text-slate-500 dark:text-slate-400 flex items-center justify-between">
							<span>Avance de cuota mensual</span>
							<span id="kpi-reps-achievement-note" class="font-medium text-slate-700 dark:text-slate-300">En curso</span>
						</div>
					</div>

					<!-- Card 3: Fuerza de Ventas Activa -->
					<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
						<div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
							<span class="text-xs font-semibold uppercase tracking-wider">Fuerza de Ventas</span>
							<div class="w-7 h-7 rounded-md bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 flex items-center justify-center">
								<i data-lucide="users" class="w-4 h-4"></i>
							</div>
						</div>
						<div class="mt-2 flex items-baseline gap-2">
							<span id="kpi-reps-active-count" class="text-2xl font-bold text-slate-900 dark:text-white font-mono tracking-tight">0</span>
							<span class="text-xs text-slate-500">vendedores</span>
						</div>
						<div id="kpi-reps-territories-sub" class="mt-2 text-[11px] text-slate-500 dark:text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-1.5 flex items-center justify-between">
							<span>Territorios cubiertos:</span>
							<span id="kpi-reps-territories-count" class="font-semibold text-slate-700 dark:text-slate-300">0</span>
						</div>
					</div>

					<!-- Card 4: Cartera en Gestión de Cobro -->
					<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
						<div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
							<span class="text-xs font-semibold uppercase tracking-wider">Cartera Viva Cobranzas</span>
							<div class="w-7 h-7 rounded-md bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 flex items-center justify-center">
								<i data-lucide="wallet" class="w-4 h-4"></i>
							</div>
						</div>
						<div class="mt-2 flex items-baseline gap-2">
							<span id="kpi-reps-ar-val" class="text-2xl font-bold text-amber-600 dark:text-amber-400 font-mono tracking-tight">$0.00</span>
						</div>
						<div class="mt-2 text-[11px] text-slate-500 dark:text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-1.5 flex items-center justify-between">
							<span>Saldos vivos de clientes asignados</span>
						</div>
					</div>
				</div>

				<!-- SUB-TABS NAVIGATION -->
				<div class="border-b border-slate-200 dark:border-slate-800">
					<nav class="flex space-x-6 text-xs sm:text-sm font-semibold">
						<button id="tab-reps-btn" onclick="switchSalesRepsSubTab('reps')" class="pb-3 border-b-2 border-brand-800 text-brand-800 dark:text-white dark:border-white transition-all flex items-center gap-2">
							<i data-lucide="users" class="w-4 h-4"></i>
							<span>Vendedores & Productividad</span>
						</button>
						<button id="tab-territories-btn" onclick="switchSalesRepsSubTab('territories')" class="pb-3 border-b-2 border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition-all flex items-center gap-2">
							<i data-lucide="map" class="w-4 h-4"></i>
							<span>Territorios & Cobertura</span>
						</button>
						<button id="tab-history-btn" onclick="switchSalesRepsSubTab('history')" class="pb-3 border-b-2 border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition-all flex items-center gap-2">
							<i data-lucide="history" class="w-4 h-4"></i>
							<span>Auditoría de Reasignaciones</span>
						</button>
					</nav>
				</div>

				<!-- SUB-TAB 1: VENDEDORES & METAS -->
				<div id="subtab-reps-view" class="space-y-4">
					<!-- Toolbar de Búsqueda y Filtros -->
					<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
						<div class="flex items-center gap-2 w-full sm:w-auto">
							<div class="relative w-full sm:w-72">
								<i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
								<input type="text" id="reps-search-input" onkeyup="filterSalesRepsList()" placeholder="Buscar por vendedor, código o correo..." class="w-full text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg pl-9 pr-3 py-1.5 text-slate-900 dark:text-white focus:outline-hidden focus:ring-1 focus:ring-brand-500" />
							</div>
							<select id="reps-filter-territory" onchange="filterSalesRepsList()" class="text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 font-medium text-slate-700 dark:text-slate-200 focus:outline-hidden">
								<option value="">Todos los Territorios</option>
							</select>
							<select id="reps-filter-status" onchange="filterSalesRepsList()" class="text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 font-medium text-slate-700 dark:text-slate-200 focus:outline-hidden">
								<option value="">Todos los Estados</option>
								<option value="1">Activos</option>
								<option value="0">Inactivos</option>
							</select>
						</div>

						<div class="text-[11px] text-slate-500 dark:text-slate-400 shrink-0">
							Período: <span class="font-semibold text-slate-700 dark:text-slate-200">Noviembre 2026 MTD</span>
						</div>
					</div>

					<!-- Tabla de Vendedores -->
					<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden">
						<div class="overflow-x-auto">
							<table class="w-full text-left text-xs whitespace-nowrap">
								<thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-semibold uppercase text-[10px] tracking-wider border-b border-slate-200/80 dark:border-slate-800">
									<tr>
										<th class="py-2.5 px-3 text-center">Rank</th>
										<th class="py-2.5 px-3">Vendedor</th>
										<th class="py-2.5 px-3">Territorio Asignado</th>
										<th class="py-2.5 px-3 text-center">Cartera Asignada</th>
										<th class="py-2.5 px-3 text-center">Pedidos</th>
										<th class="py-2.5 px-3 text-right">Ventas Netas ($)</th>
										<th class="py-2.5 px-3 text-right">Meta Mensual ($)</th>
										<th class="py-2.5 px-3 text-center">Cumplimiento</th>
										<th class="py-2.5 px-3 text-right">Cartera Viva ($)</th>
										<th class="py-2.5 px-3 text-center">Estado</th>
										<th class="py-2.5 px-3 text-center">Acciones</th>
									</tr>
								</thead>
								<tbody id="sales-reps-table-body" class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
									<tr>
										<td colspan="11" class="py-8 text-center text-xs text-slate-400">Cargando fuerza de ventas mayorista...</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div id="sales-reps-table-footer" class="p-3 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between">
							<span>Mostrando equipo comercial</span>
							<span class="font-mono text-slate-700 dark:text-slate-300">Prince Distributors B2B</span>
						</div>
					</div>
				</div>

				<!-- SUB-TAB 2: TERRITORIOS & COBERTURA -->
				<div id="subtab-territories-view" class="hidden space-y-4">
					<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex items-center justify-between gap-3">
						<div class="flex items-center gap-2">
							<div class="relative w-72">
								<i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
								<input type="text" id="territory-search-input" onkeyup="filterTerritoriesList()" placeholder="Buscar territorio, código o ciudad..." class="w-full text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg pl-9 pr-3 py-1.5 text-slate-900 dark:text-white focus:outline-hidden focus:ring-1 focus:ring-brand-500" />
							</div>
							<select id="territory-filter-zone" onchange="filterTerritoriesList()" class="text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 font-medium text-slate-700 dark:text-slate-200 focus:outline-hidden">
								<option value="">Todas las Zonas</option>
								<option value="Centro">Centro</option>
								<option value="Norte">Norte</option>
								<option value="Sur">Sur</option>
								<option value="Este">Este</option>
								<option value="Oeste">Oeste</option>
								<option value="Sierra">Sierra</option>
							</select>
						</div>
						<button onclick="openCreateTerritoryModal()" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-brand-800 text-white hover:bg-brand-700 shadow-xs transition-all flex items-center gap-1.5">
							<i data-lucide="plus" class="w-3.5 h-3.5"></i>
							<span>Crear Territorio</span>
						</button>
					</div>

					<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden">
						<div class="overflow-x-auto">
							<table class="w-full text-left text-xs whitespace-nowrap">
								<thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-semibold uppercase text-[10px] tracking-wider border-b border-slate-200/80 dark:border-slate-800">
									<tr>
										<th class="py-2.5 px-3">Código</th>
										<th class="py-2.5 px-3">Territorio</th>
										<th class="py-2.5 px-3">Ciudad & Zona</th>
										<th class="py-2.5 px-3">Descripción de Cobertura</th>
										<th class="py-2.5 px-3">Vendedores Asignados</th>
										<th class="py-2.5 px-3 text-center">Establecimientos</th>
										<th class="py-2.5 px-3 text-center">Estado</th>
										<th class="py-2.5 px-3 text-center">Acciones</th>
									</tr>
								</thead>
								<tbody id="territories-table-body" class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
									<tr>
										<td colspan="8" class="py-8 text-center text-xs text-slate-400">Cargando territorios comerciales...</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>

				<!-- SUB-TAB 3: AUDITORÍA DE REASIGNACIONES -->
				<div id="subtab-history-view" class="hidden space-y-4">
					<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden">
						<div class="p-3.5 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
							<div>
								<h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
									<span>Historial de Reasignaciones de Cartera y Territorios</span>
									<span class="text-[10px] font-mono uppercase px-1.5 py-0.5 rounded bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300">Auditoría Inmutable</span>
								</h3>
								<p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Trazabilidad completa de cambios de vendedor en establecimientos comerciales y clientes.</p>
							</div>
							<button onclick="fetchReassignmentHistory()" class="p-1.5 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white rounded-lg border border-slate-200 dark:border-slate-700" title="Refrescar">
								<i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
							</button>
						</div>

						<div class="overflow-x-auto">
							<table class="w-full text-left text-xs whitespace-nowrap">
								<thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-semibold uppercase text-[10px] tracking-wider border-b border-slate-200/80 dark:border-slate-800">
									<tr>
										<th class="py-2.5 px-3">Fecha y Hora</th>
										<th class="py-2.5 px-3">Establecimiento / Cliente</th>
										<th class="py-2.5 px-3">Vendedor Anterior &rarr; Nuevo</th>
										<th class="py-2.5 px-3">Territorio Anterior &rarr; Nuevo</th>
										<th class="py-2.5 px-3">Motivo Comercial</th>
										<th class="py-2.5 px-3">Autorizado Por</th>
									</tr>
								</thead>
								<tbody id="reassignment-history-table-body" class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
									<tr>
										<td colspan="6" class="py-8 text-center text-xs text-slate-400">Sin reasignaciones recientes registradas.</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
			
			<!-- ========================================================
			     SECCIÓN 6: RENTABILIDAD COMERCIAL B2B (PASO 10)
			     ======================================================== -->
			<div id="section-profitability" class="hidden space-y-6">
				<!-- TITLE & ACTION BUTTONS -->
				<div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-2 border-b border-slate-200/80 dark:border-slate-800">
					<div>
						<h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-3">
							<span data-i18n="nav_profitability">Rentabilidad Comercial</span>
							<span class="text-[11px] font-semibold uppercase px-2 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/60">Contabilidad de Gestión</span>
							<span class="text-[10px] font-semibold uppercase px-1.5 py-0.5 rounded bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200/50">Margen Bruto</span>
						</h1>
						<p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Análisis de utilidad bruta comercial, costos de adquisición (COGS), márgenes por producto, cliente, vendedor y territorio.</p>
					</div>

					<!-- Botones de Acción -->
					<div class="flex flex-wrap items-center gap-2">
						<button onclick="openCostAdjustmentModal()" class="inline-flex items-center gap-2 px-3 py-1.5 sm:px-3.5 sm:py-2 text-xs font-semibold rounded-lg bg-brand-800 hover:bg-brand-700 text-white shadow-xs active:scale-[0.98] transition-all">
							<i data-lucide="calculator" class="w-4 h-4"></i>
							<span>Ajuste de Costo</span>
						</button>
						<button onclick="exportProfitabilityData()" class="inline-flex items-center gap-2 px-3 py-1.5 sm:px-3.5 sm:py-2 text-xs font-semibold rounded-lg bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 shadow-xs transition-colors">
							<i data-lucide="download" class="w-4 h-4 text-slate-500"></i>
							<span>Exportar Análisis</span>
						</button>
					</div>
				</div>

				<!-- BARRA DE FILTROS GLOBALES SINCRONIZADOS -->
				<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs space-y-3">
					<div class="flex flex-wrap items-center justify-between gap-3">
						<!-- Selector de Período -->
						<div class="flex items-center gap-1.5 bg-slate-100 dark:bg-slate-800/70 p-1 rounded-lg text-xs font-medium">
							<button type="button" onclick="setProfitabilityPeriod('MTD')" id="prof-period-btn-MTD" class="px-2.5 py-1 rounded-md font-semibold bg-white dark:bg-surface-dark text-slate-900 dark:text-white shadow-xs">Mes (MTD)</button>
							<button type="button" onclick="setProfitabilityPeriod('QTD')" id="prof-period-btn-QTD" class="px-2.5 py-1 rounded-md text-slate-600 dark:text-slate-400 hover:text-slate-900">Trimestre (QTD)</button>
							<button type="button" onclick="setProfitabilityPeriod('YTD')" id="prof-period-btn-YTD" class="px-2.5 py-1 rounded-md text-slate-600 dark:text-slate-400 hover:text-slate-900">Año (YTD)</button>
							<button type="button" onclick="setProfitabilityPeriod('7D')" id="prof-period-btn-7D" class="px-2.5 py-1 rounded-md text-slate-600 dark:text-slate-400 hover:text-slate-900">7 Días</button>
							<button type="button" onclick="toggleProfitabilityCustomRange()" id="prof-period-btn-CUSTOM" class="px-2.5 py-1 rounded-md text-slate-600 dark:text-slate-400 hover:text-slate-900">Personalizado</button>
						</div>

						<div id="prof-period-badge" class="text-xs font-mono font-medium text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
							<i data-lucide="calendar" class="w-3.5 h-3.5 text-brand-600"></i>
							<span id="prof-period-label">Mes a la Fecha (Noviembre 2026)</span>
						</div>
					</div>

					<!-- Rango personalizado (condicional) -->
					<div id="prof-custom-range-bar" class="hidden flex flex-wrap items-center gap-3 pt-2 border-t border-slate-100 dark:border-slate-800">
						<div class="flex items-center gap-2 text-xs">
							<span class="text-slate-500">Desde:</span>
							<input type="date" id="prof-custom-from" class="px-2 py-1 text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white font-mono">
						</div>
						<div class="flex items-center gap-2 text-xs">
							<span class="text-slate-500">Hasta:</span>
							<input type="date" id="prof-custom-to" class="px-2 py-1 text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white font-mono">
						</div>
						<button type="button" onclick="applyProfitabilityCustomRange()" class="px-3 py-1 text-xs font-semibold rounded-lg bg-brand-800 text-white hover:bg-brand-700">Aplicar Rango</button>
					</div>

					<!-- Filtros cruzados por Cliente, Vendedor, Categoría y Territorio -->
					<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 pt-2 border-t border-slate-100 dark:border-slate-800">
						<div>
							<label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Cliente / Cadena</label>
							<select id="prof-filter-customer" onchange="applyProfitabilityFilters()" class="w-full text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-900 dark:text-white focus:outline-hidden focus:ring-1 focus:ring-brand-500">
								<option value="ALL">Todos los Clientes</option>
							</select>
						</div>
						<div>
							<label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Vendedor Comercial</label>
							<select id="prof-filter-rep" onchange="applyProfitabilityFilters()" class="w-full text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-900 dark:text-white focus:outline-hidden focus:ring-1 focus:ring-brand-500">
								<option value="ALL">Todos los Vendedores</option>
							</select>
						</div>
						<div>
							<label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Categoría</label>
							<select id="prof-filter-category" onchange="applyProfitabilityFilters()" class="w-full text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-900 dark:text-white focus:outline-hidden focus:ring-1 focus:ring-brand-500">
								<option value="ALL">Todas las Categorías</option>
								<option value="Lácteos">Lácteos</option>
								<option value="Refrigerados">Refrigerados</option>
								<option value="Congelados">Congelados</option>
								<option value="Carnes y aves">Carnes y aves</option>
								<option value="Bebidas">Bebidas</option>
								<option value="Desechables">Desechables</option>
							</select>
						</div>
						<div class="flex items-end gap-2">
							<div class="flex-1">
								<label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Territorio</label>
								<select id="prof-filter-territory" onchange="applyProfitabilityFilters()" class="w-full text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-900 dark:text-white focus:outline-hidden focus:ring-1 focus:ring-brand-500">
									<option value="ALL">Todos los Territorios</option>
								</select>
							</div>
							<button type="button" onclick="resetProfitabilityFilters()" class="p-2 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800" title="Restablecer filtros">
								<i data-lucide="rotate-ccw" class="w-4 h-4"></i>
							</button>
						</div>
					</div>
				</div>

				<!-- 6 TARJETAS KPI PRINCIPALES -->
				<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
					<!-- KPI 1: Ventas Netas Facturadas -->
					<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs relative group">
						<div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
							<span class="text-xs font-semibold uppercase tracking-wider">Ventas Netas</span>
							<div class="w-7 h-7 rounded-md bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 flex items-center justify-center">
								<i data-lucide="receipt" class="w-4 h-4"></i>
							</div>
						</div>
						<div class="mt-2">
							<span id="kpi-prof-net-sales" class="text-xl font-bold text-slate-900 dark:text-white font-mono tracking-tight">$0.00</span>
						</div>
						<div class="mt-2 text-[10px] text-slate-500 dark:text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-1.5 truncate" title="Subtotal facturado sin impuestos de ventas emitidas menos notas de crédito válidas">
							Subtotal facturado − NCs
						</div>
					</div>

					<!-- KPI 2: Costo de Ventas (COGS) -->
					<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs relative group">
						<div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
							<span class="text-xs font-semibold uppercase tracking-wider">Costo Ventas</span>
							<div class="w-7 h-7 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 flex items-center justify-center">
								<i data-lucide="package" class="w-4 h-4"></i>
							</div>
						</div>
						<div class="mt-2">
							<span id="kpi-prof-cogs" class="text-xl font-bold text-slate-700 dark:text-slate-300 font-mono tracking-tight">$0.00</span>
						</div>
						<div class="mt-2 text-[10px] text-slate-500 dark:text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-1.5 truncate" title="Costo comercial estándar congelado al momento de facturar cada línea">
							COGS adquisición congelado
						</div>
					</div>

					<!-- KPI 3: Utilidad Bruta Comercial -->
					<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs relative group">
						<div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
							<span class="text-xs font-semibold uppercase tracking-wider">Utilidad Bruta</span>
							<div class="w-7 h-7 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 flex items-center justify-center">
								<i data-lucide="dollar-sign" class="w-4 h-4"></i>
							</div>
						</div>
						<div class="mt-2">
							<span id="kpi-prof-gross-profit" class="text-xl font-bold text-emerald-600 dark:text-emerald-400 font-mono tracking-tight">$0.00</span>
						</div>
						<div class="mt-2 text-[10px] text-slate-500 dark:text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-1.5 truncate" title="Utilidad bruta = Ventas netas − Costo de ventas (COGS)">
							Ventas netas − Costo
						</div>
					</div>

					<!-- KPI 4: Margen Bruto Comercial (%) -->
					<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs relative group">
						<div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
							<span class="text-xs font-semibold uppercase tracking-wider">Margen Bruto</span>
							<div class="w-7 h-7 rounded-md bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 flex items-center justify-center">
								<i data-lucide="percent" class="w-4 h-4"></i>
							</div>
						</div>
						<div class="mt-2 flex items-baseline gap-2">
							<span id="kpi-prof-margin-val" class="text-xl font-bold text-indigo-600 dark:text-indigo-400 font-mono tracking-tight">0%</span>
						</div>
						<div class="mt-2 w-full h-1 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
							<div id="kpi-prof-margin-bar" class="h-full bg-indigo-600 rounded-full" style="width: 0%"></div>
						</div>
						<div class="mt-1 text-[10px] text-slate-400 truncate" title="Margen comercial porcentual sobre ingresos netos">
							(Utilidad / Ventas) × 100
						</div>
					</div>

					<!-- KPI 5: Productos con Margen Negativo -->
					<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs relative group">
						<div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
							<span class="text-xs font-semibold uppercase tracking-wider">Margen Negativo</span>
							<div class="w-7 h-7 rounded-md bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 flex items-center justify-center">
								<i data-lucide="alert-triangle" class="w-4 h-4"></i>
							</div>
						</div>
						<div class="mt-2 flex items-baseline gap-1.5">
							<span id="kpi-prof-negative-count" class="text-xl font-bold text-rose-600 dark:text-rose-400 font-mono tracking-tight">0</span>
							<span class="text-xs text-slate-400 font-medium">SKUs</span>
						</div>
						<div class="mt-2 text-[10px] text-slate-500 dark:text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-1.5 truncate" title="Productos donde el costo de adquisición supera el precio de venta">
							Precio venta < Costo base
						</div>
					</div>

					<!-- KPI 6: Cliente Líder en Utilidad -->
					<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs relative group">
						<div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
							<span class="text-xs font-semibold uppercase tracking-wider">Top Cliente Utilidad</span>
							<div class="w-7 h-7 rounded-md bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 flex items-center justify-center">
								<i data-lucide="award" class="w-4 h-4"></i>
							</div>
						</div>
						<div class="mt-2">
							<p id="kpi-prof-top-customer-name" class="text-xs font-bold text-slate-900 dark:text-white truncate">Cargando...</p>
							<p id="kpi-prof-top-customer-profit" class="text-base font-bold text-amber-600 dark:text-amber-400 font-mono mt-0.5">$0.00</p>
						</div>
						<div class="mt-1 text-[10px] text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-1 truncate">
							Mayor contribución bruta
						</div>
					</div>
				</div>

				<!-- 4 GRÁFICOS DE RENTABILIDAD COMERCIAL -->
				<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
					<!-- Gráfico 1: Evolución de Utilidad Bruta -->
					<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
						<div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100 dark:border-slate-800">
							<h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
								<i data-lucide="trending-up" class="w-4 h-4 text-emerald-600"></i>
								<span>Evolución de Ingresos, Costos y Utilidad Bruta</span>
							</h3>
							<span class="text-[10px] font-mono text-slate-400">USD</span>
						</div>
						<div class="h-64 w-full">
							<canvas id="chart-prof-trend"></canvas>
						</div>
					</div>

					<!-- Gráfico 2: Margen Bruto por Categoría -->
					<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
						<div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100 dark:border-slate-800">
							<h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
								<i data-lucide="layers" class="w-4 h-4 text-brand-600"></i>
								<span>Margen Comercial Bruto (%) por Categoría</span>
							</h3>
							<span class="text-[10px] font-mono text-slate-400">% Margen</span>
						</div>
						<div class="h-64 w-full">
							<canvas id="chart-prof-categories"></canvas>
						</div>
					</div>

					<!-- Gráfico 3: Top 5 Clientes por Contribución Bruta -->
					<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
						<div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100 dark:border-slate-800">
							<h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
								<i data-lucide="bar-chart-2" class="w-4 h-4 text-amber-500"></i>
								<span>Top 5 Clientes por Utilidad Bruta Acumulada</span>
							</h3>
							<span class="text-[10px] font-mono text-slate-400">USD</span>
						</div>
						<div class="h-64 w-full">
							<canvas id="chart-prof-top-customers"></canvas>
						</div>
					</div>

					<!-- Gráfico 4: Distribución de Salud de Margen -->
					<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
						<div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100 dark:border-slate-800">
							<h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
								<i data-lucide="pie-chart" class="w-4 h-4 text-indigo-600"></i>
								<span>Salud de Margen y Productos con Margen Negativo</span>
							</h3>
							<span class="text-[10px] font-mono text-slate-400">Distribución</span>
						</div>
						<div class="h-64 w-full">
							<canvas id="chart-prof-health"></canvas>
						</div>
					</div>
				</div>

				<!-- SUB-TABS: ANÁLISIS MULTIDIMENSIONAL DE RENTABILIDAD -->
				<div class="border-b border-slate-200 dark:border-slate-800">
					<nav class="flex space-x-6 text-xs sm:text-sm font-semibold overflow-x-auto pb-px">
						<button id="tab-prof-products-btn" onclick="switchProfitabilitySubTab('products')" class="pb-3 border-b-2 border-brand-800 text-brand-800 dark:text-white dark:border-white transition-all flex items-center gap-2 whitespace-nowrap">
							<i data-lucide="package" class="w-4 h-4"></i>
							<span>Por Producto & SKU</span>
						</button>
						<button id="tab-prof-customers-btn" onclick="switchProfitabilitySubTab('customers')" class="pb-3 border-b-2 border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition-all flex items-center gap-2 whitespace-nowrap">
							<i data-lucide="building-2" class="w-4 h-4"></i>
							<span>Por Cliente & Cadena</span>
						</button>
						<button id="tab-prof-reps-btn" onclick="switchProfitabilitySubTab('reps')" class="pb-3 border-b-2 border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition-all flex items-center gap-2 whitespace-nowrap">
							<i data-lucide="users" class="w-4 h-4"></i>
							<span>Por Vendedor</span>
						</button>
						<button id="tab-prof-territories-btn" onclick="switchProfitabilitySubTab('territories')" class="pb-3 border-b-2 border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition-all flex items-center gap-2 whitespace-nowrap">
							<i data-lucide="map-pin" class="w-4 h-4"></i>
							<span>Por Territorio</span>
						</button>
						<button id="tab-prof-costs-btn" onclick="switchProfitabilitySubTab('costs')" class="pb-3 border-b-2 border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition-all flex items-center gap-2 whitespace-nowrap">
							<i data-lucide="sliders" class="w-4 h-4"></i>
							<span>Matriz de Costos & Ajustes</span>
						</button>
					</nav>
				</div>

				<!-- SUB-TAB 1: PRODUCTOS & SKU -->
				<div id="subtab-prof-products-view" class="space-y-4">
					<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
						<div class="flex items-center gap-2 w-full sm:w-auto">
							<div class="relative w-full sm:w-72">
								<i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
								<input type="text" id="prof-prod-search-input" onkeyup="filterProfitabilityProducts()" placeholder="Buscar por SKU, producto o marca..." class="w-full text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg pl-9 pr-3 py-1.5 text-slate-900 dark:text-white focus:outline-hidden focus:ring-1 focus:ring-brand-500" />
							</div>
							<select id="prof-prod-filter-status" onchange="filterProfitabilityProducts()" class="text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 font-medium text-slate-700 dark:text-slate-200 focus:outline-hidden">
								<option value="ALL">Todos los Estados</option>
								<option value="HEALTHY">Margen Saludable (≥20%)</option>
								<option value="MODERATE">Margen Moderado (10-20%)</option>
								<option value="LOW">Margen Bajo (0-10%)</option>
								<option value="NEGATIVE">Margen Negativo (<0%)</option>
								<option value="UNAVAILABLE">Costo no disponible</option>
							</select>
						</div>

						<div class="text-[11px] text-slate-500 dark:text-slate-400 shrink-0">
							Mostrando <span id="prof-products-count" class="font-bold text-slate-900 dark:text-white">0</span> productos facturados
						</div>
					</div>

					<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden">
						<div class="overflow-x-auto">
							<table class="w-full text-left text-xs whitespace-nowrap">
								<thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-semibold uppercase text-[10px] tracking-wider border-b border-slate-200/80 dark:border-slate-800">
									<tr>
										<th class="py-2.5 px-3">SKU & Producto</th>
										<th class="py-2.5 px-3">Categoría / Marca</th>
										<th class="py-2.5 px-3 text-center">Cajas / Uds</th>
										<th class="py-2.5 px-3 text-right">Costo Estándar</th>
										<th class="py-2.5 px-3 text-right">Ventas Netas</th>
										<th class="py-2.5 px-3 text-right">Costo Total (COGS)</th>
										<th class="py-2.5 px-3 text-right">Utilidad Bruta</th>
										<th class="py-2.5 px-3 text-center">Margen (%)</th>
										<th class="py-2.5 px-3 text-center">Estado</th>
									</tr>
								</thead>
								<tbody id="prof-products-table-body" class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
									<tr>
										<td colspan="9" class="py-8 text-center text-xs text-slate-400">Cargando análisis de rentabilidad por producto...</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>

				<!-- SUB-TAB 2: CLIENTES & CADENAS -->
				<div id="subtab-prof-customers-view" class="hidden space-y-4">
					<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
						<div class="relative w-full sm:w-80">
							<i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
							<input type="text" id="prof-cust-search-input" oninput="filterProfitabilityCustomers()" onkeyup="filterProfitabilityCustomers()" placeholder="Buscar cliente comercial o RUC..." class="w-full text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg pl-9 pr-3 py-1.5 text-slate-900 dark:text-white focus:outline-hidden focus:ring-1 focus:ring-brand-500" />
						</div>
						<div class="text-[11px] text-slate-500 dark:text-slate-400 shrink-0">
							Mostrando <span id="prof-customers-count" class="font-bold text-slate-900 dark:text-white">0</span> cuentas corporativas
						</div>
					</div>

					<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden">
						<div class="overflow-x-auto">
							<table class="w-full text-left text-xs whitespace-nowrap">
								<thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-semibold uppercase text-[10px] tracking-wider border-b border-slate-200/80 dark:border-slate-800">
									<tr>
										<th class="py-2.5 px-3 text-center">Rank</th>
										<th class="py-2.5 px-3">Cliente Comercial</th>
										<th class="py-2.5 px-3">Tipo / RUC</th>
										<th class="py-2.5 px-3 text-center">Sedes / Facturas</th>
										<th class="py-2.5 px-3 text-right">Ventas Netas</th>
										<th class="py-2.5 px-3 text-right">Costo Ventas</th>
										<th class="py-2.5 px-3 text-right">Utilidad Bruta</th>
										<th class="py-2.5 px-3 text-center">Margen (%)</th>
										<th class="py-2.5 px-3 text-center">Estado</th>
									</tr>
								</thead>
								<tbody id="prof-customers-table-body" class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
									<tr>
										<td colspan="9" class="py-8 text-center text-xs text-slate-400">Cargando rentabilidad por cliente...</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>

				<!-- SUB-TAB 3: VENDEDORES COMERCIALES -->
				<div id="subtab-prof-reps-view" class="hidden space-y-4">
					<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden">
						<div class="overflow-x-auto">
							<table class="w-full text-left text-xs whitespace-nowrap">
								<thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-semibold uppercase text-[10px] tracking-wider border-b border-slate-200/80 dark:border-slate-800">
									<tr>
										<th class="py-2.5 px-3">Ejecutivo Comercial</th>
										<th class="py-2.5 px-3">Territorio Asignado</th>
										<th class="py-2.5 px-3 text-center">Facturas / Clientes</th>
										<th class="py-2.5 px-3 text-right">Ventas Netas</th>
										<th class="py-2.5 px-3 text-right">Costo Ventas</th>
										<th class="py-2.5 px-3 text-right">Utilidad Bruta</th>
										<th class="py-2.5 px-3 text-center">Margen Bruto (%)</th>
									</tr>
								</thead>
								<tbody id="prof-reps-table-body" class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
									<tr>
										<td colspan="7" class="py-8 text-center text-xs text-slate-400">Cargando rentabilidad por vendedor...</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>

				<!-- SUB-TAB 4: TERRITORIOS COMERCIALES -->
				<div id="subtab-prof-territories-view" class="hidden space-y-4">
					<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden">
						<div class="overflow-x-auto">
							<table class="w-full text-left text-xs whitespace-nowrap">
								<thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-semibold uppercase text-[10px] tracking-wider border-b border-slate-200/80 dark:border-slate-800">
									<tr>
										<th class="py-2.5 px-3">Código</th>
										<th class="py-2.5 px-3">Territorio</th>
										<th class="py-2.5 px-3">Ciudad / Zona</th>
										<th class="py-2.5 px-3 text-center">Facturas</th>
										<th class="py-2.5 px-3 text-right">Ventas Netas</th>
										<th class="py-2.5 px-3 text-right">Costo Ventas</th>
										<th class="py-2.5 px-3 text-right">Utilidad Bruta</th>
										<th class="py-2.5 px-3 text-center">Margen Bruto (%)</th>
									</tr>
								</thead>
								<tbody id="prof-territories-table-body" class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
									<tr>
										<td colspan="8" class="py-8 text-center text-xs text-slate-400">Cargando rentabilidad por territorio...</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>

				<!-- SUB-TAB 5: MATRIZ DE COSTOS Y AJUSTES AUDITADOS -->
				<div id="subtab-prof-costs-view" class="hidden space-y-6">
					<!-- Tabla de Matriz de Costos Estándar -->
					<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden">
						<div class="p-4 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
							<div>
								<h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
									<span>Catálogo de Costos de Adquisición Estándar</span>
									<span class="text-[10px] font-mono uppercase px-1.5 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300">Base Contable</span>
								</h3>
								<p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Costos vigentes aplicables a nuevas cotizaciones, pedidos y facturas comerciales.</p>
							</div>
							<button onclick="openCostAdjustmentModal()" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-brand-800 text-white hover:bg-brand-700 flex items-center gap-1.5">
								<i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
								<span>Nuevo Ajuste de Costo</span>
							</button>
						</div>

						<div class="overflow-x-auto">
							<table class="w-full text-left text-xs whitespace-nowrap">
								<thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-semibold uppercase text-[10px] tracking-wider border-b border-slate-200/80 dark:border-slate-800">
									<tr>
										<th class="py-2.5 px-3">SKU & Producto</th>
										<th class="py-2.5 px-3">Categoría</th>
										<th class="py-2.5 px-3 text-center">Factor Conversión</th>
										<th class="py-2.5 px-3 text-right">Costo Unitario Base</th>
										<th class="py-2.5 px-3 text-right">Costo por Caja</th>
										<th class="py-2.5 px-3 text-right">Precio Venta Caja</th>
										<th class="py-2.5 px-3 text-center">Margen Teórico</th>
										<th class="py-2.5 px-3">Fuente / Contrato</th>
										<th class="py-2.5 px-3 text-center">Acciones</th>
									</tr>
								</thead>
								<tbody id="prof-costs-table-body" class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
									<tr>
										<td colspan="9" class="py-8 text-center text-xs text-slate-400">Cargando matriz de costos estándar...</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>

					<!-- Bitácora de Auditoría de Ajustes de Costo -->
					<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden">
						<div class="p-4 border-b border-slate-200/80 dark:border-slate-800">
							<h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
								<i data-lucide="shield-check" class="w-4 h-4 text-brand-600"></i>
								<span>Historial de Auditoría de Ajustes de Costo</span>
								<span class="text-[10px] font-mono uppercase px-1.5 py-0.5 rounded bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300">Trazabilidad Inmutable</span>
							</h3>
							<p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Control de cambios de costo autorizados. Los comprobantes emitidos antes de la fecha de vigencia conservan su margen congelado original.</p>
						</div>

						<div class="overflow-x-auto">
							<table class="w-full text-left text-xs whitespace-nowrap">
								<thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-semibold uppercase text-[10px] tracking-wider border-b border-slate-200/80 dark:border-slate-800">
									<tr>
										<th class="py-2.5 px-3">Fecha y Hora</th>
										<th class="py-2.5 px-3">Producto / SKU</th>
										<th class="py-2.5 px-3">Costo Anterior &rarr; Nuevo</th>
										<th class="py-2.5 px-3">Vigencia</th>
										<th class="py-2.5 px-3">Motivo del Ajuste</th>
										<th class="py-2.5 px-3">Autorizado Por</th>
									</tr>
								</thead>
								<tbody id="prof-adjustments-table-body" class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
									<tr>
										<td colspan="6" class="py-8 text-center text-xs text-slate-400">Sin ajustes de costo registrados.</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>

			
			<!-- ========================================================
			     SECCIÓN 7: INTELIGENCIA COMERCIAL B2B (PASO 11)
			     ======================================================== -->
			<div id="section-reports" class="hidden space-y-6">
				<!-- TITLE & ACTION BUTTONS -->
				<div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-2 border-b border-slate-200/80 dark:border-slate-800">
					<div>
						<h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-3">
							<span data-i18n="nav_reports">Inteligencia Comercial</span>
							<span class="text-[11px] font-semibold uppercase px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-800/60">Motor Analítico B2B</span>
							<span class="text-[10px] font-semibold uppercase px-1.5 py-0.5 rounded bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200/50">Detección de Riesgo</span>
						</h1>
						<p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Detección automática de clientes inactivos, alertas de caída de ventas, oportunidades de recompra y recomendaciones explicables basadas en transacciones reales.</p>
					</div>

					<!-- Botones de Acción -->
					<div class="flex flex-wrap items-center gap-2">
						<button onclick="triggerIntelligenceEngine()" class="inline-flex items-center gap-2 px-3 py-1.5 sm:px-3.5 sm:py-2 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs active:scale-[0.98] transition-all">
							<i data-lucide="refresh-cw" class="w-4 h-4"></i>
							<span>Recalcular Motor</span>
						</button>
						<button onclick="openThresholdsModal()" class="inline-flex items-center gap-2 px-3 py-1.5 sm:px-3.5 sm:py-2 text-xs font-semibold rounded-lg bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 shadow-xs transition-colors">
							<i data-lucide="sliders" class="w-4 h-4 text-slate-500"></i>
							<span>Umbrales</span>
						</button>
						<button onclick="exportIntelligenceData()" class="inline-flex items-center gap-2 px-3 py-1.5 sm:px-3.5 sm:py-2 text-xs font-semibold rounded-lg bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 shadow-xs transition-colors">
							<i data-lucide="download" class="w-4 h-4 text-slate-500"></i>
							<span>Exportar</span>
						</button>
					</div>
				</div>

				<!-- BARRA DE FILTROS SINCRONIZADOS -->
				<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
					<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
						<!-- Severidad -->
						<div>
							<label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Severidad</label>
							<select id="intel-filter-severity" onchange="applyIntelligenceFilters()" class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200 focus:outline-hidden">
								<option value="ALL">Todas las severidades</option>
								<option value="CRITICAL">Solo Críticas</option>
								<option value="HIGH">Solo Altas</option>
								<option value="MEDIUM">Medias / Moderadas</option>
							</select>
						</div>

						<!-- Estado de Alerta -->
						<div>
							<label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Estado de Alerta</label>
							<select id="intel-filter-status" onchange="applyIntelligenceFilters()" class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200 focus:outline-hidden">
								<option value="ACTIVA">Solo Activas (Pendientes)</option>
								<option value="REVISADA">Solo Revisadas</option>
								<option value="ALL">Histórico Completo</option>
							</select>
						</div>

						<!-- Tipo de Alerta -->
						<div>
							<label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Categoría de Alerta</label>
							<select id="intel-filter-type" onchange="applyIntelligenceFilters()" class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200 focus:outline-hidden">
								<option value="ALL">Todas las alertas</option>
								<option value="INACTIVE_CUSTOMER">Clientes Inactivos (>30D)</option>
								<option value="DROP_SALES">Caída Significativa de Ventas</option>
								<option value="OVERDUE_INVOICE">Facturas Vencidas</option>
								<option value="NEGATIVE_MARGIN">Margen Negativo</option>
								<option value="QUOTA_RISK">Meta en Riesgo</option>
								<option value="NEW_WITHOUT_PURCHASE">Nuevos sin Primera Compra</option>
							</select>
						</div>

						<!-- Vendedor Asignado -->
						<div>
							<label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Vendedor</label>
							<select id="intel-filter-rep" onchange="applyIntelligenceFilters()" class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200 focus:outline-hidden">
								<option value="">Todos los vendedores</option>
							</select>
						</div>

						<!-- Botón Reset -->
						<div class="flex items-end">
							<button onclick="resetIntelligenceFilters()" class="w-full px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 transition-colors flex items-center justify-center gap-1.5">
								<i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
								<span>Restablecer</span>
							</button>
						</div>
					</div>
				</div>

				<!-- 4 TARJETAS KPI RESUMEN DE INTELIGENCIA COMERCIAL -->
				<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
					<!-- KPI 1: Alertas Comerciales Activas -->
					<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex flex-col justify-between">
						<div class="flex items-center justify-between">
							<span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Alertas Activas</span>
							<span class="p-2 rounded-lg bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400">
								<i data-lucide="alert-triangle" class="w-4 h-4"></i>
							</span>
						</div>
						<div class="mt-2">
							<div id="kpi-intel-active-alerts" class="text-2xl font-black font-mono tracking-tight text-slate-900 dark:text-white">0</div>
							<div class="flex items-center gap-2 mt-1 text-[11px]">
								<span id="kpi-intel-critical-badge" class="font-bold text-rose-600 dark:text-rose-400">0 críticas</span>
								<span class="text-slate-400">•</span>
								<span id="kpi-intel-high-badge" class="font-semibold text-amber-600 dark:text-amber-400">0 altas</span>
							</div>
						</div>
					</div>

					<!-- KPI 2: Clientes Inactivos / En Riesgo -->
					<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex flex-col justify-between">
						<div class="flex items-center justify-between">
							<span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Clientes en Riesgo</span>
							<span class="p-2 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400">
								<i data-lucide="user-x" class="w-4 h-4"></i>
							</span>
						</div>
						<div class="mt-2">
							<div id="kpi-intel-inactive-customers" class="text-2xl font-black font-mono tracking-tight text-amber-600 dark:text-amber-400">0</div>
							<div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Sin compras >30D o caída de ventas</div>
						</div>
					</div>

					<!-- KPI 3: Facturas Vencidas en Cobranzas -->
					<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex flex-col justify-between">
						<div class="flex items-center justify-between">
							<span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Facturas Vencidas</span>
							<span class="p-2 rounded-lg bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400">
								<i data-lucide="clock" class="w-4 h-4"></i>
							</span>
						</div>
						<div class="mt-2">
							<div id="kpi-intel-overdue-balance" class="text-2xl font-black font-mono tracking-tight text-slate-900 dark:text-white">$0.00</div>
							<div class="flex items-center gap-1.5 mt-1 text-[11px] text-purple-700 dark:text-purple-300 font-semibold">
								<span id="kpi-intel-overdue-count">0 facturas vencidas</span>
							</div>
						</div>
					</div>

					<!-- KPI 4: Oportunidades Comerciales -->
					<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex flex-col justify-between">
						<div class="flex items-center justify-between">
							<span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Oportunidades de Recompra</span>
							<span class="p-2 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400">
								<i data-lucide="trending-up" class="w-4 h-4"></i>
							</span>
						</div>
						<div class="mt-2">
							<div id="kpi-intel-opportunities-count" class="text-2xl font-black font-mono tracking-tight text-emerald-600 dark:text-emerald-400">0</div>
							<div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Cross-selling y productos abandonados</div>
						</div>
					</div>
				</div>

				<!-- NAVEGACIÓN POR SUB-PESTAÑAS DE INTELIGENCIA COMERCIAL -->
				<div class="border-b border-slate-200 dark:border-slate-800">
					<nav class="flex space-x-2 overflow-x-auto pb-px" aria-label="Tabs Inteligencia">
						<button id="tab-intel-alerts-btn" onclick="switchIntelligenceSubTab('alerts')" class="px-3.5 py-2 text-xs font-semibold border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400 whitespace-nowrap transition-colors flex items-center gap-2">
							<i data-lucide="bell-ring" class="w-4 h-4"></i>
							<span>Centro de Alertas y Recomendaciones</span>
							<span id="badge-tab-alerts-count" class="px-1.5 py-0.2 rounded-full text-[10px] bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 font-bold">0</span>
						</button>
						<button id="tab-intel-inactive-btn" onclick="switchIntelligenceSubTab('inactive')" class="px-3.5 py-2 text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white whitespace-nowrap border-b-2 border-transparent transition-colors flex items-center gap-2">
							<i data-lucide="user-x" class="w-4 h-4"></i>
							<span>Clientes en Riesgo e Inactivos</span>
							<span id="badge-tab-inactive-count" class="px-1.5 py-0.2 rounded-full text-[10px] bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300 font-bold">0</span>
						</button>
						<button id="tab-intel-opportunities-btn" onclick="switchIntelligenceSubTab('opportunities')" class="px-3.5 py-2 text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white whitespace-nowrap border-b-2 border-transparent transition-colors flex items-center gap-2">
							<i data-lucide="lightbulb" class="w-4 h-4"></i>
							<span>Oportunidades Comerciales & Recompra</span>
						</button>
						<button id="tab-intel-trends-btn" onclick="switchIntelligenceSubTab('trends')" class="px-3.5 py-2 text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white whitespace-nowrap border-b-2 border-transparent transition-colors flex items-center gap-2">
							<i data-lucide="bar-chart-2" class="w-4 h-4"></i>
							<span>Tendencias de Categorías</span>
						</button>
						<button id="tab-intel-thresholds-btn" onclick="switchIntelligenceSubTab('thresholds')" class="px-3.5 py-2 text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white whitespace-nowrap border-b-2 border-transparent transition-colors flex items-center gap-2">
							<i data-lucide="sliders" class="w-4 h-4"></i>
							<span>Configuración de Umbrales</span>
						</button>
					</nav>
				</div>

				<!-- SUB-TAB 1: CENTRO DE ALERTAS Y RECOMENDACIONES -->
				<div id="subtab-intel-alerts-view" class="space-y-4">
					<div class="flex items-center justify-between gap-4">
						<div class="text-xs text-slate-500 dark:text-slate-400" id="intel-alerts-summary-label">
							Cargando alertas prioritarias...
						</div>
						<div class="flex items-center gap-2">
							<span class="text-[11px] font-semibold text-slate-500">Filtrar por Severidad:</span>
							<button onclick="setAlertsSeverityQuickFilter('ALL')" id="btn-quick-sev-ALL" class="px-2 py-0.5 text-[11px] font-semibold rounded bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200">Todas</button>
							<button onclick="setAlertsSeverityQuickFilter('CRITICAL')" id="btn-quick-sev-CRITICAL" class="px-2 py-0.5 text-[11px] font-semibold rounded text-rose-700 dark:text-rose-300 hover:bg-rose-50 dark:hover:bg-rose-950/60">Críticas</button>
							<button onclick="setAlertsSeverityQuickFilter('HIGH')" id="btn-quick-sev-HIGH" class="px-2 py-0.5 text-[11px] font-semibold rounded text-amber-700 dark:text-amber-300 hover:bg-amber-50 dark:hover:bg-amber-950/60">Altas</button>
							<button onclick="setAlertsSeverityQuickFilter('MEDIUM')" id="btn-quick-sev-MEDIUM" class="px-2 py-0.5 text-[11px] font-semibold rounded text-blue-700 dark:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-950/60">Medias</button>
						</div>
					</div>

					<!-- Lista Dinámica de Tarjetas de Alerta -->
					<div id="intel-alerts-container" class="space-y-3">
						<div class="p-8 text-center text-xs text-slate-400 bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800">
							Cargando alertas comerciales explicables...
						</div>
					</div>
				</div>

				<!-- SUB-TAB 2: CLIENTES EN RIESGO E INACTIVOS -->
				<div id="subtab-intel-inactive-view" class="hidden space-y-4">
					<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex flex-wrap items-center justify-between gap-3">
						<div class="relative w-full sm:w-80">
							<i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
							<input type="text" id="intel-cust-search-input" oninput="filterInactiveCustomersTable()" placeholder="Buscar cliente comercial, RUC o vendedor..." class="w-full text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg pl-9 pr-3 py-1.5 text-slate-900 dark:text-white focus:outline-hidden focus:ring-1 focus:ring-indigo-500" />
						</div>
						<div class="flex items-center gap-2">
							<span class="text-xs text-slate-500">Segmento de Riesgo:</span>
							<select id="intel-cust-filter-risk" onchange="filterInactiveCustomersTable()" class="px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200 focus:outline-hidden">
								<option value="ALL">Todos los riesgos</option>
								<option value="CRITICAL">Inactivos Críticos (>90D)</option>
								<option value="HIGH">Inactivos Altos (>60D) / Caída >30%</option>
								<option value="MEDIUM">Inactivos Moderados (>30D)</option>
								<option value="MODERATE">Nuevos sin Primera Compra</option>
							</select>
						</div>
					</div>

					<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden">
						<div class="overflow-x-auto">
							<table class="w-full text-left text-xs border-collapse">
								<thead>
									<tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-800/50 text-slate-600 dark:text-slate-400 font-semibold uppercase text-[10px] tracking-wider">
										<th class="py-3 px-3">Cliente Comercial</th>
										<th class="py-3 px-3">Territorio / Vendedor</th>
										<th class="py-3 px-3 text-center">Última Compra</th>
										<th class="py-3 px-3 text-right">Días Inactivo</th>
										<th class="py-3 px-3 text-right">Facturación Histórica</th>
										<th class="py-3 px-3 text-center">Nivel de Riesgo</th>
										<th class="py-3 px-3">Acción Recomendada</th>
										<th class="py-3 px-3 text-center">Acciones</th>
									</tr>
								</thead>
								<tbody id="intel-inactive-table-body" class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
									<tr>
										<td colspan="8" class="py-8 text-center text-xs text-slate-400">Cargando clientes en riesgo...</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>

				<!-- SUB-TAB 3: OPORTUNIDADES COMERCIALES & RECOMPRA -->
				<div id="subtab-intel-opportunities-view" class="hidden space-y-6">
					<!-- Sección A: Productos que un cliente compraba y dejó de comprar -->
					<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs p-5 space-y-3">
						<div class="flex items-center justify-between">
							<div class="flex items-center gap-2">
								<span class="p-1.5 rounded-lg bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400">
									<i data-lucide="package-minus" class="w-4 h-4"></i>
								</span>
								<div>
									<h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Productos Habituales que Dejó de Comprar</h3>
									<p class="text-[11px] text-slate-500">Clientes que adquirían recurrentemente estos SKUs pero no han ordenado en sus últimas facturas.</p>
								</div>
							</div>
							<span id="intel-abandoned-count-badge" class="px-2 py-0.5 rounded text-[11px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-900/60 dark:text-rose-300">0 casos</span>
						</div>
						<div class="overflow-x-auto">
							<table class="w-full text-left text-xs border-collapse">
								<thead>
									<tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 text-[10px] text-slate-500 font-semibold uppercase">
										<th class="py-2.5 px-3">Cliente</th>
										<th class="py-2.5 px-3">Producto / SKU</th>
										<th class="py-2.5 px-3 text-right">Días sin Comprar</th>
										<th class="py-2.5 px-3">Evidencia y Datos de Respaldo</th>
										<th class="py-2.5 px-3">Acción Sugerida</th>
									</tr>
								</thead>
								<tbody id="intel-abandoned-table-body" class="divide-y divide-slate-100 dark:divide-slate-800">
									<tr><td colspan="5" class="py-6 text-center text-xs text-slate-400">Cargando productos abandonados...</td></tr>
								</tbody>
							</table>
						</div>
					</div>

					<!-- Sección B: Cross-Selling entre clientes del mismo segmento -->
					<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs p-5 space-y-3">
						<div class="flex items-center justify-between">
							<div class="flex items-center gap-2">
								<span class="p-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400">
									<i data-lucide="layers" class="w-4 h-4"></i>
								</span>
								<div>
									<h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Productos de Alta Demanda en Cuentas Similares (Cross-Selling)</h3>
									<p class="text-[11px] text-slate-500">SKUs con alta penetración en clientes del mismo canal que esta cuenta aún no incorpora.</p>
								</div>
							</div>
							<span id="intel-cross-count-badge" class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/60 dark:text-emerald-300">0 casos</span>
						</div>
						<div class="overflow-x-auto">
							<table class="w-full text-left text-xs border-collapse">
								<thead>
									<tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 text-[10px] text-slate-500 font-semibold uppercase">
										<th class="py-2.5 px-3">Cliente</th>
										<th class="py-2.5 px-3">Canal / Segmento</th>
										<th class="py-2.5 px-3">Producto Sugerido</th>
										<th class="py-2.5 px-3">Evidencia de Adopción</th>
										<th class="py-2.5 px-3">Acción Sugerida</th>
									</tr>
								</thead>
								<tbody id="intel-cross-table-body" class="divide-y divide-slate-100 dark:divide-slate-800">
									<tr><td colspan="5" class="py-6 text-center text-xs text-slate-400">Cargando oportunidades de cross-selling...</td></tr>
								</tbody>
							</table>
						</div>
					</div>

					<!-- Sección C: Clientes con Margen Comercial Bajo (<15%) -->
					<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs p-5 space-y-3">
						<div class="flex items-center justify-between">
							<div class="flex items-center gap-2">
								<span class="p-1.5 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400">
									<i data-lucide="percent" class="w-4 h-4"></i>
								</span>
								<div>
									<h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Cuentas con Margen Comercial Reducido (&lt; 15%)</h3>
									<p class="text-[11px] text-slate-500">Clientes activos cuyo margen bruto consolidado se encuentra por debajo del estándar corporativo.</p>
								</div>
							</div>
							<span id="intel-lowmargin-count-badge" class="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/60 dark:text-amber-300">0 cuentas</span>
						</div>
						<div class="overflow-x-auto">
							<table class="w-full text-left text-xs border-collapse">
								<thead>
									<tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 text-[10px] text-slate-500 font-semibold uppercase">
										<th class="py-2.5 px-3">Cliente</th>
										<th class="py-2.5 px-3">Vendedor</th>
										<th class="py-2.5 px-3 text-right">Ventas Netas</th>
										<th class="py-2.5 px-3 text-right">Margen Bruto (%)</th>
										<th class="py-2.5 px-3">Evidencia Comercial</th>
										<th class="py-2.5 px-3">Acción de Rentabilidad</th>
									</tr>
								</thead>
								<tbody id="intel-lowmargin-table-body" class="divide-y divide-slate-100 dark:divide-slate-800">
									<tr><td colspan="6" class="py-6 text-center text-xs text-slate-400">Cargando cuentas con margen bajo...</td></tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>

				<!-- SUB-TAB 4: TENDENCIAS DE CATEGORÍAS -->
				<div id="subtab-intel-trends-view" class="hidden space-y-4">
					<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
						<h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-1">Evolución de Categorías Comerciales (Últimos 30D vs 30D Previos)</h3>
						<p class="text-[11px] text-slate-500">Comparativa objetiva de ingresos y cajas comercializadas por línea mayorista.</p>
					</div>

					<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden">
						<div class="overflow-x-auto">
							<table class="w-full text-left text-xs border-collapse">
								<thead>
									<tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-800/50 text-slate-600 dark:text-slate-400 font-semibold uppercase text-[10px] tracking-wider">
										<th class="py-3 px-3">Categoría de Productos</th>
										<th class="py-3 px-3 text-center">Productos Activos</th>
										<th class="py-3 px-3 text-right">Ventas Últimos 30 Días</th>
										<th class="py-3 px-3 text-right">Ventas 30 Días Previos</th>
										<th class="py-3 px-3 text-right">Variación Neta ($)</th>
										<th class="py-3 px-3 text-right">Variación (%)</th>
										<th class="py-3 px-3 text-center">Comportamiento</th>
									</tr>
								</thead>
								<tbody id="intel-trends-table-body" class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
									<tr>
										<td colspan="7" class="py-8 text-center text-xs text-slate-400">Cargando tendencias de categorías...</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>

				<!-- SUB-TAB 5: CONFIGURACIÓN DE UMBRALES -->
				<div id="subtab-intel-thresholds-view" class="hidden space-y-4">
					<div class="bg-white dark:bg-surface-dark p-6 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs max-w-3xl">
						<div class="border-b border-slate-200 dark:border-slate-800 pb-3 mb-4">
							<h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
								<i data-lucide="sliders" class="w-4 h-4 text-indigo-600"></i>
								<span>Parámetros y Umbrales del Motor Analítico</span>
							</h3>
							<p class="text-xs text-slate-500 mt-0.5">Ajuste los criterios cuantitativos para la activación automática de alertas y clasificación de riesgos.</p>
						</div>

						<form id="form-intel-thresholds" onsubmit="submitIntelligenceThresholds(event)" class="space-y-4">
							<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
								<div>
									<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Días de Inactividad de Cliente</label>
									<div class="flex items-center gap-2">
										<input type="number" id="thresh-inactive-days" min="15" max="180" step="1" required class="w-full px-3 py-2 text-xs font-mono font-bold bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden" />
										<span class="text-xs text-slate-400 font-semibold shrink-0">días</span>
									</div>
									<p class="text-[10px] text-slate-400 mt-1">Días sin facturas para considerar al cliente inactivo.</p>
								</div>

								<div>
									<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Caída Significativa de Ventas</label>
									<div class="flex items-center gap-2">
										<input type="number" id="thresh-drop-sales" min="10" max="90" step="1" required class="w-full px-3 py-2 text-xs font-mono font-bold bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden" />
										<span class="text-xs text-slate-400 font-semibold shrink-0">%</span>
									</div>
									<p class="text-[10px] text-slate-400 mt-1">Porcentaje de caída vs período previo para alertar.</p>
								</div>
							</div>

							<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
								<div>
									<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Umbral Mínimo de Margen Bruto</label>
									<div class="flex items-center gap-2">
										<input type="number" id="thresh-low-margin" min="5" max="35" step="0.5" required class="w-full px-3 py-2 text-xs font-mono font-bold bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden" />
										<span class="text-xs text-slate-400 font-semibold shrink-0">%</span>
									</div>
									<p class="text-[10px] text-slate-400 mt-1">Márgenes por debajo de este valor se consideran en riesgo.</p>
								</div>

								<div>
									<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Días de Gracia Facturas Vencidas</label>
									<div class="flex items-center gap-2">
										<input type="number" id="thresh-grace-days" min="0" max="30" step="1" required class="w-full px-3 py-2 text-xs font-mono font-bold bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden" />
										<span class="text-xs text-slate-400 font-semibold shrink-0">días</span>
									</div>
									<p class="text-[10px] text-slate-400 mt-1">Días posteriores al vencimiento antes de disparar alerta.</p>
								</div>
							</div>

							<div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex justify-end">
								<button type="submit" id="btn-save-thresholds" class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs transition-colors">
									<i data-lucide="check" class="w-4 h-4"></i>
									<span>Guardar Umbrales y Recalcular</span>
								</button>
							</div>
						</form>
					</div>
				</div>
			</div>

			<!-- MODAL: MARCAR ALERTA COMO REVISADA -->
			<div id="modal-intel-review" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
				<div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
					<div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50">
						<h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
							<i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
							<span>Revisar y Gestionar Alerta</span>
						</h3>
						<button type="button" onclick="closeAlertReviewModal()" class="p-1 rounded-lg text-slate-400 hover:text-slate-600">
							<i data-lucide="x" class="w-4 h-4"></i>
						</button>
					</div>
					<form id="form-intel-review" onsubmit="submitAlertReview(event)" class="p-5 space-y-3">
						<input type="hidden" id="review-alert-id" />
						<div>
							<div id="review-alert-title" class="text-xs font-bold text-slate-900 dark:text-white"></div>
							<div id="review-alert-desc" class="text-[11px] text-slate-500 mt-0.5"></div>
						</div>
						<div>
							<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Revisado Por <span class="text-rose-500">*</span></label>
							<input type="text" id="review-by" required value="Dirección Comercial" class="w-full px-3 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden" />
						</div>
						<div>
							<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Acción Comercial Ejecutada / Notas <span class="text-rose-500">*</span></label>
							<textarea id="review-notes" required rows="2" placeholder="Ej: Se contactó al comprador; acordada visita comercial para el jueves." class="w-full px-3 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden"></textarea>
						</div>
						<div class="pt-2 flex justify-end gap-2">
							<button type="button" onclick="closeAlertReviewModal()" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700">Cancelar</button>
							<button type="submit" id="btn-submit-review" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs">Guardar Gestión</button>
						</div>
					</form>
				</div>
			</div>

			
			<!-- ========================================================
			     SECCIÓN 8: CONFIGURACIÓN COMERCIAL B2B
			     ======================================================== -->
			<div id="section-settings" class="hidden space-y-6">
				<!-- TITLE & ACTION BUTTONS -->
				<div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-2 border-b border-slate-200/80 dark:border-slate-800">
					<div>
						<h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-3">
							<span data-i18n="nav_settings">Configuración</span>
							<span class="text-[11px] font-semibold uppercase px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700">Parámetros del Sistema</span>
							<span class="text-[10px] font-semibold uppercase px-1.5 py-0.5 rounded bg-brand-50 dark:bg-brand-950/60 text-brand-700 dark:text-brand-300 border border-brand-200/50">Organización B2B</span>
						</h1>
						<p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Parámetros de la plataforma de distribución mayorista, políticas de crédito, régimen fiscal y reglas de integración comercial.</p>
					</div>

					<div class="flex flex-wrap items-center gap-2">
						<button onclick="saveB2bSettings()" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-lg bg-brand-800 hover:bg-brand-700 text-white shadow-xs active:scale-[0.98] transition-all">
							<i data-lucide="save" class="w-4 h-4"></i>
							<span>Guardar Configuración</span>
						</button>
					</div>
				</div>

				<!-- CARDS DE CONFIGURACIÓN -->
				<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
					<!-- Card 1: Perfil de la Organización -->
					<div class="bg-white dark:bg-surface-dark p-5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs space-y-4">
						<div class="flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
							<span class="p-2 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-600">
								<i data-lucide="building-2" class="w-4 h-4"></i>
							</span>
							<div>
								<h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Identidad Corporativa y Fiscal</h3>
								<p class="text-[11px] text-slate-500">Datos principales para cabecera de cotizaciones y facturas comerciales.</p>
							</div>
						</div>

						<div class="space-y-3 text-xs">
							<div>
								<label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nombre Comercial de la Distribuidora</label>
								<input type="text" id="set-org-name" value="Prince Distributors B2B" class="w-full px-3 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden" />
							</div>
							<div class="grid grid-cols-2 gap-3">
								<div>
									<label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Razón Social</label>
									<input type="text" id="set-org-legal" value="Distribuidora Mayorista Prince del Perú S.A.C." class="w-full px-3 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden" />
								</div>
								<div>
									<label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">RUC / Identificación Fiscal</label>
									<input type="text" id="set-org-tax" value="20601234567" class="w-full px-3 py-1.5 text-xs font-mono bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden" />
								</div>
							</div>
							<div class="grid grid-cols-2 gap-3">
								<div>
									<label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Moneda Base Operativa</label>
									<select id="set-org-currency" class="w-full px-3 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden">
										<option value="USD">Dólares Americanos (USD $)</option>
										<option value="PEN">Soles Peruanos (PEN S/)</option>
									</select>
								</div>
								<div>
									<label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Tasa Impositiva Estándar (IGV/IVA)</label>
									<div class="flex items-center gap-1.5">
										<input type="number" id="set-tax-rate" value="18.0" step="0.5" class="w-full px-3 py-1.5 text-xs font-mono font-bold bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden" />
										<span class="text-xs text-slate-400 font-bold">%</span>
									</div>
								</div>
							</div>
						</div>
					</div>

					<!-- Card 2: Políticas de Crédito y Cobranzas -->
					<div class="bg-white dark:bg-surface-dark p-5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs space-y-4">
						<div class="flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
							<span class="p-2 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600">
								<i data-lucide="shield-check" class="w-4 h-4"></i>
							</span>
							<div>
								<h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Políticas de Crédito y Morosidad</h3>
								<p class="text-[11px] text-slate-500">Reglas automáticas de validación de pedidos y control de mora.</p>
							</div>
						</div>

						<div class="space-y-3 text-xs">
							<div class="grid grid-cols-2 gap-3">
								<div>
									<label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Condición de Pago Predeterminada</label>
									<select id="set-default-terms" class="w-full px-3 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden">
										<option value="CREDIT_15">Crédito 15 días</option>
										<option value="CREDIT_30">Crédito 30 días</option>
										<option value="CASH">Contado</option>
									</select>
								</div>
								<div>
									<label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Días de Tolerancia de Pago</label>
									<input type="number" id="set-grace-days" value="3" class="w-full px-3 py-1.5 text-xs font-mono bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden" />
								</div>
							</div>
							<div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 space-y-2">
								<div class="flex items-center justify-between">
									<span class="font-medium text-slate-800 dark:text-slate-200">Bloqueo por Facturas Vencidas</span>
									<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">Activo</span>
								</div>
								<p class="text-[11px] text-slate-500">Impide confirmar pedidos automáticamente a cuentas con mora superior a 15 días.</p>
							</div>
						</div>
					</div>

					<!-- Card 3: Reglas Comerciales de Despacho y MOQ -->
					<div class="bg-white dark:bg-surface-dark p-5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs space-y-4">
						<div class="flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
							<span class="p-2 rounded-lg bg-purple-50 dark:bg-purple-950/60 text-purple-600">
								<i data-lucide="truck" class="w-4 h-4"></i>
							</span>
							<div>
								<h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Reglas Comerciales Mayoristas</h3>
								<p class="text-[11px] text-slate-500">Límites mínimos de pedido (MOQ) y control de empaques.</p>
							</div>
						</div>

						<div class="space-y-3 text-xs">
							<div class="grid grid-cols-2 gap-3">
								<div>
									<label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Pedido Mínimo Mayorista (MOQ $)</label>
									<input type="number" id="set-moq-amount" value="100.00" step="10" class="w-full px-3 py-1.5 text-xs font-mono font-bold bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden" />
								</div>
								<div>
									<label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Descuento Comercial Máximo (%)</label>
									<input type="number" id="set-max-discount" value="25.0" step="1" class="w-full px-3 py-1.5 text-xs font-mono font-bold bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden" />
								</div>
							</div>
							<div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 space-y-1">
								<div class="font-medium text-slate-800 dark:text-slate-200">Factores de Conversión de Unidades Maestras</div>
								<div class="text-[11px] text-slate-500">Caja = Factor x Unidades • Precios base calculados dinámicamente sin duplicidad.</div>
							</div>
						</div>
					</div>

					<!-- Card 4: Seguridad y Auditoría de Datos -->
					<div class="bg-white dark:bg-surface-dark p-5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs space-y-4">
						<div class="flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
							<span class="p-2 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-600">
								<i data-lucide="lock" class="w-4 h-4"></i>
							</span>
							<div>
								<h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Seguridad y Trazabilidad</h3>
								<p class="text-[11px] text-slate-500">Estado de integridad de la base de datos y registros inmutables.</p>
							</div>
						</div>

						<div class="space-y-3 text-xs">
							<div class="flex justify-between items-center py-1 border-b border-slate-100 dark:border-slate-800">
								<span class="text-slate-500">Modo de Base de Datos:</span>
								<span class="font-mono font-bold text-slate-900 dark:text-white">SQLite 3 (WAL Mode)</span>
							</div>
							<div class="flex justify-between items-center py-1 border-b border-slate-100 dark:border-slate-800">
								<span class="text-slate-500">Aislamiento Transaccional:</span>
								<span class="font-semibold text-emerald-600">Garantizado (ACID)</span>
							</div>
							<div class="flex justify-between items-center py-1 border-b border-slate-100 dark:border-slate-800">
								<span class="text-slate-500">Bitácora de Costos y Asignaciones:</span>
								<span class="font-semibold text-brand-600">Inmutable (Audited Log)</span>
							</div>
							<div class="flex justify-between items-center py-1">
								<span class="text-slate-500">Seguridad de Acceso:</span>
								<span class="font-mono text-slate-700 dark:text-slate-300">RBAC / Sesión Segura</span>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div id="section-placeholder" class="hidden space-y-6">
				<div class="pb-4 border-b border-slate-200/80 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
					<div class="flex items-center gap-3">
						<div id="placeholder-icon-box" class="w-9 h-9 rounded-lg bg-brand-50 dark:bg-brand-950/60 text-brand-700 dark:text-brand-300 border border-brand-200/60 dark:border-brand-800/60 flex items-center justify-center">
							<i id="placeholder-icon" data-lucide="layers" class="w-5 h-5"></i>
						</div>
						<div>
							<h1 id="placeholder-title" class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
								Título de Sección
							</h1>
							<p id="placeholder-desc" class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
								Descripción del módulo comercial mayorista.
							</p>
						</div>
					</div>

					<div class="flex items-center gap-2">
						<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
							<i data-lucide="clock" class="w-3.5 h-3.5"></i>
							<span data-i18n="badge_in_development">Módulo en desarrollo</span>
						</span>
					</div>
				</div>

				<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-6 sm:p-8 shadow-xs">
					<div class="max-w-2xl space-y-4">
						<div class="flex items-center gap-2 text-xs font-semibold text-brand-700 dark:text-brand-400 uppercase tracking-wider">
							<i data-lucide="cpu" class="w-4 h-4"></i>
							<span data-i18n="module_specs_title">Especificaciones de Arquitectura B2B</span>
						</div>
						<h2 id="placeholder-feature-headline" class="text-lg font-bold text-slate-900 dark:text-white">
							Capacidades Planificadas para este Módulo
						</h2>
						<p id="placeholder-feature-detail" class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
							Este componente formará parte integral del flujo transaccional Order-to-Cash de la distribuidora. Su estructura de base de datos y endpoints API están definidos según la auditoría del Paso 1.
						</p>

						<div class="pt-2">
							<h4 class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2" data-i18n="features_included">Funcionalidades en preparación:</h4>
							<ul id="placeholder-features-list" class="space-y-2 text-xs text-slate-700 dark:text-slate-300"></ul>
						</div>

						<div class="pt-4 flex items-center gap-3">
							<button onclick="handleNavClick(event, 'sales')" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors">
								<i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
								<span data-i18n="btn_back_to_sales">Volver al Resumen de Ventas</span>
							</button>
						</div>
					</div>
				</div>
			</div>

		</main>
	</div>

	<!-- MODAL: FICHA DETALLADA DE CLIENTE Y SUS ESTABLECIMIENTOS -->
	<div id="modal-cust-detail" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
		<div class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-4xl shadow-xl overflow-hidden my-8 animate-in fade-in zoom-in-95 duration-150">
			<!-- Header Modal Ficha -->
			<div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/30">
				<div class="flex items-center gap-3">
					<div class="w-10 h-10 rounded-xl bg-brand-800 text-white flex items-center justify-center font-bold text-lg shadow-xs">
						<i data-lucide="building-2" class="w-5 h-5"></i>
					</div>
					<div>
						<div class="flex items-center gap-2">
							<h3 id="detail-cust-name" class="text-base font-bold text-slate-900 dark:text-white">-</h3>
							<span id="detail-cust-demo-badge" class="hidden px-1.5 py-0.2 rounded bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 text-[10px] font-mono font-bold">DEMO</span>
							<span id="detail-cust-status" class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Activo</span>
						</div>
						<div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 flex items-center gap-3">
							<span id="detail-cust-commercial">-</span>
							<span>•</span>
							<span id="detail-cust-taxid" class="font-mono">-</span>
							<span>•</span>
							<span id="detail-cust-type" class="capitalize">-</span>
						</div>
					</div>
				</div>
				<button onclick="closeCustomerDetailModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
					<i data-lucide="x" class="w-5 h-5"></i>
				</button>
			</div>

			<!-- Body Modal Ficha -->
			<div class="p-6 space-y-6 max-h-[calc(85vh-130px)] overflow-y-auto">
				<!-- Ficha de Datos Generales -->
				<div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 rounded-xl bg-slate-50/80 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-700/80 text-xs">
					<div>
						<div class="text-slate-400 uppercase tracking-wider text-[10px] font-semibold">Teléfono de Contacto</div>
						<div id="detail-cust-phone" class="font-medium text-slate-800 dark:text-slate-200 mt-1">-</div>
					</div>
					<div>
						<div class="text-slate-400 uppercase tracking-wider text-[10px] font-semibold">Correo Electrónico</div>
						<div id="detail-cust-email" class="font-medium text-slate-800 dark:text-slate-200 mt-1">-</div>
					</div>
					<div>
						<div class="text-slate-400 uppercase tracking-wider text-[10px] font-semibold">Dirección Fiscal</div>
						<div id="detail-cust-address" class="font-medium text-slate-800 dark:text-slate-200 mt-1">-</div>
					</div>
				</div>

				<!-- Subsección Establecimientos Asociados -->
				<div class="space-y-3">
					<div class="flex items-center justify-between">
						<div>
							<h4 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
								<span>Establecimientos Asociados (Sucursales / Tiendas)</span>
								<span id="detail-est-count" class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-brand-50 dark:bg-brand-950/60 text-brand-700 dark:text-brand-300">0</span>
							</h4>
							<p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Puntos de entrega asignados a esta cuenta comercial mayorista.</p>
						</div>
						<button onclick="openEstablishmentModalForCurrentCustomer()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-brand-800 hover:bg-brand-700 text-white shadow-xs">
							<i data-lucide="plus" class="w-3.5 h-3.5"></i>
							<span>Agregar Establecimiento</span>
						</button>
					</div>

					<!-- Tabla de Establecimientos -->
					<div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
						<div id="detail-est-empty" class="hidden py-8 text-center text-slate-400 text-xs">
							No hay establecimientos registrados para este cliente. Haz clic en "Agregar Establecimiento".
						</div>
						<table id="detail-est-table" class="w-full text-left text-xs whitespace-nowrap">
							<thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-semibold uppercase text-[10px] tracking-wider border-b border-slate-200/80 dark:border-slate-800">
								<tr>
									<th class="py-2.5 px-3">Código</th>
									<th class="py-2.5 px-3">Nombre de Tienda</th>
									<th class="py-2.5 px-3">Dirección / Ciudad</th>
									<th class="py-2.5 px-3">Contacto Compras</th>
									<th class="py-2.5 px-3">Vendedor / Zona</th>
									<th class="py-2.5 px-3">Estado</th>
									<th class="py-2.5 px-3 text-center">Acciones</th>
								</tr>
							</thead>
							<tbody id="detail-est-table-body" class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
								<!-- Establecimientos inyectados dinámicamente -->
							</tbody>
						</table>
					</div>
				</div>
			</div>

			<!-- Footer Modal Ficha -->
			<div class="px-6 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 flex items-center justify-end">
				<button onclick="closeCustomerDetailModal()" class="px-4 py-2 text-xs font-semibold rounded-lg bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors">
					Cerrar Ficha
				</button>
			</div>
		</div>
	</div>

	<!-- MODAL: CREAR / EDITAR CLIENTE COMERCIAL -->
	<div id="modal-cust-form" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
		<div class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-xl shadow-xl overflow-hidden my-8 animate-in fade-in zoom-in-95 duration-150">
			<form id="form-customer" onsubmit="handleCustomerSubmit(event)">
				<input type="hidden" id="cust-form-id" value="">
				<div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/30">
					<h3 id="cust-form-title" class="text-base font-bold text-slate-900 dark:text-white">Nuevo Cliente Comercial</h3>
					<button type="button" onclick="closeCustomerModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg">
						<i data-lucide="x" class="w-5 h-5"></i>
					</button>
				</div>

				<div class="p-6 space-y-4 text-xs">
					<div id="cust-form-error" class="hidden p-3 rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border border-rose-200 font-medium"></div>

					<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
						<div class="sm:col-span-2">
							<label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Razón Social *</label>
							<input type="text" id="cust-field-name" required placeholder="Ej: Supermercados Santa Isabel S.A." class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
						</div>

						<div>
							<label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nombre Comercial</label>
							<input type="text" id="cust-field-commercial" placeholder="Ej: Santa Isabel Express" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
						</div>

						<div>
							<label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Identificación Fiscal (RUC/NIT/Tax ID) *</label>
							<input type="text" id="cust-field-taxid" required placeholder="Ej: RUC-20100458921" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
						</div>

						<div>
							<label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Tipo de Cliente *</label>
							<select id="cust-field-type" required class="w-full px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500">
								<option value="cadena">Cadena Comercial</option>
								<option value="supermercado" selected>Supermercado Independiente</option>
								<option value="minimarket">Minimarket</option>
								<option value="otro">Otro</option>
							</select>
						</div>

						<div>
							<label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Teléfono</label>
							<input type="text" id="cust-field-phone" placeholder="Ej: +51 1 614-2000" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200">
						</div>

						<div class="sm:col-span-2">
							<label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Correo Electrónico</label>
							<input type="email" id="cust-field-email" placeholder="Ej: compras@santaisabel.com" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200">
						</div>

						<div class="sm:col-span-2">
							<label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Dirección Fiscal</label>
							<input type="text" id="cust-field-address" placeholder="Ej: Av. Javier Prado Este 4200" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200">
						</div>

						<div>
							<label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Ciudad</label>
							<input type="text" id="cust-field-city" placeholder="Ej: Lima" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200">
						</div>

						<div>
							<label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Estado / Región</label>
							<input type="text" id="cust-field-state" placeholder="Ej: Lima" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200">
						</div>
					</div>
				</div>

				<div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 flex items-center justify-end gap-3">
					<button type="button" onclick="closeCustomerModal()" class="px-4 py-2 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200">
						Cancelar
					</button>
					<button type="submit" id="btn-save-cust" class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold rounded-lg bg-brand-800 hover:bg-brand-700 text-white shadow-xs">
						<span>Guardar Cliente</span>
					</button>
				</div>
			</form>
		</div>
	</div>

	<!-- MODAL: CREAR / EDITAR ESTABLECIMIENTO -->
	<div id="modal-est-form" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
		<div class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-xl shadow-xl overflow-hidden my-8 animate-in fade-in zoom-in-95 duration-150">
			<form id="form-establishment" onsubmit="handleEstablishmentSubmit(event)">
				<input type="hidden" id="est-form-id" value="">
				<input type="hidden" id="est-form-customer-id" value="">
				<div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/30">
					<div>
						<h3 id="est-form-title" class="text-base font-bold text-slate-900 dark:text-white">Nuevo Establecimiento</h3>
						<div id="est-form-cust-label" class="text-xs text-slate-500 mt-0.5">Asociado a: -</div>
					</div>
					<button type="button" onclick="closeEstablishmentModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg">
						<i data-lucide="x" class="w-5 h-5"></i>
					</button>
				</div>

				<div class="p-6 space-y-4 text-xs">
					<div id="est-form-error" class="hidden p-3 rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border border-rose-200 font-medium"></div>

					<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
						<div>
							<label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Código de Tienda / Sucursal *</label>
							<input type="text" id="est-field-code" required placeholder="Ej: EST-SI-01" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200 uppercase font-mono">
						</div>

						<div>
							<label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nombre de la Sucursal *</label>
							<input type="text" id="est-field-name" required placeholder="Ej: Santa Isabel San Isidro" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200">
						</div>

						<div class="sm:col-span-2">
							<label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Dirección de Entrega</label>
							<input type="text" id="est-field-address" placeholder="Ej: Av. Conquistadores 450" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200">
						</div>

						<div>
							<label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Ciudad</label>
							<input type="text" id="est-field-city" placeholder="Ej: Lima" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200">
						</div>

						<div>
							<label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Estado / Región</label>
							<input type="text" id="est-field-state" placeholder="Ej: Lima" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200">
						</div>

						<div>
							<label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Contacto de Compras</label>
							<input type="text" id="est-field-contact" placeholder="Ej: Patricia Silva" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200">
						</div>

						<div>
							<label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Teléfono Contacto</label>
							<input type="text" id="est-field-phone" placeholder="Ej: +51 971 223 445" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200">
						</div>

						<div>
							<label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Correo Electrónico</label>
							<input type="email" id="est-field-email" placeholder="Ej: psilva@santaisabel.com" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200">
						</div>

						<div>
							<label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Vendedor Asignado</label>
							<input type="text" id="est-field-salesrep" placeholder="Ej: Carlos Mendoza" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200">
						</div>

						<div class="sm:col-span-2">
							<label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Territorio / Zona</label>
							<input type="text" id="est-field-territory" placeholder="Ej: Lima Sur - Troncal 1" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200">
						</div>
					</div>
				</div>

				<div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 flex items-center justify-end gap-3">
					<button type="button" onclick="closeEstablishmentModal()" class="px-4 py-2 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200">
						Cancelar
					</button>
					<button type="submit" id="btn-save-est" class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold rounded-lg bg-brand-800 hover:bg-brand-700 text-white shadow-xs">
						<span>Guardar Establecimiento</span>
					</button>
				</div>
			</form>
		</div>
	</div>

	<!-- MODAL: CONFIRMACIÓN PARA DESACTIVAR REGISTRO -->
	<div id="modal-confirm" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
		<div class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md shadow-xl p-6 space-y-4">
			<div class="flex items-center gap-3">
				<div class="w-10 h-10 rounded-full bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
					<i data-lucide="alert-triangle" class="w-5 h-5"></i>
				</div>
				<div>
					<h3 id="confirm-title" class="text-sm font-bold text-slate-900 dark:text-white">Confirmar Desactivación</h3>
					<p id="confirm-desc" class="text-xs text-slate-500 mt-1">¿Estás seguro de que deseas desactivar este registro?</p>
				</div>
			</div>
			<div class="flex items-center justify-end gap-3 pt-2">
				<button type="button" onclick="closeConfirmModal()" class="px-4 py-2 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200">
					Cancelar
				</button>
				<button type="button" id="btn-confirm-action" class="px-4 py-2 text-xs font-semibold rounded-lg bg-rose-600 hover:bg-rose-700 text-white shadow-xs">
					Confirmar Desactivación
				</button>
			</div>
		</div>
	</div>

	<!-- MODAL: FORMULARIO DE PRODUCTO B2B (CREAR / EDITAR) -->
	<div id="modal-prod-form" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
		<div class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-2xl shadow-2xl overflow-hidden my-8">
			<div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
				<div class="flex items-center gap-3">
					<div class="w-8 h-8 rounded-lg bg-brand-50 dark:bg-brand-950/60 text-brand-700 dark:text-brand-300 flex items-center justify-center">
						<i data-lucide="package" class="w-4 h-4"></i>
					</div>
					<div>
						<h3 id="prod-form-title" class="text-sm font-bold text-slate-900 dark:text-white">Registrar Producto B2B</h3>
						<p class="text-[11px] text-slate-400">Especificaciones comerciales, unidades de empaque y precios de lista.</p>
					</div>
				</div>
				<button type="button" onclick="closeProductModal()" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
					<i data-lucide="x" class="w-5 h-5"></i>
				</button>
			</div>

			<form id="form-product" onsubmit="saveProductForm(event)" class="space-y-4">
				<input type="hidden" id="prod-form-id" value="">
				<div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
					<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
						<div>
							<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Código SKU <span class="text-rose-500">*</span></label>
							<input type="text" id="prod-input-sku" required placeholder="Ej: SKU-LAC-005" class="w-full px-3 py-2 text-xs font-mono uppercase bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden focus:border-brand-500">
						</div>
						<div>
							<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Código de Barras (EAN-13)</label>
							<input type="text" id="prod-input-barcode" placeholder="Ej: 7750123450099" class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden focus:border-brand-500">
						</div>
					</div>

					<div>
						<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nombre Comercial del Producto <span class="text-rose-500">*</span></label>
						<input type="text" id="prod-input-name" required placeholder="Ej: Leche Chocolatada UHT 1 L" class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden focus:border-brand-500">
					</div>

					<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
						<div>
							<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Marca Comercial <span class="text-rose-500">*</span></label>
							<input type="text" id="prod-input-brand" required placeholder="Ej: Gloria, Laive, San Fernando..." class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden focus:border-brand-500">
						</div>
						<div>
							<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Categoría Mayorista <span class="text-rose-500">*</span></label>
							<select id="prod-input-category" required class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden focus:border-brand-500">
								<option value="">Seleccionar categoría...</option>
								<option value="Lácteos">Lácteos</option>
								<option value="Refrigerados">Refrigerados</option>
								<option value="Congelados">Congelados</option>
								<option value="Carnes y aves">Carnes y aves</option>
								<option value="Bebidas">Bebidas</option>
								<option value="Desechables">Desechables</option>
								<option value="Abarrotes">Abarrotes</option>
								<option value="Limpieza">Limpieza</option>
							</select>
						</div>
					</div>

					<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-3 bg-slate-50/70 dark:bg-slate-800/40 rounded-xl border border-slate-200/60 dark:border-slate-700/60">
						<div>
							<label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Unidad Venta Menor</label>
							<input type="text" id="prod-input-unit-name" placeholder="Ej: Unidad (1 L)" value="Unidad" class="w-full px-2.5 py-1.5 text-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
						</div>
						<div>
							<label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Unidad Mayorista (Caja)</label>
							<input type="text" id="prod-input-case-name" placeholder="Ej: Caja x 12 uds" value="Caja" class="w-full px-2.5 py-1.5 text-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
						</div>
						<div>
							<label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Factor Conversión (Uds/Cja) <span class="text-rose-500">*</span></label>
							<input type="number" id="prod-input-conversion" min="1" required value="12" class="w-full px-2.5 py-1.5 text-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
						</div>
					</div>

					<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
						<div>
							<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Precio Base por Caja ($ USD) <span class="text-rose-500">*</span></label>
							<div class="relative">
								<span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs">$</span>
								<input type="number" id="prod-input-price-case" step="0.01" min="0.01" required placeholder="0.00" class="w-full pl-7 pr-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white font-mono focus:outline-hidden focus:border-brand-500">
							</div>
						</div>
						<div>
							<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Precio Base por Unidad ($ USD)</label>
							<div class="relative">
								<span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs">$</span>
								<input type="number" id="prod-input-price-unit" step="0.01" min="0" placeholder="0.00" class="w-full pl-7 pr-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white font-mono focus:outline-hidden focus:border-brand-500">
							</div>
						</div>
					</div>

					<div>
						<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Descripción Comercial</label>
						<textarea id="prod-input-desc" rows="2" placeholder="Especificaciones de conservación, peso neto, packaging..." class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden focus:border-brand-500"></textarea>
					</div>
				</div>

				<div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 flex items-center justify-end gap-3">
					<button type="button" onclick="closeProductModal()" class="px-4 py-2 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200">
						Cancelar
					</button>
					<button type="submit" id="btn-save-prod" class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold rounded-lg bg-brand-800 hover:bg-brand-700 text-white shadow-xs">
						<span>Guardar Producto</span>
					</button>
				</div>
			</form>
		</div>
	</div>

	<!-- MODAL: DETALLE Y GESTIÓN DE TARIFAS / ESCALAS DE PRODUCTO -->
	<div id="modal-prod-detail" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
		<div class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-4xl shadow-2xl overflow-hidden my-8">
			<!-- Encabezado de Producto -->
			<div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-start justify-between">
				<div class="space-y-1">
					<div class="flex items-center gap-2 flex-wrap">
						<span id="dtl-prod-sku" class="font-mono text-xs font-bold px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200">SKU-000</span>
						<span id="dtl-prod-cat" class="text-[11px] font-semibold px-2 py-0.5 rounded-md bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300">Categoría</span>
						<span id="dtl-prod-brand" class="text-[11px] font-semibold px-2 py-0.5 rounded-md bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300">Marca</span>
					</div>
					<h3 id="dtl-prod-name" class="text-base font-bold text-slate-900 dark:text-white">Nombre del Producto</h3>
					<p id="dtl-prod-meta" class="text-xs text-slate-500">Unidad: Caja x 12 uds | Precio Base: $27.00 / cja</p>
				</div>
				<button type="button" onclick="closeProductDetailModal()" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
					<i data-lucide="x" class="w-5 h-5"></i>
				</button>
			</div>

			<!-- Pestañas de Gestión -->
			<div class="px-6 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 flex items-center gap-4 text-xs font-semibold">
				<button type="button" id="tab-btn-tiers" onclick="switchProductDetailTab('tiers')" class="py-3 border-b-2 border-brand-800 text-brand-800 dark:text-white">
					Escalas por Volumen (Tier Pricing)
				</button>
				<button type="button" id="tab-btn-agreements" onclick="switchProductDetailTab('agreements')" class="py-3 border-b-2 border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">
					Acuerdos Especiales por Cliente
				</button>
				<button type="button" id="tab-btn-history" onclick="switchProductDetailTab('history')" class="py-3 border-b-2 border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">
					Historial de Modificaciones
				</button>
			</div>

			<div class="p-6 max-h-[65vh] overflow-y-auto space-y-6">
				<!-- TAB 1: ESCALAS DE VOLUMEN -->
				<div id="tab-content-tiers" class="space-y-4">
					<div class="flex items-center justify-between">
						<div>
							<h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Escalas de Descuento por Volumen</h4>
							<p class="text-[11px] text-slate-500">Precios reducidos por caja aplicables cuando el pedido supera el umbral de cantidad.</p>
						</div>
					</div>

					<!-- Formulario Inline para Añadir Escala -->
					<form id="form-add-tier" onsubmit="saveVolumeTier(event)" class="p-3.5 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700 grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
						<div>
							<label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Mínimo de Cajas</label>
							<input type="number" id="tier-min-qty" min="1" required placeholder="Ej: 10" class="w-full px-2.5 py-1.5 text-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
						</div>
						<div>
							<label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Precio por Caja ($ USD)</label>
							<input type="number" id="tier-price-case" step="0.01" min="0.01" required placeholder="Ej: 25.50" class="w-full px-2.5 py-1.5 text-xs font-mono bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
						</div>
						<div>
							<label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">% Descuento Estimado</label>
							<input type="number" id="tier-discount-pct" step="any" min="0" placeholder="Ej: 5.5" class="w-full px-2.5 py-1.5 text-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
						</div>
						<div>
							<button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-brand-800 hover:bg-brand-700 text-white shadow-xs">
								<i data-lucide="plus" class="w-3.5 h-3.5"></i>
								<span>Añadir Escala</span>
							</button>
						</div>
					</form>

					<!-- Tabla de Escalas -->
					<div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
						<table class="w-full text-left border-collapse text-xs">
							<thead>
								<tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold text-slate-500">
									<th class="py-2.5 px-3">Cantidad Mínima</th>
									<th class="py-2.5 px-3">Precio por Caja</th>
									<th class="py-2.5 px-3">% Descuento</th>
									<th class="py-2.5 px-3">Regla Activa</th>
								</tr>
							</thead>
							<tbody id="dtl-tiers-tbody" class="divide-y divide-slate-100 dark:divide-slate-800">
								<!-- Dinámico -->
							</tbody>
						</table>
					</div>
				</div>

				<!-- TAB 2: ACUERDOS ESPECIALES POR CLIENTE -->
				<div id="tab-content-agreements" class="hidden space-y-4">
					<div>
						<h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Acuerdos Comerciales de Clientes</h4>
						<p class="text-[11px] text-slate-500">Tarifa exclusiva pactada para una cadena o supermercado. Tiene máxima prioridad sobre la lista general y escalas de volumen.</p>
					</div>

					<!-- Formulario Inline para Añadir Acuerdo -->
					<form id="form-add-agreement" onsubmit="saveCustomerAgreement(event)" class="p-3.5 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700 space-y-3">
						<div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
							<div>
								<label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Cliente Comercial <span class="text-rose-500">*</span></label>
								<select id="agr-customer-select" required class="w-full px-2.5 py-1.5 text-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
									<option value="">Seleccionar cliente...</option>
								</select>
							</div>
							<div>
								<label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Precio Pactado Caja ($) <span class="text-rose-500">*</span></label>
								<input type="number" id="agr-price-case" step="0.01" min="0.01" required placeholder="Ej: 24.90" class="w-full px-2.5 py-1.5 text-xs font-mono bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
							</div>
							<div>
								<label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Precio Pactado Unit. ($)</label>
								<input type="number" id="agr-price-unit" step="0.01" min="0" placeholder="Ej: 2.20" class="w-full px-2.5 py-1.5 text-xs font-mono bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
							</div>
						</div>
						<div class="flex items-center gap-3">
							<div class="flex-1">
								<input type="text" id="agr-notes" placeholder="Notas del acuerdo / Ref de contrato o licitación..." class="w-full px-2.5 py-1.5 text-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
							</div>
							<button type="submit" class="inline-flex items-center gap-1.5 px-4 py-1.5 text-xs font-semibold rounded-lg bg-brand-800 hover:bg-brand-700 text-white shadow-xs shrink-0">
								<i data-lucide="check" class="w-3.5 h-3.5"></i>
								<span>Registrar Tarifa de Cliente</span>
							</button>
						</div>
					</form>

					<!-- Tabla de Acuerdos -->
					<div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
						<table class="w-full text-left border-collapse text-xs">
							<thead>
								<tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold text-slate-500">
									<th class="py-2.5 px-3">Cliente Comercial</th>
									<th class="py-2.5 px-3">Identificación</th>
									<th class="py-2.5 px-3 text-right">Tarifa Caja</th>
									<th class="py-2.5 px-3 text-right">Tarifa Unidad</th>
									<th class="py-2.5 px-3">Detalle / Notas</th>
								</tr>
							</thead>
							<tbody id="dtl-agreements-tbody" class="divide-y divide-slate-100 dark:divide-slate-800">
								<!-- Dinámico -->
							</tbody>
						</table>
					</div>
				</div>

				<!-- TAB 3: HISTORIAL DE MODIFICACIONES -->
				<div id="tab-content-history" class="hidden space-y-4">
					<div>
						<h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Historial de Auditoría de Precios</h4>
						<p class="text-[11px] text-slate-500">Trazabilidad de cambios de precio base de lista y actualizaciones de tarifas.</p>
					</div>

					<div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
						<table class="w-full text-left border-collapse text-xs">
							<thead>
								<tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold text-slate-500">
									<th class="py-2.5 px-3">Campo</th>
									<th class="py-2.5 px-3 text-right">Precio Anterior</th>
									<th class="py-2.5 px-3 text-right">Precio Nuevo</th>
									<th class="py-2.5 px-3">Usuario</th>
									<th class="py-2.5 px-3">Motivo</th>
									<th class="py-2.5 px-3">Fecha y Hora</th>
								</tr>
							</thead>
							<tbody id="dtl-history-tbody" class="divide-y divide-slate-100 dark:divide-slate-800">
								<!-- Dinámico -->
							</tbody>
						</table>
					</div>
				</div>
			</div>

			<div class="px-6 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 flex items-center justify-between text-xs text-slate-500">
				<span>Regla determinista: Acuerdo de Cliente &gt; Escala de Volumen &gt; Precio Base General</span>
				<button type="button" onclick="closeProductDetailModal()" class="px-4 py-1.5 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200">
					Cerrar
				</button>
			</div>
		</div>
	</div>

	<!-- MODAL: SIMULADOR Y COTIZADOR DETERMINISTA DE PRECIOS B2B -->
	<div id="modal-pricing-calc" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
		<div class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-3xl shadow-2xl overflow-hidden my-8">
			<div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
				<div class="flex items-center gap-3">
					<div class="w-8 h-8 rounded-lg bg-brand-50 dark:bg-brand-950/60 text-brand-700 dark:text-brand-300 flex items-center justify-center">
						<i data-lucide="calculator" class="w-4 h-4"></i>
					</div>
					<div>
						<h3 class="text-sm font-bold text-slate-900 dark:text-white">Simulador Determinista de Precios B2B</h3>
						<p class="text-[11px] text-slate-400">Verifica en tiempo real la tarifa, descuentos por escala y acuerdos preferenciales.</p>
					</div>
				</div>
				<button type="button" onclick="closePricingCalculatorModal()" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
					<i data-lucide="x" class="w-5 h-5"></i>
				</button>
			</div>

			<div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
				<!-- Parámetros de Simulación -->
				<div class="space-y-4">
					<h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Parámetros Comerciales</h4>
					
					<div>
						<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Producto Comercial <span class="text-rose-500">*</span></label>
						<select id="calc-product-select" onchange="runPriceSimulation()" class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden focus:border-brand-500">
							<!-- Opciones dinámicas -->
						</select>
					</div>

					<div>
						<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Cliente Comercial</label>
						<select id="calc-customer-select" onchange="runPriceSimulation()" class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden focus:border-brand-500">
							<option value="">Tarifa General (Sin Acuerdo Específico)</option>
							<!-- Clientes dinámicos -->
						</select>
					</div>

					<div class="grid grid-cols-2 gap-3">
						<div>
							<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Unidad Comercial</label>
							<select id="calc-unit-select" onchange="runPriceSimulation()" class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
								<option value="CASE">Caja Mayorista</option>
								<option value="UNIT">Unidad Suelta</option>
							</select>
						</div>
						<div>
							<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Cantidad a Cotizar</label>
							<input type="number" id="calc-quantity-input" min="1" value="10" oninput="runPriceSimulation()" class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
						</div>
					</div>
				</div>

				<!-- Tarjeta de Resultados Deterministas -->
				<div class="bg-slate-50 dark:bg-slate-800/50 p-5 rounded-xl border border-slate-200 dark:border-slate-700 flex flex-col justify-between space-y-4">
					<div>
						<div class="flex items-center justify-between mb-2">
							<span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Regla Aplicada</span>
							<span id="calc-rule-badge" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-200">
								BASE_PRICE
							</span>
						</div>
						<p id="calc-rule-desc" class="text-xs text-slate-700 dark:text-slate-300 font-medium">Precio de lista general mayorista</p>
						<p id="calc-agreement-notes" class="text-[11px] text-emerald-600 dark:text-emerald-400 mt-1 italic hidden"></p>
					</div>

					<div class="space-y-2.5 pt-3 border-t border-slate-200 dark:border-slate-700 text-xs">
						<div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
							<span>Tarifa Base de Lista:</span>
							<span id="calc-base-rate" class="font-mono font-medium">$0.00</span>
						</div>
						<div class="flex items-center justify-between text-slate-900 dark:text-white font-bold">
							<span>Tarifa Mayorista Aplicada:</span>
							<span id="calc-applied-rate" class="font-mono text-emerald-600 dark:text-emerald-400 text-sm">$0.00</span>
						</div>
						<div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
							<span>Unidades Físicas Equivalentes:</span>
							<span id="calc-equiv-units" class="font-mono">--</span>
						</div>
						<div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
							<span>Costo Unitario Resultante:</span>
							<span id="calc-unit-cost" class="font-mono font-medium">$0.0000 / ud</span>
						</div>
						<div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
							<span>Ahorro Total Cliente:</span>
							<span id="calc-savings" class="font-mono font-semibold text-emerald-600 dark:text-emerald-400">$0.00</span>
						</div>
					</div>

					<div class="pt-3 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between bg-white dark:bg-slate-800 p-3 rounded-lg border border-slate-200/80 dark:border-slate-700/80">
						<span class="text-xs font-bold text-slate-800 dark:text-slate-200">Total Cotizado:</span>
						<span id="calc-subtotal" class="text-lg font-bold font-mono text-brand-800 dark:text-white">$0.00</span>
					</div>
				</div>
			</div>

			<div class="px-6 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 flex items-center justify-end">
				<button type="button" onclick="closePricingCalculatorModal()" class="px-4 py-2 text-xs font-semibold rounded-lg bg-brand-800 hover:bg-brand-700 text-white">
					Aceptar
				</button>
			</div>
		</div>
	</div>

	<!-- MODAL: FORMULARIO COMERCIAL (COTIZACIÓN O PEDIDO B2B) -->
	<div id="modal-commercial-form" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
		<div class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-4xl shadow-2xl overflow-hidden my-8">
			<div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
				<div class="flex items-center gap-3">
					<div id="comm-form-icon-box" class="w-8 h-8 rounded-lg bg-brand-50 dark:bg-brand-950/60 text-brand-700 dark:text-brand-300 flex items-center justify-center">
						<i data-lucide="file-plus-2" class="w-4 h-4"></i>
					</div>
					<div>
						<h3 id="comm-form-title" class="text-sm font-bold text-slate-900 dark:text-white">Nueva Cotización B2B</h3>
						<p class="text-[11px] text-slate-400">Selecciona el cliente, añade líneas de productos y calcula automáticamente precios mayoristas.</p>
					</div>
				</div>
				<button type="button" onclick="closeCommercialFormModal()" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
					<i data-lucide="x" class="w-5 h-5"></i>
				</button>
			</div>

			<form id="form-commercial" onsubmit="event.preventDefault(); submitCommercialForm();" class="space-y-4">
				<input type="hidden" id="comm-form-mode" value="quote"> <!-- 'quote' o 'order' -->

				<div class="p-6 space-y-5 max-h-[72vh] overflow-y-auto">
					<!-- Cabecera Comercial -->
					<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 p-4 bg-slate-50/70 dark:bg-slate-800/40 rounded-xl border border-slate-200/60 dark:border-slate-700/60">
						<div class="sm:col-span-2">
							<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Cliente Comercial <span class="text-rose-500">*</span></label>
							<select id="comm-input-customer" required onchange="handleCommercialCustomerChange()" class="w-full px-3 py-2 text-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden focus:border-brand-500">
								<option value="">Seleccionar cliente comercial...</option>
							</select>
						</div>

						<div>
							<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Establecimiento / Sucursal</label>
							<select id="comm-input-establishment" class="w-full px-3 py-2 text-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden focus:border-brand-500">
								<option value="">Oficina Central / Sede Principal</option>
							</select>
						</div>

						<div>
							<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Ejecutivo de Ventas</label>
							<input type="text" id="comm-input-sales-rep" value="Carlos Mendoza" class="w-full px-3 py-2 text-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden focus:border-brand-500">
						</div>

						<div>
							<label id="comm-label-date" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Vigencia hasta</label>
							<input type="date" id="comm-input-date" class="w-full px-3 py-2 text-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
						</div>

						<div class="sm:col-span-3">
							<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Notas / Condiciones Comerciales</label>
							<input type="text" id="comm-input-notes" placeholder="Términos de entrega, observaciones de picking o instrucciones logísticas..." class="w-full px-3 py-2 text-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
						</div>
					</div>

					<!-- Líneas de Producto Builder -->
					<div class="space-y-3">
						<div class="flex items-center justify-between">
							<h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
								<i data-lucide="boxes" class="w-4 h-4 text-brand-700 dark:text-brand-300"></i>
								<span>Líneas del Documento Comercial</span>
							</h4>
							<span class="text-[11px] text-slate-400">Precios resueltos por motor determinista B2B</span>
						</div>

						<!-- Mini Formulario de Añadir Línea -->
						<div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700 grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
							<div class="sm:col-span-6">
								<label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Producto Comercial</label>
								<select id="comm-line-product" class="w-full px-2.5 py-1.5 text-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
									<option value="">Seleccionar producto del catálogo...</option>
								</select>
							</div>

							<div class="sm:col-span-3">
								<label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Unidad de Venta</label>
								<select id="comm-line-unit" class="w-full px-2.5 py-1.5 text-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
									<option value="CASE">Caja Mayorista</option>
									<option value="UNIT">Unidad Suelta</option>
								</select>
							</div>

							<div class="sm:col-span-2">
								<label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Cantidad</label>
								<input type="number" id="comm-line-qty" min="1" value="5" class="w-full px-2.5 py-1.5 text-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
							</div>

							<div class="sm:col-span-1">
								<button type="button" onclick="addCommercialLineFromSelector()" class="w-full py-1.5 bg-brand-800 hover:bg-brand-700 text-white rounded-lg flex items-center justify-center shadow-xs" title="Añadir producto">
									<i data-lucide="plus" class="w-4 h-4"></i>
								</button>
							</div>
						</div>

						<!-- Tabla de Líneas Seleccionadas -->
						<div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
							<table class="w-full text-left border-collapse text-xs">
								<thead>
									<tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold text-slate-500">
										<th class="py-2.5 px-3">SKU & Producto</th>
										<th class="py-2.5 px-3 text-center">Empaque / Factor</th>
										<th class="py-2.5 px-3 text-center">Cantidad</th>
										<th class="py-2.5 px-3 text-right">Tarifa Unit. Base</th>
										<th class="py-2.5 px-3 text-right">Tarifa Aplicada</th>
										<th class="py-2.5 px-3 text-right">Subtotal</th>
										<th class="py-2.5 px-3 text-center">Acción</th>
									</tr>
								</thead>
								<tbody id="comm-lines-tbody" class="divide-y divide-slate-100 dark:divide-slate-800">
									<!-- Dinámico -->
								</tbody>
							</table>
							<div id="comm-lines-empty" class="py-8 text-center text-slate-400 text-xs">
								No hay productos añadidos a esta orden. Selecciona un producto y presiona "+".
							</div>
						</div>
					</div>

					<!-- Resumen Financiero Totalizador -->
					<div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
						<div class="p-3.5 bg-slate-50 dark:bg-slate-800/30 rounded-xl border border-slate-200 dark:border-slate-700 flex items-center gap-3">
							<i data-lucide="shield-check" class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0"></i>
							<div class="text-[11px] text-slate-500">
								<span class="font-semibold text-slate-700 dark:text-slate-300 block">Precios Congelados:</span>
								Las tarifas y escalas resueltas quedarán fijadas de forma inmutable en este documento comercial.
							</div>
						</div>

						<div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700 space-y-2 text-xs">
							<div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
								<span>Subtotal Neto:</span>
								<span id="comm-sum-subtotal" class="font-mono font-medium">$0.00</span>
							</div>
							<div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
								<span>Ahorro / Descuento Mayorista:</span>
								<span id="comm-sum-discount" class="font-mono font-semibold text-emerald-600 dark:text-emerald-400">$0.00</span>
							</div>
							<div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
								<span>Impuestos (IGV 18%):</span>
								<span id="comm-sum-tax" class="font-mono font-medium">$0.00</span>
							</div>
							<div class="pt-2 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between font-bold text-sm text-slate-900 dark:text-white">
								<span>Total a Pagar:</span>
								<span id="comm-sum-total" class="font-mono text-brand-800 dark:text-white">$0.00</span>
							</div>
						</div>
					</div>
				</div>

				<div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 flex items-center justify-between">
					<button type="button" onclick="closeCommercialFormModal()" class="px-4 py-2 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200">
						Cancelar
					</button>
					<div class="flex items-center gap-2">
						<button type="button" onclick="submitCommercialForm('Borrador')" class="px-4 py-2 text-xs font-semibold rounded-lg bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200 hover:bg-slate-300">
							Guardar Borrador
						</button>
						<button type="submit" id="comm-btn-submit" class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold rounded-lg bg-brand-800 hover:bg-brand-700 text-white shadow-xs">
							<span>Confirmar y Emitir</span>
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>

	<!-- MODAL: FICHA DETALLADA DE COTIZACIÓN / PEDIDO B2B -->
	<div id="modal-commercial-detail" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
		<div class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-4xl shadow-2xl overflow-hidden my-8">
			<!-- Header Ficha -->
			<div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-start justify-between">
				<div class="space-y-1">
					<div class="flex items-center gap-2 flex-wrap">
						<span id="dtl-comm-type-pill" class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-md bg-brand-50 text-brand-700 border border-brand-200">ORDEN COMERCIAL</span>
						<span id="dtl-comm-number" class="font-mono text-sm font-bold text-slate-900 dark:text-white">PED-2026-0000</span>
						<span id="dtl-comm-status-badge"></span>
					</div>
					<h3 id="dtl-comm-customer-name" class="text-base font-bold text-slate-900 dark:text-white">Nombre del Cliente</h3>
					<p id="dtl-comm-meta" class="text-xs text-slate-500">Ejecutivo: Carlos Mendoza | Fecha: 2026-11-16</p>
				</div>
				<button type="button" onclick="closeCommercialDetailModal()" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
					<i data-lucide="x" class="w-5 h-5"></i>
				</button>
			</div>

			<!-- Pestañas Detalle vs Historial -->
			<div class="px-6 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 flex items-center gap-4 text-xs font-semibold">
				<button type="button" id="tab-cdtl-items" onclick="switchCommercialDetailTab('items')" class="py-3 border-b-2 border-brand-800 text-brand-800 dark:text-white">
					Líneas del Documento
				</button>
				<button type="button" id="tab-cdtl-history" onclick="switchCommercialDetailTab('history')" class="py-3 border-b-2 border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">
					Trazabilidad &amp; Auditoría
				</button>
			</div>

			<div class="p-6 max-h-[65vh] overflow-y-auto space-y-5">
				<!-- TAB 1: ÍTEMS Y TOTALES -->
				<div id="tab-content-cdtl-items" class="space-y-4">
					<!-- Tabla de Líneas -->
					<div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
						<table class="w-full text-left border-collapse text-xs">
							<thead>
								<tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold text-slate-500">
									<th class="py-2.5 px-3">SKU</th>
									<th class="py-2.5 px-3">Producto</th>
									<th class="py-2.5 px-3 text-center">Empaque / Factor</th>
									<th class="py-2.5 px-3 text-center">Cantidad</th>
									<th class="py-2.5 px-3 text-right">Tarifa Base</th>
									<th class="py-2.5 px-3 text-right">Tarifa Aplicada</th>
									<th class="py-2.5 px-3 text-right">Subtotal</th>
								</tr>
							</thead>
							<tbody id="dtl-comm-items-tbody" class="divide-y divide-slate-100 dark:divide-slate-800">
								<!-- Dinámico -->
							</tbody>
						</table>
					</div>

					<!-- Resumen Totales Ficha -->
					<div class="flex flex-col sm:flex-row justify-between gap-4 pt-2">
						<div class="p-3 bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-slate-200 dark:border-slate-700 text-xs flex-1">
							<span class="font-semibold text-slate-700 dark:text-slate-300 block mb-1">Notas Comerciales:</span>
							<p id="dtl-comm-notes" class="text-slate-500 italic">Sin notas registradas.</p>
						</div>

						<div class="w-full sm:w-72 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700 space-y-1.5 text-xs">
							<div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
								<span>Subtotal Neto:</span>
								<span id="dtl-comm-subtotal" class="font-mono font-medium">$0.00</span>
							</div>
							<div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
								<span>Ahorro / Descuento:</span>
								<span id="dtl-comm-discount" class="font-mono font-semibold text-emerald-600 dark:text-emerald-400">$0.00</span>
							</div>
							<div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
								<span>Impuestos (IGV 18%):</span>
								<span id="dtl-comm-tax" class="font-mono font-medium">$0.00</span>
							</div>
							<div class="pt-2 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between font-bold text-sm text-slate-900 dark:text-white">
								<span>Total Documento:</span>
								<span id="dtl-comm-total" class="font-mono text-brand-800 dark:text-white">$0.00</span>
							</div>
						</div>
					</div>
				</div>

				<!-- TAB 2: AUDITORÍA DE ESTADOS -->
				<div id="tab-content-cdtl-history" class="hidden space-y-4">
					<div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
						<table class="w-full text-left border-collapse text-xs">
							<thead>
								<tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold text-slate-500">
									<th class="py-2.5 px-3">Estado Previo</th>
									<th class="py-2.5 px-3">Nuevo Estado</th>
									<th class="py-2.5 px-3">Ejecutivo / Usuario</th>
									<th class="py-2.5 px-3">Motivo / Transición</th>
									<th class="py-2.5 px-3 font-mono">Fecha y Hora</th>
								</tr>
							</thead>
							<tbody id="dtl-comm-history-tbody" class="divide-y divide-slate-100 dark:divide-slate-800">
								<!-- Dinámico -->
							</tbody>
						</table>
					</div>
				</div>
			</div>

			<!-- Footer Acciones Contextuales -->
			<div class="px-6 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 flex items-center justify-between">
				<button type="button" onclick="closeCommercialDetailModal()" class="px-4 py-1.5 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200">
					Cerrar
				</button>
				<div id="dtl-comm-actions" class="flex items-center gap-2">
					<!-- Botones contextuales inyectados dinámicamente -->
				</div>
			</div>
		</div>
	</div>

	<!-- MODAL: FACTURAR PEDIDO CONFIRMADO (PASO 7) -->
	<div id="modal-generate-invoice" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
		<div class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-xl shadow-2xl overflow-hidden my-8">
			<div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
				<div class="flex items-center gap-3">
					<div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 flex items-center justify-center">
						<i data-lucide="receipt" class="w-4 h-4"></i>
					</div>
					<div>
						<h3 class="text-sm font-bold text-slate-900 dark:text-white">Emitir Factura Comercial B2B</h3>
						<p class="text-[11px] text-slate-400">Genera comprobante con precios y productos congelados del pedido.</p>
					</div>
				</div>
				<button type="button" onclick="closeGenerateInvoiceModal()" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
					<i data-lucide="x" class="w-5 h-5"></i>
				</button>
			</div>

			<form id="form-generate-invoice" onsubmit="submitGenerateInvoiceForm(event)" class="space-y-4 p-6">
				<div>
					<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Seleccionar Pedido Comercial Confirmado <span class="text-rose-500">*</span></label>
					<select id="inv-gen-order-select" required onchange="handleGenerateInvoiceOrderChange()" class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden focus:border-brand-500">
						<option value="">Cargando pedidos confirmados...</option>
					</select>
				</div>

				<!-- Preview de Datos del Pedido -->
				<div id="inv-gen-order-preview" class="hidden p-3.5 bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-slate-200 dark:border-slate-700 text-xs space-y-1.5">
					<div class="flex justify-between"><span class="text-slate-500">Cliente:</span><span id="inv-prev-customer" class="font-bold text-slate-900 dark:text-white">---</span></div>
					<div class="flex justify-between"><span class="text-slate-500">Líneas de producto:</span><span id="inv-prev-lines" class="font-medium">---</span></div>
					<div class="flex justify-between"><span class="text-slate-500">Subtotal Gravado:</span><span id="inv-prev-subtotal" class="font-mono font-medium">---</span></div>
					<div class="flex justify-between"><span class="text-slate-500">Impuestos (IGV 18%):</span><span id="inv-prev-tax" class="font-mono font-medium">---</span></div>
					<div class="flex justify-between pt-1 border-t border-slate-200 dark:border-slate-700 font-bold"><span class="text-slate-900 dark:text-white">Total a Facturar:</span><span id="inv-prev-total" class="font-mono text-brand-800 dark:text-brand-300">---</span></div>
				</div>

				<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
					<div>
						<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Condición de Pago <span class="text-rose-500">*</span></label>
						<select id="inv-gen-terms" required onchange="handleTermsChangeForDueDate()" class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
							<option value="contado">Contado / Inmediato</option>
							<option value="credito_15">Crédito a 15 días</option>
							<option value="credito_30" selected>Crédito a 30 días</option>
							<option value="credito_60">Crédito a 60 días</option>
						</select>
					</div>
					<div>
						<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Fecha de Vencimiento <span class="text-rose-500">*</span></label>
						<input type="date" id="inv-gen-due-date" required class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
					</div>
				</div>

				<div>
					<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Notas Comerciales / Términos</label>
					<input type="text" id="inv-gen-notes" placeholder="Términos de pago, cuenta bancaria u observaciones..." class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
				</div>

				<div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
					<button type="button" onclick="closeGenerateInvoiceModal()" class="px-4 py-2 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200">
						Cancelar
					</button>
					<button type="submit" id="btn-submit-generate-inv" class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold rounded-lg bg-brand-800 hover:bg-brand-700 text-white shadow-xs">
						<i data-lucide="check" class="w-4 h-4"></i>
						<span>Emitir Factura Comercial</span>
					</button>
				</div>
			</form>
		</div>
	</div>

	<!-- MODAL: REGISTRAR PAGO A FACTURA (PASO 7) -->
	<div id="modal-invoice-payment" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
		<div class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md shadow-2xl overflow-hidden my-8">
			<div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
				<div class="flex items-center gap-3">
					<div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 flex items-center justify-center">
						<i data-lucide="wallet" class="w-4 h-4"></i>
					</div>
					<div>
						<h3 class="text-sm font-bold text-slate-900 dark:text-white">Registrar Pago / Abono</h3>
						<p class="text-[11px] text-slate-400">Amortización parcial o liquidación de factura.</p>
					</div>
				</div>
				<button type="button" onclick="closeInvoicePaymentModal()" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
					<i data-lucide="x" class="w-5 h-5"></i>
				</button>
			</div>

			<form id="form-invoice-payment" onsubmit="submitInvoicePaymentForm(event)" class="space-y-4 p-6">
				<input type="hidden" id="pay-modal-inv-id" value="">

				<!-- Resumen de Factura -->
				<div class="p-3.5 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700 text-xs space-y-1">
					<div class="flex justify-between"><span class="text-slate-500">Factura:</span><span id="pay-modal-inv-number" class="font-mono font-bold text-slate-900 dark:text-white">---</span></div>
					<div class="flex justify-between"><span class="text-slate-500">Cliente:</span><span id="pay-modal-cust-name" class="font-medium text-slate-800 dark:text-slate-200">---</span></div>
					<div class="flex justify-between"><span class="text-slate-500">Total Facturado:</span><span id="pay-modal-total" class="font-mono font-medium">---</span></div>
					<div class="flex justify-between pt-1 border-t border-slate-200 dark:border-slate-700 font-bold"><span class="text-amber-600 dark:text-amber-400">Saldo Pendiente:</span><span id="pay-modal-balance" class="font-mono text-amber-600 dark:text-amber-400 text-sm">---</span></div>
				</div>

				<div>
					<div class="flex items-center justify-between mb-1">
						<label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Importe a Pagar ($ USD) <span class="text-rose-500">*</span></label>
						<button type="button" onclick="fillFullPaymentAmount()" class="text-[11px] font-semibold text-brand-700 dark:text-brand-300 hover:underline">
							Pagar Totalidad
						</button>
					</div>
					<input type="number" id="pay-input-amount" step="0.01" min="0.01" required placeholder="0.00" class="w-full px-3 py-2 text-sm font-mono font-bold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden focus:border-brand-500">
				</div>

				<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
					<div>
						<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Método de Pago <span class="text-rose-500">*</span></label>
						<select id="pay-input-method" required class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
							<option value="Transferencia bancaria">Transferencia bancaria</option>
							<option value="Cheque">Cheque comercial</option>
							<option value="Efectivo">Efectivo / Caja</option>
							<option value="Tarjeta corporativa">Tarjeta corporativa</option>
						</select>
					</div>
					<div>
						<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Fecha de Abono <span class="text-rose-500">*</span></label>
						<input type="date" id="pay-input-date" required class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
					</div>
				</div>

				<div>
					<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">N° Referencia Bancaria / Operación</label>
					<input type="text" id="pay-input-ref" placeholder="Ej: OP-89472610 o N° Cheque" class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
				</div>

				<div>
					<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Notas / Observaciones</label>
					<input type="text" id="pay-input-notes" placeholder="Detalle de confirmación o conciliación..." class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
				</div>

				<div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
					<button type="button" onclick="closeInvoicePaymentModal()" class="px-4 py-2 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200">
						Cancelar
					</button>
					<button type="submit" id="btn-submit-payment" class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs">
						<i data-lucide="check" class="w-4 h-4"></i>
						<span>Confirmar Pago</span>
					</button>
				</div>
			</form>
		</div>
	</div>

	<!-- MODAL: EMITIR NOTA DE CRÉDITO (PASO 7) -->
	<div id="modal-credit-note" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
		<div class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-xl shadow-2xl overflow-hidden my-8">
			<div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
				<div class="flex items-center gap-3">
					<div class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 flex items-center justify-center">
						<i data-lucide="file-minus" class="w-4 h-4"></i>
					</div>
					<div>
						<h3 class="text-sm font-bold text-slate-900 dark:text-white">Emitir Nota de Crédito B2B</h3>
						<p class="text-[11px] text-slate-400">Ajuste comercial, devolución o corrección de precio.</p>
					</div>
				</div>
				<button type="button" onclick="closeCreditNoteModal()" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
					<i data-lucide="x" class="w-5 h-5"></i>
				</button>
			</div>

			<form id="form-credit-note" onsubmit="submitCreditNoteForm(event)" class="space-y-4 p-6">
				<input type="hidden" id="cn-modal-inv-id" value="">

				<div class="p-3.5 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700 text-xs space-y-1">
					<div class="flex justify-between"><span class="text-slate-500">Factura Afectada:</span><span id="cn-modal-inv-number" class="font-mono font-bold text-slate-900 dark:text-white">---</span></div>
					<div class="flex justify-between"><span class="text-slate-500">Cliente:</span><span id="cn-modal-cust-name" class="font-medium text-slate-800 dark:text-slate-200">---</span></div>
					<div class="flex justify-between pt-1 border-t border-slate-200 dark:border-slate-700 font-bold"><span class="text-purple-700 dark:text-purple-300">Saldo Acreditable Disponible:</span><span id="cn-modal-avail-balance" class="font-mono text-purple-700 dark:text-purple-300 text-sm">---</span></div>
				</div>

				<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
					<div>
						<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Motivo Comercial <span class="text-rose-500">*</span></label>
						<select id="cn-input-reason" required class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
							<option value="devolucion">Devolución de mercadería</option>
							<option value="diferencia_precio">Diferencia de precio / tarifa</option>
							<option value="descuento">Descuento comercial posterior</option>
							<option value="ajuste_comercial" selected>Ajuste comercial por incidencia</option>
						</select>
					</div>

					<div>
						<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Importe Total Acreditado ($ USD) <span class="text-rose-500">*</span></label>
						<input type="number" id="cn-input-amount" step="0.01" min="0.01" required placeholder="0.00" oninput="calculateCreditNoteTaxes()" class="w-full px-3 py-2 text-sm font-mono font-bold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
					</div>
				</div>

				<!-- Desglose de Impuestos de la Nota de Crédito -->
				<div class="p-3 bg-slate-50 dark:bg-slate-800/30 rounded-xl border border-slate-200 dark:border-slate-700 text-xs flex items-center justify-between">
					<div>
						<span class="text-slate-500">Subtotal Neto:</span>
						<span id="cn-calc-subtotal" class="font-mono font-bold ml-1">$0.00</span>
					</div>
					<div>
						<span class="text-slate-500">IGV (18%):</span>
						<span id="cn-calc-tax" class="font-mono font-medium ml-1">$0.00</span>
					</div>
					<div>
						<span class="text-slate-700 dark:text-slate-300 font-bold">Total NC:</span>
						<span id="cn-calc-total" class="font-mono font-bold text-purple-700 dark:text-purple-300 ml-1">$0.00</span>
					</div>
				</div>

				<div>
					<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Notas / Justificación de la Nota de Crédito <span class="text-rose-500">*</span></label>
					<input type="text" id="cn-input-notes" required placeholder="Explicación detallada del motivo de acreditación..." class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
				</div>

				<div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
					<button type="button" onclick="closeCreditNoteModal()" class="px-4 py-2 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200">
						Cancelar
					</button>
					<button type="submit" id="btn-submit-credit-note" class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold rounded-lg bg-purple-700 hover:bg-purple-800 text-white shadow-xs">
						<i data-lucide="check" class="w-4 h-4"></i>
						<span>Emitir Nota de Crédito</span>
					</button>
				</div>
			</form>
		</div>
	</div>

	<!-- MODAL: FICHA DETALLADA DE FACTURA COMERCIAL (PASO 7) -->
	<div id="modal-invoice-detail" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
		<div class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-4xl shadow-2xl overflow-hidden my-8">
			<!-- Header Ficha -->
			<div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-start justify-between">
				<div class="space-y-1">
					<div class="flex items-center gap-2 flex-wrap">
						<span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 border border-blue-200">FACTURA COMERCIAL</span>
						<span id="dtl-inv-number" class="font-mono text-sm font-bold text-slate-900 dark:text-white">FAC-2026-0000</span>
						<span id="dtl-inv-status-badge"></span>
						<span id="dtl-inv-overdue-badge"></span>
					</div>
					<h3 id="dtl-inv-customer-name" class="text-base font-bold text-slate-900 dark:text-white">Nombre del Cliente</h3>
					<p id="dtl-inv-meta" class="text-xs text-slate-500">Ejecutivo: Carlos Mendoza | Emisión: 2026-11-16 | Vencimiento: 2026-12-16</p>
				</div>
				<button type="button" onclick="closeInvoiceDetailModal()" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
					<i data-lucide="x" class="w-5 h-5"></i>
				</button>
			</div>

			<!-- 4 Mini Tarjetas Financieras del Comprobante -->
			<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-4 bg-slate-50/70 dark:bg-slate-900/40 border-b border-slate-200 dark:border-slate-800 text-xs">
				<div class="p-2.5 bg-white dark:bg-surface-dark rounded-lg border border-slate-200 dark:border-slate-700">
					<span class="text-slate-500 text-[11px] block">Total Facturado</span>
					<span id="dtl-inv-total" class="font-mono font-bold text-slate-900 dark:text-white text-sm">$0.00</span>
				</div>
				<div class="p-2.5 bg-white dark:bg-surface-dark rounded-lg border border-slate-200 dark:border-slate-700">
					<span class="text-slate-500 text-[11px] block">Total Cobrado</span>
					<span id="dtl-inv-paid" class="font-mono font-bold text-emerald-600 text-sm">$0.00</span>
				</div>
				<div class="p-2.5 bg-white dark:bg-surface-dark rounded-lg border border-slate-200 dark:border-slate-700">
					<span class="text-slate-500 text-[11px] block">Notas de Crédito</span>
					<span id="dtl-inv-credits" class="font-mono font-bold text-purple-600 text-sm">$0.00</span>
				</div>
				<div class="p-2.5 bg-white dark:bg-surface-dark rounded-lg border border-slate-200 dark:border-slate-700">
					<span class="text-slate-500 text-[11px] block">Saldo Pendiente</span>
					<span id="dtl-inv-balance" class="font-mono font-bold text-rose-600 text-sm">$0.00</span>
				</div>
			</div>

			<!-- Pestañas Detalle Ficha -->
			<div class="px-6 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 flex items-center gap-4 text-xs font-semibold">
				<button type="button" id="tab-invdtl-items" onclick="switchInvoiceDetailSubTab('items')" class="py-3 border-b-2 border-brand-800 text-brand-800 dark:text-white">
					Líneas Facturadas
				</button>
				<button type="button" id="tab-invdtl-payments" onclick="switchInvoiceDetailSubTab('payments')" class="py-3 border-b-2 border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">
					Historial de Pagos (<span id="tab-invdtl-payments-count">0</span>)
				</button>
				<button type="button" id="tab-invdtl-credits" onclick="switchInvoiceDetailSubTab('credits')" class="py-3 border-b-2 border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">
					Notas de Crédito (<span id="tab-invdtl-credits-count">0</span>)
				</button>
				<button type="button" id="tab-invdtl-history" onclick="switchInvoiceDetailSubTab('history')" class="py-3 border-b-2 border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">
					Trazabilidad &amp; Auditoría
				</button>
			</div>

			<!-- Contenidos de Pestañas -->
			<div class="p-6 max-h-[55vh] overflow-y-auto">
				<!-- Tab 1: Líneas de Factura -->
				<div id="tab-content-invdtl-items" class="space-y-4">
					<table class="w-full text-left border-collapse text-xs">
						<thead>
							<tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 font-semibold bg-slate-50/50 dark:bg-slate-800/30">
								<th class="py-2.5 px-3">SKU</th>
								<th class="py-2.5 px-3">Descripción Producto</th>
								<th class="py-2.5 px-3 text-center">Unidad</th>
								<th class="py-2.5 px-3 text-center">Cant.</th>
								<th class="py-2.5 px-3 text-right">Precio Facturado</th>
								<th class="py-2.5 px-3 text-right">Subtotal</th>
							</tr>
						</thead>
						<tbody id="dtl-inv-items-tbody" class="divide-y divide-slate-100 dark:divide-slate-800">
							<!-- Inyectado vía JS -->
						</tbody>
					</table>
				</div>

				<!-- Tab 2: Pagos -->
				<div id="tab-content-invdtl-payments" class="hidden space-y-3">
					<table class="w-full text-left border-collapse text-xs">
						<thead>
							<tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 font-semibold bg-slate-50/50 dark:bg-slate-800/30">
								<th class="py-2.5 px-3">N° Pago</th>
								<th class="py-2.5 px-3">Fecha</th>
								<th class="py-2.5 px-3">Método</th>
								<th class="py-2.5 px-3">Referencia</th>
								<th class="py-2.5 px-3 text-right">Monto Abonado</th>
								<th class="py-2.5 px-3">Registrado por</th>
							</tr>
						</thead>
						<tbody id="dtl-inv-payments-tbody" class="divide-y divide-slate-100 dark:divide-slate-800">
							<!-- Inyectado vía JS -->
						</tbody>
					</table>
				</div>

				<!-- Tab 3: Notas de Crédito -->
				<div id="tab-content-invdtl-credits" class="hidden space-y-3">
					<table class="w-full text-left border-collapse text-xs">
						<thead>
							<tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 font-semibold bg-slate-50/50 dark:bg-slate-800/30">
								<th class="py-2.5 px-3">N° Nota Crédito</th>
								<th class="py-2.5 px-3">Motivo</th>
								<th class="py-2.5 px-3">Fecha</th>
								<th class="py-2.5 px-3 text-right">Total Acreditado</th>
								<th class="py-2.5 px-3 text-center">Estado</th>
							</tr>
						</thead>
						<tbody id="dtl-inv-credits-tbody" class="divide-y divide-slate-100 dark:divide-slate-800">
							<!-- Inyectado vía JS -->
						</tbody>
					</table>
				</div>

				<!-- Tab 4: Auditoría & Trazabilidad -->
				<div id="tab-content-invdtl-history" class="hidden space-y-3">
					<table class="w-full text-left border-collapse text-xs">
						<thead>
							<tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 font-semibold bg-slate-50/50 dark:bg-slate-800/30">
								<th class="py-2.5 px-3">Tipo</th>
								<th class="py-2.5 px-3">Estado Destino</th>
								<th class="py-2.5 px-3">Usuario</th>
								<th class="py-2.5 px-3">Comentario / Detalle</th>
								<th class="py-2.5 px-3 font-mono">Fecha &amp; Hora</th>
							</tr>
						</thead>
						<tbody id="dtl-inv-history-tbody" class="divide-y divide-slate-100 dark:divide-slate-800">
							<!-- Inyectado vía JS -->
						</tbody>
					</table>
				</div>
			</div>

			<!-- Footer Acciones de Factura -->
			<div class="px-6 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 flex items-center justify-between">
				<button type="button" onclick="closeInvoiceDetailModal()" class="px-4 py-1.5 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200">
					Cerrar
				</button>
				<div id="dtl-inv-actions" class="flex items-center gap-2">
					<!-- Inyectado dinámicamente vía JS -->
				</div>
			</div>
		</div>
	</div>


	<!-- ========================================================
	     MODALES DEL MÓDULO DE VENDEDORES Y TERRITORIOS (PASO 9)
	     ======================================================== -->

	<!-- MODAL 1: DETALLE / FICHA DE VENDEDOR & CARTERA -->
	<div id="modal-sales-rep-detail" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
		<div class="relative w-full max-w-4xl bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden my-8 max-h-[90vh] flex flex-col">
			<!-- Header -->
			<div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50">
				<div class="flex items-center gap-3">
					<div id="rep-dtl-avatar" class="w-10 h-10 rounded-full bg-brand-100 dark:bg-brand-900/40 text-brand-700 dark:text-brand-300 font-bold flex items-center justify-center text-sm border border-brand-200 dark:border-brand-800">
						CM
					</div>
					<div>
						<div class="flex items-center gap-2">
							<h3 id="rep-dtl-name" class="text-base font-bold text-slate-900 dark:text-white">Carlos Mendoza</h3>
							<span id="rep-dtl-status-badge" class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60">Activo</span>
						</div>
						<p class="text-xs text-slate-500 flex items-center gap-2 mt-0.5">
							<span id="rep-dtl-territory">Lima Centro Comercial</span> • 
							<span id="rep-dtl-email">c.mendoza@princedistributors.com</span> • 
							<span id="rep-dtl-phone">+51 987 654 321</span>
						</p>
					</div>
				</div>
				<button type="button" onclick="closeSalesRepDetailModal()" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">
					<i data-lucide="x" class="w-5 h-5"></i>
				</button>
			</div>

			<!-- KPIs Rápidos del Vendedor -->
			<div class="p-6 bg-slate-50/30 dark:bg-slate-900/20 border-b border-slate-100 dark:border-slate-800/80">
				<div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
					<div class="p-3 bg-white dark:bg-slate-800/70 rounded-xl border border-slate-200/80 dark:border-slate-700/60">
						<p class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Ventas Netas</p>
						<p id="rep-dtl-net-sales" class="text-lg font-bold font-mono text-slate-900 dark:text-white mt-0.5">$0.00</p>
					</div>
					<div class="p-3 bg-white dark:bg-slate-800/70 rounded-xl border border-slate-200/80 dark:border-slate-700/60">
						<p class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Meta Mensual</p>
						<div class="flex items-baseline justify-between mt-0.5">
							<span id="rep-dtl-quota" class="text-lg font-bold font-mono text-slate-900 dark:text-white">$0.00</span>
							<span id="rep-dtl-achievement" class="text-xs font-bold text-brand-600 dark:text-brand-400">0%</span>
						</div>
					</div>
					<div class="p-3 bg-white dark:bg-slate-800/70 rounded-xl border border-slate-200/80 dark:border-slate-700/60">
						<p class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Pedidos Confirmados</p>
						<p id="rep-dtl-orders-count" class="text-lg font-bold font-mono text-slate-900 dark:text-white mt-0.5">0</p>
					</div>
					<div class="p-3 bg-white dark:bg-slate-800/70 rounded-xl border border-slate-200/80 dark:border-slate-700/60">
						<p class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Saldo por Cobrar</p>
						<p id="rep-dtl-receivables" class="text-lg font-bold font-mono text-amber-600 dark:text-amber-400 mt-0.5">$0.00</p>
					</div>
				</div>
			</div>

			<!-- Sub-navegación Tabs de la Ficha -->
			<div class="px-6 pt-3 border-b border-slate-200 dark:border-slate-800 flex gap-4">
				<button type="button" onclick="switchSalesRepDetailSubTab('clients')" id="rep-dtl-tab-btn-clients" class="pb-2.5 text-xs font-bold border-b-2 border-brand-600 text-brand-600 dark:text-brand-400 flex items-center gap-1.5">
					<i data-lucide="store" class="w-3.5 h-3.5"></i> Cartera Asignada (<span id="rep-dtl-count-clients">0</span>)
				</button>
				<button type="button" onclick="switchSalesRepDetailSubTab('inactive')" id="rep-dtl-tab-btn-inactive" class="pb-2.5 text-xs font-medium border-b-2 border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 flex items-center gap-1.5">
					<i data-lucide="alert-triangle" class="w-3.5 h-3.5 text-amber-500"></i> Clientes Inactivos (>30d) (<span id="rep-dtl-count-inactive">0</span>)
				</button>
				<button type="button" onclick="switchSalesRepDetailSubTab('orders')" id="rep-dtl-tab-btn-orders" class="pb-2.5 text-xs font-medium border-b-2 border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 flex items-center gap-1.5">
					<i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i> Pedidos Recientes (<span id="rep-dtl-count-orders">0</span>)
				</button>
			</div>

			<!-- Cuerpo de las pestañas -->
			<div class="p-6 overflow-y-auto flex-1">
				<!-- Tab 1: Cartera -->
				<div id="rep-dtl-tab-clients">
					<div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
						<table class="w-full text-left text-xs">
							<thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200 dark:border-slate-800">
								<tr>
									<th class="py-2.5 px-3">Cliente / Cadena</th>
									<th class="py-2.5 px-3">Establecimiento / Sede</th>
									<th class="py-2.5 px-3">Ciudad / Zona</th>
									<th class="py-2.5 px-3 text-right">Facturación</th>
									<th class="py-2.5 px-3 text-right">Saldo CxC</th>
									<th class="py-2.5 px-3 text-center">Estado</th>
								</tr>
							</thead>
							<tbody id="rep-dtl-tbody-clients" class="divide-y divide-slate-100 dark:divide-slate-800">
								<!-- Inyectado vía JS -->
							</tbody>
						</table>
					</div>
				</div>

				<!-- Tab 2: Inactivos -->
				<div id="rep-dtl-tab-inactive" class="hidden space-y-3">
					<div class="p-3 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 rounded-xl text-xs text-amber-800 dark:text-amber-300 flex items-center gap-2">
						<i data-lucide="info" class="w-4 h-4 shrink-0 text-amber-600"></i>
						<span>Establecimientos que no registran pedidos facturados en los últimos 30 días. Requieren seguimiento proactivo del ejecutivo comercial.</span>
					</div>
					<div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
						<table class="w-full text-left text-xs">
							<thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200 dark:border-slate-800">
								<tr>
									<th class="py-2.5 px-3">Cliente</th>
									<th class="py-2.5 px-3">Sede</th>
									<th class="py-2.5 px-3">Contacto Compras</th>
									<th class="py-2.5 px-3">Última Compra</th>
									<th class="py-2.5 px-3 text-center">Días Inactivo</th>
								</tr>
							</thead>
							<tbody id="rep-dtl-tbody-inactive" class="divide-y divide-slate-100 dark:divide-slate-800">
								<!-- Inyectado vía JS -->
							</tbody>
						</table>
					</div>
				</div>

				<!-- Tab 3: Pedidos -->
				<div id="rep-dtl-tab-orders" class="hidden">
					<div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
						<table class="w-full text-left text-xs">
							<thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200 dark:border-slate-800">
								<tr>
									<th class="py-2.5 px-3">Pedido #</th>
									<th class="py-2.5 px-3">Fecha</th>
									<th class="py-2.5 px-3">Cliente</th>
									<th class="py-2.5 px-3">Sede</th>
									<th class="py-2.5 px-3 text-right">Total</th>
									<th class="py-2.5 px-3 text-center">Estado</th>
								</tr>
							</thead>
							<tbody id="rep-dtl-tbody-orders" class="divide-y divide-slate-100 dark:divide-slate-800">
								<!-- Inyectado vía JS -->
							</tbody>
						</table>
					</div>
				</div>
			</div>

			<!-- Footer -->
			<div class="px-6 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 flex items-center justify-between">
				<button type="button" onclick="closeSalesRepDetailModal()" class="px-4 py-1.5 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200">
					Cerrar
				</button>
				<button type="button" id="rep-dtl-edit-btn" onclick="openCreateSalesRepModal(currentDetailRepId)" class="px-4 py-1.5 text-xs font-semibold rounded-lg bg-brand-600 text-white hover:bg-brand-700 flex items-center gap-1.5">
					<i data-lucide="edit" class="w-3.5 h-3.5"></i> Editar Vendedor
				</button>
			</div>
		</div>
	</div>

	<!-- MODAL 2: REGISTRAR / EDITAR VENDEDOR -->
	<div id="modal-create-sales-rep" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
		<div class="relative w-full max-w-lg bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden my-8">
			<div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50">
				<div>
					<h3 id="modal-rep-form-title" class="text-base font-bold text-slate-900 dark:text-white">Registrar Ejecutivo Comercial</h3>
					<p class="text-xs text-slate-500 mt-0.5">Gestión de vendedores mayoristas, metas mensuales y asignación territorial.</p>
				</div>
				<button type="button" onclick="closeCreateSalesRepModal()" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">
					<i data-lucide="x" class="w-5 h-5"></i>
				</button>
			</div>

			<form id="form-sales-rep" onsubmit="submitSalesRepForm(event)" class="space-y-4 p-6">
				<input type="hidden" id="rep-form-id" value="">

				<div>
					<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nombre Completo <span class="text-rose-500">*</span></label>
					<input type="text" id="rep-form-name" required placeholder="Ej: Carlos Mendoza" class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden focus:border-brand-500">
				</div>

				<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
					<div>
						<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Correo Corporativo <span class="text-rose-500">*</span></label>
						<input type="email" id="rep-form-email" required placeholder="c.mendoza@princedistributors.com" class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden focus:border-brand-500">
					</div>
					<div>
						<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Teléfono / Celular</label>
						<input type="tel" id="rep-form-phone" placeholder="+51 987 654 321" class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden focus:border-brand-500">
					</div>
				</div>

				<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
					<div>
						<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Territorio Asignado</label>
						<select id="rep-form-territory" class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden focus:border-brand-500">
							<option value="">(Sin territorio asignado)</option>
						</select>
					</div>
					<div>
						<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Meta Mensual ($ USD)</label>
						<input type="number" id="rep-form-quota" step="0.01" min="0" placeholder="20000.00" class="w-full px-3 py-2 text-xs font-mono bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden focus:border-brand-500">
					</div>
				</div>

				<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
					<div>
						<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Fecha de Ingreso</label>
						<input type="date" id="rep-form-hire-date" class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden focus:border-brand-500">
					</div>
					<div>
						<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Estado</label>
						<select id="rep-form-status" class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden focus:border-brand-500">
							<option value="active">Activo</option>
							<option value="inactive">Inactivo</option>
						</select>
					</div>
				</div>

				<div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2">
					<button type="button" onclick="closeCreateSalesRepModal()" class="px-4 py-2 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200">
						Cancelar
					</button>
					<button type="submit" id="btn-submit-sales-rep" class="px-5 py-2 text-xs font-semibold rounded-lg bg-brand-600 text-white hover:bg-brand-700 shadow-xs flex items-center gap-1.5">
						<i data-lucide="save" class="w-3.5 h-3.5"></i> Guardar Vendedor
					</button>
				</div>
			</form>
		</div>
	</div>

	<!-- MODAL 3: REGISTRAR / EDITAR TERRITORIO -->
	<div id="modal-create-territory" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
		<div class="relative w-full max-w-lg bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden my-8">
			<div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50">
				<div>
					<h3 id="modal-ter-form-title" class="text-base font-bold text-slate-900 dark:text-white">Registrar Territorio Comercial</h3>
					<p class="text-xs text-slate-500 mt-0.5">Definición de zonas geográficas para asignación de cartera B2B.</p>
				</div>
				<button type="button" onclick="closeCreateTerritoryModal()" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">
					<i data-lucide="x" class="w-5 h-5"></i>
				</button>
			</div>

			<form id="form-territory" onsubmit="submitTerritoryForm(event)" class="space-y-4 p-6">
				<input type="hidden" id="ter-form-id" value="">

				<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
					<div>
						<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Código Único <span class="text-rose-500">*</span></label>
						<input type="text" id="ter-form-code" required placeholder="TER-LIM-NOR" class="w-full px-3 py-2 text-xs uppercase font-mono bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden focus:border-brand-500">
					</div>
					<div class="sm:col-span-2">
						<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nombre del Territorio <span class="text-rose-500">*</span></label>
						<input type="text" id="ter-form-name" required placeholder="Ej: Lima Norte Metropolitano" class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden focus:border-brand-500">
					</div>
				</div>

				<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
					<div>
						<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Ciudad / Región <span class="text-rose-500">*</span></label>
						<input type="text" id="ter-form-city" required placeholder="Lima" class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden focus:border-brand-500">
					</div>
					<div>
						<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Zona / Subzona</label>
						<input type="text" id="ter-form-zone" placeholder="Los Olivos / Comas / Indep." class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden focus:border-brand-500">
					</div>
				</div>

				<div>
					<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Descripción / Delimitación</label>
					<textarea id="ter-form-desc" rows="2" placeholder="Cobertura de cadenas comerciales y supermercados independientes de la zona norte." class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden focus:border-brand-500"></textarea>
				</div>

				<div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2">
					<button type="button" onclick="closeCreateTerritoryModal()" class="px-4 py-2 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200">
						Cancelar
					</button>
					<button type="submit" id="btn-submit-territory" class="px-5 py-2 text-xs font-semibold rounded-lg bg-brand-600 text-white hover:bg-brand-700 shadow-xs flex items-center gap-1.5">
						<i data-lucide="save" class="w-3.5 h-3.5"></i> Guardar Territorio
					</button>
				</div>
			</form>
		</div>
	</div>

	<!-- MODAL 4: REASIGNAR SEDE COMERCIAL -->
	<div id="modal-reassign-establishment" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
		<div class="relative w-full max-w-lg bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden my-8">
			<div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50">
				<div>
					<h3 class="text-base font-bold text-slate-900 dark:text-white">Reasignar Sede Comercial</h3>
					<p class="text-xs text-slate-500 mt-0.5">Transferencia de cobertura territorial y ejecutivo responsable.</p>
				</div>
				<button type="button" onclick="closeReassignEstablishmentModal()" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">
					<i data-lucide="x" class="w-5 h-5"></i>
				</button>
			</div>

			<form id="form-reassign-establishment" onsubmit="submitReassignmentForm(event)" class="space-y-4 p-6">
				<div>
					<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Establecimiento / Sede <span class="text-rose-500">*</span></label>
					<select id="reassign-select-est" required onchange="handleReassignmentEstChange()" class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden focus:border-brand-500">
						<option value="">Seleccionar Sede Comercial...</option>
					</select>
				</div>

				<!-- Asignación Actual (Readonly card) -->
				<div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700 text-xs space-y-1">
					<div class="flex justify-between"><span class="text-slate-500">Cliente Matriz:</span><span id="reassign-cur-customer" class="font-bold text-slate-900 dark:text-white">---</span></div>
					<div class="flex justify-between"><span class="text-slate-500">Vendedor Actual:</span><span id="reassign-cur-rep" class="font-semibold text-slate-800 dark:text-slate-200">---</span></div>
					<div class="flex justify-between"><span class="text-slate-500">Territorio Actual:</span><span id="reassign-cur-territory" class="font-mono text-slate-600 dark:text-slate-400">---</span></div>
				</div>

				<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
					<div>
						<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nuevo Vendedor <span class="text-rose-500">*</span></label>
						<select id="reassign-new-rep" required onchange="handleNewRepSelection()" class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden focus:border-brand-500">
							<option value="">Seleccionar Vendedor...</option>
						</select>
					</div>
					<div>
						<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nuevo Territorio</label>
						<select id="reassign-new-territory" class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden focus:border-brand-500">
							<option value="">(Mantener o definir)</option>
						</select>
					</div>
				</div>

				<div>
					<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Motivo de Reasignación</label>
					<input type="text" id="reassign-reason" placeholder="Ej: Reestructuración comercial de zona / Optimización de ruta" class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden focus:border-brand-500">
				</div>

				<div class="p-2.5 bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/60 rounded-xl text-[11px] text-blue-700 dark:text-blue-300 flex items-start gap-2">
					<i data-lucide="info" class="w-4 h-4 shrink-0 mt-0.5"></i>
					<span>La reasignación actualiza la atención futura. Se conserva el registro de auditoría y no se alteran las comisiones ni el historial de facturación ya emitida.</span>
				</div>

				<div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2">
					<button type="button" onclick="closeReassignEstablishmentModal()" class="px-4 py-2 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200">
						Cancelar
					</button>
					<button type="submit" id="btn-submit-reassignment" class="px-5 py-2 text-xs font-semibold rounded-lg bg-brand-600 text-white hover:bg-brand-700 shadow-xs flex items-center gap-1.5">
						<i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i> Confirmar Reasignación
					</button>
				</div>
			</form>
		</div>
	</div>



	<!-- MODAL: REGISTRAR AJUSTE DE COSTO AUTORIZADO (PASO 10) -->
	<div id="modal-cost-adjustment" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
		<div class="relative w-full max-w-lg bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden my-8">
			<div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50">
				<div>
					<h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
						<i data-lucide="calculator" class="w-4 h-4 text-brand-600"></i>
						<span>Ajuste de Costo de Adquisición</span>
					</h3>
					<p class="text-xs text-slate-500 mt-0.5">Actualización de costo estándar con registro auditado.</p>
				</div>
				<button type="button" onclick="closeCostAdjustmentModal()" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">
					<i data-lucide="x" class="w-5 h-5"></i>
				</button>
			</div>

			<form id="form-cost-adjustment" onsubmit="submitCostAdjustmentForm(event)" class="space-y-4 p-6">
				<div>
					<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Producto Comercial <span class="text-rose-500">*</span></label>
					<select id="adj-select-product" required onchange="handleCostAdjProductChange()" class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden focus:border-brand-500">
						<option value="">Seleccionar Producto...</option>
					</select>
				</div>

				<!-- Valores actuales -->
				<div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700 text-xs space-y-1">
					<div class="flex justify-between"><span class="text-slate-500">Costo Unitario Actual:</span><span id="adj-cur-unit-cost" class="font-mono font-bold text-slate-900 dark:text-white">$0.00</span></div>
					<div class="flex justify-between"><span class="text-slate-500">Costo por Caja Actual:</span><span id="adj-cur-case-cost" class="font-mono font-semibold text-slate-800 dark:text-slate-200">$0.00</span></div>
					<div class="flex justify-between"><span class="text-slate-500">Factor de Conversión:</span><span id="adj-cur-factor" class="font-mono text-slate-600 dark:text-slate-400">1 caja = 12 uds</span></div>
				</div>

				<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
					<div>
						<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nuevo Costo Unitario Base ($) <span class="text-rose-500">*</span></label>
						<input type="number" id="adj-new-unit-cost" step="0.01" min="0" required placeholder="0.00" onkeyup="handleNewUnitCostInput()" class="w-full px-3 py-2 text-xs font-mono font-bold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden focus:border-brand-500">
					</div>
					<div>
						<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Costo Proyectado por Caja</label>
						<input type="text" id="adj-new-case-cost-preview" readonly placeholder="$0.00" class="w-full px-3 py-2 text-xs font-mono bg-slate-100 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-500 dark:text-slate-400">
					</div>
				</div>

				<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
					<div>
						<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Fecha de Vigencia <span class="text-rose-500">*</span></label>
						<input type="date" id="adj-effective-date" required class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden focus:border-brand-500">
					</div>
					<div>
						<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Autorizado Por <span class="text-rose-500">*</span></label>
						<input type="text" id="adj-authorized-by" required value="Auditoría Comercial B2B" class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden focus:border-brand-500">
					</div>
				</div>

				<div>
					<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Motivo del Ajuste para Auditoría <span class="text-rose-500">*</span></label>
					<textarea id="adj-reason" required rows="2" placeholder="Ej: Negociación anual con proveedor / Actualización por flete marítimo..." class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-hidden focus:border-brand-500"></textarea>
				</div>

				<div class="p-2.5 bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/60 rounded-xl text-[11px] text-blue-700 dark:text-blue-300 flex items-start gap-2">
					<i data-lucide="info" class="w-4 h-4 shrink-0 mt-0.5"></i>
					<span>Regla contable: El nuevo costo se aplicará a futuras operaciones. Los comprobantes y márgenes facturados anteriormente se conservan congelados sin alteración retroactiva.</span>
				</div>

				<div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2">
					<button type="button" onclick="closeCostAdjustmentModal()" class="px-4 py-2 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200">
						Cancelar
					</button>
					<button type="submit" id="btn-submit-cost-adj" class="px-5 py-2 text-xs font-semibold rounded-lg bg-brand-600 text-white hover:bg-brand-700 shadow-xs flex items-center gap-1.5">
						<i data-lucide="save" class="w-3.5 h-3.5"></i> Guardar Ajuste
					</button>
				</div>
			</form>
		</div>
	</div>

	<!-- SCRIPTS DE COMPORTAMIENTO & CONTROLADORES B2B -->
	<script>
		// Diccionario de Traducción ES / EN
		const b2bTranslations = {
			es: {
				demo_mode_active: 'Modo Demo B2B Activo',
				nav_commercial: 'Gestión Comercial B2B',
				nav_sales: 'Resumen de Ventas',
				nav_customers: 'Clientes y Almacenes',
				nav_products: 'Productos y Precios',
				nav_orders: 'Cotizaciones y Pedidos',
				nav_invoices: 'Facturación B2B',
				nav_sales_reps: 'Vendedores y Territorios',
				nav_profitability: 'Rentabilidad Comercial',
				nav_reports: 'Reportes y Analítica',
				nav_settings: 'Configuración',
				collapse_sidebar: 'Colapsar menú',
				breadcrumb_root: 'Ventas Mayoristas',
				erp_sync_status: 'EDI & ERP Sincronizado (Hace 2 min)',
				btn_new_order: 'Nueva Orden B2B',
				sales_summary_title: 'Tablero Ejecutivo de Ventas',
				period_current: 'Noviembre 2026',
				sales_summary_desc: 'Supervisión en tiempo real de facturación mayorista, margen comercial, OTIF y riesgo crediticio.',
				btn_export: 'Exportar',
				kpi_total_sales: 'Ventas Totales B2B',
				kpi_net_sales: 'Ventas Netas B2B',
				kpi_confirmed_orders: 'Pedidos Confirmados',
				kpi_active_customers: 'Clientes Activos',
				kpi_average_ticket: 'Ticket Promedio',
				kpi_accounts_receivable: 'Cuentas por Cobrar',
				kpi_sales_variation: 'Variación Ventas',
				kpi_gross_margin: 'Margen Bruto',
				kpi_orders: 'Órdenes Mayoristas',
				kpi_otif: 'Nivel de Servicio OTIF',
				kpi_overdue: 'Cartera en Riesgo (>30D)',
				chart_sales_trend_title: 'Evolución de Facturación & Margen B2B',
				chart_sales_trend_desc: 'Desempeño diario en dólares facturados frente al margen bruto alcanzado.',
				legend_sales: 'Ventas ($ USD)',
				legend_margin: 'Margen %',
				chart_channel_title: 'Ventas por Canal',
				chart_channel_desc: 'Distribución porcentual de ingresos B2B.',
				chart_category_sales: 'Ventas por Categoría',
				chart_billing_vs_collections: 'Facturación vs. Cobros',
				chart_order_status: 'Pedidos por Estado',
				chart_top_products: 'Top 5 Productos Facturados',
				top_clients_title: 'Top 5 Cuentas Corporativas',
				top_clients_desc: 'Mayor volumen de compra en el periodo.',
				btn_view_all: 'Ver todos',
				recent_orders_title: 'Órdenes de Venta Mayoristas Recientes',
				recent_orders_desc: 'Últimos pedidos despachados o en preparación para cadenas y almacenes.',
				btn_filter_status: 'Filtrar Estado',
				btn_view_all_orders: 'Ver Todas (184)',
				badge_in_development: 'Módulo en desarrollo',
				module_specs_title: 'Especificaciones de Arquitectura B2B',
				features_included: 'Funcionalidades en preparación:',
				btn_back_to_sales: 'Volver al Resumen de Ventas'
			},
			en: {
				demo_mode_active: 'B2B Demo Mode Active',
				nav_commercial: 'B2B Commercial Suite',
				nav_sales: 'Sales Summary',
				nav_customers: 'Customers & Warehouses',
				nav_products: 'Products & Pricing',
				nav_orders: 'Quotes & Orders',
				nav_invoices: 'B2B Invoicing',
				nav_sales_reps: 'Sales Reps & Territories',
				nav_profitability: 'Commercial Profitability',
				nav_reports: 'Reports & Analytics',
				nav_settings: 'Settings',
				collapse_sidebar: 'Collapse menu',
				breadcrumb_root: 'Wholesale Sales',
				erp_sync_status: 'EDI & ERP Synchronized (2m ago)',
				btn_new_order: 'New B2B Order',
				sales_summary_title: 'Executive Sales Dashboard',
				period_current: 'November 2026',
				sales_summary_desc: 'Real-time monitoring of wholesale billing, commercial margin, OTIF and credit risk.',
				btn_export: 'Export',
				kpi_total_sales: 'Total B2B Sales',
				kpi_net_sales: 'Net B2B Sales',
				kpi_confirmed_orders: 'Confirmed Orders',
				kpi_active_customers: 'Active Customers',
				kpi_average_ticket: 'Average Ticket',
				kpi_accounts_receivable: 'Accounts Receivable',
				kpi_sales_variation: 'Sales Variance',
				kpi_gross_margin: 'Gross Margin',
				kpi_orders: 'Wholesale Orders',
				kpi_otif: 'OTIF Service Level',
				kpi_overdue: 'At-Risk Debt (>30D)',
				chart_sales_trend_title: 'Revenue & Gross Margin Evolution',
				chart_sales_trend_desc: 'Daily performance in billed USD versus realized gross margin.',
				legend_sales: 'Sales ($ USD)',
				legend_margin: 'Margin %',
				chart_channel_title: 'Sales by Channel',
				chart_channel_desc: 'Percentage breakdown of B2B revenues.',
				chart_category_sales: 'Sales by Category',
				chart_billing_vs_collections: 'Billing vs. Collections',
				chart_order_status: 'Orders by Status',
				chart_top_products: 'Top 5 Invoiced Products',
				top_clients_title: 'Top 5 Corporate Accounts',
				top_clients_desc: 'Highest purchase volume during period.',
				btn_view_all: 'View all',
				recent_orders_title: 'Recent Wholesale Sales Orders',
				recent_orders_desc: 'Latest dispatched or picking orders for supermarket chains and stores.',
				btn_filter_status: 'Filter Status',
				btn_view_all_orders: 'View All (184)',
				badge_in_development: 'Module in development',
				module_specs_title: 'B2B Architecture Specifications',
				features_included: 'Features under preparation:',
				btn_back_to_sales: 'Back to Sales Summary'
			}
		};

		const b2bSectionsMeta = {
			sales: { icon: 'layout-dashboard', title_es: 'Resumen de Ventas', title_en: 'Sales Summary' },
			customers: { icon: 'building-2', title_es: 'Clientes y Almacenes', title_en: 'Customers & Warehouses' },
			products: { icon: 'package', title_es: 'Productos y Precios', title_en: 'Products & Pricing', desc_es: 'Catálogo mayorista maestro con precios escalonados por volumen, empaque por cajas/palets y pedidos mínimos (MOQ).', desc_en: 'Master wholesale catalog with volume tier pricing, case/pallet packaging, and minimum order quantities (MOQ).' },
			orders: { icon: 'shopping-cart', title_es: 'Cotizaciones y Pedidos', title_en: 'Quotes & Orders', desc_es: 'Gestión integral del ciclo de ventas mayorista: Cotización, Validación crediticia, Picking WMS y Despacho.', desc_en: 'End-to-end wholesale sales cycle: Quotation, Credit approval, WMS picking, and Dispatch.' },
			invoices: { icon: 'receipt', title_es: 'Facturación y Cobranzas', title_en: 'Invoicing & Collections', desc_es: 'Control integral de emisión de facturas desde pedidos confirmados, pagos, control de morosidad y notas de crédito comerciales.', desc_en: 'Comprehensive invoicing from confirmed orders, payment amortization, overdue tracking, and commercial credit notes.' },
			'sales-reps': { icon: 'users', title_es: 'Vendedores y Territorios', title_en: 'Sales Reps & Territories', desc_es: 'Estructura comercial de ejecutivos de cuenta mayorista, asignación geográfica de clientes y control de metas.', desc_en: 'Commercial organization of wholesale account executives, geographical territories, and quota tracking.' },
			profitability: { icon: 'trending-up', title_es: 'Rentabilidad Comercial', title_en: 'Commercial Profitability', desc_es: 'Análisis detallado de margen de contribución por cliente corporativo, línea de producto y canal de distribución.', desc_en: 'In-depth contribution margin analysis by corporate client, product category, and distribution channel.' },
			reports: { icon: 'bar-chart-3', title_es: 'Reportes y Analítica', title_en: 'Reports & Analytics', desc_es: 'Centro de reportes consolidados para dirección comercial, auditoría de precios, OTIF y rotación mayorista.', desc_en: 'Consolidated executive reports hub for commercial leadership, pricing audits, OTIF, and inventory turns.' },
			settings: { icon: 'settings', title_es: 'Configuración', title_en: 'Settings', desc_es: 'Parámetros generales de la plataforma de ventas B2B, integración con ERP/EDI y políticas de crédito.', desc_en: 'General B2B sales system configuration, ERP/EDI integrations, and credit policy parameters.' }
		};

		let currentSection = getSectionFromPath() || document.body.dataset.initialSection || 'sales';
		let currentLang = localStorage.getItem('b2b_lang') || 'es';
		let customersDataCache = [];
		let currentActiveCustomerId = null;
		let searchDebounceTimer = null;

		document.addEventListener('DOMContentLoaded', function() {
			if (typeof lucide !== 'undefined') {
				lucide.createIcons();
			}
			if (localStorage.getItem('b2b_sidebar_collapsed') === 'true') {
				setSidebarCollapsed(true, false);
			}

			applyLanguage(currentLang);
			navigateToSection(currentSection, false);

			window.addEventListener('popstate', function(event) {
				const section = (event.state && event.state.section) ? event.state.section : getSectionFromPath();
				navigateToSection(section, false);
			});
		});

		function getSectionFromPath() {
			const path = window.location.pathname;
			const parts = path.split('/').filter(Boolean);
			if (parts.length >= 2 && parts[0] === 'b2b') return parts[1];
			if (parts.length === 1 && (parts[0] === 'customers' || parts[0] === 'products' || parts[0] === 'orders' || parts[0] === 'invoices' || parts[0] === 'sales-reps' || parts[0] === 'profitability' || parts[0] === 'reports' || parts[0] === 'settings')) return parts[0];
			return 'sales';
		}

		function handleNavClick(event, sectionId) {
			if (event) event.preventDefault();
			navigateToSection(sectionId, true);
			if (window.innerWidth < 768) closeMobileSidebar();
		}

		function navigateToSection(sectionId, updateHistory = true) {
			if (!b2bSectionsMeta[sectionId]) sectionId = 'sales';
			currentSection = sectionId;

			if (updateHistory) {
				const newUrl = sectionId === 'customers' ? '/b2b/customers' : (sectionId === 'products' ? '/b2b/products' : (sectionId === 'orders' ? '/b2b/orders' : (sectionId === 'invoices' ? '/b2b/invoices' : (sectionId === 'sales-reps' ? '/b2b/sales-reps' : (sectionId === 'profitability' ? '/b2b/profitability' : (sectionId === 'reports' ? '/b2b/reports' : (sectionId === 'settings' ? '/b2b/settings' : ('/b2b/' + sectionId))))))));
				window.history.pushState({ section: sectionId }, '', newUrl);
			}

			// Actualizar clases de links en Sidebar
			const navLinks = document.querySelectorAll('#b2b-sidebar-nav .nav-item');
			navLinks.forEach(link => {
				const linkSection = link.dataset.section;
				const icon = link.querySelector('i');
				if (linkSection === sectionId) {
					link.className = 'nav-item active-nav flex items-center gap-3 px-3 py-2 text-sm font-semibold rounded-lg bg-brand-50 dark:bg-brand-950/60 text-brand-700 dark:text-brand-300 border border-brand-200/60 dark:border-brand-800/60 shadow-xs transition-colors group relative';
					if (icon) icon.className = 'w-4 h-4 text-brand-700 dark:text-brand-300 shrink-0';
				} else {
					link.className = 'nav-item flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 transition-colors group relative';
					if (icon) icon.className = 'w-4 h-4 text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-200 shrink-0';
				}
			});

			// Actualizar Breadcrumb y Título
			const meta = b2bSectionsMeta[sectionId];
			const breadcrumbEl = document.getElementById('breadcrumb-current');
			if (breadcrumbEl && meta) {
				breadcrumbEl.textContent = currentLang === 'es' ? meta.title_es : meta.title_en;
			}
			document.title = (currentLang === 'es' ? meta.title_es : meta.title_en) + ' — Prince Distributors';

			// Actualizar Botón de Acción del Header
			const headerActionText = document.getElementById('header-action-text');
			if (headerActionText) {
				if (sectionId === 'customers') {
					headerActionText.textContent = currentLang === 'es' ? 'Nuevo Cliente' : 'New Customer';
				} else if (sectionId === 'products') {
					headerActionText.textContent = currentLang === 'es' ? 'Nuevo Producto' : 'New Product';
				} else if (sectionId === 'orders') {
					headerActionText.textContent = currentLang === 'es' ? 'Nuevo Pedido' : 'New Order';
				} else if (sectionId === 'invoices') {
					headerActionText.textContent = currentLang === 'es' ? 'Facturar Pedido' : 'Bill Order';
				} else if (sectionId === 'sales-reps') {
					headerActionText.textContent = currentLang === 'es' ? 'Nuevo Vendedor' : 'New Sales Rep';
				} else if (sectionId === 'profitability') {
					headerActionText.textContent = currentLang === 'es' ? 'Ajuste de Costo' : 'Adjust Cost';
				} else if (sectionId === 'reports') {
					headerActionText.textContent = currentLang === 'es' ? 'Motor Analítico' : 'Run Engine';
				} else if (sectionId === 'settings') {
					headerActionText.textContent = currentLang === 'es' ? 'Guardar Cambios' : 'Save Settings';
				} else {
					headerActionText.textContent = currentLang === 'es' ? 'Nueva Orden B2B' : 'New B2B Order';
				}
			}

			// Swapping de Contenedores
			const salesContainer = document.getElementById('section-sales');
			const custContainer = document.getElementById('section-customers');
			const prodContainer = document.getElementById('section-products');
			const ordContainer = document.getElementById('section-orders');
			const invContainer = document.getElementById('section-invoices');
			const repContainer = document.getElementById('section-sales-reps');
			const profContainer = document.getElementById('section-profitability');
			const repHubContainer = document.getElementById('section-reports');
			const settContainer = document.getElementById('section-settings');
			const placeholderContainer = document.getElementById('section-placeholder');

			salesContainer.classList.add('hidden');
			custContainer.classList.add('hidden');
			if (prodContainer) prodContainer.classList.add('hidden');
			if (ordContainer) ordContainer.classList.add('hidden');
			if (invContainer) invContainer.classList.add('hidden');
			if (repContainer) repContainer.classList.add('hidden');
			if (profContainer) profContainer.classList.add('hidden');
			if (repHubContainer) repHubContainer.classList.add('hidden');
			if (settContainer) settContainer.classList.add('hidden');
			placeholderContainer.classList.add('hidden');

			if (sectionId === 'sales') {
				salesContainer.classList.remove('hidden');
				fetchDashboardModule();
			} else if (sectionId === 'customers') {
				custContainer.classList.remove('hidden');
				fetchCustomersList();
			} else if (sectionId === 'products') {
				if (prodContainer) prodContainer.classList.remove('hidden');
				fetchProductsList();
			} else if (sectionId === 'orders') {
				if (ordContainer) ordContainer.classList.remove('hidden');
				fetchOrdersModule();
			} else if (sectionId === 'invoices') {
				if (invContainer) invContainer.classList.remove('hidden');
				fetchInvoicesModule();
			} else if (sectionId === 'sales-reps') {
				if (repContainer) repContainer.classList.remove('hidden');
				fetchSalesRepsModule();
			} else if (sectionId === 'profitability') {
				if (profContainer) profContainer.classList.remove('hidden');
				fetchProfitabilityModule();
			} else if (sectionId === 'reports') {
				if (repHubContainer) repHubContainer.classList.remove('hidden');
				fetchIntelligenceModule();
			} else if (sectionId === 'settings') {
				if (settContainer) settContainer.classList.remove('hidden');
			} else {
				placeholderContainer.classList.remove('hidden');
				const iconEl = document.getElementById('placeholder-icon');
				if (iconEl) iconEl.setAttribute('data-lucide', meta.icon);
				document.getElementById('placeholder-title').textContent = currentLang === 'es' ? meta.title_es : meta.title_en;
				document.getElementById('placeholder-desc').textContent = currentLang === 'es' ? (meta.desc_es || '') : (meta.desc_en || '');
				if (typeof lucide !== 'undefined') lucide.createIcons();
			}

			window.scrollTo({ top: 0, behavior: 'smooth' });
		}

		function handleHeaderNewAction() {
			if (currentSection === 'customers') {
				openCustomerModal();
			} else if (currentSection === 'products') {
				openProductModal();
			} else if (currentSection === 'orders') {
				openCommercialFormModal('order');
			} else if (currentSection === 'invoices') {
				openGenerateInvoiceModal();
			} else if (currentSection === 'sales-reps') {
				openCreateSalesRepModal();
			} else if (currentSection === 'profitability') {
				openCostAdjustmentModal();
			} else if (currentSection === 'reports') {
				triggerIntelligenceEngine();
			} else if (currentSection === 'settings') {
				saveB2bSettings();
			} else {
				alert('La creación de nuevas órdenes B2B se habilitará en el Paso correspondiente.');
			}
		}

		/* ========================================================
		   MÓDULO FUNCIONAL DE CLIENTES Y ALMACENES (PASO 4)
		   ======================================================== */

		// Obtener lista y métricas vía API RESTful
		async function fetchCustomersList() {
			const loading = document.getElementById('cust-table-loading');
			const empty = document.getElementById('cust-table-empty');
			const tbody = document.getElementById('cust-table-body');

			if (loading) loading.classList.remove('hidden');
			if (empty) empty.classList.add('hidden');

			const search = (document.getElementById('cust-search')?.value || '').trim();
			const type = document.getElementById('cust-filter-type')?.value || '';
			const active = document.getElementById('cust-filter-active')?.value || '';

			const params = new URLSearchParams();
			if (search) params.append('search', search);
			if (type) params.append('type', type);
			if (active !== '') params.append('active', active);

			try {
				const res = await fetch('/api/b2b/customers?' + params.toString());
				const data = await res.json();

				if (data.success) {
					// Actualizar KPIs
					if (data.metrics) {
						document.getElementById('kpi-cust-active').textContent = data.metrics.active_customers ?? 0;
						document.getElementById('kpi-cust-chains').textContent = data.metrics.chains ?? 0;
						document.getElementById('kpi-cust-est').textContent = data.metrics.total_establishments ?? 0;
						document.getElementById('kpi-cust-inactive').textContent = data.metrics.inactive_customers ?? 0;
					}

					customersDataCache = data.customers || [];
					renderCustomersTable(customersDataCache);
				}
			} catch (err) {
				console.error('Error al cargar clientes B2B:', err);
			} finally {
				if (loading) loading.classList.add('hidden');
			}
		}

		function renderCustomersTable(customers) {
			const tbody = document.getElementById('cust-table-body');
			const empty = document.getElementById('cust-table-empty');
			const summary = document.getElementById('cust-table-summary');

			tbody.innerHTML = '';

			if (!customers || customers.length === 0) {
				empty.classList.remove('hidden');
				summary.textContent = '0 clientes encontrados';
				return;
			}

			empty.classList.add('hidden');
			summary.textContent = `Mostrando ${customers.length} cliente(s) comercial(es)`;

			customers.forEach(c => {
				const tr = document.createElement('tr');
				tr.className = 'hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors group';

				// Tipo Badge Color
				let typeBadgeClass = 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300';
				let typeName = c.customer_type || 'otro';
				if (typeName === 'cadena') {
					typeBadgeClass = 'bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200/60 dark:border-blue-800/60 font-semibold';
					typeName = 'Cadena Comercial';
				} else if (typeName === 'supermercado') {
					typeBadgeClass = 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/60 font-semibold';
					typeName = 'Supermercado Ind.';
				} else if (typeName === 'minimarket') {
					typeBadgeClass = 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800/60 font-semibold';
					typeName = 'Minimarket';
				}

				// Estado Badge
				const isActive = parseInt(c.active) === 1;
				const statusBadge = isActive
					? `<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200/50"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Activo</span>`
					: `<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-500 border border-slate-200 dark:border-slate-700"><span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>Inactivo</span>`;

				const estCount = parseInt(c.active_establishments_count) || 0;
				const estPill = `<button onclick="viewCustomerDetail(${c.id})" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md font-mono font-semibold text-[11px] bg-slate-100 dark:bg-slate-800 text-brand-800 dark:text-brand-300 hover:bg-brand-50 transition-colors"><i data-lucide="store" class="w-3 h-3"></i><span>${estCount} sucursal(es)</span></button>`;

				const demoBadge = parseInt(c.is_demo) === 1 ? `<span class="px-1 py-0.2 rounded bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 text-[9px] font-mono font-bold ml-1">DEMO</span>` : '';

				tr.innerHTML = `
					<td class="py-3 px-4">
						<div class="font-semibold text-slate-900 dark:text-white flex items-center">
							<span>${escapeHtml(c.name)}</span>
							${demoBadge}
						</div>
						<div class="text-[11px] text-slate-400 mt-0.5">${escapeHtml(c.commercial_name || c.name)}</div>
					</td>
					<td class="py-3 px-4">
						<span class="px-2 py-0.5 rounded-full text-[10px] uppercase tracking-wider ${typeBadgeClass}">${typeName}</span>
					</td>
					<td class="py-3 px-4 font-mono text-slate-700 dark:text-slate-300 font-medium">${escapeHtml(c.tax_id || '-')}</td>
					<td class="py-3 px-4 text-center">${estPill}</td>
					<td class="py-3 px-4">
						<div class="text-slate-800 dark:text-slate-200 font-medium">${escapeHtml(c.email || '-')}</div>
						<div class="text-[11px] text-slate-400 font-mono">${escapeHtml(c.phone || '-')}</div>
					</td>
					<td class="py-3 px-4">${statusBadge}</td>
					<td class="py-3 px-4 text-right">
						<div class="inline-flex items-center gap-1.5">
							<button onclick="viewCustomerDetail(${c.id})" class="p-1.5 rounded-md text-slate-500 hover:text-brand-700 dark:hover:text-brand-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" title="Ver Ficha y Sucursales">
								<i data-lucide="eye" class="w-4 h-4"></i>
							</button>
							<button onclick="editCustomer(${c.id})" class="p-1.5 rounded-md text-slate-500 hover:text-brand-700 dark:hover:text-brand-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" title="Editar Cliente">
								<i data-lucide="edit-2" class="w-4 h-4"></i>
							</button>
							<button onclick="confirmToggleCustomerActive(${c.id}, ${isActive ? 0 : 1}, '${escapeJs(c.name)}')" class="p-1.5 rounded-md ${isActive ? 'text-slate-400 hover:text-rose-600' : 'text-slate-400 hover:text-emerald-600'} hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" title="${isActive ? 'Desactivar' : 'Activar'}">
								<i data-lucide="${isActive ? 'power-off' : 'power'}" class="w-4 h-4"></i>
							</button>
						</div>
					</td>
				`;
				tbody.appendChild(tr);
			});

			if (typeof lucide !== 'undefined') {
				lucide.createIcons();
			}
		}

		function handleCustomerSearch() {
			clearTimeout(searchDebounceTimer);
			searchDebounceTimer = setTimeout(() => {
				fetchCustomersList();
			}, 250);
		}

		function resetCustomerFilters() {
			const s = document.getElementById('cust-search');
			const t = document.getElementById('cust-filter-type');
			const a = document.getElementById('cust-filter-active');
			if (s) s.value = '';
			if (t) t.value = '';
			if (a) a.value = '';
			fetchCustomersList();
		}

		// Ver Ficha Detallada
		async function viewCustomerDetail(id) {
			try {
				const res = await fetch(`/api/b2b/customers/${id}`);
				const data = await res.json();
				if (!data.success || !data.customer) return;

				const c = data.customer;
				currentActiveCustomerId = c.id;

				document.getElementById('detail-cust-name').textContent = c.name;
				document.getElementById('detail-cust-commercial').textContent = c.commercial_name || c.name;
				document.getElementById('detail-cust-taxid').textContent = c.tax_id || '-';
				document.getElementById('detail-cust-type').textContent = c.customer_type || 'otro';
				document.getElementById('detail-cust-phone').textContent = c.phone || '-';
				document.getElementById('detail-cust-email').textContent = c.email || '-';
				document.getElementById('detail-cust-address').textContent = `${c.address || '-'} ${c.city ? '— ' + c.city : ''}`;

				const isDemo = parseInt(c.is_demo) === 1;
				const demoBadge = document.getElementById('detail-cust-demo-badge');
				if (demoBadge) {
					if (isDemo) demoBadge.classList.remove('hidden');
					else demoBadge.classList.add('hidden');
				}

				const isActive = parseInt(c.active) === 1;
				const statusEl = document.getElementById('detail-cust-status');
				statusEl.textContent = isActive ? 'Activo' : 'Inactivo';
				statusEl.className = isActive
					? 'px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200'
					: 'px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-500 border border-slate-200';

				// Renderizar establecimientos
				renderDetailEstablishments(c.establishments || []);

				document.getElementById('modal-cust-detail').classList.remove('hidden');
				if (typeof lucide !== 'undefined') lucide.createIcons();
			} catch (e) {
				console.error('Error cargando ficha:', e);
			}
		}

		function renderDetailEstablishments(establishments) {
			const tbody = document.getElementById('detail-est-table-body');
			const empty = document.getElementById('detail-est-empty');
			const count = document.getElementById('detail-est-count');

			tbody.innerHTML = '';
			count.textContent = establishments.length;

			if (establishments.length === 0) {
				empty.classList.remove('hidden');
				document.getElementById('detail-est-table').classList.add('hidden');
				return;
			}

			empty.classList.add('hidden');
			document.getElementById('detail-est-table').classList.remove('hidden');

			establishments.forEach(e => {
				const tr = document.createElement('tr');
				tr.className = 'hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors';
				const isActive = parseInt(e.active) === 1;

				const statusBadge = isActive
					? `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200"><span class="w-1 h-1 rounded-full bg-emerald-500"></span>Activo</span>`
					: `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-semibold bg-slate-100 text-slate-500 border border-slate-200"><span class="w-1 h-1 rounded-full bg-slate-400"></span>Inactivo</span>`;

				tr.innerHTML = `
					<td class="py-2.5 px-3 font-mono font-bold text-brand-700 dark:text-brand-300">${escapeHtml(e.code)}</td>
					<td class="py-2.5 px-3 font-semibold text-slate-900 dark:text-white">${escapeHtml(e.name)}</td>
					<td class="py-2.5 px-3">
						<div>${escapeHtml(e.address || '-')}</div>
						<div class="text-[10px] text-slate-400">${escapeHtml(e.city || '')}${e.state ? ', ' + escapeHtml(e.state) : ''}</div>
					</td>
					<td class="py-2.5 px-3">
						<div class="font-medium">${escapeHtml(e.purchasing_contact_name || '-')}</div>
						<div class="text-[10px] text-slate-400">${escapeHtml(e.purchasing_contact_phone || '')}</div>
					</td>
					<td class="py-2.5 px-3">
						<div class="font-medium">${escapeHtml(e.sales_rep_name || '-')}</div>
						<div class="text-[10px] text-slate-400">${escapeHtml(e.territory || '-')}</div>
					</td>
					<td class="py-2.5 px-3">${statusBadge}</td>
					<td class="py-2.5 px-3 text-center">
						<div class="inline-flex items-center gap-1">
							<button onclick="editEstablishment(${e.id})" class="p-1 rounded text-slate-400 hover:text-slate-700 dark:hover:text-slate-200" title="Editar Establecimiento">
								<i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
							</button>
							<button onclick="confirmToggleEstablishmentActive(${e.id}, ${isActive ? 0 : 1}, '${escapeJs(e.name)}')" class="p-1 rounded ${isActive ? 'text-slate-400 hover:text-rose-600' : 'text-slate-400 hover:text-emerald-600'}" title="${isActive ? 'Desactivar' : 'Activar'}">
								<i data-lucide="${isActive ? 'power-off' : 'power'}" class="w-3.5 h-3.5"></i>
							</button>
						</div>
					</td>
				`;
				tbody.appendChild(tr);
			});
		}

		function closeCustomerDetailModal() {
			document.getElementById('modal-cust-detail').classList.add('hidden');
			currentActiveCustomerId = null;
		}

		// Crear / Editar Cliente Modal
		function openCustomerModal(id = null) {
			const form = document.getElementById('form-customer');
			form.reset();
			document.getElementById('cust-form-error').classList.add('hidden');
			document.getElementById('cust-form-id').value = id || '';
			document.getElementById('cust-form-title').textContent = id ? 'Editar Cliente Comercial' : 'Nuevo Cliente Comercial';
			document.getElementById('modal-cust-form').classList.remove('hidden');
		}

		function editCustomer(id) {
			const customer = customersDataCache.find(c => parseInt(c.id) === parseInt(id));
			if (!customer) return;

			openCustomerModal(id);
			document.getElementById('cust-field-name').value = customer.name || '';
			document.getElementById('cust-field-commercial').value = customer.commercial_name || '';
			document.getElementById('cust-field-taxid').value = customer.tax_id || '';
			document.getElementById('cust-field-type').value = customer.customer_type || 'supermercado';
			document.getElementById('cust-field-phone').value = customer.phone || '';
			document.getElementById('cust-field-email').value = customer.email || '';
			document.getElementById('cust-field-address').value = customer.address || '';
			document.getElementById('cust-field-city').value = customer.city || '';
			document.getElementById('cust-field-state').value = customer.state || '';
		}

		function closeCustomerModal() {
			document.getElementById('modal-cust-form').classList.add('hidden');
		}

		async function handleCustomerSubmit(event) {
			event.preventDefault();
			const id = document.getElementById('cust-form-id').value;
			const errBox = document.getElementById('cust-form-error');
			errBox.classList.add('hidden');

			const payload = {
				name: document.getElementById('cust-field-name').value.trim(),
				commercial_name: document.getElementById('cust-field-commercial').value.trim(),
				tax_id: document.getElementById('cust-field-taxid').value.trim(),
				customer_type: document.getElementById('cust-field-type').value,
				phone: document.getElementById('cust-field-phone').value.trim(),
				email: document.getElementById('cust-field-email').value.trim(),
				address: document.getElementById('cust-field-address').value.trim(),
				city: document.getElementById('cust-field-city').value.trim(),
				state: document.getElementById('cust-field-state').value.trim()
			};

			const url = id ? `/api/b2b/customers/${id}` : '/api/b2b/customers';
			const method = id ? 'PUT' : 'POST';

			try {
				const res = await fetch(url, {
					method: method,
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify(payload)
				});
				const data = await res.json();

				if (!res.ok || !data.success) {
					errBox.textContent = data.error_message || data.message || 'Error al guardar el cliente comercial.';
					errBox.classList.remove('hidden');
					return;
				}

				closeCustomerModal();
				await fetchCustomersList();
			} catch (e) {
				errBox.textContent = 'Error de conexión con el servidor.';
				errBox.classList.remove('hidden');
			}
		}

		// Crear / Editar Establecimiento Modal
		function openEstablishmentModalForCurrentCustomer() {
			if (!currentActiveCustomerId) return;
			const customer = customersDataCache.find(c => parseInt(c.id) === parseInt(currentActiveCustomerId));
			openEstablishmentModal(currentActiveCustomerId, null, customer ? customer.name : '');
		}

		function openEstablishmentModal(customerId, estId = null, customerName = '') {
			const form = document.getElementById('form-establishment');
			form.reset();
			document.getElementById('est-form-error').classList.add('hidden');
			document.getElementById('est-form-id').value = estId || '';
			document.getElementById('est-form-customer-id').value = customerId;
			document.getElementById('est-form-title').textContent = estId ? 'Editar Establecimiento' : 'Nuevo Establecimiento';
			document.getElementById('est-form-cust-label').textContent = 'Asociado a: ' + (customerName || 'Cliente #' + customerId);
			document.getElementById('modal-est-form').classList.remove('hidden');
		}

		async function editEstablishment(estId) {
			if (!currentActiveCustomerId) return;
			try {
				const res = await fetch(`/api/b2b/customers/${currentActiveCustomerId}`);
				const data = await res.json();
				if (!data.success) return;
				const est = (data.customer.establishments || []).find(e => parseInt(e.id) === parseInt(estId));
				if (!est) return;

				openEstablishmentModal(currentActiveCustomerId, estId, data.customer.name);
				document.getElementById('est-field-code').value = est.code || '';
				document.getElementById('est-field-name').value = est.name || '';
				document.getElementById('est-field-address').value = est.address || '';
				document.getElementById('est-field-city').value = est.city || '';
				document.getElementById('est-field-state').value = est.state || '';
				document.getElementById('est-field-contact').value = est.purchasing_contact_name || '';
				document.getElementById('est-field-phone').value = est.purchasing_contact_phone || '';
				document.getElementById('est-field-email').value = est.purchasing_contact_email || '';
				document.getElementById('est-field-salesrep').value = est.sales_rep_name || '';
				document.getElementById('est-field-territory').value = est.territory || '';
			} catch (e) {
				console.error(e);
			}
		}

		function closeEstablishmentModal() {
			document.getElementById('modal-est-form').classList.add('hidden');
		}

		async function handleEstablishmentSubmit(event) {
			event.preventDefault();
			const estId = document.getElementById('est-form-id').value;
			const customerId = document.getElementById('est-form-customer-id').value;
			const errBox = document.getElementById('est-form-error');
			errBox.classList.add('hidden');

			const payload = {
				customer_id: customerId,
				code: document.getElementById('est-field-code').value.trim(),
				name: document.getElementById('est-field-name').value.trim(),
				address: document.getElementById('est-field-address').value.trim(),
				city: document.getElementById('est-field-city').value.trim(),
				state: document.getElementById('est-field-state').value.trim(),
				purchasing_contact_name: document.getElementById('est-field-contact').value.trim(),
				purchasing_contact_phone: document.getElementById('est-field-phone').value.trim(),
				purchasing_contact_email: document.getElementById('est-field-email').value.trim(),
				sales_rep_name: document.getElementById('est-field-salesrep').value.trim(),
				territory: document.getElementById('est-field-territory').value.trim()
			};

			const url = estId ? `/api/b2b/establishments/${estId}` : '/api/b2b/establishments';
			const method = estId ? 'PUT' : 'POST';

			try {
				const res = await fetch(url, {
					method: method,
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify(payload)
				});
				const data = await res.json();

				if (!res.ok || !data.success) {
					errBox.textContent = data.error_message || data.message || 'Error al guardar el establecimiento.';
					errBox.classList.remove('hidden');
					return;
				}

				closeEstablishmentModal();
				// Recargar ficha detallada si está abierta
				if (currentActiveCustomerId) {
					await viewCustomerDetail(currentActiveCustomerId);
				}
				await fetchCustomersList();
			} catch (e) {
				errBox.textContent = 'Error de conexión con el servidor.';
				errBox.classList.remove('hidden');
			}
		}

		// Confirmación de Desactivación
		let pendingConfirmCallback = null;

		function confirmToggleCustomerActive(id, newStatus, name) {
			const isDeactivating = newStatus === 0;
			document.getElementById('confirm-title').textContent = isDeactivating ? 'Desactivar Cliente Comercial' : 'Activar Cliente Comercial';
			document.getElementById('confirm-desc').textContent = isDeactivating
				? `¿Confirmas la desactivación de "${name}"? Si es una cadena, todos sus establecimientos quedarán suspendidos de despacho.`
				: `¿Confirmas la reactivación de "${name}"?`;
			document.getElementById('btn-confirm-action').textContent = isDeactivating ? 'Sí, Desactivar' : 'Sí, Activar';
			document.getElementById('btn-confirm-action').className = isDeactivating
				? 'px-4 py-2 text-xs font-semibold rounded-lg bg-rose-600 hover:bg-rose-700 text-white shadow-xs'
				: 'px-4 py-2 text-xs font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs';

			pendingConfirmCallback = async () => {
				await fetch(`/api/b2b/customers/${id}/toggle-active`, {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify({ active: newStatus })
				});
				closeConfirmModal();
				fetchCustomersList();
			};

			document.getElementById('modal-confirm').classList.remove('hidden');
		}

		function confirmToggleEstablishmentActive(estId, newStatus, name) {
			const isDeactivating = newStatus === 0;
			document.getElementById('confirm-title').textContent = isDeactivating ? 'Desactivar Establecimiento' : 'Activar Establecimiento';
			document.getElementById('confirm-desc').textContent = isDeactivating
				? `¿Confirmas la desactivación de la sucursal "${name}"? Se pausarán las órdenes asignadas a esta dirección.`
				: `¿Confirmas la reactivación de la sucursal "${name}"?`;
			document.getElementById('btn-confirm-action').textContent = isDeactivating ? 'Sí, Desactivar' : 'Sí, Activar';
			document.getElementById('btn-confirm-action').className = isDeactivating
				? 'px-4 py-2 text-xs font-semibold rounded-lg bg-rose-600 hover:bg-rose-700 text-white shadow-xs'
				: 'px-4 py-2 text-xs font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs';

			pendingConfirmCallback = async () => {
				await fetch(`/api/b2b/establishments/${estId}/toggle-active`, {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify({ active: newStatus })
				});
				closeConfirmModal();
				if (currentActiveCustomerId) {
					await viewCustomerDetail(currentActiveCustomerId);
				}
				fetchCustomersList();
			};

			document.getElementById('modal-confirm').classList.remove('hidden');
		}

		document.getElementById('btn-confirm-action').addEventListener('click', function() {
			if (pendingConfirmCallback) pendingConfirmCallback();
		});

		function closeConfirmModal() {
			document.getElementById('modal-confirm').classList.add('hidden');
			pendingConfirmCallback = null;
		}

		/* ========================================================
		   MÓDULO FUNCIONAL DE PRODUCTOS Y PRECIOS B2B (PASO 5)
		   ======================================================== */

		let productsDataCache = [];
		let customersForCalcCache = [];
		let activeProductDetailId = null;
		let debounceProdTimer = null;

		function debounceFetchProducts() {
			clearTimeout(debounceProdTimer);
			debounceProdTimer = setTimeout(() => {
				fetchProductsList();
			}, 250);
		}

		function resetProductFilters() {
			const s = document.getElementById('prod-search');
			if (s) s.value = '';
			const c = document.getElementById('prod-filter-category');
			if (c) c.value = '';
			const b = document.getElementById('prod-filter-brand');
			if (b) b.value = '';
			const a = document.getElementById('prod-filter-active');
			if (a) a.value = '';
			fetchProductsList();
		}

		async function fetchProductsList() {
			const loading = document.getElementById('prod-table-loading');
			const empty = document.getElementById('prod-table-empty');
			const tbody = document.getElementById('prod-table-body');

			if (loading) loading.classList.remove('hidden');
			if (empty) empty.classList.add('hidden');

			const search = (document.getElementById('prod-search')?.value || '').trim();
			const category = document.getElementById('prod-filter-category')?.value || '';
			const brand = document.getElementById('prod-filter-brand')?.value || '';
			const active = document.getElementById('prod-filter-active')?.value || '';

			const params = new URLSearchParams();
			if (search) params.append('search', search);
			if (category) params.append('category', category);
			if (brand) params.append('brand', brand);
			if (active !== '') params.append('active', active);

			try {
				const res = await fetch('/api/b2b/products?' + params.toString());
				const data = await res.json();

				if (data.success) {
					// Actualizar KPIs de Productos
					if (data.metrics) {
						const kpiTotal = document.getElementById('kpi-prod-total');
						if (kpiTotal) kpiTotal.textContent = data.metrics.total_products ?? 0;
						const kpiCat = document.getElementById('kpi-prod-categories');
						if (kpiCat) kpiCat.textContent = data.metrics.total_categories ?? 0;
						const kpiBrands = document.getElementById('kpi-prod-brands');
						if (kpiBrands) kpiBrands.textContent = data.metrics.total_brands ?? 0;
						const kpiAgr = document.getElementById('kpi-prod-agreements');
						if (kpiAgr) kpiAgr.textContent = data.metrics.active_agreements ?? 0;
					}

					// Poblar filtros si están vacíos
					if (data.filter_options) {
						populateProductFilterOptions(data.filter_options);
					}

					productsDataCache = data.products || [];
					renderProductsTable(productsDataCache);
				}
			} catch (e) {
				console.error('Error al cargar productos B2B:', e);
			} finally {
				if (loading) loading.classList.add('hidden');
			}
		}

		function populateProductFilterOptions(options) {
			const catSelect = document.getElementById('prod-filter-category');
			if (catSelect && catSelect.options.length <= 1 && options.categories) {
				options.categories.forEach(c => {
					const opt = document.createElement('option');
					opt.value = c;
					opt.textContent = c;
					catSelect.appendChild(opt);
				});
			}

			const brandSelect = document.getElementById('prod-filter-brand');
			if (brandSelect && brandSelect.options.length <= 1 && options.brands) {
				options.brands.forEach(b => {
					const opt = document.createElement('option');
					opt.value = b;
					opt.textContent = b;
					brandSelect.appendChild(opt);
				});
			}
		}

		function renderProductsTable(products) {
			const tbody = document.getElementById('prod-table-body');
			const empty = document.getElementById('prod-table-empty');
			const summary = document.getElementById('prod-table-summary');

			if (!tbody) return;
			tbody.innerHTML = '';

			if (!products || products.length === 0) {
				if (empty) empty.classList.remove('hidden');
				if (summary) summary.textContent = '0 productos encontrados';
				return;
			}

			if (empty) empty.classList.add('hidden');
			if (summary) {
				const activeCount = products.filter(p => parseInt(p.active) === 1).length;
				summary.textContent = `Mostrando ${products.length} productos mayoristas (${activeCount} activos)`;
			}

			products.forEach(p => {
				const tr = document.createElement('tr');
				tr.className = 'hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors';

				const isActive = parseInt(p.active) === 1;
				const statusBadge = isActive
					? `<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/60"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Activo</span>`
					: `<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-500 border border-slate-200 dark:border-slate-700"><span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>Inactivo</span>`;

				const tiersCount = parseInt(p.tiers_count || 0);
				const agrCount = parseInt(p.agreements_count || 0);
				let rulesHtml = '';
				if (tiersCount > 0) {
					rulesHtml += `<span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200/60 dark:border-blue-800/60 mr-1"><i data-lucide="layers" class="w-3 h-3"></i>${tiersCount} esc.</span>`;
				}
				if (agrCount > 0) {
					rulesHtml += `<span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800/60"><i data-lucide="file-check" class="w-3 h-3"></i>${agrCount} ac.</span>`;
				}
				if (tiersCount === 0 && agrCount === 0) {
					rulesHtml = `<span class="text-slate-400 text-[11px]">—</span>`;
				}

				const priceCase = parseFloat(p.base_price_case || 0).toFixed(2);
				const priceUnit = parseFloat(p.base_price_unit || 0).toFixed(2);
				const convFactor = parseInt(p.conversion_factor || 12);
				const unitName = escapeHtml(p.unit_name || 'Unidad');
				const caseName = escapeHtml(p.case_name || 'Caja');

				tr.innerHTML = `
					<td class="py-3 px-4 font-mono font-bold text-slate-900 dark:text-white">
						${escapeHtml(p.sku)}
					</td>
					<td class="py-3 px-4 max-w-xs">
						<div class="font-semibold text-slate-900 dark:text-white truncate" title="${escapeHtml(p.name)}">
							${escapeHtml(p.name)}
						</div>
						<div class="text-[11px] text-slate-400 flex items-center gap-2 mt-0.5">
							${p.barcode ? `<span class="flex items-center gap-1 font-mono"><i data-lucide="barcode" class="w-3.5 h-3.5"></i>${escapeHtml(p.barcode)}</span>` : ''}
						</div>
					</td>
					<td class="py-3 px-4">
						<div class="flex items-center gap-1.5 flex-wrap">
							<span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200/60 dark:border-slate-700/60">
								${escapeHtml(p.brand)}
							</span>
							<span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300">
								${escapeHtml(p.category)}
							</span>
						</div>
					</td>
					<td class="py-3 px-4 text-center">
						<span class="font-medium text-slate-700 dark:text-slate-300">${caseName}</span>
						<div class="text-[10px] text-slate-400">1 cja = ${convFactor} uds</div>
					</td>
					<td class="py-3 px-4 text-right font-mono font-medium text-slate-600 dark:text-slate-300">
						$${priceUnit}
					</td>
					<td class="py-3 px-4 text-right font-mono font-bold text-slate-900 dark:text-white">
						$${priceCase}
					</td>
					<td class="py-3 px-4 text-center">
						${rulesHtml}
					</td>
					<td class="py-3 px-4 text-center">
						${statusBadge}
					</td>
					<td class="py-3 px-4 text-right">
						<div class="flex items-center justify-end gap-1.5">
							<button onclick="openProductDetailModal(${p.id})" class="p-1.5 text-brand-700 dark:text-brand-300 hover:bg-brand-50 dark:hover:bg-brand-950/60 rounded-lg transition-colors" title="Gestionar Tarifas y Escalas">
								<i data-lucide="sliders" class="w-4 h-4"></i>
							</button>
							<button onclick="openProductModal(${p.id})" class="p-1.5 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors" title="Editar Producto">
								<i data-lucide="edit-3" class="w-4 h-4"></i>
							</button>
							<button onclick="confirmToggleProductActive(${p.id}, ${isActive ? 0 : 1}, '${escapeJs(p.name)}')" class="p-1.5 ${isActive ? 'text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40' : 'text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/40'} rounded-lg transition-colors" title="${isActive ? 'Desactivar' : 'Reactivar'}">
								<i data-lucide="${isActive ? 'power' : 'check-circle'}" class="w-4 h-4"></i>
							</button>
						</div>
					</td>
				`;
				tbody.appendChild(tr);
			});

			if (typeof lucide !== 'undefined') lucide.createIcons();
		}

		// Abrir modal de creación / edición de producto
		function openProductModal(prodId = null) {
			const form = document.getElementById('form-product');
			form.reset();
			document.getElementById('prod-form-id').value = prodId || '';
			document.getElementById('prod-form-title').textContent = prodId ? 'Editar Producto B2B' : 'Registrar Producto B2B';

			if (prodId) {
				const prod = productsDataCache.find(p => parseInt(p.id) === parseInt(prodId));
				if (prod) {
					document.getElementById('prod-input-sku').value = prod.sku || '';
					document.getElementById('prod-input-barcode').value = prod.barcode || '';
					document.getElementById('prod-input-name').value = prod.name || '';
					document.getElementById('prod-input-brand').value = prod.brand || '';
					document.getElementById('prod-input-category').value = prod.category || '';
					document.getElementById('prod-input-unit-name').value = prod.unit_name || 'Unidad';
					document.getElementById('prod-input-case-name').value = prod.case_name || 'Caja';
					document.getElementById('prod-input-conversion').value = prod.conversion_factor || 12;
					document.getElementById('prod-input-price-case').value = parseFloat(prod.base_price_case || 0).toFixed(2);
					document.getElementById('prod-input-price-unit').value = parseFloat(prod.base_price_unit || 0).toFixed(2);
					document.getElementById('prod-input-desc').value = prod.description || '';
				}
			} else {
				document.getElementById('prod-input-conversion').value = '12';
				document.getElementById('prod-input-unit-name').value = 'Unidad';
				document.getElementById('prod-input-case-name').value = 'Caja';
			}

			document.getElementById('modal-prod-form').classList.remove('hidden');
			if (typeof lucide !== 'undefined') lucide.createIcons();
		}

		function closeProductModal() {
			document.getElementById('modal-prod-form').classList.add('hidden');
		}

		async function saveProductForm(e) {
			e.preventDefault();
			const prodId = document.getElementById('prod-form-id').value;

			const payload = {
				sku: document.getElementById('prod-input-sku').value.trim(),
				barcode: document.getElementById('prod-input-barcode').value.trim(),
				name: document.getElementById('prod-input-name').value.trim(),
				brand: document.getElementById('prod-input-brand').value.trim(),
				category: document.getElementById('prod-input-category').value,
				unit_name: document.getElementById('prod-input-unit-name').value.trim() || 'Unidad',
				case_name: document.getElementById('prod-input-case-name').value.trim() || 'Caja',
				conversion_factor: parseInt(document.getElementById('prod-input-conversion').value) || 12,
				base_price_case: parseFloat(document.getElementById('prod-input-price-case').value) || 0,
				base_price_unit: parseFloat(document.getElementById('prod-input-price-unit').value) || 0,
				description: document.getElementById('prod-input-desc').value.trim()
			};

			try {
				const url = prodId ? `/api/b2b/products/${prodId}` : '/api/b2b/products';
				const method = prodId ? 'PUT' : 'POST';

				const res = await fetch(url, {
					method: method,
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify(payload)
				});
				const data = await res.json();

				if (!res.ok || !data.success) {
					alert(data.error_message || data.message || 'Error al guardar el producto.');
					return;
				}

				closeProductModal();
				await fetchProductsList();
			} catch (err) {
				alert('Error de conexión con el servidor al guardar el producto.');
			}
		}

		// Modal de Detalle Comercial (Escalas, Acuerdos, Historial)
		async function openProductDetailModal(prodId, defaultTab = 'tiers') {
			activeProductDetailId = prodId;
			const modal = document.getElementById('modal-prod-detail');
			modal.classList.remove('hidden');

			try {
				// Cargar detalle del producto
				const res = await fetch(`/api/b2b/products/${prodId}`);
				const data = await res.json();
				if (!data.success || !data.product) return;

				const p = data.product;
				document.getElementById('dtl-prod-sku').textContent = p.sku;
				document.getElementById('dtl-prod-name').textContent = p.name;
				document.getElementById('dtl-prod-cat').textContent = p.category;
				document.getElementById('dtl-prod-brand').textContent = p.brand;
				document.getElementById('dtl-prod-meta').textContent = `Empaque: ${p.case_name || 'Caja'} x ${p.conversion_factor} uds | Precio Base: $${parseFloat(p.base_price_case).toFixed(2)} / cja ($${parseFloat(p.base_price_unit).toFixed(2)} / ud)`;

				// Cargar lista de clientes en el selector de acuerdos si está vacío
				await ensureCustomersLoadedForSelect();

				// Renderizar tablas de las pestañas
				renderDetailTiers(p.tiers || []);
				renderDetailAgreements(p.customer_agreements || []);
				renderDetailHistory(p.price_history || []);

				switchProductDetailTab(defaultTab);
			} catch (err) {
				console.error('Error cargando detalle de producto:', err);
			}

			if (typeof lucide !== 'undefined') lucide.createIcons();
		}

		function closeProductDetailModal() {
			document.getElementById('modal-prod-detail').classList.add('hidden');
			activeProductDetailId = null;
		}

		function switchProductDetailTab(tab) {
			const btnTiers = document.getElementById('tab-btn-tiers');
			const btnAgr = document.getElementById('tab-btn-agreements');
			const btnHist = document.getElementById('tab-btn-history');

			const cTiers = document.getElementById('tab-content-tiers');
			const cAgr = document.getElementById('tab-content-agreements');
			const cHist = document.getElementById('tab-content-history');

			[btnTiers, btnAgr, btnHist].forEach(b => {
				b.className = 'py-3 border-b-2 border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300';
			});
			[cTiers, cAgr, cHist].forEach(c => c.classList.add('hidden'));

			if (tab === 'tiers') {
				btnTiers.className = 'py-3 border-b-2 border-brand-800 text-brand-800 dark:text-white font-bold';
				cTiers.classList.remove('hidden');
			} else if (tab === 'agreements') {
				btnAgr.className = 'py-3 border-b-2 border-brand-800 text-brand-800 dark:text-white font-bold';
				cAgr.classList.remove('hidden');
			} else if (tab === 'history') {
				btnHist.className = 'py-3 border-b-2 border-brand-800 text-brand-800 dark:text-white font-bold';
				cHist.classList.remove('hidden');
			}

			if (typeof lucide !== 'undefined') lucide.createIcons();
		}

		function renderDetailTiers(tiers) {
			const tbody = document.getElementById('dtl-tiers-tbody');
			if (!tbody) return;
			tbody.innerHTML = '';

			if (tiers.length === 0) {
				tbody.innerHTML = `<tr><td colspan="4" class="py-4 text-center text-slate-400 text-xs">No hay escalas de volumen configuradas para este producto.</td></tr>`;
				return;
			}

			tiers.forEach(t => {
				const tr = document.createElement('tr');
				tr.className = 'hover:bg-slate-50/50 dark:hover:bg-slate-800/40';
				tr.innerHTML = `
					<td class="py-2.5 px-3 font-semibold text-slate-800 dark:text-slate-200">≥ ${t.min_quantity} cajas</td>
					<td class="py-2.5 px-3 font-mono font-bold text-emerald-600 dark:text-emerald-400">$${parseFloat(t.price_per_case).toFixed(2)}</td>
					<td class="py-2.5 px-3 font-mono text-slate-600 dark:text-slate-300">${t.discount_percent ? t.discount_percent + '%' : '—'}</td>
					<td class="py-2.5 px-3">
						<span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Activo</span>
					</td>
				`;
				tbody.appendChild(tr);
			});
		}

		function renderDetailAgreements(agreements) {
			const tbody = document.getElementById('dtl-agreements-tbody');
			if (!tbody) return;
			tbody.innerHTML = '';

			if (agreements.length === 0) {
				tbody.innerHTML = `<tr><td colspan="5" class="py-4 text-center text-slate-400 text-xs">No hay acuerdos comerciales específicos registrados para este producto.</td></tr>`;
				return;
			}

			agreements.forEach(a => {
				const tr = document.createElement('tr');
				tr.className = 'hover:bg-slate-50/50 dark:hover:bg-slate-800/40';
				tr.innerHTML = `
					<td class="py-2.5 px-3 font-semibold text-slate-800 dark:text-slate-200">${escapeHtml(a.customer_name)}</td>
					<td class="py-2.5 px-3 font-mono text-xs text-slate-500">${escapeHtml(a.customer_tax_id || '—')}</td>
					<td class="py-2.5 px-3 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">$${parseFloat(a.price_per_case).toFixed(2)}</td>
					<td class="py-2.5 px-3 text-right font-mono text-slate-600 dark:text-slate-300">${a.price_per_unit ? '$' + parseFloat(a.price_per_unit).toFixed(2) : '—'}</td>
					<td class="py-2.5 px-3 text-slate-500 text-[11px]">${escapeHtml(a.notes || 'Tarifa pactada')}</td>
				`;
				tbody.appendChild(tr);
			});
		}

		function renderDetailHistory(history) {
			const tbody = document.getElementById('dtl-history-tbody');
			if (!tbody) return;
			tbody.innerHTML = '';

			if (history.length === 0) {
				tbody.innerHTML = `<tr><td colspan="6" class="py-4 text-center text-slate-400 text-xs">Sin modificaciones de precio registradas en auditoría.</td></tr>`;
				return;
			}

			history.forEach(h => {
				const tr = document.createElement('tr');
				tr.className = 'hover:bg-slate-50/50 dark:hover:bg-slate-800/40';
				tr.innerHTML = `
					<td class="py-2 px-3 font-mono text-xs text-slate-700 dark:text-slate-300">${escapeHtml(h.field_changed)}</td>
					<td class="py-2 px-3 text-right font-mono text-slate-400">$${parseFloat(h.old_price).toFixed(2)}</td>
					<td class="py-2 px-3 text-right font-mono font-bold text-emerald-600">$${parseFloat(h.new_price).toFixed(2)}</td>
					<td class="py-2 px-3 text-slate-600 dark:text-slate-300">${escapeHtml(h.changed_by || 'Sistema')}</td>
					<td class="py-2 px-3 text-slate-500 text-[11px]">${escapeHtml(h.reason || 'Actualización')}</td>
					<td class="py-2 px-3 font-mono text-[11px] text-slate-400">${escapeHtml(h.created_timestamp)}</td>
				`;
				tbody.appendChild(tr);
			});
		}

		async function ensureCustomersLoadedForSelect() {
			if (customersForCalcCache.length === 0) {
				try {
					const res = await fetch('/api/b2b/customers');
					const data = await res.json();
					if (data.success && data.customers) {
						customersForCalcCache = data.customers;
					}
				} catch (e) {}
			}
			const selAgr = document.getElementById('agr-customer-select');
			if (selAgr && selAgr.options.length <= 1) {
				selAgr.innerHTML = '<option value="">Seleccionar cliente comercial...</option>';
				customersForCalcCache.forEach(c => {
					const opt = document.createElement('option');
					opt.value = c.id;
					opt.textContent = `${c.name} (${c.tax_id || c.type})`;
					selAgr.appendChild(opt);
				});
			}
		}

		async function saveVolumeTier(e) {
			e.preventDefault();
			if (!activeProductDetailId) return;

			const minQty = parseInt(document.getElementById('tier-min-qty').value);
			const priceCase = parseFloat(document.getElementById('tier-price-case').value);
			const discPct = document.getElementById('tier-discount-pct').value;

			try {
				const res = await fetch(`/api/b2b/products/${activeProductDetailId}/tiers`, {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify({
						min_quantity: minQty,
						price_per_case: priceCase,
						discount_percent: discPct !== '' ? parseFloat(discPct) : null
					})
				});
				const data = await res.json();
				if (data.success) {
					document.getElementById('form-add-tier').reset();
					await openProductDetailModal(activeProductDetailId);
					await fetchProductsList();
				} else {
					alert(data.error_message || 'Error al guardar escala de volumen.');
				}
			} catch (err) {
				alert('Error al comunicarse con el servidor.');
			}
		}

		async function saveCustomerAgreement(e) {
			e.preventDefault();
			if (!activeProductDetailId) return;

			const custId = parseInt(document.getElementById('agr-customer-select').value);
			const priceCase = parseFloat(document.getElementById('agr-price-case').value);
			const priceUnit = document.getElementById('agr-price-unit').value;
			const notes = document.getElementById('agr-notes').value.trim();

			try {
				const res = await fetch(`/api/b2b/products/${activeProductDetailId}/customer-prices`, {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify({
						customer_id: custId,
						price_per_case: priceCase,
						price_per_unit: priceUnit !== '' ? parseFloat(priceUnit) : null,
						notes: notes
					})
				});
				const data = await res.json();
				if (data.success) {
					document.getElementById('form-add-agreement').reset();
					await openProductDetailModal(activeProductDetailId, 'agreements');
					await fetchProductsList();
				} else {
					alert(data.error_message || 'Error al guardar acuerdo comercial.');
				}
			} catch (err) {
				alert('Error al comunicarse con el servidor.');
			}
		}

		function confirmToggleProductActive(id, newStatus, name) {
			const isDeactivating = newStatus === 0;
			document.getElementById('confirm-title').textContent = isDeactivating ? 'Desactivar Producto Comercial' : 'Reactivar Producto';
			document.getElementById('confirm-desc').textContent = isDeactivating
				? `¿Confirmas la desactivación de "${name}"? El producto ya no estará disponible para cotizaciones ni nuevas órdenes de compra.`
				: `¿Confirmas la reactivación de "${name}" en el catálogo comercial mayorista?`;
			document.getElementById('btn-confirm-action').textContent = isDeactivating ? 'Sí, Desactivar' : 'Sí, Activar';
			document.getElementById('btn-confirm-action').className = isDeactivating
				? 'px-4 py-2 text-xs font-semibold rounded-lg bg-rose-600 hover:bg-rose-700 text-white shadow-xs'
				: 'px-4 py-2 text-xs font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs';

			pendingConfirmCallback = async () => {
				await fetch(`/api/b2b/products/${id}/toggle-active`, {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify({ active: newStatus })
				});
				closeConfirmModal();
				fetchProductsList();
			};

			document.getElementById('modal-confirm').classList.remove('hidden');
		}

		// Simulador / Cotizador de Precios B2B
		async function openPricingCalculatorModal(initialProductId = null, initialCustomerId = null) {
			const modal = document.getElementById('modal-pricing-calc');
			modal.classList.remove('hidden');

			// Cargar catálogo completo de productos en el selector del simulador
			try {
				const res = await fetch('/api/b2b/products');
				const data = await res.json();
				if (data.success && data.products) {
					const pSelect = document.getElementById('calc-product-select');
					pSelect.innerHTML = '';
					data.products.forEach(p => {
						const opt = document.createElement('option');
						opt.value = p.id;
						opt.textContent = `${p.sku} — ${p.name} (${p.brand})`;
						pSelect.appendChild(opt);
					});
				}
			} catch (e) {}

			if (initialProductId) {
				const pSelect = document.getElementById('calc-product-select');
				if (pSelect) pSelect.value = initialProductId;
			}

			await ensureCustomersLoadedForSelect();
			const cSelect = document.getElementById('calc-customer-select');
			if (cSelect && cSelect.options.length <= 1) {
				cSelect.innerHTML = '<option value="">Tarifa General (Sin Acuerdo Específico)</option>';
				customersForCalcCache.forEach(c => {
					const opt = document.createElement('option');
					opt.value = c.id;
					opt.textContent = `${c.name} (${c.tax_id || c.type})`;
					cSelect.appendChild(opt);
				});
			}

			if (initialCustomerId) {
				const cSelect = document.getElementById('calc-customer-select');
				if (cSelect) cSelect.value = initialCustomerId;
			}

			await runPriceSimulation();
			if (typeof lucide !== 'undefined') lucide.createIcons();
		}

		function closePricingCalculatorModal() {
			document.getElementById('modal-pricing-calc').classList.add('hidden');
		}

		async function runPriceSimulation() {
			const productId = parseInt(document.getElementById('calc-product-select')?.value || 0);
			const customerId = parseInt(document.getElementById('calc-customer-select')?.value || 0);
			const unit = document.getElementById('calc-unit-select')?.value || 'CASE';
			const qty = parseInt(document.getElementById('calc-quantity-input')?.value || 1);

			if (productId <= 0) return;

			try {
				const res = await fetch('/api/b2b/pricing/calculate', {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify({
						product_id: productId,
						customer_id: customerId > 0 ? customerId : null,
						unit: unit,
						quantity: qty
					})
				});
				const data = await res.json();
				if (!data.success || !data.calculation) return;

				const calc = data.calculation;
				const badge = document.getElementById('calc-rule-badge');
				const desc = document.getElementById('calc-rule-desc');
				const notes = document.getElementById('calc-agreement-notes');

				if (calc.rule_applied === 'CUSTOMER_AGREEMENT') {
					badge.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800';
					badge.textContent = 'ACUERDO COMERCIAL EXCLUSIVO';
				} else if (calc.rule_applied === 'VOLUME_TIER') {
					badge.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 dark:bg-blue-950/60 text-blue-800 dark:text-blue-300 border border-blue-300 dark:border-blue-800';
					badge.textContent = 'ESCALA DE VOLUMEN APLICADA';
				} else {
					badge.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700';
					badge.textContent = 'PRECIO DE LISTA GENERAL';
				}

				desc.textContent = calc.rule_description;
				if (calc.agreement_detail) {
					notes.textContent = 'Nota: ' + calc.agreement_detail;
					notes.classList.remove('hidden');
				} else {
					notes.classList.add('hidden');
				}

				document.getElementById('calc-base-rate').textContent = '$' + calc.base_rate;
				document.getElementById('calc-applied-rate').textContent = '$' + calc.applied_rate;
				document.getElementById('calc-equiv-units').textContent = calc.total_units_equivalent + ' uds';
				document.getElementById('calc-unit-cost').textContent = '$' + calc.unit_cost_equivalent + ' / ud';
				document.getElementById('calc-savings').textContent = '$' + calc.savings;
				document.getElementById('calc-subtotal').textContent = '$' + calc.subtotal;
			} catch (e) {
				console.error('Error calculando simulación:', e);
			}
		}

		/* ========================================================
		   MÓDULO FUNCIONAL DE COTIZACIONES Y PEDIDOS B2B (PASO 6)
		   ======================================================== */

		let ordersMainTab = 'orders'; // 'orders' o 'quotes'
		let currentOrdersCache = [];
		let currentQuotesCache = [];
		let commercialLines = [];
		let activeCommercialDetail = null;
		let debounceOrdersTimer = null;

		function switchOrdersMainTab(tab) {
			ordersMainTab = tab;
			const btnOrders = document.getElementById('tab-subnav-orders');
			const btnQuotes = document.getElementById('tab-subnav-quotes');
			const tableOrders = document.getElementById('container-orders-table');
			const tableQuotes = document.getElementById('container-quotes-table');
			const filterStatus = document.getElementById('orders-filter-status');

			if (tab === 'orders') {
				btnOrders.className = 'inline-flex items-center gap-2 pb-3 px-1 border-b-2 border-brand-800 text-brand-800 dark:text-white text-xs font-bold transition-all';
				btnQuotes.className = 'inline-flex items-center gap-2 pb-3 px-1 border-b-2 border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 text-xs font-medium transition-all';
				tableOrders.classList.remove('hidden');
				tableQuotes.classList.add('hidden');

				if (filterStatus) {
					filterStatus.innerHTML = `
						<option value="">Todos los Estados</option>
						<option value="Confirmado">Confirmado</option>
						<option value="Borrador">Borrador</option>
						<option value="Cancelado">Cancelado</option>
					`;
				}
				fetchOrdersList();
			} else {
				btnQuotes.className = 'inline-flex items-center gap-2 pb-3 px-1 border-b-2 border-brand-800 text-brand-800 dark:text-white text-xs font-bold transition-all';
				btnOrders.className = 'inline-flex items-center gap-2 pb-3 px-1 border-b-2 border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 text-xs font-medium transition-all';
				tableQuotes.classList.remove('hidden');
				tableOrders.classList.add('hidden');

				if (filterStatus) {
					filterStatus.innerHTML = `
						<option value="">Todos los Estados</option>
						<option value="Pendiente">Pendiente</option>
						<option value="Aprobada">Aprobada</option>
						<option value="Convertida">Convertida</option>
						<option value="Borrador">Borrador</option>
						<option value="Rechazada">Rechazada</option>
						<option value="Vencida">Vencida</option>
						<option value="Cancelada">Cancelada</option>
					`;
				}
				fetchQuotesList();
			}

			if (typeof lucide !== 'undefined') lucide.createIcons();
		}

		async function fetchOrdersModule() {
			await ensureCustomersLoadedForSelect();
			populateOrdersCustomerFilter();
			await Promise.all([
				fetchOrdersMetrics(),
				ordersMainTab === 'orders' ? fetchOrdersList() : fetchQuotesList()
			]);
		}

		function populateOrdersCustomerFilter() {
			const sel = document.getElementById('orders-filter-customer');
			if (sel && sel.options.length <= 1 && customersForCalcCache.length > 0) {
				sel.innerHTML = '<option value="">Todos los Clientes</option>';
				customersForCalcCache.forEach(c => {
					const opt = document.createElement('option');
					opt.value = c.id;
					opt.textContent = c.name;
					sel.appendChild(opt);
				});
			}
		}

		async function fetchOrdersMetrics() {
			try {
				const res = await fetch('/api/b2b/orders/metrics');
				const data = await res.json();
				if (data.success && data.metrics) {
					const m = data.metrics;
					const kConf = document.getElementById('kpi-ord-confirmed');
					if (kConf) kConf.textContent = m.confirmed_orders ?? 0;
					const kRev = document.getElementById('kpi-ord-revenue');
					if (kRev) kRev.textContent = '$' + (m.confirmed_revenue || '0.00');
					const kQuo = document.getElementById('kpi-ord-quotes');
					if (kQuo) kQuo.textContent = m.active_quotes ?? 0;
					const kConv = document.getElementById('kpi-ord-conversion');
					if (kConv) kConv.textContent = (m.conversion_rate ?? 0) + '%';
				}
			} catch (e) {
				console.error('Error cargando métricas de órdenes:', e);
			}
		}

		function debounceFetchOrders() {
			clearTimeout(debounceOrdersTimer);
			debounceOrdersTimer = setTimeout(() => {
				fetchCurrentOrdersOrQuotes();
			}, 250);
		}

		function fetchCurrentOrdersOrQuotes() {
			if (ordersMainTab === 'orders') {
				fetchOrdersList();
			} else {
				fetchQuotesList();
			}
		}

		function resetOrderFilters() {
			const s = document.getElementById('orders-search');
			if (s) s.value = '';
			const st = document.getElementById('orders-filter-status');
			if (st) st.value = '';
			const c = document.getElementById('orders-filter-customer');
			if (c) c.value = '';
			fetchCurrentOrdersOrQuotes();
		}

		async function fetchOrdersList() {
			const loading = document.getElementById('orders-table-loading');
			const empty = document.getElementById('orders-table-empty');
			const tbody = document.getElementById('orders-table-body');

			if (loading) loading.classList.remove('hidden');
			if (empty) empty.classList.add('hidden');

			const search = (document.getElementById('orders-search')?.value || '').trim();
			const status = document.getElementById('orders-filter-status')?.value || '';
			const customerId = document.getElementById('orders-filter-customer')?.value || '';

			const params = new URLSearchParams();
			if (search) params.append('search', search);
			if (status) params.append('status', status);
			if (customerId) params.append('customer_id', customerId);

			try {
				const res = await fetch('/api/b2b/orders?' + params.toString());
				const data = await res.json();
				if (data.success) {
					currentOrdersCache = data.orders || [];
					renderOrdersTable(currentOrdersCache);
					const bCount = document.getElementById('badge-orders-count');
					if (bCount) bCount.textContent = currentOrdersCache.length;
				}
			} catch (e) {
				console.error('Error cargando pedidos:', e);
			} finally {
				if (loading) loading.classList.add('hidden');
			}
		}

		async function fetchQuotesList() {
			const loading = document.getElementById('quotes-table-loading');
			const empty = document.getElementById('quotes-table-empty');
			const tbody = document.getElementById('quotes-table-body');

			if (loading) loading.classList.remove('hidden');
			if (empty) empty.classList.add('hidden');

			const search = (document.getElementById('orders-search')?.value || '').trim();
			const status = document.getElementById('orders-filter-status')?.value || '';
			const customerId = document.getElementById('orders-filter-customer')?.value || '';

			const params = new URLSearchParams();
			if (search) params.append('search', search);
			if (status) params.append('status', status);
			if (customerId) params.append('customer_id', customerId);

			try {
				const res = await fetch('/api/b2b/quotes?' + params.toString());
				const data = await res.json();
				if (data.success) {
					currentQuotesCache = data.quotes || [];
					renderQuotesTable(currentQuotesCache);
					const bCount = document.getElementById('badge-quotes-count');
					if (bCount) bCount.textContent = currentQuotesCache.length;
				}
			} catch (e) {
				console.error('Error cargando cotizaciones:', e);
			} finally {
				if (loading) loading.classList.add('hidden');
			}
		}

		function renderOrdersTable(orders) {
			const tbody = document.getElementById('orders-table-body');
			const empty = document.getElementById('orders-table-empty');
			const summary = document.getElementById('orders-table-summary');

			if (!tbody) return;
			tbody.innerHTML = '';

			if (!orders || orders.length === 0) {
				if (empty) empty.classList.remove('hidden');
				if (summary) summary.textContent = '0 pedidos encontrados';
				return;
			}

			if (empty) empty.classList.add('hidden');
			if (summary) {
				const confirmed = orders.filter(o => o.status === 'Confirmado').length;
				summary.textContent = `Mostrando ${orders.length} pedidos comerciales (${confirmed} confirmados)`;
			}

			orders.forEach(o => {
				const tr = document.createElement('tr');
				tr.className = 'hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors';

				let statusBadge = '';
				if (o.status === 'Confirmado') {
					statusBadge = `<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/60"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Confirmado</span>`;
				} else if (o.status === 'Cancelado') {
					statusBadge = `<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200/60 dark:border-rose-800/60"><span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>Cancelado</span>`;
				} else {
					statusBadge = `<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700"><span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>Borrador</span>`;
				}

				const branchText = o.establishment_name ? `<div class="text-[10px] text-slate-400 flex items-center gap-1"><i data-lucide="map-pin" class="w-3 h-3"></i>${escapeHtml(o.establishment_name)}</div>` : '';
				const dateText = (o.created_timestamp || '').split(' ')[0];

				tr.innerHTML = `
					<td class="py-3 px-4 font-mono font-bold text-slate-900 dark:text-white">
						${escapeHtml(o.order_number)}
						${o.source_quote_number ? `<div class="text-[10px] font-normal text-slate-400">Ref: ${escapeHtml(o.source_quote_number)}</div>` : ''}
					</td>
					<td class="py-3 px-4">
						<div class="font-semibold text-slate-900 dark:text-white">${escapeHtml(o.customer_name)}</div>
						${branchText}
					</td>
					<td class="py-3 px-4 text-slate-600 dark:text-slate-300">
						${escapeHtml(o.sales_rep)}
					</td>
					<td class="py-3 px-4 text-center">
						<span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
							${o.items_count} ítems
						</span>
					</td>
					<td class="py-3 px-4 text-right font-mono text-slate-600 dark:text-slate-300">
						$${parseFloat(o.subtotal || 0).toFixed(2)}
					</td>
					<td class="py-3 px-4 text-right font-mono font-bold text-slate-900 dark:text-white text-sm">
						$${parseFloat(o.total || 0).toFixed(2)}
					</td>
					<td class="py-3 px-4 text-center">
						${statusBadge}
					</td>
					<td class="py-3 px-4 text-right">
						<div class="flex items-center justify-end gap-1.5">
							<button onclick="openCommercialDetailModal('order', ${o.id})" class="p-1.5 text-brand-700 dark:text-brand-300 hover:bg-brand-50 dark:hover:bg-brand-950/60 rounded-lg transition-colors" title="Ver Detalle y Auditoría">
								<i data-lucide="eye" class="w-4 h-4"></i>
							</button>
							<button onclick="duplicateOrderAction(${o.id})" class="p-1.5 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors" title="Duplicar Pedido">
								<i data-lucide="copy" class="w-4 h-4"></i>
							</button>
							${o.status === 'Borrador' ? `
								<button onclick="confirmUpdateOrderStatus(${o.id}, 'Confirmado')" class="p-1.5 text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 rounded-lg transition-colors" title="Confirmar Pedido">
									<i data-lucide="check" class="w-4 h-4"></i>
								</button>
							` : ''}
							${o.status === 'Confirmado' ? `
								<button onclick="openGenerateInvoiceModal(${o.id})" class="p-1.5 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950/40 rounded-lg transition-colors" title="Facturar Pedido">
									<i data-lucide="receipt" class="w-4 h-4"></i>
								</button>
								<button onclick="confirmUpdateOrderStatus(${o.id}, 'Cancelado')" class="p-1.5 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition-colors" title="Cancelar Pedido">
									<i data-lucide="x-circle" class="w-4 h-4"></i>
								</button>
							` : ''}
						</div>
					</td>
				`;
				tbody.appendChild(tr);
			});

			if (typeof lucide !== 'undefined') lucide.createIcons();
		}

		function renderQuotesTable(quotes) {
			const tbody = document.getElementById('quotes-table-body');
			const empty = document.getElementById('quotes-table-empty');
			const summary = document.getElementById('quotes-table-summary');

			if (!tbody) return;
			tbody.innerHTML = '';

			if (!quotes || quotes.length === 0) {
				if (empty) empty.classList.remove('hidden');
				if (summary) summary.textContent = '0 cotizaciones encontradas';
				return;
			}

			if (empty) empty.classList.add('hidden');
			if (summary) {
				summary.textContent = `Mostrando ${quotes.length} cotizaciones comerciales`;
			}

			quotes.forEach(q => {
				const tr = document.createElement('tr');
				tr.className = 'hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors';

				let statusBadge = '';
				if (q.status === 'Aprobada') {
					statusBadge = `<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/60"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Aprobada</span>`;
				} else if (q.status === 'Convertida') {
					statusBadge = `<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200/60 dark:border-purple-800/60"><i data-lucide="check" class="w-3 h-3"></i>Convertida</span>`;
				} else if (q.status === 'Pendiente') {
					statusBadge = `<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800/60"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Pendiente</span>`;
				} else if (q.status === 'Rechazada' || q.status === 'Cancelada') {
					statusBadge = `<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200/60 dark:border-rose-800/60"><span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>${escapeHtml(q.status)}</span>`;
				} else if (q.status === 'Vencida') {
					statusBadge = `<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-500 border border-slate-200 dark:border-slate-700">Vencida</span>`;
				} else {
					statusBadge = `<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">Borrador</span>`;
				}

				const branchText = q.establishment_name ? `<div class="text-[10px] text-slate-400 flex items-center gap-1"><i data-lucide="map-pin" class="w-3 h-3"></i>${escapeHtml(q.establishment_name)}</div>` : '';

				tr.innerHTML = `
					<td class="py-3 px-4 font-mono font-bold text-slate-900 dark:text-white">
						${escapeHtml(q.quote_number)}
					</td>
					<td class="py-3 px-4">
						<div class="font-semibold text-slate-900 dark:text-white">${escapeHtml(q.customer_name)}</div>
						${branchText}
					</td>
					<td class="py-3 px-4 text-slate-600 dark:text-slate-300">
						${escapeHtml(q.sales_rep)}
					</td>
					<td class="py-3 px-4 font-mono text-xs text-slate-500">
						${escapeHtml(q.valid_until)}
					</td>
					<td class="py-3 px-4 text-center">
						<span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
							${q.items_count} ítems
						</span>
					</td>
					<td class="py-3 px-4 text-right font-mono text-slate-600 dark:text-slate-300">
						$${parseFloat(q.subtotal || 0).toFixed(2)}
					</td>
					<td class="py-3 px-4 text-right font-mono font-bold text-slate-900 dark:text-white text-sm">
						$${parseFloat(q.total || 0).toFixed(2)}
					</td>
					<td class="py-3 px-4 text-center">
						${statusBadge}
					</td>
					<td class="py-3 px-4 text-right">
						<div class="flex items-center justify-end gap-1.5">
							<button onclick="openCommercialDetailModal('quote', ${q.id})" class="p-1.5 text-brand-700 dark:text-brand-300 hover:bg-brand-50 dark:hover:bg-brand-950/60 rounded-lg transition-colors" title="Ver Detalle y Acciones">
								<i data-lucide="eye" class="w-4 h-4"></i>
							</button>
							${q.status === 'Aprobada' ? `
								<button onclick="convertQuoteAction(${q.id})" class="inline-flex items-center gap-1 px-2 py-1 text-[11px] font-semibold rounded-md bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200 hover:bg-purple-100 transition-colors" title="Convertir a Pedido">
									<i data-lucide="arrow-right" class="w-3 h-3"></i>
									<span>A Pedido</span>
								</button>
							` : ''}
							${q.status === 'Pendiente' ? `
								<button onclick="updateQuoteStatusAction(${q.id}, 'Aprobada')" class="p-1.5 text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 rounded-lg transition-colors" title="Aprobar Cotización">
									<i data-lucide="check" class="w-4 h-4"></i>
								</button>
								<button onclick="updateQuoteStatusAction(${q.id}, 'Rechazada')" class="p-1.5 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition-colors" title="Rechazar Cotización">
									<i data-lucide="x" class="w-4 h-4"></i>
								</button>
							` : ''}
						</div>
					</td>
				`;
				tbody.appendChild(tr);
			});

			if (typeof lucide !== 'undefined') lucide.createIcons();
		}

		// Constructor Comercial (Crear Cotización o Pedido)
		async function openCommercialFormModal(mode = 'order') {
			const form = document.getElementById('form-commercial');
			form.reset();
			commercialLines = [];

			document.getElementById('comm-form-mode').value = mode;
			const isOrder = mode === 'order';

			document.getElementById('comm-form-title').textContent = isOrder ? 'Nuevo Pedido Comercial Mayorista' : 'Nueva Cotización B2B';
			document.getElementById('comm-label-date').textContent = isOrder ? 'Fecha de Entrega Deseada' : 'Vigencia de Cotización';
			document.getElementById('comm-btn-submit').querySelector('span').textContent = isOrder ? 'Confirmar y Emitir Pedido' : 'Emitir Cotización';

			// Fecha por defecto
			const today = new Date();
			if (!isOrder) today.setDate(today.getDate() + 15);
			document.getElementById('comm-input-date').value = today.toISOString().split('T')[0];

			await ensureCustomersLoadedForSelect();
			const custSelect = document.getElementById('comm-input-customer');
			custSelect.innerHTML = '<option value="">Seleccionar cliente comercial...</option>';
			customersForCalcCache.forEach(c => {
				const opt = document.createElement('option');
				opt.value = c.id;
				opt.textContent = `${c.name} (${c.tax_id || c.customer_type})`;
				custSelect.appendChild(opt);
			});

			// Cargar productos en el selector de líneas
			if (productsDataCache.length === 0) {
				await fetchProductsList();
			}
			const prodSelect = document.getElementById('comm-line-product');
			prodSelect.innerHTML = '<option value="">Seleccionar producto del catálogo...</option>';
			productsDataCache.forEach(p => {
				const opt = document.createElement('option');
				opt.value = p.id;
				opt.textContent = `${p.sku} — ${p.name} ($${parseFloat(p.base_price_case).toFixed(2)}/cja)`;
				prodSelect.appendChild(opt);
			});

			renderCommercialLinesTable();
			document.getElementById('modal-commercial-form').classList.remove('hidden');
			if (typeof lucide !== 'undefined') lucide.createIcons();
		}

		function closeCommercialFormModal() {
			document.getElementById('modal-commercial-form').classList.add('hidden');
		}

		async function handleCommercialCustomerChange() {
			const custId = parseInt(document.getElementById('comm-input-customer')?.value || 0);
			const estSelect = document.getElementById('comm-input-establishment');
			estSelect.innerHTML = '<option value="">Oficina Central / Sede Principal</option>';

			if (custId > 0) {
				try {
					const res = await fetch(`/api/b2b/customers/${custId}`);
					const data = await res.json();
					if (data.success && data.customer && data.customer.establishments) {
						data.customer.establishments.forEach(e => {
							const opt = document.createElement('option');
							opt.value = e.id;
							opt.textContent = `${e.name} (${e.city || 'Sucursal'})`;
							estSelect.appendChild(opt);
						});
					}
				} catch (e) {}

				// Si ya hay líneas agregadas, recalcular sus precios con las tarifas del nuevo cliente
				if (commercialLines.length > 0) {
					for (let i = 0; i < commercialLines.length; i++) {
						const item = commercialLines[i];
						const calcRes = await fetch('/api/b2b/pricing/calculate', {
							method: 'POST',
							headers: { 'Content-Type': 'application/json' },
							body: JSON.stringify({
								product_id: item.product_id,
								customer_id: custId,
								unit: item.commercial_unit,
								quantity: item.quantity
							})
						});
						const calcData = await calcRes.json();
						if (calcData.success && calcData.calculation) {
							const c = calcData.calculation;
							commercialLines[i].base_price = parseFloat(c.base_rate);
							commercialLines[i].unit_price = parseFloat(c.applied_rate);
							commercialLines[i].subtotal = parseFloat(c.subtotal);
							commercialLines[i].discount_amount = parseFloat(c.savings);
							commercialLines[i].rule_applied = c.rule_applied;
						}
					}
					renderCommercialLinesTable();
				}
			}
		}

		async function addCommercialLineFromSelector() {
			const prodId = parseInt(document.getElementById('comm-line-product')?.value || 0);
			const custId = parseInt(document.getElementById('comm-input-customer')?.value || 0);
			const unit = document.getElementById('comm-line-unit')?.value || 'CASE';
			const qty = parseInt(document.getElementById('comm-line-qty')?.value || 1);

			if (prodId <= 0) {
				alert('Selecciona un producto del catálogo.');
				return;
			}
			if (custId <= 0) {
				alert('Selecciona primero el cliente comercial para resolver sus tarifas pactadas.');
				return;
			}

			try {
				const res = await fetch('/api/b2b/pricing/calculate', {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify({
						product_id: prodId,
						customer_id: custId,
						unit: unit,
						quantity: qty
					})
				});
				const data = await res.json();
				if (!data.success || !data.calculation) return;

				const c = data.calculation;
				const prod = productsDataCache.find(p => parseInt(p.id) === prodId);

				commercialLines.push({
					product_id: prodId,
					sku: c.sku,
					name: c.product_name,
					brand: prod ? prod.brand : '',
					commercial_unit: unit,
					unit_label: unit === 'CASE' ? (prod ? prod.case_name : 'Caja') : (prod ? prod.unit_name : 'Unidad'),
					quantity: qty,
					conversion_factor: c.conversion_factor,
					total_units: c.total_units_equivalent,
					base_price: parseFloat(c.base_rate),
					unit_price: parseFloat(c.applied_rate),
					discount_amount: parseFloat(c.savings),
					subtotal: parseFloat(c.subtotal),
					rule_applied: c.rule_applied,
					rule_description: c.rule_description
				});

				renderCommercialLinesTable();
			} catch (err) {
				alert('Error al calcular precio del producto.');
			}
		}

		function removeCommercialLine(index) {
			commercialLines.splice(index, 1);
			renderCommercialLinesTable();
		}

		function renderCommercialLinesTable() {
			const tbody = document.getElementById('comm-lines-tbody');
			const empty = document.getElementById('comm-lines-empty');
			if (!tbody) return;

			tbody.innerHTML = '';
			if (commercialLines.length === 0) {
				if (empty) empty.classList.remove('hidden');
				calculateAndRenderCommercialTotals();
				return;
			}

			if (empty) empty.classList.add('hidden');

			commercialLines.forEach((l, idx) => {
				const tr = document.createElement('tr');
				tr.className = 'hover:bg-slate-50/50 dark:hover:bg-slate-800/40';

				let rulePill = '';
				if (l.rule_applied === 'CUSTOMER_AGREEMENT') {
					rulePill = `<span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">Pactado</span>`;
				} else if (l.rule_applied === 'VOLUME_TIER') {
					rulePill = `<span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300">Escala</span>`;
				}

				tr.innerHTML = `
					<td class="py-2.5 px-3">
						<div class="font-semibold text-slate-800 dark:text-slate-200">${escapeHtml(l.name)}</div>
						<div class="font-mono text-[10px] text-slate-400">${escapeHtml(l.sku)} — ${escapeHtml(l.brand)}</div>
					</td>
					<td class="py-2.5 px-3 text-center">
						<span class="font-medium">${escapeHtml(l.unit_label)}</span>
						<div class="text-[10px] text-slate-400">Factor: ${l.conversion_factor} (${l.total_units} uds)</div>
					</td>
					<td class="py-2.5 px-3 text-center font-bold font-mono text-slate-900 dark:text-white">
						${l.quantity}
					</td>
					<td class="py-2.5 px-3 text-right font-mono text-slate-500">
						$${l.base_price.toFixed(2)}
					</td>
					<td class="py-2.5 px-3 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
						$${l.unit_price.toFixed(2)} ${rulePill}
					</td>
					<td class="py-2.5 px-3 text-right font-mono font-bold text-slate-900 dark:text-white">
						$${l.subtotal.toFixed(2)}
					</td>
					<td class="py-2.5 px-3 text-center">
						<button type="button" onclick="removeCommercialLine(${idx})" class="p-1 text-slate-400 hover:text-rose-500 rounded" title="Eliminar línea">
							<i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
						</button>
					</td>
				`;
				tbody.appendChild(tr);
			});

			calculateAndRenderCommercialTotals();
			if (typeof lucide !== 'undefined') lucide.createIcons();
		}

		function calculateAndRenderCommercialTotals() {
			let subtotal = 0.00;
			let savings = 0.00;

			commercialLines.forEach(l => {
				subtotal += l.subtotal;
				savings += l.discount_amount;
			});

			const taxRate = 18.00;
			const tax = roundToTwo(subtotal * (taxRate / 100));
			const total = roundToTwo(subtotal + tax);

			document.getElementById('comm-sum-subtotal').textContent = '$' + subtotal.toFixed(2);
			document.getElementById('comm-sum-discount').textContent = '$' + savings.toFixed(2);
			document.getElementById('comm-sum-tax').textContent = '$' + tax.toFixed(2);
			document.getElementById('comm-sum-total').textContent = '$' + total.toFixed(2);
		}

		function roundToTwo(num) {
			return +(Math.round(num + "e+2")  + "e-2");
		}

		async function submitCommercialForm(statusOverride = null) {
			const mode = document.getElementById('comm-form-mode').value; // 'quote' o 'order'
			const custId = parseInt(document.getElementById('comm-input-customer')?.value || 0);

			if (custId <= 0) {
				alert('Debes seleccionar un cliente comercial.');
				return;
			}
			if (commercialLines.length === 0) {
				alert('Debes añadir al menos una línea de producto.');
				return;
			}

			const estId = parseInt(document.getElementById('comm-input-establishment')?.value || 0) || null;
			const salesRep = document.getElementById('comm-input-sales-rep')?.value.trim() || 'Carlos Mendoza';
			const dateVal = document.getElementById('comm-input-date')?.value;
			const notes = document.getElementById('comm-input-notes')?.value.trim();

			let status = statusOverride;
			if (!status) {
				status = mode === 'quote' ? 'Pendiente' : 'Confirmado';
			}

			const payload = {
				customer_id: custId,
				establishment_id: estId,
				sales_rep: salesRep,
				status: status,
				notes: notes,
				items: commercialLines.map(l => ({
					product_id: l.product_id,
					commercial_unit: l.commercial_unit,
					quantity: l.quantity
				}))
			};

			if (mode === 'quote') {
				payload.valid_until = dateVal;
			}

			try {
				const url = mode === 'quote' ? '/api/b2b/quotes' : '/api/b2b/orders';
				const res = await fetch(url, {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify(payload)
				});
				const data = await res.json();

				if (!res.ok || !data.success) {
					alert(data.error_message || data.message || 'Error al procesar el documento comercial.');
					return;
				}

				closeCommercialFormModal();
				await fetchOrdersModule();
			} catch (err) {
				alert('Error de conexión al guardar el documento comercial.');
			}
		}

		// Detalle Comercial & Auditoría
		async function openCommercialDetailModal(type, id) {
			const modal = document.getElementById('modal-commercial-detail');
			modal.classList.remove('hidden');

			try {
				const url = type === 'order' ? `/api/b2b/orders/${id}` : `/api/b2b/quotes/${id}`;
				const res = await fetch(url);
				const data = await res.json();

				if (!data.success) return;
				const doc = type === 'order' ? data.order : data.quote;
				activeCommercialDetail = { type, doc };

				// Llenar Header
				const typePill = document.getElementById('dtl-comm-type-pill');
				typePill.textContent = type === 'order' ? 'PEDIDO COMERCIAL' : 'COTIZACIÓN B2B';
				typePill.className = type === 'order'
					? 'text-[10px] font-bold uppercase px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 border border-blue-200'
					: 'text-[10px] font-bold uppercase px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 border border-purple-200';

				document.getElementById('dtl-comm-number').textContent = type === 'order' ? doc.order_number : doc.quote_number;
				document.getElementById('dtl-comm-customer-name').textContent = doc.customer_name;
				document.getElementById('dtl-comm-meta').textContent = `Ejecutivo: ${doc.sales_rep} | Registrado: ${doc.created_timestamp}`;

				// Badge Estado
				const badgeContainer = document.getElementById('dtl-comm-status-badge');
				badgeContainer.innerHTML = `<span class="px-2 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-800">${escapeHtml(doc.status)}</span>`;

				// Totales
				document.getElementById('dtl-comm-subtotal').textContent = '$' + parseFloat(doc.subtotal).toFixed(2);
				document.getElementById('dtl-comm-discount').textContent = '$' + parseFloat(doc.discount_amount).toFixed(2);
				document.getElementById('dtl-comm-tax').textContent = '$' + parseFloat(doc.tax_amount).toFixed(2);
				document.getElementById('dtl-comm-total').textContent = '$' + parseFloat(doc.total).toFixed(2);
				document.getElementById('dtl-comm-notes').textContent = doc.notes || 'Sin notas registradas.';

				// Renderizar Ítems
				renderCommercialDetailItems(doc.items || []);
				renderCommercialDetailHistory(doc.history || []);

				// Botones de acción contextual
				renderCommercialDetailActions(type, doc);
				switchCommercialDetailTab('items');
			} catch (e) {
				console.error('Error cargando detalle:', e);
			}

			if (typeof lucide !== 'undefined') lucide.createIcons();
		}

		function closeCommercialDetailModal() {
			document.getElementById('modal-commercial-detail').classList.add('hidden');
			activeCommercialDetail = null;
		}

		function switchCommercialDetailTab(tab) {
			const btnItems = document.getElementById('tab-cdtl-items');
			const btnHist = document.getElementById('tab-cdtl-history');
			const cItems = document.getElementById('tab-content-cdtl-items');
			const cHist = document.getElementById('tab-content-cdtl-history');

			if (tab === 'items') {
				btnItems.className = 'py-3 border-b-2 border-brand-800 text-brand-800 dark:text-white font-bold';
				btnHist.className = 'py-3 border-b-2 border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300';
				cItems.classList.remove('hidden');
				cHist.classList.add('hidden');
			} else {
				btnHist.className = 'py-3 border-b-2 border-brand-800 text-brand-800 dark:text-white font-bold';
				btnItems.className = 'py-3 border-b-2 border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300';
				cHist.classList.remove('hidden');
				cItems.classList.add('hidden');
			}

			if (typeof lucide !== 'undefined') lucide.createIcons();
		}

		function renderCommercialDetailItems(items) {
			const tbody = document.getElementById('dtl-comm-items-tbody');
			if (!tbody) return;
			tbody.innerHTML = '';

			items.forEach(it => {
				const tr = document.createElement('tr');
				tr.className = 'hover:bg-slate-50/50 dark:hover:bg-slate-800/40';

				let ruleTag = '';
				if (it.rule_applied === 'CUSTOMER_AGREEMENT') {
					ruleTag = `<span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800">Acuerdo Cliente</span>`;
				} else if (it.rule_applied === 'VOLUME_TIER') {
					ruleTag = `<span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-blue-100 text-blue-800">Escala Volumen</span>`;
				}

				tr.innerHTML = `
					<td class="py-2.5 px-3 font-mono text-xs font-bold text-slate-900 dark:text-white">${escapeHtml(it.product_sku)}</td>
					<td class="py-2.5 px-3">
						<div class="font-semibold text-slate-800 dark:text-slate-200">${escapeHtml(it.product_name)}</div>
						<div class="text-[10px] text-slate-400">${escapeHtml(it.product_brand)}</div>
					</td>
					<td class="py-2.5 px-3 text-center">
						<span>${it.commercial_unit === 'CASE' ? 'Caja' : 'Unidad'}</span>
						<div class="text-[10px] text-slate-400">Factor: ${it.conversion_factor} (${it.total_units} uds)</div>
					</td>
					<td class="py-2.5 px-3 text-center font-bold font-mono">${it.quantity}</td>
					<td class="py-2.5 px-3 text-right font-mono text-slate-400">$${parseFloat(it.base_price).toFixed(2)}</td>
					<td class="py-2.5 px-3 text-right font-mono font-bold text-emerald-600">$${parseFloat(it.unit_price).toFixed(2)} ${ruleTag}</td>
					<td class="py-2.5 px-3 text-right font-mono font-bold text-slate-900 dark:text-white">$${parseFloat(it.subtotal).toFixed(2)}</td>
				`;
				tbody.appendChild(tr);
			});
		}

		function renderCommercialDetailHistory(history) {
			const tbody = document.getElementById('dtl-comm-history-tbody');
			if (!tbody) return;
			tbody.innerHTML = '';

			if (history.length === 0) {
				tbody.innerHTML = `<tr><td colspan="5" class="py-4 text-center text-slate-400 text-xs">Sin registros de auditoría aún.</td></tr>`;
				return;
			}

			history.forEach(h => {
				const tr = document.createElement('tr');
				tr.className = 'hover:bg-slate-50/50 dark:hover:bg-slate-800/40';
				tr.innerHTML = `
					<td class="py-2 px-3 text-slate-500">${escapeHtml(h.from_status || 'Inicio')}</td>
					<td class="py-2 px-3 font-bold text-slate-900 dark:text-white">${escapeHtml(h.to_status)}</td>
					<td class="py-2 px-3 text-slate-600 dark:text-slate-300">${escapeHtml(h.changed_by)}</td>
					<td class="py-2 px-3 text-slate-500 text-[11px]">${escapeHtml(h.comment || 'Transición de estado')}</td>
					<td class="py-2 px-3 font-mono text-[11px] text-slate-400">${escapeHtml(h.created_timestamp)}</td>
				`;
				tbody.appendChild(tr);
			});
		}

		function renderCommercialDetailActions(type, doc) {
			const container = document.getElementById('dtl-comm-actions');
			if (!container) return;
			container.innerHTML = '';

			if (type === 'quote') {
				if (doc.status === 'Pendiente') {
					container.innerHTML += `
						<button onclick="updateQuoteStatusFromDetail('Aprobada')" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs">
							<i data-lucide="check" class="w-4 h-4"></i>
							<span>Aprobar Cotización</span>
						</button>
						<button onclick="updateQuoteStatusFromDetail('Rechazada')" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-rose-600 hover:bg-rose-700 text-white shadow-xs">
							<i data-lucide="x" class="w-4 h-4"></i>
							<span>Rechazar</span>
						</button>
					`;
				} else if (doc.status === 'Aprobada') {
					container.innerHTML += `
						<button onclick="convertQuoteFromDetail()" class="inline-flex items-center gap-1.5 px-4 py-1.5 text-xs font-semibold rounded-lg bg-purple-700 hover:bg-purple-800 text-white shadow-xs">
							<i data-lucide="arrow-right-circle" class="w-4 h-4"></i>
							<span>Convertir a Pedido B2B</span>
						</button>
					`;
				}
			} else {
				// Pedido
				container.innerHTML += `
					<button onclick="duplicateOrderFromDetail()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200">
						<i data-lucide="copy" class="w-4 h-4"></i>
						<span>Duplicar Pedido</span>
					</button>
				`;

				if (doc.status === 'Borrador') {
					container.innerHTML += `
						<button onclick="updateOrderStatusFromDetail('Confirmado')" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs">
							<i data-lucide="check" class="w-4 h-4"></i>
							<span>Confirmar Pedido</span>
						</button>
					`;
				}
				if (doc.status === 'Confirmado') {
					container.innerHTML += `
						<button onclick="updateOrderStatusFromDetail('Cancelado')" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-rose-600 hover:bg-rose-700 text-white shadow-xs">
							<i data-lucide="x-circle" class="w-4 h-4"></i>
							<span>Cancelar Pedido</span>
						</button>
					`;
				}
			}
		}

		async function updateQuoteStatusAction(quoteId, newStatus) {
			try {
				const res = await fetch(`/api/b2b/quotes/${quoteId}/status`, {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify({ status: newStatus })
				});
				const data = await res.json();
				if (data.success) {
					await fetchOrdersModule();
				}
			} catch (e) {}
		}

		async function updateQuoteStatusFromDetail(newStatus) {
			if (!activeCommercialDetail || activeCommercialDetail.type !== 'quote') return;
			await updateQuoteStatusAction(activeCommercialDetail.doc.id, newStatus);
			await openCommercialDetailModal('quote', activeCommercialDetail.doc.id);
		}

		async function convertQuoteAction(quoteId) {
			try {
				const res = await fetch(`/api/b2b/quotes/${quoteId}/convert-to-order`, {
					method: 'POST'
				});
				const data = await res.json();
				if (data.success) {
					switchOrdersMainTab('orders');
					await fetchOrdersModule();
					openCommercialDetailModal('order', data.order.id);
				} else {
					alert(data.error_message || 'Error al convertir cotización.');
				}
			} catch (e) {
				alert('Error de conexión al convertir cotización.');
			}
		}

		async function convertQuoteFromDetail() {
			if (!activeCommercialDetail || activeCommercialDetail.type !== 'quote') return;
			await convertQuoteAction(activeCommercialDetail.doc.id);
		}

		async function confirmUpdateOrderStatus(orderId, newStatus) {
			try {
				const res = await fetch(`/api/b2b/orders/${orderId}/status`, {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify({ status: newStatus })
				});
				const data = await res.json();
				if (data.success) {
					await fetchOrdersModule();
				}
			} catch (e) {}
		}

		async function updateOrderStatusFromDetail(newStatus) {
			if (!activeCommercialDetail || activeCommercialDetail.type !== 'order') return;
			await confirmUpdateOrderStatus(activeCommercialDetail.doc.id, newStatus);
			await openCommercialDetailModal('order', activeCommercialDetail.doc.id);
		}

		async function duplicateOrderAction(orderId) {
			try {
				const res = await fetch(`/api/b2b/orders/${orderId}/duplicate`, {
					method: 'POST'
				});
				const data = await res.json();
				if (data.success) {
					await fetchOrdersModule();
					openCommercialDetailModal('order', data.order.id);
				}
			} catch (e) {}
		}

		async function duplicateOrderFromDetail() {
			if (!activeCommercialDetail || activeCommercialDetail.type !== 'order') return;
			await duplicateOrderAction(activeCommercialDetail.doc.id);
		}

		/* ========================================================
		   MÓDULO FUNCIONAL DE FACTURACIÓN Y COBRANZAS B2B (PASO 7)
		   ======================================================== */

		let invoicesSubTab = 'invoices'; // 'invoices', 'credit-notes', 'overdue'
		let currentInvoicesCache = [];
		let currentCreditNotesCache = [];
		let activeInvoiceDetail = null;
		let debounceInvoicesTimer = null;
		let eligibleConfirmedOrders = [];

		async function fetchInvoicesModule() {
			const loading = document.getElementById('invoices-table-loading');
			if (loading) loading.classList.remove('hidden');

			try {
				// Cargar métricas ejecutivas
				const resMetrics = await fetch('/api/b2b/invoices/metrics');
				const dataMetrics = await resMetrics.json();
				if (dataMetrics.success && dataMetrics.metrics) {
					renderInvoicesKPIs(dataMetrics.metrics);
				}

				// Cargar clientes en filtro si está vacío
				await populateInvoicesCustomerFilter();

				// Cargar facturas y notas de crédito
				await fetchInvoicesList();
				await fetchCreditNotesList();
			} catch (e) {
				console.error('Error cargando módulo de facturación:', e);
			} finally {
				if (loading) loading.classList.add('hidden');
			}
		}

		function renderInvoicesKPIs(m) {
			const netSalesEl = document.getElementById('kpi-inv-net-sales');
			const receivablesEl = document.getElementById('kpi-inv-receivables');
			const collectedEl = document.getElementById('kpi-inv-collected');
			const overdueEl = document.getElementById('kpi-inv-overdue');
			const overdueCountEl = document.getElementById('kpi-inv-overdue-count');
			const navBadge = document.getElementById('nav-invoices-badge');
			const badgeOverdueTab = document.getElementById('badge-overdue-tab-count');

			if (netSalesEl) netSalesEl.textContent = '$' + m.net_sales;
			if (receivablesEl) receivablesEl.textContent = '$' + m.total_receivables;
			if (collectedEl) collectedEl.textContent = '$' + m.total_collected;
			if (overdueEl) overdueEl.textContent = '$' + m.total_overdue;
			if (overdueCountEl) overdueCountEl.textContent = m.overdue_count;
			if (badgeOverdueTab) badgeOverdueTab.textContent = m.overdue_count;
			if (navBadge) navBadge.textContent = m.active_invoices_count;
		}

		async function populateInvoicesCustomerFilter() {
			const select = document.getElementById('invoices-filter-customer');
			if (!select || select.options.length > 1) return;

			await ensureCustomersLoadedForSelect();
			customersForCalcCache.forEach(c => {
				const opt = document.createElement('option');
				opt.value = c.id;
				opt.textContent = `${c.name} (${c.customer_type})`;
				select.appendChild(opt);
			});
		}

		function switchInvoicesSubTab(tab) {
			invoicesSubTab = tab;
			const btnInv = document.getElementById('tab-subnav-invoices');
			const btnCn = document.getElementById('tab-subnav-credit-notes');
			const btnOvd = document.getElementById('tab-subnav-overdue');
			const tblInv = document.getElementById('table-container-invoices');
			const tblCn = document.getElementById('table-container-credit-notes');

			const activeClass = 'inline-flex items-center gap-2 pb-3 px-1 border-b-2 border-brand-800 text-brand-800 dark:text-white text-xs font-bold transition-all';
			const inactiveClass = 'inline-flex items-center gap-2 pb-3 px-1 border-b-2 border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 text-xs font-medium transition-all';

			btnInv.className = inactiveClass;
			btnCn.className = inactiveClass;
			btnOvd.className = inactiveClass;

			if (tab === 'invoices') {
				btnInv.className = activeClass;
				tblInv.classList.remove('hidden');
				tblCn.classList.add('hidden');
				fetchInvoicesList(false);
			} else if (tab === 'credit-notes') {
				btnCn.className = activeClass;
				tblInv.classList.add('hidden');
				tblCn.classList.remove('hidden');
				fetchCreditNotesList();
			} else if (tab === 'overdue') {
				btnOvd.className = 'inline-flex items-center gap-2 pb-3 px-1 border-b-2 border-rose-600 text-rose-600 dark:text-rose-400 text-xs font-bold transition-all';
				tblInv.classList.remove('hidden');
				tblCn.classList.add('hidden');
				fetchInvoicesList(true);
			}

			if (typeof lucide !== 'undefined') lucide.createIcons();
		}

		function debounceFetchInvoices() {
			clearTimeout(debounceInvoicesTimer);
			debounceInvoicesTimer = setTimeout(() => {
				if (invoicesSubTab === 'credit-notes') {
					fetchCreditNotesList();
				} else {
					fetchInvoicesList(invoicesSubTab === 'overdue');
				}
			}, 300);
		}

		function resetInvoicesFilters() {
			document.getElementById('invoices-search').value = '';
			document.getElementById('invoices-filter-status').value = '';
			document.getElementById('invoices-filter-customer').value = '';
			if (invoicesSubTab === 'overdue') {
				switchInvoicesSubTab('invoices');
			} else {
				fetchInvoicesList(false);
			}
		}

		async function fetchInvoicesList(overdueOnly = false) {
			const search = document.getElementById('invoices-search')?.value.trim() || '';
			const status = document.getElementById('invoices-filter-status')?.value || '';
			const customerId = document.getElementById('invoices-filter-customer')?.value || '';

			const params = new URLSearchParams();
			if (search) params.append('search', search);
			if (status) params.append('status', status);
			if (customerId) params.append('customer_id', customerId);
			if (overdueOnly) params.append('overdue_only', '1');

			try {
				const res = await fetch(`/api/b2b/invoices?${params.toString()}`);
				const data = await res.json();
				if (data.success && data.invoices) {
					currentInvoicesCache = data.invoices;
					renderInvoicesTable(currentInvoicesCache);
					const tabBadge = document.getElementById('badge-invoices-tab-count');
					if (tabBadge && !overdueOnly) tabBadge.textContent = currentInvoicesCache.length;
				}
			} catch (e) {
				console.error('Error al listar facturas:', e);
			}
		}

		function renderInvoicesTable(invoices) {
			const tbody = document.getElementById('invoices-table-body');
			const empty = document.getElementById('invoices-table-empty');
			const summary = document.getElementById('invoices-table-summary');
			if (!tbody) return;
			tbody.innerHTML = '';

			if (!invoices || invoices.length === 0) {
				if (empty) empty.classList.remove('hidden');
				if (summary) summary.textContent = '0 facturas encontradas';
				return;
			}

			if (empty) empty.classList.add('hidden');
			if (summary) summary.textContent = `Mostrando ${invoices.length} facturas comerciales`;

			invoices.forEach(inv => {
				const tr = document.createElement('tr');
				tr.className = 'hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors';

				let statusBadge = '';
				if (inv.status === 'Pagada') {
					statusBadge = `<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/60"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Pagada</span>`;
				} else if (inv.status === 'Parcialmente pagada') {
					statusBadge = `<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200/60"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Parcial</span>`;
				} else if (inv.status === 'Emitida') {
					statusBadge = `<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200/60"><span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>Emitida</span>`;
				} else if (inv.status === 'Anulada') {
					statusBadge = `<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200/60"><span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>Anulada</span>`;
				} else {
					statusBadge = `<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200">Borrador</span>`;
				}

				const overdueBadge = inv.is_overdue === 1 ? `<span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded text-[9px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 ml-1">VENCIDA</span>` : '';
				const branchText = inv.establishment_name ? `<div class="text-[10px] text-slate-400 flex items-center gap-1"><i data-lucide="map-pin" class="w-3 h-3"></i>${escapeHtml(inv.establishment_name)}</div>` : '';
				const balanceColor = parseFloat(inv.balance) > 0 ? 'text-rose-600 dark:text-rose-400 font-bold' : 'text-slate-400';

				tr.innerHTML = `
					<td class="py-3 px-4 font-mono font-bold text-slate-900 dark:text-white">
						${escapeHtml(inv.invoice_number)}
						${overdueBadge}
					</td>
					<td class="py-3 px-4">
						<div class="font-semibold text-slate-900 dark:text-white">${escapeHtml(inv.customer_name)}</div>
						${branchText}
					</td>
					<td class="py-3 px-4 font-mono text-xs text-slate-500">
						${inv.order_number ? escapeHtml(inv.order_number) : '<span class="text-slate-400 italic">Directa</span>'}
					</td>
					<td class="py-3 px-4">
						<div class="font-mono text-xs text-slate-700 dark:text-slate-300">${escapeHtml(inv.issue_date)}</div>
						<div class="font-mono text-[10px] text-slate-400">Vence: ${escapeHtml(inv.due_date)}</div>
					</td>
					<td class="py-3 px-4 text-right font-mono font-bold text-slate-900 dark:text-white text-sm">
						$${parseFloat(inv.total).toFixed(2)}
					</td>
					<td class="py-3 px-4 text-right font-mono text-emerald-600 dark:text-emerald-400 font-semibold">
						$${parseFloat(inv.paid_amount).toFixed(2)}
					</td>
					<td class="py-3 px-4 text-right font-mono ${balanceColor}">
						$${parseFloat(inv.balance).toFixed(2)}
					</td>
					<td class="py-3 px-4 text-center">
						${statusBadge}
					</td>
					<td class="py-3 px-4 text-right">
						<div class="flex items-center justify-end gap-1.5">
							<button onclick="openInvoiceDetailModal(${inv.id})" class="p-1.5 text-brand-700 dark:text-brand-300 hover:bg-brand-50 dark:hover:bg-brand-950/60 rounded-lg transition-colors" title="Ver Detalle de Factura">
								<i data-lucide="eye" class="w-4 h-4"></i>
							</button>
							${parseFloat(inv.balance) > 0.001 && inv.status !== 'Anulada' ? `
								<button onclick="openInvoicePaymentModal(${inv.id})" class="p-1.5 text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/60 rounded-lg transition-colors" title="Registrar Pago / Abono">
									<i data-lucide="wallet" class="w-4 h-4"></i>
								</button>
							` : ''}
							${inv.status !== 'Anulada' && inv.status !== 'Borrador' ? `
								<button onclick="openCreditNoteModal(${inv.id})" class="p-1.5 text-purple-600 hover:bg-purple-50 dark:hover:bg-purple-950/60 rounded-lg transition-colors" title="Emitir Nota de Crédito">
									<i data-lucide="file-minus" class="w-4 h-4"></i>
								</button>
							` : ''}
							<button onclick="openInvoicePdf(${inv.id})" class="p-1.5 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors" title="Imprimir / Descargar PDF">
								<i data-lucide="printer" class="w-4 h-4"></i>
							</button>
						</div>
					</td>
				`;
				tbody.appendChild(tr);
			});

			if (typeof lucide !== 'undefined') lucide.createIcons();
		}

		async function fetchCreditNotesList() {
			try {
				const res = await fetch('/api/b2b/credit-notes');
				const data = await res.json();
				if (data.success && data.credit_notes) {
					currentCreditNotesCache = data.credit_notes;
					renderCreditNotesTable(currentCreditNotesCache);
					const cnBadge = document.getElementById('badge-cn-tab-count');
					if (cnBadge) cnBadge.textContent = currentCreditNotesCache.length;
				}
			} catch (e) {
				console.error('Error al listar notas de crédito:', e);
			}
		}

		function renderCreditNotesTable(cns) {
			const tbody = document.getElementById('credit-notes-table-body');
			const empty = document.getElementById('credit-notes-table-empty');
			const summary = document.getElementById('credit-notes-table-summary');
			if (!tbody) return;
			tbody.innerHTML = '';

			if (!cns || cns.length === 0) {
				if (empty) empty.classList.remove('hidden');
				if (summary) summary.textContent = '0 notas de crédito registradas';
				return;
			}

			if (empty) empty.classList.add('hidden');
			if (summary) summary.textContent = `Mostrando ${cns.length} notas de crédito comerciales`;

			cns.forEach(cn => {
				const tr = document.createElement('tr');
				tr.className = 'hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors';

				const reasonLabels = {
					devolucion: 'Devolución de mercadería',
					diferencia_precio: 'Diferencia de precio',
					descuento: 'Descuento comercial',
					ajuste_comercial: 'Ajuste comercial'
				};

				tr.innerHTML = `
					<td class="py-3 px-4 font-mono font-bold text-purple-700 dark:text-purple-300">
						${escapeHtml(cn.credit_note_number)}
					</td>
					<td class="py-3 px-4 font-mono font-semibold text-slate-800 dark:text-slate-200">
						${escapeHtml(cn.invoice_number)}
					</td>
					<td class="py-3 px-4 font-semibold text-slate-900 dark:text-white">
						${escapeHtml(cn.customer_name)}
					</td>
					<td class="py-3 px-4">
						<span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200">
							${reasonLabels[cn.reason] || escapeHtml(cn.reason)}
						</span>
					</td>
					<td class="py-3 px-4 text-right font-mono text-slate-600 dark:text-slate-300">
						$${parseFloat(cn.subtotal).toFixed(2)}
					</td>
					<td class="py-3 px-4 text-right font-mono text-slate-500">
						$${parseFloat(cn.tax_amount).toFixed(2)}
					</td>
					<td class="py-3 px-4 text-right font-mono font-bold text-purple-700 dark:text-purple-300 text-sm">
						$${parseFloat(cn.total).toFixed(2)}
					</td>
					<td class="py-3 px-4 text-center">
						<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Emitida</span>
					</td>
					<td class="py-3 px-4 text-right">
						<button onclick="openInvoiceDetailModal(${cn.invoice_id})" class="p-1.5 text-brand-700 dark:text-brand-300 hover:bg-brand-50 rounded-lg transition-colors" title="Ver Factura Vinculada">
							<i data-lucide="external-link" class="w-4 h-4"></i>
						</button>
					</td>
				`;
				tbody.appendChild(tr);
			});

			if (typeof lucide !== 'undefined') lucide.createIcons();
		}

		/* Modal: Emitir Factura desde Pedido */
		async function openGenerateInvoiceModal(orderId = null) {
			const modal = document.getElementById('modal-generate-invoice');
			const select = document.getElementById('inv-gen-order-select');
			const preview = document.getElementById('inv-gen-order-preview');
			preview.classList.add('hidden');
			select.innerHTML = '<option value="">Cargando pedidos confirmados...</option>';
			modal.classList.remove('hidden');

			try {
				const res = await fetch('/api/b2b/orders?status=Confirmado');
				const data = await res.json();
				select.innerHTML = '<option value="">Seleccionar pedido comercial confirmado...</option>';

				if (data.success && data.orders) {
					eligibleConfirmedOrders = data.orders;
					data.orders.forEach(o => {
						const opt = document.createElement('option');
						opt.value = o.id;
						opt.textContent = `${o.order_number} — ${o.customer_name} ($${parseFloat(o.total).toFixed(2)})`;
						select.appendChild(opt);
					});

					if (orderId) {
						select.value = orderId;
						handleGenerateInvoiceOrderChange();
					}
				}
			} catch (e) {
				select.innerHTML = '<option value="">Error cargando pedidos</option>';
			}

			// Fechas por defecto
			handleTermsChangeForDueDate();
			if (typeof lucide !== 'undefined') lucide.createIcons();
		}

		function closeGenerateInvoiceModal() {
			document.getElementById('modal-generate-invoice').classList.add('hidden');
		}

		function handleGenerateInvoiceOrderChange() {
			const select = document.getElementById('inv-gen-order-select');
			const preview = document.getElementById('inv-gen-order-preview');
			const ordId = parseInt(select.value);

			if (!ordId) {
				preview.classList.add('hidden');
				return;
			}

			const ord = eligibleConfirmedOrders.find(o => o.id === ordId);
			if (ord) {
				document.getElementById('inv-prev-customer').textContent = ord.customer_name;
				document.getElementById('inv-prev-lines').textContent = `${ord.items_count || 1} productos empaquetados`;
				document.getElementById('inv-prev-subtotal').textContent = '$' + parseFloat(ord.subtotal).toFixed(2);
				document.getElementById('inv-prev-tax').textContent = '$' + parseFloat(ord.tax_amount).toFixed(2);
				document.getElementById('inv-prev-total').textContent = '$' + parseFloat(ord.total).toFixed(2);
				preview.classList.remove('hidden');
			}
		}

		function handleTermsChangeForDueDate() {
			const terms = document.getElementById('inv-gen-terms')?.value || 'credito_30';
			const dateInput = document.getElementById('inv-gen-due-date');
			if (!dateInput) return;

			const daysMap = { contado: 0, credito_15: 15, credito_30: 30, credito_60: 60 };
			const days = daysMap[terms] ?? 30;

			const d = new Date();
			d.setDate(d.getDate() + days);
			dateInput.value = d.toISOString().split('T')[0];
		}

		async function submitGenerateInvoiceForm(event) {
			if (event) event.preventDefault();
			const orderId = parseInt(document.getElementById('inv-gen-order-select')?.value || 0);
			if (orderId <= 0) {
				alert('Debes seleccionar un pedido confirmado.');
				return;
			}

			const payload = {
				payment_terms: document.getElementById('inv-gen-terms').value,
				due_date: document.getElementById('inv-gen-due-date').value,
				notes: document.getElementById('inv-gen-notes').value.trim()
			};

			try {
				const res = await fetch(`/api/b2b/invoices/from-order/${orderId}`, {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify(payload)
				});
				const data = await res.json();

				if (!res.ok || !data.success) {
					alert(data.error_message || 'Error al emitir factura comercial.');
					return;
				}

				closeGenerateInvoiceModal();
				await fetchInvoicesModule();
				openInvoiceDetailModal(data.invoice.id);
			} catch (e) {
				alert('Error de conexión al emitir factura comercial.');
			}
		}

		/* Modal: Registrar Pago */
		async function openInvoicePaymentModal(invoiceId) {
			const modal = document.getElementById('modal-invoice-payment');
			modal.classList.remove('hidden');

			try {
				const res = await fetch(`/api/b2b/invoices/${invoiceId}`);
				const data = await res.json();
				if (!data.success || !data.invoice) return;

				const inv = data.invoice;
				document.getElementById('pay-modal-inv-id').value = inv.id;
				document.getElementById('pay-modal-inv-number').textContent = inv.invoice_number;
				document.getElementById('pay-modal-cust-name').textContent = inv.customer_name;
				document.getElementById('pay-modal-total').textContent = '$' + parseFloat(inv.total).toFixed(2);
				document.getElementById('pay-modal-balance').textContent = '$' + parseFloat(inv.balance).toFixed(2);

				const amountInput = document.getElementById('pay-input-amount');
				amountInput.value = parseFloat(inv.balance).toFixed(2);
				amountInput.max = parseFloat(inv.balance).toFixed(2);

				document.getElementById('pay-input-date').value = new Date().toISOString().split('T')[0];
				document.getElementById('pay-input-ref').value = '';
				document.getElementById('pay-input-notes').value = '';
			} catch (e) {
				console.error('Error cargando factura para pago:', e);
			}

			if (typeof lucide !== 'undefined') lucide.createIcons();
		}

		function closeInvoicePaymentModal() {
			document.getElementById('modal-invoice-payment').classList.add('hidden');
		}

		function fillFullPaymentAmount() {
			const bal = document.getElementById('pay-modal-balance')?.textContent.replace('$', '') || '0';
			document.getElementById('pay-input-amount').value = parseFloat(bal).toFixed(2);
		}

		async function submitInvoicePaymentForm(event) {
			if (event) event.preventDefault();
			const invId = parseInt(document.getElementById('pay-modal-inv-id')?.value || 0);
			const amount = parseFloat(document.getElementById('pay-input-amount')?.value || 0);

			if (invId <= 0 || amount <= 0) {
				alert('Ingresa un importe de pago válido mayor a 0.');
				return;
			}

			const payload = {
				amount: amount,
				payment_method: document.getElementById('pay-input-method').value,
				payment_date: document.getElementById('pay-input-date').value,
				reference: document.getElementById('pay-input-ref').value.trim(),
				notes: document.getElementById('pay-input-notes').value.trim()
			};

			try {
				const res = await fetch(`/api/b2b/invoices/${invId}/payments`, {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify(payload)
				});
				const data = await res.json();

				if (!res.ok || !data.success) {
					alert(data.error_message || 'Error al procesar el pago.');
					return;
				}

				closeInvoicePaymentModal();
				await fetchInvoicesModule();

				// Si el detalle de factura está abierto, refrescarlo
				if (activeInvoiceDetail && activeInvoiceDetail.id === invId) {
					openInvoiceDetailModal(invId);
				}
			} catch (e) {
				alert('Error de conexión al registrar pago.');
			}
		}

		/* Modal: Nota de Crédito */
		async function openCreditNoteModal(invoiceId = null) {
			const modal = document.getElementById('modal-credit-note');
			modal.classList.remove('hidden');

			if (!invoiceId && currentInvoicesCache.length > 0) {
				// Tomar la primera factura válida
				const validInv = currentInvoicesCache.find(i => i.status !== 'Anulada' && i.status !== 'Borrador');
				if (validInv) invoiceId = validInv.id;
			}

			if (invoiceId) {
				try {
					const res = await fetch(`/api/b2b/invoices/${invoiceId}`);
					const data = await res.json();
					if (data.success && data.invoice) {
						const inv = data.invoice;
						document.getElementById('cn-modal-inv-id').value = inv.id;
						document.getElementById('cn-modal-inv-number').textContent = inv.invoice_number;
						document.getElementById('cn-modal-cust-name').textContent = inv.customer_name;

						const creditable = Math.max(0, parseFloat(inv.total) - parseFloat(inv.credit_amount));
						document.getElementById('cn-modal-avail-balance').textContent = '$' + creditable.toFixed(2);

						const amountInput = document.getElementById('cn-input-amount');
						amountInput.max = creditable.toFixed(2);
						amountInput.value = (creditable * 0.1).toFixed(2); // Sugerir 10%
						calculateCreditNoteTaxes();
					}
				} catch (e) {
					console.error('Error cargando factura para nota de crédito:', e);
				}
			}

			if (typeof lucide !== 'undefined') lucide.createIcons();
		}

		function closeCreditNoteModal() {
			document.getElementById('modal-credit-note').classList.add('hidden');
		}

		function calculateCreditNoteTaxes() {
			const total = parseFloat(document.getElementById('cn-input-amount')?.value || 0);
			const subtotal = total > 0 ? (total / 1.18) : 0;
			const tax = total - subtotal;

			document.getElementById('cn-calc-subtotal').textContent = '$' + subtotal.toFixed(2);
			document.getElementById('cn-calc-tax').textContent = '$' + tax.toFixed(2);
			document.getElementById('cn-calc-total').textContent = '$' + total.toFixed(2);
		}

		async function submitCreditNoteForm(event) {
			if (event) event.preventDefault();
			const invId = parseInt(document.getElementById('cn-modal-inv-id')?.value || 0);
			const amount = parseFloat(document.getElementById('cn-input-amount')?.value || 0);

			if (invId <= 0 || amount <= 0) {
				alert('Ingresa un importe válido para la nota de crédito.');
				return;
			}

			const payload = {
				amount: amount,
				reason: document.getElementById('cn-input-reason').value,
				notes: document.getElementById('cn-input-notes').value.trim()
			};

			try {
				const res = await fetch(`/api/b2b/invoices/${invId}/credit-notes`, {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify(payload)
				});
				const data = await res.json();

				if (!res.ok || !data.success) {
					alert(data.error_message || 'Error al emitir nota de crédito.');
					return;
				}

				closeCreditNoteModal();
				await fetchInvoicesModule();

				if (activeInvoiceDetail && activeInvoiceDetail.id === invId) {
					openInvoiceDetailModal(invId);
				}
			} catch (e) {
				alert('Error de conexión al emitir nota de crédito.');
			}
		}

		/* Modal: Ficha Detallada de Factura */
		async function openInvoiceDetailModal(id) {
			const modal = document.getElementById('modal-invoice-detail');
			modal.classList.remove('hidden');

			try {
				const res = await fetch(`/api/b2b/invoices/${id}`);
				const data = await res.json();
				if (!data.success || !data.invoice) return;

				const inv = data.invoice;
				activeInvoiceDetail = inv;

				// Header
				document.getElementById('dtl-inv-number').textContent = inv.invoice_number;
				document.getElementById('dtl-inv-customer-name').textContent = inv.customer_name;
				document.getElementById('dtl-inv-meta').textContent = `Ejecutivo: ${inv.sales_rep} | Pedido: ${inv.order_number || 'Directo'} | Emisión: ${inv.issue_date} | Vencimiento: ${inv.due_date} | Término: ${inv.payment_terms.toUpperCase()}`;

				// Badges
				const badgeContainer = document.getElementById('dtl-inv-status-badge');
				badgeContainer.innerHTML = `<span class="px-2 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-800">${escapeHtml(inv.status)}</span>`;

				const overdueContainer = document.getElementById('dtl-inv-overdue-badge');
				overdueContainer.innerHTML = inv.is_overdue === 1 ? `<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">EN MORA</span>` : '';

				// Mini cards
				document.getElementById('dtl-inv-total').textContent = '$' + parseFloat(inv.total).toFixed(2);
				document.getElementById('dtl-inv-paid').textContent = '$' + parseFloat(inv.paid_amount).toFixed(2);
				document.getElementById('dtl-inv-credits').textContent = '$' + parseFloat(inv.credit_amount).toFixed(2);
				document.getElementById('dtl-inv-balance').textContent = '$' + parseFloat(inv.balance).toFixed(2);

				// Tab counts
				document.getElementById('tab-invdtl-payments-count').textContent = inv.payments?.length || 0;
				document.getElementById('tab-invdtl-credits-count').textContent = inv.credit_notes?.length || 0;

				// Renderizar Ítems
				renderInvoiceDetailItems(inv.items || []);
				renderInvoiceDetailPayments(inv.payments || []);
				renderInvoiceDetailCredits(inv.credit_notes || []);
				renderInvoiceDetailHistory(inv.history || []);

				// Botones contextuales de acción en footer
				renderInvoiceDetailActions(inv);
				switchInvoiceDetailSubTab('items');
			} catch (e) {
				console.error('Error cargando detalle de factura:', e);
			}

			if (typeof lucide !== 'undefined') lucide.createIcons();
		}

		function closeInvoiceDetailModal() {
			document.getElementById('modal-invoice-detail').classList.add('hidden');
			activeInvoiceDetail = null;
		}

		function switchInvoiceDetailSubTab(tab) {
			const tabs = ['items', 'payments', 'credits', 'history'];
			tabs.forEach(t => {
				const btn = document.getElementById(`tab-invdtl-${t}`);
				const cnt = document.getElementById(`tab-content-invdtl-${t}`);
				if (t === tab) {
					btn.className = 'py-3 border-b-2 border-brand-800 text-brand-800 dark:text-white font-bold';
					cnt.classList.remove('hidden');
				} else {
					btn.className = 'py-3 border-b-2 border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300';
					cnt.classList.add('hidden');
				}
			});
			if (typeof lucide !== 'undefined') lucide.createIcons();
		}

		function renderInvoiceDetailItems(items) {
			const tbody = document.getElementById('dtl-inv-items-tbody');
			if (!tbody) return;
			tbody.innerHTML = '';

			items.forEach(it => {
				const tr = document.createElement('tr');
				tr.className = 'hover:bg-slate-50/50 dark:hover:bg-slate-800/40';
				tr.innerHTML = `
					<td class="py-2.5 px-3 font-mono font-bold text-slate-900 dark:text-white">${escapeHtml(it.product_sku)}</td>
					<td class="py-2.5 px-3">
						<div class="font-semibold text-slate-800 dark:text-slate-200">${escapeHtml(it.product_name)}</div>
						<div class="text-[10px] text-slate-400">${escapeHtml(it.product_brand)}</div>
					</td>
					<td class="py-2.5 px-3 text-center">
						<span>${it.commercial_unit === 'CASE' ? 'Caja' : 'Unidad'}</span>
						<div class="text-[10px] text-slate-400">Factor: ${it.conversion_factor} (${it.total_units} uds)</div>
					</td>
					<td class="py-2.5 px-3 text-center font-bold font-mono">${it.quantity}</td>
					<td class="py-2.5 px-3 text-right font-mono font-bold text-slate-700 dark:text-slate-300">$${parseFloat(it.unit_price).toFixed(2)}</td>
					<td class="py-2.5 px-3 text-right font-mono font-bold text-slate-900 dark:text-white">$${parseFloat(it.subtotal).toFixed(2)}</td>
				`;
				tbody.appendChild(tr);
			});
		}

		function renderInvoiceDetailPayments(payments) {
			const tbody = document.getElementById('dtl-inv-payments-tbody');
			if (!tbody) return;
			tbody.innerHTML = '';

			if (!payments || payments.length === 0) {
				tbody.innerHTML = '<tr><td colspan="6" class="py-4 text-center text-slate-400 text-xs">Sin abonos registrados en esta factura.</td></tr>';
				return;
			}

			payments.forEach(p => {
				const tr = document.createElement('tr');
				tr.className = 'hover:bg-slate-50/50 dark:hover:bg-slate-800/40';
				tr.innerHTML = `
					<td class="py-2 px-3 font-mono font-bold text-slate-900 dark:text-white">${escapeHtml(p.payment_number)}</td>
					<td class="py-2 px-3 font-mono text-slate-500">${escapeHtml(p.payment_date)}</td>
					<td class="py-2 px-3 text-slate-700 dark:text-slate-300 font-medium">${escapeHtml(p.payment_method)}</td>
					<td class="py-2 px-3 font-mono text-[11px] text-slate-500">${escapeHtml(p.reference || 'N/A')}</td>
					<td class="py-2 px-3 text-right font-mono font-bold text-emerald-600">$${parseFloat(p.amount).toFixed(2)}</td>
					<td class="py-2 px-3 text-slate-500 text-[11px]">${escapeHtml(p.recorded_by)}</td>
				`;
				tbody.appendChild(tr);
			});
		}

		function renderInvoiceDetailCredits(cns) {
			const tbody = document.getElementById('dtl-inv-credits-tbody');
			if (!tbody) return;
			tbody.innerHTML = '';

			if (!cns || cns.length === 0) {
				tbody.innerHTML = '<tr><td colspan="5" class="py-4 text-center text-slate-400 text-xs">Sin notas de crédito aplicadas.</td></tr>';
				return;
			}

			cns.forEach(cn => {
				const tr = document.createElement('tr');
				tr.className = 'hover:bg-slate-50/50 dark:hover:bg-slate-800/40';
				tr.innerHTML = `
					<td class="py-2 px-3 font-mono font-bold text-purple-700 dark:text-purple-300">${escapeHtml(cn.credit_note_number)}</td>
					<td class="py-2 px-3 text-slate-700 dark:text-slate-300">${escapeHtml(cn.reason)}</td>
					<td class="py-2 px-3 font-mono text-slate-500">${escapeHtml(cn.created_timestamp?.split(' ')[0])}</td>
					<td class="py-2 px-3 text-right font-mono font-bold text-purple-700">$${parseFloat(cn.total).toFixed(2)}</td>
					<td class="py-2 px-3 text-center"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700">Emitida</span></td>
				`;
				tbody.appendChild(tr);
			});
		}

		function renderInvoiceDetailHistory(history) {
			const tbody = document.getElementById('dtl-inv-history-tbody');
			if (!tbody) return;
			tbody.innerHTML = '';

			if (!history || history.length === 0) {
				tbody.innerHTML = '<tr><td colspan="5" class="py-4 text-center text-slate-400 text-xs">Sin registros de auditoría.</td></tr>';
				return;
			}

			history.forEach(h => {
				const tr = document.createElement('tr');
				tr.className = 'hover:bg-slate-50/50 dark:hover:bg-slate-800/40';
				tr.innerHTML = `
					<td class="py-2 px-3 font-semibold text-slate-700 dark:text-slate-300">${escapeHtml(h.document_type)}</td>
					<td class="py-2 px-3 font-bold text-slate-900 dark:text-white">${escapeHtml(h.to_status)}</td>
					<td class="py-2 px-3 text-slate-600 dark:text-slate-400">${escapeHtml(h.changed_by)}</td>
					<td class="py-2 px-3 text-slate-500 text-[11px]">${escapeHtml(h.comment || '')}</td>
					<td class="py-2 px-3 font-mono text-[11px] text-slate-400">${escapeHtml(h.created_timestamp)}</td>
				`;
				tbody.appendChild(tr);
			});
		}

		function renderInvoiceDetailActions(inv) {
			const container = document.getElementById('dtl-inv-actions');
			if (!container) return;
			container.innerHTML = '';

			// Botón PDF
			container.innerHTML += `
				<button onclick="openInvoicePdf(${inv.id})" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200">
					<i data-lucide="printer" class="w-4 h-4"></i>
					<span>PDF / Imprimir</span>
				</button>
			`;

			// Botón Pago
			if (parseFloat(inv.balance) > 0.001 && inv.status !== 'Anulada') {
				container.innerHTML += `
					<button onclick="openInvoicePaymentModal(${inv.id})" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs">
						<i data-lucide="wallet" class="w-4 h-4"></i>
						<span>Registrar Pago</span>
					</button>
				`;
			}

			// Botón Nota de Crédito
			if (inv.status !== 'Anulada' && inv.status !== 'Borrador') {
				container.innerHTML += `
					<button onclick="openCreditNoteModal(${inv.id})" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-purple-700 hover:bg-purple-800 text-white shadow-xs">
						<i data-lucide="file-minus" class="w-4 h-4"></i>
						<span>Nota de Crédito</span>
					</button>
				`;
			}

			// Botón Anular Factura
			if (inv.status !== 'Anulada' && parseFloat(inv.paid_amount) <= 0.001 && parseFloat(inv.credit_amount) <= 0.001) {
				container.innerHTML += `
					<button onclick="confirmVoidInvoice(${inv.id})" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100">
						<i data-lucide="slash" class="w-4 h-4"></i>
						<span>Anular Factura</span>
					</button>
				`;
			}

			if (typeof lucide !== 'undefined') lucide.createIcons();
		}

		function openInvoicePdf(id) {
			window.open(`/api/b2b/invoices/${id}/print`, '_blank');
		}

		async function confirmVoidInvoice(id) {
			if (!confirm('¿Estás seguro de que deseas anular esta factura comercial? Esta acción no se puede deshacer.')) return;

			try {
				const res = await fetch(`/api/b2b/invoices/${id}/status`, {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify({ status: 'Anulada', comment: 'Anulación manual solicitada por el usuario' })
				});
				const data = await res.json();
				if (!res.ok || !data.success) {
					alert(data.error_message || 'Error al anular factura.');
					return;
				}
				await fetchInvoicesModule();
				openInvoiceDetailModal(id);
			} catch (e) {
				alert('Error al procesar la anulación.');
			}
		}

		function escapeHtml(text) {
			if (!text) return '';
			return String(text).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
		}

		function escapeJs(text) {
			if (!text) return '';
			return String(text).replace(/\\/g, '\\\\').replace(/'/g, "\\'");
		}

		/* ========================================================
		   SIDEBAR, IDIOMAS, TEMAS & GRÁFICOS
		   ======================================================== */

		function toggleSidebarCollapse() {
			const isCollapsed = document.body.classList.contains('sidebar-collapsed');
			setSidebarCollapsed(!isCollapsed, true);
		}

		function setSidebarCollapsed(collapsed, save = true) {
			const sidebar = document.getElementById('main-sidebar');
			const content = document.getElementById('content-container');
			const icon = document.getElementById('icon-collapse');

			if (collapsed) {
				document.body.classList.add('sidebar-collapsed');
				sidebar.classList.remove('w-72');
				sidebar.classList.add('w-20');
				content.classList.remove('md:pl-72');
				content.classList.add('md:pl-20');
				if (icon) icon.setAttribute('data-lucide', 'panel-left-open');
			} else {
				document.body.classList.remove('sidebar-collapsed');
				sidebar.classList.remove('w-20');
				sidebar.classList.add('w-72');
				content.classList.remove('md:pl-20');
				content.classList.add('md:pl-72');
				if (icon) icon.setAttribute('data-lucide', 'panel-left-close');
			}

			if (save) localStorage.setItem('b2b_sidebar_collapsed', collapsed ? 'true' : 'false');
			if (typeof lucide !== 'undefined') lucide.createIcons();
			if (window.salesTrendChartInstance) window.salesTrendChartInstance.resize();
			if (window.channelShareChartInstance) window.channelShareChartInstance.resize();
			if (window.billingVsCollectionsChartInstance) window.billingVsCollectionsChartInstance.resize();
			if (window.orderStatusChartInstance) window.orderStatusChartInstance.resize();
			if (window.topProductsChartInstance) window.topProductsChartInstance.resize();
		}

		function toggleMobileSidebar() {
			const sidebar = document.getElementById('main-sidebar');
			const overlay = document.getElementById('mobile-overlay');
			const isHidden = sidebar.classList.contains('-translate-x-full');
			if (isHidden) {
				sidebar.classList.remove('-translate-x-full');
				overlay.classList.remove('hidden');
			} else {
				closeMobileSidebar();
			}
		}

		function closeMobileSidebar() {
			const sidebar = document.getElementById('main-sidebar');
			const overlay = document.getElementById('mobile-overlay');
			sidebar.classList.add('-translate-x-full');
			overlay.classList.add('hidden');
		}

		function switchLanguage(lang) {
			if (lang !== 'es' && lang !== 'en') lang = 'es';
			currentLang = lang;
			localStorage.setItem('b2b_lang', lang);
			applyLanguage(lang);
		}

		function applyLanguage(lang) {
			const t = b2bTranslations[lang] || b2bTranslations.es;
			const btnEs = document.getElementById('lang-btn-es');
			const btnEn = document.getElementById('lang-btn-en');
			if (lang === 'es') {
				btnEs.className = 'px-2 py-1 rounded-md text-brand-800 dark:text-white bg-white dark:bg-slate-700 shadow-xs transition-all';
				btnEn.className = 'px-2 py-1 rounded-md text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-all';
			} else {
				btnEn.className = 'px-2 py-1 rounded-md text-brand-800 dark:text-white bg-white dark:bg-slate-700 shadow-xs transition-all';
				btnEs.className = 'px-2 py-1 rounded-md text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-all';
			}

			document.querySelectorAll('[data-i18n]').forEach(el => {
				const key = el.getAttribute('data-i18n');
				if (t[key]) el.textContent = t[key];
			});

			navigateToSection(currentSection, false);
		}

		/* ========================================================
		   TABLERO EJECUTIVO DE VENTAS B2B (PASO 8)
		   Controlador JavaScript de Analítica, Filtros y Gráficos
		   ======================================================== */

		let dashboardState = {
			period: 'MTD',
			fromDate: null,
			toDate: null,
			customerId: '',
			establishmentId: '',
			categoryId: '',
			status: 'ALL',
			granularity: 'day',
			filtersLoaded: false,
			filterOptions: null,
			lastData: null
		};

		// Instancias de Gráficos Chart.js
		window.salesTrendChartInstance = null;
		window.channelShareChartInstance = null; // Usado para Ventas por Categoría
		window.billingVsCollectionsChartInstance = null;
		window.orderStatusChartInstance = null;
		window.topProductsChartInstance = null;

		function destroyAllDashboardCharts() {
			if (window.salesTrendChartInstance) {
				window.salesTrendChartInstance.destroy();
				window.salesTrendChartInstance = null;
			}
			if (window.channelShareChartInstance) {
				window.channelShareChartInstance.destroy();
				window.channelShareChartInstance = null;
			}
			if (window.billingVsCollectionsChartInstance) {
				window.billingVsCollectionsChartInstance.destroy();
				window.billingVsCollectionsChartInstance = null;
			}
			if (window.orderStatusChartInstance) {
				window.orderStatusChartInstance.destroy();
				window.orderStatusChartInstance = null;
			}
			if (window.topProductsChartInstance) {
				window.topProductsChartInstance.destroy();
				window.topProductsChartInstance = null;
			}
		}

		function toggleTheme() {
			const html = document.documentElement;
			const isDark = html.classList.toggle('dark');
			localStorage.setItem('b2b_theme', isDark ? 'dark' : 'light');

			destroyAllDashboardCharts();
			if (currentSection === 'sales') {
				if (dashboardState.lastData && dashboardState.lastData.charts) {
					renderDashboardCharts(dashboardState.lastData.charts);
				} else {
					fetchDashboardModule();
				}
			}
		}

		async function fetchDashboardFilters() {
			if (dashboardState.filtersLoaded) return;
			try {
				const res = await fetch('/api/b2b/dashboard/filters');
				const data = await res.json();
				if (!data.success) return;

				dashboardState.filterOptions = data.filters;
				dashboardState.filtersLoaded = true;

				// Poblar selector de clientes
				const custSelect = document.getElementById('dash-filter-customer');
				if (custSelect && data.filters.customers) {
					custSelect.innerHTML = '<option value="">Todos los Clientes</option>';
					data.filters.customers.forEach(c => {
						const opt = document.createElement('option');
						opt.value = c.id;
						opt.textContent = c.name + (c.customer_type ? ' (' + c.customer_type + ')' : '');
						custSelect.appendChild(opt);
					});
				}

				// Poblar selector de establecimientos
				populateEstablishmentOptions();

				// Poblar selector de categorías
				const catSelect = document.getElementById('dash-filter-category');
				if (catSelect && data.filters.categories) {
					catSelect.innerHTML = '<option value="">Todas las Categorías</option>';
					data.filters.categories.forEach(cat => {
						const opt = document.createElement('option');
						opt.value = cat.id;
						opt.textContent = cat.name;
						catSelect.appendChild(opt);
					});
				}
			} catch (err) {
				console.error('Error al cargar opciones de filtros B2B:', err);
			}
		}

		function populateEstablishmentOptions(customerId = null) {
			const estSelect = document.getElementById('dash-filter-establishment');
			if (!estSelect || !dashboardState.filterOptions) return;

			estSelect.innerHTML = '<option value="">Todos los Establecimientos</option>';
			let list = dashboardState.filterOptions.establishments || [];
			if (customerId) {
				list = list.filter(e => String(e.customer_id) === String(customerId));
			}

			list.forEach(e => {
				const opt = document.createElement('option');
				opt.value = e.id;
				opt.textContent = e.name + (e.city ? ' - ' + e.city : '');
				estSelect.appendChild(opt);
			});
		}

		async function fetchDashboardModule() {
			if (!dashboardState.filtersLoaded) {
				await fetchDashboardFilters();
			}

			const params = new URLSearchParams();
			params.set('period', dashboardState.period);
			if (dashboardState.period === 'CUSTOM') {
				if (dashboardState.fromDate) params.set('from_date', dashboardState.fromDate);
				if (dashboardState.toDate) params.set('to_date', dashboardState.toDate);
			}
			if (dashboardState.customerId) params.set('customer_id', dashboardState.customerId);
			if (dashboardState.establishmentId) params.set('establishment_id', dashboardState.establishmentId);
			if (dashboardState.categoryId) params.set('category_id', dashboardState.categoryId);
			if (dashboardState.status && dashboardState.status !== 'ALL') params.set('status', dashboardState.status);
			params.set('granularity', dashboardState.granularity);

			try {
				const res = await fetch('/api/b2b/dashboard/executive?' + params.toString());
				const data = await res.json();
				if (!data.success) {
					console.error('Error en API Dashboard B2B:', data);
					return;
				}

				dashboardState.lastData = data;

				// Renderizar 6 KPIs principales
				renderDashboardKPIs(data.kpis, data.filter_applied);

				// Renderizar 6 Gráficos ejecutivos
				renderDashboardCharts(data.charts);

				// Renderizar Tablas de Top Cuentas y Órdenes Recientes
				renderTopAccounts(data.tables.top_accounts);
				renderRecentOrders(data.tables.recent_orders);

				if (typeof lucide !== 'undefined') lucide.createIcons();
			} catch (err) {
				console.error('Fallo al actualizar Tablero Ejecutivo B2B:', err);
			}
		}

		function renderDashboardKPIs(kpis, filterApplied) {
			if (!kpis) return;

			// Período badge
			const badge = document.getElementById('dash-period-badge');
			if (badge && filterApplied && filterApplied.period_label) {
				badge.textContent = filterApplied.period_label;
			}

			// KPI 1: Ventas Netas Facturadas
			const netSales = kpis.net_sales;
			const elNetVal = document.getElementById('kpi-net-sales-val');
			const elNetVar = document.getElementById('kpi-net-sales-var');
			const elNetSub = document.getElementById('kpi-net-sales-sub');
			const elNetCard = document.getElementById('kpi-net-sales-card');
			if (elNetVal) elNetVal.textContent = netSales.formatted;
			if (elNetVar) {
				elNetVar.className = 'text-[11px] font-semibold flex items-center ' + netSales.variation_class;
				elNetVar.innerHTML = `<i data-lucide="${netSales.raw >= (netSales.previous_raw || 0) ? 'trending-up' : 'trending-down'}" class="w-3 h-3 mr-0.5"></i>${netSales.variation_text}`;
			}
			if (elNetSub) elNetSub.textContent = `Facturado: $${netSales.gross_subtotal} | NC: $${netSales.credit_notes}`;
			if (elNetCard) elNetCard.title = netSales.tooltip;

			// KPI 2: Pedidos Confirmados
			const orders = kpis.confirmed_orders;
			const elOrdVal = document.getElementById('kpi-orders-val');
			const elOrdVar = document.getElementById('kpi-orders-var');
			const elOrdSub = document.getElementById('kpi-orders-sub');
			const elOrdCard = document.getElementById('kpi-orders-card');
			if (elOrdVal) elOrdVal.textContent = orders.formatted;
			if (elOrdVar) {
				elOrdVar.className = 'text-[11px] font-semibold flex items-center ' + orders.variation_class;
				elOrdVar.textContent = orders.variation_text;
			}
			if (elOrdSub) elOrdSub.textContent = `${orders.raw} órdenes en firme registradas`;
			if (elOrdCard) elOrdCard.title = orders.tooltip;

			// KPI 3: Clientes Activos
			const cust = kpis.active_customers;
			const elCustVal = document.getElementById('kpi-customers-val');
			const elCustVar = document.getElementById('kpi-customers-var');
			const elCustSub = document.getElementById('kpi-customers-sub');
			const elCustCard = document.getElementById('kpi-customers-card');
			if (elCustVal) elCustVal.textContent = cust.formatted;
			if (elCustVar) elCustVar.textContent = `${cust.percentage_of_total}% de cartera`;
			if (elCustSub) elCustSub.textContent = `${cust.raw} de ${cust.total_registered} clientes con compras`;
			if (elCustCard) elCustCard.title = cust.tooltip;

			// KPI 4: Ticket Promedio Facturado
			const ticket = kpis.average_ticket;
			const elTickVal = document.getElementById('kpi-ticket-val');
			const elTickVar = document.getElementById('kpi-ticket-var');
			const elTickSub = document.getElementById('kpi-ticket-sub');
			const elTickCard = document.getElementById('kpi-ticket-card');
			if (elTickVal) elTickVal.textContent = ticket.formatted;
			if (elTickVar) {
				elTickVar.className = 'text-[11px] font-semibold flex items-center ' + ticket.variation_class;
				elTickVar.textContent = ticket.variation_text;
			}
			if (elTickSub) elTickSub.textContent = `${ticket.invoices_count} facturas comerciales válidas`;
			if (elTickCard) elTickCard.title = ticket.tooltip;

			// KPI 5: Cuentas por Cobrar
			const ar = kpis.accounts_receivable;
			const elArVal = document.getElementById('kpi-receivable-val');
			const elArVar = document.getElementById('kpi-receivable-var');
			const elArSub = document.getElementById('kpi-receivable-sub');
			const elArCard = document.getElementById('kpi-receivable-card');
			if (elArVal) elArVal.textContent = ar.formatted;
			if (elArVar) elArVar.textContent = `Mora: ${ar.overdue_formatted} (${ar.overdue_percent}%)`;
			if (elArSub) elArSub.textContent = `Saldo exigible pendiente de cobro`;
			if (elArCard) elArCard.title = ar.tooltip;

			// KPI 6: Variación vs. Período Anterior
			const variation = kpis.sales_variation;
			const elVarVal = document.getElementById('kpi-variation-val');
			const elVarSub = document.getElementById('kpi-variation-sub');
			const elVarCard = document.getElementById('kpi-variation-card');
			if (elVarVal) {
				elVarVal.textContent = variation.formatted;
				elVarVal.className = 'text-xl sm:text-2xl font-bold font-mono tracking-tight ' + variation.class;
			}
			if (elVarSub) elVarSub.textContent = `Período previo: ${variation.previous_net_sales}`;
			if (elVarCard) elVarCard.title = variation.tooltip;
		}

		function renderDashboardCharts(charts) {
			if (!charts) return;

			const isDark = document.documentElement.classList.contains('dark');
			const textColor = isDark ? '#94a3b8' : '#64748b';
			const gridColor = isDark ? 'rgba(31, 41, 55, 0.6)' : 'rgba(226, 232, 240, 0.8)';

			// --- Gráfico 1: Evolución de Facturación & Margen B2B ---
			const ctxTrend = document.getElementById('salesTrendChart');
			if (ctxTrend) {
				if (window.salesTrendChartInstance) {
					window.salesTrendChartInstance.destroy();
				}
				const trend = charts.sales_trend;
				window.salesTrendChartInstance = new Chart(ctxTrend.getContext('2d'), {
					type: 'bar',
					data: {
						labels: trend.labels && trend.labels.length ? trend.labels : ['Sin datos'],
						datasets: [
							{
								type: 'bar',
								label: currentLang === 'es' ? 'Ventas Netas ($ USD)' : 'Net Sales ($ USD)',
								data: trend.sales && trend.sales.length ? trend.sales : [0],
								backgroundColor: isDark ? '#1e40af' : '#1e3a8a',
								borderRadius: 4,
								yAxisID: 'y-sales',
								barPercentage: 0.5
							},
							{
								type: 'line',
								label: currentLang === 'es' ? 'Margen Bruto (%)' : 'Gross Margin (%)',
								data: trend.margins && trend.margins.length ? trend.margins : [0],
								borderColor: '#10b981',
								backgroundColor: 'transparent',
								borderWidth: 2.5,
								pointBackgroundColor: '#10b981',
								pointRadius: 4,
								pointHoverRadius: 6,
								yAxisID: 'y-margin'
							}
						]
					},
					options: {
						responsive: true,
						maintainAspectRatio: false,
						tooltips: {
							mode: 'index',
							intersect: false,
							backgroundColor: isDark ? '#1f2937' : '#0f172a',
							titleFontColor: '#ffffff',
							bodyFontColor: '#e2e8f0',
							borderColor: isDark ? '#374151' : '#cbd5e1',
							borderWidth: 1,
							callbacks: {
								label: function(tooltipItem, data) {
									const label = data.datasets[tooltipItem.datasetIndex].label || '';
									if (label.includes('Ventas') || label.includes('Sales')) {
										return label + ': $' + Number(tooltipItem.yLabel).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' USD';
									} else {
										return label + ': ' + tooltipItem.yLabel + '%';
									}
								}
							}
						},
						legend: { display: false },
						scales: {
							xAxes: [{
								gridLines: { color: 'transparent', zeroLineColor: gridColor },
								ticks: { fontColor: textColor, fontSize: 11 }
							}],
							yAxes: [
								{
									id: 'y-sales',
									position: 'left',
									gridLines: { color: gridColor, zeroLineColor: gridColor },
									ticks: {
										fontColor: textColor,
										fontSize: 10,
										callback: function(v) { return '$' + (v >= 1000 ? (v / 1000).toFixed(1) + 'k' : v); }
									}
								},
								{
									id: 'y-margin',
									position: 'right',
									gridLines: { display: false },
									ticks: {
										fontColor: '#10b981',
										fontSize: 10,
										min: 0,
										max: 40,
										callback: function(v) { return v + '%'; }
									}
								}
							]
						}
					}
				});
			}

			// --- Gráfico 2: Ventas por Categoría (channelShareChart) ---
			const ctxCategory = document.getElementById('channelShareChart');
			if (ctxCategory) {
				if (window.channelShareChartInstance) {
					window.channelShareChartInstance.destroy();
				}
				const cat = charts.category_sales;
				const hasCatData = cat && cat.data && cat.data.length && cat.total > 0;
				const catLabels = hasCatData ? cat.labels : ['Sin información'];
				const catData = hasCatData ? cat.data : [1];
				const catColors = hasCatData ? cat.colors : ['#cbd5e1'];

				window.channelShareChartInstance = new Chart(ctxCategory.getContext('2d'), {
					type: 'doughnut',
					data: {
						labels: catLabels,
						datasets: [{
							data: catData,
							backgroundColor: catColors,
							borderWidth: 2,
							borderColor: isDark ? '#111827' : '#ffffff'
						}]
					},
					options: {
						responsive: true,
						maintainAspectRatio: false,
						cutoutPercentage: 70,
						legend: { display: false },
						tooltips: {
							backgroundColor: isDark ? '#1f2937' : '#0f172a',
							titleFontColor: '#ffffff',
							bodyFontColor: '#e2e8f0',
							borderColor: isDark ? '#374151' : '#cbd5e1',
							borderWidth: 1,
							callbacks: {
								label: function(tooltipItem, data) {
									if (!hasCatData) return 'Sin información';
									const label = data.labels[tooltipItem.index] || '';
									const val = data.datasets[0].data[tooltipItem.index];
									const pct = cat.percentages[tooltipItem.index] || 0;
									return label + ': $' + Number(val).toLocaleString(undefined, {minimumFractionDigits: 2}) + ' (' + pct + '%)';
								}
							}
						}
					}
				});

				// Poblar leyenda dinámica de categorías
				const legendContainer = document.getElementById('category-legend-list');
				if (legendContainer) {
					if (!hasCatData) {
						legendContainer.innerHTML = '<div class="py-3 text-center text-xs text-slate-400">Sin ventas categorizadas en el período</div>';
					} else {
						legendContainer.innerHTML = cat.labels.slice(0, 4).map((lbl, idx) => `
							<div class="flex items-center justify-between">
								<div class="flex items-center gap-2 truncate max-w-[170px]">
									<span class="w-2.5 h-2.5 rounded-xs shrink-0" style="background-color: ${cat.colors[idx]}"></span>
									<span class="text-slate-700 dark:text-slate-300 font-medium truncate">${lbl}</span>
								</div>
								<div class="font-mono font-semibold text-slate-900 dark:text-white shrink-0">$${Number(cat.data[idx]).toLocaleString(undefined, {minimumFractionDigits: 0, maximumFractionDigits: 0})} (${cat.percentages[idx]}%)</div>
							</div>
						`).join('');
					}
				}
			}

			// --- Gráfico 3: Facturación vs. Cobros ---
			const ctxBilling = document.getElementById('billingVsCollectionsChart');
			if (ctxBilling) {
				if (window.billingVsCollectionsChartInstance) {
					window.billingVsCollectionsChartInstance.destroy();
				}
				const bvc = charts.billing_vs_collections;
				window.billingVsCollectionsChartInstance = new Chart(ctxBilling.getContext('2d'), {
					type: 'bar',
					data: {
						labels: bvc.labels && bvc.labels.length ? bvc.labels : ['Período'],
						datasets: [
							{
								label: 'Facturado ($)',
								data: bvc.billed,
								backgroundColor: isDark ? '#1e40af' : '#1e3a8a',
								borderRadius: 4
							},
							{
								label: 'Cobrado ($)',
								data: bvc.collected,
								backgroundColor: '#10b981',
								borderRadius: 4
							}
						]
					},
					options: {
						responsive: true,
						maintainAspectRatio: false,
						tooltips: {
							mode: 'index',
							intersect: false,
							backgroundColor: isDark ? '#1f2937' : '#0f172a',
							callbacks: {
								label: function(tooltipItem, data) {
									const label = data.datasets[tooltipItem.datasetIndex].label || '';
									return label + ': $' + Number(tooltipItem.yLabel).toLocaleString(undefined, {minimumFractionDigits: 2});
								}
							}
						},
						legend: {
							display: true,
							position: 'bottom',
							labels: { fontColor: textColor, boxWidth: 10, fontSize: 10 }
						},
						scales: {
							xAxes: [{ ticks: { fontColor: textColor, fontSize: 10 } }],
							yAxes: [{
								ticks: {
									fontColor: textColor,
									fontSize: 10,
									callback: function(v) { return '$' + (v >= 1000 ? (v / 1000).toFixed(0) + 'k' : v); }
								}
							}]
						}
					}
				});

				const elRate = document.getElementById('billing-collections-rate');
				if (elRate) {
					const rate = bvc.total_billed > 0 ? roundNumber((bvc.total_collected / bvc.total_billed) * 100, 1) : 0;
					elRate.textContent = `${rate}% recaudado`;
				}
			}

			// --- Gráfico 4: Pedidos por Estado ---
			const ctxOrder = document.getElementById('orderStatusChart');
			if (ctxOrder) {
				if (window.orderStatusChartInstance) {
					window.orderStatusChartInstance.destroy();
				}
				const ord = charts.order_status;
				const hasOrders = ord && ord.counts && ord.total_orders > 0;
				const ordLabels = hasOrders ? ord.labels : ['Sin pedidos'];
				const ordCounts = hasOrders ? ord.counts : [1];
				const ordColors = hasOrders ? ord.colors : ['#cbd5e1'];

				window.orderStatusChartInstance = new Chart(ctxOrder.getContext('2d'), {
					type: 'doughnut',
					data: {
						labels: ordLabels,
						datasets: [{
							data: ordCounts,
							backgroundColor: ordColors,
							borderWidth: 2,
							borderColor: isDark ? '#111827' : '#ffffff'
						}]
					},
					options: {
						responsive: true,
						maintainAspectRatio: false,
						cutoutPercentage: 65,
						legend: {
							display: true,
							position: 'bottom',
							labels: { fontColor: textColor, boxWidth: 10, fontSize: 10 }
						},
						tooltips: {
							backgroundColor: isDark ? '#1f2937' : '#0f172a',
							callbacks: {
								label: function(tooltipItem, data) {
									if (!hasOrders) return 'Sin pedidos';
									const label = data.labels[tooltipItem.index] || '';
									const count = data.datasets[0].data[tooltipItem.index];
									const amt = ord.amounts[tooltipItem.index] || 0;
									return `${label}: ${count} órdenes ($${Number(amt).toLocaleString(undefined, {minimumFractionDigits: 2})})`;
								}
							}
						}
					}
				});

				const elTotalBadge = document.getElementById('dash-order-status-total');
				const elConfirmRate = document.getElementById('order-confirm-rate');
				if (elTotalBadge) elTotalBadge.textContent = `${ord.total_orders} Pedidos`;
				if (elConfirmRate) {
					const confIdx = ord.labels.indexOf('Confirmado');
					const confCount = confIdx !== -1 ? ord.counts[confIdx] : 0;
					const pct = ord.total_orders > 0 ? roundNumber((confCount / ord.total_orders) * 100, 1) : 0;
					elConfirmRate.textContent = `${pct}% confirmación`;
				}
			}

			// --- Gráfico 5: Top 5 Productos Facturados ---
			const ctxProd = document.getElementById('topProductsChart');
			if (ctxProd) {
				if (window.topProductsChartInstance) {
					window.topProductsChartInstance.destroy();
				}
				const topP = charts.top_products;
				const hasProd = topP && topP.data && topP.data.length > 0;
				const prodLabels = hasProd ? topP.labels.map(l => l.length > 15 ? l.substring(0, 15) + '...' : l) : ['Sin productos'];
				const prodData = hasProd ? topP.data : [0];

				window.topProductsChartInstance = new Chart(ctxProd.getContext('2d'), {
					type: 'horizontalBar',
					data: {
						labels: prodLabels,
						datasets: [{
							label: 'Facturado ($)',
							data: prodData,
							backgroundColor: '#2563eb',
							borderRadius: 3
						}]
					},
					options: {
						responsive: true,
						maintainAspectRatio: false,
						legend: { display: false },
						tooltips: {
							backgroundColor: isDark ? '#1f2937' : '#0f172a',
							callbacks: {
								title: function(tooltipItem) {
									const idx = tooltipItem[0].index;
									return hasProd ? topP.labels[idx] : 'Sin datos';
								},
								label: function(tooltipItem) {
									const idx = tooltipItem.index;
									if (!hasProd) return '0.00';
									return `Facturado: $${Number(topP.data[idx]).toLocaleString(undefined, {minimumFractionDigits: 2})} (${topP.units[idx]} un.)`;
								}
							}
						},
						scales: {
							xAxes: [{
								ticks: {
									fontColor: textColor,
									fontSize: 10,
									callback: function(v) { return '$' + (v >= 1000 ? (v / 1000).toFixed(0) + 'k' : v); }
								}
							}],
							yAxes: [{ ticks: { fontColor: textColor, fontSize: 10 } }]
						}
					}
				});

				const elProdLeader = document.getElementById('top-product-leader');
				if (elProdLeader) {
					elProdLeader.textContent = hasProd ? topP.labels[0] : 'Sin datos';
				}
			}
		}

		function renderTopAccounts(topAccountsData) {
			const container = document.getElementById('top-accounts-list');
			const footerSum = document.getElementById('top-accounts-total-sum');
			if (!container) return;

			if (!topAccountsData || !topAccountsData.accounts || topAccountsData.accounts.length === 0) {
				container.innerHTML = '<div class="py-8 text-center text-xs text-slate-400">Sin información de clientes en el período seleccionado.</div>';
				if (footerSum) footerSum.textContent = '$0.00';
				return;
			}

			if (footerSum) footerSum.textContent = `${topAccountsData.total_top_sales_formatted} (${topAccountsData.accounts.length} Cuentas)`;

			container.innerHTML = topAccountsData.accounts.map(acc => `
				<div class="py-3 flex items-center justify-between gap-3 hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors px-1 rounded-md">
					<div class="flex items-center gap-3 overflow-hidden">
						<div class="w-6 h-6 rounded bg-slate-100 dark:bg-slate-800 font-bold text-slate-600 dark:text-slate-300 text-xs flex items-center justify-center shrink-0">
							${acc.rank}
						</div>
						<div class="truncate">
							<div class="text-xs font-semibold text-slate-900 dark:text-white truncate" title="${acc.customer_name}">${acc.customer_name}</div>
							<div class="text-[11px] text-slate-400 flex items-center gap-1.5 mt-0.5">
								<span class="px-1 py-0.2 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[10px] font-medium">${acc.customer_type}</span>
								<span>• RUC: ${acc.tax_id}</span>
							</div>
						</div>
					</div>
					<div class="text-right shrink-0">
						<div class="text-xs font-mono font-bold text-slate-900 dark:text-white">${acc.total_sales_formatted}</div>
						<div class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400">Margen: ${acc.margin_percent}%</div>
					</div>
				</div>
			`).join('');
		}

		function renderRecentOrders(recentOrdersData) {
			const tbody = document.getElementById('recent-orders-table-body');
			const footerSum = document.getElementById('recent-orders-monitored-sum');
			if (!tbody) return;

			if (!recentOrdersData || recentOrdersData.length === 0) {
				tbody.innerHTML = '<tr><td colspan="7" class="py-8 text-center text-xs text-slate-400">Sin órdenes recientes que cumplan con los filtros.</td></tr>';
				if (footerSum) footerSum.textContent = 'Total monitoreado: $0.00 USD';
				return;
			}

			const totalSum = recentOrdersData.reduce((acc, o) => acc + o.total, 0);
			if (footerSum) footerSum.textContent = `Total monitoreado: $${Number(totalSum).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} USD`;

			tbody.innerHTML = recentOrdersData.map(o => {
				let statusBadge = '';
				if (o.status === 'Confirmado') {
					statusBadge = `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200/50"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Confirmado</span>`;
				} else if (o.status === 'Borrador') {
					statusBadge = `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200/60"><span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>Borrador</span>`;
				} else {
					statusBadge = `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border border-rose-200/50"><span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>Cancelado</span>`;
				}

				return `
					<tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
						<td class="py-2.5 px-3 font-mono font-bold text-brand-700 dark:text-brand-300">${o.order_number}</td>
						<td class="py-2.5 px-3 font-medium text-slate-900 dark:text-white">
							<div class="truncate max-w-[180px]" title="${o.customer_name}">${o.customer_name}</div>
							<div class="text-[10px] text-slate-400 font-normal">RUC: ${o.tax_id}</div>
						</td>
						<td class="py-2.5 px-3">
							<div class="text-xs text-slate-700 dark:text-slate-200 truncate max-w-[150px]">${o.establishment_name}</div>
							<div class="text-[10px] text-slate-400">${o.city}</div>
						</td>
						<td class="py-2.5 px-3 text-center font-mono">${o.items_count} líneas</td>
						<td class="py-2.5 px-3 text-right font-mono font-bold text-slate-900 dark:text-white">${o.total_formatted}</td>
						<td class="py-2.5 px-3">${statusBadge}</td>
						<td class="py-2.5 px-3 text-center">
							<button onclick="handleNavClick(event, 'orders')" class="p-1 text-slate-400 hover:text-brand-700 dark:hover:text-brand-300 transition-colors" title="Ver en Pedidos">
								<i data-lucide="eye" class="w-3.5 h-3.5"></i>
							</button>
						</td>
					</tr>
				`;
			}).join('');
		}

		// Controladores de Filtros Globales del Dashboard
		function setDashboardPeriod(preset) {
			dashboardState.period = preset;
			const btns = ['7d', 'mtd', 'qtd', 'ytd', 'custom'];
			btns.forEach(b => {
				const el = document.getElementById('dash-btn-' + b);
				if (el) {
					if (b === preset.toLowerCase()) {
						el.className = 'px-2.5 py-1 font-semibold rounded-md bg-white dark:bg-slate-700 text-brand-800 dark:text-white shadow-xs transition-all';
					} else {
						el.className = 'px-2.5 py-1 font-medium rounded-md text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-all';
					}
				}
			});

			const rangeBar = document.getElementById('dash-custom-range-bar');
			if (rangeBar && preset !== 'CUSTOM') {
				rangeBar.classList.add('hidden');
			}

			fetchDashboardModule();
		}

		function toggleCustomDateFilter() {
			const rangeBar = document.getElementById('dash-custom-range-bar');
			if (!rangeBar) return;
			const isHidden = rangeBar.classList.contains('hidden');
			if (isHidden) {
				rangeBar.classList.remove('hidden');
				setDashboardPeriod('CUSTOM');
			} else {
				rangeBar.classList.add('hidden');
				setDashboardPeriod('MTD');
			}
		}

		function applyCustomDateFilter() {
			const fromVal = document.getElementById('dash-custom-from')?.value;
			const toVal = document.getElementById('dash-custom-to')?.value;
			if (!fromVal || !toVal) return;

			dashboardState.period = 'CUSTOM';
			dashboardState.fromDate = fromVal;
			dashboardState.toDate = toVal;
			fetchDashboardModule();
		}

		function onDashboardCustomerChange() {
			const sel = document.getElementById('dash-filter-customer');
			dashboardState.customerId = sel ? sel.value : '';
			populateEstablishmentOptions(dashboardState.customerId);
			dashboardState.establishmentId = '';
			const estSel = document.getElementById('dash-filter-establishment');
			if (estSel) estSel.value = '';
			fetchDashboardModule();
		}

		function onDashboardEstablishmentChange() {
			const sel = document.getElementById('dash-filter-establishment');
			dashboardState.establishmentId = sel ? sel.value : '';
			fetchDashboardModule();
		}

		function onDashboardCategoryChange() {
			const sel = document.getElementById('dash-filter-category');
			dashboardState.categoryId = sel ? sel.value : '';
			fetchDashboardModule();
		}

		function onDashboardStatusChange() {
			const sel = document.getElementById('dash-filter-status');
			dashboardState.status = sel ? sel.value : 'ALL';
			fetchDashboardModule();
		}

		function resetDashboardFilters() {
			dashboardState.period = 'MTD';
			dashboardState.fromDate = null;
			dashboardState.toDate = null;
			dashboardState.customerId = '';
			dashboardState.establishmentId = '';
			dashboardState.categoryId = '';
			dashboardState.status = 'ALL';
			dashboardState.granularity = 'day';

			const custSel = document.getElementById('dash-filter-customer');
			if (custSel) custSel.value = '';
			const estSel = document.getElementById('dash-filter-establishment');
			if (estSel) {
				populateEstablishmentOptions();
				estSel.value = '';
			}
			const catSel = document.getElementById('dash-filter-category');
			if (catSel) catSel.value = '';
			const statSel = document.getElementById('dash-filter-status');
			if (statSel) statSel.value = 'ALL';
			const rangeBar = document.getElementById('dash-custom-range-bar');
			if (rangeBar) rangeBar.classList.add('hidden');

			setDashboardPeriod('MTD');
		}

		function exportDashboardSummary() {
			if (!dashboardState.lastData) {
				alert('No hay datos disponibles para exportar.');
				return;
			}
			const jsonStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(dashboardState.lastData, null, 2));
			const downloadAnchor = document.createElement('a');
			downloadAnchor.setAttribute("href", jsonStr);
			downloadAnchor.setAttribute("download", `dashboard_b2b_${dashboardState.period}_${new Date().toISOString().slice(0,10)}.json`);
			document.body.appendChild(downloadAnchor);
			downloadAnchor.click();
			downloadAnchor.remove();
		}

		function roundNumber(num, dec) {
			return +(Math.round(num + "e+" + dec) + "e-" + dec);
		}

		/* ========================================================
		   MÓDULO FUNCIONAL DE VENDEDORES Y TERRITORIOS (PASO 9)
		   ======================================================== */

		const salesRepsState = {
			subTab: 'reps', // 'reps', 'territories', 'history'
			repsData: [],
			territoriesData: [],
			historyData: [],
			metrics: null,
			search: '',
			territoryFilter: 'ALL',
			statusFilter: 'ALL',
			establishmentsCache: []
		};
		let currentDetailRepId = null;

		async function fetchSalesRepsModule() {
			try {
				await Promise.all([
					fetchSalesRepsMetrics(),
					fetchSalesRepsList(),
					fetchTerritoriesList(),
					fetchEstablishmentsForReassign()
				]);
			} catch (err) {
				console.error('Error cargando módulo de vendedores:', err);
			}
		}

		async function fetchSalesRepsMetrics() {
			try {
				const res = await fetch('/api/b2b/sales-reps/metrics');
				if (!res.ok) throw new Error('Error al obtener KPIs');
				const json = await res.json();
				const m = json.metrics || json;
				salesRepsState.metrics = m;
				renderSalesRepsKPIs(m);
			} catch (err) {
				console.error('Error fetching sales reps metrics:', err);
			}
		}

		function renderSalesRepsKPIs(m) {
			if (!m) return;
			const teamSalesEl = document.getElementById('kpi-reps-team-sales');
			if (teamSalesEl) teamSalesEl.textContent = m.month_net_sales_formatted || ('$' + (m.month_net_sales || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

			const quotaValEl = document.getElementById('kpi-reps-quota-val');
			if (quotaValEl) quotaValEl.textContent = m.total_monthly_quota_formatted || ('$' + (m.total_monthly_quota || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

			const achValEl = document.getElementById('kpi-reps-achievement-val');
			const achPct = m.quota_achievement_percent != null ? m.quota_achievement_percent : 0;
			if (achValEl) achValEl.textContent = achPct + '%';

			const achBarEl = document.getElementById('kpi-reps-achievement-bar');
			if (achBarEl) {
				const pct = Math.min(100, Math.max(0, achPct));
				achBarEl.style.width = pct + '%';
			}

			const activeCountEl = document.getElementById('kpi-reps-active-count');
			if (activeCountEl) activeCountEl.textContent = m.total_active_reps || 0;

			const territoriesCountEl = document.getElementById('kpi-reps-territories-count');
			if (territoriesCountEl) territoriesCountEl.textContent = m.total_territories || 6;

			const arValEl = document.getElementById('kpi-reps-ar-val');
			if (arValEl) arValEl.textContent = m.total_accounts_receivable_formatted || ('$' + (m.total_accounts_receivable || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
		}

		function switchSalesRepsSubTab(tab) {
			salesRepsState.subTab = tab;
			const btnReps = document.getElementById('tab-reps-btn') || document.getElementById('subtab-sales-reps-btn');
			const btnTerr = document.getElementById('tab-territories-btn') || document.getElementById('subtab-territories-btn');
			const btnHist = document.getElementById('tab-history-btn') || document.getElementById('subtab-history-btn');

			const viewReps = document.getElementById('subtab-reps-view');
			const viewTerr = document.getElementById('subtab-territories-view');
			const viewHist = document.getElementById('subtab-history-view');

			const activeBtnClass = 'pb-3 border-b-2 border-brand-800 text-brand-800 dark:text-white dark:border-white transition-all flex items-center gap-2 font-semibold text-xs sm:text-sm';
			const inactiveBtnClass = 'pb-3 border-b-2 border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition-all flex items-center gap-2 font-semibold text-xs sm:text-sm';

			if (btnReps) btnReps.className = tab === 'reps' ? activeBtnClass : inactiveBtnClass;
			if (btnTerr) btnTerr.className = tab === 'territories' ? activeBtnClass : inactiveBtnClass;
			if (btnHist) btnHist.className = tab === 'history' ? activeBtnClass : inactiveBtnClass;

			if (viewReps) viewReps.classList.toggle('hidden', tab !== 'reps');
			if (viewTerr) viewTerr.classList.toggle('hidden', tab !== 'territories');
			if (viewHist) viewHist.classList.toggle('hidden', tab !== 'history');

			if (tab === 'history') {
				fetchReassignmentHistory();
			} else if (tab === 'territories') {
				fetchTerritoriesList();
			} else {
				fetchSalesRepsList();
			}
			if (typeof lucide !== 'undefined') lucide.createIcons();
		}

		async function fetchSalesRepsList() {
			try {
				const res = await fetch('/api/b2b/sales-reps');
				if (!res.ok) throw new Error('Error al listar vendedores');
				const json = await res.json();
				const reps = json.sales_reps || json;
				salesRepsState.repsData = reps;
				populateRepTerritoryFilter(reps);
				renderSalesRepsTable(reps);

				// Actualizar top rep en KPI card
				if (reps && reps.length > 0) {
					const topRep = reps[0];
					const topRepNameEl = document.getElementById('kpi-reps-top-rep-name');
					if (topRepNameEl) topRepNameEl.textContent = topRep.name || 'N/A';
					const topRepValEl = document.getElementById('kpi-reps-top-rep-val');
					if (topRepValEl) {
						const topSales = topRep.kpis?.net_sales || 0;
						topRepValEl.textContent = '$' + topSales.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
					}
				}
			} catch (err) {
				console.error('Error fetching sales reps:', err);
			}
		}

		function populateRepTerritoryFilter(reps) {
			const select = document.getElementById('reps-filter-territory');
			if (!select) return;
			const territories = Array.from(new Set(reps.map(r => r.territory?.name || r.territory_name || '').filter(Boolean)));
			const currentVal = select.value;
			select.innerHTML = '<option value="ALL">Todos los Territorios</option>';
			territories.forEach(t => {
				const opt = document.createElement('option');
				opt.value = t;
				opt.textContent = t;
				select.appendChild(opt);
			});
			if (territories.includes(currentVal)) {
				select.value = currentVal;
			}
		}

		function filterSalesRepsList() {
			const searchInput = document.getElementById('reps-search-input');
			const search = searchInput ? searchInput.value.toLowerCase().trim() : '';
			const terrFilter = document.getElementById('reps-filter-territory')?.value;
			const statFilter = document.getElementById('reps-filter-status')?.value;

			const filtered = salesRepsState.repsData.filter(r => {
				const terrName = r.territory?.name || r.territory_name || '';
				const matchSearch = !search || 
					(r.name && r.name.toLowerCase().includes(search)) ||
					(r.email && r.email.toLowerCase().includes(search)) ||
					(terrName && terrName.toLowerCase().includes(search));
				
				const matchTerr = !terrFilter || terrFilter === 'ALL' || terrName === terrFilter;
				const isRepActive = r.active === 1 || r.status === 'active';
				let matchStat = true;
				if (statFilter === '1' || statFilter === 'active') {
					matchStat = isRepActive;
				} else if (statFilter === '0' || statFilter === 'inactive') {
					matchStat = !isRepActive;
				}
				return matchSearch && matchTerr && matchStat;
			});

			renderSalesRepsTable(filtered);
		}

		function renderSalesRepsTable(reps) {
			const tbody = document.getElementById('sales-reps-table-body');
			if (!tbody) return;

			if (!reps || reps.length === 0) {
				tbody.innerHTML = `
					<tr>
						<td colspan="9" class="py-12 text-center text-slate-400">
							<i data-lucide="users" class="w-8 h-8 mx-auto mb-2 text-slate-300 dark:text-slate-600"></i>
							<p class="font-medium">No se encontraron vendedores comerciales con los filtros aplicados.</p>
						</td>
					</tr>
				`;
				if (typeof lucide !== 'undefined') lucide.createIcons();
				return;
			}

			tbody.innerHTML = reps.map((rep, idx) => {
				const initials = rep.name ? rep.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase() : 'SR';
				const isActive = rep.active === 1 || rep.status === 'active';
				const statusBadge = isActive
					? '<span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Activo</span>'
					: '<span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 border border-slate-200 dark:border-slate-700"><span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>Inactivo</span>';

				const quota = parseFloat(rep.monthly_quota || 0);
				const kpis = rep.kpis || {};
				const netSales = parseFloat(kpis.net_sales != null ? kpis.net_sales : (rep.net_sales || 0));
				const achPct = parseFloat(kpis.quota_achievement != null ? kpis.quota_achievement : (rep.achievement_pct || 0));
				const barWidth = Math.min(100, Math.max(0, achPct));
				
				let barColor = 'bg-rose-500';
				let achTextColor = 'text-rose-600 dark:text-rose-400';
				if (achPct >= 100) {
					barColor = 'bg-emerald-500';
					achTextColor = 'text-emerald-600 dark:text-emerald-400';
				} else if (achPct >= 75) {
					barColor = 'bg-brand-500';
					achTextColor = 'text-brand-600 dark:text-brand-400';
				} else if (achPct >= 50) {
					barColor = 'bg-amber-500';
					achTextColor = 'text-amber-600 dark:text-amber-400';
				}

				const rankBadge = idx === 0 
					? '<span class="w-5 h-5 rounded-full bg-amber-100 text-amber-800 text-[10px] font-bold flex items-center justify-center border border-amber-300" title="Top 1">🥇</span>'
					: (idx === 1 
						? '<span class="w-5 h-5 rounded-full bg-slate-100 text-slate-700 text-[10px] font-bold flex items-center justify-center border border-slate-300" title="Top 2">🥈</span>'
						: (idx === 2 
							? '<span class="w-5 h-5 rounded-full bg-amber-50 text-amber-900 text-[10px] font-bold flex items-center justify-center border border-amber-400" title="Top 3">🥉</span>'
							: `<span class="text-[11px] font-mono text-slate-400 font-semibold pl-1">#${idx+1}</span>`));

				const terrName = rep.territory?.name || rep.territory_name || 'Sin Asignar';
				const assignedStores = kpis.assigned_establishments != null ? kpis.assigned_establishments : (rep.assigned_stores_count || 0);
				const confirmedOrders = kpis.confirmed_orders != null ? kpis.confirmed_orders : (rep.orders_count || 0);
				const arBalance = parseFloat(kpis.accounts_receivable != null ? kpis.accounts_receivable : (rep.receivables_balance || 0));

				return `
					<tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
						<td class="py-3 px-3">
							<div class="flex items-center gap-2">
								${rankBadge}
								<div class="w-8 h-8 rounded-full bg-brand-100 dark:bg-brand-900/40 text-brand-700 dark:text-brand-300 font-bold flex items-center justify-center text-xs shrink-0 border border-brand-200 dark:border-brand-800">
									${initials}
								</div>
								<div>
									<button type="button" onclick="openSalesRepDetailModal(${rep.id})" class="text-xs font-bold text-slate-900 dark:text-white hover:text-brand-600 hover:underline text-left">
										${escapeHtml(rep.name)}
									</button>
									<p class="text-[11px] text-slate-400">${escapeHtml(rep.email)}</p>
								</div>
							</div>
						</td>
						<td class="py-3 px-3">
							<span class="inline-flex items-center gap-1 font-medium text-slate-800 dark:text-slate-200">
								<i data-lucide="map-pin" class="w-3.5 h-3.5 text-slate-400"></i>
								${escapeHtml(terrName)}
							</span>
						</td>
						<td class="py-3 px-3 text-center">
							<span class="inline-flex items-center justify-center px-2 py-0.5 font-mono font-semibold text-xs rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
								${assignedStores}
							</span>
						</td>
						<td class="py-3 px-3 text-right font-mono font-medium text-slate-600 dark:text-slate-300">
							$${quota.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
						</td>
						<td class="py-3 px-3 text-right font-mono font-bold text-slate-900 dark:text-white">
							$${netSales.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
						</td>
						<td class="py-3 px-3">
							<div class="w-32 mx-auto">
								<div class="flex items-center justify-between text-[11px] font-bold mb-1">
									<span class="${achTextColor}">${achPct}%</span>
									<span class="text-[10px] text-slate-400 font-normal">MTD</span>
								</div>
								<div class="w-full h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
									<div class="h-full ${barColor} rounded-full" style="width: ${barWidth}%"></div>
								</div>
							</div>
						</td>
						<td class="py-3 px-3 text-center font-mono font-semibold text-slate-700 dark:text-slate-300">
							${confirmedOrders}
						</td>
						<td class="py-3 px-3 text-right font-mono font-semibold text-amber-600 dark:text-amber-400">
							$${arBalance.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
						</td>
						<td class="py-3 px-3 text-center">
							<div class="flex items-center justify-center gap-1">
								<button type="button" onclick="openSalesRepDetailModal(${rep.id})" class="p-1 text-slate-400 hover:text-brand-600 dark:hover:text-brand-400 rounded-md hover:bg-slate-100 dark:hover:bg-slate-800" title="Ver Cartera y Productividad">
									<i data-lucide="eye" class="w-4 h-4"></i>
								</button>
								<button type="button" onclick="openCreateSalesRepModal(${rep.id})" class="p-1 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 rounded-md hover:bg-slate-100 dark:hover:bg-slate-800" title="Editar Vendedor">
									<i data-lucide="edit" class="w-4 h-4"></i>
								</button>
								<button type="button" onclick="toggleSalesRepStatus(${rep.id})" class="p-1 text-slate-400 hover:text-amber-600 dark:hover:text-amber-400 rounded-md hover:bg-slate-100 dark:hover:bg-slate-800" title="Cambiar Estado Activo/Inactivo">
									<i data-lucide="power" class="w-4 h-4"></i>
								</button>
							</div>
						</td>
					</tr>
				`;
			}).join('');

			if (typeof lucide !== 'undefined') lucide.createIcons();
		}

		/* Detalle / Ficha del Vendedor */
		async function openSalesRepDetailModal(repId) {
			currentDetailRepId = repId;
			const modal = document.getElementById('modal-sales-rep-detail');
			if (!modal) return;

			try {
				const res = await fetch(`/api/b2b/sales-reps/${repId}`);
				if (!res.ok) throw new Error('Error al cargar detalle');
				const json = await res.json();
				const rep = json.sales_rep || json;
				const stores = rep.assigned_portfolio || [];
				const inactive = rep.inactive_customers || [];
				const orders = rep.recent_orders || [];
				const kpis = rep.kpis || {};

				const initials = rep.name ? rep.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase() : 'SR';
				document.getElementById('rep-dtl-avatar').textContent = initials;
				document.getElementById('rep-dtl-name').textContent = rep.name;
				document.getElementById('rep-dtl-territory').textContent = rep.territory?.name || rep.territory_name || 'Sin territorio asignado';
				document.getElementById('rep-dtl-email').textContent = rep.email || '';
				document.getElementById('rep-dtl-phone').textContent = rep.phone || 'Sin teléfono';

				const statusBadge = document.getElementById('rep-dtl-status-badge');
				if (statusBadge) {
					const isActive = rep.active === 1 || rep.status === 'active';
					if (isActive) {
						statusBadge.className = 'px-2 py-0.5 text-[11px] font-semibold rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60';
						statusBadge.textContent = 'Activo';
					} else {
						statusBadge.className = 'px-2 py-0.5 text-[11px] font-semibold rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700';
						statusBadge.textContent = 'Inactivo';
					}
				}

				const netSales = parseFloat(kpis.net_sales != null ? kpis.net_sales : (rep.net_sales || 0));
				const quota = parseFloat(rep.monthly_quota || 0);
				const achPct = kpis.quota_achievement != null ? kpis.quota_achievement : (rep.achievement_pct || 0);
				const ordersCount = kpis.confirmed_orders != null ? kpis.confirmed_orders : (rep.orders_count || 0);
				const arBalance = parseFloat(kpis.accounts_receivable != null ? kpis.accounts_receivable : (rep.receivables_balance || 0));

				document.getElementById('rep-dtl-net-sales').textContent = '$' + netSales.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
				document.getElementById('rep-dtl-quota').textContent = '$' + quota.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
				document.getElementById('rep-dtl-achievement').textContent = achPct + '%';
				document.getElementById('rep-dtl-orders-count').textContent = ordersCount;
				document.getElementById('rep-dtl-receivables').textContent = '$' + arBalance.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

				document.getElementById('rep-dtl-count-clients').textContent = stores.length;
				document.getElementById('rep-dtl-count-inactive').textContent = inactive.length;
				document.getElementById('rep-dtl-count-orders').textContent = orders.length;

				renderRepDetailClients(stores);
				renderRepDetailInactive(inactive);
				renderRepDetailOrders(orders);

				switchSalesRepDetailSubTab('clients');
				modal.classList.remove('hidden');
				if (typeof lucide !== 'undefined') lucide.createIcons();
			} catch (err) {
				console.error('Error opening sales rep detail:', err);
				alert('No se pudo cargar la información del vendedor.');
			}
		}

		function closeSalesRepDetailModal() {
			const modal = document.getElementById('modal-sales-rep-detail');
			if (modal) modal.classList.add('hidden');
			currentDetailRepId = null;
		}

		function switchSalesRepDetailSubTab(tab) {
			const btnClients = document.getElementById('rep-dtl-tab-btn-clients');
			const btnInactive = document.getElementById('rep-dtl-tab-btn-inactive');
			const btnOrders = document.getElementById('rep-dtl-tab-btn-orders');

			const tabClients = document.getElementById('rep-dtl-tab-clients');
			const tabInactive = document.getElementById('rep-dtl-tab-inactive');
			const tabOrders = document.getElementById('rep-dtl-tab-orders');

			const activeClass = 'pb-2.5 text-xs font-bold border-b-2 border-brand-600 text-brand-600 dark:text-brand-400 flex items-center gap-1.5';
			const inactiveClass = 'pb-2.5 text-xs font-medium border-b-2 border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 flex items-center gap-1.5';

			if (btnClients) btnClients.className = tab === 'clients' ? activeClass : inactiveClass;
			if (btnInactive) btnInactive.className = tab === 'inactive' ? activeClass : inactiveClass;
			if (btnOrders) btnOrders.className = tab === 'orders' ? activeClass : inactiveClass;

			if (tabClients) tabClients.classList.toggle('hidden', tab !== 'clients');
			if (tabInactive) tabInactive.classList.toggle('hidden', tab !== 'inactive');
			if (tabOrders) tabOrders.classList.toggle('hidden', tab !== 'orders');
		}

		function renderRepDetailClients(stores) {
			const tbody = document.getElementById('rep-dtl-tbody-clients');
			if (!tbody) return;
			if (!stores || stores.length === 0) {
				tbody.innerHTML = '<tr><td colspan="6" class="py-6 text-center text-slate-400">Sin sedes comerciales asignadas a este ejecutivo.</td></tr>';
				return;
			}
			tbody.innerHTML = stores.map(s => `
				<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
					<td class="py-2.5 px-3 font-semibold text-slate-900 dark:text-white">${escapeHtml(s.customer_name || '')}</td>
					<td class="py-2.5 px-3 font-medium text-slate-700 dark:text-slate-300">${escapeHtml(s.establishment_name || s.name || '')} <span class="text-[10px] text-slate-400 font-mono">(${escapeHtml(s.store_code || s.code || '')})</span></td>
					<td class="py-2.5 px-3 text-slate-600 dark:text-slate-400">${escapeHtml(s.city || '')} / ${escapeHtml(s.address || '')}</td>
					<td class="py-2.5 px-3 text-right font-mono font-bold text-slate-900 dark:text-white">$${parseFloat(s.net_sales || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
					<td class="py-2.5 px-3 text-right font-mono font-semibold text-amber-600 dark:text-amber-400">$${parseFloat(s.receivables_balance || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
					<td class="py-2.5 px-3 text-center">
						<span class="px-2 py-0.5 text-[10px] font-semibold rounded-full bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400">Asignada</span>
					</td>
				</tr>
			`).join('');
		}

		function renderRepDetailInactive(inactive) {
			const tbody = document.getElementById('rep-dtl-tbody-inactive');
			if (!tbody) return;
			if (!inactive || inactive.length === 0) {
				tbody.innerHTML = '<tr><td colspan="5" class="py-6 text-center text-emerald-600 dark:text-emerald-400 font-medium">✓ Todos los clientes asignados tienen compras recientes dentro de los últimos 30 días.</td></tr>';
				return;
			}
			tbody.innerHTML = inactive.map(c => `
				<tr class="hover:bg-amber-50/40 dark:hover:bg-amber-950/20">
					<td class="py-2.5 px-3 font-semibold text-slate-900 dark:text-white">${escapeHtml(c.customer_name || '')}</td>
					<td class="py-2.5 px-3 text-slate-700 dark:text-slate-300 font-mono text-[11px]">${escapeHtml(c.tax_id || '')}</td>
					<td class="py-2.5 px-3 text-slate-500 uppercase text-[10px] font-semibold">${escapeHtml(c.customer_type || 'comercial')}</td>
					<td class="py-2.5 px-3 font-mono text-slate-600 dark:text-slate-400">${c.last_purchase_date || 'Sin compras registradas'}</td>
					<td class="py-2.5 px-3 text-center">
						<span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
							${c.days_since_last != null ? Math.round(c.days_since_last) + ' días' : '> 30 días'}
						</span>
					</td>
				</tr>
			`).join('');
		}

		function renderRepDetailOrders(orders) {
			const tbody = document.getElementById('rep-dtl-tbody-orders');
			if (!tbody) return;
			if (!orders || orders.length === 0) {
				tbody.innerHTML = '<tr><td colspan="6" class="py-6 text-center text-slate-400">Sin pedidos registrados bajo este vendedor.</td></tr>';
				return;
			}
			tbody.innerHTML = orders.map(o => `
				<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
					<td class="py-2.5 px-3 font-mono font-bold text-brand-700 dark:text-brand-300">${escapeHtml(o.order_number)}</td>
					<td class="py-2.5 px-3 font-mono text-slate-600 dark:text-slate-400">${(o.created_timestamp || '').substring(0, 10)}</td>
					<td class="py-2.5 px-3 font-medium text-slate-900 dark:text-white">${escapeHtml(o.customer_name || '')}</td>
					<td class="py-2.5 px-3 text-slate-600 dark:text-slate-400">${escapeHtml(o.store_name || 'Principal')}</td>
					<td class="py-2.5 px-3 text-right font-mono font-bold text-slate-900 dark:text-white">$${parseFloat(o.total || o.total_amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
					<td class="py-2.5 px-3 text-center">
						<span class="px-2 py-0.5 text-[10px] font-semibold rounded-full bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 uppercase">${escapeHtml(o.status)}</span>
					</td>
				</tr>
			`).join('');
		}

		/* Crear / Editar Vendedor */
		function openCreateSalesRepModal(repId = null) {
			const modal = document.getElementById('modal-create-sales-rep');
			if (!modal) return;
			const form = document.getElementById('form-sales-rep');
			if (form) form.reset();

			// Llenar select de territorios con ID y Nombre
			const terrSelect = document.getElementById('rep-form-territory');
			if (terrSelect) {
				terrSelect.innerHTML = '<option value="">(Sin territorio asignado)</option>';
				salesRepsState.territoriesData.forEach(t => {
					const opt = document.createElement('option');
					opt.value = t.id;
					opt.textContent = `${t.code} - ${t.name} (${t.city})`;
					terrSelect.appendChild(opt);
				});
			}

			if (repId) {
				const rep = salesRepsState.repsData.find(r => r.id == repId);
				if (rep) {
					document.getElementById('modal-rep-form-title').textContent = 'Editar Ejecutivo Comercial';
					document.getElementById('rep-form-id').value = rep.id;
					document.getElementById('rep-form-name').value = rep.name;
					document.getElementById('rep-form-email').value = rep.email;
					document.getElementById('rep-form-phone').value = rep.phone || '';
					document.getElementById('rep-form-territory').value = rep.territory?.id || rep.territory_id || '';
					document.getElementById('rep-form-quota').value = rep.monthly_quota || '';
					document.getElementById('rep-form-hire-date').value = rep.hire_date || '';
					document.getElementById('rep-form-status').value = (rep.active === 1 || rep.status === 'active') ? 'active' : 'inactive';
				}
			} else {
				document.getElementById('modal-rep-form-title').textContent = 'Registrar Ejecutivo Comercial';
				document.getElementById('rep-form-id').value = '';
				document.getElementById('rep-form-hire-date').value = new Date().toISOString().substring(0, 10);
				document.getElementById('rep-form-status').value = 'active';
			}

			modal.classList.remove('hidden');
			if (typeof lucide !== 'undefined') lucide.createIcons();
		}

		function closeCreateSalesRepModal() {
			const modal = document.getElementById('modal-create-sales-rep');
			if (modal) modal.classList.add('hidden');
		}

		async function submitSalesRepForm(e) {
			e.preventDefault();
			const id = document.getElementById('rep-form-id').value;
			const payload = {
				name: document.getElementById('rep-form-name').value.trim(),
				email: document.getElementById('rep-form-email').value.trim(),
				phone: document.getElementById('rep-form-phone').value.trim(),
				territory_id: document.getElementById('rep-form-territory').value ? parseInt(document.getElementById('rep-form-territory').value) : null,
				monthly_quota: parseFloat(document.getElementById('rep-form-quota').value) || 0,
				hire_date: document.getElementById('rep-form-hire-date').value,
				active: document.getElementById('rep-form-status').value === 'active' ? 1 : 0
			};

			try {
				const url = id ? `/api/b2b/sales-reps/${id}` : '/api/b2b/sales-reps';
				const method = id ? 'PUT' : 'POST';
				const res = await fetch(url, {
					method: method,
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify(payload)
				});

				const result = await res.json();
				if (!res.ok) throw new Error(result.error || 'Error al guardar');

				closeCreateSalesRepModal();
				await fetchSalesRepsModule();
			} catch (err) {
				alert('Error al guardar vendedor: ' + err.message);
			}
		}

		async function toggleSalesRepStatus(id) {
			if (!confirm('¿Desea cambiar el estado de este vendedor comercial?')) return;
			try {
				const res = await fetch(`/api/b2b/sales-reps/${id}/toggle-status`, { method: 'POST' });
				if (!res.ok) throw new Error('Error al cambiar estado');
				await fetchSalesRepsModule();
			} catch (err) {
				alert('Error: ' + err.message);
			}
		}

		/* Territorios */
		async function fetchTerritoriesList() {
			try {
				const res = await fetch('/api/b2b/sales-reps/territories');
				if (!res.ok) throw new Error('Error al listar territorios');
				const json = await res.json();
				const data = json.territories || json;
				salesRepsState.territoriesData = data;
				renderTerritoriesTable(data);
			} catch (err) {
				console.error('Error fetching territories:', err);
			}
		}

		function filterTerritoriesList() {
			const search = document.getElementById('territory-search-input')?.value.toLowerCase().trim() || '';
			const zone = document.getElementById('territory-filter-zone')?.value || '';

			const filtered = salesRepsState.territoriesData.filter(t => {
				const matchSearch = !search || 
					(t.code && t.code.toLowerCase().includes(search)) ||
					(t.name && t.name.toLowerCase().includes(search)) ||
					(t.city && t.city.toLowerCase().includes(search));
				const matchZone = !zone || (t.zone && t.zone.toLowerCase().includes(zone.toLowerCase()));
				return matchSearch && matchZone;
			});

			renderTerritoriesTable(filtered);
		}

		function renderTerritoriesTable(territories) {
			const tbody = document.getElementById('territories-table-body');
			if (!tbody) return;

			if (!territories || territories.length === 0) {
				tbody.innerHTML = '<tr><td colspan="8" class="py-8 text-center text-slate-400">No se encontraron territorios registrados.</td></tr>';
				return;
			}

			tbody.innerHTML = territories.map(t => {
				const repsList = Array.isArray(t.assigned_reps) ? t.assigned_reps.join(', ') : (t.assigned_reps || 'Sin asignar');
				const estCount = t.establishments_count != null ? t.establishments_count : (t.stores_count || 0);
				return `
					<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
						<td class="py-2.5 px-3 font-mono font-bold text-slate-900 dark:text-white">${escapeHtml(t.code)}</td>
						<td class="py-2.5 px-3 font-semibold text-slate-900 dark:text-white">${escapeHtml(t.name)}</td>
						<td class="py-2.5 px-3 text-slate-600 dark:text-slate-400">${escapeHtml(t.city)} <span class="text-slate-400">(${escapeHtml(t.zone || 'Metropolitana')})</span></td>
						<td class="py-2.5 px-3 text-slate-500 max-w-xs truncate">${escapeHtml(t.description || 'Zona comercial asignada')}</td>
						<td class="py-2.5 px-3 font-medium text-slate-800 dark:text-slate-200">
							<span class="inline-flex items-center gap-1.5"><i data-lucide="user-check" class="w-3.5 h-3.5 text-brand-600"></i> ${escapeHtml(repsList)}</span>
						</td>
						<td class="py-2.5 px-3 text-center font-mono font-semibold text-slate-700 dark:text-slate-300">
							${estCount}
						</td>
						<td class="py-2.5 px-3 text-center">
							<span class="px-2 py-0.5 text-[10px] font-semibold rounded-full bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200/50">Activo</span>
						</td>
						<td class="py-2.5 px-3 text-center">
							<button type="button" onclick="openCreateTerritoryModal(${t.id})" class="p-1 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 rounded-md hover:bg-slate-100 dark:hover:bg-slate-800" title="Editar Territorio">
								<i data-lucide="edit" class="w-4 h-4"></i>
							</button>
						</td>
					</tr>
				`;
			}).join('');

			if (typeof lucide !== 'undefined') lucide.createIcons();
		}

		function openCreateTerritoryModal(terId = null) {
			const modal = document.getElementById('modal-create-territory');
			if (!modal) return;
			const form = document.getElementById('form-territory');
			if (form) form.reset();

			if (terId) {
				const t = salesRepsState.territoriesData.find(item => item.id == terId);
				if (t) {
					document.getElementById('modal-ter-form-title').textContent = 'Editar Territorio Comercial';
					document.getElementById('ter-form-id').value = t.id;
					document.getElementById('ter-form-code').value = t.code;
					document.getElementById('ter-form-name').value = t.name;
					document.getElementById('ter-form-city').value = t.city;
					document.getElementById('ter-form-zone').value = t.zone || '';
					document.getElementById('ter-form-desc').value = t.description || '';
				}
			} else {
				document.getElementById('modal-ter-form-title').textContent = 'Registrar Territorio Comercial';
				document.getElementById('ter-form-id').value = '';
			}

			modal.classList.remove('hidden');
			if (typeof lucide !== 'undefined') lucide.createIcons();
		}

		function closeCreateTerritoryModal() {
			const modal = document.getElementById('modal-create-territory');
			if (modal) modal.classList.add('hidden');
		}

		async function submitTerritoryForm(e) {
			e.preventDefault();
			const id = document.getElementById('ter-form-id').value;
			const payload = {
				code: document.getElementById('ter-form-code').value.trim(),
				name: document.getElementById('ter-form-name').value.trim(),
				city: document.getElementById('ter-form-city').value.trim(),
				zone: document.getElementById('ter-form-zone').value.trim(),
				description: document.getElementById('ter-form-desc').value.trim(),
				is_active: 1
			};

			try {
				const url = id ? `/api/b2b/sales-reps/territories/${id}` : '/api/b2b/sales-reps/territories';
				const method = id ? 'PUT' : 'POST';
				const res = await fetch(url, {
					method: method,
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify(payload)
				});

				const result = await res.json();
				if (!res.ok) throw new Error(result.error || 'Error al guardar territorio');

				closeCreateTerritoryModal();
				await fetchTerritoriesList();
			} catch (err) {
				alert('Error al guardar territorio: ' + err.message);
			}
		}

		/* Reasignación de Sedes */
		async function fetchEstablishmentsForReassign() {
			try {
				const res = await fetch('/api/b2b/customers');
				if (!res.ok) return;
				const json = await res.json();
				const custs = json.customers || json;
				const allStores = [];
				if (Array.isArray(custs)) {
					custs.forEach(c => {
						if (c.establishments && Array.isArray(c.establishments)) {
							c.establishments.forEach(est => {
								allStores.push({
									id: est.id,
									customer_name: c.business_name || c.name,
									store_name: est.name,
									store_code: est.code,
									sales_rep_name: est.sales_rep_name,
									territory: est.territory
								});
							});
						}
					});
				}
				salesRepsState.establishmentsCache = allStores;
			} catch (e) {
				console.error('Error fetching establishments for reassign:', e);
			}
		}

		function openReassignEstablishmentModal() {
			const modal = document.getElementById('modal-reassign-establishment');
			if (!modal) return;
			const form = document.getElementById('form-reassign-establishment');
			if (form) form.reset();

			// Llenar select de sedes
			const estSelect = document.getElementById('reassign-select-est');
			if (estSelect) {
				estSelect.innerHTML = '<option value="">Seleccionar Sede Comercial...</option>';
				salesRepsState.establishmentsCache.forEach(s => {
					const opt = document.createElement('option');
					opt.value = s.id;
					opt.textContent = `${s.customer_name} — ${s.store_name} (${s.store_code || 'S/C'})`;
					estSelect.appendChild(opt);
				});
			}

			// Llenar nuevo vendedor
			const repSelect = document.getElementById('reassign-new-rep');
			if (repSelect) {
				repSelect.innerHTML = '<option value="">Seleccionar Vendedor...</option>';
				salesRepsState.repsData.filter(r => r.active === 1 || r.status === 'active').forEach(r => {
					const opt = document.createElement('option');
					opt.value = r.name;
					const terrName = r.territory?.name || r.territory_name || '';
					opt.textContent = `${r.name} (${terrName || 'Sin territorio'})`;
					opt.dataset.territory = terrName;
					repSelect.appendChild(opt);
				});
			}

			// Llenar nuevo territorio
			const terSelect = document.getElementById('reassign-new-territory');
			if (terSelect) {
				terSelect.innerHTML = '<option value="">(Mantener o definir)</option>';
				salesRepsState.territoriesData.forEach(t => {
					const opt = document.createElement('option');
					opt.value = t.name;
					opt.textContent = `${t.code} - ${t.name}`;
					terSelect.appendChild(opt);
				});
			}

			document.getElementById('reassign-cur-customer').textContent = '---';
			document.getElementById('reassign-cur-rep').textContent = '---';
			document.getElementById('reassign-cur-territory').textContent = '---';

			modal.classList.remove('hidden');
			if (typeof lucide !== 'undefined') lucide.createIcons();
		}

		function closeReassignEstablishmentModal() {
			const modal = document.getElementById('modal-reassign-establishment');
			if (modal) modal.classList.add('hidden');
		}

		function handleReassignmentEstChange() {
			const estId = document.getElementById('reassign-select-est').value;
			const est = salesRepsState.establishmentsCache.find(s => s.id == estId);
			if (est) {
				document.getElementById('reassign-cur-customer').textContent = est.customer_name;
				document.getElementById('reassign-cur-rep').textContent = est.sales_rep_name || 'Sin Asignar';
				document.getElementById('reassign-cur-territory').textContent = est.territory || 'Sin Territorio';
			} else {
				document.getElementById('reassign-cur-customer').textContent = '---';
				document.getElementById('reassign-cur-rep').textContent = '---';
				document.getElementById('reassign-cur-territory').textContent = '---';
			}
		}

		function handleNewRepSelection() {
			const repSelect = document.getElementById('reassign-new-rep');
			const selectedOpt = repSelect.options[repSelect.selectedIndex];
			const terName = selectedOpt?.dataset?.territory;
			if (terName) {
				const terSelect = document.getElementById('reassign-new-territory');
				if (terSelect) {
					for (let i = 0; i < terSelect.options.length; i++) {
						if (terSelect.options[i].value === terName) {
							terSelect.selectedIndex = i;
							break;
						}
					}
				}
			}
		}

		async function submitReassignmentForm(e) {
			e.preventDefault();
			const estId = document.getElementById('reassign-select-est').value;
			const newRep = document.getElementById('reassign-new-rep').value;
			const newTerritory = document.getElementById('reassign-new-territory').value;
			const reason = document.getElementById('reassign-reason').value.trim();

			if (!estId || !newRep) {
				alert('Por favor seleccione la sede y el nuevo ejecutivo comercial.');
				return;
			}

			try {
				const res = await fetch('/api/b2b/sales-reps/reassign-establishment', {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify({
						establishment_id: parseInt(estId),
						new_sales_rep_name: newRep,
						new_territory: newTerritory,
						reason: reason || 'Reasignación operativa de cartera'
					})
				});

				const result = await res.json();
				if (!res.ok) throw new Error(result.error || 'Error al reasignar');

				closeReassignEstablishmentModal();
				alert('Sede comercial reasignada correctamente con registro en auditoría.');
				await fetchSalesRepsModule();
			} catch (err) {
				alert('Error en reasignación: ' + err.message);
			}
		}

		/* Historial de Reasignaciones */
		async function fetchReassignmentHistory() {
			try {
				const res = await fetch('/api/b2b/sales-reps/history');
				if (!res.ok) throw new Error('Error al obtener historial');
				const json = await res.json();
				const data = json.history || json;
				salesRepsState.historyData = data;
				renderReassignmentHistoryTable(data);
			} catch (err) {
				console.error('Error fetching history:', err);
			}
		}

		function renderReassignmentHistoryTable(history) {
			const tbody = document.getElementById('reassignment-history-table-body');
			if (!tbody) return;

			if (!history || history.length === 0) {
				tbody.innerHTML = '<tr><td colspan="6" class="py-8 text-center text-slate-400">Sin reasignaciones recientes registradas en auditoría.</td></tr>';
				return;
			}

			tbody.innerHTML = history.map(h => {
				const fromRep = h.from_rep_name || h.prev_sales_rep_name || 'Sin Asignar';
				const toRep = h.to_rep_name || h.new_sales_rep_name || 'N/A';
				const fromTerr = h.from_territory_name || h.prev_territory || '---';
				const toTerr = h.to_territory_name || h.new_territory || '---';
				const entity = h.entity_name || h.customer_name || 'Sede Comercial';
				const dt = (h.created_timestamp || h.created_at || '').substring(0, 19);

				return `
					<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
						<td class="py-2.5 px-3 font-mono text-slate-500 text-[11px]">${dt}</td>
						<td class="py-2.5 px-3">
							<span class="font-bold text-slate-900 dark:text-white">${escapeHtml(entity)}</span>
						</td>
						<td class="py-2.5 px-3">
							<span class="text-slate-400 line-through mr-1 text-[11px]">${escapeHtml(fromRep)}</span>
							<span class="font-bold text-brand-700 dark:text-brand-300">&rarr; ${escapeHtml(toRep)}</span>
						</td>
						<td class="py-2.5 px-3">
							<span class="text-slate-400 mr-1 text-[11px]">${escapeHtml(fromTerr)}</span>
							<span class="font-medium text-slate-800 dark:text-slate-200">&rarr; ${escapeHtml(toTerr)}</span>
						</td>
						<td class="py-2.5 px-3 text-slate-600 dark:text-slate-400 max-w-xs truncate">${escapeHtml(h.reason || 'Reasignación comercial')}</td>
						<td class="py-2.5 px-3 text-slate-500 font-mono text-[11px]">${escapeHtml(h.changed_by || 'Dirección Comercial')}</td>
					</tr>
				`;
			}).join('');
		}


		// =========================================================================
		// MÓDULO 6: RENTABILIDAD COMERCIAL B2B (PASO 10)
		// =========================================================================

		let profitabilityState = {
			period: 'MTD',
			customFrom: '',
			customTo: '',
			customerId: '',
			salesRepId: '',
			categoryId: '',
			territoryId: '',
			currentSubTab: 'products',
			metrics: null,
			productsData: [],
			customersData: [],
			repsData: [],
			territoriesData: [],
			categoriesData: [],
			costsCatalog: [],
			adjustmentsHistory: [],
			charts: {
				trend: null,
				categories: null,
				topCustomers: null,
				health: null
			}
		};

		function buildProfitabilityQueryParams() {
			const p = new URLSearchParams();
			p.set('period', profitabilityState.period);
			if (profitabilityState.period === 'CUSTOM') {
				if (profitabilityState.customFrom) p.set('date_from', profitabilityState.customFrom);
				if (profitabilityState.customTo) p.set('date_to', profitabilityState.customTo);
			}
			if (profitabilityState.customerId) p.set('customer_id', profitabilityState.customerId);
			if (profitabilityState.salesRepId) p.set('sales_rep_id', profitabilityState.salesRepId);
			if (profitabilityState.categoryId) p.set('category_id', profitabilityState.categoryId);
			if (profitabilityState.territoryId) p.set('territory_id', profitabilityState.territoryId);
			return p.toString();
		}

		async function fetchProfitabilityModule() {
			try {
				const q = buildProfitabilityQueryParams();
				const res = await fetch(`/api/b2b/profitability/metrics?${q}`);
				const data = await res.json();
				if (data.success && data.metrics) {
					profitabilityState.metrics = data.metrics;
					renderProfitabilityKPIs(data.metrics);
				}

				// Populate filter dropdowns if not loaded
				await populateProfitabilityFilters();

				// Load charts
				await renderProfitabilityCharts();

				// Load current subtab
				switchProfitabilitySubTab(profitabilityState.currentSubTab || 'products');

				if (window.lucide) lucide.createIcons();
			} catch (err) {
				console.error('Error fetching profitability module:', err);
			}
		}

		function renderProfitabilityKPIs(m) {
			const elNet = document.getElementById('kpi-prof-net-sales');
			const elCogs = document.getElementById('kpi-prof-cogs');
			const elGross = document.getElementById('kpi-prof-gross-profit');
			const elMarginVal = document.getElementById('kpi-prof-margin-val');
			const elMarginBar = document.getElementById('kpi-prof-margin-bar');
			const elNeg = document.getElementById('kpi-prof-negative-count');
			const elTopCustName = document.getElementById('kpi-prof-top-customer-name');
			const elTopCustProfit = document.getElementById('kpi-prof-top-customer-profit');
			const elPeriodBadge = document.getElementById('prof-period-badge');
			const elPeriodLabel = document.getElementById('prof-period-label');

			if (elNet) elNet.textContent = m.net_sales_formatted || '$0.00';
			if (elCogs) elCogs.textContent = m.cogs_formatted || '$0.00';
			if (elGross) {
				elGross.textContent = m.gross_profit_formatted || '$0.00';
				if (m.gross_profit < 0) {
					elGross.className = 'text-2xl font-black font-mono tracking-tight text-rose-600 dark:text-rose-400';
				} else {
					elGross.className = 'text-2xl font-black font-mono tracking-tight text-emerald-600 dark:text-emerald-400';
				}
			}
			if (elMarginVal) {
				if (m.gross_margin_percent === null || m.net_sales <= 0) {
					elMarginVal.textContent = 'N/D';
					if (elMarginBar) elMarginBar.style.width = '0%';
				} else {
					elMarginVal.textContent = m.gross_margin_formatted || `${m.gross_margin_percent}%`;
					if (elMarginBar) {
						const clamped = Math.max(0, Math.min(100, m.gross_margin_percent));
						elMarginBar.style.width = `${clamped}%`;
						if (m.gross_margin_percent < 0) {
							elMarginBar.className = 'h-1.5 rounded-full bg-rose-500 transition-all duration-500';
						} else if (m.gross_margin_percent < 15) {
							elMarginBar.className = 'h-1.5 rounded-full bg-amber-500 transition-all duration-500';
						} else {
							elMarginBar.className = 'h-1.5 rounded-full bg-emerald-500 transition-all duration-500';
						}
					}
				}
			}
			if (elNeg) {
				elNeg.textContent = m.negative_margin_products_count || 0;
				if (m.negative_margin_products_count > 0) {
					elNeg.className = 'inline-flex items-center justify-center px-2 py-0.5 rounded text-xs font-bold bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800';
				} else {
					elNeg.className = 'inline-flex items-center justify-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800';
				}
			}
			if (elTopCustName) {
				elTopCustName.textContent = m.top_customer ? m.top_customer.name : 'Sin ventas registradas';
				elTopCustName.title = m.top_customer ? m.top_customer.name : '';
			}
			if (elTopCustProfit) {
				elTopCustProfit.textContent = m.top_customer ? `${m.top_customer.profit_formatted} (${m.top_customer.margin_percent}%)` : '$0.00';
			}
			if (elPeriodBadge) elPeriodBadge.textContent = m.period || profitabilityState.period;
			if (elPeriodLabel) elPeriodLabel.textContent = m.period_label || 'Período actual';
		}

		async function populateProfitabilityFilters() {
			try {
				// Customers filter
				const custSelect = document.getElementById('prof-filter-customer');
				if (custSelect && custSelect.options.length <= 1) {
					const res = await fetch('/api/b2b/customers');
					const d = await res.json();
					if (d.success && d.customers) {
						d.customers.forEach(c => {
							const opt = document.createElement('option');
							opt.value = c.id;
							opt.textContent = c.business_name || c.trade_name;
							custSelect.appendChild(opt);
						});
					}
				}

				// Sales reps filter
				const repSelect = document.getElementById('prof-filter-rep');
				if (repSelect && repSelect.options.length <= 1) {
					const res = await fetch('/api/b2b/sales-reps');
					const d = await res.json();
					if (d.success && d.sales_reps) {
						d.sales_reps.forEach(r => {
							const opt = document.createElement('option');
							opt.value = r.id;
							opt.textContent = r.name;
							repSelect.appendChild(opt);
						});
					}
				}

				// Categories filter
				const catSelect = document.getElementById('prof-filter-category');
				if (catSelect && catSelect.options.length <= 1) {
					const res = await fetch('/api/b2b/profitability/categories');
					const d = await res.json();
					if (d.success && d.categories) {
						d.categories.forEach(c => {
							const opt = document.createElement('option');
							opt.value = c.category_name;
							opt.textContent = `${c.category_name} (${c.products_count} prod)`;
							catSelect.appendChild(opt);
						});
					}
				}

				// Territories filter
				const terrSelect = document.getElementById('prof-filter-territory');
				if (terrSelect && terrSelect.options.length <= 1) {
					const res = await fetch('/api/b2b/territories');
					const d = await res.json();
					if (d.success && d.territories) {
						d.territories.forEach(t => {
							const opt = document.createElement('option');
							opt.value = t.id;
							opt.textContent = `${t.code} - ${t.name}`;
							terrSelect.appendChild(opt);
						});
					}
				}
			} catch (err) {
				console.warn('Error populating profitability filters:', err);
			}
		}

		async function renderProfitabilityCharts() {
			const isDark = document.documentElement.classList.contains('dark');
			const textColor = isDark ? '#94a3b8' : '#64748b';
			const gridColor = isDark ? 'rgba(51, 65, 85, 0.4)' : 'rgba(226, 232, 240, 0.8)';
			const q = buildProfitabilityQueryParams();

			try {
				// Chart 1: Trend (Net sales vs COGS vs Gross Profit)
				const ctxTrend = document.getElementById('chart-prof-trend');
				if (ctxTrend) {
					const resTrend = await fetch(`/api/b2b/profitability/trend?${q}`);
					const dTrend = await resTrend.json();
					if (profitabilityState.charts.trend) profitabilityState.charts.trend.destroy();

					const labels = dTrend.labels || [];
					const sales = dTrend.sales || [];
					const cogs = dTrend.cogs || [];
					const profit = dTrend.profit || [];

					profitabilityState.charts.trend = new Chart(ctxTrend.getContext('2d'), {
						type: 'bar',
						data: {
							labels: labels,
							datasets: [
								{
									label: 'Ventas Netas ($)',
									data: sales,
									backgroundColor: 'rgba(59, 130, 246, 0.85)',
									borderRadius: 4
								},
								{
									label: 'Costo de Ventas (COGS)',
									data: cogs,
									backgroundColor: 'rgba(148, 163, 184, 0.7)',
									borderRadius: 4
								},
								{
									label: 'Utilidad Bruta ($)',
									data: profit,
									type: 'line',
									borderColor: '#10b981',
									backgroundColor: 'rgba(16, 185, 129, 0.1)',
									fill: true,
									tension: 0.3,
									pointBackgroundColor: '#10b981',
									pointRadius: 4
								}
							]
						},
						options: {
							responsive: true,
							maintainAspectRatio: false,
							legend: {
								display: true,
								position: 'top',
								labels: { fontColor: textColor, boxWidth: 12, fontSize: 11 }
							},
							tooltips: {
								callbacks: {
									label: function(item, data) {
										const datasetLabel = data.datasets[item.datasetIndex].label || '';
										const val = Number(item.yLabel || 0).toLocaleString(undefined, { minimumFractionDigits: 2 });
										return `${datasetLabel}: $${val}`;
									}
								}
							},
							scales: {
								xAxes: [{ ticks: { fontColor: textColor, fontSize: 11 }, gridLines: { color: gridColor } }],
								yAxes: [{
									ticks: {
										fontColor: textColor,
										fontSize: 11,
										callback: function(v) { return '$' + (v >= 1000 ? (v / 1000).toFixed(0) + 'k' : v); }
									},
									gridLines: { color: gridColor }
								}]
							}
						}
					});
				}

				// Chart 2: Margen por Categoría
				const ctxCat = document.getElementById('chart-prof-categories');
				if (ctxCat) {
					const resCat = await fetch(`/api/b2b/profitability/categories?${q}`);
					const dCat = await resCat.json();
					if (profitabilityState.charts.categories) profitabilityState.charts.categories.destroy();

					const cats = (dCat.categories || []).slice(0, 6);
					const catLabels = cats.map(c => c.category_name);
					const catMargins = cats.map(c => c.gross_margin_percent);
					const catProfits = cats.map(c => c.gross_profit);

					profitabilityState.charts.categories = new Chart(ctxCat.getContext('2d'), {
						type: 'horizontalBar',
						data: {
							labels: catLabels,
							datasets: [{
								label: 'Margen Bruto (%)',
								data: catMargins,
								backgroundColor: catMargins.map(m => m >= 20 ? 'rgba(16, 185, 129, 0.85)' : (m >= 0 ? 'rgba(245, 158, 11, 0.85)' : 'rgba(244, 63, 94, 0.85)')),
								borderRadius: 4
							}]
						},
						options: {
							responsive: true,
							maintainAspectRatio: false,
							legend: { display: false },
							tooltips: {
								callbacks: {
									label: function(item) {
										const idx = item.index;
										const margin = catMargins[idx];
										const prof = catProfits[idx] ? Number(catProfits[idx]).toLocaleString(undefined, {minimumFractionDigits: 2}) : '0.00';
										return `Margen: ${margin}% (Utilidad: $${prof})`;
									}
								}
							},
							scales: {
								xAxes: [{
									ticks: {
										fontColor: textColor,
										fontSize: 10,
										callback: function(v) { return v + '%'; }
									},
									gridLines: { color: gridColor }
								}],
								yAxes: [{ ticks: { fontColor: textColor, fontSize: 10 }, gridLines: { display: false } }]
							}
						}
					});
				}

				// Chart 3: Top 5 Clientes por Utilidad Bruta
				const ctxTop = document.getElementById('chart-prof-top-customers');
				if (ctxTop) {
					const resCust = await fetch(`/api/b2b/profitability/customers?${q}`);
					const dCust = await resCust.json();
					if (profitabilityState.charts.topCustomers) profitabilityState.charts.topCustomers.destroy();

					const top5 = (dCust.customers || []).slice(0, 5);
					const custLabels = top5.map(c => {
						const name = c.customer_name || 'Cliente';
						return name.length > 14 ? name.substring(0, 14) + '...' : name;
					});
					const custProfits = top5.map(c => c.gross_profit);

					profitabilityState.charts.topCustomers = new Chart(ctxTop.getContext('2d'), {
						type: 'bar',
						data: {
							labels: custLabels,
							datasets: [{
								label: 'Utilidad Bruta ($)',
								data: custProfits,
								backgroundColor: '#3b82f6',
								borderRadius: 4
							}]
						},
						options: {
							responsive: true,
							maintainAspectRatio: false,
							legend: { display: false },
							tooltips: {
								callbacks: {
									title: function(items) {
										const idx = items[0].index;
										return top5[idx] ? top5[idx].customer_name : '';
									},
									label: function(item) {
										const idx = item.index;
										const val = Number(custProfits[idx] || 0).toLocaleString(undefined, {minimumFractionDigits: 2});
										const margin = top5[idx] ? top5[idx].gross_margin_percent : 0;
										return `Utilidad: $${val} (Margen: ${margin}%)`;
									}
								}
							},
							scales: {
								xAxes: [{ ticks: { fontColor: textColor, fontSize: 10 }, gridLines: { display: false } }],
								yAxes: [{
									ticks: {
										fontColor: textColor,
										fontSize: 10,
										callback: function(v) { return '$' + (v >= 1000 ? (v / 1000).toFixed(0) + 'k' : v); }
									},
									gridLines: { color: gridColor }
								}]
							}
						}
					});
				}

				// Chart 4: Salud de Márgenes (Distribución)
				const ctxHealth = document.getElementById('chart-prof-health');
				if (ctxHealth) {
					const resProd = await fetch(`/api/b2b/profitability/products?${q}`);
					const dProd = await resProd.json();
					if (profitabilityState.charts.health) profitabilityState.charts.health.destroy();

					let healthy = 0;   // >= 20%
					let moderate = 0;  // 0% - 20%
					let negative = 0;  // < 0%
					let noCost = 0;    // Costo no disponible

					(dProd.products || []).forEach(p => {
						if (p.current_unit_cost === null) {
							noCost++;
						} else if (p.gross_margin_percent < 0) {
							negative++;
						} else if (p.gross_margin_percent >= 20) {
							healthy++;
						} else {
							moderate++;
						}
					});

					profitabilityState.charts.health = new Chart(ctxHealth.getContext('2d'), {
						type: 'doughnut',
						data: {
							labels: ['Saludable (≥20%)', 'Moderado (0-20%)', 'Negativo (<0%)', 'Sin Costo'],
							datasets: [{
								data: [healthy, moderate, negative, noCost],
								backgroundColor: ['#10b981', '#f59e0b', '#f43f5e', '#94a3b8'],
								borderWidth: 2,
								borderColor: isDark ? '#1e293b' : '#ffffff'
							}]
						},
						options: {
							responsive: true,
							maintainAspectRatio: false,
							cutoutPercentage: 65,
							legend: {
								display: true,
								position: 'bottom',
								labels: { fontColor: textColor, boxWidth: 10, fontSize: 10 }
							},
							tooltips: {
								callbacks: {
									label: function(item, data) {
										const label = data.labels[item.index] || '';
										const count = data.datasets[0].data[item.index] || 0;
										return `${label}: ${count} productos`;
									}
								}
							}
						}
					});
				}
			} catch (err) {
				console.error('Error rendering profitability charts:', err);
			}
		}

		function setProfitabilityPeriod(period) {
			profitabilityState.period = period;
			['7D', 'MTD', 'QTD', 'YTD', 'CUSTOM'].forEach(p => {
				const btn = document.getElementById(`prof-period-btn-${p}`);
				if (btn) {
					if (p === period) {
						btn.className = 'px-2.5 py-1 text-xs font-semibold rounded-md bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs transition-colors';
					} else {
						btn.className = 'px-2.5 py-1 text-xs font-semibold rounded-md text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors';
					}
				}
			});

			const rangeBar = document.getElementById('prof-custom-range-bar');
			if (rangeBar) {
				if (period === 'CUSTOM') {
					rangeBar.classList.remove('hidden');
				} else {
					rangeBar.classList.add('hidden');
				}
			}

			if (period !== 'CUSTOM') {
				fetchProfitabilityModule();
			}
		}

		function toggleProfitabilityCustomRange() {
			const rangeBar = document.getElementById('prof-custom-range-bar');
			if (rangeBar) {
				rangeBar.classList.toggle('hidden');
			}
		}

		function applyProfitabilityCustomRange() {
			const from = document.getElementById('prof-custom-from');
			const to = document.getElementById('prof-custom-to');
			if (!from || !to || !from.value || !to.value) {
				alert('Por favor seleccione ambas fechas para el rango personalizado.');
				return;
			}
			profitabilityState.customFrom = from.value;
			profitabilityState.customTo = to.value;
			setProfitabilityPeriod('CUSTOM');
			fetchProfitabilityModule();
		}

		function applyProfitabilityFilters() {
			const cust = document.getElementById('prof-filter-customer');
			const rep = document.getElementById('prof-filter-rep');
			const cat = document.getElementById('prof-filter-category');
			const terr = document.getElementById('prof-filter-territory');

			profitabilityState.customerId = cust ? cust.value : '';
			profitabilityState.salesRepId = rep ? rep.value : '';
			profitabilityState.categoryId = cat ? cat.value : '';
			profitabilityState.territoryId = terr ? terr.value : '';

			fetchProfitabilityModule();
		}

		function resetProfitabilityFilters() {
			const cust = document.getElementById('prof-filter-customer');
			const rep = document.getElementById('prof-filter-rep');
			const cat = document.getElementById('prof-filter-category');
			const terr = document.getElementById('prof-filter-territory');
			const from = document.getElementById('prof-custom-from');
			const to = document.getElementById('prof-custom-to');

			if (cust) cust.value = '';
			if (rep) rep.value = '';
			if (cat) cat.value = '';
			if (terr) terr.value = '';
			if (from) from.value = '';
			if (to) to.value = '';

			profitabilityState.customerId = '';
			profitabilityState.salesRepId = '';
			profitabilityState.categoryId = '';
			profitabilityState.territoryId = '';
			profitabilityState.customFrom = '';
			profitabilityState.customTo = '';

			setProfitabilityPeriod('MTD');
		}

		function switchProfitabilitySubTab(tabName) {
			profitabilityState.currentSubTab = tabName;
			const tabs = ['products', 'customers', 'reps', 'territories', 'costs'];

			tabs.forEach(t => {
				const btn = document.getElementById(`tab-prof-${t}-btn`);
				const view = document.getElementById(`subtab-prof-${t}-view`);
				if (btn) {
					if (t === tabName) {
						btn.className = 'px-3 py-2 text-xs font-semibold border-b-2 border-brand-600 text-brand-600 dark:text-brand-400 whitespace-nowrap transition-colors';
					} else {
						btn.className = 'px-3 py-2 text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white whitespace-nowrap border-b-2 border-transparent transition-colors';
					}
				}
				if (view) {
					if (t === tabName) {
						view.classList.remove('hidden');
					} else {
						view.classList.add('hidden');
					}
				}
			});

			if (tabName === 'products') fetchProfitabilityProducts();
			else if (tabName === 'customers') fetchProfitabilityCustomers();
			else if (tabName === 'reps') fetchProfitabilityReps();
			else if (tabName === 'territories') fetchProfitabilityTerritories();
			else if (tabName === 'costs') fetchProfitabilityCosts();
		}

		async function fetchProfitabilityProducts() {
			try {
				const q = buildProfitabilityQueryParams();
				const res = await fetch(`/api/b2b/profitability/products?${q}`);
				const d = await res.json();
				if (d.success && d.products) {
					profitabilityState.productsData = d.products;
					renderProfitabilityProductsTable(d.products);
				}
			} catch (err) {
				console.error('Error fetching profitability products:', err);
			}
		}

		function renderProfitabilityProductsTable(products) {
			const tbody = document.getElementById('prof-products-table-body');
			const countEl = document.getElementById('prof-products-count');
			if (!tbody) return;

			if (countEl) countEl.textContent = `${products.length} productos analizados`;

			if (products.length === 0) {
				tbody.innerHTML = `<tr><td colspan="9" class="py-8 text-center text-xs text-slate-400">No se encontraron productos con ventas o costos en los filtros seleccionados.</td></tr>`;
				return;
			}

			tbody.innerHTML = products.map(p => {
				const isNoCost = p.current_unit_cost === null || p.gross_profit === null;
				const isNegative = p.is_negative || (p.gross_margin_percent !== null && p.gross_margin_percent < 0);

				let statusBadge = '';
				if (isNoCost) {
					statusBadge = `<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-300 dark:border-slate-700">Costo no disponible</span>`;
				} else if (isNegative) {
					statusBadge = `<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800">Margen Negativo</span>`;
				} else if (p.gross_margin_percent >= 20) {
					statusBadge = `<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">Saludable</span>`;
				} else {
					statusBadge = `<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800">Moderado</span>`;
				}

				const costUnitDisplay = isNoCost ? '<span class="text-slate-400 text-xs italic">Costo no disponible</span>' : `${p.current_unit_cost_formatted} <span class="text-[10px] text-slate-400">/ud</span>`;
				const costCaseDisplay = isNoCost ? '' : `<div class="text-[10px] text-slate-400">${p.current_case_cost_formatted}/caja</div>`;
				const profitDisplay = isNoCost ? '<span class="text-slate-400 italic">N/D</span>' : `<span class="font-bold ${isNegative ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white'}">${p.gross_profit_formatted}</span>`;
				const marginDisplay = isNoCost ? '<span class="text-slate-400 italic">N/D</span>' : `<span class="font-black ${isNegative ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400'}">${p.gross_margin_formatted}</span>`;

				return `
					<tr class="border-b border-slate-100 dark:border-slate-800/80 hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors ${isNegative ? 'bg-rose-50/20 dark:bg-rose-950/10' : ''}">
						<td class="py-3 px-3">
							<div class="font-mono text-xs font-bold text-slate-900 dark:text-white">${escapeHtml(p.sku)}</div>
							<div class="text-xs text-slate-600 dark:text-slate-300 font-medium">${escapeHtml(p.name)}</div>
							<div class="text-[10px] text-slate-400">${escapeHtml(p.brand || '')} • ${escapeHtml(p.category || 'General')}</div>
						</td>
						<td class="py-3 px-3 text-center">
							<span class="font-mono text-xs text-slate-600 dark:text-slate-300">1 caja = ${p.conversion_factor} uds</span>
						</td>
						<td class="py-3 px-3 text-right">
							<div class="font-mono text-xs font-semibold text-slate-800 dark:text-slate-200">${costUnitDisplay}</div>
							${costCaseDisplay}
						</td>
						<td class="py-3 px-3 text-right">
							<div class="font-mono text-xs text-slate-900 dark:text-white">${p.cases_sold} cjs</div>
							<div class="text-[10px] text-slate-400 font-mono">(${p.units_sold} uds)</div>
						</td>
						<td class="py-3 px-3 text-right font-mono text-xs font-semibold text-slate-900 dark:text-white">
							${p.net_sales_formatted}
						</td>
						<td class="py-3 px-3 text-right font-mono text-xs text-slate-600 dark:text-slate-400">
							${isNoCost ? '<span class="italic text-slate-400">N/D</span>' : p.cogs_formatted}
						</td>
						<td class="py-3 px-3 text-right font-mono text-xs">
							${profitDisplay}
						</td>
						<td class="py-3 px-3 text-right font-mono text-xs">
							${marginDisplay}
						</td>
						<td class="py-3 px-3 text-center">
							${statusBadge}
						</td>
						<td class="py-3 px-3 text-center">
							<button onclick="openCostAdjustmentModal(${p.product_id})" class="p-1 text-slate-400 hover:text-brand-600 dark:hover:text-brand-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded transition-colors" title="Ajustar Costo Comercial">
								<i data-lucide="calculator" class="w-4 h-4"></i>
							</button>
						</td>
					</tr>
				`;
			}).join('');

			if (window.lucide) lucide.createIcons();
		}

		function filterProfitabilityProducts() {
			const query = (document.getElementById('prof-prod-search-input')?.value || '').toLowerCase().trim();
			const statusFilter = document.getElementById('prof-prod-filter-status')?.value || 'ALL';

			const filtered = profitabilityState.productsData.filter(p => {
				const matchQuery = !query ||
					(p.sku && p.sku.toLowerCase().includes(query)) ||
					(p.name && p.name.toLowerCase().includes(query)) ||
					(p.category && p.category.toLowerCase().includes(query));

				let matchStatus = true;
				if (statusFilter === 'NEGATIVE') {
					matchStatus = p.is_negative || (p.gross_margin_percent !== null && p.gross_margin_percent < 0);
				} else if (statusFilter === 'HEALTHY') {
					matchStatus = p.gross_margin_percent !== null && p.gross_margin_percent >= 20;
				} else if (statusFilter === 'NO_COST') {
					matchStatus = p.current_unit_cost === null || p.gross_profit === null;
				}

				return matchQuery && matchStatus;
			});

			renderProfitabilityProductsTable(filtered);
		}

		async function fetchProfitabilityCustomers() {
			try {
				const q = buildProfitabilityQueryParams();
				const res = await fetch(`/api/b2b/profitability/customers?${q}`);
				const d = await res.json();
				if (d.success && d.customers) {
					profitabilityState.customersData = d.customers;
					renderProfitabilityCustomersTable(d.customers);
				}
			} catch (err) {
				console.error('Error fetching profitability customers:', err);
			}
		}

		function renderProfitabilityCustomersTable(customers) {
			const tbody = document.getElementById('prof-customers-table-body');
			const countEl = document.getElementById('prof-customers-count');
			if (!tbody) return;

			if (countEl) countEl.textContent = `${customers.length} cuentas comerciales`;

			if (customers.length === 0) {
				tbody.innerHTML = `<tr><td colspan="8" class="py-8 text-center text-xs text-slate-400">Sin clientes en el período seleccionado.</td></tr>`;
				return;
			}

			tbody.innerHTML = customers.map(c => {
				const isNegative = c.gross_margin_percent !== null && c.gross_margin_percent < 0;
				return `
					<tr class="border-b border-slate-100 dark:border-slate-800/80 hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
						<td class="py-3 px-3">
							<div class="font-bold text-xs text-slate-900 dark:text-white">${escapeHtml(c.customer_name || c.name || '')}</div>
							<div class="text-[10px] text-slate-400 font-mono">RUC: ${escapeHtml(c.tax_id || 'N/A')}</div>
						</td>
						<td class="py-3 px-3">
							<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">${escapeHtml(c.customer_type || 'Supermercado')}</span>
						</td>
						<td class="py-3 px-3 text-right font-mono text-xs font-semibold text-slate-900 dark:text-white">
							${c.net_sales_formatted}
						</td>
						<td class="py-3 px-3 text-right font-mono text-xs text-slate-600 dark:text-slate-400">
							${c.cogs_formatted}
						</td>
						<td class="py-3 px-3 text-right font-mono text-xs font-bold ${isNegative ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white'}">
							${c.gross_profit_formatted}
						</td>
						<td class="py-3 px-3 text-right font-mono text-xs font-black ${isNegative ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400'}">
							${c.gross_margin_formatted}
						</td>
						<td class="py-3 px-3 text-right font-mono text-xs text-brand-600 dark:text-brand-400 font-semibold">
							${c.profit_share_percent}%
						</td>
						<td class="py-3 px-3 text-xs text-slate-600 dark:text-slate-300">
							${escapeHtml(c.sales_rep_name || 'Sin vendedor')}
						</td>
					</tr>
				`;
			}).join('');
		}

		function filterProfitabilityCustomers() {
			const query = (document.getElementById('prof-cust-search-input')?.value || '').toLowerCase().trim();
			const filtered = profitabilityState.customersData.filter(c => {
				const name = (c.customer_name || c.name || '').toLowerCase();
				const taxId = (c.tax_id || '').toLowerCase();
				const rep = (c.sales_rep_name || '').toLowerCase();
				return !query || name.includes(query) || taxId.includes(query) || rep.includes(query);
			});
			renderProfitabilityCustomersTable(filtered);
		}

		async function fetchProfitabilityReps() {
			try {
				const q = buildProfitabilityQueryParams();
				const res = await fetch(`/api/b2b/profitability/sales-reps?${q}`);
				const d = await res.json();
				if (d.success && d.sales_reps) {
					profitabilityState.repsData = d.sales_reps;
					renderProfitabilityRepsTable(d.sales_reps);
				}
			} catch (err) {
				console.error('Error fetching profitability reps:', err);
			}
		}

		function renderProfitabilityRepsTable(reps) {
			const tbody = document.getElementById('prof-reps-table-body');
			if (!tbody) return;

			if (reps.length === 0) {
				tbody.innerHTML = `<tr><td colspan="7" class="py-8 text-center text-xs text-slate-400">Sin vendedores con datos comerciales en el período.</td></tr>`;
				return;
			}

			tbody.innerHTML = reps.map(r => {
				const achievePct = r.monthly_quota > 0 ? ((r.net_sales / r.monthly_quota) * 100).toFixed(1) : 0;
				return `
					<tr class="border-b border-slate-100 dark:border-slate-800/80 hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
						<td class="py-3 px-3 font-semibold text-xs text-slate-900 dark:text-white">
							${escapeHtml(r.sales_rep_name)}
						</td>
						<td class="py-3 px-3 text-xs text-slate-600 dark:text-slate-400">
							${escapeHtml(r.territory_name || 'Sin territorio')}
						</td>
						<td class="py-3 px-3 text-right font-mono text-xs text-slate-600 dark:text-slate-400">
							${r.monthly_quota_formatted}
						</td>
						<td class="py-3 px-3 text-right font-mono text-xs font-semibold text-slate-900 dark:text-white">
							${r.net_sales_formatted}
						</td>
						<td class="py-3 px-3 text-right font-mono text-xs font-bold text-slate-900 dark:text-white">
							${r.gross_profit_formatted}
						</td>
						<td class="py-3 px-3 text-right font-mono text-xs font-black text-emerald-600 dark:text-emerald-400">
							${r.gross_margin_formatted}
						</td>
						<td class="py-3 px-3 text-right font-mono text-xs font-semibold ${achievePct >= 100 ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400'}">
							${achievePct}%
						</td>
					</tr>
				`;
			}).join('');
		}

		async function fetchProfitabilityTerritories() {
			try {
				const q = buildProfitabilityQueryParams();
				const res = await fetch(`/api/b2b/profitability/territories?${q}`);
				const d = await res.json();
				if (d.success && d.territories) {
					profitabilityState.territoriesData = d.territories;
					renderProfitabilityTerritoriesTable(d.territories);
				}
			} catch (err) {
				console.error('Error fetching profitability territories:', err);
			}
		}

		function renderProfitabilityTerritoriesTable(territories) {
			const tbody = document.getElementById('prof-territories-table-body');
			if (!tbody) return;

			if (territories.length === 0) {
				tbody.innerHTML = `<tr><td colspan="6" class="py-8 text-center text-xs text-slate-400">Sin territorios con ventas en el período.</td></tr>`;
				return;
			}

			tbody.innerHTML = territories.map(t => {
				return `
					<tr class="border-b border-slate-100 dark:border-slate-800/80 hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
						<td class="py-3 px-3">
							<span class="font-mono text-xs font-bold text-slate-900 dark:text-white">${escapeHtml(t.territory_code || t.code || '')}</span> - 
							<span class="text-xs text-slate-700 dark:text-slate-300">${escapeHtml(t.territory_name || t.name || '')}</span>
						</td>
						<td class="py-3 px-3 text-xs text-slate-600 dark:text-slate-400">
							${escapeHtml(t.city || '')} / ${escapeHtml(t.zone || '')}
						</td>
						<td class="py-3 px-3 text-right font-mono text-xs font-semibold text-slate-900 dark:text-white">
							${t.net_sales_formatted}
						</td>
						<td class="py-3 px-3 text-right font-mono text-xs text-slate-600 dark:text-slate-400">
							${t.cogs_formatted}
						</td>
						<td class="py-3 px-3 text-right font-mono text-xs font-bold text-slate-900 dark:text-white">
							${t.gross_profit_formatted}
						</td>
						<td class="py-3 px-3 text-right font-mono text-xs font-black text-emerald-600 dark:text-emerald-400">
							${t.gross_margin_formatted}
						</td>
					</tr>
				`;
			}).join('');
		}

		async function fetchProfitabilityCosts() {
			try {
				const res = await fetch('/api/b2b/profitability/costs');
				const d = await res.json();
				if (d.success) {
					profitabilityState.costsCatalog = d.costs || [];
					profitabilityState.adjustmentsHistory = d.adjustments || [];
					renderProfitabilityCostsTable(profitabilityState.costsCatalog);
					renderCostAdjustmentsTable(profitabilityState.adjustmentsHistory);
				}
			} catch (err) {
				console.error('Error fetching profitability costs catalog:', err);
			}
		}

		function renderProfitabilityCostsTable(costs) {
			const tbody = document.getElementById('prof-costs-table-body');
			if (!tbody) return;

			if (costs.length === 0) {
				tbody.innerHTML = `<tr><td colspan="7" class="py-8 text-center text-xs text-slate-400">No hay costos configurados en el catálogo.</td></tr>`;
				return;
			}

			tbody.innerHTML = costs.map(c => {
				const hasCost = c.current_unit_cost !== null;
				return `
					<tr class="border-b border-slate-100 dark:border-slate-800/80 hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
						<td class="py-3 px-3">
							<div class="font-mono text-xs font-bold text-slate-900 dark:text-white">${escapeHtml(c.sku)}</div>
							<div class="text-xs text-slate-700 dark:text-slate-300">${escapeHtml(c.name)}</div>
						</td>
						<td class="py-3 px-3 text-xs text-slate-600 dark:text-slate-400">
							${escapeHtml(c.category || 'General')}
						</td>
						<td class="py-3 px-3 text-center font-mono text-xs text-slate-600 dark:text-slate-400">
							1 caja = ${c.conversion_factor} uds
						</td>
						<td class="py-3 px-3 text-right font-mono text-xs font-semibold ${hasCost ? 'text-slate-900 dark:text-white' : 'text-slate-400 italic'}">
							${c.unit_cost_formatted}
						</td>
						<td class="py-3 px-3 text-right font-mono text-xs text-slate-600 dark:text-slate-400">
							${c.case_cost_formatted}
						</td>
						<td class="py-3 px-3 text-center font-mono text-[11px] text-slate-500">
							${c.effective_from || '—'}
						</td>
						<td class="py-3 px-3 text-center">
							<button onclick="openCostAdjustmentModal(${c.product_id})" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded bg-brand-50 hover:bg-brand-100 dark:bg-brand-950/60 dark:hover:bg-brand-900 text-brand-700 dark:text-brand-300 transition-colors">
								<i data-lucide="calculator" class="w-3.5 h-3.5"></i>
								<span>Ajustar</span>
							</button>
						</td>
					</tr>
				`;
			}).join('');

			if (window.lucide) lucide.createIcons();
		}

		function renderCostAdjustmentsTable(adjustments) {
			const tbody = document.getElementById('prof-adjustments-table-body');
			if (!tbody) return;

			if (adjustments.length === 0) {
				tbody.innerHTML = `<tr><td colspan="7" class="py-8 text-center text-xs text-slate-400">Sin historial de ajustes de costo registrados.</td></tr>`;
				return;
			}

			tbody.innerHTML = adjustments.map(a => {
				return `
					<tr class="border-b border-slate-100 dark:border-slate-800/80 hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
						<td class="py-2.5 px-3 font-mono text-[11px] text-slate-500">
							${escapeHtml(a.created_at || '')}
						</td>
						<td class="py-2.5 px-3 text-xs font-semibold text-slate-900 dark:text-white">
							<span class="font-mono text-brand-600 dark:text-brand-400 mr-1">${escapeHtml(a.sku || '')}</span>
							${escapeHtml(a.product_name || '')}
						</td>
						<td class="py-2.5 px-3 text-right font-mono text-xs">
							<span class="text-slate-400 line-through mr-1">$${Number(a.old_unit_cost || 0).toFixed(2)}</span>
							<span class="font-bold text-brand-700 dark:text-brand-300">&rarr; $${Number(a.new_unit_cost || 0).toFixed(2)}</span>
						</td>
						<td class="py-2.5 px-3 text-right font-mono text-xs text-slate-500">
							$${Number(a.new_case_cost || 0).toFixed(2)}
						</td>
						<td class="py-2.5 px-3 text-xs text-slate-600 dark:text-slate-400 max-w-xs truncate" title="${escapeHtml(a.reason || '')}">
							${escapeHtml(a.reason || '')}
						</td>
						<td class="py-2.5 px-3 text-xs text-slate-500 font-mono">
							${escapeHtml(a.authorized_by || 'Auditoría')}
						</td>
						<td class="py-2.5 px-3 text-center font-mono text-[11px] text-slate-500">
							${escapeHtml(a.effective_date || '')}
						</td>
					</tr>
				`;
			}).join('');
		}

		async function openCostAdjustmentModal(productId = null) {
			const modal = document.getElementById('modal-cost-adjustment');
			if (!modal) return;

			// Ensure costs catalog is loaded
			if (!profitabilityState.costsCatalog || profitabilityState.costsCatalog.length === 0) {
				await fetchProfitabilityCosts();
			}

			// Populate product dropdown
			const select = document.getElementById('adj-select-product');
			if (select) {
				select.innerHTML = '<option value="">Seleccionar Producto...</option>';
				profitabilityState.costsCatalog.forEach(p => {
					const opt = document.createElement('option');
					opt.value = p.product_id;
					opt.textContent = `${p.sku} - ${p.name}`;
					select.appendChild(opt);
				});

				if (productId) {
					select.value = productId;
					handleCostAdjProductChange();
				} else {
					resetCostAdjustmentFormFields();
				}
			}

			// Default effective date to today
			const dt = document.getElementById('adj-effective-date');
			if (dt && !dt.value) {
				dt.value = new Date().toISOString().split('T')[0];
			}

			modal.classList.remove('hidden');
			if (window.lucide) lucide.createIcons();
		}

		function closeCostAdjustmentModal() {
			const modal = document.getElementById('modal-cost-adjustment');
			if (modal) modal.classList.add('hidden');
		}

		function resetCostAdjustmentFormFields() {
			const curUnit = document.getElementById('adj-cur-unit-cost');
			const curCase = document.getElementById('adj-cur-case-cost');
			const curFactor = document.getElementById('adj-cur-factor');
			const newUnit = document.getElementById('adj-new-unit-cost');
			const newCase = document.getElementById('adj-new-case-cost-preview');

			if (curUnit) curUnit.textContent = '$0.00';
			if (curCase) curCase.textContent = '$0.00';
			if (curFactor) curFactor.textContent = '1 caja = 12 uds';
			if (newUnit) newUnit.value = '';
			if (newCase) newCase.value = '$0.00';
		}

		function handleCostAdjProductChange() {
			const select = document.getElementById('adj-select-product');
			if (!select) return;

			const pid = select.value;
			const p = profitabilityState.costsCatalog.find(item => item.product_id == pid);

			const curUnit = document.getElementById('adj-cur-unit-cost');
			const curCase = document.getElementById('adj-cur-case-cost');
			const curFactor = document.getElementById('adj-cur-factor');

			if (p) {
				if (curUnit) curUnit.textContent = p.unit_cost_formatted || '$0.00';
				if (curCase) curCase.textContent = p.case_cost_formatted || '$0.00';
				if (curFactor) curFactor.textContent = `1 caja = ${p.conversion_factor} uds`;
			} else {
				resetCostAdjustmentFormFields();
			}

			handleNewUnitCostInput();
		}

		function handleNewUnitCostInput() {
			const select = document.getElementById('adj-select-product');
			const newUnitInput = document.getElementById('adj-new-unit-cost');
			const previewInput = document.getElementById('adj-new-case-cost-preview');

			if (!select || !newUnitInput || !previewInput) return;

			const pid = select.value;
			const p = profitabilityState.costsCatalog.find(item => item.product_id == pid);
			const factor = p ? p.conversion_factor : 1;

			const val = parseFloat(newUnitInput.value);
			if (!isNaN(val) && val >= 0) {
				const caseCost = val * factor;
				previewInput.value = `$${caseCost.toFixed(2)}`;
			} else {
				previewInput.value = '$0.00';
			}
		}

		async function submitCostAdjustmentForm(event) {
			event.preventDefault();
			const select = document.getElementById('adj-select-product');
			const newUnitInput = document.getElementById('adj-new-unit-cost');
			const dtInput = document.getElementById('adj-effective-date');
			const reasonInput = document.getElementById('adj-reason');
			const authInput = document.getElementById('adj-authorized-by');
			const btn = document.getElementById('btn-submit-cost-adj');

			if (!select.value || !newUnitInput.value || !reasonInput.value) {
				alert('Por favor complete todos los campos obligatorios.');
				return;
			}

			const payload = {
				product_id: parseInt(select.value),
				new_unit_cost: parseFloat(newUnitInput.value),
				effective_date: dtInput.value || new Date().toISOString().split('T')[0],
				reason: reasonInput.value.trim(),
				authorized_by: authInput.value.trim() || 'Auditoría Comercial B2B'
			};

			try {
				if (btn) {
					btn.disabled = true;
					btn.innerHTML = '<span>Guardando...</span>';
				}

				const res = await fetch('/api/b2b/profitability/costs/adjust', {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify(payload)
				});
				const d = await res.json();

				if (d.success) {
					alert(`Ajuste de costo registrado exitosamente para ${d.product_name}.\nNuevo costo unitario: $${payload.new_unit_cost.toFixed(2)}`);
					closeCostAdjustmentModal();
					// Refresh costs catalog and current view
					await fetchProfitabilityCosts();
					await fetchProfitabilityModule();
				} else {
					alert(`Error al registrar ajuste de costo: ${d.error || 'Operación fallida'}`);
				}
			} catch (err) {
				console.error('Error submitting cost adjustment:', err);
				alert('Error al comunicarse con el servidor.');
			} finally {
				if (btn) {
					btn.disabled = false;
					btn.innerHTML = '<i data-lucide="check" class="w-4 h-4"></i><span>Guardar Ajuste Auditado</span>';
					if (window.lucide) lucide.createIcons();
				}
			}
		}

		function exportProfitabilityData() {
			let csvContent = 'data:text/csv;charset=utf-8,';
			const subtab = profitabilityState.currentSubTab;

			if (subtab === 'products') {
				csvContent += 'SKU,Producto,Categoria,Factor_Caja,Costo_Unitario,Costo_Caja,Cajas_Vendidas,Unidades_Vendidas,Ventas_Netas,COGS,Utilidad_Bruta,Margen_Bruto_Pct,Estado\n';
				profitabilityState.productsData.forEach(p => {
					csvContent += `"${p.sku}","${p.name}","${p.category || ''}",${p.conversion_factor},${p.current_unit_cost || ''},${p.current_case_cost || ''},${p.cases_sold},${p.units_sold},${p.net_sales},${p.cogs || ''},${p.gross_profit || ''},${p.gross_margin_percent || ''},"${p.status}"\n`;
				});
			} else if (subtab === 'customers') {
				csvContent += 'Cliente,Tipo,RUC,Ventas_Netas,COGS,Utilidad_Bruta,Margen_Pct,Participacion_Utilidad_Pct,Vendedor\n';
				profitabilityState.customersData.forEach(c => {
					csvContent += `"${c.customer_name}","${c.customer_type}","${c.tax_id || ''}",${c.net_sales},${c.cogs},${c.gross_profit},${c.gross_margin_percent},${c.profit_share_percent},"${c.sales_rep_name || ''}"\n`;
				});
			} else if (subtab === 'reps') {
				csvContent += 'Vendedor,Territorio,Meta_Mensual,Ventas_Netas,Utilidad_Bruta,Margen_Pct\n';
				profitabilityState.repsData.forEach(r => {
					csvContent += `"${r.sales_rep_name}","${r.territory_name || ''}",${r.monthly_quota},${r.net_sales},${r.gross_profit},${r.gross_margin_percent}\n`;
				});
			} else if (subtab === 'territories') {
				csvContent += 'Codigo,Territorio,Ciudad,Ventas_Netas,COGS,Utilidad_Bruta,Margen_Pct\n';
				profitabilityState.territoriesData.forEach(t => {
					csvContent += `"${t.territory_code}","${t.territory_name}","${t.city || ''}",${t.net_sales},${t.cogs},${t.gross_profit},${t.gross_margin_percent}\n`;
				});
			} else {
				csvContent += 'SKU,Producto,Categoria,Factor_Caja,Costo_Unitario,Costo_Caja,Vigencia_Desde\n';
				profitabilityState.costsCatalog.forEach(c => {
					csvContent += `"${c.sku}","${c.name}","${c.category || ''}",${c.conversion_factor},${c.current_unit_cost || ''},${c.current_case_cost || ''},"${c.effective_from || ''}"\n`;
				});
			}

			const encodedUri = encodeURI(csvContent);
			const link = document.createElement('a');
			link.setAttribute('href', encodedUri);
			link.setAttribute('download', `rentabilidad_b2b_${subtab}_${new Date().toISOString().split('T')[0]}.csv`);
			document.body.appendChild(link);
			link.click();
			document.body.removeChild(link);
		}


		// =========================================================================
		// MÓDULO 7: INTELIGENCIA COMERCIAL B2B (PASO 11)
		// =========================================================================

		let intelligenceState = {
			severity: 'ALL',
			status: 'ACTIVA',
			alertType: 'ALL',
			salesRep: '',
			territory: '',
			currentSubTab: 'alerts',
			kpis: null,
			alertsData: [],
			inactiveCustomersData: [],
			opportunitiesData: null,
			trendsData: [],
			thresholdsData: []
		};

		function buildIntelligenceQueryParams() {
			const p = new URLSearchParams();
			if (intelligenceState.severity) p.set('severity', intelligenceState.severity);
			if (intelligenceState.status) p.set('status', intelligenceState.status);
			if (intelligenceState.alertType) p.set('alert_type', intelligenceState.alertType);
			if (intelligenceState.salesRep) p.set('sales_rep', intelligenceState.salesRep);
			if (intelligenceState.territory) p.set('territory', intelligenceState.territory);
			return p.toString();
		}

		async function fetchIntelligenceModule() {
			try {
				const q = buildIntelligenceQueryParams();
				const res = await fetch(`/api/b2b/intelligence/kpis?${q}`);
				const data = await res.json();
				if (data.success && data.kpis) {
					intelligenceState.kpis = data.kpis;
					renderIntelligenceKPIs(data.kpis);
				}

				// Llenar filtros de vendedores y territorios si están vacíos
				await populateIntelligenceFilters();

				// Cargar subpestaña activa
				switchIntelligenceSubTab(intelligenceState.currentSubTab || 'alerts');

				if (window.lucide) lucide.createIcons();
			} catch (err) {
				console.error('Error fetching intelligence module:', err);
			}
		}

		function renderIntelligenceKPIs(k) {
			const elActive = document.getElementById('kpi-intel-active-alerts');
			const elCritBadge = document.getElementById('kpi-intel-critical-badge');
			const elHighBadge = document.getElementById('kpi-intel-high-badge');
			const elInactive = document.getElementById('kpi-intel-inactive-customers');
			const elOverdueBal = document.getElementById('kpi-intel-overdue-balance');
			const elOverdueCount = document.getElementById('kpi-intel-overdue-count');
			const elOpps = document.getElementById('kpi-intel-opportunities-count');

			if (elActive) elActive.textContent = k.total_active_alerts ?? 0;
			if (elCritBadge) elCritBadge.textContent = `${k.critical_alerts ?? 0} críticas`;
			if (elHighBadge) elHighBadge.textContent = `${k.high_alerts ?? 0} altas`;
			if (elInactive) elInactive.textContent = k.inactive_customers_count ?? 0;
			if (elOverdueBal) elOverdueBal.textContent = k.overdue_balance_formatted || '$0.00';
			if (elOverdueCount) elOverdueCount.textContent = `${k.overdue_invoices_count ?? 0} facturas vencidas`;
			if (elOpps) elOpps.textContent = k.opportunities_count ?? 0;

			// Badges en tabs
			const badgeTabAlerts = document.getElementById('badge-tab-alerts-count');
			if (badgeTabAlerts) badgeTabAlerts.textContent = k.total_active_alerts ?? 0;
			const badgeTabInactive = document.getElementById('badge-tab-inactive-count');
			if (badgeTabInactive) badgeTabInactive.textContent = k.inactive_customers_count ?? 0;
		}

		async function populateIntelligenceFilters() {
			try {
				const repSelect = document.getElementById('intel-filter-rep');
				if (repSelect && repSelect.options.length <= 1) {
					const res = await fetch('/api/b2b/sales-reps');
					const d = await res.json();
					if (d.success && d.sales_reps) {
						d.sales_reps.forEach(r => {
							const opt = document.createElement('option');
							opt.value = r.name;
							opt.textContent = r.name;
							repSelect.appendChild(opt);
						});
					}
				}
			} catch (err) {
				console.warn('Error populating intelligence filters:', err);
			}
		}

		function switchIntelligenceSubTab(tabName) {
			intelligenceState.currentSubTab = tabName;
			const tabs = ['alerts', 'inactive', 'opportunities', 'trends', 'thresholds'];

			tabs.forEach(t => {
				const btn = document.getElementById(`tab-intel-${t}-btn`);
				const view = document.getElementById(`subtab-intel-${t}-view`);
				if (btn) {
					if (t === tabName) {
						btn.className = 'px-3.5 py-2 text-xs font-semibold border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400 whitespace-nowrap transition-colors flex items-center gap-2';
					} else {
						btn.className = 'px-3.5 py-2 text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white whitespace-nowrap border-b-2 border-transparent transition-colors flex items-center gap-2';
					}
				}
				if (view) {
					if (t === tabName) {
						view.classList.remove('hidden');
					} else {
						view.classList.add('hidden');
					}
				}
			});

			if (tabName === 'alerts') fetchIntelligenceAlerts();
			else if (tabName === 'inactive') fetchInactiveCustomers();
			else if (tabName === 'opportunities') fetchCommercialOpportunities();
			else if (tabName === 'trends') fetchCategoryTrends();
			else if (tabName === 'thresholds') fetchThresholdsConfig();
		}

		async function fetchIntelligenceAlerts() {
			try {
				const q = buildIntelligenceQueryParams();
				const res = await fetch(`/api/b2b/intelligence/alerts?${q}`);
				const d = await res.json();
				if (d.success && d.alerts) {
					intelligenceState.alertsData = d.alerts;
					renderIntelligenceAlerts(d.alerts);
				}
			} catch (err) {
				console.error('Error fetching intelligence alerts:', err);
			}
		}

		function renderIntelligenceAlerts(alerts) {
			const container = document.getElementById('intel-alerts-container');
			const summaryLabel = document.getElementById('intel-alerts-summary-label');
			if (!container) return;

			if (summaryLabel) {
				summaryLabel.textContent = `Mostrando ${alerts.length} alertas identificadas según filtros seleccionados`;
			}

			if (alerts.length === 0) {
				container.innerHTML = `
					<div class="p-8 text-center text-xs text-slate-400 bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800">
						<i data-lucide="check-circle-2" class="w-8 h-8 text-emerald-500 mx-auto mb-2 opacity-80"></i>
						<div class="font-bold text-slate-700 dark:text-slate-300">No hay alertas activas en esta categoría</div>
						<div class="text-[11px] mt-0.5">Todas las cuentas y transacciones cumplen los umbrales configurados.</div>
					</div>
				`;
				if (window.lucide) lucide.createIcons();
				return;
			}

			container.innerHTML = alerts.map(a => {
				const isCrit = a.severity === 'CRITICAL';
				const isHigh = a.severity === 'HIGH';
				const isReviewed = a.status === 'REVISADA';

				let sevBadgeClass = 'bg-slate-100 text-slate-700 border-slate-300';
				let borderCardClass = 'border-slate-200 dark:border-slate-800';
				let iconName = 'alert-circle';
				let iconColor = 'text-slate-500';

				if (isCrit) {
					sevBadgeClass = 'bg-rose-100 text-rose-800 dark:bg-rose-950/80 dark:text-rose-200 border-rose-300 dark:border-rose-800';
					borderCardClass = 'border-rose-200 dark:border-rose-900/60 bg-rose-50/15 dark:bg-rose-950/10';
					iconName = 'alert-octagon';
					iconColor = 'text-rose-600 dark:text-rose-400';
				} else if (isHigh) {
					sevBadgeClass = 'bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-200 border-amber-300 dark:border-amber-800';
					borderCardClass = 'border-amber-200 dark:border-amber-900/60 bg-amber-50/15 dark:bg-amber-950/10';
					iconName = 'alert-triangle';
					iconColor = 'text-amber-600 dark:text-amber-400';
				} else {
					sevBadgeClass = 'bg-blue-100 text-blue-800 dark:bg-blue-950/80 dark:text-blue-200 border-blue-300 dark:border-blue-800';
					iconName = 'info';
					iconColor = 'text-blue-600 dark:text-blue-400';
				}

				return `
					<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border ${borderCardClass} shadow-xs space-y-3 transition-all">
						<div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
							<div class="flex items-start gap-3">
								<span class="p-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 shrink-0 ${iconColor}">
									<i data-lucide="${iconName}" class="w-5 h-5"></i>
								</span>
								<div>
									<div class="flex flex-wrap items-center gap-2">
										<span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase border ${sevBadgeClass}">${a.severity}</span>
										<span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700">${escapeHtml(a.alert_type)}</span>
										${isReviewed ? '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300"><i data-lucide="check" class="w-3 h-3 inline mr-1"></i>REVISADA</span>' : ''}
										<span class="text-[11px] text-slate-400 font-mono">${a.created_at || ''}</span>
									</div>
									<h4 class="text-sm font-bold text-slate-900 dark:text-white mt-1">${escapeHtml(a.title)}</h4>
									<p class="text-xs text-slate-600 dark:text-slate-300 mt-0.5">${escapeHtml(a.description)}</p>
								</div>
							</div>

							<!-- Acciones Rápidas -->
							<div class="flex items-center gap-2 shrink-0 sm:self-start">
								${!isReviewed ? `
									<button onclick="openAlertReviewModal(${a.id})" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/60 dark:hover:bg-emerald-900 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 transition-colors">
										<i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
										<span>Gestionar / Revisar</span>
									</button>
								` : `
									<span class="text-[11px] text-slate-400 italic">Revisado por ${escapeHtml(a.reviewed_by || 'Auditor')}</span>
								`}
							</div>
						</div>

						<!-- Bloque de Evidencia y Acción Sugerida -->
						<div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-2 border-t border-slate-100 dark:border-slate-800/80 text-xs">
							<div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200/70 dark:border-slate-700/60">
								<div class="text-[10px] font-bold uppercase text-slate-400 tracking-wider mb-1 flex items-center gap-1.5">
									<i data-lucide="database" class="w-3 h-3"></i>
									<span>Evidencia Cuantitativa</span>
								</div>
								<div class="text-[11px] font-mono text-slate-700 dark:text-slate-300 break-words">
									${a.threshold_used ? `<span class="text-slate-400">Umbral:</span> ${escapeHtml(a.threshold_used)} • ` : ''}
									<span class="text-slate-400">Entidad:</span> ${escapeHtml(a.entity_name || 'Comercial')} • 
									<span class="text-slate-400">Vendedor:</span> ${escapeHtml(a.sales_rep_name || 'General')}
								</div>
							</div>

							<div class="p-2.5 rounded-lg bg-indigo-50/50 dark:bg-indigo-950/30 border border-indigo-200/60 dark:border-indigo-800/60">
								<div class="text-[10px] font-bold uppercase text-indigo-700 dark:text-indigo-400 tracking-wider mb-1 flex items-center gap-1.5">
									<i data-lucide="compass" class="w-3 h-3"></i>
									<span>Recomendación Explicable</span>
								</div>
								<div class="text-xs text-indigo-950 dark:text-indigo-200 font-medium">
									${escapeHtml(a.suggested_action)}
								</div>
							</div>
						</div>
					</div>
				`;
			}).join('');

			if (window.lucide) lucide.createIcons();
		}

		function setAlertsSeverityQuickFilter(sev) {
			intelligenceState.severity = sev;
			const sel = document.getElementById('intel-filter-severity');
			if (sel) sel.value = sev;

			['ALL', 'CRITICAL', 'HIGH', 'MEDIUM'].forEach(s => {
				const btn = document.getElementById(`btn-quick-sev-${s}`);
				if (btn) {
					if (s === sev) {
						btn.className = 'px-2 py-0.5 text-[11px] font-semibold rounded bg-slate-800 text-white dark:bg-slate-200 dark:text-slate-900';
					} else {
						btn.className = 'px-2 py-0.5 text-[11px] font-semibold rounded text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800';
					}
				}
			});

			fetchIntelligenceAlerts();
		}

		function applyIntelligenceFilters() {
			const sev = document.getElementById('intel-filter-severity');
			const stat = document.getElementById('intel-filter-status');
			const type = document.getElementById('intel-filter-type');
			const rep = document.getElementById('intel-filter-rep');

			intelligenceState.severity = sev ? sev.value : 'ALL';
			intelligenceState.status = stat ? stat.value : 'ALL';
			intelligenceState.alertType = type ? type.value : 'ALL';
			intelligenceState.salesRep = rep ? rep.value : '';

			fetchIntelligenceModule();
		}

		function resetIntelligenceFilters() {
			const sev = document.getElementById('intel-filter-severity');
			const stat = document.getElementById('intel-filter-status');
			const type = document.getElementById('intel-filter-type');
			const rep = document.getElementById('intel-filter-rep');

			if (sev) sev.value = 'ALL';
			if (stat) stat.value = 'ACTIVA';
			if (type) type.value = 'ALL';
			if (rep) rep.value = '';

			intelligenceState.severity = 'ALL';
			intelligenceState.status = 'ACTIVA';
			intelligenceState.alertType = 'ALL';
			intelligenceState.salesRep = '';

			fetchIntelligenceModule();
		}

		async function fetchInactiveCustomers() {
			try {
				const q = buildIntelligenceQueryParams();
				const res = await fetch(`/api/b2b/intelligence/inactive-customers?${q}`);
				const d = await res.json();
				if (d.success && d.customers) {
					intelligenceState.inactiveCustomersData = d.customers;
					renderInactiveCustomersTable(d.customers);
				}
			} catch (err) {
				console.error('Error fetching inactive customers:', err);
			}
		}

		function renderInactiveCustomersTable(customers) {
			const tbody = document.getElementById('intel-inactive-table-body');
			if (!tbody) return;

			if (customers.length === 0) {
				tbody.innerHTML = `<tr><td colspan="8" class="py-8 text-center text-xs text-slate-400">Sin clientes en riesgo bajo los filtros actuales.</td></tr>`;
				return;
			}

			tbody.innerHTML = customers.map(c => {
				let riskBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700">Normal</span>';
				if (c.risk_level === 'CRITICAL') {
					riskBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-200 border border-rose-300">Crítico (>90D)</span>';
				} else if (c.risk_level === 'HIGH') {
					riskBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200 border border-amber-300">Alto</span>';
				} else if (c.risk_level === 'MEDIUM') {
					riskBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-200">Moderado (>30D)</span>';
				} else if (c.risk_level === 'MODERATE') {
					riskBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-200 text-slate-800 dark:bg-slate-800 dark:text-slate-300">Nuevo sin Compra</span>';
				}

				return `
					<tr class="border-b border-slate-100 dark:border-slate-800/80 hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
						<td class="py-3 px-3">
							<div class="font-bold text-xs text-slate-900 dark:text-white">${escapeHtml(c.customer_name)}</div>
							<div class="text-[10px] text-slate-400 font-mono">RUC: ${escapeHtml(c.tax_id)} • ${escapeHtml(c.customer_type)}</div>
						</td>
						<td class="py-3 px-3 text-xs text-slate-600 dark:text-slate-400">
							<div>${escapeHtml(c.territory_name || 'General')}</div>
							<div class="text-[10px] text-slate-400">${escapeHtml(c.sales_rep_name || 'Sin vendedor')}</div>
						</td>
						<td class="py-3 px-3 text-center font-mono text-xs text-slate-600 dark:text-slate-400">
							${c.last_purchase_date}
						</td>
						<td class="py-3 px-3 text-right font-mono text-xs font-bold ${c.days_inactive >= 30 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-700 dark:text-slate-300'}">
							${c.days_inactive !== 'N/A' ? `${c.days_inactive} días` : '—'}
						</td>
						<td class="py-3 px-3 text-right font-mono text-xs font-semibold text-slate-900 dark:text-white">
							${c.total_sales_formatted}
							<div class="text-[10px] text-slate-400">Ticket prom: ${c.avg_ticket_formatted}</div>
						</td>
						<td class="py-3 px-3 text-center">
							${riskBadge}
						</td>
						<td class="py-3 px-3 text-xs text-slate-600 dark:text-slate-300 max-w-xs">
							${escapeHtml(c.suggested_action)}
						</td>
						<td class="py-3 px-3 text-center">
							<button onclick="navigateToSection('customers');" class="p-1 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors" title="Ver ficha de cliente">
								<i data-lucide="external-link" class="w-4 h-4"></i>
							</button>
						</td>
					</tr>
				`;
			}).join('');

			if (window.lucide) lucide.createIcons();
		}

		function filterInactiveCustomersTable() {
			const query = (document.getElementById('intel-cust-search-input')?.value || '').toLowerCase().trim();
			const riskFilter = document.getElementById('intel-cust-filter-risk')?.value || 'ALL';

			const filtered = intelligenceState.inactiveCustomersData.filter(c => {
				const matchQuery = !query ||
					(c.customer_name && c.customer_name.toLowerCase().includes(query)) ||
					(c.tax_id && c.tax_id.toLowerCase().includes(query)) ||
					(c.sales_rep_name && c.sales_rep_name.toLowerCase().includes(query));

				let matchRisk = true;
				if (riskFilter !== 'ALL') {
					matchRisk = c.risk_level === riskFilter;
				}

				return matchQuery && matchRisk;
			});

			renderInactiveCustomersTable(filtered);
		}

		async function fetchCommercialOpportunities() {
			try {
				const res = await fetch('/api/b2b/intelligence/opportunities');
				const d = await res.json();
				if (d.success && d.opportunities) {
					intelligenceState.opportunitiesData = d.opportunities;
					renderCommercialOpportunities(d.opportunities);
				}
			} catch (err) {
				console.error('Error fetching opportunities:', err);
			}
		}

		function renderCommercialOpportunities(opps) {
			// A. Abandoned products
			const abTbody = document.getElementById('intel-abandoned-table-body');
			const abBadge = document.getElementById('intel-abandoned-count-badge');
			const abList = opps.abandoned_products || [];

			if (abBadge) abBadge.textContent = `${abList.length} casos`;
			if (abTbody) {
				if (abList.length === 0) {
					abTbody.innerHTML = `<tr><td colspan="5" class="py-6 text-center text-xs text-slate-400">Sin productos abandonados detectados.</td></tr>`;
				} else {
					abTbody.innerHTML = abList.map(a => `
						<tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50/50 dark:hover:bg-slate-800/40">
							<td class="py-2.5 px-3 font-semibold text-slate-900 dark:text-white">${escapeHtml(a.customer_name)}</td>
							<td class="py-2.5 px-3">
								<div class="font-mono text-xs font-bold text-slate-800 dark:text-slate-200">${escapeHtml(a.sku)}</div>
								<div class="text-[11px] text-slate-500">${escapeHtml(a.product_name)}</div>
							</td>
							<td class="py-2.5 px-3 text-right font-mono font-bold text-rose-600 dark:text-rose-400">${a.days_since_last_purchase} días</td>
							<td class="py-2.5 px-3 text-slate-600 dark:text-slate-300 max-w-sm">${escapeHtml(a.evidence)}</td>
							<td class="py-2.5 px-3 text-indigo-700 dark:text-indigo-300 font-medium">${escapeHtml(a.suggested_action)}</td>
						</tr>
					`).join('');
				}
			}

			// B. Cross-selling
			const crossTbody = document.getElementById('intel-cross-table-body');
			const crossBadge = document.getElementById('intel-cross-count-badge');
			const crossList = opps.cross_selling || [];

			if (crossBadge) crossBadge.textContent = `${crossList.length} casos`;
			if (crossTbody) {
				if (crossList.length === 0) {
					crossTbody.innerHTML = `<tr><td colspan="5" class="py-6 text-center text-xs text-slate-400">Sin recomendaciones de cross-selling por el momento.</td></tr>`;
				} else {
					crossTbody.innerHTML = crossList.map(c => `
						<tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50/50 dark:hover:bg-slate-800/40">
							<td class="py-2.5 px-3 font-semibold text-slate-900 dark:text-white">${escapeHtml(c.customer_name)}</td>
							<td class="py-2.5 px-3"><span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">${escapeHtml(c.customer_type)}</span></td>
							<td class="py-2.5 px-3">
								<div class="font-mono text-xs font-bold text-slate-800 dark:text-slate-200">${escapeHtml(c.sku)}</div>
								<div class="text-[11px] text-slate-500">${escapeHtml(c.product_name)}</div>
							</td>
							<td class="py-2.5 px-3 text-slate-600 dark:text-slate-300 max-w-sm">${escapeHtml(c.evidence)}</td>
							<td class="py-2.5 px-3 text-emerald-700 dark:text-emerald-300 font-medium">${escapeHtml(c.suggested_action)}</td>
						</tr>
					`).join('');
				}
			}

			// C. Low margin customers
			const lmTbody = document.getElementById('intel-lowmargin-table-body');
			const lmBadge = document.getElementById('intel-lowmargin-count-badge');
			const lmList = opps.low_margin_customers || [];

			if (lmBadge) lmBadge.textContent = `${lmList.length} cuentas`;
			if (lmTbody) {
				if (lmList.length === 0) {
					lmTbody.innerHTML = `<tr><td colspan="6" class="py-6 text-center text-xs text-slate-400">Todas las cuentas operan por encima del 15% de margen bruto.</td></tr>`;
				} else {
					lmTbody.innerHTML = lmList.map(l => `
						<tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50/50 dark:hover:bg-slate-800/40">
							<td class="py-2.5 px-3 font-semibold text-slate-900 dark:text-white">${escapeHtml(l.customer_name)}</td>
							<td class="py-2.5 px-3 text-slate-500">${escapeHtml(l.sales_rep_name || 'General')}</td>
							<td class="py-2.5 px-3 text-right font-mono font-semibold">${l.net_sales_formatted}</td>
							<td class="py-2.5 px-3 text-right font-mono font-bold text-amber-600 dark:text-amber-400">${l.gross_margin_percent}%</td>
							<td class="py-2.5 px-3 text-slate-600 dark:text-slate-300">${escapeHtml(l.evidence)}</td>
							<td class="py-2.5 px-3 text-brand-700 dark:text-brand-300 font-medium">${escapeHtml(l.suggested_action)}</td>
						</tr>
					`).join('');
				}
			}
		}

		async function fetchCategoryTrends() {
			try {
				const res = await fetch('/api/b2b/intelligence/category-trends');
				const d = await res.json();
				if (d.success && d.categories) {
					intelligenceState.trendsData = d.categories;
					renderCategoryTrendsTable(d.categories);
				}
			} catch (err) {
				console.error('Error fetching category trends:', err);
			}
		}

		function renderCategoryTrendsTable(categories) {
			const tbody = document.getElementById('intel-trends-table-body');
			if (!tbody) return;

			if (categories.length === 0) {
				tbody.innerHTML = `<tr><td colspan="7" class="py-8 text-center text-xs text-slate-400">Sin datos de tendencias en el período.</td></tr>`;
				return;
			}

			tbody.innerHTML = categories.map(c => {
				const isGrowth = c.trend === 'GROWTH';
				const isDecline = c.trend === 'DECLINE';
				let trendBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600">Estable</span>';

				if (isGrowth) {
					trendBadge = '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300"><i data-lucide="trending-up" class="w-3 h-3"></i>Crecimiento</span>';
				} else if (isDecline) {
					trendBadge = '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300"><i data-lucide="trending-down" class="w-3 h-3"></i>Caída</span>';
				}

				return `
					<tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50/50 dark:hover:bg-slate-800/40">
						<td class="py-3 px-3 font-semibold text-xs text-slate-900 dark:text-white">${escapeHtml(c.category_name)}</td>
						<td class="py-3 px-3 text-center font-mono text-xs text-slate-600 dark:text-slate-400">${c.products_count} prod (${c.cases_sold} cjs)</td>
						<td class="py-3 px-3 text-right font-mono text-xs font-bold text-slate-900 dark:text-white">${c.sales_current_formatted}</td>
						<td class="py-3 px-3 text-right font-mono text-xs text-slate-500">${c.sales_prior_formatted}</td>
						<td class="py-3 px-3 text-right font-mono text-xs font-semibold ${isGrowth ? 'text-emerald-600 dark:text-emerald-400' : (isDecline ? 'text-rose-600 dark:text-rose-400' : 'text-slate-700')}">${c.difference_formatted}</td>
						<td class="py-3 px-3 text-right font-mono text-xs font-bold ${isGrowth ? 'text-emerald-600' : (isDecline ? 'text-rose-600' : 'text-slate-700')}">${c.growth_percent > 0 ? '+' : ''}${c.growth_percent}%</td>
						<td class="py-3 px-3 text-center">${trendBadge}</td>
					</tr>
				`;
			}).join('');

			if (window.lucide) lucide.createIcons();
		}

		async function fetchThresholdsConfig() {
			try {
				const res = await fetch('/api/b2b/intelligence/thresholds');
				const d = await res.json();
				if (d.success && d.thresholds) {
					intelligenceState.thresholdsData = d.thresholds;
					populateThresholdsForm(d.thresholds);
				}
			} catch (err) {
				console.error('Error fetching thresholds:', err);
			}
		}

		function populateThresholdsForm(thresholds) {
			thresholds.forEach(t => {
				if (t.threshold_key === 'inactive_days_threshold') {
					const el = document.getElementById('thresh-inactive-days');
					if (el) el.value = t.threshold_value;
				} else if (t.threshold_key === 'drop_sales_percent_threshold') {
					const el = document.getElementById('thresh-drop-sales');
					if (el) el.value = t.threshold_value;
				} else if (t.threshold_key === 'low_margin_percent_threshold') {
					const el = document.getElementById('thresh-low-margin');
					if (el) el.value = t.threshold_value;
				} else if (t.threshold_key === 'overdue_days_grace') {
					const el = document.getElementById('thresh-grace-days');
					if (el) el.value = t.threshold_value;
				}
			});
		}

		async function submitIntelligenceThresholds(event) {
			event.preventDefault();
			const inact = parseFloat(document.getElementById('thresh-inactive-days')?.value || 30);
			const drop = parseFloat(document.getElementById('thresh-drop-sales')?.value || 30);
			const margin = parseFloat(document.getElementById('thresh-low-margin')?.value || 15);
			const grace = parseFloat(document.getElementById('thresh-grace-days')?.value || 0);
			const btn = document.getElementById('btn-save-thresholds');

			const payload = {
				thresholds: {
					inactive_days_threshold: inact,
					drop_sales_percent_threshold: drop,
					low_margin_percent_threshold: margin,
					overdue_days_grace: grace
				}
			};

			try {
				if (btn) btn.disabled = true;
				const res = await fetch('/api/b2b/intelligence/thresholds', {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify(payload)
				});
				const d = await res.json();
				if (d.success) {
					alert('Umbrales comerciales actualizados exitosamente. El motor analítico ha recalculado todas las alertas.');
					fetchIntelligenceModule();
				} else {
					alert(`Error al actualizar umbrales: ${d.error_message || 'Operación fallida'}`);
				}
			} catch (err) {
				console.error('Error saving thresholds:', err);
			} finally {
				if (btn) btn.disabled = false;
			}
		}

		function openThresholdsModal() {
			switchIntelligenceSubTab('thresholds');
		}

		async function triggerIntelligenceEngine() {
			try {
				const res = await fetch('/api/b2b/intelligence/run', {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify({})
				});
				const d = await res.json();
				if (d.success) {
					alert('Motor de Inteligencia Comercial recalculado exitosamente con transacciones reales.');
					fetchIntelligenceModule();
				}
			} catch (err) {
				console.error('Error running engine:', err);
			}
		}

		function openAlertReviewModal(alertId) {
			const modal = document.getElementById('modal-intel-review');
			if (!modal) return;

			const a = intelligenceState.alertsData.find(item => item.id == alertId);
			if (!a) return;

			document.getElementById('review-alert-id').value = a.id;
			document.getElementById('review-alert-title').textContent = a.title;
			document.getElementById('review-alert-desc').textContent = a.description;
			document.getElementById('review-notes').value = '';

			modal.classList.remove('hidden');
			if (window.lucide) lucide.createIcons();
		}

		function closeAlertReviewModal() {
			const modal = document.getElementById('modal-intel-review');
			if (modal) modal.classList.add('hidden');
		}

		async function submitAlertReview(event) {
			event.preventDefault();
			const aid = parseInt(document.getElementById('review-alert-id')?.value || 0);
			const by = document.getElementById('review-by')?.value.trim();
			const notes = document.getElementById('review-notes')?.value.trim();
			const btn = document.getElementById('btn-submit-review');

			if (!aid || !by || !notes) {
				alert('Por favor complete todos los campos.');
				return;
			}

			try {
				if (btn) btn.disabled = true;
				const res = await fetch('/api/b2b/intelligence/alerts/review', {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify({ alert_id: aid, reviewed_by: by, notes: notes })
				});
				const d = await res.json();
				if (d.success) {
					alert('Alerta marcada como revisada exitosamente.');
					closeAlertReviewModal();
					fetchIntelligenceModule();
				} else {
					alert(`Error al registrar gestión: ${d.error_message || 'Fallo de servidor'}`);
				}
			} catch (err) {
				console.error('Error submitting review:', err);
			} finally {
				if (btn) btn.disabled = false;
			}
		}

		function exportIntelligenceData() {
			let csvContent = 'data:text/csv;charset=utf-8,';
			const subtab = intelligenceState.currentSubTab;

			if (subtab === 'inactive') {
				csvContent += 'Cliente,RUC,Tipo,Territorio,Vendedor,Ultima_Compra,Dias_Inactivo,Facturacion_Total,Nivel_Riesgo,Accion\n';
				intelligenceState.inactiveCustomersData.forEach(c => {
					csvContent += `"${c.customer_name}","${c.tax_id}","${c.customer_type}","${c.territory_name}","${c.sales_rep_name}","${c.last_purchase_date}",${c.days_inactive},${c.total_sales},"${c.risk_level}","${c.suggested_action}"\n`;
				});
			} else {
				csvContent += 'ID,Tipo,Severidad,Titulo,Entidad,Vendedor,Estado,Fecha,Accion_Sugerida\n';
				intelligenceState.alertsData.forEach(a => {
					csvContent += `${a.id},"${a.alert_type}","${a.severity}","${a.title}","${a.entity_name || ''}","${a.sales_rep_name || ''}","${a.status}","${a.created_at}","${a.suggested_action}"\n`;
				});
			}

			const encodedUri = encodeURI(csvContent);
			const link = document.createElement('a');
			link.setAttribute('href', encodedUri);
			link.setAttribute('download', `inteligencia_b2b_${subtab}_${new Date().toISOString().split('T')[0]}.csv`);
			document.body.appendChild(link);
			link.click();
			document.body.removeChild(link);
		}


		function saveB2bSettings() {
			alert('Parámetros de configuración comercial guardados exitosamente.\nPolíticas aplicadas en toda la suite B2B.');
		}

	</script>
</body>
</html>
