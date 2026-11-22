{{-- MODAL 1: ASISTENTE DE PLANIFICACIÓN EN CUATRO PASOS (WIZARD) --}}
<div id="modal-wfm-wizard" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
	<div class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-3xl shadow-2xl overflow-hidden my-8 animate-in fade-in zoom-in-95 duration-150">
		<!-- Header del Asistente -->
		<div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/60 dark:bg-slate-900/40">
			<div>
				<h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
					<i data-lucide="sparkles" class="w-4 h-4 text-brand-700"></i>
					<span>Asistente de Planificación Inteligente</span>
				</h3>
				<p class="text-xs text-slate-500 dark:text-slate-400">
					Genere la dotación óptima en 4 sencillos pasos guiados por demanda real.
				</p>
			</div>
			<button onclick="closeWfmModal('modal-wfm-wizard')" class="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-lg">
				<i data-lucide="x" class="w-5 h-5"></i>
			</button>
		</div>

		<!-- Stepper Navigation (1 -> 2 -> 3 -> 4) -->
		<div class="px-6 py-3 bg-white dark:bg-surface-dark border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between text-xs font-semibold">
			<div class="flex items-center gap-2" id="wfm-step-pill-1">
				<span class="w-6 h-6 rounded-full bg-brand-700 text-white flex items-center justify-center text-xs font-bold">1</span>
				<span class="text-brand-800 dark:text-brand-300">Analizar Ventas</span>
			</div>
			<div class="w-8 h-0.5 bg-slate-200 dark:bg-slate-700"></div>
			<div class="flex items-center gap-2" id="wfm-step-pill-2">
				<span class="w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center text-xs font-bold">2</span>
				<span class="text-slate-500 dark:text-slate-400">Calcular Horas</span>
			</div>
			<div class="w-8 h-0.5 bg-slate-200 dark:bg-slate-700"></div>
			<div class="flex items-center gap-2" id="wfm-step-pill-3">
				<span class="w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center text-xs font-bold">3</span>
				<span class="text-slate-500 dark:text-slate-400">Construir Horarios</span>
			</div>
			<div class="w-8 h-0.5 bg-slate-200 dark:bg-slate-700"></div>
			<div class="flex items-center gap-2" id="wfm-step-pill-4">
				<span class="w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center text-xs font-bold">4</span>
				<span class="text-slate-500 dark:text-slate-400">Revisar & Publicar</span>
			</div>
		</div>

		<!-- Contenido de los 4 Pasos -->
		<div class="p-6">
			<!-- PASO 1: ANALIZAR VENTAS -->
			<div id="wfm-wizard-step-1" class="space-y-4">
				<div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-700">
					<div class="flex items-center justify-between mb-3">
						<span class="text-xs font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider">Demanda Histórica & Ventas B2B Proyectadas</span>
						<span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">+8.4% vs semana previa</span>
					</div>
					<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
						<div class="p-3 bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700">
							<span class="text-slate-400 text-[11px]">Ventas Proyectadas</span>
							<p class="text-base font-bold text-slate-900 dark:text-white mt-0.5">$48,500.00</p>
						</div>
						<div class="p-3 bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700">
							<span class="text-slate-400 text-[11px]">Pedidos Confirmados</span>
							<p class="text-base font-bold text-indigo-600 dark:text-indigo-400 mt-0.5">38 pedidos B2B</p>
						</div>
						<div class="p-3 bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700">
							<span class="text-slate-400 text-[11px]">Cajas por Reponer</span>
							<p class="text-base font-bold text-slate-900 dark:text-white mt-0.5">1,420 cajas</p>
						</div>
						<div class="p-3 bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700">
							<span class="text-slate-400 text-[11px]">Visitas Preventa</span>
							<p class="text-base font-bold text-slate-900 dark:text-white mt-0.5">64 clientes</p>
						</div>
					</div>
				</div>

				<div class="p-3.5 rounded-lg bg-emerald-50/70 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-xs text-emerald-800 dark:text-emerald-300">
					✓ <strong>Modelo B2B Calibrado:</strong> La proyección utiliza órdenes en firme de cadenas mayoristas y tasa de rotación histórica de SKUs en góndola.
				</div>
			</div>

			<!-- PASO 2: CALCULAR HORAS -->
			<div id="wfm-wizard-step-2" class="hidden space-y-4">
				<div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
					<div>
						<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Ventas Objetivo / Hora</label>
						<input type="number" id="wiz-target-sales-hour" value="150" class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 font-medium" />
					</div>
					<div>
						<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Costo Laboral Hora ($)</label>
						<input type="number" id="wiz-hourly-cost" value="7.50" step="0.5" class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 font-medium" />
					</div>
					<div>
						<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Presupuesto Semanal ($)</label>
						<input type="number" id="wiz-budget" value="2800" class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 font-medium" />
					</div>
				</div>

				<!-- Resultado de Cálculo de Horas -->
				<div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-700 space-y-3">
					<div class="flex items-center justify-between text-xs">
						<span class="font-bold text-slate-700 dark:text-slate-300">Horas Recomendadas vs Presupuesto:</span>
						<span class="font-bold text-brand-700 dark:text-brand-300">340.0 horas necesarias</span>
					</div>
					<div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-xs">
						<div class="p-2.5 bg-white dark:bg-slate-800 rounded-lg">
							<span class="text-slate-400 text-[10px]">Costo Estimado</span>
							<p class="font-bold text-slate-800 dark:text-white">$2,520.00</p>
						</div>
						<div class="p-2.5 bg-white dark:bg-slate-800 rounded-lg">
							<span class="text-slate-400 text-[10px]">Margen Presupuestario</span>
							<p class="font-bold text-emerald-600">+$280.00 disponible</p>
						</div>
						<div class="p-2.5 bg-white dark:bg-slate-800 rounded-lg">
							<span class="text-slate-400 text-[10px]">Cobertura Proyectada</span>
							<p class="font-bold text-emerald-600">98.8% Óptima</p>
						</div>
					</div>
				</div>
			</div>

			<!-- PASO 3: CONSTRUIR HORARIOS -->
			<div id="wfm-wizard-step-3" class="hidden space-y-4">
				<p class="text-xs text-slate-600 dark:text-slate-300">
					Se aplicarán automáticamente las plantillas oficiales de <strong>Apertura (07:00-15:30)</strong>, <strong>Intermedio (09:00-17:30)</strong> y <strong>Cierre (13:30-22:00)</strong> respetando la disponibilidad y contrato de cada colaborador.
				</p>

				<div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-700 space-y-2">
					<div class="flex items-center justify-between text-xs py-1 border-b border-slate-200 dark:border-slate-700">
						<span class="font-medium text-slate-700 dark:text-slate-300">Reposición y exhibición:</span>
						<span class="font-bold text-emerald-600">4 Colaboradores (168h asignadas)</span>
					</div>
					<div class="flex items-center justify-between text-xs py-1 border-b border-slate-200 dark:border-slate-700">
						<span class="font-medium text-slate-700 dark:text-slate-300">Fuerza de Ventas B2B:</span>
						<span class="font-bold text-indigo-600">3 Ejecutivos (124h asignadas)</span>
					</div>
					<div class="flex items-center justify-between text-xs py-1">
						<span class="font-medium text-slate-700 dark:text-slate-300">Operaciones & Almacén WMS:</span>
						<span class="font-bold text-purple-600">3 Operadores (44h asignadas)</span>
					</div>
				</div>
			</div>

			<!-- PASO 4: REVISAR Y PUBLICAR -->
			<div id="wfm-wizard-step-4" class="hidden space-y-4">
				<div class="space-y-2 text-xs">
					<div class="p-3 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 flex items-center gap-2 text-emerald-800 dark:text-emerald-300">
						<i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
						<span><strong>0 Solapamientos:</strong> Ningún colaborador tiene turnos superpuestos.</span>
					</div>
					<div class="p-3 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 flex items-center gap-2 text-emerald-800 dark:text-emerald-300">
						<i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
						<span><strong>Descanso Legal Cumplido:</strong> Intervalo mínimo de 12 horas entre turnos garantizado.</span>
					</div>
					<div class="p-3 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 flex items-center gap-2 text-emerald-800 dark:text-emerald-300">
						<i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
						<span><strong>Costo Laboral Controlado:</strong> $2,520.00 (Dentro del presupuesto de $2,800.00).</span>
					</div>
				</div>
			</div>
		</div>

		<!-- Footer con Botones Siguiente / Anterior / Guardar Borrador / Publicar -->
		<div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/60 dark:bg-slate-900/40">
			<button id="wfm-btn-prev" onclick="navigateWizard(-1)" class="px-4 py-2 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 transition-colors hidden">
				‹ Anterior
			</button>
			<button type="button" onclick="saveScheduleDraft(); closeWfmModal('modal-wfm-wizard');" class="px-3.5 py-2 text-xs font-semibold rounded-lg border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 transition-colors">
				Guardar borrador y continuar después
			</button>
			<div class="flex items-center gap-2 ml-auto">
				<button id="wfm-btn-next" onclick="navigateWizard(1)" class="px-4 py-2 text-xs font-semibold rounded-lg bg-brand-700 text-white hover:bg-brand-800 transition-colors">
					Siguiente Paso ›
				</button>
				<button id="wfm-btn-finish" onclick="finalizeWizardPublication()" class="px-5 py-2 text-xs font-semibold rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition-colors hidden flex items-center gap-2">
					<i data-lucide="send" class="w-3.5 h-3.5"></i>
					<span>Publicar Programación Oficial</span>
				</button>
			</div>
		</div>
	</div>
</div>

{{-- MODAL 2: EDITAR / ASIGNAR TURNO --}}
<div id="modal-wfm-shift-edit" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
	<div class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden my-8 animate-in fade-in zoom-in-95 duration-150">
		<div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/60 dark:bg-slate-900/40">
			<div class="flex items-center gap-2">
				<i data-lucide="calendar-plus" class="w-5 h-5 text-brand-700"></i>
				<h3 class="text-sm font-bold text-slate-900 dark:text-white" id="wfm-shift-modal-title">
					Asignar Turno de Trabajo
				</h3>
			</div>
			<button onclick="closeWfmModal('modal-wfm-shift-edit')" class="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-lg">
				<i data-lucide="x" class="w-5 h-5"></i>
			</button>
		</div>

		<form id="form-wfm-shift" onsubmit="handleSaveShift(event)" class="p-6 space-y-4">
			<input type="hidden" id="shift-edit-id" value="" />
			<input type="hidden" id="shift-edit-emp-id" value="" />
			<input type="hidden" id="shift-edit-dow" value="" />
			<input type="hidden" id="shift-edit-date" value="" />

			<!-- Selector de Colaborador (Habilitado para nuevo turno general) -->
			<div id="shift-modal-emp-select-wrap" class="hidden">
				<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Colaborador</label>
				<select id="shift-edit-emp-select" onchange="handleModalEmployeeSelect(this.value)" class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 font-medium">
					<!-- Poblado dinámicamente -->
				</select>
			</div>

			<!-- Empleado y Día Info Fijo -->
			<div id="shift-modal-emp-info-wrap" class="p-3 bg-slate-50 dark:bg-slate-800/40 rounded-lg border border-slate-200 dark:border-slate-700 text-xs">
				<div class="flex justify-between">
					<span class="text-slate-500">Colaborador:</span>
					<span class="font-bold text-slate-800 dark:text-white" id="shift-modal-emp-name">Jorge Luis Quispe</span>
				</div>
				<div class="flex justify-between mt-1">
					<span class="text-slate-500">Día / Fecha:</span>
					<span class="font-semibold text-brand-700" id="shift-modal-dow-name">Lunes, 16 Nov 2026</span>
				</div>
			</div>

			<!-- Selector Rápido de Plantilla -->
			<div>
				<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Cargar Plantilla Estándar</label>
				<select id="shift-edit-template" onchange="applyShiftTemplateToForm()" class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 font-medium">
					<option value="">Personalizado...</option>
					<option value="TMPL-APT">Apertura Mayorista (07:00 - 15:30 • 8.0h)</option>
					<option value="TMPL-INT">Intermedio Reposición (09:00 - 17:30 • 8.0h)</option>
					<option value="TMPL-CIE">Cierre y Despacho (13:30 - 22:00 • 8.0h)</option>
					<option value="TMPL-RUT">Ruta Preventa (08:00 - 17:00 • 8.0h)</option>
					<option value="TMPL-PAR">Medio Tiempo (08:00 - 12:00 • 4.0h)</option>
					<option value="REST">Descanso Semanal / Libre (0.0h)</option>
				</select>
			</div>

			<div class="grid grid-cols-2 gap-3">
				<div>
					<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Hora Inicio</label>
					<input type="time" id="shift-edit-start" value="08:00" required onchange="triggerLiveShiftValidation()" class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 font-medium" />
				</div>
				<div>
					<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Hora Fin</label>
					<input type="time" id="shift-edit-end" value="16:30" required onchange="triggerLiveShiftValidation()" class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 font-medium" />
				</div>
			</div>

			<div class="grid grid-cols-2 gap-3">
				<div>
					<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Descanso (Minutos)</label>
					<input type="number" id="shift-edit-break" value="30" step="15" min="0" onchange="triggerLiveShiftValidation()" class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 font-medium" />
				</div>
				<div>
					<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tipo de Turno</label>
					<select id="shift-edit-type" onchange="triggerLiveShiftValidation()" class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 font-medium">
						<option value="opening">Apertura</option>
						<option value="mid" selected>Intermedio</option>
						<option value="closing">Cierre</option>
						<option value="standard">Ruta Preventa</option>
						<option value="rest">Descanso / Día Libre</option>
						<option value="vacation">Vacaciones Autorizadas</option>
						<option value="absence">Ausencia / Permiso</option>
					</select>
				</div>
			</div>

			<!-- Asignación de Turnos Repetitivos -->
			<div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700/80 space-y-2">
				<div class="flex items-center justify-between">
					<label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-800 dark:text-slate-200">
						<input type="checkbox" id="shift-edit-repeat-toggle" onchange="toggleRepeatDaysSection(this.checked)" class="rounded text-brand-700 focus:ring-brand-500 w-4 h-4" />
						<span>Asignar turno repetitivo en múltiples días</span>
					</label>
					<span class="text-[10px] text-slate-400">Jornada continua</span>
				</div>
				<div id="shift-repeat-days-container" class="hidden pt-1 flex items-center justify-between gap-1 text-xs">
					<label class="flex items-center gap-1 cursor-pointer px-2 py-1 bg-white dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-700 text-[11px] font-semibold">
						<input type="checkbox" name="repeat-day" value="1" class="repeat-dow-checkbox rounded text-brand-700"> Lun
					</label>
					<label class="flex items-center gap-1 cursor-pointer px-2 py-1 bg-white dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-700 text-[11px] font-semibold">
						<input type="checkbox" name="repeat-day" value="2" class="repeat-dow-checkbox rounded text-brand-700"> Mar
					</label>
					<label class="flex items-center gap-1 cursor-pointer px-2 py-1 bg-white dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-700 text-[11px] font-semibold">
						<input type="checkbox" name="repeat-day" value="3" class="repeat-dow-checkbox rounded text-brand-700"> Mié
					</label>
					<label class="flex items-center gap-1 cursor-pointer px-2 py-1 bg-white dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-700 text-[11px] font-semibold">
						<input type="checkbox" name="repeat-day" value="4" class="repeat-dow-checkbox rounded text-brand-700"> Jue
					</label>
					<label class="flex items-center gap-1 cursor-pointer px-2 py-1 bg-white dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-700 text-[11px] font-semibold">
						<input type="checkbox" name="repeat-day" value="5" class="repeat-dow-checkbox rounded text-brand-700"> Vie
					</label>
					<label class="flex items-center gap-1 cursor-pointer px-2 py-1 bg-white dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-700 text-[11px] font-semibold">
						<input type="checkbox" name="repeat-day" value="6" class="repeat-dow-checkbox rounded text-brand-700"> Sáb
					</label>
					<label class="flex items-center gap-1 cursor-pointer px-2 py-1 bg-white dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-700 text-[11px] font-semibold">
						<input type="checkbox" name="repeat-day" value="7" class="repeat-dow-checkbox rounded text-brand-700"> Dom
					</label>
				</div>
			</div>

			<div>
				<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tareas / Nota Operativa (Opcional)</label>
				<input type="text" id="shift-edit-notes" placeholder="Ej. Reposición pasillos 2 y 3, conteo FEFO de vencimientos" class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-2" />
			</div>

			<!-- Contenedor de Alertas y Validaciones en Tiempo Real -->
			<div id="shift-validation-alerts" class="hidden space-y-1 text-xs">
				<!-- Inyectado dinámicamente con advertencias o bloqueos -->
			</div>

			<div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
				<button type="button" onclick="confirmDeleteShiftFromModal()" class="px-3 py-2 text-xs font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition-colors flex items-center gap-1.5">
					<i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
					<span>Quitar Turno</span>
				</button>
				<div class="flex items-center gap-2">
					<button type="button" onclick="closeWfmModal('modal-wfm-shift-edit')" class="px-3.5 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors">
						Cancelar
					</button>
					<button type="submit" id="shift-submit-btn" class="px-4 py-2 text-xs font-semibold bg-brand-700 hover:bg-brand-800 text-white rounded-lg transition-colors shadow-2xs">
						Guardar Turno
					</button>
				</div>
			</div>
		</form>
	</div>
</div>

{{-- MODAL DE CONFIRMACIÓN PARA ELIMINAR TURNO CON AUDITORÍA --}}
<div id="modal-wfm-delete-confirm" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
	<div class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-150">
		<div class="p-6 text-center space-y-3">
			<div class="w-12 h-12 rounded-full bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center mx-auto">
				<i data-lucide="alert-triangle" class="w-6 h-6"></i>
			</div>
			<h3 class="text-base font-bold text-slate-900 dark:text-white">
				¿Eliminar este turno de trabajo?
			</h3>
			<p class="text-xs text-slate-500 dark:text-slate-400" id="wfm-delete-confirm-text">
				Esta acción eliminará la jornada asignada y convertirá el día en descanso semanal para el colaborador. Se registrará en la auditoría inmutable de WFM.
			</p>
			<input type="hidden" id="delete-confirm-shift-id" value="" />
		</div>
		<div class="px-6 py-3 bg-slate-50 dark:bg-slate-900/40 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-2">
			<button onclick="closeWfmModal('modal-wfm-delete-confirm')" class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors">
				Cancelar
			</button>
			<button onclick="executeConfirmedShiftDelete()" class="px-4 py-2 text-xs font-semibold bg-rose-600 hover:bg-rose-700 text-white rounded-lg transition-colors shadow-2xs">
				Confirmar Eliminación
			</button>
		</div>
	</div>
</div>

{{-- MODAL EDUCATIVO: GUÍA Y SIGNIFICADO DE LOS 8 KPIS SUPERIORES --}}
<div id="modal-wfm-kpi-info" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
	<div class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-2xl shadow-2xl overflow-hidden my-8 animate-in fade-in zoom-in-95 duration-150">
		<div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/60 dark:bg-slate-900/40">
			<div class="flex items-center gap-2">
				<i data-lucide="help-circle" class="w-5 h-5 text-brand-700"></i>
				<h3 class="text-sm font-bold text-slate-900 dark:text-white">
					Indicadores Clave de Gestión de Horarios (KPIs)
				</h3>
			</div>
			<button onclick="closeWfmModal('modal-wfm-kpi-info')" class="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-lg">
				<i data-lucide="x" class="w-5 h-5"></i>
			</button>
		</div>

		<div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto text-xs">
			<p class="text-slate-600 dark:text-slate-400 text-xs">
				Los 8 indicadores superiores se recalculan en tiempo real según la fecha, sucursal, departamento y vista seleccionadas (Día, Semana o Mes). A continuación se detalla su significado, fórmula y utilidad:
			</p>

			<div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
				<!-- KPI 1 -->
				<div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 space-y-1">
					<div class="flex items-center gap-1.5 font-bold text-slate-900 dark:text-white">
						<i data-lucide="users" class="w-4 h-4 text-brand-700"></i>
						<span>1. Personal Programado</span>
					</div>
					<p class="text-slate-600 dark:text-slate-300"><strong>Qué es:</strong> Cantidad de colaboradores con turnos activos en el periodo seleccionado.</p>
					<p class="text-slate-500 dark:text-slate-400"><strong>Cálculo:</strong> Conteo único de colaboradores con jornada laboral asignada (excluyendo descansos y ausencias).</p>
					<p class="text-slate-500 dark:text-slate-400"><strong>Utilidad:</strong> Verificar la plantilla activa disponible para cubrir la operación de almacén y ventas.</p>
				</div>

				<!-- KPI 2 -->
				<div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 space-y-1">
					<div class="flex items-center gap-1.5 font-bold text-emerald-700 dark:text-emerald-400">
						<i data-lucide="shield-check" class="w-4 h-4 text-emerald-600"></i>
						<span>2. Cobertura de Turnos (%)</span>
					</div>
					<p class="text-slate-600 dark:text-slate-300"><strong>Qué es:</strong> Nivel porcentual de cumplimiento de horas programadas respecto a la demanda óptima.</p>
					<p class="text-slate-500 dark:text-slate-400"><strong>Cálculo:</strong> <code class="px-1 py-0.5 rounded bg-slate-200 dark:bg-slate-800">(Horas Programadas / Horas Requeridas) × 100</code>.</p>
					<p class="text-slate-500 dark:text-slate-400"><strong>Utilidad:</strong> Prevenir tanto el subdimensionamiento (pérdida de ventas) como el sobredimensionamiento (costo excesivo).</p>
				</div>

				<!-- KPI 3 -->
				<div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 space-y-1">
					<div class="flex items-center gap-1.5 font-bold text-amber-700 dark:text-amber-400">
						<i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600"></i>
						<span>3. Turnos Sin Cubrir</span>
					</div>
					<p class="text-slate-600 dark:text-slate-300"><strong>Qué es:</strong> Puestos o jornadas faltantes para satisfacer los picos de despacho y venta.</p>
					<p class="text-slate-500 dark:text-slate-400"><strong>Cálculo:</strong> <code class="px-1 py-0.5 rounded bg-slate-200 dark:bg-slate-800">MAX(0, (Horas Requeridas − Horas Asignadas) / 8.0)</code>.</p>
					<p class="text-slate-500 dark:text-slate-400"><strong>Utilidad:</strong> Identificar brechas operativas antes de que afecten la entrega a clientes mayoristas.</p>
				</div>

				<!-- KPI 4 -->
				<div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 space-y-1">
					<div class="flex items-center gap-1.5 font-bold text-slate-900 dark:text-white">
						<i data-lucide="clock" class="w-4 h-4 text-indigo-600"></i>
						<span>4. Horas Programadas</span>
					</div>
					<p class="text-slate-600 dark:text-slate-300"><strong>Qué es:</strong> Suma de horas netas de trabajo efectivo planificadas.</p>
					<p class="text-slate-500 dark:text-slate-400"><strong>Cálculo:</strong> Suma de horas brutas de todos los turnos descontando los minutos de descanso/almuerzo.</p>
					<p class="text-slate-500 dark:text-slate-400"><strong>Utilidad:</strong> Base para el control de jornada laboral y comparación con el presupuesto.</p>
				</div>

				<!-- KPI 5 -->
				<div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 space-y-1">
					<div class="flex items-center gap-1.5 font-bold text-blue-700 dark:text-blue-400">
						<i data-lucide="plus-circle" class="w-4 h-4 text-blue-600"></i>
						<span>5. Horas Adicionales Previstas</span>
					</div>
					<p class="text-slate-600 dark:text-slate-300"><strong>Qué es:</strong> Horas programadas por encima de la jornada ordinaria legal o contractual.</p>
					<p class="text-slate-500 dark:text-slate-400"><strong>Cálculo:</strong> Excedente de horas por encima de 48h semanales (o 40h según contrato individual).</p>
					<p class="text-slate-500 dark:text-slate-400"><strong>Utilidad:</strong> Anticipar sobrecostos de horas extraordinarias y evitar sanciones laborales.</p>
				</div>

				<!-- KPI 6 -->
				<div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 space-y-1">
					<div class="flex items-center gap-1.5 font-bold text-rose-700 dark:text-rose-400">
						<i data-lucide="user-x" class="w-4 h-4 text-rose-600"></i>
						<span>6. Ausencias Registradas</span>
					</div>
					<p class="text-slate-600 dark:text-slate-300"><strong>Qué es:</strong> Colaboradores en descanso médico, vacaciones autorizadas o permiso.</p>
					<p class="text-slate-500 dark:text-slate-400"><strong>Cálculo:</strong> Suma de turnos marcados como 'vacation' o 'absence' en el rango de fechas.</p>
					<p class="text-slate-500 dark:text-slate-400"><strong>Utilidad:</strong> Conocer de antemano el personal no disponible para redistribuir la carga.</p>
				</div>

				<!-- KPI 7 -->
				<div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 space-y-1">
					<div class="flex items-center gap-1.5 font-bold text-purple-700 dark:text-purple-400">
						<i data-lucide="arrow-left-right" class="w-4 h-4 text-purple-600"></i>
						<span>7. Solicitudes Pendientes</span>
					</div>
					<p class="text-slate-600 dark:text-slate-300"><strong>Qué es:</strong> Peticiones de cambio de turno o descanso ingresadas por colaboradores.</p>
					<p class="text-slate-500 dark:text-slate-400"><strong>Cálculo:</strong> Conteo de registros en la tabla de solicitudes con estado 'pending'.</p>
					<p class="text-slate-500 dark:text-slate-400"><strong>Utilidad:</strong> Resolver a tiempo las necesidades del personal antes de la publicación definitiva.</p>
				</div>

				<!-- KPI 8 -->
				<div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 space-y-1">
					<div class="flex items-center gap-1.5 font-bold text-emerald-800 dark:text-emerald-300">
						<i data-lucide="dollar-sign" class="w-4 h-4 text-emerald-600"></i>
						<span>8. Costo Estimado de Personal</span>
					</div>
					<p class="text-slate-600 dark:text-slate-300"><strong>Qué es:</strong> Inversión proyectada en nómina directa para los turnos asignados.</p>
					<p class="text-slate-500 dark:text-slate-400"><strong>Cálculo:</strong> <code class="px-1 py-0.5 rounded bg-slate-200 dark:bg-slate-800">Σ (Horas netas del turno × Tarifa horaria contractual del colaborador)</code>.</p>
					<p class="text-slate-500 dark:text-slate-400"><strong>Utilidad:</strong> Asegurar que la planificación se mantenga dentro del presupuesto aprobado.</p>
				</div>
			</div>
		</div>

		<div class="px-6 py-3 border-t border-slate-200 dark:border-slate-800 flex justify-end bg-slate-50/60 dark:bg-slate-900/40">
			<button onclick="closeWfmModal('modal-wfm-kpi-info')" class="px-4 py-2 text-xs font-semibold rounded-lg bg-slate-800 text-white hover:bg-slate-900 transition-colors">
				Entendido
			</button>
		</div>
	</div>
</div>

{{-- MODAL 3: SIMULADOR WHAT-IF --}}
<div id="modal-wfm-simulate" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
	<div class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-xl shadow-2xl overflow-hidden my-8 animate-in fade-in zoom-in-95 duration-150">
		<div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-indigo-50/50 dark:bg-indigo-950/30">
			<div class="flex items-center gap-2">
				<i data-lucide="sparkles" class="w-5 h-5 text-indigo-600"></i>
				<h3 class="text-base font-bold text-slate-900 dark:text-white">
					Simulador de Escenarios What-If
				</h3>
			</div>
			<button onclick="closeWfmModal('modal-wfm-simulate')" class="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-lg">
				<i data-lucide="x" class="w-5 h-5"></i>
			</button>
		</div>

		<div class="p-6 space-y-4">
			<p class="text-xs text-slate-500">
				Proyecte el impacto en dotación y costo ante variaciones de demanda sin alterar la programación publicada.
			</p>

			<!-- Controles Rápidos -->
			<div>
				<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">Variación de Ventas:</label>
				<div class="grid grid-cols-4 gap-2">
					<button onclick="setSimSalesDelta(-10)" class="py-2 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 sim-delta-btn" data-delta="-10">-10% Ventas</button>
					<button onclick="setSimSalesDelta(0)" class="py-2 text-xs font-semibold rounded-lg border border-indigo-600 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 sim-delta-btn" data-delta="0">Normal</button>
					<button onclick="setSimSalesDelta(10)" class="py-2 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 sim-delta-btn" data-delta="10">+10% Pico</button>
					<button onclick="setSimSalesDelta(20)" class="py-2 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 sim-delta-btn" data-delta="20">+20% Máximo</button>
				</div>
			</div>

			<!-- Ausencias imprevistas -->
			<div>
				<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Colaboradores Ausentes:</label>
				<select id="sim-absent-count" onchange="runLiveSimulation()" class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 font-medium">
					<option value="0" selected>0 ausencias (Dotación completa)</option>
					<option value="1">1 ausencia (Baja médica o permiso)</option>
					<option value="2">2 ausencias imprevistas</option>
					<option value="3">3 ausencias (Contingencia)</option>
				</select>
			</div>

			<!-- Resultados Proyectados -->
			<div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 space-y-3">
				<div class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
					Impacto Proyectado en Cobertura
				</div>
				<div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-xs">
					<div class="p-2.5 bg-white dark:bg-slate-800 rounded-lg">
						<span class="text-slate-400 text-[10px]">Ventas Proyectadas</span>
						<p class="font-bold text-slate-900 dark:text-white" id="sim-res-sales">$48,500.00</p>
					</div>
					<div class="p-2.5 bg-white dark:bg-slate-800 rounded-lg">
						<span class="text-slate-400 text-[10px]">Horas Necesarias</span>
						<p class="font-bold text-indigo-600" id="sim-res-hours">340.0 hrs</p>
					</div>
					<div class="p-2.5 bg-white dark:bg-slate-800 rounded-lg">
						<span class="text-slate-400 text-[10px]">Cobertura Proyectada</span>
						<p class="font-bold text-emerald-600" id="sim-res-coverage">98.8%</p>
					</div>
				</div>
				<div class="p-2.5 bg-indigo-50/70 dark:bg-indigo-950/40 rounded-lg border border-indigo-200 dark:border-indigo-800 text-xs text-indigo-800 dark:text-indigo-300" id="sim-res-advice">
					Operación estable: Cobertura proyectada en 98.8% dentro del margen presupuestario.
				</div>
			</div>
		</div>

		<div class="px-6 py-3 border-t border-slate-200 dark:border-slate-800 flex justify-end bg-slate-50/60 dark:bg-slate-900/40">
			<button onclick="closeWfmModal('modal-wfm-simulate')" class="px-4 py-2 text-xs font-semibold rounded-lg bg-slate-800 text-white hover:bg-slate-900 transition-colors">
				Cerrar Simulador
			</button>
		</div>
	</div>
</div>

{{-- MODAL 4: OPTIMIZACIÓN INTELIGENTE DE TURNOS (REGLAS DETERMINISTAS) --}}
<div id="modal-wfm-optimize" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
	<div class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-2xl shadow-2xl overflow-hidden my-8 animate-in fade-in zoom-in-95 duration-150">
		<div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-gradient-to-r from-emerald-900/40 to-slate-900/20">
			<div>
				<h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
					<i data-lucide="sparkles" class="w-5 h-5 text-emerald-600"></i>
					<span>Optimización Inteligente de Semana (Algoritmo Determinista WFM)</span>
				</h3>
				<p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
					Analiza la demanda de pedidos, picos de almacén y reglas laborales para proponer ajustes de dotación.
				</p>
			</div>
			<button onclick="closeWfmModal('modal-wfm-optimize')" class="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-lg">
				<i data-lucide="x" class="w-5 h-5"></i>
			</button>
		</div>

		<div class="p-6 space-y-4">
			<!-- Comparativa Antes vs Propuesta -->
			<div class="grid grid-cols-2 gap-3 text-xs">
				<div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/40">
					<span class="text-[10px] uppercase font-bold text-slate-400">Estado Actual de la Semana</span>
					<div class="mt-2 space-y-1">
						<div class="flex justify-between"><span>Horas Programadas:</span> <strong id="opt-cur-hours" class="text-slate-800 dark:text-white">236.0h</strong></div>
						<div class="flex justify-between"><span>Costo Estimado:</span> <strong id="opt-cur-cost" class="text-slate-800 dark:text-white">$1,884.00</strong></div>
						<div class="flex justify-between"><span>Cobertura de Personal:</span> <strong id="opt-cur-cov" class="text-amber-600">69.4%</strong></div>
						<div class="flex justify-between"><span>Margen Presupuestario:</span> <strong id="opt-cur-margin" class="text-emerald-600">+$916.00</strong></div>
					</div>
				</div>

				<div class="p-3.5 rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50/50 dark:bg-emerald-950/30">
					<span class="text-[10px] uppercase font-bold text-emerald-700 dark:text-emerald-300">Propuesta Optimizada WFM</span>
					<div class="mt-2 space-y-1 text-emerald-900 dark:text-emerald-200">
						<div class="flex justify-between"><span>Horas Proyectadas:</span> <strong id="opt-prop-hours" class="font-bold">243.5h</strong></div>
						<div class="flex justify-between"><span>Costo Proyectado:</span> <strong id="opt-prop-cost" class="font-bold">$1,940.00</strong></div>
						<div class="flex justify-between"><span>Nueva Cobertura:</span> <strong id="opt-prop-cov" class="font-bold text-emerald-600">71.6% (+2.2%)</strong></div>
						<div class="flex justify-between"><span>Margen Resultante:</span> <strong id="opt-prop-margin" class="font-bold text-emerald-600">+$860.00</strong></div>
					</div>
				</div>
			</div>

			<!-- Lista de Sugerencias Específicas -->
			<div class="space-y-2">
				<div class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
					Ajustes Deterministas Recomendados
				</div>
				<div id="opt-suggestions-list" class="space-y-2 text-xs">
					<!-- Rellenado dinámicamente -->
				</div>
			</div>
		</div>

		<div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/60 dark:bg-slate-900/40">
			<button id="btn-revert-opt" onclick="revertCurrentOptimization()" class="px-3.5 py-2 text-xs font-semibold rounded-lg border border-slate-300 dark:border-slate-700 hover:bg-slate-100 text-slate-700 dark:text-slate-300 transition-colors hidden">
				Revertir Optimización
			</button>
			<div class="flex items-center gap-2 ml-auto">
				<button onclick="closeWfmModal('modal-wfm-optimize')" class="px-4 py-2 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 transition-colors">
					Cancelar
				</button>
				<button id="btn-apply-opt" onclick="applyCurrentOptimization()" class="px-5 py-2 text-xs font-bold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs flex items-center gap-1.5 transition-colors">
					<i data-lucide="check" class="w-4 h-4"></i>
					<span>Aplicar Optimización al Borrador</span>
				</button>
			</div>
		</div>
	</div>
</div>

{{-- MODAL 5: BANDEJA DE NOTIFICACIONES DEMO INTERNAS --}}
<div id="modal-wfm-notifications" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
	<div class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden my-8 animate-in fade-in zoom-in-95 duration-150">
		<div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/60 dark:bg-slate-900/40">
			<div class="flex items-center gap-2">
				<i data-lucide="bell" class="w-5 h-5 text-amber-500"></i>
				<h3 class="text-sm font-bold text-slate-900 dark:text-white">Centro de Notificaciones Internas</h3>
			</div>
			<button onclick="closeWfmModal('modal-wfm-notifications')" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg">
				<i data-lucide="x" class="w-5 h-5"></i>
			</button>
		</div>

		<div class="p-4 space-y-2 max-h-96 overflow-y-auto" id="wfm-notifications-container">
			<!-- Renderizado dinámicamente -->
		</div>

		<div class="px-6 py-3 border-t border-slate-200 dark:border-slate-800 flex justify-between items-center bg-slate-50/60 dark:bg-slate-900/40">
			<button onclick="markAllNotificationsRead()" class="text-xs text-brand-700 dark:text-brand-400 font-semibold hover:underline">
				Marcar todas como leídas
			</button>
			<button onclick="closeWfmModal('modal-wfm-notifications')" class="px-4 py-1.5 text-xs font-semibold rounded-lg bg-slate-800 text-white">
				Cerrar
			</button>
		</div>
	</div>
</div>

{{-- MODAL 6: PANEL DE CONTROL DEMO (RESTABLECER, EXPORTAR, AUDITORÍA) --}}
<div id="modal-wfm-demo-settings" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
	<div class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden my-8 animate-in fade-in zoom-in-95 duration-150">
		<div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/60 dark:bg-slate-900/40">
			<div class="flex items-center gap-2">
				<i data-lucide="settings-2" class="w-5 h-5 text-brand-700"></i>
				<h3 class="text-sm font-bold text-slate-900 dark:text-white">Herramientas de datos</h3>
			</div>
			<button onclick="closeWfmModal('modal-wfm-demo-settings')" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg">
				<i data-lucide="x" class="w-5 h-5"></i>
			</button>
		</div>

		<div class="p-6 space-y-4 text-xs">
			<div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300">
				<div class="font-bold">Persistencia Local Activa (IndexedDB / LocalStorage)</div>
				<p class="text-[11px] text-emerald-700 dark:text-emerald-400 mt-0.5">
					Cualquier turno creado, editado o movido se guarda automáticamente en tu navegador y persiste al recargar la página.
				</p>
			</div>

			<div class="space-y-2">
				<button onclick="exportDemoDatasetJson()" class="w-full py-2.5 px-3 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 rounded-lg font-semibold flex items-center justify-center gap-2 transition-colors">
					<i data-lucide="download" class="w-4 h-4"></i>
					<span>Exportar dataset completo (JSON)</span>
				</button>
				<button onclick="confirmResetDemoData()" class="w-full py-2.5 px-3 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/50 text-rose-700 dark:text-rose-300 rounded-lg font-semibold border border-rose-200 dark:border-rose-800 flex items-center justify-center gap-2 transition-colors">
					<i data-lucide="rotate-ccw" class="w-4 h-4"></i>
					<span>Restablecer datos a valores de fábrica</span>
				</button>
			</div>
		</div>

		<div class="px-6 py-3 border-t border-slate-200 dark:border-slate-800 flex justify-end bg-slate-50/60 dark:bg-slate-900/40">
			<button onclick="closeWfmModal('modal-wfm-demo-settings')" class="px-4 py-1.5 text-xs font-semibold rounded-lg bg-slate-800 text-white">
				Cerrar
			</button>
		</div>
	</div>
</div>
