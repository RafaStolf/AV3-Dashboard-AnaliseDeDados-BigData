import {ref} from 'vue';
export const navigationError=ref('');
export function recoverNavigation(error:unknown,path:string):void{
  const message=error instanceof Error?error.message:String(error);
  if(/dynamically imported module|Importing a module script|loading chunk/i.test(message)){
    const key='brasil-perspectiva:reload:'+path;
    try{
      const last=Number(sessionStorage.getItem(key)??'0');
      if(Date.now()-last>60_000){
        sessionStorage.setItem(key,String(Date.now()));
        window.location.assign(path);
        return;
      }
    }catch{/* Storage may be unavailable; offer a manual retry without a reload loop. */}
  }
  navigationError.value='Não foi possível abrir esta página. Verifique sua conexão e tente recarregar.';
}
