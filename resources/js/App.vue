<script setup lang="ts">
import {ref,computed,onMounted,onBeforeUnmount} from 'vue';
import {useRoute} from 'vue-router';
import {navigationError} from './lib/navigation';
import {PhHouse,PhArrowsLeftRight,PhChartLineUp,PhChartBar,PhChartScatter,PhArticle,PhDatabase,PhBookOpen,PhInfo,PhList,PhX,PhArrowUpRight} from '@phosphor-icons/vue';
const route=useRoute();
const open=ref(false);
const reloadPage=()=>window.location.reload();
const mobile=ref(false);
const media=matchMedia('(max-width: 780px)');
const resize=()=>{mobile.value=media.matches;if(!mobile.value)open.value=false};
const escape=(event:KeyboardEvent)=>{if(event.key==='Escape')open.value=false};
onMounted(()=>{resize();media.addEventListener('change',resize);window.addEventListener('keydown',escape)});
onBeforeUnmount(()=>{media.removeEventListener('change',resize);window.removeEventListener('keydown',escape)});
const links=[{to:'/',label:'Panorama',icon:PhHouse},{to:'/comparar',label:'Comparar',icon:PhArrowsLeftRight},{to:'/evolucao',label:'Evolução',icon:PhChartLineUp},{to:'/distribuicao',label:'Distribuições',icon:PhChartBar},{to:'/associacao',label:'Renda e educação',icon:PhChartScatter},{to:'/analises',label:'Análises',icon:PhArticle},{to:'/dados',label:'Dados',icon:PhDatabase},{to:'/docs',label:'Documentação',icon:PhBookOpen},{to:'/sobre',label:'Sobre o projeto',icon:PhInfo}];
const active=(path:string)=>path==='/'?route.path==='/':route.path.startsWith(path);
const period=computed(()=>['/','/comparar','/evolucao','/distribuicao','/associacao','/dados'].includes(route.path));
</script>
<template>
  <a class="skip-link" href="#main">Ir para o conteúdo</a>
  <header class="mobile-header"><RouterLink to="/" class="mobile-brand">Brasil em Perspectiva</RouterLink><button class="icon-button" @click="open=!open" :aria-expanded="open" aria-controls="sidebar" :aria-label="open?'Fechar navegação':'Abrir navegação'"><component :is="open?PhX:PhList" :size="26"/></button></header>
  <div v-if="open" class="mobile-scrim" @click="open=false"></div>
  <aside id="sidebar" class="sidebar" :class="{open}" :inert="mobile&&!open||undefined">
    <RouterLink to="/" class="brand" @click="open=false"><span>Brasil em<br>Perspectiva</span><small>TERRITÓRIOS EM FOCO</small></RouterLink>
    <p class="sidebar-intro">Desenvolvimento humano<br>nos municípios brasileiros</p>
    <nav aria-label="Navegação principal"><RouterLink v-for="item in links" :key="item.to" :to="{path:item.to,query:period?route.query:{}}" :class="{active:active(item.to)}" :aria-current="active(item.to)?'page':undefined" @click="open=false"><component :is="item.icon" :size="23" weight="regular"/><span>{{item.label}}</span></RouterLink></nav>
    <div class="sidebar-bottom"><span class="eyebrow">UM OLHAR HISTÓRICO</span><p>1991 <span>·</span> 2000 <span>·</span> 2010</p><a href="https://www.atlasbrasil.org.br/" target="_blank" rel="noopener noreferrer">Conheça o Atlas Brasil <PhArrowUpRight :size="15"/></a></div>
  </aside>
  <div class="site-body"><main id="main" tabindex="-1"><div v-if="navigationError" class="notice" role="alert">{{navigationError}} <button class="text-button" @click="reloadPage">Recarregar</button></div><RouterView/></main>
    <footer class="site-footer"><p><strong>Fonte:</strong> <a href="https://www.atlasbrasil.org.br/" target="_blank" rel="noopener noreferrer">Atlas Brasil</a> — PNUD, Ipea e Fundação João Pinheiro <span class="footer-years">· 1991, 2000 e 2010</span></p><RouterLink to="/docs">Fontes e metodologia <PhArrowUpRight :size="14"/></RouterLink><small>Projeto acadêmico independente. Dados históricos, não um retrato atual dos municípios.</small></footer>
  </div>
</template>
