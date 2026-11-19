import type {Bootstrap} from '../types';
let boot:Promise<Bootstrap>|undefined;
export function bootstrap():Promise<Bootstrap>{
  return boot??=fetch('/api/bootstrap',{headers:{Accept:'application/json'}}).then(async r=>{if(!r.ok)throw new Error('Não foi possível carregar a base.');return r.json()}).catch(e=>{boot=undefined;throw e});
}
