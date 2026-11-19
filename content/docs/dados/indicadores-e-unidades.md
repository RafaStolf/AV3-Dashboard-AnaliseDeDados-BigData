---
title: "Indicadores, unidades e ausências"
slug: indicadores-e-unidades
category: dados
summary: "Dicionário mínimo de IDHM, dimensões, renda per capita e valores ausentes."
status: published
order: 20
updated_at: "2026-11-18"
---

## Dicionário mínimo

O CSV e a tabela de observações usam os campos `idhm`, `idhm_educacao`, `idhm_renda`, `idhm_longevidade` e `renda_per_capita`. O manifesto registra as cinco séries censitárias correspondentes do Ipeadata.

| Indicador | Unidade | Cuidado de leitura |
| --- | --- | --- |
| IDHM | Índice adimensional entre 0 e 1 | Não é percentual de pessoas. |
| IDHM Educação | Índice adimensional entre 0 e 1 | Componente educacional definido pelo Atlas. |
| IDHM Renda | Índice adimensional entre 0 e 1 | Não equivale à renda em reais. |
| IDHM Longevidade | Índice adimensional entre 0 e 1 | Não é idade em anos. |
| Renda per capita | Renda domiciliar mensal por pessoa, em reais de 1º de agosto de 2010 | Não é salário médio nem PIB per capita. |

Esse resumo não substitui a metodologia oficial. O projeto consulta índices publicados; não propõe reconstruí-los a partir de variáveis primárias sem especificação validada.

## Renda e comparabilidade

A unidade está confirmada nos metadados `ADH_RDPC` do manifesto: renda domiciliar mensal per capita das pessoas residentes em domicílios particulares permanentes, em reais de 1º de agosto de 2010. A fonte já fornece essa base de preços; o pipeline não aplica deflator.

As séries dos três anos compartilham a referência monetária documentada. Não misturar renda nominal de outra fonte ou edição sem tratamento explícito. A análise de associação usa um ano por vez. Não há ponderadores populacionais no CSV: média de rendas municipais não equivale à renda per capita oficial do país.

## Ausências e precisão

Ausente significa indisponível, inválido ou não aplicável, conforme o motivo registrado. Nunca substituir automaticamente por zero. Um zero válido é uma observação. A lacuna desta carga é Nazária/PI: cinco métricas nulas em 1991, 2000 e 2010, totalizando 15 células sem imputação. Valores não finitos, textos inválidos e índices fora dos limites exigem revisão.

Conservar a precisão disponibilizada pela fonte. Ordenar e calcular antes do arredondamento de apresentação. Valores exibidos com poucas casas podem parecer empatados apesar de serem diferentes; a tabela deve permitir compreender essa precisão.

A diferença entre índices é expressa em pontos de índice. Não usar pontos percentuais para a escala adimensional sem transformação declarada.

## Referência

Fonte: Atlas do Desenvolvimento Humano no Brasil — PNUD, Ipea e Fundação João Pinheiro (FJP). Consulte a [apresentação institucional](https://www.undp.org/pt/brazil/atlas-dos-municipios), o dicionário da edição importada e [proveniência](/docs/dados/origem-e-proveniencia). Os anos 1991, 2000 e 2010 são históricos, sem diagnóstico atual.
