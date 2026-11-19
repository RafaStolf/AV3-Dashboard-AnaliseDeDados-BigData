#!/usr/bin/env python3
"""Build the national Atlas CSV from official APIs; Python 3.10+, stdlib only."""
import argparse
import csv
import hashlib
import io
import json
import math
from pathlib import Path
import re
import time
from datetime import datetime, timezone
from urllib.parse import urljoin, urlparse
from urllib.request import Request, urlopen

ROOT = Path(__file__).resolve().parents[2]
DATA = ROOT / 'data'
API = 'https://www.ipeadata.gov.br/api/odata4/'
YEARS = (1991, 2000, 2010)
SERIES = {
    'idhm': 'ADH_IDHM',
    'idhm_educacao': 'ADH_IDHM_E',
    'idhm_renda': 'ADH_IDHM_R',
    'idhm_longevidade': 'ADH_IDHM_L',
    'renda_per_capita': 'ADH_RDPC',
}
COLUMNS = ['codigo_ibge', 'municipio', 'uf', 'regiao', 'ano', *SERIES]
REGIONS = {'Norte', 'Nordeste', 'Sudeste', 'Sul', 'Centro-Oeste'}
SIDRA = 'https://apisidra.ibge.gov.br/values/t/1301/n6/all/v/615/p/2010'
STATES = 'https://servicodados.ibge.gov.br/api/v1/localidades/estados'
CURRENT = 'https://servicodados.ibge.gov.br/api/v1/localidades/municipios'
METHOD = 'https://www.ipeadata.gov.br/doc/Metodologia%20ADH.pdf'
KNOWN_MISSING = {'2206720'}  # Nazária: present in IBGE 2010, absent in all five Ipeadata series.


def require(condition, message):
    if not condition:
        raise ValueError(message)


def utcnow():
    return datetime.now(timezone.utc).isoformat(timespec='seconds')


def sha256(content):
    return hashlib.sha256(content).hexdigest()


def write_json(path, value):
    path.write_text(json.dumps(value, ensure_ascii=False, indent=2) + '\n', encoding='utf-8')


class Sources:
    """Content-addressed raw snapshots. Existing downloads are never removed."""
    def __init__(self, directory, offline=False, refresh=False):
        self.directory = directory
        self.raw = directory / 'raw'
        self.raw.mkdir(parents=True, exist_ok=True)
        self.index_path = self.raw / 'index.json'
        self.index = json.loads(self.index_path.read_text(encoding='utf-8')) if self.index_path.exists() else {}
        self.offline, self.refresh = offline, refresh
        self.used = {}

    def fetch(self, url):
        require(urlparse(url).hostname in {
            'www.ipeadata.gov.br', 'apisidra.ibge.gov.br', 'servicodados.ibge.gov.br'
        }, f'Unexpected source host: {url}')
        if url in self.index and not self.refresh:
            entry = self.index[url]
            body = (self.directory / entry['path']).read_bytes()
            require(sha256(body) == entry['sha256'], f'Raw checksum mismatch: {url}')
        else:
            require(not self.offline, f'No cached source for offline run: {url}')
            for attempt in range(3):
                try:
                    with urlopen(Request(url, headers={'User-Agent': 'BrasilEmPerspectiva-AtlasPipeline/1.0'}), timeout=60) as response:
                        body = response.read()
                        entry = {
                            'url': url, 'resolved_url': response.url, 'downloaded_at_utc': utcnow(),
                            'content_type': response.headers.get('Content-Type'),
                            'last_modified': response.headers.get('Last-Modified'),
                            'sha256': sha256(body), 'bytes': len(body),
                        }
                    break
                except (OSError, TimeoutError):
                    if attempt == 2:
                        raise
                    time.sleep(2 ** attempt)
            extension = '.pdf' if body.startswith(b'%PDF') else '.json' if body.lstrip().startswith((b'{', b'[')) else '.html'
            entry['path'] = 'raw/' + entry['sha256'] + extension
            target = self.directory / entry['path']
            if target.exists():
                require(target.read_bytes() == body, f'Existing raw snapshot is corrupted: {target.name}')
            else:
                target.write_bytes(body)
            self.index[url] = entry
            write_json(self.index_path, self.index)
        self.used[url] = entry
        return body

    def json(self, url):
        return json.loads(self.fetch(url))

    def odata(self, path):
        url = API + path
        rows, seen = [], set()
        while url:
            require(url not in seen, 'OData pagination cycle')
            seen.add(url)
            document = self.json(url)
            require(isinstance(document.get('value'), list), f'Invalid OData response: {url}')
            rows.extend(document['value'])
            link = document.get('@odata.nextLink')
            url = urljoin(url, link) if link else None
            if url and url.startswith('http://www.ipeadata.gov.br/'):
                url = 'https://' + url[len('http://'):]
        return rows


def reference(sources):
    states = {str(x['id']): x for x in sources.json(STATES)}
    require(len(states) == 27, 'Expected 27 UFs')
    municipalities = {}
    table = sources.json(SIDRA)
    require(isinstance(table, list) and table[0]['D1C'] == 'Município (Código)', 'SIDRA schema changed')
    for row in table[1:]:
        require(row['NC'] == '6' and row['D3C'] == '2010' and row['D2C'] == '615', 'Unexpected SIDRA grain')
        code = row['D1C']
        require(re.fullmatch(r'\d{7}', code) and code not in municipalities, f'Invalid/duplicate IBGE code {code}')
        state = states[code[:2]]
        name, uf = row['D1N'].rsplit(' - ', 1)
        require(uf == state['sigla'], f'UF/code mismatch: {code}')
        municipalities[code] = {'codigo_ibge': code, 'municipio': name, 'uf': uf, 'regiao': state['regiao']['nome']}
    require(len(municipalities) == 5565, 'Expected 5,565 IBGE 2010 municipal units')
    current = {str(x['id']): x for x in sources.json(CURRENT)}
    require(not (municipalities.keys() - current.keys()), 'Historical code missing from current IBGE; explicit mapping required')
    return municipalities, current


def series_values(rows, series, reference_codes):
    result, excluded = {}, 0
    for row in rows:
        require(row['SERCODIGO'] == series, f'Series mismatch: {series}')
        if row['NIVNOME'] != 'Municípios':
            excluded += 1
            continue
        year = int(row['VALDATA'][:4])
        require(year in YEARS, f'Unexpected municipal year: {series} {year}')
        code = row['TERCODIGO']
        require(code in reference_codes, f'Unmapped municipal code: {code}')
        key = (code, year)
        require(key not in result, f'Duplicate municipality-year: {series} {key}')
        value = row['VALVALOR']
        if value is not None:
            require(isinstance(value, (int, float)) and not isinstance(value, bool) and math.isfinite(value), f'Invalid number: {key}')
            require(value >= 0 and (series == 'ADH_RDPC' or value <= 1), f'Out of range: {series} {key}')
        result[key] = value
    for year in YEARS:
        codes = {c for c, y in result if y == year}
        require(reference_codes - codes == KNOWN_MISSING, f'Unexpected coverage gap: {series} {year}: {reference_codes - codes}')
    return result, excluded


def validate_rows(rows):
    keys, codes, ufs, regions = set(), set(), set(), set()
    nulls = {c: 0 for c in SERIES}
    bounds = {c: [] for c in SERIES}
    by_year = {str(y): {'rows': 0, 'complete_rows': 0} for y in YEARS}
    geometric_error = 0.0
    for row in rows:
        code, year = row['codigo_ibge'], int(row['ano'])
        require(re.fullmatch(r'\d{7}', code) and year in YEARS, 'Invalid output code/year')
        require((code, year) not in keys, f'Duplicate output key: {code}/{year}')
        require(row['municipio'] and re.fullmatch(r'[A-Z]{2}', row['uf']) and row['regiao'] in REGIONS, 'Invalid output metadata')
        keys.add((code, year)); codes.add(code); ufs.add(row['uf']); regions.add(row['regiao'])
        values = {}
        for column in SERIES:
            value = row[column]
            if value is None or value == '':
                nulls[column] += 1
                continue
            value = float(value)
            require(math.isfinite(value) and value >= 0 and (column == 'renda_per_capita' or value <= 1), f'Invalid output value: {column}')
            bounds[column].append(value)
            values[column] = value
        by_year[str(year)]['rows'] += 1
        by_year[str(year)]['complete_rows'] += int(len(values) == len(SERIES))
        if all(c in values for c in list(SERIES)[:4]):
            geometric_error = max(geometric_error, abs(values['idhm'] - (values['idhm_educacao'] * values['idhm_renda'] * values['idhm_longevidade']) ** (1 / 3)))
    require(len(rows) == 16695 and len(codes) == 5565, 'Output national panel size mismatch')
    require(len(ufs) == 27 and regions == REGIONS, 'Output national geography mismatch')
    require(all(v['rows'] == 5565 for v in by_year.values()), 'Output year coverage mismatch')
    require(geometric_error <= 0.002, 'IDHM/component inconsistency beyond rounding tolerance')
    return {'rows': len(rows), 'municipalities': len(codes), 'ufs': len(ufs), 'regions': len(regions),
            'unique_municipality_year': True, 'by_year': by_year, 'missing_by_column': nulls,
            'ranges': {c: {'min': min(v), 'max': max(v)} for c, v in bounds.items()},
            'max_idhm_geometric_mean_absolute_error': geometric_error}


def build(args):
    DATA.mkdir(parents=True, exist_ok=True)
    sources = Sources(DATA, args.offline, args.refresh)
    municipalities, current = reference(sources)
    territories = sources.odata('Territorios')
    names = {x['TERCODIGO']: x['TERNOME'] for x in territories if x['NIVNOME'] == 'Municípios'}
    values, metadata, excluded = {}, {}, {}
    for column, series in SERIES.items():
        print(f'Loading {series}', flush=True)
        meta = sources.odata(f"Metadados('{series}')")
        require(len(meta) == 1 and meta[0]['SERCODIGO'] == series, f'Missing metadata: {series}')
        require(meta[0]['FNTSIGLA'] == 'IPEA/ADH Censo IBGE', f'Unexpected source: {series}')
        if column == 'renda_per_capita':
            require(meta[0]['UNINOME'] == 'R$, a preços do ano 2010' and '01/agosto de 2010' in meta[0]['SERCOMENTARIO'], 'Income price basis unverified')
        metadata[column] = meta[0]
        values[column], excluded[column] = series_values(sources.odata(f"Metadados('{series}')/Valores"), series, set(municipalities))
    sources.fetch(METHOD)
    sources.fetch('https://www.ipeadata.gov.br/api/')
    rows = []
    for code, municipality in sorted(municipalities.items()):
        for year in YEARS:
            rows.append({**municipality, 'ano': year, **{c: values[c].get((code, year)) for c in SERIES}})
    validation = validate_rows(rows)
    out = io.StringIO(newline='')
    writer = csv.DictWriter(out, fieldnames=COLUMNS, lineterminator='\n')
    writer.writeheader(); writer.writerows(rows)
    content = out.getvalue().encode('utf-8')
    # Validate the serialized artifact as consumed by Laravel before writing it.
    require(validate_rows(list(csv.DictReader(io.StringIO(content.decode('utf-8'))))) == validation, 'CSV round-trip mismatch')
    caveats = [
        'Nazária/PI (2206720) is in the IBGE 2010 roster but absent in all five municipal Ipeadata series for all three years; its 15 indicator cells are blank. No imputation.',
        'Municipal geography uses the roster from IBGE SIDRA table 1301, period 2010. Names are labels returned by that query at capture time and may include subsequent renamings, not guaranteed historical 2010 spellings. Earlier Atlas census indicators were spatially harmonized by the producers to 2010 areas; these are not the municipal boundaries existing in 1991 or 2000.',
        'Brasília/DF and Fernando de Noronha/PE are included as IBGE municipal-level units, without implying ordinary municipal legal status.',
        'Use the recalculated Atlas census series for temporal comparison. Do not mix with older Atlas methodologies, national global IDH, PNAD chained series, or current municipal boundaries.',
        'Income is monthly household per-capita income of people in permanent private households, in BRL at 1 August 2010 prices, as verified in ADH_RDPC metadata. No inflation conversion was performed here.',
        'UF and five-region labels come from the IBGE locality API captured at download time. Municipality codes are joined exactly as seven-character strings; no truncation, check-digit synthesis, fuzzy join or historical crosswalk was needed for this snapshot.',
        'A simple mean of municipal IDHM or income is not an official Brazil/UF/region aggregate. This CSV contains no population weights.',
        'Public API access does not itself establish a redistribution license. No specific dataset license was verified; preserve attribution and review source terms before redistribution.',
    ]
    manifest = {
        'schema_version': 1, 'dataset': 'Brasil em Perspectiva — Atlas municipal censitário',
        'generated_at_utc': utcnow(), 'generator': 'scripts/data/build_atlas.py',
        'attribution': 'Atlas do Desenvolvimento Humano no Brasil — PNUD, Ipea e Fundação João Pinheiro; Censos Demográficos IBGE; distribuição Ipeadata. Cadastro territorial: IBGE.',
        'output': {'path': 'data/atlas_municipios.csv', 'sha256': sha256(content), 'bytes': len(content), 'encoding': 'UTF-8', 'delimiter': ',', 'decimal_separator': '.', 'missing_value': '', 'columns': COLUMNS, 'primary_key': ['codigo_ibge', 'ano']},
        'coverage': {'years': list(YEARS), 'geography_year': 2010, 'expected_municipal_units': 5565, 'observed_municipal_units_per_series_year': 5564, 'complete_rows': sum(v['complete_rows'] for v in validation['by_year'].values()), 'missing_municipal_units': [municipalities[c] for c in sorted(KNOWN_MISSING)]},
        'series': metadata, 'sources': list(sources.used.values()),
        'source_paths_relative_to': 'data/', 'excluded_nonmunicipal_observations': excluded,
        'mapping': {'strategy': 'Exact seven-digit IBGE code against 2010 SIDRA roster and captured current IBGE roster', 'unmatched_indicator_codes': [], 'historical_code_overrides': [],
                    'ipeadata_name_differences': [{'codigo_ibge': c, 'ibge_sidra_2010_period': m['municipio'], 'ipeadata': names.get(c)} for c, m in sorted(municipalities.items()) if names.get(c) != m['municipio']],
                    'current_ibge_units_outside_2010_roster': [{'codigo_ibge': c, 'municipio': m['nome']} for c, m in sorted(current.items()) if c not in municipalities]},
        'validation': validation, 'comparability_and_caveats': caveats,
        'methodology': {'url': METHOD, 'spatial_harmonization': 'page 2', 'income_definition': 'Renda per capita section; also ADH_RDPC SERCOMENTARIO'},
        'license': {'identifier': None, 'status': 'Specific dataset redistribution license/terms not verified; do not infer a license from public availability.', 'source_terms_entrypoints': ['https://www.ipeadata.gov.br/', 'https://www.ibge.gov.br/']},
    }
    (DATA / 'atlas_municipios.csv').write_bytes(content)
    write_json(DATA / 'manifest.json', manifest)
    print(json.dumps(validation, ensure_ascii=False, indent=2))
    print('CSV SHA256:', sha256(content))


def verify():
    manifest = json.loads((DATA / 'manifest.json').read_text(encoding='utf-8'))
    content = (DATA / 'atlas_municipios.csv').read_bytes()
    require(sha256(content) == manifest['output']['sha256'], 'CSV checksum mismatch')
    reader = csv.DictReader(io.StringIO(content.decode('utf-8')))
    require(reader.fieldnames == COLUMNS, 'CSV column contract mismatch')
    report = validate_rows(list(reader))
    require(report == manifest['validation'], 'CSV validation does not match manifest')
    print(json.dumps(report, ensure_ascii=False, indent=2))
    print('CSV and manifest verified')


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__)
    group = parser.add_mutually_exclusive_group()
    group.add_argument('--offline', action='store_true', help='Rebuild using checksum-verified raw cache only')
    group.add_argument('--refresh', action='store_true', help='Download fresh snapshots; retain all older raw files')
    group.add_argument('--verify', action='store_true', help='Verify delivered CSV and manifest without network or raw files')
    args = parser.parse_args()
    verify() if args.verify else build(args)
