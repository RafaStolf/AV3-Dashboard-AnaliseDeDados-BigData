---
title: "Conheça o Brasil em Perspectiva"
slug: visao-geral
category: comecar
summary: "Objetivo, escopo histórico e caminhos para explorar as cinco perguntas do projeto."
status: published
order: 10
updated_at: "2026-11-18"
---

## Propósito e escopo

Brasil em Perspectiva — Territórios em Foco é um projeto acadêmico para explorar o desenvolvimento humano dos municípios brasileiros. O recorte previsto abrange 1991, 2000 e 2010, com navegação por município, UF e grande região. A carga foi concluída e conferida: são 16.695 observações de 5.565 unidades no cadastro de 2010, 27 UFs e cinco regiões. Há 5.564 valores válidos por indicador e ano; Nazária/PI tem as cinco métricas nulas nas três linhas, sem imputação.

Os indicadores são históricos e não descrevem a situação atual de uma cidade. O Atlas utiliza informações dos Censos Demográficos desses três anos, conforme a [apresentação do PNUD](https://www.undp.org/pt/brazil/atlas-dos-municipios).

## Cinco perguntas

1. Quais municípios têm os maiores e os menores valores de IDHM no recorte?
2. Onde ocorreu maior avanço entre dois períodos comparáveis?
3. Qual dimensão tem o menor valor em cada município?
4. Como a distribuição municipal dos indicadores difere entre regiões?
5. Existe associação entre renda per capita e IDHM Educação no mesmo ano?

Cada pergunta exige observações válidas específicas. Por isso, gráficos diferentes podem apresentar quantidades diferentes de municípios mesmo com filtros semelhantes.

## Caminhos de navegação

O [Panorama](/) apresenta o recorte; [Comparar](/comparar) aproxima municípios; [Evolução](/evolucao) acompanha períodos. [Distribuições](/distribuicao) mostra o boxplot regional e [Renda e educação](/associacao) apresenta a dispersão. Em [Dados](/dados), consulte proveniência e disponibilidade. As [Análises](/analises) explicam as perguntas; a [Documentação](/docs) registra métodos e regras de uso.

O endereço canônico é [dashboard.stolner.com](https://dashboard.stolner.com). Sua indicação não comprova disponibilidade ou implantação.

## Estado do trabalho

O pipeline produziu CSV e manifesto, e a carga SQLite foi reconciliada com as cinco métricas do CSV. Os 585 valores do legado RMSP também coincidem. As análises combinam explicações metodológicas e resultados de referência explicitamente vinculados aos hashes dos dados e do manifesto. Seus gráficos ao vivo abrem no Brasil em 2010 e permitem escolher os três anos. Alterar o gráfico não altera o recorte das afirmações publicadas. A verificação dos dados não comprova deploy ou recuperação operacional.

Comece por [origem dos dados](/docs/dados/origem-e-proveniencia) e pelo [guia de filtros](/docs/guia/filtros-exportacao-e-links).

Fonte dos indicadores: Atlas do Desenvolvimento Humano no Brasil — PNUD, Ipea e Fundação João Pinheiro (FJP). Elaboração: equipe do projeto.
