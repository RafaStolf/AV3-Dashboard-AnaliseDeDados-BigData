import {describe,it,expect} from 'vitest';
import {parseState,serializeState,defaults,resolveMunicipalities} from './view-state';
describe('links compartilháveis',()=>{
  it('valida parâmetros e versões desconhecidas',()=>{
    expect(parseState('?year=2026&metric=__proto__&region=abc&a=../../env')).toEqual(defaults);
    expect(parseState('?v=2&year=1991')).toEqual(defaults);
  });
  it('mantém o estado e uma serialização estável',()=>{
    const state={...defaults,year:1991 as const,region:'Nordeste',uf:'BA'};
    expect(parseState(serializeState(state))).toEqual(state);
    expect(serializeState(parseState(serializeState(state)))).toBe(serializeState(state));
  });
  it('preserva a ausência de um segundo município no recorte do Distrito Federal',()=>{
    const state={...defaults,uf:'DF',a:'5300108',b:''};
    expect(parseState(serializeState(state))).toEqual(state);
    expect(parseState('?uf=DF&a=5300108').b).toBe(defaults.b);
  });
  it('usa os municípios padrão para campos vazios quando disponíveis',()=>{
    const codes=['5200050',defaults.b,defaults.a];
    expect(resolveMunicipalities(parseState('?a=&b='),codes)).toEqual({a:defaults.a,b:defaults.b});
    expect(resolveMunicipalities(parseState('?a=&b='),['5300108'])).toEqual({a:'5300108',b:''});
    expect(resolveMunicipalities(defaults,[])).toEqual({a:'',b:''});
  });
});
