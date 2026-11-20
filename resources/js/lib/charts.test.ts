import {init, registerMap, use} from 'echarts/core';
import {MapChart} from 'echarts/charts';
import {GeoComponent, TooltipComponent, VisualMapComponent} from 'echarts/components';
import {SVGRenderer} from 'echarts/renderers';
import {describe, expect, it} from 'vitest';
import {mapOption} from './charts';

use([MapChart, GeoComponent, TooltipComponent, VisualMapComponent, SVGRenderer]);

registerMap('brasil', {
  type: 'FeatureCollection',
  features: [{
    type: 'Feature',
    properties: {name: '35'},
    geometry: {type: 'Polygon', coordinates: [[[0, 0], [1, 0], [1, 1], [0, 1], [0, 0]]]},
  }, {
    type: 'Feature',
    properties: {name: '29'},
    geometry: {type: 'Polygon', coordinates: [[[1, 0], [2, 0], [2, 1], [1, 1], [1, 0]]]},
  }],
});

describe('mapa por UF', () => {
  it('preserva a proporção da malha ao redimensionar e trocar o recorte', () => {
    const chart = init(null, undefined, {renderer: 'svg', ssr: true, width: 800, height: 540});
    const sp = {uf: 'SP', median: 0.72, n: 645, count: 645};
    const ba = {uf: 'BA', median: 0.61, n: 417, count: 417};
    const expectUndistortedGeometry = () => {
      const [left, bottom] = chart.convertToPixel({seriesIndex: 0}, [0, 0]);
      const [right, top] = chart.convertToPixel({seriesIndex: 0}, [2, 1]);
      const width = right - left;
      const height = bottom - top;

      expect(width / height).toBeCloseTo(2);
      expect(left).toBeGreaterThanOrEqual(0);
      expect(top).toBeGreaterThanOrEqual(0);
      expect(right).toBeLessThanOrEqual(chart.getWidth());
      expect(bottom).toBeLessThanOrEqual(chart.getHeight());
    };

    try {
      chart.setOption(mapOption([sp, ba], 'idhm'), {notMerge: true});
      expectUndistortedGeometry();

      chart.resize({width: 280, height: 320});
      expectUndistortedGeometry();

      chart.setOption(mapOption([sp], 'idhm', 'SP'), {notMerge: true});
      expectUndistortedGeometry();
    } finally {
      chart.dispose();
    }
  });

  it('renderiza em cinza a UF excluída pelo filtro', () => {
    const chart = init(null, undefined, {renderer: 'svg', ssr: true, width: 500, height: 450});

    try {
      chart.setOption(mapOption([{uf: 'SP', median: 0.72, n: 645, count: 645}], 'idhm', 'SP'));
      expect(chart.renderToSVGString()).toMatch(/<path [^>]*fill="(?:#e8e4dd|rgb\(232,\s*228,\s*221\))"/);
    } finally {
      chart.dispose();
    }
  });
});
