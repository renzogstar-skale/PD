/*
 * Ayuda contextual de indicadores B2B.
 * Lee el diccionario centralizado (public/data/b2b-kpi-dictionary.json) y:
 *  - unifica el nombre visible de cada indicador marcado con data-kpi="<id>",
 *  - agrega un icono de información con explicación (hover/foco en escritorio, toque en móvil).
 * Uso en el HTML: <span data-kpi="net_sales">Ventas netas</span>
 *   data-kpi-label="name|none" → usa el nombre largo o conserva el texto original (por defecto: nombre corto).
 */
(function () {
	'use strict';

	var DICTIONARY_URL = document.currentScript ? document.currentScript.getAttribute('data-dictionary') : null;
	var dict = null;
	var popover = null;
	var activeTrigger = null;
	var hideTimer = null;
	var scanTimer = null;
	var canHover = window.matchMedia && window.matchMedia('(hover: hover) and (pointer: fine)').matches;
	var INFO_SVG = '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>';

	function lang() {
		try { return localStorage.getItem('b2b_lang') === 'en' ? 'en' : 'es'; } catch (e) { return 'es'; }
	}

	function esc(s) {
		return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
		});
	}

	function get(id) {
		return dict && dict.kpis[id] ? dict.kpis[id] : null;
	}

	function text(id, field, l) {
		var k = get(id);
		if (!k) return '';
		var t = k[l || lang()] || k.es;
		return t[field] || '';
	}

	function injectStyles() {
		if (document.getElementById('b2b-kpi-help-styles')) return;
		var css = '' +
			'.kpi-info-btn{display:inline-flex;align-items:center;justify-content:center;vertical-align:middle;margin-left:4px;padding:2px;border:0;background:transparent;border-radius:9999px;color:#94a3b8;cursor:help;line-height:0;flex-shrink:0;text-transform:none;letter-spacing:normal}' +
			'.kpi-info-btn:hover,.kpi-info-btn:focus-visible,.kpi-info-btn[aria-expanded="true"]{color:#4f46e5;background:rgba(99,102,241,.1);outline:none}' +
			'@media (hover:none){.kpi-info-btn{padding:6px;margin:-4px -4px -4px 0}}' +
			'.kpi-popover{position:fixed;z-index:10000;width:340px;max-width:calc(100vw - 24px);max-height:min(70vh,520px);overflow:auto;background:#fff;color:#334155;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 12px 32px rgba(15,23,42,.18);padding:14px 16px;font:12px/1.5 Inter,system-ui,sans-serif;text-transform:none;letter-spacing:normal;text-align:left;white-space:normal}' +
			'.dark .kpi-popover{background:#0f172a;color:#cbd5e1;border-color:#1e293b}' +
			'.kpi-popover h4{margin:0 26px 8px 0;font-size:13px;font-weight:700;color:#0f172a}' +
			'.dark .kpi-popover h4{color:#f8fafc}' +
			'.kpi-popover dl{margin:0}.kpi-popover dt{margin-top:8px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b}' +
			'.kpi-popover dd{margin:2px 0 0}.kpi-popover .kpi-formula,.kpi-popover .kpi-example{display:block}.kpi-popover .kpi-formula{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:11px;background:#f1f5f9;border-radius:6px;padding:4px 6px}' +
			'.dark .kpi-popover .kpi-formula{background:#1e293b}' +
			'.kpi-popover .kpi-example{background:#eef2ff;border-radius:6px;padding:4px 6px;color:#3730a3}' +
			'.dark .kpi-popover .kpi-example{background:rgba(67,56,202,.25);color:#c7d2fe}' +
			'.kpi-popover .kpi-close{position:absolute;top:8px;right:8px;width:28px;height:28px;border:0;background:transparent;border-radius:8px;color:#94a3b8;font-size:18px;line-height:1;cursor:pointer}' +
			'.kpi-popover .kpi-close:hover{background:rgba(148,163,184,.15);color:#475569}';
		var style = document.createElement('style');
		style.id = 'b2b-kpi-help-styles';
		style.textContent = css;
		document.head.appendChild(style);
	}

	function render(id, periodOverride) {
		var l = lang();
		var k = get(id);
		var t = k[l] || k.es;
		var L = dict.meta.labels[l];
		var period = (dict.meta.periods[periodOverride || k.period] || {})[l] || '';
		var source = (k.source || []).map(function (s) { return (dict.meta.sources[s] || {})[l] || s; }).join(' ');
		var rows = [
			[L.what, esc(t.what)],
			[L.formula, '<span class="kpi-formula">' + esc(t.formula) + '</span>'],
			[L.period, esc(period)],
			[L.interpretation, esc(t.interpretation)],
			[L.decision, esc(t.decision)]
		];
		if (t.example) rows.push([L.example, '<span class="kpi-example">' + esc(t.example) + '</span>']);
		if (t.evaluation) rows.push([L.evaluation, esc(t.evaluation)]);
		rows.push([L.source, esc(source)]);
		return '<button type="button" class="kpi-close" aria-label="' + (l === 'en' ? 'Close' : 'Cerrar') + '">×</button>' +
			'<h4>' + esc(t.name) + '</h4><dl>' +
			rows.map(function (r) { return '<dt>' + r[0] + '</dt><dd>' + r[1] + '</dd>'; }).join('') + '</dl>';
	}

	function position(trigger) {
		var r = trigger.getBoundingClientRect();
		var vw = window.innerWidth, vh = window.innerHeight, m = 12, gap = 8;
		var below = vh - r.bottom - gap - m, above = r.top - gap - m;
		popover.style.maxHeight = '';
		var pw = popover.offsetWidth, ph = popover.offsetHeight;
		var left = Math.min(Math.max(m, r.left + r.width / 2 - pw / 2), vw - pw - m);
		var top;
		// Nunca tapa la fila del indicador: va abajo, arriba, o en el lado con más espacio con desplazamiento interno.
		if (ph <= below) top = r.bottom + gap;
		else if (ph <= above) top = r.top - gap - ph;
		else if (below >= above) { popover.style.maxHeight = below + 'px'; top = r.bottom + gap; }
		else { popover.style.maxHeight = above + 'px'; top = m; }
		popover.style.left = left + 'px';
		popover.style.top = top + 'px';
	}

	function show(trigger) {
		var id = trigger.getAttribute('data-kpi-ref');
		if (!get(id)) return;
		clearTimeout(hideTimer);
		if (activeTrigger && activeTrigger !== trigger) activeTrigger.setAttribute('aria-expanded', 'false');
		if (!popover) {
			popover = document.createElement('div');
			popover.className = 'kpi-popover';
			popover.id = 'kpi-popover';
			popover.setAttribute('role', 'dialog');
			popover.addEventListener('mouseenter', function () { clearTimeout(hideTimer); });
			popover.addEventListener('mouseleave', scheduleHide);
			popover.addEventListener('click', function (e) {
				e.stopPropagation();
				if (e.target.closest('.kpi-close')) hide();
			});
			document.body.appendChild(popover);
		}
		// Una pantalla puede declarar el período real de sus cálculos (p. ej. historial completo).
		var scope = trigger.closest('[data-kpi-period]');
		popover.innerHTML = render(id, scope ? scope.getAttribute('data-kpi-period') : null);
		popover.setAttribute('aria-label', text(id, 'name'));
		popover.style.display = 'block';
		position(trigger);
		activeTrigger = trigger;
		trigger.setAttribute('aria-expanded', 'true');
	}

	function hide() {
		clearTimeout(hideTimer);
		if (popover) popover.style.display = 'none';
		if (activeTrigger) activeTrigger.setAttribute('aria-expanded', 'false');
		activeTrigger = null;
	}

	function scheduleHide() {
		clearTimeout(hideTimer);
		hideTimer = setTimeout(hide, 180);
	}

	function makeButton(id) {
		var b = document.createElement('button');
		b.type = 'button';
		b.className = 'kpi-info-btn';
		b.innerHTML = INFO_SVG;
		configureButton(b, id);
		return b;
	}

	function configureButton(b, id) {
		b.classList.add('kpi-info-btn');
		b.setAttribute('data-kpi-ref', id);
		b.setAttribute('aria-haspopup', 'dialog');
		b.setAttribute('aria-expanded', 'false');
		b.setAttribute('aria-label', dict.meta.labels[lang()].info + ': ' + text(id, 'name'));
		b.removeAttribute('title');
		b.removeAttribute('onclick');
	}

	// Elimina el tooltip nativo de la tarjeta contenedora para no duplicar la explicación.
	function stripCardTitle(el) {
		var card = el.parentElement;
		for (var i = 0; card && i < 6; i++, card = card.parentElement) {
			if (card.hasAttribute('title')) { card.removeAttribute('title'); break; }
		}
	}

	function enhance(root) {
		if (!dict) return;
		var l = lang();
		(root || document).querySelectorAll('[data-kpi]').forEach(function (el) {
			var id = el.getAttribute('data-kpi');
			if (!get(id)) return;
			var mode = el.getAttribute('data-kpi-label');
			if (el.tagName === 'BUTTON') {
				// Botón de información ya existente: se reutiliza como disparador.
				if (el.getAttribute('data-kpi-ready') !== l) {
					var tip = el.querySelector('.invisible, [class*="group-hover/tip"]');
					if (tip) tip.remove();
					configureButton(el, id);
					el.setAttribute('data-kpi-ready', l);
				}
				return;
			}
			if (mode !== 'none') {
				var label = text(id, mode === 'name' ? 'name' : 'short', l);
				var node = el.firstChild && el.firstChild.nodeType === 3 ? el.firstChild : null;
				if (node) node.nodeValue = label; else el.insertBefore(document.createTextNode(label), el.firstChild);
			}
			var btn = el.querySelector(':scope > .kpi-info-btn');
			if (!btn) {
				el.appendChild(makeButton(id));
				stripCardTitle(el);
			} else {
				configureButton(btn, id);
			}
			el.setAttribute('data-kpi-ready', l);
		});
	}

	function scheduleScan() {
		clearTimeout(scanTimer);
		scanTimer = setTimeout(function () { enhance(document); }, 120);
	}

	function bindEvents() {
		document.addEventListener('click', function (e) {
			var trigger = e.target.closest ? e.target.closest('.kpi-info-btn[data-kpi-ref]') : null;
			if (trigger) {
				e.preventDefault();
				e.stopPropagation();
				if (activeTrigger === trigger && popover && popover.style.display === 'block') hide();
				else show(trigger);
				return;
			}
			if (popover && !popover.contains(e.target)) hide();
		}, true);
		if (canHover) {
			document.addEventListener('mouseover', function (e) {
				var trigger = e.target.closest ? e.target.closest('.kpi-info-btn[data-kpi-ref]') : null;
				if (trigger) show(trigger);
			});
			document.addEventListener('mouseout', function (e) {
				var trigger = e.target.closest ? e.target.closest('.kpi-info-btn[data-kpi-ref]') : null;
				if (trigger && !trigger.contains(e.relatedTarget)) scheduleHide();
			});
		}
		document.addEventListener('focusin', function (e) {
			if (e.target.matches && e.target.matches('.kpi-info-btn[data-kpi-ref]') && e.target.matches(':focus-visible')) show(e.target);
		});
		document.addEventListener('keydown', function (e) { if (e.key === 'Escape') hide(); });
		window.addEventListener('resize', hide);
		window.addEventListener('scroll', function () { if (activeTrigger) position(activeTrigger); }, true);
		new MutationObserver(function (muts) {
			for (var i = 0; i < muts.length; i++) {
				if (muts[i].addedNodes.length && !(popover && popover.contains(muts[i].target))) { scheduleScan(); return; }
			}
		}).observe(document.body, { childList: true, subtree: true });
	}

	function init() {
		injectStyles();
		var src = DICTIONARY_URL || window.B2B_KPI_DICTIONARY_URL || '/data/b2b-kpi-dictionary.json';
		fetch(src, { credentials: 'same-origin' })
			.then(function (r) { return r.json(); })
			.then(function (d) {
				dict = d;
				window.dispatchEvent(new CustomEvent('b2b:kpi-dictionary-ready', { detail: d }));
				enhance(document);
				bindEvents();
			})
			.catch(function (err) { console.warn('[KPI] No se pudo cargar el diccionario de indicadores', err); });
	}

	window.B2BKpiHelp = {
		get: get,
		name: function (id, l) { return text(id, 'name', l); },
		short: function (id, l) { return text(id, 'short', l); },
		refresh: function () { hide(); document.querySelectorAll('[data-kpi-ready]').forEach(function (el) { el.removeAttribute('data-kpi-ready'); }); enhance(document); },
		dictionary: function () { return dict; }
	};

	if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
	else init();
})();
