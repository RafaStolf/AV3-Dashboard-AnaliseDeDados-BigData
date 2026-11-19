<script setup lang="ts">
import {ref,computed,watch,onBeforeUnmount} from 'vue';
import {useRoute} from 'vue-router';
import Chart from './Chart.vue';
import * as charts from '../lib/charts';
import {bootstrap} from '../lib/api';
import {number} from '../lib/view-state';
import type {Dashboard,Year} from '../types';
const props=defineProps<{type:string;referenceHash?:string|null}>();
const data=ref<Dashboard>();const year=ref<Year>(2010);const error=ref('');const hash=ref('');const route=useRoute();
let controller:AbortController|undefined;
onBeforeUnmount(()=>controller?.abort());
watch([year,()=>props.type],async()=>{
  controller?.abort();controller=new AbortController();const signal=controller.signal;error.value='';
  try{const [boot,res]=await Promise.all([bootstrap(),fetch(`/api/dashboard?year=${year.value}&a=3550308&b=2927408`,{signal})]);if(!res.ok)throw Error();const next=await res.json();if(!signal.aborted){data.value=next;hash.value=boot.dataset?.sha256??''}}catch{if(!signal.aborted)error.value='Não foi possível carregar o gráfico. Tente outro período.'}
},{immediate:true});
const option=computed(()=>{
  if(!data.value)return {};
  if(props.type==='evolution')return charts.growth(data.value,'idhm');
  if(props.type==='distribution')return charts.distribution(data.value,'idhm');
  if(props.type==='association')return charts.association(data.value);
  if(props.type==='dimensions')return charts.comparison(data.value.rows.find(r=>r.code==='3550308'),data.value.rows.find(r=>r.code==='2927408'));
  return charts.ranking(data.value.rows,'idhm');
});
const target=computed(()=>({ranking:'/',evolution:'/evolucao',distribution:'/distribuicao',association:'/associacao',dimensions:'/comparar'}[props.type]??'/'));
</script>
<template><section class="article-chart"><div class="article-chart-header"><div><span class="eyebrow">EXPLORE A EVIDÊNCIA</span><h2>Uma leitura dos dados</h2></div><label v-if="type!=='evolution'">Ano<select v-model="year"><option :value="1991">1991</option><option :value="2000">2000</option><option :value="2010">2010</option></select></label></div><p v-if="referenceHash&&hash&&referenceHash!==hash" class="notice" role="status">A base ativa difere da versão usada no texto. Consulte os hashes abaixo antes de comparar os resultados.</p><p v-if="error" role="alert">{{error}}</p><Chart v-else-if="data" :option="option" :height="350" :label="'Visualização da análise: '+props.type"/><p v-else role="status">Carregando gráfico…</p><p v-if="type==='association'&&data" class="data-note">Correlação de Pearson: {{number(data.association.r)}} · {{data.association.n.toLocaleString('pt-BR')}} pares válidos. Associação não demonstra causalidade.</p><p class="chart-source">Atlas Brasil — PNUD, Ipea e FJP · recorte nacional · {{year}}<br v-if="type==='dimensions'"><span v-if="type==='dimensions'">Comparação ilustrativa com dados reais: São Paulo (SP) e Salvador (BA).</span><span v-if="type==='evolution'"> · Diferenças entre 1991 e 2010; a seleção de ano não altera esse intervalo.</span></p><RouterLink :to="{path:target,query:{year:String(year),v:'1'}}" class="editorial-link">Explorar no dashboard →</RouterLink><details v-if="hash"><summary>Versão dos dados desta visualização</summary><p>Base ativa do gráfico:</p><code class="hash">{{hash}}</code><template v-if="referenceHash"><p>Versão de referência do texto:</p><code class="hash">{{referenceHash}}</code></template><p class="data-note">O texto registra sua versão de referência. O gráfico consulta a versão atualmente importada, identificada acima.</p></details></section></template>
