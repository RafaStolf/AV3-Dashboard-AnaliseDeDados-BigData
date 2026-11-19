---
title: "Modelo de dados e relações"
slug: modelo-de-dados
category: modelagem
summary: "Tabelas efetivas, chaves, cadastro de 2010 e extensões futuras claramente separadas."
status: published
order: 10
updated_at: "2026-11-18"
---

## Modelo implementado

A migração `2026_11_18_000001_create_atlas_tables.php` implementa três tabelas no SQLite. A carga verificada possui um dataset, 5.565 territórios e 16.695 observações. O contrato atual armazena as cinco métricas em colunas de cada linha municipal por ano.

| Tabela | Campos efetivos | Chaves e relações |
| --- | --- | --- |
| datasets | `id`, `name`, `sha256`, `manifest`, `imported_at` | `id` identifica a carga; `sha256` registra o CSV e `manifest` guarda seu contexto. |
| territories | `code`, `name`, `uf`, `region` | `code` é chave primária textual de sete dígitos; UF e região possuem índices. |
| observations | `id`, `dataset_id`, `territory_code`, `year`, `idhm`, `idhm_educacao`, `idhm_renda`, `idhm_longevidade`, `renda_per_capita` | Chaves estrangeiras para dataset e território; ano indexado; métricas anuláveis. |

Um dataset tem muitas observações e um território tem observações dos três anos. A unicidade implementada é `(territory_code, year)`, sem `dataset_id`. Portanto, o esquema não permite armazenar simultaneamente duas edições para o mesmo território e ano. O importador substitui a carga anterior em transação, em vez de acrescentar um histórico de versões.

## Territórios e unidade temporal

O cadastro é fixado na referência de 2010, com UF e região como atributos diretos de cada território. Não há tabela de grupos com vigência. Os indicadores censitários de 1991 e 2000 foram harmonizados pelos produtores para essa referência. Nomes são rótulos; códigos textuais constituem a identidade.

Nazária/PI permanece no cadastro e possui três observações com as cinco métricas nulas. Nulo não equivale a zero nem elimina o território do denominador cadastral. Brasília/DF e Fernando de Noronha/PE seguem o nível municipal da fonte.

## Métricas e derivados

Não existe tabela `indicators` implementada: as métricas permitidas são definidas no código e no dicionário. Também não existe `territory_memberships`. Essas entidades eram possibilidades do desenho conceitual, não componentes atuais.

Mediana, delta, quartis e Pearson são calculados pelos serviços a partir das observações. Não são gravados como se fossem valores oficiais de UF ou região. Esta base contém observações municipais e não inclui ponderadores populacionais.

## Extensões futuras

Histórico simultâneo de datasets exigiria rever unicidade e consultas para selecionar explicitamente uma versão. Indicadores dinâmicos ou vínculos territoriais com vigência exigiriam novas tabelas, migrações e contratos; não são capacidades já disponíveis. Arquivar CSV e manifesto continua necessário para reproduzir artigos após substituição da carga.

Fonte: Atlas do Desenvolvimento Humano no Brasil — PNUD, Ipea e Fundação João Pinheiro (FJP), distribuição Ipeadata e cadastro IBGE. Dados históricos de 1991, 2000 e 2010. Consulte [importação](/docs/modelagem/importacao-e-validacao) e [proveniência](/docs/dados/origem-e-proveniencia).
