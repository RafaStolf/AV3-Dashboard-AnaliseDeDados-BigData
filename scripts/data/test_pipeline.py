"""Regression checks for data loss, corrupt snapshots and invalid source joins."""
import csv
import json
from pathlib import Path
import tempfile
import unittest

import build_atlas as pipeline


class PipelineTests(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        with (pipeline.DATA / 'atlas_municipios.csv').open(encoding='utf-8', newline='') as handle:
            cls.rows = list(csv.DictReader(handle))

    def test_delivered_panel(self):
        report = pipeline.validate_rows(self.rows)
        self.assertEqual(report['rows'], 16695)
        self.assertEqual(report['missing_by_column'], {column: 3 for column in pipeline.SERIES})
        missing = [r for r in self.rows if any(r[c] == '' for c in pipeline.SERIES)]
        self.assertEqual({r['codigo_ibge'] for r in missing}, {'2206720'})
        self.assertEqual({r['ano'] for r in missing}, {'1991', '2000', '2010'})

    def test_duplicate_key_fails(self):
        with self.assertRaisesRegex(ValueError, 'Duplicate output key'):
            pipeline.validate_rows(self.rows + [self.rows[0]])

    def test_lost_municipality_year_fails(self):
        with self.assertRaisesRegex(ValueError, 'panel size mismatch'):
            pipeline.validate_rows(self.rows[1:])

    def test_invalid_numeric_values_fail(self):
        for field, value in [('idhm', '1.001'), ('idhm', '-0.01'), ('idhm', 'nan'), ('renda_per_capita', '-1')]:
            with self.subTest(field=field, value=value):
                rows = [{**self.rows[0], field: value}, *self.rows[1:]]
                with self.assertRaisesRegex(ValueError, 'Invalid output value'):
                    pipeline.validate_rows(rows)

    def test_unmapped_source_code_fails(self):
        row = {'SERCODIGO': 'ADH_IDHM', 'NIVNOME': 'Municípios', 'TERCODIGO': '0000000', 'VALDATA': '2010-01-01', 'VALVALOR': 0.5}
        with self.assertRaisesRegex(ValueError, 'Unmapped municipal code'):
            pipeline.series_values([row], 'ADH_IDHM', {'1100015', '2206720'})

    def test_duplicate_source_key_fails(self):
        row = {'SERCODIGO': 'ADH_IDHM', 'NIVNOME': 'Municípios', 'TERCODIGO': '1100015', 'VALDATA': '2010-01-01', 'VALVALOR': 0.641}
        with self.assertRaisesRegex(ValueError, 'Duplicate municipality-year'):
            pipeline.series_values([row, row], 'ADH_IDHM', {'1100015', '2206720'})

    def test_new_source_gap_fails(self):
        with self.assertRaisesRegex(ValueError, 'Unexpected coverage gap'):
            pipeline.series_values([], 'ADH_IDHM', {'1100015', '2206720'})

    def test_corrupt_raw_cache_fails_without_network(self):
        with tempfile.TemporaryDirectory() as temp:
            directory = Path(temp)
            sources = pipeline.Sources(directory, offline=True)
            source_url = pipeline.STATES
            (directory / 'raw' / 'snapshot.json').write_bytes(b'corrupted')
            sources.index[source_url] = {'path': 'raw/snapshot.json', 'sha256': pipeline.sha256(b'original')}
            with self.assertRaisesRegex(ValueError, 'Raw checksum mismatch'):
                sources.fetch(source_url)

    def test_missing_offline_cache_fails_without_network(self):
        with tempfile.TemporaryDirectory() as temp:
            sources = pipeline.Sources(Path(temp), offline=True)
            with self.assertRaisesRegex(ValueError, 'No cached source'):
                sources.fetch(pipeline.STATES)


if __name__ == '__main__':
    unittest.main()
