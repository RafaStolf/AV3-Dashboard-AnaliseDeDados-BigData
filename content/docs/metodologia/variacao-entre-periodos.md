---
title: "Variação entre períodos com municípios pareados"
slug: variacao-entre-periodos
category: metodologia
summary: "Diferença absoluta, interseção de territórios e limites da comparação temporal."
status: published
order: 20
updated_at: "2026-11-18"
---

## Pergunta e implementação atual

Onde ocorreu maior avanço? O painel de evolução mostra trajetórias em 1991, 2000 e 2010 e calcula o ranking de avanços no intervalo fixo 1991–2010. O parâmetro `year` seleciona o ano de outras leituras, mas não muda os extremos desse delta. `a` e `b` são códigos dos municípios em comparação, não anos.

O cadastro é de 2010; as séries anteriores foram harmonizadas pelos produtores para essas áreas. O pareamento usa código exato. A comparação descreve essas unidades harmonizadas, não os limites administrativos existentes em cada censo.

## Diferença absoluta

Para cada município com valores válidos nos dois extremos, calcular `delta = valor_2010 - valor_1991`. Índices usam pontos de índice; renda usa reais na base de preços de 1º de agosto de 2010. Ausência em qualquer extremo exclui o par, sem diferença zero artificial.

A API arredonda o delta a seis casas, ordena do maior para o menor e usa código para desempate. O gráfico apresenta os primeiros 12. A ordenação não demonstra superioridade entre deltas iguais. No recorte nacional, há 5.564 pares válidos; Nazária/PI não participa.

## Resumos e duração

Mediana dos deltas exige calcular cada diferença primeiro. Em geral, ela não equivale à diferença entre medianas anuais. A API também fornece medianas de cada ano sobre seus valores válidos; não as apresenta como mediana das diferenças. Neste snapshot, o conjunto válido é o mesmo nos três anos, mas isso deve ser revisto em futuras cargas.

O delta absoluto não é uma taxa anual. Intervalos 1991–2000 e 2000–2010 têm durações diferentes. Seleção livre de dois anos, taxa anual e variação percentual são extensões possíveis, não recursos atuais; percentual exigiria tratar início zero.

## Resultado e limites

O CSV de referência confirma maior aumento de IDHM de 0,493 ponto em Bodó/RN, de 0,136 para 0,629, entre 1991 e 2010. A mediana das diferenças municipais é 0,276 ponto. Veja o [artigo de evolução](/analises/comparar-avancos-no-tempo) para versão e alcance.

O avanço não comprova efeito de política pública, melhora de todos os grupos ou continuidade após 2010. Geografias e metodologias incompatíveis não devem ser misturadas. Dados históricos não são diagnóstico atual.

Fonte: Atlas do Desenvolvimento Humano no Brasil — PNUD, Ipea e Fundação João Pinheiro (FJP), distribuição Ipeadata e cadastro IBGE. Consulte [validação](/docs/modelagem/importacao-e-validacao).
