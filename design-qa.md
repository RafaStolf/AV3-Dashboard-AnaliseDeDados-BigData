# Validação do desenho

Data: 19 de novembro de 2026.

## Referência e método

A referência é a composição combinada aprovada pelo usuário: visual editorial da proposta 2 e estrutura lateral da proposta 3. A imagem contém valores ilustrativos; a aplicação usa dados reais, com São Paulo e Salvador como seleção inicial.

Comparação no navegador integrado em viewport CSS 1440 × 1024, densidade 1; captura mobile em 390 × 844. Evidências locais ignoradas pelo Git em artifacts/: reference.png, comparacao-desktop.jpg (primeira passagem), comparacao-desktop-final.jpg, comparacao-mobile-final.jpg e comparison-board.jpg. O quadro de comparação colocou referência e implementação lado a lado, normalizadas para o mesmo tamanho. As capturas do navegador são JPEG, e não evidência de equivalência pixel a pixel com o mockup gerado.

## Primeira passagem e correções

- P2 corrigido: espaçamento adicional do cabeçalho e das ferramentas deslocava a evolução histórica para fora da composição principal. A barra de ações passou ao cabeçalho da comparação e o aviso nacional de ausência deixou de ocupar espaço quando não afeta os municípios selecionados. Comparação e evolução ficaram visíveis em conjunto no desktop.
- P2 corrigido: rótulos numéricos do eixo se sobrepunham na comparação mobile. Foi habilitado hideOverlap; o gráfico redimensiona via ResizeObserver.
- P2 corrigido: links da navegação lateral mobile fechada permaneciam acessíveis fora da tela. O menu agora usa inert quando fechado e aceita Escape.
- Tabelas HTML acrescentadas para medianas por UF, quartis e extremos, maiores avanços e trajetórias. A tabela municipal e a exportação preservam dados ausentes.

## Superfícies de fidelidade

- Tipografia: títulos em Source Serif 4, serifada local com fallback Georgia; corpo em Segoe UI/Arial. Hierarquia e quebras conferidas em desktop e mobile. Rótulos do gráfico principal foram ampliados.
- Espaçamento e composição: navegação lateral, filtros no topo, barras centrais e evolução acompanhada de chamada editorial. No mobile, menu e filtros recolhem e as áreas passam a coluna única. A largura do documento permaneceu dentro da viewport no teste.
- Cores: fundo marfim, tinta azul-marinho, comparação em azul e terracota e separadores finos; legenda mantém a correspondência entre municípios.
- Imagens e recursos: interface não depende de raster do mockup. Ícones Phosphor, fonte local e geometria oficial de UF do IBGE. Gráficos ECharts continuam vinculados aos dados reais.
- Texto: identidade Brasil em Perspectiva — Territórios em Foco, fontes Atlas presentes, documentação pública separada do blog. Números ilustrativos foram substituídos por valores importados.

## Interações verificadas

Troca de ano, recorte regional e UF, ajuste dos municípios ao recorte, navegação entre páginas, busca documental por Pearson, abertura de metodologia, menu e filtros mobile, abertura da tabela de quartis e troca de indicador. Não houve mensagens de erro ou aviso no console na passagem final local. Não houve overflow horizontal global no teste mobile; tabelas extensas usam rolagem própria.

## Diferenças esperadas e limites

A navegação ganhou entradas para distribuições, renda/educação e documentação, necessárias ao escopo aprovado. Foram acrescentadas ferramentas de compartilhar/exportar e alternativas tabulares. O conteúdo real difere dos exemplos e não deve ser avaliado pelos números fictícios do mockup. O rodapé e as tabelas podem continuar abaixo da dobra.

P3: o seletor nativo de municípios contém toda a lista do recorte; pode evoluir para uma busca mais confortável. A verificação foi feita no navegador integrado, sem uma matriz completa de dispositivos físicos ou teste com leitor de tela real.

final result: passed

## Conferência no endereço público

A comparação e a navegação foram conferidas em https://dashboard.stolner.com após a implantação ARM64. A busca da documentação encontrou páginas sobre Pearson, e o artigo de renda/educação exibiu r = 0,791 com 5.564 pares. Captura: artifacts/dashboard-publicado.jpg. Uma falha de carregamento de chunk de uma aba aberta durante atualização foi corrigida com recuperação limitada de navegação, sem loop automático de recarregamento. A aba foi atualizada e a navegação final funcionou.

