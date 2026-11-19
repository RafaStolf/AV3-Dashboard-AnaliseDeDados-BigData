# Dados — Brasil em Perspectiva

O arquivo `atlas_municipios.csv` contém dados reais do Atlas do Desenvolvimento Humano no Brasil, distribuídos pela API pública oficial do Ipeadata. A coleta usa exclusivamente as séries censitárias `ADH_IDHM`, `ADH_IDHM_E`, `ADH_IDHM_R`, `ADH_IDHM_L` e `ADH_RDPC`.

## Contrato para importação

- CSV em UTF-8, sem BOM, separador vírgula, decimal ponto e cabeçalho na primeira linha. Ordenação por código IBGE e ano; quebra de linha LF.
- Colunas, nesta ordem: `codigo_ibge,municipio,uf,regiao,ano,idhm,idhm_educacao,idhm_renda,idhm_longevidade,renda_per_capita`.
- Chave única: `(codigo_ibge, ano)`. Tratar o código como texto de sete dígitos e o ano como inteiro.
- Campos numéricos vazios significam **ausência de informação**: importar como `NULL`, nunca como zero.
- Índices no intervalo fechado [0, 1]. Renda mensal per capita em reais de **1º de agosto de 2010**, unidade confirmada em `ADH_RDPC`. Os valores já vêm nessa base de preços; o pipeline não aplica deflator.
- `uf` usa sigla e `regiao` contém Norte, Nordeste, Sudeste, Sul ou Centro-Oeste.

## Cobertura e lacuna conhecida

O painel possui **16.695 registros**, correspondentes a **5.565 unidades municipais**, **27 UFs** e **cinco regiões**, em **1991, 2000 e 2010**. Há **16.692 registros completos**.

**Nazária/PI (`2206720`) está no cadastro IBGE de 2010, mas não consta em nenhuma das cinco séries municipais do Ipeadata na coleta.** Mantivemos três linhas com os cinco indicadores em branco: 15 células ausentes no total. Não houve preenchimento por estimativa, cópia de Teresina ou substituição por valores de outros anos. O pipeline interrompe a execução se surgirem outras lacunas de cobertura, para exigir investigação explícita. Eventuais valores nulos declarados pela fonte também são preservados e contabilizados.

Brasília/DF e Fernando de Noronha/PE integram o nível municipal do cadastro IBGE; sua inclusão não representa uma afirmação de que têm o mesmo regime jurídico de municípios comuns.

## Fontes e correspondência territorial

1. [API oficial do Ipeadata](https://www.ipeadata.gov.br/api/): metadados, valores e territórios. Exemplo: [metadados de renda](https://www.ipeadata.gov.br/api/odata4/Metadados('ADH_RDPC')) e [valores de IDHM](https://www.ipeadata.gov.br/api/odata4/Metadados('ADH_IDHM')/Valores). A filtragem por `NIVNOME = Municípios` exclui Brasil, UFs e outros níveis; o pipeline acompanha paginação OData quando existir.
2. [IBGE SIDRA, tabela 1301, período 2010](https://apisidra.ibge.gov.br/values/t/1301/n6/all/v/615/p/2010): lista oficial das 5.565 unidades municipais. A variável 615 é área territorial; **somente código e nome** são utilizados para construir o cadastro. A tabela não fornece os indicadores socioeconômicos deste CSV.
3. [API IBGE de UFs e regiões](https://servicodados.ibge.gov.br/api/v1/localidades/estados) e [cadastro municipal atual capturado](https://servicodados.ibge.gov.br/api/v1/localidades/municipios): validação das UFs, regiões e códigos.
4. [Metodologia do Atlas, hospedada no Ipeadata](https://www.ipeadata.gov.br/doc/Metodologia%20ADH.pdf): página 2 descreve o recálculo de 1991/2000 e a compatibilização territorial; a seção Renda per capita define a unidade monetária e população de referência.

Todos os códigos das observações encontradas coincidem exatamente com códigos de sete dígitos do IBGE, tanto no cadastro do período 2010 quanto no cadastro atual capturado. Não foi necessário inventar dígito verificador, converter códigos de seis dígitos, fazer aproximação por nomes ou aplicar tabela de correspondência histórica.

Os nomes usados são os rótulos que a consulta SIDRA para o período 2010 retorna **na data da coleta**. Podem incorporar renomeações posteriores; não se afirma que sejam a grafia oficial vigente em 2010. O manifesto registra diferenças de nome entre SIDRA e Ipeadata. O relacionamento é sempre pelo código.

Municípios do cadastro atual que não pertencem ao cadastro de 2010 são listados em `mapping.current_ibge_units_outside_2010_roster`; não recebem dados artificiais. Não usar o cadastro municipal atual inteiro como denominador de cobertura deste Atlas.

## Comparabilidade

Os produtores recalcularam 1991 e 2000 com a metodologia do Atlas de 2013 e compatibilizaram as áreas para permitir comparação com 2010. Portanto, a leitura temporal considera a geografia harmonizada de 2010, incluindo áreas que ainda não eram municípios independentes nos anos anteriores.

Não combinar diretamente estes valores com resultados de edições antigas do Atlas, com o IDH global de países, com séries encadeadas Censo/PNAD ou com limites municipais atuais. Um mapa deve usar malha de 2010 ou uma correspondência territorial explicitamente documentada; por isso não foi incluída uma malha atual como substituição.

A renda corresponde à renda domiciliar mensal per capita das pessoas em domicílios particulares permanentes. Não é salário médio, PIB per capita ou renda nominal de cada censo. Uma média simples dos indicadores municipais não produz um indicador oficial nacional, estadual ou regional; o arquivo não contém ponderadores populacionais.

## Reprodução e validação

Executar na raiz do repositório com Python 3.10 ou superior, sem dependências externas:

```powershell
python scripts/data/build_atlas.py
python scripts/data/build_atlas.py --verify
python -B -m unittest discover -s scripts/data -p "test_*.py" -v
```

O primeiro comando baixa fontes ainda não armazenadas e reutiliza snapshots com checksum válido. Para reproduzir exclusivamente a partir dos downloads locais ou obter uma nova coleta:

```powershell
python scripts/data/build_atlas.py --offline
python scripts/data/build_atlas.py --refresh
```

`--verify` confere o CSV entregue contra o SHA256 e o relatório do manifesto, sem acesso à rede e sem exigir `raw/`. `--offline` reconstrói o CSV e manifesto com os arquivos brutos locais, verificando seus SHA256; a data de geração do manifesto muda, mas o CSV permanece idêntico. `--refresh` consulta novamente as fontes oficiais e conserva os snapshots anteriores. As APIs podem corrigir dados históricos: o SHA256 identifica a versão efetivamente consumida.

As validações incluem código/UF, correspondência exata de códigos, duplicação município/ano, cobertura dos três anos, escopo municipal, fonte e unidade, números finitos, limites dos índices, renda não negativa e consistência do IDHM com a média geométrica das dimensões. A tolerância desta última é 0,002, para acomodar o arredondamento dos componentes publicados. A maior diferença observada na coleta foi aproximadamente 0,000500. O CSV serializado também é validado antes da gravação.

Os testes de regressão usam o CSV real entregue e verificam a rejeição de duplicatas, perda de linhas, números inválidos, códigos sem correspondência, novas lacunas de cobertura e cache bruto corrompido. As pequenas entradas inválidas dos testes não integram o CSV.

## Manifesto, downloads e atribuição

`manifest.json` contém a data UTC, cobertura, séries/metadados originais, URLs exatas, data de download por resposta, SHA256, tamanho e caminho dos snapshots, checksum do CSV, resultados de validação, divergências cadastrais e limitações. As datas representam acesso/geração, não a data dos censos. Os metadados incluem também a data de atualização informada pelo Ipeadata.

`raw/` guarda os bytes originais em arquivos nomeados pelo SHA256 e um índice de cache. A pasta é ignorada pelo Git por `data/.gitignore`; todos os arquivos permanecem no workspace e nenhuma exclusão de downloads é feita. Para uma cópia arquivística reproduzível, preservar CSV, manifesto, scripts **e** `raw/`. Em um clone sem `raw/`, a coleta usa as fontes online e pode produzir outra versão se elas tiverem mudado.

Atribuição: **Atlas do Desenvolvimento Humano no Brasil — PNUD, Ipea e Fundação João Pinheiro; Censos Demográficos do IBGE; distribuição pelo Ipeadata. Cadastro territorial: IBGE.**

**Licença/termos:** o acesso público foi verificado, mas não foi confirmada uma licença específica de redistribuição dos conjuntos. O manifesto deixa o identificador de licença nulo. Não atribuir automaticamente CC-BY, domínio público ou a licença do código deste projeto aos dados. Conservar a atribuição e consultar os termos dos respectivos provedores antes de redistribuir.
