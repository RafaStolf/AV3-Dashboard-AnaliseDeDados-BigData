import {describe,it,expect} from 'vitest';
import {parseState,serializeState,defaults} from './view-state';
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
});
