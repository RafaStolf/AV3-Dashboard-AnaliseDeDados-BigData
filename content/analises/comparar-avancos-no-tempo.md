---
title: "Como comparar avanços entre dois períodos"
slug: comparar-avancos-no-tempo
category: analises
summary: "Compare os avanços do IDHM entre 1991 e 2010 com os mesmos municípios nos dois períodos."
status: published
order: 20
updated_at: "2026-11-18"
chart: evolution
author: "Equipe do projeto"
dataset_sha256: a4aa7b86737fd6fdeb7bd7e178dfaf83603471189880720590699e44b58ea45a
---

Resultados textuais referentes ao Brasil entre 1991 e 2010; o gráfico de avanços mantém esse intervalo.

## Propósito

Onde ocorreu maior avanço entre dois períodos? É preciso observar início, fim e diferença para as mesmas unidades territoriais.

## Método

O avanço implementado usa intervalo fixo 1991–2010. Parear IDHM por código na geografia harmonizada de 2010 e calcular valor final menos inicial. São 5.564 pares completos; Nazária/PI não participa. Ordenar deltas, preservando empates, e calcular a mediana depois de obter cada diferença. Não confundir com subtração de medianas anuais. Veja a [regra temporal](/docs/metodologia/variacao-entre-periodos).

## Resultado calculado para 1991–2010

Bodó/RN tem o maior aumento de IDHM no conjunto nacional: de 0,136 em 1991 para 0,629 em 2010, diferença de 0,493 ponto de índice. Esse máximo é único na precisão do CSV. A mediana das diferenças dos 5.564 municípios pareados é 0,276 ponto.

## Leitura do gráfico ao vivo

O gráfico apresenta até 12 municípios com os maiores avanços entre 1991 e 2010. A seleção de ano do artigo não muda esse intervalo. Para acompanhar os três anos observados de municípios específicos, explore [Evolução](/evolucao).

Compare pontos de partida e chegada, não só delta. Intervalos 1991–2000 e 2000–2010 têm durações diferentes; nenhuma diferença apresentada é taxa anual.

## Limites

As séries foram harmonizadas pelos produtores para áreas de 2010, não limites administrativos de cada censo. O avanço não demonstra efeito de gestão ou programa, melhora uniforme entre moradores nem continuidade depois de 2010. São resultados históricos e não diagnóstico atual. Não extrapolar para anos sem observação.

## Versão de referência

Os resultados textuais foram calculados no CSV identificado abaixo. Para reproduzi-los, preserve o CSV e o manifesto correspondentes.

- CSV SHA-256: `a4aa7b86737fd6fdeb7bd7e178dfaf83603471189880720590699e44b58ea45a`.
- Manifesto SHA-256: `604aa709827db0d5054fdf311f875ce0fe9ae6761987a3c111fda82d5aa31679`.

O gráfico consulta a base ativa, cuja versão pode ser conferida em [Dados](/dados). Uma atualização dessa base não altera o recorte dos resultados textuais publicados.

## Fonte e exploração

Fonte dos indicadores: Atlas do Desenvolvimento Humano no Brasil — PNUD, Ipea e Fundação João Pinheiro (FJP), Censos Demográficos IBGE e distribuição Ipeadata; cadastro territorial IBGE de 2010. Confira [proveniência e cobertura](/docs/dados/origem-e-proveniencia) e o [guia de filtros](/docs/guia/filtros-exportacao-e-links).
