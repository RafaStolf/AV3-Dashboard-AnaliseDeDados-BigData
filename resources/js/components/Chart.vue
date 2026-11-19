<script setup lang="ts">
import {ref,onMounted,onBeforeUnmount,watch} from 'vue';
import * as echarts from 'echarts/core';
import {BarChart,LineChart,ScatterChart,BoxplotChart,HeatmapChart,MapChart} from 'echarts/charts';
import {GridComponent,TooltipComponent,LegendComponent,VisualMapComponent,GeoComponent,AriaComponent,DataZoomComponent,MarkLineComponent} from 'echarts/components';
import {CanvasRenderer} from 'echarts/renderers';
import type {EChartsOption} from 'echarts';
echarts.use([BarChart,LineChart,ScatterChart,BoxplotChart,HeatmapChart,MapChart,GridComponent,TooltipComponent,LegendComponent,VisualMapComponent,GeoComponent,AriaComponent,DataZoomComponent,MarkLineComponent,CanvasRenderer]);
const props=withDefaults(defineProps<{option:EChartsOption;height?:number;label:string;map?:boolean}>(),{height:340,map:false});
const emit=defineEmits<{select:[name:string]}>();
const host=ref<HTMLDivElement>();
const error=ref('');
let chart:echarts.ECharts|undefined;
let observer:ResizeObserver|undefined;
let disposed=false;
const render=()=>chart?.setOption({backgroundColor:'transparent',animation:!matchMedia('(prefers-reduced-motion: reduce)').matches,textStyle:{fontFamily:'"Segoe UI", sans-serif',color:'#111f34'},...props.option},{notMerge:true});
onMounted(async()=>{
  try {
    if(props.map&&!echarts.getMap('brasil')){
      const response=await fetch('/assets/brasil-ufs.geojson');
      if(!response.ok)throw new Error('Mapa indisponível.');
      const geo=await response.json();
      for(const feature of geo.features)feature.properties.name=feature.properties.codarea;
      echarts.registerMap('brasil',geo);
    }
  } catch {
    if(!disposed)error.value='Não foi possível carregar o mapa. Os mesmos valores estão disponíveis na tabela por UF.';
    return;
  }
  if(disposed||!host.value)return;
  chart=echarts.init(host.value,undefined,{renderer:'canvas'});
  chart.on('click',params=>emit('select',String(params.name)));
  observer=new ResizeObserver(()=>chart?.resize());observer.observe(host.value);
  render();
});
watch(()=>props.option,render,{deep:true});
onBeforeUnmount(()=>{disposed=true;observer?.disconnect();chart?.dispose()});
</script>
<template><p v-if="error" class="empty-state" role="status">{{error}}</p><div ref="host" v-show="!error" class="chart" :class="{'chart--map':map}" :style="map?undefined:{height:height+'px'}" role="img" :aria-label="label"></div></template>
