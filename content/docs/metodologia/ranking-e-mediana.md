---
title: "Ranking municipal e mediana do recorte"
slug: ranking-e-mediana
category: metodologia
summary: "Ordenação, empates e resumo municipal sem atribuir caráter oficial a agregações derivadas."
status: published
order: 10
updated_at: "2026-11-18"
---

## Pergunta e conjunto analisado

Quais municípios apresentam maiores e menores valores de IDHM? O painel usa ano, métrica, região e UF selecionados. Apenas valores não nulos participam da ordenação; não se misturam municípios e agregados oficiais.

Em 2010, o recorte nacional contém 5.565 unidades cadastradas e 5.564 valores válidos. Nazária/PI fica fora da ordenação numérica, mas continua no cadastro. Posição numa UF não equivale a posição nacional.

## Ordenação implementada

O gráfico ordena os valores armazenados, antes do arredondamento, em ordem decrescente ou crescente conforme a aba. Em valores iguais, utiliza o nome municipal para apresentação e exibe no máximo 12 barras. Não inclui automaticamente todos os empatados no corte, nem publica uma coluna de posições com regra de competição.

Portanto, a ordem de barras empatadas não indica diferença de desenvolvimento. A tabela completa permite consultar outros municípios. Uma futura classificação numérica poderia usar posição igual para valores iguais, mas essa é uma extensão, não o comportamento atual.

## Mediana municipal

Ordenar os valores válidos. Com quantidade ímpar, selecionar o central; com quantidade par, tirar a média dos dois centrais. Com um valor, retornar esse valor; sem valores, retornar indisponível. A implementação equivale ao quantil R7 de probabilidade 0,5.

Cada município tem o mesmo peso. A mediana descreve municípios, não habitantes, e não é IDHM oficial de região, UF ou Brasil. O mapa atual agrupa municípios por UF e representa suas medianas, sem transformar esse resumo em índice estadual oficial.

## Referência e limites

O cálculo independente do CSV de referência encontra mediana nacional municipal de 0,665 em 2010, com 5.564 valores. Os extremos são documentados no [artigo sobre ranking](/analises/ler-ranking-municipal), vinculados ao snapshot.

Diferenças pequenas de índice podem mudar a ordenação; valores arredondados podem parecer empatados. Ranking não identifica causas ou desigualdades internas. Observações de 1991, 2000 e 2010 não constituem diagnóstico atual.

Fonte: Atlas do Desenvolvimento Humano no Brasil — PNUD, Ipea e Fundação João Pinheiro (FJP), distribuição Ipeadata e cadastro IBGE. Consulte [indicadores](/docs/dados/indicadores-e-unidades) e [proveniência](/docs/dados/origem-e-proveniencia).
