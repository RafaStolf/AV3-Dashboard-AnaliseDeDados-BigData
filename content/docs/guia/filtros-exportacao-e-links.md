---
title: "Filtros, tabelas, exportação e links"
slug: filtros-exportacao-e-links
category: guia
summary: "Como manter o recorte visível, compartilhar consultas e conferir arquivos exportados."
status: published
order: 10
updated_at: "2026-11-18"
---

## Páginas e seleção

O painel oferece [Panorama](/), [Comparar](/comparar), [Evolução](/evolucao), [Distribuições](/distribuicao), [Renda e educação](/associacao) e [Dados](/dados). [Documentação](/docs), [Análises](/analises) e [Sobre](/sobre) completam a navegação.

O padrão é Brasil, ano 2010 e IDHM. Região e UF restringem o conjunto municipal. Ao mudar região, a seleção de UF é limpa; códigos de comparação incompatíveis são ajustados aos territórios disponíveis, com aviso. A renda e a educação na dispersão são fixas, mesmo que outro indicador esteja selecionado para outras visualizações.

A base tem 5.565 unidades por ano, mas 5.564 valores válidos para cada métrica. Nazária/PI permanece na tabela com ausências. Não confundir quantidade cadastral com tamanho utilizado no cálculo.

## Parâmetros reais da URL

| Chave | Significado e valores |
| --- | --- |
| `v` | Versão do estado, atualmente `1`. Versão desconhecida retorna aos padrões. |
| `year` | `1991`, `2000` ou `2010`; padrão `2010`. |
| `metric` | `idhm`, `idhm_educacao`, `idhm_renda`, `idhm_longevidade` ou `renda_per_capita`; padrão `idhm`. |
| `region` | Norte, Nordeste, Centro-Oeste, Sudeste ou Sul; vazio significa todas. |
| `uf` | Sigla em maiúsculas pertencente ao cadastro; vazio significa todas no recorte. |
| `a`, `b` | Códigos IBGE textuais de sete dígitos dos dois municípios em comparação. |

Os padrões de comparação são `a=3550308` e `b=2927408`, São Paulo/SP e Salvador/BA. Eles não restringem ranking, distribuição, correlação ou exportação a dois municípios: selecionam histórias e comparações dentro do recorte de região/UF.

Exemplo de [distribuição nacional de 2010](/distribuicao?v=1&year=2010&metric=idhm). A página [Evolução](/evolucao?v=1&year=2010&metric=idhm) mostra trajetórias dos três anos e avanços fixos de 1991 a 2010; `a` e `b` não são anos inicial e final.

A URL alimenta o estado e o histórico de navegação; o botão de compartilhar copia o endereço atual. Datas e indicadores inválidos recebem padrões no parser; combinações territoriais são conferidas com o cadastro. Confira o resumo dos filtros após abrir um link. O domínio canônico é [dashboard.stolner.com](https://dashboard.stolner.com).

## Artigos e reprodução

Os gráficos dos artigos abrem no Brasil em 2010 e permitem escolher 1991, 2000 ou 2010. Trocar o ano altera os gráficos dependentes do ano, não as afirmações textuais calculadas para 2010. O gráfico de dimensões ilustra apenas São Paulo/SP e Salvador/BA, embora os resultados textuais contem todos os municípios válidos do país. O artigo de evolução declara separadamente o intervalo 1991–2010.

Um link preserva filtros, não uma cópia imutável do banco. Para reproduzir uma afirmação, guardar CSV, manifesto, hashes e recorte descritos no artigo. A aplicação usa a carga ativa, que pode ser substituída.

## Tabela e exportação

A tabela do painel tem busca local e páginas de 25 linhas. O download usa `/api/export`, não `/dados/exportar`, e inclui as observações de todo o recorte de região/UF e ano solicitado. Busca textual da tabela, página atual e municípios `a`/`b` não limitam esse arquivo.

O [CSV nacional de 2010](/api/export?year=2010) é diferente do [CSV dos três anos](/api/export): sem `year`, o endpoint exporta todos os anos. O formato de download é UTF-8 com BOM, vírgula, ponto decimal e ausências vazias. São exportadas observações e cinco métricas, não ranking, quartis ou Pearson. O código trata campos textuais contra fórmulas de planilha.

A exportação atual não embute manifesto, método ou hash e não é necessariamente idêntica em bytes ao CSV original. Arquive sua proveniência à parte. Importe códigos como texto na planilha para preservar identidade. Tabela e gráficos oferecem formas complementares de consulta; não presumir que todo detalhe gráfico já tenha uma tabela específica.

Fonte: Atlas do Desenvolvimento Humano no Brasil — PNUD, Ipea e Fundação João Pinheiro (FJP), distribuição Ipeadata e cadastro IBGE. Dados históricos de 1991, 2000 e 2010, sem diagnóstico atual. Veja [proveniência](/docs/dados/origem-e-proveniencia).
