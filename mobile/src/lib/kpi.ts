/**
 * Diccionario de indicadores compartido con la plataforma web.
 * Fuente única: public/data/b2b-kpi-dictionary.json (no duplicar definiciones aquí).
 */
import dictionary from '../../../public/data/b2b-kpi-dictionary.json';

export type KpiLang = 'es' | 'en';

export interface KpiTexts {
  short: string;
  name: string;
  what: string;
  formula: string;
  interpretation: string;
  decision: string;
  example: string;
  evaluation: string;
}

export interface KpiDefinition {
  id: string;
  category: string;
  unit: string;
  period: string;
  source: string[];
  es: KpiTexts;
  en: KpiTexts;
}

type Dictionary = {
  meta: {
    periods: Record<string, Record<KpiLang, string>>;
    sources: Record<string, Record<KpiLang, string>>;
    labels: Record<KpiLang, Record<string, string>>;
  };
  kpis: Record<string, KpiDefinition>;
};

const dict = dictionary as unknown as Dictionary;

export function getKpi(id: string): KpiDefinition | undefined {
  return dict.kpis[id];
}

export function kpiShort(id: string, lang: KpiLang = 'es'): string {
  return dict.kpis[id]?.[lang]?.short ?? id;
}

export function kpiName(id: string, lang: KpiLang = 'es'): string {
  return dict.kpis[id]?.[lang]?.name ?? id;
}

/** Secciones de la explicación en el orden en que se muestran. */
export function kpiExplanation(id: string, lang: KpiLang = 'es'): { title: string; rows: { label: string; text: string }[] } | null {
  const k = dict.kpis[id];
  if (!k) return null;
  const t = k[lang] || k.es;
  const L = dict.meta.labels[lang];
  const rows = [
    { label: L.what, text: t.what },
    { label: L.formula, text: t.formula },
    { label: L.period, text: dict.meta.periods[k.period]?.[lang] ?? '' },
    { label: L.interpretation, text: t.interpretation },
    { label: L.decision, text: t.decision },
    { label: L.example, text: t.example },
    { label: L.evaluation, text: t.evaluation },
    { label: L.source, text: k.source.map((s) => dict.meta.sources[s]?.[lang] ?? s).join(' ') },
  ].filter((r) => r.text);
  return { title: t.name, rows };
}
