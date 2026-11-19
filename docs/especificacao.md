# Brasil em Perspectiva

## Territórios em Foco

Especificação inicial de produto e arquitetura, em 18 de novembro de 2026. Decisões fornecidas pelo usuário estão indicadas como confirmadas; escolhas técnicas complementares são propostas. Este documento não comprova implementação, importação da base ampliada ou implantação.

## Objetivo e decisões confirmadas

Explorar como o desenvolvimento humano e suas dimensões variam entre os territórios selecionados e como essas diferenças evoluíram nos períodos disponíveis.

- Marca: combinar Brasil em Perspectiva com Territórios em Foco.
- Dados: Atlas do Desenvolvimento Humano no Brasil; anos 1991, 2000 e 2010.
- Cobertura confirmada: municípios do Brasil, agrupados por UF e pelas cinco grandes regiões (Norte, Nordeste, Centro-Oeste, Sudeste e Sul), conforme disponibilidade e comparabilidade da base do Atlas em cada ano.
- Publicação: aplicação na VPS Oracle, acessível por endereço na Cloudflare.
- Repositório confirmado: https://github.com/RafaStolf/AV3-Dashboard-AnaliseDeDados-BigData.git. Clonado na subpasta AV3-Dashboard-AnaliseDeDados-BigData/, com Git próprio; remoto vazio no momento do clone.
- Endereço público proposto: dashboard.stolner.com. DNS e roteamento ainda não configurados para este projeto.
- Tecnologia pretendida: PHP, Laravel, Vue 3 e SQLite.
- Análises: blog com artigos em Markdown e gráficos adequados à informação.
- Investigação inicial: as cinco perguntas abaixo.
- Referência ao Atlas: permanente no site e associada às visualizações e análises.
- Notion não faz parte do projeto. A documentação será publicada obrigatoriamente no próprio site, em uma área de base de conhecimento inspirada em https://docs.centrium.com.br/. Os arquivos-fonte da documentação e dos artigos serão mantidos em Markdown no repositório.
- Direção visual confirmada: combinar o visual editorial da proposta 2 com a estrutura funcional da proposta 3. A proposta 1 foi considerada genérica pelo usuário.

## Direção visual selecionada

Usar fundo marfim, títulos serifados, texto em azul-marinho, acentos em terracota e azul e separadores finos da proposta 2. Aplicar esse visual à estrutura da proposta 3: navegação lateral, filtros na parte superior, gráfico principal no centro e evolução ou interpretação como conteúdo complementar. A identidade principal é Brasil em Perspectiva, com Territórios em Foco como subtítulo.

A navegação lateral terá Panorama, Comparar, Evolução, Análises, Dados, Documentação e Sobre. A página de comparação usará seleção de municípios com filtros de região, UF e ano. A tipografia editorial não deve reduzir a legibilidade dos eixos, controles e tabelas. A versão mobile adapta a navegação e organiza as visualizações em coluna.

Uma única composição visual revisada foi produzida a partir das duas imagens selecionadas e aprovada pelo usuário. Ela é a referência visual para implementação. Os números da composição são ilustrativos, sem valor de evidência analítica, e a imagem não representa uma aplicação funcional. A inclusão de Documentação na navegação é um ajuste de escopo posterior à aprovação da composição, preservando seu estilo.

## Situação observada

O diretório contém um protótipo HTML e um CSV com 117 observações de 39 municípios da Região Metropolitana de São Paulo, nos três anos definidos. Essa cobertura não representa a base ampliada. A conferência de contagem não constitui auditoria de todos os valores.

O diretório também está dentro de um repositório Git mais amplo, cuja raiz é E:/Documents/Projeto. O repositório específico foi clonado na subpasta AV3-Dashboard-AnaliseDeDados-BigData/, com .git próprio e origin confirmado. Nenhum commit ou push foi realizado. Os documentos e o protótipo anteriores continuam fora do clone.

## Organização do site proposta

1. Panorama: mapa, distribuição e resumo do recorte; ano e indicador claramente visíveis.
2. Comparar: seleção de territórios e comparação das dimensões e dos períodos.
3. Evolução: trajetórias entre 1991, 2000 e 2010 e mudanças em pontos de índice.
4. Análises: listagem de artigos e páginas com interpretação, evidências e gráficos.
5. Dados: consulta e download da base, cobertura, versão e links para sua documentação.
6. Documentação: base de conhecimento pública com busca, categorias e artigos de metodologia, dados, modelagem, cálculos e uso do sistema.
7. Sobre: propósito acadêmico, equipe e escopo.

## Documentação pública no site

Requisito confirmado: disponibilizar a documentação no site, com organização de base de conhecimento inspirada na Wiki Centrium. A página de referência consultada oferece busca, áreas temáticas, artigos e uma lista de atualizações. Reutilizar esses princípios de organização com a identidade editorial já aprovada; não há solicitação de cópia visual exata ou do conteúdo da Centrium.

Proposta de navegação: entrada Documentação no menu principal, página inicial com busca e categorias, navegação lateral hierárquica dentro da área, breadcrumbs e sumário de seções nos artigos longos. Cada página terá título, conteúdo, data de atualização, referências e links relacionados. A busca abrangerá apenas documentação publicada e apresentará título, trecho e categoria; rascunhos não serão indexados.

Categorias iniciais propostas:

- Comece aqui: objetivo, escopo nacional, períodos e guia de navegação.
- Base de dados: Atlas Brasil, obtenção, proveniência, cobertura, códigos territoriais e dicionário de indicadores.
- Metodologia e cálculos: IDHM e dimensões, diferenças temporais, rankings, distribuições, associação e limitações.
- Modelagem: tabelas, chaves, relações, processo de importação e validações.
- Guia do dashboard: filtros, comparações, leitura dos gráficos, compartilhamento e exportações.
- Arquitetura e desenvolvimento: componentes, instalação local e testes; apenas informações apropriadas à publicação.
- Publicação e manutenção: visão geral da hospedagem, atualização de conteúdo e dados, histórico de alterações; sem segredos ou detalhes operacionais privados.

Documentação descreve como os dados e o sistema funcionam. O blog Análises apresenta investigações e interpretações. Ambos usam Markdown e podem se referenciar; mantêm navegação e finalidade próprias. Proposta de localização dos fontes: content/docs e content/analises. Metadados de documentação incluem título, slug, categoria, ordem, resumo, status e datas. A renderização compartilha as restrições de segurança de Markdown dos artigos.

## Perguntas e visualizações

| Pergunta | Visualização principal | Complemento e regra |
| --- | --- | --- |
| Quais territórios têm maiores e menores valores de IDHM? | Barras horizontais ordenadas | Mapa coroplético para localização; empates explícitos; ranking restrito ao recorte. |
| Onde ocorreu maior avanço entre dois períodos? | Gráfico de pontos conectados entre início e fim | Barras da diferença absoluta; comparar as mesmas unidades nos dois períodos. |
| Qual dimensão apresenta o menor valor em cada território? | Matriz de calor de educação, renda e longevidade | Barras agrupadas em comparações curtas; escala comum; menor componente não equivale a causa. |
| Como a distribuição dos indicadores difere entre regiões? | Boxplot com pontos quando viável | Informar tamanho da amostra, definição dos quartis e regra de agrupamento; ponto representa município. |
| Existe associação entre renda per capita e IDHM Educação? | Dispersão com um ponto por município | Um ano por análise; coeficiente e tamanho amostral documentados; associação não demonstra causalidade. |

Gráficos terão alternativa tabular, fonte, unidade, período e descrição acessível. A mesma cor terá o mesmo significado entre páginas. Mapas usarão malhas geográficas compatíveis e documentadas; as geometrias não estão presentes como base ampliada validada.

## Referência e rastreabilidade

Rodapé proposto: Fonte dos indicadores: Atlas do Desenvolvimento Humano no Brasil — PNUD, Ipea e Fundação João Pinheiro. Elaboração: equipe do projeto. Projeto acadêmico independente.

Cada gráfico deverá mostrar fonte, edição ou versão da base, ano e transformação relevante. Um intermediário de obtenção, se utilizado, será identificado junto com a fonte original. A fonte dos limites geográficos também será registrada.

Cada importação registrará URL, data de obtenção, arquivo original, SHA-256, edição, cobertura, regras de limpeza e relatório de validação. Verificar termos de reutilização antes de redistribuir os arquivos. Licença do código e termos dos dados são registros distintos.

## Arquitetura recomendada

Navegador → HTTPS na Cloudflare → Cloudflare Tunnel → servidor web na VPS → PHP-FPM e Laravel → SQLite e arquivos Markdown.

O túnel será proposto como transporte público; nenhum domínio, porta ou configuração atual da VPS foi verificado. A aplicação terá serviço e diretório próprios. O servidor web deverá expor apenas public/ do Laravel. O serviço local será acessível ao conector por loopback quando estiverem no mesmo host; outra topologia exige configuração explícita.

Proposta: Vue 3 com TypeScript e Inertia, mantendo controllers e rotas no Laravel. Vite compila os assets para produção. Node participa do build; SSR e um processo Node permanente não são necessários à proposta inicial.

SQLite atende ao cenário esperado de leitura e importações ocasionais. Manter o arquivo fora da raiz pública, em disco local persistente. Usar transações e índices, avaliar WAL e busy timeout no ambiente real. Muitas gravações simultâneas ou múltiplas instâncias da aplicação exigirão reavaliação, possivelmente PostgreSQL.

## Dados e relações propostas

O município é a unidade principal de observação. A navegação territorial seguirá Brasil → grande região → UF → município, com comparações entre municípios e distribuições municipais por UF ou grande região. Os filtros territoriais serão encadeados e permitirão limpar ou ampliar a seleção. Mapas e downloads informarão a cobertura efetiva por ano; registros ausentes não serão tratados como zero. A composição municipal e a compatibilidade dos códigos entre 1991, 2000 e 2010 precisam ser validadas na base escolhida.

- datasets: fonte, edição, URL, data de obtenção, checksum e unidade monetária.
- territories: código, nome e tipo territorial; conservar códigos como texto.
- territory_memberships: município, agrupamento, classificação e vigência. Suporta recortes não hierárquicos e mudanças na composição.
- indicators: código, nome, definição, unidade e limites válidos.
- observations: dataset, território, indicador, ano e valor anulável; unicidade nessa combinação.

Observações oficiais agregadas e estatísticas derivadas de municípios serão identificadas separadamente. Não calcular um IDHM regional oficial por média ou mediana municipal. Médias ponderadas só serão usadas quando justificadas e houver pesos compatíveis.

Importação: conservar a base original, padronizar códigos, validar anos e tipos, detectar duplicidades e ausências, conferir limites dos índices e reconciliar os valores importados. Campo ausente não será convertido em zero. Dados com geografias incompatíveis não serão unidos apenas pelo nome.

Para comparação temporal, exigir pares válidos por unidade e informar exclusões. Mudanças na composição territorial e valores monetários precisam de tratamento documentado. A base local indica renda a preços de 2010 no protótipo; confirmar essa definição na fonte ampliada antes de usá-la.

## Contratos de visualização

Proposta: ECharts em componentes Vue para barras, linhas, matriz, boxplot e dispersão; renderer Canvas para esses gráficos, acompanhado de tabela HTML. Mapa SVG com geometria simplificada e cobertura validada, por ECharts, é a primeira alternativa para o mapa temático sem mapa-base.

Separar carregamento, validação, cálculo, configuração do gráfico, renderização e estado de interação. Cálculos compartilhados ficam no backend e são consumidos tanto pelo dashboard quanto pelos artigos. Definir tipos para Observation, Indicator, Territory, Provenance, ChartResult e ViewState antes dos componentes.

ViewState versionado na URL: ano, indicador, recorte territorial, seleção e período de comparação. Parsear, validar, normalizar defaults e serializar em ordem estável. Parâmetros inválidos recebem fallback explícito; versões desconhecidas não são interpretadas silenciosamente. Voltar e avançar do navegador restauram a visualização. Hover e posição do cursor não entram na URL.

Precedência proposta: URL válida, preferência local de apresentação e defaults. Preferências locais não substituirão silenciosamente o recorte de um link compartilhado. Sem contas ou workspaces salvos na primeira proposta.

Desempenho: até três visualizações simultâneas no panorama; as outras carregam sob demanda. Registrar e importar apenas módulos usados, liberar instâncias ao desmontar componentes, simplificar geometrias sem descaracterizá-las e limitar rótulos simultâneos. Validar desempenho com a cobertura ampliada real antes de assumir que todos os pontos e polígonos cabem na mesma tela.

Mobile: coluna única, filtros recolhíveis sem esconder o estado ativo, seleção por toque, detalhes acessíveis sem hover, tabela rolável com cabeçalhos claros e respeito a movimento reduzido. Em conexão instável, mostrar erro e tentativa novamente; resposta antiga será identificada. Sem dados disponíveis, mostrar estado vazio em vez de números fictícios.

## Blog em Markdown

Proposta: arquivos em content/analises, com front matter contendo título, slug, resumo, autor, datas, status, tags, fontes e referências de visualizações. Apenas artigos publicados entram na listagem pública. Começar sem editor administrativo, comentários ou upload público.

Cada artigo conterá pergunta, método, resultados, interpretação, limitações e referências. Texto e gráficos compartilharão a versão dos dados e o recorte. Gráficos serão inseridos por identificadores de blocos permitidos, nunca por JavaScript ou PHP arbitrários no Markdown.

Artigos com gráficos históricos devem fixar dataset, indicador, territórios e anos para manter o argumento reproduzível. Uma exploração adicional pode ser oferecida por link ao dashboard. Não inventar interpretações antes de executar e conferir os cálculos.

## Rotas propostas

- GET /, /comparar e /evolucao: páginas do painel.
- GET /analises e /analises/{slug}: artigos publicados.
- GET /dados e /sobre: consulta da base e contexto do projeto.
- GET /docs e /docs/{categoria}/{slug}: base de conhecimento e páginas de documentação.
- GET /docs/busca?q=...: busca limitada e validada sobre a documentação publicada; registrar esta rota antes das rotas dinâmicas.
- GET /metodologia: redirecionamento para a categoria correspondente na documentação, caso esse endereço seja mantido.
- GET /api/v1/observacoes: consulta validada, limitada e paginada quando necessário.
- GET /api/v1/analises/{tipo}: cálculos permitidos, com parâmetros validados e proveniência.
- GET /dados/exportar: exportação do recorte com limites e tratamento seguro do CSV.

As rotas são propostas; não foram implementadas. Inertia pode fornecer os dados iniciais das páginas sem uma API separada para cada operação. Endpoints devem ser criados apenas quando houver consumidor real.

## Segurança e operação

A política SECURITY.md registra propriedades exigidas, não controles já verificados. Configurar debug desativado, segredos fora do Git, consultas parametrizadas, allowlist de indicadores e ordenações, saneamento de Markdown e proteção de arquivos privados.

Importação será uma operação de manutenção restrita, sem URL remota arbitrária aceita do visitante. Aplicar limites de consulta e exportação, impedir travessia de diretórios e evitar fórmulas executáveis nos CSVs exportados. Proteger qualquer futura administração com autenticação, autorização, CSRF e limites de tentativa.

Backups consistentes do SQLite, conteúdo e configuração necessária à recuperação; segredos em armazenamento adequado. Validar restauração e integridade. Fazer deploy por release e manter possibilidade de voltar à versão anterior, considerando compatibilidade das migrações. Não presumir que copiar o arquivo SQLite durante gravações é um backup consistente.

## Alternativas e decisões pendentes

Laravel com Inertia é a recomendação, coerente com a stack escolhida. Vue separado por API oferece independência, com maior trabalho de contratos e implantação. Site inteiramente estático simplifica operação, mas se afasta da stack e do backend pretendidos. Essas alternativas não foram formalmente escolhidas pelo usuário.

Pendências: configuração do subdomínio confirmado dashboard.stolner.com; recursos e acesso à VPS; edição e rota de obtenção da base nacional e sua cobertura efetiva por ano; termos de redistribuição; responsáveis por autoria e revisão. O recorte territorial e a composição visual combinada foram aprovados. A documentação pública no próprio site foi confirmada como requisito.

## Entregas e critérios de aceite

1. Identidade e desenho responsivo: implementar a combinação visual aprovada e documentar as telas e os estados.
2. Base e modelagem: importar cobertura definida com unicidade, rastreabilidade e reconciliação; dados originais preservados.
3. Dashboard: responder às cinco perguntas com filtros consistentes, cálculos conferidos, URLs compartilháveis e tabelas acessíveis.
4. Artigos e documentação: renderizar Markdown seguro, manter fontes e reproduzir o recorte dos gráficos nos artigos; publicar a base de conhecimento com categorias, navegação, busca e referências. Validar que rascunhos não aparecem nas páginas nem na busca.
5. Publicação: servir pela Cloudflare e VPS, confirmar HTTPS, bloqueio de arquivos privados, consultas, exportação, backup e restauração.

## Fontes técnicas consultadas

- https://laravel.com/starter-kits
- https://developers.cloudflare.com/tunnel/get-started/
- https://developers.cloudflare.com/tunnel/concepts/routing/
- https://sqlite.org/whentouse.html
- https://www.undp.org/pt/brazil/atlas-dos-municipios
- https://docs.centrium.com.br/ — referência de organização da documentação, fornecida pelo usuário.

## Estado desta entrega

Especificação em Markdown e política inicial de segurança produzidas. Aplicação Laravel e Vue implementada no clone local, com 16.695 registros, cinco análises em Markdown e doze páginas de documentação pública. Banco SQLite local importado. A configuração de produção está em Dockerfile, compose.yaml e deploy/. Não houve commit ou push nem criação de arquivo Figma. A aplicação foi implantada na VPS Oracle e publicada em https://dashboard.stolner.com por um Cloudflare Tunnel dedicado. Notion está fora do escopo por decisão do usuário. O template Word System Design foi localizado e inspecionado. O script de renderização por LibreOffice falhou por ausência de soffice.exe no PATH; posteriormente, a instalação e a automação do Microsoft Word 16.0 foram verificadas com sucesso. Assim, a ausência do LibreOffice não constitui bloqueio geral para trabalhar com documentos Word. O documento final ainda não foi produzido ou validado visualmente.

## Decisões efetivas de implementação

A aplicação usa Vue 3 com TypeScript e Vue Router como SPA, com API de mesma origem servida pelo Laravel, em vez da alternativa Inertia inicialmente recomendada. O banco implementa datasets, territories e observations; os cinco indicadores são colunas anuláveis por município e ano. UF e região são atributos do cadastro de referência de 2010. Tabelas separadas para indicadores e vínculos territoriais com vigência permanecem como extensões conceituais, sem serem apresentadas como já implementadas. As páginas de modelagem no site descrevem o esquema efetivo.

A documentação é pública em /docs e os artigos em /analises. O endereço confirmado é https://dashboard.stolner.com. O desenvolvimento local utiliza http://127.0.0.1:8796.

