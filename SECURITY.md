# Política de segurança

## Sistema e escopo

Brasil em Perspectiva — Territórios em Foco é um projeto acadêmico de consulta de indicadores do Atlas do Desenvolvimento Humano no Brasil. Esta política cobre este diretório e seus descendentes, incluindo a aplicação Laravel e Vue, importadores, consultas SQLite, exportações, artigos Markdown e configuração de implantação mantida no projeto.

A arquitetura pretendida usa uma VPS Oracle com acesso público pela Cloudflare. A implementação inclui API de consulta, importador transacional com reconciliação de checksum e cobertura, exportação CSV, SPA Vue e renderização segura de Markdown. Os controles de aplicação foram exercitados por testes locais. A aplicação está publicada em https://dashboard.stolner.com por um túnel dedicado. HTTPS, consultas públicas, documentação e bloqueio de arquivos privados foram verificados no ambiente real em 19 de novembro de 2026. Responsáveis e canal de divulgação de vulnerabilidades ainda não foram definidos.

## Modelo de ameaças e fronteiras de confiança

Visitantes públicos controlam parâmetros de consulta, filtros, identificadores de artigos, cabeçalhos e frequência das requisições. A origem pública da base não torna arquivos importados confiáveis para parsing, armazenamento ou exportação. Markdown e metadados podem conter conteúdo inseguro mesmo quando escritos por colaboradores.

Os ativos incluem integridade das observações, proveniência e cálculos, arquivos de conteúdo, disponibilidade da VPS, banco SQLite, backups e segredos de implantação. As fronteiras relevantes ficam entre navegador e aplicação, aplicação e banco/arquivos, importador e fonte externa, conteúdo Markdown e HTML renderizado e Cloudflare e serviço local.

A autoria usa arquivos versionados e a importação é uma operação de manutenção local. Não presumir que existirão autenticação, administração ou isolamento entre clientes. Caso sejam adicionados, sua fronteira deve ser documentada antes de depender desses controles.

## Invariantes de segurança

- A raiz pública do servidor deve ser public/ na aplicação Laravel. Banco, .env, Git, backups, logs privados e fontes Markdown não publicadas não podem ser servidos por caminhos diretos.
- A aplicação deve validar filtros, anos, tipos territoriais, paginação e limites. Consultas usam parametrização; nomes de campos e ordenações dinâmicos exigem allowlist.
- Visitantes não podem alterar bases, artigos ou configuração. Qualquer futura mutação administrativa exige autenticação, autorização e proteção CSRF aplicável.
- Slugs e caminhos de conteúdo não podem permitir leitura arbitrária ou travessia de diretórios. Artigos em rascunho não podem aparecer nas rotas públicas.
- Markdown não executa PHP, JavaScript ou expressões arbitrárias. HTML e URLs são restringidos ou saneados; blocos de gráfico usam contratos permitidos e validação.
- Importadores não aceitam comandos ou destinos de rede arbitrários de visitantes. Arquivos são tratados com limites de tamanho, formato e recursos; conteúdo importado não pode executar código.
- Exportações respeitam o recorte autorizado e não introduzem fórmulas de planilha executáveis a partir de campos textuais.
- Debug de produção permanece desativado. Segredos, tokens do túnel e credenciais não entram no repositório, nos assets ou em respostas/logs públicos.
- Consultas, gráficos e exportações possuem limites para evitar consumo desproporcional de CPU, memória e disco.
- Importações preservam integridade, unicidade e proveniência em transações apropriadas; falhas não devem publicar uma base parcialmente carregada como completa.
- Backups precisam ser consistentes e recuperáveis; a configuração de túnel, firewall ou WAF não substitui autorização e validação na aplicação.

## Achados reportáveis e contexto de severidade

São reportáveis, quando alcançáveis no sistema efetivo: execução remota, injeção SQL, leitura ou escrita arbitrária, exposição de segredos ou backups, XSS persistente/refletido, SSRF em importações, acesso a rascunhos não publicados, contorno de autorização, corrupção das observações e negação de serviço com impacto demonstrável.

Avaliar severidade pelo caminho de ataque real, privilégios exigidos, exposição e impacto sobre a aplicação e a VPS compartilhada. Não presumir impacto crítico apenas pelo nome de uma classe de falha. Diferenciar vulnerabilidade demonstrada de recomendação ou configuração futura ainda inexistente.

## Exclusões e riscos aceitos

Nenhuma exclusão de classe de vulnerabilidade ou aceitação de risco foi confirmada pelo proprietário. Dados estatísticos intencionalmente públicos não constituem vazamento por si; isso não exclui segredos, dados privados, rascunhos ou informações de infraestrutura.

## Limitações conhecidas e controles compensatórios

O hostname definido é dashboard.stolner.com. A VPS Oracle ARM64 e a configuração do túnel dedicado foram inspecionadas durante a implantação. A configuração fornecida só confia na identidade de visitante informada pelo conector cloudflared no IP reservado de sua rede dedicada; não estender essa confiança a redes públicas. Testes locais de validação de parâmetros, saneamento de Markdown, isolamento de rascunhos, CSV e rollback foram executados. A rota pública do túnel foi testada. Não houve auditoria completa do WAF ou do firewall; testes funcionais e resultados locais não fundamentam supressão genérica de achados de produção.

Reavaliar a política se forem adicionados contas, editor web, upload público, novas fontes remotas, múltiplas instâncias ou processamento de dados pessoais. A conversa privada do grupo não é conteúdo público do produto e não deve ser distribuída com o site.
