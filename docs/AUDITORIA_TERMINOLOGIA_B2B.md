# Auditoría de terminología y comprensión — Plataforma B2B Prince Distributors

Fecha: 2026-11-21 · Alcance: web (15 secciones B2B, ES/EN) y app móvil Expo.

## 1. Qué se hizo

| Área | Resultado |
|---|---|
| Diccionario centralizado | `public/data/b2b-kpi-dictionary.json` — 106 indicadores con id, nombre visible ES/EN (corto y completo), definición, fórmula, unidad, período, interpretación, decisión, ejemplo, criterio de evaluación y fuente de datos. |
| Web | `public/js/b2b-kpi-help.js`: icono de información junto a cada indicador (`data-kpi="<id>"`). Hover/foco en escritorio, toque en tablet/móvil, cierre con ✕, toque fuera o Esc. El nombre visible se toma del diccionario en el idioma activo. 124 etiquetas conectadas. |
| Móvil (Expo) | `mobile/src/lib/kpi.ts` importa el mismo JSON (Metro observa `public/data`, ver `mobile/metro.config.js`). Componente `KpiInfo` y prop `kpi` en `StatCard`. Conectado en Inicio, Rentabilidad y Ciclo de vida. |
| Glosario existente | `b2bGlossaryData` se sincroniza con el diccionario al cargar: mismo nombre, definición, fórmula, ejemplo y fuente. |
| Tooltips nativos | Se eliminaron los `title` con pseudo-SQL (`COUNT(b2b_products WHERE active = 1)`, `SUM(balance_due)`…) y los que el JS reasignaba desde la API. |

## 2. Cambios por pantalla (término anterior → nuevo)

### Resumen de ventas
| Anterior | Nuevo | Razón |
|---|---|---|
| Ventas Netas B2B | Ventas netas facturadas | Distinguir facturado de cobrado. |
| Ticket Promedio | Valor promedio por factura | Su fórmula es ventas netas / facturas válidas. |
| Variación Ventas | Crecimiento de ventas | Nombre explícito; no es margen. |
| MTD / QTD / YTD / 7D | Mes actual / Trimestre actual / Año actual / 7 días (con texto completo "…hasta hoy") | Abreviaturas en inglés. |
| Saldo vivo | Saldo pendiente de pago | Reemplazo obligatorio. |
| NC | notas de crédito | Abreviatura. |
| Pipeline de pedidos | Pedidos según su estado | Anglicismo. |
| …registradas en SQLite | (eliminado) | Término técnico. |
| Comparativa determinista | Comparación con el período anterior | Lenguaje artificial. |
| Supervisión en tiempo real… OTIF… | Descripción comercial (qué muestra, para qué sirve, qué decidir) | Descripción del tablero. |

### Clientes y almacenes
| Anterior | Nuevo | Razón |
|---|---|---|
| Tasa de Pérdida (Churn) | Tasa de pérdida de clientes | Reemplazo obligatorio. |
| Tasa de Retención vs Churn | Retención vs. pérdida de clientes | Anglicismo. |
| Almacenes Activos | Locales con compras | La fórmula cuenta locales con compras en el período. |
| Deuda Vencida | Clientes con deuda vencida | Es una cantidad de clientes, no un importe. |
| DIRECTORIO B2B | CARTERA DE CLIENTES + descripción | Claridad. |
| **Corrección de datos** | Las tarjetas "Clientes inactivos", "Locales con compras" y "Clientes con deuda vencida" mostraban otra métrica (tasa de pérdida, facturación y crecimiento de cartera) por una sobrescritura en `analytics_dashboard_scripts.blade.php`. Ahora muestran el valor de su propia fórmula. No se cambió ningún cálculo. | Inactivo ≠ perdido. |

### Productos y precios
| Anterior | Nuevo | Razón |
|---|---|---|
| SKU Activos / SKU con Venta / Sin Movimiento | Productos activos / Productos con ventas / Productos sin ventas (código interno SKU explicado en la ayuda) | Abreviatura. |
| Utilidad Bruta | Ganancia bruta | Terminología uniforme; distinguir de ganancia neta. |
| Ventas − COGS | Ventas − costo de los productos | Abreviatura. |
| Escalas por Volumen (Tier Pricing) | Precios según cantidad comprada | Reemplazo obligatorio. |
| Motor/Regla determinista de precios | Precios calculados automáticamente según las reglas comerciales | Lenguaje artificial. |
| Cumpl. Meta | Productos en margen meta | Abreviatura ambigua. |

### Cotizaciones y pedidos
| Anterior | Nuevo | Razón |
|---|---|---|
| Pedidos Firmes (contaba todos los pedidos) | Pedidos recibidos (cualquier estado) | El nombre no coincidía con la fórmula. |
| Cumplimiento (pedidos confirmados/total) | Pedidos en firme (%) | Medía estados, no puntualidad. |
| Ticket Prom. | Valor promedio por pedido | Pedido ≠ factura. |
| Pendientes % | Pedidos pendientes | Claridad. |
| Pipeline comercial | Oportunidades de venta en curso | Reemplazo obligatorio. |
| Embudo Comercial B2B (Funnel) | Etapas del proceso de venta | Reemplazo obligatorio. |
| Backlog logístico | Pedidos pendientes de despacho | Anglicismo. |
| OTIF estándar | Estándar de servicio / Entregas completas y a tiempo | Reemplazo obligatorio. |

### Facturación y cobranza
| Anterior | Nuevo | Razón |
|---|---|---|
| Recaudado | Dinero cobrado | Distinguir cobrado de facturado. |
| Tasa Cobranza | Tasa de cobro | Claridad. |
| Mora Vencida / Mora % | Deuda vencida / Facturas vencidas | Claridad. |
| DSO / DSO Meta | Días promedio de cobro / Meta de días de cobro | Abreviatura en inglés. |
| Cartera viva | Cuentas pendientes de cobro | Reemplazo obligatorio. |
| Antigüedad de Saldos (Aging) | Antigüedad de la deuda por días de atraso | Anglicismo. |
| Sin impuestos − NC / Total NC | Sin impuestos, menos notas de crédito / Total notas de crédito | Abreviatura. |

### Vendedores y territorios
| Anterior | Nuevo | Razón |
|---|---|---|
| Venta / Rep | Venta promedio por vendedor | Abreviatura. |
| Cumpl. Meta | Cumplimiento de meta (meta ≠ proyección, aclarado) | Diferenciar meta de proyección. |
| Rank | Posición | Reemplazo obligatorio. |
| Auditoría Inmutable / Trazabilidad | Historial protegido / Historial de cambios | Lenguaje técnico. |

### Rentabilidad comercial
| Anterior | Nuevo | Razón |
|---|---|---|
| Costo Ventas (COGS) / COGS estándar / Ratio COGS | Costo de los productos vendidos / Costo vigente al facturar / Costo sobre ventas | Reemplazo obligatorio. |
| Var. Margen (fórmula: crecimiento de la utilidad) | Crecimiento de la ganancia | Confundía margen con crecimiento. |
| Margen Contr. | Margen de contribución % (aclarado: no es ganancia neta) | Abreviatura. |
| Mes (MTD) … Año (YTD) | Mes actual / Trimestre actual / Año actual | Reemplazo obligatorio. |

### Inteligencia comercial (reportes)
| Anterior | Nuevo | Razón |
|---|---|---|
| Motor Analítico (B2B) | Sistema de análisis comercial / Actualizar análisis | Reemplazo obligatorio. |
| Umbrales | Límites de alerta / Límites para generar alertas | Reemplazo obligatorio. |
| Severidad | Nivel de gravedad (también en el CSV exportado) | Reemplazo obligatorio. |
| Fulfillment (fórmula OTIF) | Entregas completas y a tiempo | Reemplazo obligatorio. |
| Efectividad pipeline | Efectividad de oportunidades | Anglicismo. |
| Cross-Selling | Venta de productos complementarios | Reemplazo obligatorio. |

### Configuración
| Anterior | Nuevo | Razón |
|---|---|---|
| SQLite 3 (WAL Mode) · ACID · Inmutable (Audited Log) · RBAC | Respaldo activo y protegido · Registro completo, sin pérdidas · Protegida contra cambios · Por rol de usuario y sesión segura | Términos técnicos fuera de la interfaz comercial. |
| Pedido Mínimo Mayorista (MOQ $) | Pedido mínimo requerido ($) | Reemplazo obligatorio. |

### Horarios, operaciones, personal, asistencia, solicitudes
| Anterior | Nuevo | Razón |
|---|---|---|
| Personal Prog. / Horas Prog. / Costo Estim. / Déficit Horas | Personal programado / Horas programadas / Costo laboral estimado / Horas sin cubrir | Abreviaturas. |
| FEFO (insignia, tarjeta, certificación) | Rotación por vencimiento (primero en vencer, primero en salir) | Sigla en inglés. |
| Stock mínimo / Bajo umbral (target <0.25%) | Existencia mínima / Bajo el límite (meta < 0,25%) | Anglicismos. |
| Benchmark mayorista | Referencia del sector mayorista | Anglicismo. |
| SLA respuesta / Cumplimiento SLA | Plazo de respuesta / Respondidas en menos de 24 horas | Sigla. |
| Tardanzas / Resolución | Llegadas tarde / Respuesta en 24 horas | Claridad. |
| …de forma determinista | …según la demanda | Lenguaje artificial. |
| Cargos de demo en inglés (Lead Merchandiser, Key Account Executive…) | Reponedor líder, Ejecutivo de cuentas clave… | Inglés innecesario. |

### Ciclo de vida del producto
| Anterior | Nuevo | Razón |
|---|---|---|
| Sell-through / GMROI / GMROS / DIO | Porcentaje vendido del inventario / Ganancia por dólar en inventario / Ganancia por metro cuadrado / Días de inventario | Siglas en inglés. |
| SKUs en Crecimiento/Madurez/Declive | Productos en crecimiento / estables / en declive | Abreviatura. |
| Riesgo Quiebre Stock / ROP | Riesgo de quedarse sin inventario / punto de reposición | Anglicismos. |
| Markdown / OC / Horeca | Rebaja de precio / orden de compra / hoteles y restaurantes | Anglicismos y siglas. |
| Precisión Pronóstico | Precisión de la proyección (proyección ≠ meta, aclarado) | Reemplazo obligatorio. |

### Segunda pasada (residuos)
| Pantalla | Anterior | Nuevo | Razón |
|---|---|---|---|
| Inteligencia comercial | Cargando / Sin recomendaciones de cross-selling | …de venta de productos complementarios | Reemplazo obligatorio. |
| Inteligencia comercial | Umbral Mínimo de Margen Bruto / Umbral: | Límite mínimo de margen bruto para alertar / Límite de alerta: | Reemplazo obligatorio. |
| Glosario | MOQ, Lead Time, DIO, OTIF en consejos y ejemplos | Pedido mínimo requerido, tiempo de reposición, días de inventario, entregas completas y a tiempo | Siglas sin explicar (la sigla técnica se conserva solo en la insignia "nombre técnico"). |
| Tour guiado | Filtros Temporales (7D, MTD, QTD, YTD) | Filtros de período (7 días, mes, trimestre y año actual hasta hoy) | Abreviaturas. |
| Gráficos (analítica) | COGS ($) / Monto en Funnel ($) | Costo de los productos vendidos ($) / Monto en etapas del proceso de venta ($) | Reemplazo obligatorio. |
| Ciclo de vida | Ventas MTD / COGS: | Ventas del mes actual / Costo de los productos: | Reemplazo obligatorio. |

Revalidación: 17 rutas × ES/EN → 200, 0 términos de la lista obligatoria ni técnicos visibles, 0 errores JS.

## 3. Validación (Playwright, servidor local con el seed de demo)

- 15 rutas `/b2b/<sección>` en ES y EN: todas responden 200.
- 124 iconos de información: en escritorio (1440 px) cada uno abre su explicación al pasar el cursor, dentro de la pantalla y con al menos 6 apartados (ES y EN).
- Tablet (820 px) y móvil (390 px): se abre al tocar y se cierra al tocar fuera; sin desplazamiento horizontal.
- Términos técnicos o en inglés visibles: de 182 apariciones (sección × idioma) a 14; las restantes son palabras normales del modo inglés, códigos de producto (`SKU-CER-008`) y textos de la caché local de la base (el seed de producción no los contiene).
- Textos cortados: de 30 a 4 (nombres largos de clientes en la tabla de modo inglés).
- Errores JS: solo el previo `Cannot read properties of undefined (reading 'options')`, que ya existía antes de los cambios.

## 4. Pendientes detectados (no modificados: requieren revisión de cálculos)

1. **Períodos**: los módulos analíticos de Pedidos, Facturación, Vendedores, Rentabilidad y Reportes calculan sobre todo el historial aunque la pantalla tenga filtro de fechas. En Pedidos y Facturación la ayuda lo declara ("Todo el historial registrado"); en las otras pantallas conviene decidir si los cálculos deben respetar el filtro.
2. **Valores fijos de demo**: "Entregas completas y a tiempo" usa 96,5% fijo; la fecha de corte de deuda vencida es `2026-11-16`.
3. **Días promedio de cobro** usa saldos de todo el historial con los días del período elegido.
4. **Traducción al inglés**: los indicadores, descripciones y botones de período tienen inglés completo; gran parte del resto de la interfaz web (tablas, formularios, modales) sigue solo en español porque no usa el sistema de traducción `data-i18n`.
5. **App móvil**: sin `node_modules` instalado no se ejecutó `tsc`; se verificó la sintaxis de los archivos modificados con esbuild.
