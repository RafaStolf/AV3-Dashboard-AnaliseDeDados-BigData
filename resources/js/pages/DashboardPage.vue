<script setup lang="ts">
import {ref,computed,watch,onBeforeUnmount} from 'vue';
import {useRoute,useRouter} from 'vue-router';
import {PhArrowRight,PhDownloadSimple,PhLink,PhArrowCounterClockwise,PhMagnifyingGlass,PhCaretDown,PhFunnel,PhWarningCircle} from '@phosphor-icons/vue';
import Chart from '../components/Chart.vue';
import {bootstrap} from '../lib/api';
import {metrics,regions,parseState,serializeState,resolveMunicipalities,formatMetric,number,defaults} from '../lib/view-state';
import * as charts from '../lib/charts';
import type {Dashboard,Bootstrap,ViewState,Year,Observation} from '../types';
const route=useRoute(),router=useRouter();
const boot=ref<Bootstrap>();const data=ref<Dashboard>();const displayedState=ref<ViewState>({...defaults});const loading=ref(true);const error=ref('');const notice=ref('');const copied=ref(false);const search=ref('');const page=ref(1);const bottom=ref(false);const filtersOpen=ref(false);
const state=computed(()=>parseState(new URLSearchParams(Object.entries(route.query).map(([k,v])=>[k,String(v??'')])).toString()));
const section=computed(()=>route.path==='/comparar'?'compare':route.path==='/evolucao'?'evolution':route.path==='/distribuicao'?'distribution':route.path==='/associacao'?'association':route.path==='/dados'?'data':'overview');
const titles:Record<string,string>={compare:'Compare os territórios',overview:'Um país, muitas perspectivas',evolution:'O desenvolvimento ao longo do tempo',distribution:'As diferenças dentro das regiões',association:'Renda e educação em perspectiva',data:'Os dados por trás das perspectivas'};
const subtitles:Record<string,string>={compare:'Desenvolvimento humano nos municípios brasileiros',overview:'Explore o desenvolvimento humano e suas diferenças territoriais',evolution:'Trajetórias municipais entre os Censos de 1991, 2000 e 2010',distribution:'Como os indicadores se distribuem entre os municípios brasileiros',association:'Explore a associação entre renda per capita e IDHM Educação',data:'Consulte, filtre e baixe a base utilizada neste projeto'};
const territories=computed(()=>boot.value?.territories??[]);
const scoped=computed(()=>territories.value.filter(t=>(!state.value.region||t.region===state.value.region)&&(!state.value.uf||t.uf===state.value.uf)));
const ufs=computed(()=>[...new Set(territories.value.filter(t=>!state.value.region||t.region===state.value.region).map(t=>t.uf))].sort());
const selectedA=computed(()=>data.value?.rows.find(r=>r.code===displayedState.value.a));
const selectedB=computed(()=>data.value?.rows.find(r=>r.code===displayedState.value.b));
const comparisonNames=computed(()=>[selectedA.value?.name,selectedB.value?.name].filter(Boolean).join(' × '));
const regionLabel=computed(()=>state.value.uf||state.value.region||'Brasil');
const displayedRegionLabel=computed(()=>displayedState.value.uf||displayedState.value.region||'Brasil');
const refreshingRecorte=computed(()=>loading.value&&!!data.value&&serializeState(state.value)!==serializeState(displayedState.value));
let request:AbortController|undefined;
async function load(){
  request?.abort();request=new AbortController();const signal=request.signal;loading.value=true;error.value='';
  try{
    boot.value=await bootstrap();
    if(signal.aborted)return;
    const s={...state.value};
    let correction='';
    if(s.uf&&!ufs.value.includes(s.uf)){
      correction=s.region
        ? `A UF ${s.uf} está fora do recorte. Exibindo todos os municípios da região ${s.region}.`
        : `A UF ${s.uf} não consta na base. Exibindo todo o Brasil.`;
      s.uf='';
    }
    const codes=territories.value.filter(t=>(!s.region||t.region===s.region)&&(!s.uf||t.uf===s.uf)).map(t=>t.code);
    Object.assign(s,resolveMunicipalities(s,codes));
    if(serializeState(s)!==serializeState(state.value)){
      if(!correction&&(section.value==='compare'||section.value==='evolution'))correction='Os municípios selecionados foram ajustados ao filtro.';
      const path=route.path;
      await update(s,true);
      if(route.path===path&&serializeState(state.value)===serializeState(s))notice.value=correction;
      return;
    }
    const res=await fetch('/api/dashboard?'+serializeState(s),{signal,headers:{Accept:'application/json'}});
    if(!res.ok)throw new Error(res.status===429?'Muitas consultas em sequência. Aguarde um momento e tente novamente.':'Não foi possível consultar os indicadores. Tente novamente.');
    const result:Dashboard=await res.json();
    if(signal.aborted)return;
    displayedState.value={...s};
    data.value=result;
  }catch(e){if(!signal.aborted)error.value=e instanceof Error?e.message:'Erro de conexão.'}finally{if(!signal.aborted)loading.value=false}
}
async function update(patch:Partial<ViewState>,replace=false){const next={...state.value,...patch};const query=Object.fromEntries(new URLSearchParams(serializeState(next)));await router[replace?'replace':'push']({path:route.path,query});}
function field(event:Event,key:keyof ViewState){const value=(event.target as HTMLSelectElement).value;update({...{[key]:value},...(key==='region'?{uf:''}:{})} as Partial<ViewState>)}
function selectMapUf(code:string){
  const uf=charts.ufCode(code);
  if(!uf)return;
  const region=territories.value.find(t=>t.uf===uf)?.region;
  update({uf:state.value.uf===uf?'':uf,region:state.value.region&&region!==state.value.region?'':state.value.region});
}
async function copy(){try{await navigator.clipboard.writeText(location.href);copied.value=true;setTimeout(()=>copied.value=false,2500)}catch{notice.value='Copie o endereço da barra do navegador para compartilhar este recorte.'}}
watch(()=>[route.query,route.path],()=>{page.value=1;notice.value='';load()},{immediate:true});
onBeforeUnmount(()=>request?.abort());
const comparison=computed(()=>charts.comparison(selectedA.value,selectedB.value));
const evolution=computed(()=>data.value?charts.evolution(data.value,section.value==='compare'?'idhm':data.value.metric,displayedState.value.a,displayedState.value.b):{});
const ranking=computed(()=>charts.ranking(data.value?.rows??[],data.value?.metric??'idhm',bottom.value));
const rankingCount=computed(()=>Math.min(data.value?.summary.valid??0,charts.rankingLimit));
const rankingHeight=computed(()=>Math.max(120,rankingCount.value*33+28));
const growth=computed(()=>data.value?charts.growth(data.value,data.value.metric):{});
const distribution=computed(()=>data.value?charts.distribution(data.value,data.value.metric):{});
const association=computed(()=>data.value?charts.association(data.value):{});
const map=computed(()=>charts.mapOption(data.value?.ufSummary??[],data.value?.metric??'idhm',displayedState.value.uf));
const tableRows=computed(()=>{const q=search.value.toLocaleLowerCase('pt-BR');return (data.value?.rows??[]).filter(r=>(r.name+' '+r.uf).toLocaleLowerCase('pt-BR').includes(q))});
const paginated=computed(()=>tableRows.value.slice((page.value-1)*25,page.value*25));
watch(search,()=>page.value=1);
const periods:Year[]=[1991,2000,2010];
const chartSource='Atlas Brasil — PNUD, Ipea e Fundação João Pinheiro';
const amount=(n:number)=>n.toLocaleString('pt-BR');
const articleLink=computed(()=>({compare:'/analises/dimensoes-desenvolvimento',overview:'/analises/maiores-menores-idhm',evolution:'/analises/evolucao-idhm',distribution:'/analises/desigualdades-regionais',association:'/analises/renda-educacao',data:'/docs'})[section.value]??'/analises');
</script>
<template>
  <header class="page-header" :class="{'comparison-header':section==='compare'}"><div><p class="eyebrow">ATLAS DO DESENVOLVIMENTO HUMANO</p><h1>{{titles[section]}}</h1><p class="subtitle">{{subtitles[section]}}</p></div><span class="edition">SÉRIE CENSITÁRIA<br><strong>1991 — 2010</strong></span></header>
  <div class="filter-mobile"><button class="text-button" @click="filtersOpen=!filtersOpen" :aria-expanded="filtersOpen"><PhFunnel :size="18"/>Filtros <PhCaretDown :size="15"/></button><span>{{regionLabel}} · {{state.year}}</span></div>
  <section class="filters" :class="{expanded:filtersOpen}" aria-label="Filtros do dashboard">
    <div class="geography-filters"><label>Região<select :value="state.region" @change="field($event,'region')"><option value="">Todas as regiões</option><option v-for="r in regions" :key="r">{{r}}</option></select></label><label class="uf-filter">UF<select :value="state.uf" @change="field($event,'uf')"><option value="">Todas</option><option v-for="uf in ufs" :key="uf">{{uf}}</option></select></label></div>
    <div v-if="section==='compare'||section==='evolution'" class="municipality-filters"><label>Município A<select :value="state.a" @change="field($event,'a')"><option v-for="t in scoped" :key="t.code" :value="t.code">{{t.name}} ({{t.uf}})</option></select></label><label>Município B<select :value="state.b" :disabled="scoped.length<2" @change="field($event,'b')"><option v-if="scoped.length<2" value="">Sem segundo município</option><option v-else v-for="t in scoped" :key="t.code" :value="t.code">{{t.name}} ({{t.uf}})</option></select></label></div>
    <label v-if="!['compare','association'].includes(section)" class="metric-filter">Indicador<select :value="state.metric" @change="field($event,'metric')"><option v-for="(label,key) in metrics" :key="key" :value="key">{{label}}</option></select></label>
    <div class="year-filter"><span class="filter-label">Ano histórico</span><div class="segmented" role="group" aria-label="Ano histórico"><button v-for="year in periods" :key="year" :aria-pressed="state.year===year" :class="{selected:state.year===year}" @click="update({year})">{{year}}</button></div></div>
  </section>
  <div class="context-bar" :class="{'comparison-toolbar':section==='compare'}"><span><span class="context-dot"></span>{{data?displayedRegionLabel:regionLabel}} <template v-if="data">· {{amount(data.summary.count)}} {{data.summary.count===1?'município':'municípios'}}</template><span v-if="loading" class="loading-text"> · Atualizando…</span></span><div><button class="text-button" @click="update({...defaults})" aria-label="Restaurar filtros"><PhArrowCounterClockwise :size="15"/><span>Limpar</span></button><button class="text-button" @click="copy"><PhLink :size="17"/>{{copied?'Link copiado':'Compartilhar'}}</button><a class="text-button" :href="'/api/export?'+serializeState(state)"><PhDownloadSimple :size="17"/>CSV</a></div></div>
  <p v-if="refreshingRecorte" class="refresh-note" role="status" aria-live="polite">Exibindo {{displayedRegionLabel}} · {{displayedState.year}} · {{metrics[displayedState.metric]}} enquanto atualizamos para {{regionLabel}} · {{state.year}} · {{metrics[state.metric]}}.</p>
  <p v-if="notice" class="notice" role="status">{{notice}} <button @click="notice=''" aria-label="Dispensar aviso">×</button></p>
  <div v-if="error" class="error-state" role="alert"><PhWarningCircle :size="30"/><h2>Não conseguimos carregar os dados</h2><p>{{error}}</p><button class="primary-button" @click="load">Tentar novamente</button></div>
  <div v-else-if="loading&&!data" class="loading-state" role="status"><span class="loading-line"></span>Preparando as perspectivas do Brasil…</div>
  <template v-else-if="data">
    <p v-if="data.summary.missing&&(section!=='compare'||selectedA?.idhm==null||selectedB?.idhm==null)" class="data-note">{{data.summary.missing}} município(s) sem valor para {{metrics[data.metric]}} neste ano. Ausências são preservadas e excluídas dos cálculos.</p>
    <section v-if="section==='compare'" class="comparison-section">
      <div class="section-heading"><h2>IDHM por dimensão <span v-if="comparisonNames" class="heading-detail">— {{comparisonNames}} ({{data.year}})</span></h2><p class="section-caption">Educação, renda e longevidade em uma escala comum de 0 a 1.</p></div>
      <Chart v-if="selectedA||selectedB" :option="comparison" :height="315" :label="`IDHM de educação, renda e longevidade — ${comparisonNames} (${data.year})`"/>
      <p v-else class="empty-state">Selecione municípios com observações disponíveis.</p>
      <p class="chart-source"><strong>Fonte:</strong> {{chartSource}} · {{data.year}}</p>
      <div class="comparison-bottom"><section><h2>Evolução do IDHM <span class="heading-detail">(1991, 2000 e 2010)</span></h2><Chart :option="evolution" :height="255" label="Evolução histórica dos municípios selecionados"/></section><aside class="editorial-teaser"><span class="eyebrow">ANÁLISE</span><h3>Três dimensões,<br>muitas perspectivas</h3><p>O índice sintetiza trajetórias distintas. Explore como educação, renda e longevidade se combinam em cada município.</p><RouterLink to="/analises" class="editorial-link">Ler análises <PhArrowRight :size="20"/></RouterLink><RouterLink to="/docs/metodologia" class="secondary-link">Entenda os indicadores</RouterLink></aside></div>
    </section>
    <template v-else-if="section==='overview'">
      <div class="summary-strip"><div><span>Municípios no recorte</span><strong>{{amount(data.summary.count)}}</strong></div><div><span>{{metrics[data.metric]}} municipal mediano</span><strong>{{formatMetric(data.summary.median,data.metric)}}</strong></div><p>Um ponto de partida para explorar o Brasil.<br><RouterLink to="/docs/metodologia">Como interpretar estas medidas <PhArrowRight :size="15"/></RouterLink></p></div>
      <div class="overview-grid">
        <section>
          <div class="section-heading">
            <span class="eyebrow">LEITURA TERRITORIAL</span>
            <h2>{{metrics[data.metric]}} por UF</h2>
            <p class="section-caption">Mediana municipal por UF · {{data.year}}</p>
          </div>
          <div class="map-controls">
            <label>Explorar UF
              <select :value="state.uf" @change="field($event,'uf')">
                <option value="">Todas as UFs</option>
                <option v-for="uf in ufs" :key="uf" :value="uf">{{uf}}</option>
              </select>
            </label>
            <button v-if="state.uf" class="text-button map-clear" @click="update({uf:''})">Remover filtro {{state.uf}} <span aria-hidden="true">×</span></button>
            <p v-else>Selecione uma UF no mapa ou na lista.</p>
          </div>
          <Chart :option="map" :map="true" label="Mapa do Brasil por mediana municipal em cada UF. Use o campo Explorar UF para filtrar pelo teclado ou consulte a tabela Valores do mapa por UF." @select="selectMapUf"/>
          <p class="chart-source">
            <template v-if="displayedState.region||displayedState.uf||data.ufSummary.some(uf=>uf.median===null)">Em cinza, UFs fora do recorte ou sem valores.<br></template>
            Malha: IBGE · Medianas dos municípios com valor disponível.
          </p>
        </section>
        <section class="ranking-section">
          <div class="section-heading">
            <h2>{{bottom?'Menores':'Maiores'}} valores municipais</h2>
            <div class="small-tabs" role="group" aria-label="Ordenação do ranking">
              <button :class="{selected:!bottom}" :aria-pressed="!bottom" @click="bottom=false">Maiores</button>
              <button :class="{selected:bottom}" :aria-pressed="bottom" @click="bottom=true">Menores</button>
            </div>
          </div>
          <template v-if="rankingCount">
            <Chart :option="ranking" :height="rankingHeight" label="Ranking dos municípios no recorte selecionado"/>
            <p class="chart-source">{{rankingCount===1?'1 município no ranking':rankingCount+' municípios no ranking · Empates ordenados pelo nome.'}}</p>
          </template>
          <p v-else class="empty-state">Nenhum município com valor para este indicador no recorte.</p>
        </section>
      </div>
    </template>
    <template v-else-if="section==='evolution'">
      <section class="main-chart-section"><h2>{{metrics[data.metric]}} em três momentos</h2><p class="section-caption">Trajetórias dos municípios selecionados · os períodos não representam medições anuais.</p><Chart :option="evolution" :height="350" :label="'Evolução de '+metrics[data.metric]"/></section>
      <section class="main-chart-section"><h2>Maiores avanços entre 1991 e 2010</h2><p class="section-caption">Diferença absoluta, com pares válidos nos dois anos · {{amount(data.growth.length)}} municípios comparáveis.</p><Chart :option="growth" :height="380" label="Maiores diferenças absolutas entre 1991 e 2010"/><p class="data-note">Diferenças do IDHM são expressas em pontos de índice. Para renda, a diferença é monetária.</p></section>
    </template>
    <template v-else-if="section==='distribution'">
      <section class="main-chart-section"><h2>{{metrics[data.metric]}} entre municípios de cada região</h2><p class="section-caption">Distribuição municipal em {{data.year}} · quartis pelo método R7 · extremos representam mínimo e máximo.</p><Chart :option="distribution" :height="400" label="Distribuição dos indicadores por grande região"/><div class="distribution-labels"><span v-for="d in data.distribution" :key="d.name">{{d.name}} <strong>n = {{amount(d.n)}}</strong></span></div><p class="data-note">A linha central é a mediana. A caixa reúne os 50% centrais dos municípios. Cada município tem o mesmo peso; não é uma distribuição da população.</p></section>
    </template>
    <template v-else-if="section==='association'">
      <div class="summary-strip compact"><div><span>Correlação de Pearson</span><strong>{{number(data.association.r)}}</strong></div><div><span>Pares válidos</span><strong>{{amount(data.association.n)}}</strong></div><p>Uma associação estatística não demonstra causalidade.<br>Dados de renda a preços de agosto de 2010.</p></div><section class="main-chart-section"><h2>Renda per capita × IDHM Educação</h2><p class="section-caption">Cada ponto representa um município · {{data.year}}</p><Chart :option="association" :height="440" label="Dispersão entre renda per capita e IDHM Educação"/></section>
    </template>
    <template v-else-if="section==='data'">
      <section class="dataset-intro"><span class="eyebrow">BASE ABERTA PARA EXPLORAÇÃO</span><h2>Transparência em cada observação</h2><p>Os indicadores do Atlas Brasil foram obtidos pelo Ipeadata e organizados na malha municipal de referência de 2010. Valores ausentes permanecem vazios; a renda é expressa a preços de agosto de 2010.</p><div class="dataset-facts"><span><strong>{{amount(boot?.territories.length??0)}}</strong> municípios</span><span><strong>{{amount(boot?.observations??0)}}</strong> registros</span><span><strong>3</strong> anos censitários</span></div><RouterLink to="/docs/dados" class="editorial-link">Consulte a origem e o dicionário <PhArrowRight :size="20"/></RouterLink><details v-if="boot?.dataset"><summary>Integridade e versão da base</summary><p>SHA-256 do CSV normalizado:</p><code class="hash">{{boot.dataset.sha256}}</code></details></section>
    </template>
    <p v-if="section!=='compare'" class="chart-source global-chart-source"><strong>Fonte:</strong> {{chartSource}} · {{data.year}}<span v-if="section==='association'"> · R$ a preços de agosto de 2010</span></p>
    <section class="derived-table-section" aria-label="Alternativas tabulares dos gráficos">
      <details v-if="section==='overview'"><summary>Valores do mapa por UF</summary><div class="table-scroll"><table><caption>Mediana municipal de {{metrics[data.metric]}} por UF · {{data.year}}</caption><thead><tr><th scope="col">UF</th><th scope="col">Municípios no recorte</th><th scope="col">Valores válidos</th><th scope="col">Mediana municipal</th></tr></thead><tbody><tr v-for="u in data.ufSummary" :key="u.uf"><th scope="row">{{u.uf}}</th><td>{{u.count}}</td><td>{{u.n}}</td><td>{{formatMetric(u.median,data.metric)}}</td></tr></tbody></table></div></details>
      <details v-if="section==='distribution'"><summary>Quartis e extremos em tabela</summary><div class="table-scroll"><table><caption>{{metrics[data.metric]}} por grande região · {{data.year}} · quantis R7</caption><thead><tr><th scope="col">Região</th><th scope="col">n</th><th scope="col">Mínimo</th><th scope="col">Q1</th><th scope="col">Mediana</th><th scope="col">Q3</th><th scope="col">Máximo</th></tr></thead><tbody><tr v-for="d in data.distribution" :key="d.name"><th scope="row">{{d.name}}</th><td>{{d.n}}</td><td v-for="(v,i) in d.values" :key="i">{{formatMetric(v,data.metric)}}</td></tr></tbody></table></div></details>
      <details v-if="section==='evolution'"><summary>Maiores avanços em tabela</summary><div class="table-scroll"><table><caption>12 maiores diferenças de {{metrics[data.metric]}} entre 1991 e 2010</caption><thead><tr><th scope="col">Município</th><th scope="col">UF</th><th scope="col">1991</th><th scope="col">2010</th><th scope="col">Diferença absoluta</th></tr></thead><tbody><tr v-for="r in data.growth.slice(0,12)" :key="r.code"><th scope="row">{{r.name}}</th><td>{{r.uf}}</td><td>{{formatMetric(r.start,data.metric)}}</td><td>{{formatMetric(r.end,data.metric)}}</td><td>{{formatMetric(r.delta,data.metric)}}</td></tr></tbody></table></div></details>
      <details v-if="section==='compare'||section==='evolution'"><summary>Trajetórias dos municípios em tabela</summary><div class="table-scroll"><table><caption>Observações dos municípios selecionados nos três anos</caption><thead><tr><th scope="col">Município</th><th scope="col">Ano</th><th scope="col">{{section==='compare'?'IDHM':metrics[data.metric]}}</th></tr></thead><tbody><tr v-for="r in data.selectedHistory" :key="r.code+r.year"><th scope="row">{{r.name}} ({{r.uf}})</th><td>{{r.year}}</td><td>{{formatMetric(r[section==='compare'?'idhm':data.metric],section==='compare'?'idhm':data.metric)}}</td></tr></tbody></table></div></details>
    </section>
    <section class="data-table-section"><details :open="section==='data'"><summary>Consultar os valores em tabela <span>{{amount(data.rows.length)}} registros no ano selecionado</span></summary><div class="table-controls"><label class="search-input"><PhMagnifyingGlass :size="18"/><input v-model="search" type="search" placeholder="Buscar município ou UF" aria-label="Buscar na tabela"></label><a :href="'/api/export?'+serializeState(state)" class="text-button"><PhDownloadSimple :size="18"/>Baixar CSV do recorte</a></div><div class="table-scroll"><table><caption>Indicadores municipais do recorte em {{data.year}}</caption><thead><tr><th scope="col">Município</th><th scope="col">UF</th><th scope="col">IDHM</th><th scope="col">Educação</th><th scope="col">Renda</th><th scope="col">Longevidade</th><th scope="col">Renda per capita</th></tr></thead><tbody><tr v-for="r in paginated" :key="r.code"><th scope="row">{{r.name}}</th><td>{{r.uf}}</td><td>{{number(r.idhm)}}</td><td>{{number(r.idhm_educacao)}}</td><td>{{number(r.idhm_renda)}}</td><td>{{number(r.idhm_longevidade)}}</td><td>{{formatMetric(r.renda_per_capita,'renda_per_capita')}}</td></tr><tr v-if="!paginated.length"><td colspan="7">Nenhum município encontrado.</td></tr></tbody></table></div><div class="pagination"><span>{{amount(tableRows.length)}} resultados · página {{page}} de {{Math.max(1,Math.ceil(tableRows.length/25))}}</span><button :disabled="page===1" @click="page--">Anterior</button><button :disabled="page*25>=tableRows.length" @click="page++">Próxima</button></div></details></section>
    <div v-if="section!=='compare'" class="bottom-reading"><span class="eyebrow">APROFUNDE A LEITURA</span><RouterLink to="/analises" class="editorial-link">Dos indicadores às perguntas: explore as análises <PhArrowRight :size="20"/></RouterLink></div>
  </template>
</template>
