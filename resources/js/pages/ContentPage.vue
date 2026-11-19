<script setup lang="ts">
import {ref,computed,watch,defineAsyncComponent,onBeforeUnmount} from 'vue';
import {useRoute} from 'vue-router';
import {PhMagnifyingGlass,PhArrowRight,PhArrowLeft,PhBookOpen,PhArrowUpRight} from '@phosphor-icons/vue';
const ArticleChart=defineAsyncComponent(()=>import('../components/ArticleChart.vue'));
import type {Page} from '../types';
const props=defineProps<{type:'docs'|'analises'}>();
const route=useRoute();const pages=ref<Page[]>([]);const loading=ref(true);const error=ref('');const q=ref('');
const categories:Record<string,{name:string;description:string}>={comecar:{name:'Comece aqui',description:'O projeto, seus objetivos e como explorar.'},dados:{name:'Base de dados',description:'Fontes, cobertura e significado dos indicadores.'},metodologia:{name:'Metodologia e cálculos',description:'As decisões por trás de cada comparação.'},modelagem:{name:'Modelagem dos dados',description:'Tabelas, relações e validação da importação.'},guia:{name:'Guia do dashboard',description:'Filtros, gráficos, links e exportações.'},desenvolvimento:{name:'Arquitetura e desenvolvimento',description:'Tecnologia, publicação e recuperação.'}};
let controller:AbortController|undefined;
onBeforeUnmount(()=>controller?.abort());
watch(()=>props.type,async()=>{controller?.abort();controller=new AbortController();const signal=controller.signal;loading.value=true;error.value='';q.value='';try{const r=await fetch('/api/content/'+props.type,{signal});if(!r.ok)throw Error();const next=await r.json();if(!signal.aborted)pages.value=next}catch{if(!signal.aborted)error.value='Não foi possível carregar o conteúdo.'}finally{if(!signal.aborted)loading.value=false}},{immediate:true});
const selected=computed(()=>pages.value.find(p=>p.slug===route.params.slug&&(props.type==='analises'||p.category===route.params.category)));
const filtered=computed(()=>{const needle=q.value.trim().toLocaleLowerCase('pt-BR');return pages.value.filter(p=>(!route.params.category||p.category===route.params.category)&&(!needle||(p.title+' '+p.summary+' '+p.text).toLocaleLowerCase('pt-BR').includes(needle)))});
const html=computed(()=>{if(!selected.value)return '';const doc=new DOMParser().parseFromString(selected.value.html,'text/html');doc.querySelectorAll('h1,h2,h3').forEach((h,i)=>h.id='secao-'+i);doc.querySelectorAll('a[href^="http"]').forEach(a=>{a.setAttribute('target','_blank');a.setAttribute('rel','noopener noreferrer')});return doc.body.innerHTML});
const toc=computed(()=>{const doc=new DOMParser().parseFromString(html.value,'text/html');return [...doc.querySelectorAll('h1,h2,h3')].map(h=>({id:h.id,title:h.textContent??''}))});
const groups=computed(()=>Object.entries(categories).map(([id,info])=>({id,...info,items:pages.value.filter(p=>p.category===id)})).filter(c=>c.items.length));
const date=(s:string)=>/^\d{4}-\d{2}-\d{2}$/.test(s)?new Date(s+'T12:00:00').toLocaleDateString('pt-BR',{day:'numeric',month:'long',year:'numeric'}):s;
const reload=()=>window.location.reload();
</script>
<template>
  <header class="page-header content-page-header"><div><p class="eyebrow">{{type==='docs'?'BASE DE CONHECIMENTO':'PERGUNTAS, MÉTODOS E PERSPECTIVAS'}}</p><h1>{{type==='docs'?'Documentação':'Análises'}}</h1><p class="subtitle">{{type==='docs'?'Conheça os dados, entenda os cálculos e explore com confiança.':'Leituras do desenvolvimento humano, com evidência e contexto.'}}</p></div><PhBookOpen v-if="type==='docs'" :size="45" weight="light" class="header-icon"/></header>
  <div v-if="loading" class="loading-state" role="status">Carregando {{type==='docs'?'documentação':'análises'}}…</div>
  <div v-else-if="error" class="error-state" role="alert"><p>{{error}}</p><button class="primary-button" @click="reload">Tentar novamente</button></div>
  <template v-else>
    <div class="content-layout" :class="{'with-doc-nav':type==='docs'}">
      <nav v-if="type==='docs'" class="docs-nav" aria-label="Categorias da documentação"><RouterLink to="/docs" class="docs-home">Visão geral</RouterLink><div v-for="group in groups" :key="group.id"><RouterLink :to="'/docs/'+group.id" class="docs-group-title">{{group.name}}</RouterLink><RouterLink v-for="p in group.items" :key="p.url" :to="p.url" :class="{selected:selected?.url===p.url}">{{p.title}}</RouterLink></div></nav>
      <div class="content-main">
        <nav class="breadcrumbs" aria-label="Localização"><RouterLink :to="type==='docs'?'/docs':'/analises'">{{type==='docs'?'Documentação':'Análises'}}</RouterLink><template v-if="route.params.category"><span>/</span><RouterLink :to="'/docs/'+route.params.category">{{categories[String(route.params.category)]?.name??route.params.category}}</RouterLink></template><template v-if="selected"><span>/</span><span aria-current="page">{{selected.title}}</span></template></nav>
        <template v-if="selected">
          <article class="document"><header class="document-header"><h2>{{selected.title}}</h2><p class="lead">{{selected.summary}}</p><p class="article-meta">{{selected.author}} <span>·</span> Atualizado em {{date(selected.updated_at)}}</p></header><details v-if="toc.length>2" class="table-of-contents" open><summary>Nesta página</summary><a v-for="item in toc" :key="item.id" :href="'#'+item.id">{{item.title}}</a></details><ArticleChart v-if="type==='analises'&&selected.chart" :type="selected.chart" :reference-hash="selected.dataset_sha256"/><div class="prose" v-html="html"></div><div class="article-end"><strong>Fonte dos indicadores</strong><p><a href="https://www.atlasbrasil.org.br/" target="_blank" rel="noopener noreferrer">Atlas Brasil — PNUD, Ipea e Fundação João Pinheiro <PhArrowUpRight :size="14"/></a></p><p>Dados censitários de 1991, 2000 e 2010.</p></div><RouterLink :to="type==='docs'?'/docs':'/analises'" class="editorial-link"><PhArrowLeft :size="18"/>{{type==='docs'?'Voltar à documentação':'Todas as análises'}}</RouterLink></article>
        </template>
        <div v-else-if="route.params.slug" class="empty-state"><h2>Página não encontrada</h2><p>Este conteúdo não está disponível ou não foi publicado.</p><RouterLink :to="type==='docs'?'/docs':'/analises'">Voltar ao índice</RouterLink></div>
        <template v-else>
          <label class="search-input docs-search"><PhMagnifyingGlass :size="22"/><input v-model="q" type="search" :placeholder="type==='docs'?'Busque por indicador, cálculo, fonte…':'Buscar uma análise…'" :aria-label="type==='docs'?'Buscar na documentação':'Buscar análises'"><kbd aria-hidden="true">BUSCAR</kbd></label>
          <template v-if="type==='docs'&&!q&&!route.params.category"><p class="content-intro">Da origem de um indicador à leitura de um gráfico: a documentação reúne as escolhas que tornam esta análise reproduzível.</p><div class="category-grid"><RouterLink v-for="group in groups" :key="group.id" :to="'/docs/'+group.id" class="category-link"><span class="eyebrow">{{group.items.length}} {{group.items.length===1?'PÁGINA':'PÁGINAS'}}</span><h2>{{group.name}} <PhArrowRight :size="22"/></h2><p>{{group.description}}</p></RouterLink></div><h2 class="index-heading">Explore a documentação</h2></template>
          <h2 v-else-if="route.params.category" class="index-heading">{{categories[String(route.params.category)]?.name??'Categoria'}}</h2>
          <p v-if="q" class="search-results" role="status">{{filtered.length}} resultado(s) para “{{q}}”</p>
          <div class="article-list"><RouterLink v-for="(p,i) in filtered" :key="p.url" :to="p.url" class="article-list-item"><span class="article-number">{{String(i+1).padStart(2,'0')}}</span><div><span class="eyebrow">{{type==='docs'?(categories[p.category]?.name??p.category):'ANÁLISE · 1991 — 2010'}}</span><h2>{{p.title}}</h2><p>{{p.summary}}</p><span v-if="type==='analises'" class="article-meta">{{p.author}} · {{date(p.updated_at)}}</span></div><PhArrowRight :size="24"/></RouterLink><p v-if="!filtered.length" class="empty-state">Nenhum conteúdo encontrado. Tente outro termo ou categoria.</p></div>
        </template>
      </div>
    </div>
  </template>
</template>
