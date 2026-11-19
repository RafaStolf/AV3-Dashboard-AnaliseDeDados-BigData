import type {EChartsOption} from 'echarts';
import type {Dashboard,Metric,Observation,UfSummary} from '../types';
import {metrics,number,formatMetric} from './view-state';
export const blue='#075fa8',orange='#c65316',ink='#111f34',muted='#657080',grid='#dcd6ca';
const base={tooltip:{trigger:'axis',backgroundColor:'#fffcf6',borderColor:'#d5cdbd',textStyle:{color:ink}},grid:{left:16,right:45,top:22,bottom:45,containLabel:true},legend:{bottom:0,left:10,icon:'roundRect',itemWidth:17,itemHeight:9,textStyle:{color:ink,fontSize:15}},aria:{enabled:false},color:[blue,orange]};
const xValue=(money=false)=>({type:'value' as const,min:0,max:money?undefined:1,splitLine:{lineStyle:{color:grid}},axisLabel:{color:muted,fontSize:11,hideOverlap:true,formatter:(v:number)=>money?v.toLocaleString('pt-BR'):number(v,2)}});
export function comparison(a:Observation|undefined,b:Observation|undefined):EChartsOption{
  const keys=['idhm_educacao','idhm_renda','idhm_longevidade'] as const;
  return {...base,grid:{...base.grid,left:12,top:24,bottom:52},xAxis:xValue(),yAxis:{type:'category',inverse:true,data:keys.map(k=>metrics[k]),axisLine:{show:false},axisTick:{show:false},axisLabel:{color:ink,fontSize:16,fontWeight:600,margin:18}},series:[a,b].filter(Boolean).map((r,i)=>({name:r!.name+' ('+r!.uf+')',type:'bar',data:keys.map(k=>r![k]),barWidth:19,barGap:'25%',itemStyle:{color:i?orange:blue,borderRadius:[0,2,2,0]},label:{show:true,position:'right',color:ink,fontWeight:600,fontSize:16,formatter:(p:any)=>number(p.value)}}))} as EChartsOption;
}
export function evolution(data:Dashboard,metric:Metric,a:string,b:string):EChartsOption{
  const selected=[a,b].map(code=>data.selectedHistory.filter(r=>r.code===code));
  const lines=selected.filter(r=>r.length).map((r,i)=>({name:r[0]!.name+' ('+r[0]!.uf+')',type:'line',data:r.sort((x,y)=>x.year-y.year).map(v=>[v.year,v[metric]]),showSymbol:true,symbol:'circle',symbolSize:9,connectNulls:false,lineStyle:{width:3,color:i?orange:blue},itemStyle:{color:i?orange:blue},label:{show:true,position:i?'bottom':'top',color:i?orange:blue,fontWeight:600,formatter:(p:any)=>formatMetric(p.value[1],metric)}}));
  return {...base,grid:{...base.grid,left:4,right:40,top:30,bottom:48},xAxis:{type:'value',min:1991,max:2010,interval:1,axisLabel:{showMinLabel:true,showMaxLabel:true,formatter:(v:number)=>[1991,2000,2010].includes(v)?String(v):''},splitLine:{show:false},axisTick:{show:false}},yAxis:xValue(metric==='renda_per_capita'),series:lines.length?lines:[{name:'Mediana municipal do recorte',type:'line',data:data.history.map(v=>[v.year,v.value]),lineStyle:{width:3}}]} as EChartsOption;
}
export function ranking(rows:Observation[],metric:Metric,bottom=false):EChartsOption{
  const sorted=rows.filter(r=>r[metric]!=null).sort((a,b)=>(bottom?1:-1)*(a[metric]!-b[metric]!)||a.name.localeCompare(b.name)).slice(0,12);
  return {...base,legend:{show:false},grid:{left:8,right:65,top:6,bottom:25,containLabel:true},xAxis:xValue(metric==='renda_per_capita'),yAxis:{type:'category',inverse:true,data:sorted.map(r=>r.name+' · '+r.uf),axisLine:{show:false},axisTick:{show:false},axisLabel:{fontSize:12,color:ink,width:170,overflow:'truncate'}},series:[{type:'bar',data:sorted.map(r=>r[metric]),barWidth:13,itemStyle:{color:bottom?orange:blue},label:{show:true,position:'right',color:ink,fontSize:12,formatter:(p:any)=>formatMetric(p.value,metric)}}]} as EChartsOption;
}
export function growth(data:Dashboard,metric:Metric):EChartsOption{
  const rows=data.growth.slice(0,12);
  return {...base,legend:{show:false},grid:{...base.grid,right:70,bottom:32},xAxis:{...xValue(true),axisLabel:{formatter:(v:number)=>number(v,metric==='renda_per_capita'?0:2)}},yAxis:{type:'category',inverse:true,data:rows.map(r=>r.name+' · '+r.uf),axisLine:{show:false},axisTick:{show:false},axisLabel:{width:160,overflow:'truncate',color:ink}},series:[{type:'bar',data:rows.map(r=>r.delta),barWidth:14,itemStyle:{color:orange},label:{show:true,position:'right',formatter:(p:any)=>(p.value>0?'+':'')+formatMetric(p.value,metric)}}]} as EChartsOption;
}
export function distribution(data:Dashboard,metric:Metric):EChartsOption{
  return {...base,legend:{show:false},grid:{left:40,right:40,top:32,bottom:45},xAxis:{type:'category',data:data.distribution.map(d=>d.name),axisLabel:{color:ink,fontSize:13}},yAxis:xValue(metric==='renda_per_capita'),series:[{type:'boxplot',data:data.distribution.map(d=>d.values),itemStyle:{color:'#f1dfcd',borderColor:orange,borderWidth:2},tooltip:{formatter:(p:any)=>{const d=data.distribution[p.dataIndex]!;return `${d.name} · n = ${d.n}<br>Mínimo: ${formatMetric(d.values[0],metric)}<br>Q1: ${formatMetric(d.values[1],metric)}<br>Mediana: ${formatMetric(d.values[2],metric)}<br>Q3: ${formatMetric(d.values[3],metric)}<br>Máximo: ${formatMetric(d.values[4],metric)}`}}}]} as EChartsOption;
}
export function association(data:Dashboard):EChartsOption{
  return {...base,legend:{show:false},tooltip:{trigger:'item',formatter:(p:any)=>`${escapeHtml(p.data[2])} (${p.data[3]})<br>Renda: ${formatMetric(p.data[0],'renda_per_capita')}<br>Educação: ${number(p.data[1])}`},grid:{left:50,right:30,top:25,bottom:65},xAxis:{...xValue(true),name:'Renda per capita (R$ de 2010)',nameLocation:'middle',nameGap:42},yAxis:{...xValue(),name:'IDHM Educação'},series:[{type:'scatter',symbolSize:7,data:data.rows.filter(r=>r.renda_per_capita!==null&&r.idhm_educacao!==null).map(r=>[r.renda_per_capita,r.idhm_educacao,r.name,r.uf]),itemStyle:{color:blue,opacity:.55}}]} as EChartsOption;
}
export function escapeHtml(s:string):string{return s.replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]!))}
const codes:Record<string,string>={'11':'RO','12':'AC','13':'AM','14':'RR','15':'PA','16':'AP','17':'TO','21':'MA','22':'PI','23':'CE','24':'RN','25':'PB','26':'PE','27':'AL','28':'SE','29':'BA','31':'MG','32':'ES','33':'RJ','35':'SP','41':'PR','42':'SC','43':'RS','50':'MS','51':'MT','52':'GO','53':'DF'};
export const ufCode=(code:string)=>codes[code]??'';
export function mapOption(summaries:UfSummary[],metric:Metric,selectedUf=''):EChartsOption{
  const values=Object.entries(codes).map(([code,uf])=>{const summary=summaries.find(s=>s.uf===uf);return {name:code,value:summary?.median??undefined,n:summary?.n??0,uf}});
  const observed=values.map(d=>d.value).filter((v):v is number=>v!==undefined);
  const selectedCode=Object.entries(codes).find(([,uf])=>uf===selectedUf)?.[0];
  return {tooltip:{trigger:'item',formatter:(p:any)=>`${p.data?.uf??ufCode(p.name)}<br>Mediana municipal: ${formatMetric(p.data?.value,metric)}<br>${p.data?.n??0} municípios com valor`},visualMap:{min:observed.length?Math.min(...observed):0,max:observed.length?Math.max(...observed):1,left:'center',bottom:8,orient:'horizontal',calculable:false,inRange:{color:['#efe2ce','#cfab78','#b36732','#934014']},text:['Maior','Menor'],textStyle:{color:ink}},series:[{type:'map',map:'brasil',roam:false,aspectScale:1,top:10,bottom:65,left:10,right:10,selectedMode:'single',selectedMap:selectedCode?{[selectedCode]:true}:{},select:{label:{show:true,fontWeight:700,color:ink},itemStyle:{areaColor:'#efc183',borderColor:'#934014',borderWidth:2}},label:{show:true,color:ink,fontSize:10,formatter:(p:any)=>ufCode(p.name)},itemStyle:{borderColor:'#fbf7ef',borderWidth:1,areaColor:'#e8e4dd'},emphasis:{label:{show:true},itemStyle:{areaColor:'#d4b284'}},data:values}]} as EChartsOption;
}
