---
title: "Arquitetura, publicação e recuperação"
slug: arquitetura-publicacao-e-recuperacao
category: desenvolvimento
summary: "SPA Vue com API Laravel, contratos atuais, publicação e recuperação segura."
status: published
order: 10
updated_at: "2026-11-18"
---

## Arquitetura implementada

A aplicação usa Laravel como backend, SQLite como banco e Vue 3 com TypeScript como SPA. Vue Router controla a navegação; o frontend consulta a API Laravel na mesma origem. A integração atual não usa Inertia. Vite prepara os assets; versões e comandos devem seguir os manifestos e arquivos de lock.

Os gráficos usam ECharts. O componente examinado inicializa o renderer Canvas e registra a geometria do mapa por GeoJSON de UFs. SVG é uma alternativa de renderização/cartografia que exige configuração própria; não se deve deduzir sua utilização apenas porque a página contém um mapa. O mapa atual resume municípios por UF e não apresenta todos os polígonos municipais.

## Rotas atuais

| Endpoint | Uso |
| --- | --- |
| `/api/bootstrap` | Cadastro, anos, quantidade de observações e dataset com SHA e manifesto. |
| `/api/dashboard` | Observações do recorte, resumo, histórias, deltas 1991–2010, quartis e Pearson. |
| `/api/export` | CSV de observações com filtros de região, UF e ano opcional. |
| `/api/content/docs` | Documentação publicada, com busca opcional por `q`. |
| `/api/content/analises` | Artigos publicados, com busca opcional por `q`. |

As páginas são `/`, `/comparar`, `/evolucao`, `/distribuicao`, `/associacao`, `/dados`, `/docs`, `/analises` e `/sobre`, além dos detalhes de artigos e documentos. Não há contrato `/api/v1` implementado. Os parâmetros reais estão no [guia](/docs/guia/filtros-exportacao-e-links).

## Dados e conteúdo

A carga ativa possui 16.695 observações. O importador substitui a base em transação; o banco atual não conserva múltiplas edições por município/ano. Preserve CSV e manifesto fora desse ciclo para reproduzir artigos anteriores.

Documentos usam `content/docs/{category}/{slug}.md`; artigos usam `content/analises/{slug}.md`. Metadados planos identificam título, slug, categoria, resumo, status, ordem e data; artigos acrescentam autor e identificador de gráfico. O serviço publica somente `published`, remove HTML de entrada e bloqueia links inseguros ao renderizar Markdown. Isso descreve o código, não uma auditoria integral de segurança.

Os artigos abrem com gráfico nacional de 2010 e seleção dos três anos. O texto fixa dados e manifesto por SHA-256; o gráfico usa a carga ativa. Se ela mudar, os hashes do texto continuam identificando os resultados originais e precisam ser confrontados com o bootstrap.

## Publicação

O domínio canônico é [dashboard.stolner.com](https://dashboard.stolner.com). A arquitetura de publicação usa VPS e Cloudflare; não se afirma aqui que DNS, HTTPS ou recuperação foram testados.

Antes de release, instalar dependências dos locks, preparar configuração privada, executar build e verificações e revisar migrações. Servir somente `public/` do Laravel, manter banco e arquivos privados fora da raiz pública, desativar debug e não publicar segredos em Git, conteúdo ou assets. Conferir páginas, API, filtros, cálculos, exportação e bloqueio de arquivos privados.

## Backup e recuperação

Definir periodicidade e retenção conforme tolerância à perda e tempo de recuperação. Guardar banco consistente, conteúdo, versão da release, CSV, manifesto e configuração necessária. Preservar downloads originais para reconstrução offline; proteger segredos em armazenamento adequado e manter cópia independente do servidor.

Usar backup consistente do SQLite ou controlar gravações. Copiar só o arquivo principal durante atividade, especialmente com WAL, não garante recuperação. Testar restauração isolada, integridade, esquema, relações e cálculos. Voltar arquivos não desfaz migrações: reversão exige conjunto compatível e ponto de recuperação registrado.

Fonte dos indicadores históricos: Atlas do Desenvolvimento Humano no Brasil — PNUD, Ipea e Fundação João Pinheiro (FJP), distribuição Ipeadata e cadastro IBGE, anos 1991, 2000 e 2010. Veja [modelo](/docs/modelagem/modelo-de-dados) e [proveniência](/docs/dados/origem-e-proveniencia).
