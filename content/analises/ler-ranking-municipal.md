---
title: "Como ler um ranking municipal de IDHM"
slug: ler-ranking-municipal
category: analises
summary: "Conheça os extremos e a mediana do IDHM municipal em 2010 e entenda os limites de um ranking."
status: published
order: 10
updated_at: "2026-11-18"
chart: ranking
author: "Equipe do projeto"
dataset_sha256: a4aa7b86737fd6fdeb7bd7e178dfaf83603471189880720590699e44b58ea45a
---

Resultados textuais referentes ao Brasil em 2010; a visualização pode explorar outros anos.

## Propósito

Quais territórios têm maiores e menores valores de IDHM? A ordenação ajuda a localizar extremos dentro de um ano e recorte, sem transformar posição em diagnóstico completo.

## Método

Selecionar IDHM, Brasil e 2010, usando valores numéricos antes do arredondamento. Há 5.565 unidades cadastradas e 5.564 valores válidos; Nazária/PI é excluída por ausência. A mediana dá peso igual aos municípios. O gráfico atual mostra até 12 barras por aba e desempata valores pelo nome, sem expandir a lista para todos os empatados no corte. A [metodologia](/docs/metodologia/ranking-e-mediana) distingue ordenação e posição numérica.

## Resultado calculado para 2010

São Caetano do Sul/SP apresenta o maior IDHM, 0,862, e Melgaço/PA o menor, 0,418, entre os 5.564 valores municipais válidos. A mediana é 0,665. Os extremos são únicos na precisão armazenada desse ano. A mediana municipal não é um IDHM oficial do Brasil nem representa o habitante mediano.

## Leitura do gráfico ao vivo

Observe ano, indicador, recorte e distância entre barras. Alterar filtros no dashboard muda a população de comparação; o gráfico do artigo começa no recorte nacional. Se os valores arredondados coincidirem, confira a precisão antes de interpretar a ordem. O cálculo ao vivo é fornecido pela visualização; os números acima fixam 2010 e o snapshot declarado.

## Limites

Ranking não explica causas ou desigualdades internas e não deve, sozinho, orientar políticas ou caracterizar moradores. Dados de 1991, 2000 e 2010 são históricos, sem diagnóstico atual. A conferência do arquivo não comprova qualidade de todas as variáveis da fonte primária.

## Versão de referência

Os resultados textuais foram calculados no CSV identificado abaixo. Para reproduzi-los, preserve o CSV e o manifesto correspondentes.

- CSV SHA-256: `a4aa7b86737fd6fdeb7bd7e178dfaf83603471189880720590699e44b58ea45a`.
- Manifesto SHA-256: `604aa709827db0d5054fdf311f875ce0fe9ae6761987a3c111fda82d5aa31679`.

O gráfico consulta a base ativa, cuja versão pode ser conferida em [Dados](/dados). Uma atualização dessa base não altera o recorte dos resultados textuais publicados.

## Fonte e exploração

Fonte dos indicadores: Atlas do Desenvolvimento Humano no Brasil — PNUD, Ipea e Fundação João Pinheiro (FJP), Censos Demográficos IBGE e distribuição Ipeadata; cadastro territorial IBGE de 2010. Confira [proveniência e cobertura](/docs/dados/origem-e-proveniencia) e o [guia de filtros](/docs/guia/filtros-exportacao-e-links).
