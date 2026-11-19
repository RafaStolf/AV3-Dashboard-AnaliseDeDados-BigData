---
title: "Importação e validação da base"
slug: importacao-e-validacao
category: modelagem
summary: "Critérios de publicação, reconciliação e tratamento de erros sem inventar completude."
status: published
order: 20
updated_at: "2026-11-18"
---

## Carga concluída e conferida

O pipeline gerou `data/atlas_municipios.csv` e `data/manifest.json`; a carga Laravel já foi importada no SQLite. A conferência independente leu os arquivos e o banco sem modificar a carga.

| Verificação realizada | Resultado |
| --- | --- |
| Linhas CSV e observações SQLite | 16.695 em cada um. |
| Cadastro | 5.565 unidades, 27 UFs, cinco regiões. |
| Anos | 1991, 2000 e 2010; 5.565 linhas por ano. |
| Chave município/ano | 16.695 combinações distintas. |
| Linhas completas | 16.692 no total; 5.564 por ano. |
| Ausências | Apenas Nazária/PI: três linhas, cinco métricas nulas em cada. |
| CSV versus banco | Igualdade das cinco métricas em todas as linhas por código e ano. |
| Regressão do legado RMSP | 585 valores coincidentes em 117 linhas. |
| Integridade do arquivo | SHA-256 calculado igual ao registrado no manifesto e em datasets. |

Esses resultados se referem ao snapshot descrito em [proveniência](/docs/dados/origem-e-proveniencia), não a uma garantia sobre futuras coletas ou sobre a disponibilidade da produção.

## Pipeline e controles

A coleta usa séries censitárias oficiais do Ipeadata, preserva respostas originais e confere códigos contra o cadastro IBGE de 2010. As verificações incluem cabeçalho, anos, duplicidades, cobertura, código/UF, números finitos, limites dos índices e renda não negativa. Ausências não viram zero. O manifesto registra a checagem da média geométrica das dimensões com tolerância de 0,002 para arredondamento; o maior erro observado é aproximadamente 0,000500.

O importador Laravel valida o contrato do CSV e substitui datasets, territórios e observações numa transação. Chaves estrangeiras e unicidade protegem as relações. Falha na transação reverte a substituição. A checagem completa de fonte e cobertura pertence ao pipeline; a existência do comando de importação não substitui essa etapa.

## Verificação e reprodução

Para conferir o CSV contra o manifesto, sem rede e sem reconstruir dados:

```powershell
python scripts/data/build_atlas.py --verify
```

A opção `--offline` reconstrói a partir dos downloads preservados; `--refresh` faz nova coleta. Ambas são operações de manutenção, não ações de visitante. Nova coleta pode receber correções históricas e gerar outro hash. Reimportar substitui a versão ativa, portanto preservar o snapshot anterior antes dessa operação.

## Alcance da validação

A reconciliação comprova fidelidade entre os artefatos examinados e o legado, não ausência de erros na fonte primária. Backup restaurável, deploy público e concordância de todos os estados da interface precisam de verificações próprias. Não estão comprovados pela contagem de linhas.

Fonte: Atlas do Desenvolvimento Humano no Brasil — PNUD, Ipea e Fundação João Pinheiro (FJP), Censos IBGE e distribuição Ipeadata. Os anos 1991, 2000 e 2010 são históricos, sem diagnóstico atual. Consulte o [modelo efetivo](/docs/modelagem/modelo-de-dados).
