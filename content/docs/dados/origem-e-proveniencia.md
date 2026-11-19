---
title: "Origem, cobertura e proveniência"
slug: origem-e-proveniencia
category: dados
summary: "Como identificar a edição do Atlas, a cobertura efetiva e as condições de reutilização."
status: published
order: 10
updated_at: "2026-11-18"
---

## Fonte e coleta

Fonte dos indicadores: Atlas do Desenvolvimento Humano no Brasil — PNUD, Ipea e Fundação João Pinheiro (FJP), com dados dos Censos Demográficos do IBGE e distribuição pela API oficial do Ipeadata. Foram coletadas as séries censitárias `ADH_IDHM`, `ADH_IDHM_E`, `ADH_IDHM_R`, `ADH_IDHM_L` e `ADH_RDPC` para 1991, 2000 e 2010.

O cadastro territorial vem do IBGE SIDRA, tabela 1301, período 2010. UF e grande região são conferidas pela API de localidades do IBGE. A coleta foi registrada em 19/11/2026 UTC, correspondente à noite de 18/11/2026 no horário de Brasília. Datas de coleta, importação e publicação não são o ano da observação: os dados continuam históricos, sem diagnóstico atual.

## Cobertura verificada

O CSV e o banco importado contêm 16.695 observações: 5.565 unidades de nível municipal, 27 UFs e cinco regiões, em três anos. Cada ano tem 5.565 linhas, das quais 5.564 contêm as cinco métricas; são 16.692 linhas completas no conjunto.

Nazária/PI, código `2206720`, está no cadastro de referência, mas não consta nas cinco séries coletadas. Suas três linhas preservam os cinco valores nulos: 15 células ausentes, sem imputação. Ela conta no cadastro, mas não nas medianas, correlações ou deltas que exigem valores válidos.

Brasília/DF e Fernando de Noronha/PE são unidades do nível municipal do IBGE; isso não implica regime jurídico de município comum.

## Versão e rastreabilidade

O manifesto `data/manifest.json` registra URLs, datas de download, metadados, checksums dos arquivos brutos, cobertura, regras territoriais e validações. A presente descrição usa:

- SHA-256 do CSV normalizado: `a4aa7b86737fd6fdeb7bd7e178dfaf83603471189880720590699e44b58ea45a`.
- SHA-256 dos bytes do manifesto consultado: `604aa709827db0d5054fdf311f875ce0fe9ae6761987a3c111fda82d5aa31679`.

O primeiro identifica os dados; o segundo identifica o registro de proveniência desta publicação. Regenerar o manifesto pode mudar seu hash sem alterar o CSV. A conferência independente comparou todas as cinco métricas do CSV com o SQLite e encontrou igualdade nas 16.695 linhas. Os 585 valores das 117 linhas do legado RMSP também coincidem.

## Geografia e reutilização

As séries anteriores foram harmonizadas pelos produtores para áreas de 2010. Não representam os limites administrativos existentes em 1991 ou 2000. Os nomes são rótulos devolvidos na coleta e podem incluir renomeações posteriores; o relacionamento usa código exato de sete dígitos. Municípios fora do cadastro de 2010 não recebem dados artificiais.

A licença específica de redistribuição não foi confirmada. Acesso público e licença do código não substituem termos dos provedores. Limites cartográficos exigem proveniência própria.

Referências: [PNUD](https://www.undp.org/pt/brazil/atlas-dos-municipios), [API Ipeadata](https://www.ipeadata.gov.br/api/), [SIDRA 2010](https://apisidra.ibge.gov.br/values/t/1301/n6/all/v/615/p/2010) e [metodologia do Atlas](https://www.ipeadata.gov.br/doc/Metodologia%20ADH.pdf). Leia [validação](/docs/modelagem/importacao-e-validacao).
