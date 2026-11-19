import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import App from './App.vue';
import {recoverNavigation,navigationError} from './lib/navigation';
const DashboardPage=()=>import('./pages/DashboardPage.vue');
const ContentPage=()=>import('./pages/ContentPage.vue');
const AboutPage=()=>import('./pages/AboutPage.vue');
import '../css/site.css';
const routes = [
  ...['/','/comparar','/evolucao','/distribuicao','/associacao','/dados'].map(path=>({path,component:DashboardPage})),
  {path:'/docs/:category?/:slug?',component:ContentPage,props:{type:'docs'}},
  {path:'/analises/:slug?',component:ContentPage,props:{type:'analises'}},
  {path:'/sobre',component:AboutPage},
  {path:'/:pathMatch(.*)*',component:AboutPage,props:{notFound:true}},
];
const router=createRouter({history:createWebHistory(),routes,scrollBehavior:(to,from,saved)=>saved??(to.path!==from.path?{top:0}:{})});
router.onError((error,to)=>recoverNavigation(error,to.fullPath));
router.afterEach(()=>{navigationError.value=''});
createApp(App).use(router).mount('#app');
