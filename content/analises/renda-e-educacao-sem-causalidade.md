---
title: "Renda e educação: associação sem causalidade"
slug: renda-e-educacao-sem-causalidade
category: analises
summary: "Explore a associação entre renda e IDHM Educação em 2010, sem confundir correlação com causalidade."
status: published
order: 50
updated_at: "2026-11-18"
chart: association
author: "Equipe do projeto"
dataset_sha256: a4aa7b86737fd6fdeb7bd7e178dfaf83603471189880720590699e44b58ea45a
---

Resultados textuais referentes ao Brasil em 2010; a visualização pode explorar outros anos.

## Propósito

Existe associação entre renda per capita e IDHM Educação? A dispersão permite observar se municípios com renda diferente apresentam padrões distintos no componente educacional.

## Método

Usar Brasil e 2010, formando pares completos do mesmo município. Calcular Pearson nos valores originais e informar `n`. A renda é domiciliar mensal per capita em reais de 1º de agosto de 2010, conforme a série `ADH_RDPC`; não foi aplicado outro deflator. Excluir pares ausentes, sem preenchimento. A [metodologia](/docs/metodologia/associacao-e-pearson) apresenta a fórmula.

## Resultado calculado para 2010

Para 5.564 pares municipais válidos, o cálculo independente encontra `r = 0,7913902393`, aproximadamente 0,7914. O sinal positivo indica associação linear positiva no conjunto: municípios com rendas maiores tendem a apresentar maior IDHM Educação. Nazária/PI está fora porque as duas variáveis são ausentes. Não foram removidos extremos por aparência.

## Leitura do gráfico ao vivo

Cada ponto é um município. A visualização fornece `r` e `n` para o ano selecionado; observe também curvas, grupos, sobreposição e dispersão. O coeficiente sozinho não descreve tudo isso. Trocar 2010 por 1991 ou 2000 altera o gráfico, mas o coeficiente textual continua fixado em 2010.

## Limites

Associação não prova que renda causa educação ou o inverso. Outras variáveis e composição regional podem influenciar o padrão. Relações municipais não autorizam deduzir relações entre pessoas dentro deles.

O foco é IDHM Educação, distinto do IDHM total, que também incorpora renda. Não juntar anos como observações independentes nem atribuir significância estatística sem desenho inferencial. Os três anos são históricos, sem diagnóstico atual. A análise é descritiva e não estima efeito causal.

## Versão de referência

Os resultados textuais foram calculados no CSV identificado abaixo. Para reproduzi-los, preserve o CSV e o manifesto correspondentes.

- CSV SHA-256: `a4aa7b86737fd6fdeb7bd7e178dfaf83603471189880720590699e44b58ea45a`.
- Manifesto SHA-256: `604aa709827db0d5054fdf311f875ce0fe9ae6761987a3c111fda82d5aa31679`.

O gráfico consulta a base ativa, cuja versão pode ser conferida em [Dados](/dados). Uma atualização dessa base não altera o recorte dos resultados textuais publicados.

## Fonte e exploração

Fonte dos indicadores: Atlas do Desenvolvimento Humano no Brasil — PNUD, Ipea e Fundação João Pinheiro (FJP), Censos Demográficos IBGE e distribuição Ipeadata; cadastro territorial IBGE de 2010. Confira [proveniência e cobertura](/docs/dados/origem-e-proveniencia) e o [guia de filtros](/docs/guia/filtros-exportacao-e-links).
