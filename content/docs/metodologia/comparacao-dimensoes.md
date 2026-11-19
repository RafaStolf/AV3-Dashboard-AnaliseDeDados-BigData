---
title: "Comparação das dimensões do IDHM"
slug: comparacao-dimensoes
category: metodologia
summary: "Menor componente municipal, escala comum, empates e interpretação responsável."
status: published
order: 30
updated_at: "2026-11-18"
---

## Pergunta e requisitos

Qual dimensão apresenta o menor valor em cada município? Comparar IDHM Educação, IDHM Renda e IDHM Longevidade no mesmo município, ano e edição. Todas devem compartilhar a escala de 0 a 1.

Não comparar diretamente renda per capita em moeda com um índice de educação. IDHM Renda e renda per capita são variáveis diferentes.

## Regra de cálculo

Para cada município com as três dimensões válidas, calcular `menor = min(educacao, renda, longevidade)`. Identificar todas as dimensões com valor armazenado igual ao mínimo. Preservar empates sem escolher arbitrariamente um componente.

A diferença `amplitude = max(dimensoes) - min(dimensoes)`, em pontos de índice, pode ajudar a descrever o contraste entre componentes. Ela não mede desigualdade de renda nem dispersão entre moradores.

Se faltar uma dimensão, a conclusão sobre o menor componente fica indisponível. Valores existentes podem ser mostrados com aviso de incompletude. Resumos de frequência devem informar o denominador de municípios completos e explicar que empates podem entrar em mais de uma categoria; nesse caso, as frequências somadas podem exceder o denominador.

## Como ler

A página Comparar implementa barras agrupadas dos componentes dos dois municípios selecionados. A escala comum evita comparações visuais enganosas. Uma matriz de calor seria uma extensão futura. A identificação do mínimo descrita aqui é uma regra analítica: as contagens nacionais do artigo foram calculadas diretamente no CSV, não são um campo pronto da API. Consulte os valores e não dependa só de cor para reconhecer diferenças.

## Referência calculada

Em 2010 há 5.564 municípios com as três dimensões válidas. Educação está no mínimo em 5.245, renda em 330 e longevidade em nenhum; 11 municípios empatam educação e renda. As frequências incluem empates e somam mais que o total. Veja [o artigo](/analises/interpretar-dimensoes-do-idhm) para os hashes da versão.

## Limites

O menor componente é uma posição relativa dentro do perfil municipal. Não prova causa de um IDHM menor, não mede isoladamente qualidade de serviço e não determina prioridade automática de investimento. Cada dimensão tem definição própria e pode ocultar desigualdades internas.

Os dados de 1991, 2000 e 2010 são históricos. Decisões atuais exigem outras evidências e informações recentes.

Fonte: Atlas do Desenvolvimento Humano no Brasil — PNUD, Ipea e Fundação João Pinheiro (FJP). Consulte [indicadores](/docs/dados/indicadores-e-unidades) e [o artigo sobre dimensões](/analises/interpretar-dimensoes-do-idhm).
