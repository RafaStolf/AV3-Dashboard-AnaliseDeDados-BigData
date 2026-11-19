export type Year = 1991 | 2000 | 2010;
export type Metric = 'idhm' | 'idhm_educacao' | 'idhm_renda' | 'idhm_longevidade' | 'renda_per_capita';
export interface Territory { code: string; name: string; uf: string; region: string }
export interface Observation extends Territory { year: Year; idhm: number | null; idhm_educacao: number | null; idhm_renda: number | null; idhm_longevidade: number | null; renda_per_capita: number | null }
export interface ViewState { v: 1; year: Year; metric: Metric; region: string; uf: string; a: string; b: string }
export interface Growth { code: string; name: string; uf: string; start: number; end: number; delta: number }
export interface UfSummary {uf:string;n:number;count:number;median:number|null}
export interface Dashboard {
  year: Year; metric: Metric; rows: Observation[];
  summary: {count: number; valid: number; missing: number; median: number | null};
  history: {year: Year; value: number | null; n: number}[];
  selectedHistory: Observation[]; growth: Growth[];
  distribution: {name: string; n: number; values: number[]}[];
  ufSummary: UfSummary[];
  association: {r: number | null; n: number};
  provenance: {source: string; url: string; years: Year[]; incomeUnit: string};
}
export interface Page {slug: string; category: string; title: string; summary: string; order: number; updated_at: string; author: string; chart: string | null; dataset_sha256: string | null; url: string; html: string; text: string}
export interface Bootstrap { territories: Territory[]; years: Year[]; observations: number; dataset: {name: string; sha256: string; imported_at: string; manifest: Record<string, unknown>} | null }
