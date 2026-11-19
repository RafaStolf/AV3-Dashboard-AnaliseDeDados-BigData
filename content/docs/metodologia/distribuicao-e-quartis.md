---
title: "Distribuição regional e quartis do boxplot"
slug: distribuicao-e-quartis
category: metodologia
summary: "Quartis R7 implementados, extremidades mínimo/máximo e leitura da distribuição municipal."
status: published
order: 40
updated_at: "2026-11-18"
---

## Unidade e agrupamento

Como a distribuição dos indicadores difere entre regiões? A implementação fixa o ano e o indicador selecionados e agrupa as observações municipais válidas por grande região. Cada município recebe o mesmo peso; não há ponderação populacional.

A base importada usa cadastro de 2010. Em cada ano são 5.564 valores válidos e uma unidade sem métricas, Nazária/PI. O gráfico informa `n` por grupo. Com filtro de UF ou região, descreve apenas o recorte; grupos sem valores não recebem caixa artificial.

## Quartis R7 implementados

O serviço estatístico já utiliza interpolação linear R7. Para valores ordenados `x[1] ... x[n]`, calcular para probabilidade `p`:

- `h = 1 + (n - 1) * p`, `j = floor(h)` e `g = h - j`.
- Para `j < n`, `Q(p) = x[j] + g * (x[j+1] - x[j])`.
- Quando `j = n`, usar `x[n]`. Com um valor, todos os quantis coincidem; sem valores, o quantil é indisponível.

Q1, mediana e Q3 usam `p = 0,25`, `0,50` e `0,75`. A definição corresponde ao tipo 7 do [manual do R](https://stat.ethz.ch/R-manual/R-devel/library/stats/html/quantile.html). É uma convenção do dashboard, não um método atribuído ao Atlas.

## Extremidades atuais: mínimo e máximo

A API entrega cinco valores: mínimo, Q1, mediana, Q3 e máximo. A caixa vai de Q1 a Q3; os bigodes chegam ao mínimo e ao máximo observados. O gráfico atual não usa cercas de 1,5 IQR nem separa pontos extremos por essa regra.

O intervalo interquartil continua definido por `IQR = Q3 - Q1`. Com IQR zero, a caixa colapsa; valores fora dos quartis permanecem representados pelas extremidades. Com um município, não há dispersão observada. A implementação não exclui valores por parecerem extremos.

## Resultado de referência e limites

No snapshot publicado, a mediana municipal de IDHM em 2010 é 0,716 no Sul e 0,588 no Nordeste. As cinco medianas e respectivos tamanhos estão no [artigo de distribuições](/analises/comparar-distribuicoes-regionais). Esses são resumos de municípios, não IDHM oficial regional e não distribuição entre habitantes.

O boxplot não revela sozinho desigualdade dentro das cidades ou causas das diferenças. Para comparar anos, conferir composição e cobertura. Os períodos 1991, 2000 e 2010 são históricos, sem diagnóstico atual.

Fonte: Atlas do Desenvolvimento Humano no Brasil — PNUD, Ipea e Fundação João Pinheiro (FJP), distribuição Ipeadata e cadastro IBGE. Consulte [proveniência](/docs/dados/origem-e-proveniencia).
