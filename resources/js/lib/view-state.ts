import type { ViewState, Metric, Year } from '../types';
export const metrics: Record<Metric, string> = { idhm: 'IDHM', idhm_educacao: 'IDHM Educação', idhm_renda: 'IDHM Renda', idhm_longevidade: 'IDHM Longevidade', renda_per_capita: 'Renda per capita' };
export const regions = ['Norte', 'Nordeste', 'Centro-Oeste', 'Sudeste', 'Sul'];
export const defaults: ViewState = {v: 1, year: 2010, metric: 'idhm', region: '', uf: '', a: '3550308', b: '2927408'};
export function parseState(query: string): ViewState {
  const p = new URLSearchParams(query);
  if (p.has('v') && p.get('v') !== '1') return {...defaults};
  const code = (k: 'a'|'b') => {
    const value = p.get(k);
    return value !== null && (value === '' || /^\d{7}$/.test(value)) ? value : defaults[k];
  };
  return { v: 1, year: [1991,2000,2010].includes(Number(p.get('year'))) ? Number(p.get('year')) as Year : 2010,
    metric: Object.hasOwn(metrics, p.get('metric') ?? '') ? p.get('metric') as Metric : 'idhm',
    region: regions.includes(p.get('region') ?? '') ? p.get('region')! : '',
    uf: /^[A-Z]{2}$/.test(p.get('uf') ?? '') ? p.get('uf')! : '', a: code('a'), b: code('b') };
}
export function resolveMunicipalities(state: Pick<ViewState,'a'|'b'>, codes: string[]) {
  const requestedA = state.a || defaults.a;
  const a = codes.includes(requestedA) ? requestedA : codes[0] ?? '';
  const requestedB = state.b || defaults.b;
  if (codes.length < 2) return {a, b: ''};
  if (codes.includes(requestedB) && (state.b || requestedB !== a)) return {a, b: requestedB};
  return {a, b: codes.find(code => code !== a) ?? ''};
}
export function serializeState(s: ViewState): string {
  const p = new URLSearchParams({v:'1', year:String(s.year), metric:s.metric});
  for (const k of ['region','uf'] as const) if(s[k]) p.set(k, s[k]);
  p.set('a', s.a);
  p.set('b', s.b);
  return p.toString();
}
export const number = (v: number | null | undefined, digits=3) => v == null ? '—' : v.toLocaleString('pt-BR',{minimumFractionDigits:digits,maximumFractionDigits:digits});
export const formatMetric = (v: number | null | undefined, m: Metric) => m === 'renda_per_capita' ? (v == null ? '—' : v.toLocaleString('pt-BR',{style:'currency',currency:'BRL'})) : number(v);
