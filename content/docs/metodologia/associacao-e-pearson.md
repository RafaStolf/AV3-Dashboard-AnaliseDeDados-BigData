---
title: "Associação entre renda e educação"
slug: associacao-e-pearson
category: metodologia
summary: "Pares completos, coeficiente de Pearson, tamanho amostral e limites de inferência."
status: published
order: 50
updated_at: "2026-11-18"
---

## Pergunta e pares completos

Existe associação entre renda per capita e IDHM Educação? Selecionar um ano, edição e recorte. Para cada município, formar um par com `x = renda per capita` e `y = IDHM Educacao`. Incluir apenas pares completos, numéricos, finitos e validados. Não combinar renda de um município com educação de outro.

Informar `n`, a quantidade de pares utilizados, e exclusões por ausência ou invalidez. A quantidade é municipal, não de pessoas. Não empilhar 1991, 2000 e 2010 como observações independentes no mesmo coeficiente.

## Pearson

Sobre os mesmos pares, obter `media_x` e `media_y`. Calcular:

`r = soma((x - media_x) * (y - media_y)) / raiz(soma((x - media_x)^2) * soma((y - media_y)^2))`

Exigir pelo menos dois pares e variação não nula nas duas variáveis. Caso contrário, retornar coeficiente indisponível e motivo. Com dois pares distintos, o coeficiente é extremo e pouco informativo como resumo; explicitar esse limite.

Pearson mede associação linear. O sinal descreve direção; a magnitude descreve intensidade linear no recorte. Valor próximo de zero não exclui relação não linear. O [manual estatístico do R](https://stat.ethz.ch/R-manual/R-devel/library/stats/html/cor.html) documenta correlação e uso de observações completas.

## Leitura da dispersão

Cada ponto é um município. A renda está expressa em reais de 1º de agosto de 2010, conforme os metadados da carga. Mostrar ano, fonte, `r` e `n`. A tabela permite conferir os pares; transparência e seleção ajudam quando pontos se sobrepõem.

Não remover extremos por aparência. Erro comprovado exige correção ou exclusão registrada. Se a apresentação usar eixo logarítmico, identificá-lo: o coeficiente definido aqui continua calculado nos valores originais. Correlação sobre valores transformados precisa de identificação separada.

## Resultado de referência

O cálculo independente no CSV nacional de 2010 encontra `n = 5.564` e `r = 0,7913902393`, aproximadamente 0,7914, para renda per capita e IDHM Educação. Nazária/PI é excluída por ausência das duas métricas. O resultado está vinculado ao snapshot do [artigo](/analises/renda-e-educacao-sem-causalidade); o gráfico recalcula ao mudar o ano.

## Limites de inferência

Associação não demonstra causalidade. Composição regional, urbanização e outras variáveis podem se relacionar às duas medidas. Relações municipais não autorizam conclusões sobre indivíduos. IDHM Educação não é o IDHM total, que incorpora também renda.

O coeficiente descreve o conjunto disponível. Não apresentar significância estatística ou generalização sem desenho inferencial próprio. Dados históricos não constituem diagnóstico atual.

Fonte dos indicadores: Atlas do Desenvolvimento Humano no Brasil — PNUD, Ipea e Fundação João Pinheiro (FJP). Leia [o artigo sobre associação](/analises/renda-e-educacao-sem-causalidade).
