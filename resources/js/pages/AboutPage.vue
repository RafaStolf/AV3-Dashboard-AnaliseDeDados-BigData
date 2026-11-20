<script setup lang="ts">withDefaults(defineProps<{notFound?:boolean}>(),{notFound:false});</script>
<template><header class="page-header"><div><p class="eyebrow">UM PROJETO ACADÊMICO</p><h1>{{notFound?'Página não encontrada':'Brasil em Perspectiva'}}</h1><p class="subtitle">Territórios em Foco</p></div></header><article class="prose about-prose"><template v-if="notFound"><p>Este endereço não corresponde a uma página do projeto.</p><RouterLink to="/">Voltar ao panorama</RouterLink></template><template v-else>
  <p>Desenvolvemos o Brasil em Perspectiva em grupo no WayHub do Ânima Hub, como alunos do Ulife. Usamos os dados do Atlas Brasil para estudar o desenvolvimento humano nos municípios brasileiros.</p>
  <section aria-labelledby="equipe">
    <h2 id="equipe">Alunos do projeto</h2>
    <table class="student-roster" aria-labelledby="equipe">
      <thead><tr><th scope="col">Nome</th><th scope="col"><abbr title="Registro acadêmico">RA</abbr></th></tr></thead>
      <tbody>
        <tr><th scope="row">Isaac caleb Bilar correa</th><td>12724135878</td></tr>
        <tr><th scope="row">Joao Pedro Aguiar de Pinho Pacheco</th><td>12624213071</td></tr>
        <tr><th scope="row">Marcio da Silva dos Anjos</th><td>825111033</td></tr>
        <tr><th scope="row">Rafael Stolf Silva</th><td>122515186</td></tr>
        <tr><th scope="row">Victor Tavares de Felice</th><td>826170899</td></tr>
      </tbody>
    </table>
    <h2>Orientação</h2>
    <p><strong>Henrique Ruiz Poyatos Neto</strong> é o orientador da área. <strong>Iara Maria Goncalves Salvador Novaes</strong> é a coorientadora do grupo.</p>
  </section>
  <h2>Olhar para os territórios, compreender as diferenças</h2><p>Como o desenvolvimento humano varia entre os municípios brasileiros? Como essas diferenças evoluíram entre 1991 e 2010? Estas são as perguntas que orientam o Brasil em Perspectiva.</p><p>O projeto reúne visualizações interativas, análises e documentação em um só lugar. A proposta é facilitar a exploração dos indicadores, sem perder de vista suas definições, suas fontes e seus limites.</p><h2>O que estamos investigando</h2><ol><li>Quais municípios apresentam os maiores e menores valores de IDHM?</li><li>Onde ocorreu maior avanço entre os períodos?</li><li>Qual dimensão apresenta menor valor em cada território?</li><li>Como as distribuições municipais diferem entre as regiões?</li><li>Existe associação entre renda per capita e IDHM Educação?</li></ol><h2>Dados e responsabilidade</h2><p>Utilizamos o Atlas do Desenvolvimento Humano no Brasil, de PNUD, Ipea e Fundação João Pinheiro, distribuído pelo Ipeadata. A série censitária compreende 1991, 2000 e 2010. A identificação territorial é baseada na malha municipal de referência de 2010, com cadastro do IBGE.</p><p>Os dados são históricos. A análise não caracteriza a situação atual dos municípios, não demonstra causalidade e não substitui estudos locais. O grupo desenvolveu o dashboard de forma independente das instituições que produziram os dados.</p><h2>Transparência como parte da análise</h2><p>As fontes, regras de importação, cálculos e decisões de modelagem estão documentados publicamente. As tabelas e exportações permitem conferir os valores usados nas visualizações.</p><p><RouterLink to="/docs">Explore a documentação</RouterLink> ou <RouterLink to="/dados">consulte a base de dados</RouterLink>.</p><h2>Código do projeto</h2><p><a href="https://github.com/RafaStolf/AV3-Dashboard-AnaliseDeDados-BigData" target="_blank" rel="noopener noreferrer">Acompanhe o desenvolvimento no GitHub</a>.</p></template></article></template>
<style scoped>
.about-prose .student-roster { display: table; font-size: 14px; }
.student-roster td { white-space: nowrap; }
</style>
