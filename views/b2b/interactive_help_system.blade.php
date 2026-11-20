{{-- SISTEMA INTEGRAL DE SIMPLIFICACIÓN DEL LENGUAJE, AYUDA INTERACTIVA Y GLOSARIO INTELIGENTE --}}

<!-- ========================================================================= -->
<!-- 1. MODAL REUTILIZABLE DE AYUDA INTERACTIVA DE KPIS (6 SECCIONES + 3 ROLES)-->
<!-- ========================================================================= -->
<div id="modal-kpi-interactive-help" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 transition-all" role="dialog" aria-modal="true" aria-labelledby="kpi-help-modal-title">
	<div class="relative w-full max-w-2xl bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl overflow-hidden transform transition-all animate-in fade-in zoom-in-95 duration-150">
		
		<!-- Modal Header con Título, Nombre Simple y Badge Técnico -->
		<div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800/80 bg-slate-50/70 dark:bg-slate-900/40 flex items-start justify-between gap-3">
			<div class="flex items-center gap-3">
				<div id="kpi-help-icon-box" class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200/60 dark:border-indigo-800/60 flex items-center justify-center shrink-0">
					<i data-lucide="help-circle" class="w-5 h-5"></i>
				</div>
				<div>
					<div class="flex flex-wrap items-center gap-2">
						<h3 id="kpi-help-modal-title" class="text-base sm:text-lg font-bold text-slate-900 dark:text-white leading-snug">
							Nombre del Indicador
						</h3>
						<span id="kpi-help-tech-badge" class="px-2 py-0.5 text-[10px] font-mono font-semibold rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
							TÉCNICO
						</span>
					</div>
					<p id="kpi-help-category" class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
						Categoría
					</p>
				</div>
			</div>
			<button onclick="closeKpiHelpModal()" class="p-1.5 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors" title="Cerrar ayuda (Esc)">
				<i data-lucide="x" class="w-5 h-5"></i>
			</button>
		</div>

		<!-- Selector de Rol para la Perspectiva de Decisiones -->
		<div class="px-5 py-2.5 bg-slate-100/70 dark:bg-slate-900/70 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between gap-2 text-xs">
			<span class="font-medium text-slate-600 dark:text-slate-400 flex items-center gap-1.5 shrink-0">
				<i data-lucide="user" class="w-3.5 h-3.5 text-slate-400"></i>
				<span data-i18n="help_view_as">Ver consejos para:</span>
			</span>
			<div class="inline-flex p-0.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-2xs">
				<button id="role-tab-employee" onclick="switchHelpRole('employee')" class="px-2.5 py-1 font-semibold rounded-md text-indigo-700 dark:text-white bg-indigo-50 dark:bg-indigo-950/80 shadow-2xs transition-all flex items-center gap-1">
					<span>👷</span> <span class="hidden sm:inline">Operario / Almacén</span><span class="sm:hidden">Operario</span>
				</button>
				<button id="role-tab-supervisor" onclick="switchHelpRole('supervisor')" class="px-2.5 py-1 font-medium rounded-md text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-all flex items-center gap-1">
					<span>👔</span> <span class="hidden sm:inline">Supervisor</span><span class="sm:hidden">Superv.</span>
				</button>
				<button id="role-tab-manager" onclick="switchHelpRole('manager')" class="px-2.5 py-1 font-medium rounded-md text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-all flex items-center gap-1">
					<span>💼</span> <span class="hidden sm:inline">Gerente General</span><span class="sm:hidden">Gerente</span>
				</button>
			</div>
		</div>

		<!-- Cuerpo del Modal: Las 6 Preguntas Claras -->
		<div class="p-5 max-h-[70vh] overflow-y-auto space-y-4 text-xs sm:text-sm">
			
			<!-- Pregunta 1: ¿Qué significa? -->
			<div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/80 dark:border-slate-800">
				<h4 class="font-bold text-slate-900 dark:text-white flex items-center gap-2 text-xs uppercase tracking-wider text-indigo-600 dark:text-indigo-400">
					<i data-lucide="info" class="w-4 h-4 shrink-0"></i>
					<span>1. ¿Qué significa este indicador?</span>
				</h4>
				<p id="kpi-help-what" class="mt-2 text-slate-700 dark:text-slate-300 leading-relaxed text-xs sm:text-sm">
					Cargando explicación...
				</p>
			</div>

			<!-- Pregunta 2: ¿Para qué sirve en mi negocio? -->
			<div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/80 dark:border-slate-800">
				<h4 class="font-bold text-slate-900 dark:text-white flex items-center gap-2 text-xs uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
					<i data-lucide="target" class="w-4 h-4 shrink-0"></i>
					<span>2. ¿Para qué le sirve a mi empresa?</span>
				</h4>
				<p id="kpi-help-why" class="mt-2 text-slate-700 dark:text-slate-300 leading-relaxed text-xs sm:text-sm">
					Cargando utilidad práctica...
				</p>
			</div>

			<!-- Pregunta 3: ¿Cómo se calcula? (Fórmula en palabras + Ejemplo real) -->
			<div class="p-3.5 rounded-xl bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/60">
				<h4 class="font-bold text-indigo-900 dark:text-indigo-300 flex items-center gap-2 text-xs uppercase tracking-wider">
					<i data-lucide="calculator" class="w-4 h-4 shrink-0 text-indigo-600 dark:text-indigo-400"></i>
					<span>3. ¿Cómo se calcula?</span>
				</h4>
				<div class="mt-2 space-y-2">
					<div class="p-2.5 rounded-lg bg-white dark:bg-slate-900 border border-indigo-200/60 dark:border-indigo-800 font-mono text-xs text-indigo-900 dark:text-indigo-200" id="kpi-help-formula">
						Fórmula en palabras
					</div>
					<div class="text-xs text-slate-600 dark:text-slate-400" id="kpi-help-example">
						<strong>Ejemplo numérico con datos reales:</strong> Cargando ejemplo...
					</div>
				</div>
			</div>

			<!-- Pregunta 4: ¿Cómo interpretar el resultado? (Semáforo contextual) -->
			<div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/80 dark:border-slate-800">
				<h4 class="font-bold text-slate-900 dark:text-white flex items-center gap-2 text-xs uppercase tracking-wider text-amber-600 dark:text-amber-400">
					<i data-lucide="gauge" class="w-4 h-4 shrink-0"></i>
					<span>4. ¿Cómo interpretar el resultado?</span>
				</h4>
				<div class="mt-2.5 grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs" id="kpi-help-interpretation-boxes">
					<div class="p-2.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 text-emerald-800 dark:text-emerald-200">
						<div class="font-bold flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Favorable</div>
						<div class="mt-1 text-[11px]" id="kpi-help-interp-favorable">Valor óptimo</div>
					</div>
					<div class="p-2.5 rounded-lg bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 text-amber-800 dark:text-amber-200">
						<div class="font-bold flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-amber-500"></span> Atención</div>
						<div class="mt-1 text-[11px]" id="kpi-help-interp-warning">Requiere seguimiento</div>
					</div>
					<div class="p-2.5 rounded-lg bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 text-rose-800 dark:text-rose-200">
						<div class="font-bold flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-rose-500"></span> Crítico</div>
						<div class="mt-1 text-[11px]" id="kpi-help-interp-critical">Acción urgente</div>
					</div>
				</div>
			</div>

			<!-- Pregunta 5: ¿Qué puedo hacer? (Específico según el rol activo) -->
			<div class="p-3.5 rounded-xl bg-purple-50/60 dark:bg-purple-950/30 border border-purple-200/80 dark:border-purple-800/60">
				<h4 class="font-bold text-purple-900 dark:text-purple-300 flex items-center justify-between text-xs uppercase tracking-wider">
					<div class="flex items-center gap-2">
						<i data-lucide="check-circle-2" class="w-4 h-4 text-purple-600 dark:text-purple-400"></i>
						<span>5. ¿Qué acciones puedo tomar?</span>
					</div>
					<span id="kpi-help-role-label" class="px-2 py-0.5 rounded text-[10px] font-semibold bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 normal-case">
						Perspectiva: Operario / Almacén
					</span>
				</h4>
				<div class="mt-2 text-slate-700 dark:text-slate-300 leading-relaxed text-xs sm:text-sm" id="kpi-help-actions">
					Cargando acciones recomendadas...
				</div>
			</div>

			<!-- Pregunta 6: ¿De dónde sale la información? -->
			<div class="p-3 rounded-lg bg-slate-100/70 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-xs text-slate-500 dark:text-slate-400 flex items-start gap-2.5">
				<i data-lucide="database" class="w-4 h-4 text-slate-400 shrink-0 mt-0.5"></i>
				<div>
					<span class="font-semibold text-slate-700 dark:text-slate-300">Fuente de datos & Actualización:</span>
					<div id="kpi-help-source" class="mt-0.5">
						Datos calculados desde las transacciones reales y conteos de almacén en Supabase PostgreSQL.
					</div>
				</div>
			</div>
		</div>

		<!-- Footer con botones -->
		<div class="px-5 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60 flex items-center justify-between gap-3">
			<button onclick="openGlossaryModal()" class="text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
				<i data-lucide="book-open" class="w-3.5 h-3.5"></i>
				<span>Ver todos los términos en el Glosario</span>
			</button>
			<button onclick="closeKpiHelpModal()" class="px-4 py-2 text-xs font-semibold rounded-lg bg-slate-800 hover:bg-slate-900 text-white dark:bg-slate-700 dark:hover:bg-slate-600 transition-colors shadow-xs">
				Entendido
			</button>
		</div>
	</div>
</div>

<!-- ========================================================================= -->
<!-- 2. MODAL REUTILIZABLE DE AYUDA PARA GRÁFICOS (EJES, COLORES, DECISIONES) -->
<!-- ========================================================================= -->
<div id="modal-chart-interactive-help" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 transition-all" role="dialog" aria-modal="true" aria-labelledby="chart-help-modal-title">
	<div class="relative w-full max-w-xl bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl overflow-hidden transform transition-all animate-in fade-in zoom-in-95 duration-150">
		<div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800/80 bg-slate-50/70 dark:bg-slate-900/40 flex items-center justify-between gap-3">
			<div class="flex items-center gap-3">
				<div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/60 flex items-center justify-center shrink-0">
					<i data-lucide="bar-chart-2" class="w-5 h-5"></i>
				</div>
				<div>
					<h3 id="chart-help-modal-title" class="text-base sm:text-lg font-bold text-slate-900 dark:text-white leading-snug">
						Cómo leer este gráfico
					</h3>
					<p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5" id="chart-help-modal-subtitle">
						Guía visual para interpretar ejes, colores y patrones
					</p>
				</div>
			</div>
			<button onclick="closeChartHelpModal()" class="p-1.5 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors">
				<i data-lucide="x" class="w-5 h-5"></i>
			</button>
		</div>

		<div class="p-5 max-h-[70vh] overflow-y-auto space-y-3.5 text-xs sm:text-sm">
			<!-- Qué representa -->
			<div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/80 dark:border-slate-800">
				<div class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5 text-xs uppercase tracking-wider text-indigo-600 dark:text-indigo-400">
					<i data-lucide="pie-chart" class="w-4 h-4"></i>
					<span>¿Qué información representa?</span>
				</div>
				<p id="chart-help-what" class="mt-1.5 text-slate-700 dark:text-slate-300 leading-relaxed text-xs sm:text-sm">
					Cargando descripción...
				</p>
			</div>

			<!-- Ejes y colores -->
			<div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/80 dark:border-slate-800">
				<div class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5 text-xs uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
					<i data-lucide="palette" class="w-4 h-4"></i>
					<span>¿Qué significan sus ejes, líneas y colores?</span>
				</div>
				<div id="chart-help-axes" class="mt-1.5 text-slate-700 dark:text-slate-300 leading-relaxed text-xs sm:text-sm">
					Cargando leyenda...
				</div>
			</div>

			<!-- Qué patrones observar -->
			<div class="p-3 rounded-xl bg-amber-50/60 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-800/60">
				<div class="font-bold text-amber-900 dark:text-amber-300 flex items-center gap-1.5 text-xs uppercase tracking-wider">
					<i data-lucide="search" class="w-4 h-4 text-amber-600"></i>
					<span>¿Qué patrones o señales debo buscar?</span>
				</div>
				<p id="chart-help-patterns" class="mt-1.5 text-slate-700 dark:text-slate-300 leading-relaxed text-xs sm:text-sm">
					Cargando patrones clave...
				</p>
			</div>

			<!-- Decisiones recomendadas -->
			<div class="p-3 rounded-xl bg-purple-50/60 dark:bg-purple-950/30 border border-purple-200/80 dark:border-purple-800/60">
				<div class="font-bold text-purple-900 dark:text-purple-300 flex items-center gap-1.5 text-xs uppercase tracking-wider">
					<i data-lucide="lightbulb" class="w-4 h-4 text-purple-600"></i>
					<span>Decisiones comerciales que permite tomar</span>
				</div>
				<p id="chart-help-decisions" class="mt-1.5 text-slate-700 dark:text-slate-300 leading-relaxed text-xs sm:text-sm">
					Cargando decisiones...
				</p>
			</div>
		</div>

		<div class="px-5 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60 text-right">
			<button onclick="closeChartHelpModal()" class="px-4 py-2 text-xs font-semibold rounded-lg bg-slate-800 hover:bg-slate-900 text-white dark:bg-slate-700 dark:hover:bg-slate-600 transition-colors shadow-xs">
				Cerrar
			</button>
		</div>
	</div>
</div>

<!-- ========================================================================= -->
<!-- 3. MODAL DEL GLOSARIO CENTRAL DE TÉRMINOS CON BUSCADOR EN VIVO          -->
<!-- ========================================================================= -->
<div id="modal-business-glossary" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 transition-all" role="dialog" aria-modal="true" aria-labelledby="glossary-modal-title">
	<div class="relative w-full max-w-4xl bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl overflow-hidden transform transition-all animate-in fade-in zoom-in-95 duration-150 flex flex-col max-h-[85vh]">
		
		<!-- Header Glosario con Buscador y Filtros -->
		<div class="p-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/40 shrink-0">
			<div class="flex items-center justify-between gap-3 mb-3">
				<div class="flex items-center gap-3">
					<div class="w-10 h-10 rounded-xl bg-brand-50 dark:bg-brand-950/60 text-brand-700 dark:text-brand-300 border border-brand-200/60 dark:border-brand-800/60 flex items-center justify-center shrink-0">
						<i data-lucide="book-open" class="w-5 h-5"></i>
					</div>
					<div>
						<h3 id="glossary-modal-title" class="text-lg font-bold text-slate-900 dark:text-white">
							Glosario de Negocio PRINCES
						</h3>
						<p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
							Definiciones claras, fórmulas y ejemplos prácticos para todo el equipo
						</p>
					</div>
				</div>
				<button onclick="closeGlossaryModal()" class="p-1.5 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors">
					<i data-lucide="x" class="w-5 h-5"></i>
				</button>
			</div>

			<!-- Buscador y chips de categoría -->
			<div class="flex flex-col sm:flex-row sm:items-center gap-2.5">
				<div class="relative flex-1">
					<i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
					<input type="text" id="glossary-search-input" oninput="filterGlossaryItems()" placeholder="Buscar por término simple o técnico (ej: Sell-through, Rotación, OTIF, GMROI)..." class="w-full pl-9 pr-4 py-2 text-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
				</div>
				<div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0 text-xs shrink-0" id="glossary-category-chips">
					<button onclick="setGlossaryCategory('ALL')" class="glossary-cat-chip active px-2.5 py-1.5 rounded-lg font-semibold bg-brand-800 text-white shadow-2xs transition-all">Todos</button>
					<button onclick="setGlossaryCategory('Ventas')" class="glossary-cat-chip px-2.5 py-1.5 rounded-lg font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-200/60 dark:hover:bg-slate-800 transition-all">Ventas</button>
					<button onclick="setGlossaryCategory('Inventario')" class="glossary-cat-chip px-2.5 py-1.5 rounded-lg font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-200/60 dark:hover:bg-slate-800 transition-all">Inventario & PLM</button>
					<button onclick="setGlossaryCategory('Finanzas')" class="glossary-cat-chip px-2.5 py-1.5 rounded-lg font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-200/60 dark:hover:bg-slate-800 transition-all">Finanzas</button>
					<button onclick="setGlossaryCategory('Logistica')" class="glossary-cat-chip px-2.5 py-1.5 rounded-lg font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-200/60 dark:hover:bg-slate-800 transition-all">Logística & Almacén</button>
				</div>
			</div>
		</div>

		<!-- Lista de Términos con Scroll -->
		<div class="flex-1 overflow-y-auto p-5 divide-y divide-slate-100 dark:divide-slate-800/80 space-y-4" id="glossary-items-container">
			<!-- Generado dinámicamente -->
		</div>

		<!-- Footer -->
		<div class="px-5 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60 flex items-center justify-between text-xs text-slate-500 shrink-0">
			<span id="glossary-count-summary">Mostrando 20 términos</span>
			<button onclick="closeGlossaryModal()" class="px-4 py-2 text-xs font-semibold rounded-lg bg-slate-800 hover:bg-slate-900 text-white dark:bg-slate-700 dark:hover:bg-slate-600 transition-colors shadow-xs">
				Cerrar Glosario
			</button>
		</div>
	</div>
</div>

<!-- ========================================================================= -->
<!-- 4. TOUR GUIADO INTERACTIVO: "APRENDER A USAR ESTE TABLERO" (STEP-BY-STEP) -->
<!-- ========================================================================= -->
<div id="tour-guide-overlay" class="fixed inset-0 z-50 hidden bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4 transition-all">
	<div id="tour-guide-card" class="relative w-full max-w-lg bg-white dark:bg-surface-dark border-2 border-brand-500 rounded-2xl shadow-2xl p-6 transform transition-all animate-in fade-in zoom-in-95">
		
		<div class="flex items-start justify-between gap-3 mb-4">
			<div class="flex items-center gap-2.5">
				<span class="w-7 h-7 rounded-lg bg-brand-100 dark:bg-brand-950/80 text-brand-800 dark:text-brand-300 font-bold text-xs flex items-center justify-center" id="tour-step-badge">
					1
				</span>
				<div>
					<div class="text-[10px] font-semibold uppercase tracking-wider text-brand-600 dark:text-brand-400">Guía Interactiva Paso a Paso</div>
					<h3 class="text-base font-bold text-slate-900 dark:text-white" id="tour-step-title">
						Bienvenido a PRINCES
					</h3>
				</div>
			</div>
			<button onclick="endDashboardTour()" class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-lg">
				<i data-lucide="x" class="w-4 h-4"></i>
			</button>
		</div>

		<div class="text-xs sm:text-sm text-slate-700 dark:text-slate-300 leading-relaxed mb-6 space-y-2" id="tour-step-content">
			<!-- Explicación del paso -->
		</div>

		<!-- Progreso y controles -->
		<div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-slate-800">
			<span class="text-xs text-slate-400 font-medium" id="tour-progress-text">
				Paso 1 de 5
			</span>
			<div class="flex items-center gap-2">
				<button id="tour-btn-prev" onclick="prevTourStep()" class="px-3 py-1.5 text-xs font-medium rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 disabled:opacity-40 transition-colors">
					Anterior
				</button>
				<button id="tour-btn-next" onclick="nextTourStep()" class="px-4 py-1.5 text-xs font-semibold rounded-lg bg-brand-800 hover:bg-brand-700 text-white shadow-xs transition-colors flex items-center gap-1.5">
					<span id="tour-next-label">Siguiente</span>
					<i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
				</button>
			</div>
		</div>
	</div>
</div>

<!-- ========================================================================= -->
<!-- 5. BASE DE CONOCIMIENTO CENTRAL (GLOSARIO, GRÁFICOS Y CONTROLADORES JS)    -->
<!-- ========================================================================= -->
<script>
	// Estado activo del sistema de ayuda
	let currentHelpMetricKey = null;
	let currentHelpRole = 'employee'; // 'employee', 'supervisor', 'manager'
	let currentTourStep = 0;
	let currentGlossaryCat = 'ALL';

	// DICCIONARIO MAESTRO DEL GLOSARIO DE NEGOCIOS B2B
	const b2bGlossaryData = {
		// --- VENTAS & COMERCIAL ---
		net_sales: {
			key: 'net_sales',
			name_es: 'Ventas Netas Facturadas',
			name_en: 'Net B2B Sales',
			technical_name: 'Net Billed Sales (excl. Tax & Credit Notes)',
			category: 'Ventas',
			what_is_it_es: 'Es el dinero real que la empresa ha facturado a clientes por la venta de mercadería, una vez descontadas las devoluciones, anulaciones y notas de crédito comerciales, antes de impuestos.',
			what_is_it_en: 'Actual revenues billed to wholesale clients after deducting returns, cancellations, and commercial credit notes, before taxes.',
			why_it_matters_es: 'Representa el ingreso genuino del negocio. No infla la cifra con impuestos (como el IGV) ni con pedidos que luego fueron devueltos.',
			why_it_matters_en: 'Represents true gross revenue without tax distortion or returned merchandise.',
			formula_text_es: 'Total Facturas Válidas Emitidas − Total Notas de Crédito Aplicadas',
			formula_text_en: 'Total Valid Invoices − Total Credit Notes Applied',
			formula_example_es: 'Si emitimos $100.000 en facturas comerciales y tuvimos $2.000 en notas de crédito por mercadería devuelta, las Ventas Netas son $98.000.',
			formula_example_en: 'If $100,000 was invoiced and $2,000 issued in return credit notes, Net Sales equals $98,000.',
			interpretation_es: {
				favorable: 'Crecimiento sostenido frente al mes anterior sin incremento desproporcionado de devoluciones.',
				warning: 'Ventas estancadas o con más del 5% de notas de crédito aplicadas sobre el total facturado.',
				critical: 'Caída de ventas superior al 10% o devoluciones frecuentes por fallas de despacho.'
			},
			roles_es: {
				employee: 'Asegurar que cada entrega lleve el albarán y factura correctos para evitar devoluciones y reclamos de clientes.',
				supervisor: 'Revisar pedidos retenidos o no confirmados para acelerar la facturación antes del cierre del mes.',
				manager: 'Evaluar el cumplimiento de cuotas de facturación y rentabilidad por línea de producto frente al presupuesto.'
			},
			data_source_es: 'Módulo de Facturación y Cuentas por Cobrar de Supabase PostgreSQL (facturas en estado Emitida y Pagada).'
		},

		orders: {
			key: 'orders',
			name_es: 'Pedidos Confirmados',
			name_en: 'Confirmed Orders',
			technical_name: 'Closed Wholesale Sales Orders',
			category: 'Ventas',
			what_is_it_es: 'Cantidad de órdenes comerciales de compra emitidas por clientes que han sido aprobadas y están listas para preparación o despacho.',
			what_is_it_en: 'Number of validated and credit-approved commercial purchase orders ready for warehouse picking or dispatch.',
			why_it_matters_es: 'Mide la actividad comercial real. Un pedido confirmado es un compromiso firme de compra que activa el trabajo en el almacén.',
			why_it_matters_en: 'Measures real commercial volume. Confirmed orders trigger warehouse picking and dispatch logistics.',
			formula_text_es: 'Conteo total de pedidos en estado "Confirmado" registrados durante el período',
			formula_text_en: 'Total count of orders in "Confirmed" status recorded during the period',
			formula_example_es: 'En el mes actual se registraron 184 pedidos comerciales confirmados por los ejecutivos de cuenta.',
			formula_example_en: 'In the current month, 184 confirmed commercial wholesale orders were booked.',
			interpretation_es: {
				favorable: 'Volumen creciente de pedidos con un tiempo de aprobación menor a 24 horas.',
				warning: 'Muchos pedidos acumulados en borrador o pendientes de aprobación crediticia.',
				critical: 'Disminución drástica de pedidos o cancelaciones reiteradas por falta de stock.'
			},
			roles_es: {
				employee: 'Priorizar el armado (picking) y embalaje de los pedidos con fecha de entrega más próxima.',
				supervisor: 'Monitorear la cola de pedidos pendientes de aprobación para que no se retrasen las entregas.',
				manager: 'Monitorear la tasa de conversión de cotizaciones a pedidos y la productividad del equipo de ventas.'
			},
			data_source_es: 'Tabla b2b_orders con estado = "Confirmado".'
		},

		active_customers: {
			key: 'active_customers',
			name_es: 'Clientes con Compras en el Período',
			name_en: 'Active Purchasing Clients',
			technical_name: 'Active Transacting Accounts',
			category: 'Ventas',
			what_is_it_es: 'Número de empresas, supermercados o cadenas que realizaron al menos una compra confirmada en el período analizado.',
			what_is_it_en: 'Number of commercial accounts that placed at least one confirmed order or invoice in the analyzed period.',
			why_it_matters_es: 'Permite saber qué porcentaje de nuestra cartera total nos está comprando actualmente y qué clientes están inactivos.',
			why_it_matters_en: 'Identifies buying accounts versus dormant accounts at risk of defection.',
			formula_text_es: 'Clientes únicos con al menos 1 factura o pedido confirmado en el período',
			formula_text_en: 'Distinct customer count with ≥1 invoice or order in the period',
			formula_example_es: 'De 32 clientes corporativos registrados, 24 realizaron compras este mes (75% de la cartera activa).',
			formula_example_en: 'Out of 32 registered corporate clients, 24 placed orders this month (75% active portfolio).',
			interpretation_es: {
				favorable: 'Más del 70% de la cartera comprando regularmente sin concentración excesiva en un solo cliente.',
				warning: 'Entre 50% y 70% de clientes activos; clientes habituales que llevan más de 20 días sin pedir.',
				critical: 'Menos del 50% de clientes activos o más del 60% de ventas concentradas en una sola cadena.'
			},
			roles_es: {
				employee: 'Identificar a los clientes frecuentes para atender sus solicitudes y despachos con máxima agilidad.',
				supervisor: 'Llamar a los clientes que compraron el mes pasado pero no han registrado órdenes este mes.',
				manager: 'Diseñar planes de reactivación comercial y evaluar la diversificación del riesgo de cartera.'
			},
			data_source_es: 'Registro único de clientes en pedidos confirmados durante el rango de fechas seleccionado.'
		},

		ticket: {
			key: 'ticket',
			name_es: 'Venta Promedio por Factura',
			name_en: 'Average Order Value (Ticket)',
			technical_name: 'Average Invoiced Ticket (AOV)',
			category: 'Ventas',
			what_is_it_es: 'Monto promedio en dinero que representa cada factura emitida a nuestros clientes mayoristas.',
			what_is_it_en: 'Average billed dollar amount per valid commercial wholesale invoice.',
			why_it_matters_es: 'Indica si los clientes están haciendo pedidos grandes y eficientes, o si compran en pequeños pedidos que encarecen el transporte.',
			why_it_matters_en: 'Determines order sizing efficiency and whether logistics costs per drop are optimized.',
			formula_text_es: 'Ventas Netas Totales ($) / Cantidad Total de Facturas Emitidas',
			formula_text_en: 'Total Net Sales ($) / Total Valid Invoices Issued',
			formula_example_es: 'Si vendimos $98.000 en 140 facturas, el ticket promedio es $98.000 / 140 = $700 por factura.',
			formula_example_en: 'If $98,000 was billed across 140 invoices, the average ticket is $98,000 / 140 = $700 per invoice.',
			interpretation_es: {
				favorable: 'Ticket promedio creciente que supera el costo mínimo de envío por camión.',
				warning: 'Ticket estable pero con muchos pedidos fragmentados que saturan el almacén.',
				critical: 'Ticket por debajo del pedido mínimo rentable (MOQ).'
			},
			roles_es: {
				employee: 'Consolidar productos de un mismo cliente en un solo bulto o palet para optimizar espacio.',
				supervisor: 'Incentivar promociones por volumen (escalonamiento de precios) para que el cliente pida pedidos completos.',
				manager: 'Ajustar la política de pedidos mínimos (MOQ) y flete gratis para proteger el margen operativo.'
			},
			data_source_es: 'Ventas netas divididas entre facturas válidas emitidas en el período.'
		},

		receivable: {
			key: 'receivable',
			name_es: 'Dinero Pendiente de Cobro',
			name_en: 'Accounts Receivable',
			technical_name: 'Outstanding Invoices & Aging Receivables',
			category: 'Finanzas',
			what_is_it_es: 'Suma de dinero de todas las facturas ya entregadas a clientes cuyo cobro aún no ha ingresado a nuestra cuenta bancaria.',
			what_is_it_en: 'Total outstanding monetary balance on issued invoices awaiting payment from wholesale clients.',
			why_it_matters_es: 'Es el capital de la empresa en manos de los clientes. Si cobramos a tiempo, tenemos liquidez para pagar sueldos y proveedores.',
			why_it_matters_en: 'Directly drives corporate cash flow, working capital, and supplier payment ability.',
			formula_text_es: 'Total Facturado − Pagos Recibidos y Notas de Crédito Aplicadas',
			formula_text_en: 'Total Invoiced − Cash Collected & Credit Notes Applied',
			formula_example_es: 'De $100.000 facturados se han cobrado $85.500; quedan $14.500 pendientes de cobro.',
			formula_example_en: 'From $100,000 billed, $85,500 has been collected, leaving $14,500 pending collection.',
			interpretation_es: {
				favorable: 'Más del 85% de las cuentas por cobrar están dentro del plazo acordado (corrientes).',
				warning: 'Facturas vencidas entre 1 y 30 días que superan el 15% del total de cartera.',
				critical: 'Deudas con más de 30 o 60 días de vencimiento sin promesa de pago.'
			},
			roles_es: {
				employee: 'Solicitar y adjuntar siempre la constancia de recepción firmada y sellada por el cliente.',
				supervisor: 'Enviar recordatorios de vencimiento 3 días antes y suspender crédito a clientes morosos.',
				manager: 'Negociar acuerdos de pago con cuentas clave y revisar límites de crédito por cliente.'
			},
			data_source_es: 'Saldos vivos de la tabla b2b_invoices deduciendo pagos amortizados.'
		},

		sales_variation: {
			key: 'sales_variation',
			name_es: 'Crecimiento frente al Período Anterior',
			name_en: 'Sales Variance',
			technical_name: 'Period-over-Period Revenue Growth (%)',
			category: 'Ventas',
			what_is_it_es: 'Porcentaje en que aumentaron o disminuyeron las ventas respecto al mes o período anterior comparable.',
			what_is_it_en: 'Percentage increase or decrease in sales compared to the equivalent prior period.',
			why_it_matters_es: 'Muestra la velocidad a la que se expande el negocio. Permite detectar caídas tempranas antes de que afecten el balance.',
			why_it_matters_en: 'Signals market traction and top-line momentum or early warning of market contraction.',
			formula_text_es: '((Ventas Período Actual − Ventas Período Anterior) / Ventas Período Anterior) × 100',
			formula_text_en: '((Current Sales − Prior Sales) / Prior Sales) × 100',
			formula_example_es: 'Si vendimos $98.000 este mes y el mes anterior vendimos $90.000: (($98.000 − $90.000) / $90.000) × 100 = +8.88%.',
			formula_example_en: 'If current sales are $98,000 vs $90,000 prior: (($98,000 − $90,000) / $90,000) × 100 = +8.88%.',
			interpretation_es: {
				favorable: 'Crecimiento positivo (+5% o más) respaldado por mayor volumen de unidades vendidas.',
				warning: 'Crecimiento plano (entre -2% y +2%) que requiere reactivación comercial.',
				critical: 'Caída de ventas superior al -5% respecto al período anterior.'
			},
			roles_es: {
				employee: 'Reportar inmediatamente si algún cliente clave reduce drásticamente el tamaño de sus pedidos habituales.',
				supervisor: 'Revisar qué categorías o vendedores explican el crecimiento o la caída.',
				manager: 'Ajustar la estrategia comercial, precios o campañas promocionales para revertir tendencias negativas.'
			},
			data_source_es: 'Comparación determinista entre el rango de fechas seleccionado y su período anterior idéntico.'
		},

		// --- CICLO DE VIDA DEL PRODUCTO (PLM) ---
		sell_through: {
			key: 'sell_through',
			name_es: 'Porcentaje de Ventas del Inventario',
			name_en: 'Sell-through Rate (%)',
			technical_name: 'Sell-Through Rate (STR)',
			category: 'Inventario',
			what_is_it_es: 'Indica qué porcentaje de la mercadería disponible para la venta se vendió efectivamente durante el mes o período.',
			what_is_it_en: 'Percentage of available stock for sale that was actually sold to customers during the period.',
			why_it_matters_es: 'Mide la rapidez con la que los clientes compran lo que trajimos al almacén. Un sell-through alto significa que trajimos productos que la gente realmente quiere.',
			why_it_matters_en: 'Validates purchasing accuracy and demand alignment. Prevents over-ordering low-demand SKUs.',
			formula_text_es: '(Unidades Vendidas / Unidades Disponibles para la Venta) × 100',
			formula_text_en: '(Units Sold / Units Available for Sale) × 100',
			formula_example_es: 'Si teníamos 100 cajas en almacén y recibimos 20 del proveedor (120 en total), y vendimos 60 cajas: (60 / 120) × 100 = 50.0%.',
			formula_example_en: 'If 100 units were in stock + 20 received (120 available), and 60 were sold: (60 / 120) × 100 = 50.0%.',
			interpretation_es: {
				favorable: 'Mayor al 50%: la mercadería se absorbe rápido y el capital rota saludablemente.',
				warning: 'Entre 30% y 50%: venta moderada; conviene no comprar lotes excesivos.',
				critical: 'Menor al 25%: el producto se está quedando estancado en el almacén.'
			},
			roles_es: {
				employee: 'Asegurar que los productos con alto sell-through estén ubicados en las zonas de acceso rápido del almacén.',
				supervisor: 'Revisar la reposición de productos con sell-through > 60% para que no se agoten.',
				manager: 'Planear promociones o descuentos para productos con sell-through < 25% para recuperar capital.'
			},
			data_source_es: 'Transacciones de venta versus stock inicial y entradas de almacén registradas en el período.'
		},

		turnover: {
			key: 'turnover',
			name_es: 'Renovación de Inventario (veces al año)',
			name_en: 'Inventory Turnover',
			technical_name: 'Inventory Turnover Ratio (COGS / Average Inventory)',
			category: 'Inventario',
			what_is_it_es: 'Cuántas veces al año la empresa vacía y vuelve a llenar su almacén vendiendo sus productos a precio de costo.',
			what_is_it_en: 'How many times per year the total inventory is completely sold and replaced.',
			why_it_matters_es: 'Cuantas más veces renueve el inventario, más dinero gana la empresa con la misma inversión. Mucho stock guardado durante meses cuesta dinero.',
			why_it_matters_en: 'Drives capital efficiency and minimizes holding costs, spoilage, and obsolescence.',
			formula_text_es: 'Costo de Mercadería Vendida (anualizado) / Inventario Promedio a Costo',
			formula_text_en: 'Cost of Goods Sold (Annualized) / Average Inventory at Cost',
			formula_example_es: 'Si vendemos mercadería por un costo de $120.000 al año y mantenemos en promedio $80.000 en stock: $120.000 / $80.000 = 1.5 veces al año.',
			formula_example_en: 'If annualized COGS is $120,000 and average inventory is $80,000: $120,000 / $80,000 = 1.5x.',
			interpretation_es: {
				favorable: 'Mayor a 1.25x en productos secos o mayor a 4x en perecederos: rotación ágil.',
				warning: 'Entre 0.8x y 1.2x: rotación lenta; hay dinero inmovilizado innecesariamente.',
				critical: 'Menor a 0.8x: inventario sobredimensionado con alto riesgo de vencimiento o deterioro.'
			},
			roles_es: {
				employee: 'Controlar las fechas de vencimiento (PEPS/FIFO: lo primero que entra es lo primero que sale).',
				supervisor: 'Ajustar la frecuencia de compra: comprar lotes más pequeños y frecuentes.',
				manager: 'Reducir el capital inmovilizado en productos de rotación lenta para reinvertir en los productos estrella.'
			},
			data_source_es: 'Costo histórico de ventas dividido entre el valor de existencias actualizadas en el almacén.'
		},

		coverage_dio: {
			key: 'coverage_dio',
			name_es: 'Días de Inventario Disponible',
			name_en: 'Days of Inventory Coverage (DIO)',
			technical_name: 'Days of Inventory Outstanding (DIO)',
			category: 'Inventario',
			what_is_it_es: 'Si no compramos más mercadería hoy, ¿para cuántos días de venta nos alcanza el stock que tenemos actualmente guardado?',
			what_is_it_en: 'Number of sales days the current inventory can support before running out.',
			why_it_matters_es: 'Evita dos peligros graves: tener demasiado stock (capital congelado) o tener muy poco (quedarse sin producto y perder clientes).',
			why_it_matters_en: 'Balances working capital against the risk of costly stockouts.',
			formula_text_es: 'Unidades Disponibles en Almacén / Venta Promedio Diaria Esperada',
			formula_text_en: 'Available Warehouse Units / Expected Daily Sales Demand',
			formula_example_es: 'Si tenemos 600 unidades en almacén y se venden 25 unidades por día: 600 / 25 = 24 días de cobertura.',
			formula_example_en: 'If 600 units are in warehouse and 25 sell per day: 600 / 25 = 24 days of coverage.',
			interpretation_es: {
				favorable: 'Entre 20 y 35 días: cobertura equilibrada que cubre el tiempo del proveedor sin sobrecargar el almacén.',
				warning: 'Menor a 10 días (riesgo inminente de quiebre) o entre 45 y 60 días (exceso de stock).',
				critical: 'Menor a 5 días (se agotará esta semana) o mayor a 90 días (inventario hipertrofiado).'
			},
			roles_es: {
				employee: 'Avisar de inmediato cuando queden pocas cajas en el rack de almacenamiento para activar reposición.',
				supervisor: 'Revisar los tiempos de entrega del proveedor (Lead Time) y emitir pedidos cuando la cobertura baje del punto de reorden.',
				manager: 'Autorizar pedidos de emergencia o transferencias entre sucursales para evitar quiebres.'
			},
			data_source_es: 'Existencias físicas disponibles divididas entre la demanda proyectada por el modelo Holt-Winters.'
		},

		gmroi: {
			key: 'gmroi',
			name_es: 'Ganancia por Dólar Invertido en Stock',
			name_en: 'Gross Margin Return on Inventory (GMROI)',
			technical_name: 'Gross Margin Return on Investment (GMROI)',
			category: 'Finanzas',
			what_is_it_es: 'Por cada dólar que la empresa tiene invertido en mercadería en el almacén, cuántos dólares de ganancia bruta obtiene al venderla.',
			what_is_it_en: 'Monetary gross profit generated for every dollar invested in inventory cost.',
			why_it_matters_es: 'Une dos factores cruciales: el margen de ganancia y la velocidad de venta. Un producto con poco margen pero que rota rápido puede dar más dinero que uno caro que nunca se vende.',
			why_it_matters_en: 'Combines profitability and inventory velocity into one decisive retail efficiency metric.',
			formula_text_es: 'Ganancia Bruta Total en Dólares / Inventario Promedio a Costo',
			formula_text_en: 'Total Gross Profit ($) / Average Inventory at Cost ($)',
			formula_example_es: 'Si un producto generó $31.500 de ganancia bruta y mantuvimos $10.000 inmovilizados en su stock: $31.500 / $10.000 = $3.15 de retorno por dólar.',
			formula_example_en: 'If product yielded $31,500 gross margin on $10,000 average inventory: $31,500 / $10,000 = $3.15 GMROI.',
			interpretation_es: {
				favorable: 'Mayor a $2.50: producto altamente rentable y eficiente para el negocio.',
				warning: 'Entre $1.20 y $2.50: producto que genera ganancias moderadas.',
				critical: 'Menor a $1.00: el producto destruye valor; cuesta más mantenerlo en stock de lo que deja en ganancia.'
			},
			roles_es: {
				employee: 'Cuidar con esmero los productos con alto GMROI para evitar roturas o mermas.',
				supervisor: 'Asignar mejor espacio y exhibición a los productos con GMROI > $2.50.',
				manager: 'Descatalogar o sustituir productos con GMROI < $1.00 sostenido por más de 3 meses.'
			},
			data_source_es: 'Margen bruto acumulado dividido entre el inventario promedio a costo en el período.'
		},

		gmros: {
			key: 'gmros',
			name_es: 'Ganancia por Metro Cuadrado de Espacio',
			name_en: 'Gross Margin Return on Space (GMROS)',
			technical_name: 'Gross Margin Return on Space (GMROS per m²)',
			category: 'Finanzas',
			what_is_it_es: 'Cuánto dinero de ganancia genera cada metro cuadrado del almacén o góndola física donde está guardado o exhibido el producto.',
			what_is_it_en: 'Gross margin generated per square meter of allocated retail shelf or warehouse storage space.',
			why_it_matters_es: 'El espacio físico del almacén y de las tiendas cuesta dinero (alquiler, luz, mantenimiento). Permite aprovechar cada metro al máximo.',
			why_it_matters_en: 'Optimizes commercial space allocation and warehouse slotting profitability.',
			formula_text_es: 'Ganancia Bruta Total ($) / Metros Cuadrados Asignados (m²)',
			formula_text_en: 'Total Gross Profit ($) / Allocated Floor/Rack Space (m²)',
			formula_example_es: 'Si la categoría de Lácteos dejó $35.000 de ganancia y ocupa 85 m² de almacén refrigerado: $35.000 / 85 = $411.76 por m².',
			formula_example_en: 'If Dairy generated $35,000 margin occupying 85 m²: $35,000 / 85 = $411.76 per m².',
			interpretation_es: {
				favorable: 'Mayor a $300/m²: uso altamente productivo del espacio físico.',
				warning: 'Entre $150/m² y $300/m²: rendimiento estándar aceptable.',
				critical: 'Menor a $150/m²: mercadería voluminosa que deja muy poco beneficio por el espacio que ocupa.'
			},
			roles_es: {
				employee: 'Mantener los pasillos y racks despejados y respetar las ubicaciones asignadas por sistema.',
				supervisor: 'Reubicar productos voluminosos de bajo margen a zonas secundarias del almacén.',
				manager: 'Reasignar metros cuadrados de exhibición hacia las líneas de producto con mayor GMROS.'
			},
			data_source_es: 'Margen comercial dividido entre la superficie en metros cuadrados asignada en la base de datos.'
		},

		growth_skus: {
			key: 'growth_skus',
			name_es: 'Productos con Ventas en Aumento',
			name_en: 'Growing Products (%)',
			technical_name: 'Growth Stage SKU Percentage',
			category: 'Inventario',
			what_is_it_es: 'Porcentaje de productos del catálogo cuyas ventas están creciendo de forma constante y acelerada (más de un 10% en las últimas semanas).',
			what_is_it_en: 'Percentage of SKUs showing sustained sales demand growth exceeding +10%.',
			why_it_matters_es: 'Son el motor del futuro de la empresa. Requieren atención para no quedarse sin stock cuando la demanda está subiendo.',
			why_it_matters_en: 'Drives business expansion and identifies future bestsellers that need proactive replenishment.',
			formula_text_es: '(Cantidad de SKUs en Etapa de Crecimiento / Total SKUs Analizados) × 100',
			formula_text_en: '(Growth SKUs Count / Total Analyzed SKUs) × 100',
			formula_example_es: 'De 16 productos analizados, 3 tienen ventas en fuerte crecimiento: (3 / 16) × 100 = 18.8%.',
			formula_example_en: 'Out of 16 analyzed products, 3 exhibit strong growth: (3 / 16) × 100 = 18.8%.',
			interpretation_es: {
				favorable: 'Entre 15% y 30%: portafolio dinámico y saludable con nuevos éxitos comerciales.',
				warning: 'Menor a 10%: la empresa está dependiendo de productos maduros sin renovación.',
				critical: '0%: no hay productos ganando tracción en el mercado.'
			},
			roles_es: {
				employee: 'Identificar los productos en crecimiento para que nunca falten en las zonas de picking rápido.',
				supervisor: 'Incrementar el stock de seguridad y adelantar órdenes de compra a proveedores.',
				manager: 'Negociar mejores descuentos por volumen con los fabricantes de estos productos.'
			},
			data_source_es: 'Clasificación automática por tendencia positiva sostenida en el servicio de ciclo de vida.'
		},

		maturity_skus: {
			key: 'maturity_skus',
			name_es: 'Productos con Ventas Consolidadas',
			name_en: 'Mature Products (%)',
			technical_name: 'Maturity Stage SKU Percentage',
			category: 'Inventario',
			what_is_it_es: 'Porcentaje de productos favoritos y tradicionales cuyas ventas son estables, predecibles y sin grandes sobresaltos.',
			what_is_it_en: 'Percentage of established products with steady, predictable sales volume.',
			why_it_matters_es: 'Son las "vacas lecheras" del negocio: pagan los sueldos y los costos fijos gracias a su venta segura día a día.',
			why_it_matters_en: 'Provides consistent operational cash flow and dependable baseline revenues.',
			formula_text_es: '(Cantidad de SKUs en Madurez / Total SKUs Analizados) × 100',
			formula_text_en: '(Maturity SKUs Count / Total Analyzed SKUs) × 100',
			formula_example_es: 'De 16 productos, 6 están en madurez con ventas constantes: (6 / 16) × 100 = 37.5%.',
			formula_example_en: 'Out of 16 products, 6 are in maturity: (6 / 16) × 100 = 37.5%.',
			interpretation_es: {
				favorable: 'Entre 35% y 55%: base de ventas sólida y predecible.',
				warning: 'Más del 70%: catálogo envejecido con riesgo de volverse obsoleto si los gustos cambian.',
				critical: 'Menor al 20%: cartera inestable con ventas muy difíciles de predecir.'
			},
			roles_es: {
				employee: 'Mantener las existencias organizadas y conteos cíclicos al día en estos productos clave.',
				supervisor: 'Automatizar las órdenes de reposición basadas en el punto de reorden (ROP).',
				manager: 'Proteger la relación comercial y precios competitivos con los clientes que compran estos SKUs.'
			},
			data_source_es: 'Clasificación algorítmica por estabilidad de ventas y varianza menor al 15%.'
		},

		decline_skus: {
			key: 'decline_skus',
			name_es: 'Productos que están Perdiendo Ventas',
			name_en: 'Declining Products (%)',
			technical_name: 'Decline Stage SKU Percentage',
			category: 'Inventario',
			what_is_it_es: 'Porcentaje de productos cuya demanda viene cayendo de manera sostenida durante varias semanas consecutivas.',
			what_is_it_en: 'Percentage of products showing consistent, sustained reduction in customer demand.',
			why_it_matters_es: 'Alerta a tiempo para no seguir comprando unidades que terminarán pudriéndose o acumulando polvo en el almacén.',
			why_it_matters_en: 'Prevents wasteful reordering and prompts timely promotional clearance.',
			formula_text_es: '(Cantidad de SKUs en Declive / Total SKUs Analizados) × 100',
			formula_text_en: '(Decline SKUs Count / Total Analyzed SKUs) × 100',
			formula_example_es: 'De 16 productos, 2 están perdiendo ventas sostenidamente: (2 / 16) × 100 = 12.5%.',
			formula_example_en: 'Out of 16 products, 2 are declining: (2 / 16) × 100 = 12.5%.',
			interpretation_es: {
				favorable: 'Menor al 15%: nivel normal de recambio natural del mercado.',
				warning: 'Entre 15% y 25%: varios productos perdiendo interés del cliente.',
				critical: 'Mayor al 30%: pérdida severa de competitividad en varias líneas de producto.'
			},
			roles_es: {
				employee: 'Identificar lotes antiguos de estos productos para ofrecerlos con prioridad antes de que venzan.',
				supervisor: 'Suspender inmediatamente las compras automáticas de estos SKUs.',
				manager: 'Utilizar el Simulador de Descuentos para liquidar el stock remanente y sustituirlos por novedades.'
			},
			data_source_es: 'Detección de pendiente negativa sostenida de demanda ajustada por disponibilidad de stock.'
		},

		immobilized_capital: {
			key: 'immobilized_capital',
			name_es: 'Dinero Detenido en Stock sin Venta',
			name_en: 'Immobilized Capital ($)',
			technical_name: 'Immobilized / Dead Stock at Cost Value',
			category: 'Inventario',
			what_is_it_es: 'Monto total en dólares que la empresa tiene invertido en productos que llevan más de 60 o 90 días sin venderse.',
			what_is_it_en: 'Total dollar value of inventory tied up in slow-moving or non-selling merchandise (>60/90 days).',
			why_it_matters_es: 'Es dinero que la empresa no puede usar para pagar sueldos, comprar mercadería nueva o invertir. Cada día que pasa en el almacén pierde valor.',
			why_it_matters_en: 'Represents trapped liquidity, holding costs, and impending write-off risk.',
			formula_text_es: 'Suma de (Unidades sin rotación en >60 días × Costo Unitario de Compra)',
			formula_text_en: 'Sum of (Unsold units past 60 days × Unit Purchase Cost)',
			formula_example_es: 'Si tenemos 200 cajas de galletas a $15 de costo sin venta en 90 días, tenemos $3.000 inmovilizados.',
			formula_example_en: 'If 200 boxes costing $15 each have not moved in 90 days: $3,000 immobilized.',
			interpretation_es: {
				favorable: 'Menor al 5% del valor total del almacén: inventario limpio y en constante movimiento.',
				warning: 'Entre 5% y 15%: capital congelado que requiere promociones o liquidaciones puntuales.',
				critical: 'Mayor al 15%: riesgo financiero grave por acumulación de mercancía obsoleta.'
			},
			roles_es: {
				employee: 'Revisar físicamente el estado de las cajas y reportar si tienen empaques dañados o polvo.',
				supervisor: 'Revisar la lista de productos estancados y armar combos o promociones 2x1.',
				manager: 'Aprobar descuentos de liquidación progresivos (10%, 20%, 30%) para recuperar el dinero en efectivo.'
			},
			data_source_es: 'Valoración a costo de los lotes clasificados en franjas de 60-90d, 91-180d y >180d.'
		},

		stockout_risk: {
			key: 'stockout_risk',
			name_es: 'Riesgo de Quedarse sin Stock',
			name_en: 'Stockout Risk Rate (%)',
			technical_name: 'Stockout / Out-of-Stock Risk Percentage',
			category: 'Inventario',
			what_is_it_es: 'Porcentaje de productos del catálogo cuyas existencias actuales se agotarán antes de que llegue el siguiente pedido del proveedor.',
			what_is_it_en: 'Percentage of products facing stock exhaustion before supplier replenishment arrives.',
			why_it_matters_es: 'Un quiebre de stock significa ventas perdidas inmediatamente: si el cliente nos pide leche y no tenemos, se la comprará a la competencia.',
			why_it_matters_en: 'A stockout means direct lost sales and damaged wholesale customer trust.',
			formula_text_es: '(Productos con Cobertura < Tiempo de Entrega del Proveedor / Total Productos) × 100',
			formula_text_en: '(Products with Coverage < Lead Time / Total Products) × 100',
			formula_example_es: 'Si de 16 productos, 2 tienen menos días de stock de los que tarda el camión en llegar: (2 / 16) × 100 = 12.5% de riesgo.',
			formula_example_en: 'If 2 out of 16 products will run out before supplier delivery: (2 / 16) × 100 = 12.5% risk.',
			interpretation_es: {
				favorable: 'Menor al 5%: abastecimiento puntual y stock de seguridad bien calibrado.',
				warning: 'Entre 5% y 15%: varios productos clave al borde del agotamiento.',
				critical: 'Mayor al 20%: crisis logística con ventas perdidas aseguradas esta semana.'
			},
			roles_es: {
				employee: 'Verificar si realmente no hay stock físico en el rack o si el producto estaba mal ubicado.',
				supervisor: 'Llamar al proveedor para agilizar el despacho o pedir transferencia urgente desde Broward o Miami.',
				manager: 'Autorizar compras prioritarias y evaluar proveedores alternativos con menor tiempo de entrega.'
			},
			data_source_es: 'Cálculo algorítmico comparando Cobertura en Días (DIO) versus Tiempo de Reposición (Lead Time).'
		},

		net_margin_post_discounts: {
			key: 'net_margin_post_discounts',
			name_es: 'Margen Real tras Descuentos',
			name_en: 'Margin Post-Discounts (%)',
			technical_name: 'Net Realized Gross Margin Post-Discounts & Rebates',
			category: 'Finanzas',
			what_is_it_es: 'Porcentaje de ganancia que realmente le queda a la empresa tras descontar todas las rebajas, bonificaciones por volumen y promociones comerciales.',
			what_is_it_en: 'Percentage of revenue retained as gross profit after accounting for all price cuts, rebates, and volume discounts.',
			why_it_matters_es: 'Muchos negocios venden mucho pero ganan poco porque dan demasiados descuentos. Este indicador muestra la rentabilidad real de bolsillo.',
			why_it_matters_en: 'Reveals true operational profitability beneath deceptive top-line sales figures.',
			formula_text_es: '((Ventas Netas tras Descuentos − Costo de Mercadería Vendida) / Ventas Netas tras Descuentos) × 100',
			formula_text_en: '((Net Sales Post-Discounts − COGS) / Net Sales Post-Discounts) × 100',
			formula_example_es: 'Si vendemos un lote por $100 con $10 de descuento (ingresan $90) y nos costó $71.60: (($90 − $71.60) / $90) × 100 = 20.44% de margen real.',
			formula_example_en: 'If product sells at $100 with $10 discount ($90 net) with $71.60 COGS: (($90 − $71.60) / $90) × 100 = 20.44%.',
			interpretation_es: {
				favorable: 'Mayor al 22%: margen comercial saludable y protegido.',
				warning: 'Entre 15% y 22%: margen ajustado; evaluar si los descuentos otorgados están justificados.',
				critical: 'Menor al 15%: margen peligroso que apenas cubre los gastos operativos de almacenamiento y transporte.'
			},
			roles_es: {
				employee: 'No aplicar rebajas manuales ni duplicar promociones sin autorización del sistema.',
				supervisor: 'Revisar la política de descuentos de los vendedores que otorgan rebajas excesivas.',
				manager: 'Fijar techos máximos de descuento por cliente y renegociar costos de adquisición con proveedores.'
			},
			data_source_es: 'Ventas netas deducidas de costo de mercancía vendida en el módulo financiero de Supabase.'
		},

		forecast_accuracy: {
			key: 'forecast_accuracy',
			name_es: 'Exactitud de la Estimación de Ventas',
			name_en: 'Forecast Accuracy (%)',
			technical_name: 'Demand Forecast Accuracy (100% − MAPE)',
			category: 'Inventario',
			what_is_it_es: 'Qué tan acertada fue la estimación de ventas que hizo el sistema en comparación con lo que realmente compraron los clientes.',
			what_is_it_en: 'Accuracy percentage of mathematical demand predictions compared to actual sales demand.',
			why_it_matters_es: 'Un pronóstico certero permite comprar la cantidad exacta de mercadería: ni de más (evita dinero congelado) ni de menos (evita quiebres de stock).',
			why_it_matters_en: 'Optimizes procurement budgets, reduces emergency rush orders, and balances inventory holding.',
			formula_text_es: '100% − Error Porcentual Absoluto Medio (MAPE)',
			formula_text_en: '100% − Mean Absolute Percentage Error (MAPE)',
			formula_example_es: 'Si el sistema calculó vender 100 unidades y se vendieron 92, el error fue del 8%, lo que significa una precisión del 92.0%.',
			formula_example_en: 'If forecast predicted 100 units and 92 sold, error is 8%, yielding 92.0% forecast accuracy.',
			interpretation_es: {
				favorable: 'Mayor al 85%: pronóstico altamente confiable para compras automáticas.',
				warning: 'Entre 70% y 85%: precisión moderada; conviene revisar la estacionalidad del producto.',
				critical: 'Menor al 70%: ventas erráticas o falta de historial suficiente; requiere supervisión humana.'
			},
			roles_es: {
				employee: 'Reportar cuando un cliente comente que planea un evento o compra inusualmente grande el próximo mes.',
				supervisor: 'Ajustar los modelos de estacionalidad (promedio móvil vs Holt-Winters) según la categoría.',
				manager: 'Utilizar el pronóstico para planificar el flujo de caja de compras del próximo trimestre con confianza.'
			},
			data_source_es: 'Comparación algorítmica entre la serie real de demanda y las predicciones Holt-Winters calculadas.'
		},

		// --- TÉRMINOS LOGÍSTICOS & RETAIL GENERALES ---
		otif: {
			key: 'otif',
			name_es: 'Entregas a Tiempo y Completas (OTIF)',
			name_en: 'On-Time In-Full (OTIF)',
			technical_name: 'On-Time In-Full Delivery Service Level',
			category: 'Logistica',
			what_is_it_es: 'Porcentaje de pedidos entregados al cliente en la fecha pactada y con exactamente todos los productos solicitados sin faltantes.',
			what_is_it_en: 'Percentage of customer wholesale orders delivered strictly on schedule and with 100% complete items.',
			why_it_matters_es: 'Es el indicador de oro del servicio mayorista. Las grandes cadenas como Walmart o Sedano\'s multan a los proveedores que no cumplen su OTIF.',
			why_it_matters_en: 'Primary benchmark for B2B supplier reliability, preventing retailer penalties and SLA chargebacks.',
			formula_text_es: '(Pedidos Entregados a Tiempo y Completos / Total de Pedidos Despachados) × 100',
			formula_text_en: '(Orders Delivered On-Time and In-Full / Total Dispatched Orders) × 100',
			formula_example_es: 'De 100 pedidos despachados, 96 llegaron a la hora prometida y con todas sus cajas completas: OTIF = 96.0%.',
			formula_example_en: 'Out of 100 orders dispatched, 96 arrived on schedule with zero missing items: OTIF = 96.0%.',
			interpretation_es: {
				favorable: 'Mayor al 95%: estándar de excelencia mayorista.',
				warning: 'Entre 88% y 95%: retrasos frecuentes en preparación o transporte.',
				critical: 'Menor al 88%: riesgo inminente de perder contratos con cadenas de supermercados.'
			},
			roles_es: {
				employee: 'Verificar cada ítem de la lista antes de cerrar la caja y cargar el camión.',
				supervisor: 'Programar los turnos del equipo de picking según las horas de salida de las rutas de entrega.',
				manager: 'Evaluar acuerdos con empresas de transporte y tiempos de preparación en almacén.'
			},
			data_source_es: 'Conteo de pedidos con fecha de entrega real menor o igual a fecha pactada y 0 faltantes.'
		},

		moq: {
			key: 'moq',
			name_es: 'Pedido Mínimo por Orden (MOQ)',
			name_en: 'Minimum Order Quantity (MOQ)',
			technical_name: 'Minimum Order Quantity (MOQ)',
			category: 'Ventas',
			what_is_it_es: 'Cantidad mínima de unidades o cajas que un cliente debe comprar obligatoriamente para acceder a un precio mayorista.',
			what_is_it_en: 'Smallest order size or case count a wholesaler agrees to sell at contract prices.',
			why_it_matters_es: 'Evita que la empresa pierda dinero enviando un camión para entregar una sola caja de producto.',
			why_it_matters_en: 'Protects logistics operating margin by ensuring order drops meet break-even delivery thresholds.',
			formula_text_es: 'Establecido por regla de catálogo según la presentación (ej: mínimo 5 cajas o 1 palet completo)',
			formula_text_en: 'Configured business rule per SKU based on case pack or pallet size',
			formula_example_es: 'El Aceite de Oliva 5L tiene un MOQ de 10 cajas por pedido mayorista.',
			formula_example_en: 'Olive Oil 5L carries an MOQ of 10 cases per wholesale purchase order.',
			interpretation_es: {
				favorable: 'MOQ alineado con el empaque del fabricante (cajas cerradas sin fraccionar).',
				warning: 'Clientes que solicitan excepciones frecuentes para comprar menos del mínimo.',
				critical: 'Venta de unidades sueltas que generan pérdidas por manipulación y transporte.'
			},
			roles_es: {
				employee: 'Despachar únicamente cajas selladas de fábrica para evitar daños y errores de conteo.',
				supervisor: 'Configurar en el sistema las alertas para que los vendedores no puedan ingresar pedidos bajo el MOQ sin autorización.',
				manager: 'Revisar si los MOQ actuales son atractivos para tiendas pequeñas o si deben crearse escalonamientos.'
			},
			data_source_es: 'Parámetro definido en el maestro de productos b2b_products.'
		},

		cogs: {
			key: 'cogs',
			name_es: 'Costo de Mercadería Vendida',
			name_en: 'Cost of Goods Sold (COGS)',
			technical_name: 'Cost of Goods Sold (COGS)',
			category: 'Finanzas',
			what_is_it_es: 'Cuánto le costó a PRINCES comprarle al fabricante los productos que luego le vendió a los clientes.',
			what_is_it_en: 'Direct costs attributable to the production or procurement of goods sold by the company.',
			why_it_matters_es: 'Es el gasto directo más grande de una distribuidora mayorista. Si compramos caro, nos queda muy poco margen de ganancia.',
			why_it_matters_en: 'Largest operational cost baseline; determines bottom-line gross margin sustainability.',
			formula_text_es: 'Unidades Vendidas × Costo Unitario de Compra (promedio ponderado)',
			formula_text_en: 'Units Sold × Weighted Average Unit Purchase Cost',
			formula_example_es: 'Si vendimos 500 botellas de vino a $20 cada una ($10.000 venta) y las compramos al viñedo a $12 cada una, el costo es $6.000.',
			formula_example_en: 'If 500 bottles were sold at $20 ($10,000 sales) and purchased at $12, COGS is $6,000.',
			interpretation_es: {
				favorable: 'Costo estable o a la baja gracias a negociaciones de compras por volumen.',
				warning: 'Subidas de costo por inflación del proveedor que no se han trasladado a la lista de precios.',
				critical: 'Costo mayor al precio de venta por promociones mal calculadas.'
			},
			roles_es: {
				employee: 'Registrar con precisión las facturas del proveedor al momento de recibir la mercadería en muelle.',
				supervisor: 'Verificar que el precio facturado por el proveedor coincida exactamente con la orden de compra.',
				manager: 'Renegociar contratos con fabricantes y buscar fuentes alternativas de abastecimiento.'
			},
			data_source_es: 'Costo promedio ponderado calculado en las transacciones de entrada a almacén.'
		}
	};

	// DICCIONARIO DE GUÍA PARA GRÁFICOS
	const b2bChartsHelpData = {
		sales_trend: {
			title: 'Evolución de Facturación & Margen B2B',
			what: 'Muestra el comportamiento diario de las ventas brutas en dólares y el porcentaje de margen obtenido en cada jornada.',
			axes: '<strong>Eje Horizontal (X):</strong> Días del mes analizado.<br><strong>Barras Azules (Eje Y Izq):</strong> Dólares facturados en el día.<br><strong>Línea Verde (Eje Y Der):</strong> Porcentaje de margen comercial alcanzado.',
			patterns: 'Observe si en los días con mayores picos de venta el margen se mantiene estable o si cae bruscamente (lo que indicaría que se dieron descuentos excesivos para cerrar volumen).',
			decisions: 'Identificar días de la semana con menor actividad para lanzar promociones especiales y evitar días muertos de despacho.'
		},
		sales_by_channel: {
			title: 'Ventas por Canal de Distribución',
			what: 'Muestra qué porcentaje de los ingresos proviene de Supermercados, Distribuidores Mayoristas, Tiendas Gourmet o Restaurantes.',
			axes: '<strong>Gráfico de Torta / Anillo:</strong> Cada color representa un canal de venta y su porcentaje del total facturado.',
			patterns: 'Compruebe si el negocio depende demasiado de un solo canal (ej: 80% en supermercados) o si la cartera está diversificada.',
			decisions: 'Fortalecer la fuerza de ventas en los canales más rentables y equilibrar la dependencia comercial.'
		},
		lifecycle_distribution: {
			title: 'Distribución del Portafolio por Etapas',
			what: 'Muestra la proporción de productos de su catálogo divididos en las 5 fases: Introducción, Crecimiento, Madurez, Declive y Salida.',
			axes: '<strong>Barras apiladas:</strong> Cada segmento representa una etapa del ciclo de vida y el porcentaje de productos que se encuentran en ella.',
			patterns: 'Un negocio sano debe tener una base sólida de Madurez (35-50%), un bloque activo de Crecimiento (15-25%) y muy pocos productos en Declive o Salida (<15%).',
			decisions: 'Si hay muchos productos en Declive, debe activarse la sustitución por novedades y aplicar descuentos antes de que se vuelvan obsoletos.'
		},
		lifecycle_curve: {
			title: 'Curva del Ciclo de Vida del Producto',
			what: 'Muestra la trayectoria histórica de ventas de los productos a lo largo del tiempo, simulando la curva canónica del ciclo de vida.',
			axes: '<strong>Eje X:</strong> Tiempo (meses desde su lanzamiento).<br><strong>Eje Y:</strong> Volumen de ventas promedio en unidades.',
			patterns: 'Permite identificar si los productos nuevos están logrando la pendiente ascendente esperada o si se han estancado en la fase de introducción.',
			decisions: 'Revisar la exhibición y precios de los productos nuevos que no despegan tras 60 días de lanzamiento.'
		},
		bcg_matrix: {
			title: 'Matriz de Rentabilidad y Rotación (BCG Retail)',
			what: 'Ubica cada producto en un mapa de cuatro cuadrantes según su velocidad de venta y su margen de ganancia.',
			axes: '<strong>Eje Horizontal (X):</strong> Rotación de inventario (veces al año).<br><strong>Eje Vertical (Y):</strong> Margen bruto (%).<br><strong>Tamaño del círculo:</strong> Valor del stock en almacén.<br><strong>Color:</strong> Nivel de riesgo (Verde = Seguro, Amarillo = Alerta, Rojo = Crítico).',
			patterns: 'Los productos en el cuadrante superior derecho son las "Estrellas" (alta rotación y alto margen). Los del cuadrante inferior izquierdo son "Perros" o productos lastre (lenta rotación y bajo margen).',
			decisions: 'Priorizar inventario y mejor exhibición para los productos Estrella, y evaluar descontinuar o liquidar los productos del cuadrante inferior izquierdo.'
		},
		immobilized_aging: {
			title: 'Antigüedad de Capital Inmovilizado',
			what: 'Visualiza cuánto dinero en mercadería está detenido en el almacén dividido en franjas de alerta de tiempo: 60 a 90 días, 91 a 180 días y más de 180 días sin movimiento.',
			axes: '<strong>Barras horizontales:</strong> Franjas de tiempo en días sin venta frente a los dólares inmovilizados a costo.',
			patterns: 'La franja roja (>180 días) es dinero en peligro inminente de pérdida total por obsolescencia o caducidad.',
			decisions: 'Aplicar el Simulador de Descuentos para liquidar con rebajas agresivas el lote de >180 días y transferir productos de 60-90 días a tiendas con mayor rotación.'
		},
		demand_trend: {
			title: 'Tendencia de Demanda Real vs. Pronóstico',
			what: 'Compara la venta histórica real de los productos frente a la estimación matemática de ventas futuras calculada por el modelo Holt-Winters.',
			axes: '<strong>Línea Azul:</strong> Demanda histórica real registrada.<br><strong>Línea Verde Punteada:</strong> Pronóstico estadístico de ventas futuras.<br><strong>Línea Roja:</strong> Punto de Reorden (ROP), nivel mínimo de stock donde debe emitirse una nueva orden de compra.',
			patterns: 'Cuando la línea azul cruza por debajo de la línea roja, significa que estamos consumiendo el stock de seguridad y debemos comprar inmediatamente.',
			decisions: 'Aprobar sugerencias de reposición antes de que las existencias caigan por debajo del nivel de seguridad.'
		},
		commercial_opportunities: {
			title: 'Oportunidades Comerciales de Rentabilidad',
			what: 'Suma de los beneficios económicos estimados si se ejecutan las recomendaciones del motor de inteligencia (recuperación de capital detenido, prevención de quiebres y compras sugeridas).',
			axes: '<strong>Tarjetas de impacto:</strong> Valor estimado en dólares de dinero a recuperar o ventas a proteger.',
			patterns: 'Muestra el impacto monetario cuantificado de tomar decisiones basadas en datos versus no actuar.',
			decisions: 'Revisar la pestaña de Recomendaciones para autorizar las acciones de mayor impacto económico primero.'
		}
	};

	// CONTROLADORES DE APERTURA Y CIERRE DE MODALES
	function openKpiHelp(metricKey) {
		const data = b2bGlossaryData[metricKey];
		if (!data) {
			console.warn('No hay datos en glosario para la métrica:', metricKey);
			return;
		}

		currentHelpMetricKey = metricKey;

		// Asignar textos
		const lang = localStorage.getItem('b2b_lang') || 'es';
		document.getElementById('kpi-help-modal-title').textContent = lang === 'en' ? data.name_en : data.name_es;
		document.getElementById('kpi-help-tech-badge').textContent = data.technical_name;
		document.getElementById('kpi-help-category').textContent = 'Categoría: ' + data.category;
		document.getElementById('kpi-help-what').textContent = lang === 'en' ? data.what_is_it_en : data.what_is_it_es;
		document.getElementById('kpi-help-why').textContent = lang === 'en' ? data.why_it_matters_en : data.why_it_matters_es;
		document.getElementById('kpi-help-formula').textContent = lang === 'en' ? data.formula_text_en : data.formula_text_es;
		document.getElementById('kpi-help-example').innerHTML = '<strong>' + (lang === 'en' ? 'Numerical Example:' : 'Ejemplo numérico con datos reales:') + '</strong> ' + (lang === 'en' ? data.formula_example_en : data.formula_example_es);
		
		const interp = data.interpretation_es;
		document.getElementById('kpi-help-interp-favorable').textContent = interp.favorable;
		document.getElementById('kpi-help-interp-warning').textContent = interp.warning;
		document.getElementById('kpi-help-interp-critical').textContent = interp.critical;

		document.getElementById('kpi-help-source').textContent = data.data_source_es;

		// Actualizar según rol actual
		updateHelpRoleContent();

		// Mostrar modal
		const modal = document.getElementById('modal-kpi-interactive-help');
		modal.classList.remove('hidden');
		if (typeof lucide !== 'undefined') lucide.createIcons();
	}

	function closeKpiHelpModal() {
		const modal = document.getElementById('modal-kpi-interactive-help');
		if (modal) modal.classList.add('hidden');
	}

	function switchHelpRole(roleName) {
		currentHelpRole = roleName;
		
		// Actualizar estilos de tabs
		['employee', 'supervisor', 'manager'].forEach(r => {
			const btn = document.getElementById('role-tab-' + r);
			if (btn) {
				if (r === roleName) {
					btn.className = 'px-2.5 py-1 font-semibold rounded-md text-indigo-700 dark:text-white bg-indigo-50 dark:bg-indigo-950/80 shadow-2xs transition-all flex items-center gap-1';
				} else {
					btn.className = 'px-2.5 py-1 font-medium rounded-md text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-all flex items-center gap-1';
				}
			}
		});

		updateHelpRoleContent();
	}

	function updateHelpRoleContent() {
		if (!currentHelpMetricKey) return;
		const data = b2bGlossaryData[currentHelpMetricKey];
		if (!data) return;

		const roleLabels = {
			employee: 'Perspectiva: Operario / Almacén / Vendedor',
			supervisor: 'Perspectiva: Supervisor Comercial / Almacén',
			manager: 'Perspectiva: Gerencia General / Dirección'
		};

		document.getElementById('kpi-help-role-label').textContent = roleLabels[currentHelpRole] || roleLabels.employee;
		
		const roleActions = data.roles_es || {};
		document.getElementById('kpi-help-actions').textContent = roleActions[currentHelpRole] || roleActions.employee || 'Consulte al supervisor de área.';
	}

	function openChartHelp(chartKey) {
		const data = b2bChartsHelpData[chartKey];
		if (!data) return;

		document.getElementById('chart-help-modal-title').textContent = data.title;
		document.getElementById('chart-help-what').textContent = data.what;
		document.getElementById('chart-help-axes').innerHTML = data.axes;
		document.getElementById('chart-help-patterns').textContent = data.patterns;
		document.getElementById('chart-help-decisions').textContent = data.decisions;

		const modal = document.getElementById('modal-chart-interactive-help');
		modal.classList.remove('hidden');
		if (typeof lucide !== 'undefined') lucide.createIcons();
	}

	function closeChartHelpModal() {
		const modal = document.getElementById('modal-chart-interactive-help');
		if (modal) modal.classList.add('hidden');
	}

	// --- GLOSARIO DE NEGOCIOS ---
	function openGlossaryModal() {
		const modal = document.getElementById('modal-business-glossary');
		modal.classList.remove('hidden');
		renderGlossaryItems();
		if (typeof lucide !== 'undefined') lucide.createIcons();
	}

	function closeGlossaryModal() {
		const modal = document.getElementById('modal-business-glossary');
		if (modal) modal.classList.add('hidden');
	}

	function setGlossaryCategory(cat) {
		currentGlossaryCat = cat;
		const chips = document.querySelectorAll('.glossary-cat-chip');
		chips.forEach(c => {
			if (c.textContent.includes(cat) || (cat === 'ALL' && c.textContent.includes('Todos'))) {
				c.className = 'glossary-cat-chip active px-2.5 py-1.5 rounded-lg font-semibold bg-brand-800 text-white shadow-2xs transition-all';
			} else {
				c.className = 'glossary-cat-chip px-2.5 py-1.5 rounded-lg font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-200/60 dark:hover:bg-slate-800 transition-all';
			}
		});
		renderGlossaryItems();
	}

	function filterGlossaryItems() {
		renderGlossaryItems();
	}

	function renderGlossaryItems() {
		const container = document.getElementById('glossary-items-container');
		if (!container) return;

		const query = (document.getElementById('glossary-search-input')?.value || '').toLowerCase().trim();
		const items = Object.values(b2bGlossaryData).filter(item => {
			const matchesCat = currentGlossaryCat === 'ALL' || item.category.toLowerCase().includes(currentGlossaryCat.toLowerCase());
			const matchesQuery = !query || 
				item.name_es.toLowerCase().includes(query) || 
				item.name_en.toLowerCase().includes(query) || 
				item.technical_name.toLowerCase().includes(query) || 
				item.what_is_it_es.toLowerCase().includes(query);
			return matchesCat && matchesQuery;
		});

		document.getElementById('glossary-count-summary').textContent = `Mostrando ${items.length} términos del negocio`;

		if (items.length === 0) {
			container.innerHTML = `
				<div class="py-12 text-center text-slate-400 text-xs">
					<i data-lucide="search-x" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
					No se encontraron términos que coincidan con la búsqueda.
				</div>
			`;
			if (typeof lucide !== 'undefined') lucide.createIcons();
			return;
		}

		container.innerHTML = items.map(item => `
			<div class="pt-4 first:pt-0">
				<div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2">
					<div>
						<div class="flex items-center gap-2">
							<span class="text-sm font-bold text-slate-900 dark:text-white">${item.name_es}</span>
							<span class="px-2 py-0.5 rounded text-[10px] font-mono font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">${item.technical_name}</span>
							<span class="px-1.5 py-0.2 rounded text-[9px] font-semibold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300">${item.category}</span>
						</div>
						<p class="text-xs text-slate-600 dark:text-slate-300 mt-1 leading-relaxed">${item.what_is_it_es}</p>
					</div>
					<button onclick="closeGlossaryModal(); openKpiHelp('${item.key}')" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 shrink-0 transition-colors flex items-center gap-1">
						<span>Ver ayuda completa</span>
						<i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
					</button>
				</div>
				<div class="mt-2.5 p-2 rounded-lg bg-slate-50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-800/80 text-[11px] text-slate-500 dark:text-slate-400 font-mono">
					<strong>Fórmula:</strong> ${item.formula_text_es}
				</div>
			</div>
		`).join('');

		if (typeof lucide !== 'undefined') lucide.createIcons();
	}

	// --- TOUR GUIADO PASO A PASO ("APRENDER A USAR ESTE TABLERO") ---
	const dashboardTourSteps = [
		{
			title: 'Bienvenido al Tablero Ejecutivo de PRINCES',
			content: 'Esta plataforma fue diseñada para que <strong>cualquier persona del equipo</strong>, desde el almacén hasta la gerencia general, comprenda las ventas, el inventario y las decisiones del negocio sin necesidad de ser un experto en finanzas.<br><br>A continuación haremos un breve recorrido de 5 pasos por las herramientas esenciales.',
			elementId: 'b2b-content-area'
		},
		{
			title: '1. Filtros de Período y Sucursales',
			content: 'En la parte superior puede seleccionar el período de análisis con un solo clic: <strong>7D</strong> (últimos 7 días), <strong>MTD</strong> (mes en curso), <strong>QTD</strong> (trimestre) o <strong>YTD</strong> (año).<br><br>También puede filtrar por cliente, cadena o establecimiento específico para evaluar sucursales individuales.',
			elementId: 'dash-btn-mtd'
		},
		{
			title: '2. Tarjetas de Indicadores con Ayuda Interactiva',
			content: 'Cada tarjeta muestra un indicador clave del negocio (como ventas netas, pedidos confirmados, rotación o sell-through).<br><br><strong>¿No conoce un término?</strong> Simplemente pulse el ícono <strong>(i)</strong> en cualquier tarjeta para abrir la ventana de ayuda donde se explica qué significa, cómo se calcula y qué acciones puede tomar según su rol.',
			elementId: 'kpi-net-sales-card'
		},
		{
			title: '3. Gráficos de Tendencias y Desempeño',
			content: 'Los gráficos le permiten detectar anomalías y oportunidades rápidamente. En cada gráfico encontrará el botón <strong>"Explicar este gráfico"</strong> que le detallará qué significan las líneas y barras, y qué patrones debe vigilar.',
			elementId: 'dash-granularity-badge'
		},
		{
			title: '4. Inteligencia Comercial y Simulación',
			content: 'En la sección de <strong>Ciclo de Vida del Producto</strong>, el sistema clasifica automáticamente cada producto en 5 etapas (Introducción, Crecimiento, Madurez, Declive y Salida) y le advierte si un producto corre riesgo de agotarse o si tiene capital congelado en el almacén.<br><br>Además, el <strong>Simulador de Descuentos</strong> le permite calcular antes de lanzar una rebaja si el margen es rentable.',
			elementId: 'section-product-lifecycle'
		}
	];

	function startDashboardTour() {
		currentTourStep = 0;
		const overlay = document.getElementById('tour-guide-overlay');
		overlay.classList.remove('hidden');
		renderTourStep();
	}

	function renderTourStep() {
		const step = dashboardTourSteps[currentTourStep];
		if (!step) return;

		document.getElementById('tour-step-badge').textContent = currentTourStep + 1;
		document.getElementById('tour-step-title').textContent = step.title;
		document.getElementById('tour-step-content').innerHTML = step.content;
		document.getElementById('tour-progress-text').textContent = `Paso ${currentTourStep + 1} de ${dashboardTourSteps.length}`;

		const prevBtn = document.getElementById('tour-btn-prev');
		prevBtn.disabled = (currentTourStep === 0);

		const nextLabel = document.getElementById('tour-next-label');
		if (currentTourStep === dashboardTourSteps.length - 1) {
			nextLabel.textContent = 'Finalizar Guía';
		} else {
			nextLabel.textContent = 'Siguiente';
		}

		if (typeof lucide !== 'undefined') lucide.createIcons();
	}

	function nextTourStep() {
		if (currentTourStep < dashboardTourSteps.length - 1) {
			currentTourStep++;
			renderTourStep();
		} else {
			endDashboardTour();
		}
	}

	function prevTourStep() {
		if (currentTourStep > 0) {
			currentTourStep--;
			renderTourStep();
		}
	}

	function endDashboardTour() {
		const overlay = document.getElementById('tour-guide-overlay');
		if (overlay) overlay.classList.add('hidden');
	}

	// Cerrar modales con tecla Escape
	document.addEventListener('keydown', function(e) {
		if (e.key === 'Escape') {
			closeKpiHelpModal();
			closeChartHelpModal();
			closeGlossaryModal();
			endDashboardTour();
		}
	});
</script>
