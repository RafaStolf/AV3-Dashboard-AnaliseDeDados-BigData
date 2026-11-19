# Implantação na VPS Oracle

## Pré-requisitos e configuração

Acesso SSH e usuário remoto ainda precisam ser definidos pelo responsável pela VPS. Não há conexão, instalação remota ou publicação automática neste slice. Na VPS Linux, instalar Docker Engine com Compose v2 e obter uma cópia revisada do repositório. As imagens suportam a arquitetura nativa do host; testar o build na arquitetura Oracle usada, especialmente ARM64. Não publicar as portas 8088 ou 9000 no firewall da Oracle.

Os comandos deste runbook são Bash na raiz do repositório. Preparar o ambiente sem sobrescrever uma configuração existente:

```bash
test -f .env || cp .env.example .env
chmod 600 .env
```

Manter `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://dashboard.stolner.com`, `SESSION_SECURE_COOKIE=true`, `CACHE_STORE=file`, `SESSION_DRIVER=file` e `QUEUE_CONNECTION=sync`. O Compose fixa essas opções de runtime principais e o banco em `/var/www/html/storage/app/atlas.sqlite`, independentemente da configuração local de desenvolvimento. `DB_URL`, se definido externamente, também pode afetar o Laravel; não definir um DSN alternativo neste ambiente SQLite.

Construir ambos os targets. Usar uma tag de release única (exemplo abaixo); manter a mesma para app e Nginx durante todo o ciclo:

```bash
export IMAGE_TAG=release-001
docker compose build --pull
```

Gerar uma chave **somente no primeiro preparo** e inserir o resultado em `APP_KEY` no `.env` pelo editor seguro. O comando não inicializa o banco:

```bash
docker compose run --rm --no-deps app php -r 'echo "base64:".base64_encode(random_bytes(32)).PHP_EOL;'
```

Não salvar a chave no código, imagem, argumentos de build, logs públicos ou CI secrets desnecessários. Preservá-la em armazenamento seguro junto da configuração de recuperação. Não rodar `key:generate` em todo deploy. `DEPLOY_CACHE=true` permite caches de configuração, rotas e views no startup, já com o ambiente real; o padrão é `false`. As rotas atuais incluem closures, portanto testar também essa opção antes de habilitar no ambiente real. Cache não é gerado com segredo no build.

```bash
docker compose up -d --no-build --wait --wait-timeout 180
docker compose ps
curl --fail http://127.0.0.1:8088/up
curl --fail http://127.0.0.1:8088/api/bootstrap
curl --fail http://127.0.0.1:8088/ > /tmp/atlas-index.html
docker compose logs --tail=100 app nginx
```

O processo inicial prepara permissões, exige `APP_KEY`, executa `migrate --force` e importa o CSV somente se não existir dataset. Falha de migração, checksum ou importação impede iniciar o FPM; o Nginx aguarda o health check. O health do FPM usa seu ping FastCGI, e o do Nginx usa `/up` via Laravel. `/up` confirma o boot HTTP, mas também conferir API e contagens para validar o dataset. Subir somente **uma instância de app**, pois as migrações e SQLite não são coordenados para múltiplos escritores de deploy.

## Cloudflare Tunnel e HTTPS

Usar um túnel gerenciado no Cloudflare Zero Trust, com hostname público **dashboard.stolner.com**. O TLS público termina no Cloudflare; o transporte até o cloudflared é autenticado pelo túnel. Não é necessário expor entrada HTTP/HTTPS da VPS para este serviço.

O modo de produção suportado usa **cloudflared no Compose**, no perfil `tunnel`. Cadastrar o serviço de origem `http://nginx:8080`. `localhost` dentro desse container não aponta para Nginx. Salvar o token real no arquivo configurado por `CLOUDFLARE_TUNNEL_TOKEN_FILE`; o caminho padrão é `deploy/secrets/tunnel-token`. Criar a pasta com modo 700 e o arquivo com modo 600, propriedade UID/GID `65532:65532`, que é o usuário fixado para esse serviço no Compose. Com Compose local, o segredo é um bind mount e a propriedade do arquivo de origem precisa permitir a leitura pelo UID 65532; não presumir que `uid`/`mode` da sintaxe de secrets alterem isso. Não conceder leitura global ao token. O segredo é montado como `/run/secrets/tunnel_token`, sem token na linha de comando.

```bash
docker compose --profile tunnel up -d --no-build --wait --wait-timeout 180
```

Sem o perfil, nenhum token é necessário para testes locais da origem. Um cloudflared no host apontando para `127.0.0.1:8088` não pertence à fronteira confiável configurada: não usar esse caminho para tráfego público, pois identidade de cliente e esquema não seriam convertidos.

No dashboard Cloudflare, manter o host original `dashboard.stolner.com` (ou configurar esse valor no HTTP Host Header da origem), habilitar **Always Use HTTPS** no edge e confirmar o DNS do túnel. O conector é uma origem exclusivamente HTTPS do ponto de vista público; o Nginx estabelece `HTTPS=on`, `REQUEST_SCHEME=https` e `SERVER_PORT=443` para essa origem, independentemente de um X-Forwarded-Proto enviado pelo visitante. Permitir saída ao Cloudflare conforme a [documentação de firewall](https://developers.cloudflare.com/cloudflare-one/networks/connectors/cloudflare-tunnel/configure-tunnels/tunnel-with-firewall/).

### IP do visitante e fronteira confiável

A rede dedicada `ingress` usa **172.30.88.0/29**; somente Nginx e cloudflared participam dela no Compose. O conector tem IP reservado **172.30.88.3**. `set_real_ip_from` no Nginx confia **somente nesse IP**, com `real_ip_header CF-Connecting-IP` e `real_ip_recursive off`. Não há confiança em `0.0.0.0/0`, no gateway Docker, em toda a sub-rede ou em X-Forwarded-For arbitrário. Não conectar outros serviços à rede de ingresso nem reutilizar o IP reservado. Acesso administrativo ao Docker permite alterar essa fronteira e deve permanecer restrito.

Após a conversão, `$remote_addr` é o visitante e é transmitido como `REMOTE_ADDR` ao PHP-FPM. `$realip_remote_addr` preserva o peer de transporte e é usado para decidir HTTPS. O Nginx remove os headers Forwarded/X-Forwarded-* da requisição FastCGI. Laravel recebe identidade e esquema canônicos, por isso os proxies loopback atualmente configurados no backend não precisam ser ampliados. O throttle deixa de agrupar todos os visitantes no IP do conector. A origem direta `127.0.0.1:8088` permanece HTTP e não aceita `CF-Connecting-IP` forjado do host/gateway.

Se a sub-rede conflitar com rotas da VPS, escolher uma rede livre **antes de subir a stack** e mudar juntos o subnet/IP em `compose.yaml`, o IP exato e o map em `deploy/nginx.conf`, e os endereços do teste `deploy/tests/proxy.sh`. Não alterar só a rede ou trocar a regra por confiança ampla. O teste usa a mesma sub-rede; executar antes da stack real ou em host isolado.

O ensaio abaixo monta uma fixture PHP apenas nos containers temporários e testa `REMOTE_ADDR`, HTTPS e remoção de headers. Verifica dois visitantes distintos pelo conector, spoofing de um vizinho não confiável e spoofing via porta loopback. Não modifica o código da aplicação. Exige Bash/sh, curl, Python 3, Docker e as duas imagens construídas:

```bash
IMAGE_TAG=release-001 sh deploy/tests/proxy.sh
```

Depois de configurar o túnel, verificar no domínio real:

```bash
curl --fail --head https://dashboard.stolner.com/up
curl --fail https://dashboard.stolner.com/api/bootstrap
curl --fail https://dashboard.stolner.com/ > /tmp/atlas-public.html
```

Confirmar no navegador assets e navegação sem mixed content, URLs geradas HTTPS, cookies `Secure`, host correto, CSP e identificação do cliente/rate limit. Repetir a verificação de arquivos bloqueados (`/.env`, `/composer.json`, `/data/atlas_municipios.csv`, `/other.php` devem responder 403/404) e baixar um `/build/assets/*.js` referenciado pelo HTML. A origem está pronta somente após esses checks; health local não prova funcionamento do túnel.

## Backup consistente

Executar antes de toda atualização que aplique migrações ou substitua dados. A API `SQLite3::backup` cria um snapshot consistente inclusive com WAL; não copiar o arquivo SQLite vivo com `cp`, `docker cp` ou tar. A cópia para fora só acontece depois que o snapshot terminou e foi verificado.

```bash
stamp=$(date -u +%Y%m%dT%H%M%SZ)
backup="/var/www/html/storage/app/backups/atlas-$stamp.sqlite"
docker compose exec -T app php deploy/sqlite.php backup "$backup"
docker compose exec -T app php deploy/sqlite.php verify "$backup"
mkdir -p deploy/backups
chmod 700 deploy/backups
docker compose cp "app:$backup" "deploy/backups/atlas-$stamp.sqlite"
chmod 600 "deploy/backups/atlas-$stamp.sqlite"
sha256sum "deploy/backups/atlas-$stamp.sqlite"
```

A verificação exige `integrity_check=ok`, nenhuma violação de chaves estrangeiras e tabelas de migração/dataset/territórios/observações preenchidas. Na carga entregue, esperar 1 dataset, 5.565 territórios e 16.695 observações; o número de migrações pode evoluir. Conferir essas contagens com o histórico da release. Manter cópia verificada fora da VPS, junto do checksum, identificação da imagem/revisão, configuração e chave em armazenamento seguro. O volume sozinho não protege de perda da VPS. Não versionar backups nem `deploy/secrets`; eles são excluídos do contexto Docker, mas continuam exigindo cuidado no Git.

## Atualizar e reverter

1. Criar e verificar backup; registrar as tags atuais e revisão do código. Construir as novas imagens com a mesma tag nova para os dois serviços antes da janela de troca.
2. Parar Nginx e app para que migrações não disputem escrita com a versão anterior: `docker compose stop nginx app`. Se ativo, parar também `docker compose --profile tunnel stop cloudflared`.
3. Executar `docker compose up -d --no-build --wait --wait-timeout 180` com `IMAGE_TAG` da nova release. Repetir os checks de origem, API, assets e domínio. Reiniciar o perfil tunnel se usado.
4. Conferir os logs; a mensagem `Existing dataset preserved.` confirma que o startup não recarregou os dados. Atualizações deliberadas do CSV requerem novo backup e execução explícita de `atlas:import`, em janela sem tráfego; o comando substitui tudo em transação.

Rollback de **código**: selecionar `IMAGE_TAG` anterior e recriar app e Nginx juntos, mantendo o volume, somente se o schema atual for compatível com aquele código. As imagens anteriores devem permanecer disponíveis. Construir novamente código novo usando uma tag antiga não restaura a release anterior.

Rollback de **migração/dados**: não presumir que voltar o código desfaça o schema. Revisar os métodos `down()` antes de usar `migrate:rollback`; algumas migrações descartam dados. Quando houver incompatibilidade, restaurar um backup anterior junto do código correspondente. A restauração perde mudanças posteriores ao snapshot; registrar esse ponto de recuperação e preservar um backup do estado atual antes.

## Restaurar e comprovar recuperação

Escolher um snapshot já verificado. Se ele estiver apenas no armazenamento externo, transferi-lo para `deploy/backups`, conferir seu SHA-256 e copiá-lo para `storage/app/backups` do container com `docker compose cp`, corrigindo a propriedade para `www-data` antes de parar o app. O comando abaixo usa o snapshot já presente no volume, por exemplo o criado na seção anterior.

Parar todos os escritores, inclusive jobs ou shells de manutenção. Com o perfil ativo, parar também cloudflared:

```bash
docker compose --profile tunnel stop cloudflared
docker compose stop nginx app
# Definir IMAGE_TAG para a release compatível com o snapshot.
docker compose run --rm --no-deps app php deploy/sqlite.php verify "$backup"
docker compose run --rm --no-deps -e RESTORE_OFFLINE=yes app php deploy/sqlite.php restore "$backup"
docker compose run --rm --no-deps app php deploy/sqlite.php verify /var/www/html/storage/app/atlas.sqlite
docker compose up -d --no-build --wait --wait-timeout 180
curl --fail http://127.0.0.1:8088/up
curl --fail http://127.0.0.1:8088/api/bootstrap
```

O utilitário restaura por backup API para um arquivo temporário, compara contagens, verifica integridade e só então troca o banco parado. `RESTORE_OFFLINE=yes` é uma declaração do operador, não um mecanismo de parada: nunca executar restore com app ou outros escritores vivos. Sidecars WAL/SHM/journal são removidos somente nessa condição offline. O startup posterior roda migrações da imagem escolhida; usar a release correta antes de iniciar. Não apagar o volume nem usar `migrate:fresh` como recuperação.

Confirmar contagens iguais às do snapshot e conteúdo da API/SPA, assets e logs; então reabrir o túnel e repetir checks HTTPS. Fazer um ensaio de restauração em stack/volume isolado antes de depender desse procedimento em produção.

## Referências operacionais

- [Laravel: deployment e Nginx](https://laravel.com/docs/13.x/deployment)
- [Laravel: proxies confiáveis](https://laravel.com/docs/13.x/requests#configuring-trusted-proxies)
- [SQLite Online Backup API](https://www.sqlite.org/backup.html)
- [Cloudflare: túnel gerenciado com Docker](https://developers.cloudflare.com/cloudflare-one/networks/connectors/cloudflare-tunnel/get-started/)
- [npm: ignore-scripts](https://docs.npmjs.com/cli/v11/using-npm/config/#ignore-scripts)
