---
title: "Como comparar distribuições municipais por região"
slug: comparar-distribuicoes-regionais
category: analises
summary: "Compare as medianas e a distribuição do IDHM municipal entre as regiões brasileiras em 2010."
status: published
order: 40
updated_at: "2026-11-18"
chart: distribution
author: "Equipe do projeto"
dataset_sha256: a4aa7b86737fd6fdeb7bd7e178dfaf83603471189880720590699e44b58ea45a
---

Resultados textuais referentes ao Brasil em 2010; a visualização pode explorar outros anos.

## Propósito

Como a distribuição dos indicadores difere entre regiões? O boxplot permite observar centro e dispersão entre municípios, além de um resumo único por grupo.

## Método

Selecionar Brasil, IDHM e 2010; agrupar os municípios válidos pela região registrada no cadastro. Cada município recebe peso igual. Quartis usam interpolação R7; as extremidades atuais são mínimo e máximo, sem cercas de 1,5 IQR ou remoção de extremos. Consulte a [metodologia](/docs/metodologia/distribuicao-e-quartis).

## Resultado calculado para 2010

| Região | Municípios válidos | Mediana municipal de IDHM |
| --- | --- | --- |
| Norte | 449 | 0,609 |
| Nordeste | 1.793 | 0,588 |
| Centro-Oeste | 466 | 0,6925 |
| Sudeste | 1.668 | 0,705 |
| Sul | 1.188 | 0,716 |

O Sul tem a maior mediana e o Nordeste a menor neste snapshot, com diferença de 0,128 ponto. São 5.564 municípios válidos; Nazária/PI explica a unidade sem valor no Nordeste. A precisão adicional de 0,6925 decorre da mediana de quantidade par, sem alterar os valores originais.

## Leitura do gráfico ao vivo

O gráfico fornece quartis e extremos calculados para o ano selecionado. Compare caixas, medianas e tamanhos dos grupos; o número de municípios não é igual entre regiões. Mudar o ano altera a caixa ao vivo, mas não os resultados textuais de 2010. Filtros territoriais no dashboard descrevem apenas a seleção ativa.

## Limites

Mediana municipal não é IDHM regional oficial nem representa o habitante mediano. Dispersão entre cidades não mede desigualdade dentro delas, e extremos não são automaticamente erros. Composição territorial e cobertura devem acompanhar comparações. Os dados de 1991, 2000 e 2010 são históricos, sem diagnóstico atual ou explicação causal das diferenças.

## Versão de referência

Os resultados textuais foram calculados no CSV identificado abaixo. Para reproduzi-los, preserve o CSV e o manifesto correspondentes.

- CSV SHA-256: `a4aa7b86737fd6fdeb7bd7e178dfaf83603471189880720590699e44b58ea45a`.
- Manifesto SHA-256: `604aa709827db0d5054fdf311f875ce0fe9ae6761987a3c111fda82d5aa31679`.

O gráfico consulta a base ativa, cuja versão pode ser conferida em [Dados](/dados). Uma atualização dessa base não altera o recorte dos resultados textuais publicados.

## Fonte e exploração

Fonte dos indicadores: Atlas do Desenvolvimento Humano no Brasil — PNUD, Ipea e Fundação João Pinheiro (FJP), Censos Demográficos IBGE e distribuição Ipeadata; cadastro territorial IBGE de 2010. Confira [proveniência e cobertura](/docs/dados/origem-e-proveniencia) e o [guia de filtros](/docs/guia/filtros-exportacao-e-links).
