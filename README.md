# Brasil em Perspectiva

Dashboard municipal do Atlas do Desenvolvimento Humano no Brasil, com Laravel 13, PHP 8.4+, Vue 3, TypeScript, Vite e SQLite. A SPA é renderizada pelo Blade com `@vite`; o build publicado fica em `public/build`. O domínio de produção previsto é **https://dashboard.stolner.com**, na VPS Oracle, por Cloudflare Tunnel.

## Executar localmente

Requisitos: PHP **8.4 ou superior** compatível com `composer.lock`, Composer 2, Node **24**, npm e Python 3.10+ para validação dos dados. Habilitar `pdo_sqlite`, `sqlite3`, `mbstring`, `intl`, `zip`, além das extensões padrão exigidas pelo Composer. O lockfile é a referência de compatibilidade, mesmo que o mínimo declarado no `composer.json` seja menor.

Na raiz do repositório, em PowerShell:

```powershell
composer install
Copy-Item .env.example .env
```

Editar **a cópia local** `.env`: `APP_ENV=local`, `APP_DEBUG=true`, `APP_URL=http://127.0.0.1:8796` e `SESSION_SECURE_COOKIE=false`. Manter `DB_CONNECTION=sqlite`; sem `DB_DATABASE`, Laravel usa `database/database.sqlite`.

```powershell
php artisan key:generate
if (!(Test-Path database/database.sqlite)) { New-Item database/database.sqlite -ItemType File }
npm ci --ignore-scripts --include=optional
npm run build
php artisan migrate
php artisan atlas:import
php artisan serve --host=127.0.0.1 --port=8796
```

Abrir [http://127.0.0.1:8796](http://127.0.0.1:8796). Para HMR, executar `npm run dev` em outro terminal; para voltar ao build estático, parar o Vite e remover `public/hot`, se remanescente. `artisan serve` é somente para desenvolvimento. Em produção, usar Nginx e PHP-FPM.

A política `.npmrc` desativa scripts de instalação. `npm run build` continua executando o script explícito. O lock atual usa Rolldown e binários opcionais por plataforma; instalar também dependências opcionais. Se uma atualização passar a exigir scripts nativos, identificar o pacote e revisar sua permissão específica conforme a versão do npm; não habilitar globalmente todos os scripts nem alterar o lock durante deploy.

`storage/app`, `storage/logs`, `storage/framework/cache/data`, `storage/framework/sessions`, `storage/framework/views` e `bootstrap/cache` precisam existir e ser graváveis pelo usuário do PHP. O entrypoint do container prepara esses diretórios.

## Dados e reprodução

O CSV versionado `data/atlas_municipios.csv` contém **16.695 observações**, **5.565 unidades municipais**, 27 UFs e cinco regiões, em **1991, 2000 e 2010**. A chave é código IBGE + ano. Há 16.692 linhas completas. Nazária/PI (`2206720`) tem cinco indicadores ausentes nos três anos: **15 células vazias**, importadas como `NULL`, sem imputação.

SHA-256 do CSV entregue:

```text
a4aa7b86737fd6fdeb7bd7e178dfaf83603471189880720590699e44b58ea45a
```

`data/manifest.json` registra checksum, fontes, cobertura e regras de transformação. O comando abaixo verifica o CSV e manifesto sem rede ou snapshots brutos:

```powershell
python -B scripts/data/build_atlas.py --verify
python -B -m unittest discover -s scripts/data -p "test_*.py" -v
```

A reconstrução `python scripts/data/build_atlas.py --offline` exige os snapshots locais e seu índice em `data/raw`; eles não são necessários para o CI de verificação. Uma nova coleta com `--refresh` acessa a rede e deve ser revisada como atualização de dados.

Fontes: [API oficial Ipeadata](https://www.ipeadata.gov.br/api/), [série IDHM](https://www.ipeadata.gov.br/api/odata4/Metadados('ADH_IDHM')/Valores), [metadados de renda](https://www.ipeadata.gov.br/api/odata4/Metadados('ADH_RDPC')), [metodologia do Atlas no Ipeadata](https://www.ipeadata.gov.br/doc/Metodologia%20ADH.pdf) e [cadastro municipal IBGE/SIDRA de 2010](https://apisidra.ibge.gov.br/values/t/1301/n6/all/v/615/p/2010). Séries: `ADH_IDHM`, `ADH_IDHM_E`, `ADH_IDHM_R`, `ADH_IDHM_L`, `ADH_RDPC`. Atribuição: PNUD, Ipea, Fundação João Pinheiro e IBGE; distribuição Ipeadata. A renda mensal per capita está em reais de agosto de 2010; médias municipais não são indicadores oficiais agregados. Ver [contrato, limites e termos dos dados](data/README.md).

`php artisan atlas:import` substitui o dataset inteiro em transação. Usar explicitamente apenas no preparo local ou em uma atualização revisada, com backup. O deploy automático importa somente quando a tabela `datasets` está vazia e confere o checksum antes da primeira carga. Atualizar a imagem ou reiniciar o container preserva o dataset existente, mesmo que o CSV da imagem tenha mudado.

## Validação e CI

```powershell
composer test
npm run typecheck
npm test
npm run build
python -B scripts/data/build_atlas.py --verify
python -B -m unittest discover -s scripts/data -p "test_*.py" -v
```

Os testes Laravel usam SQLite em memória conforme `phpunit.xml`. O workflow `.github/workflows/ci.yml` executa PHP 8.4/Composer, Node 24, testes frontend, build Vite, verificação offline dos dados e build dos dois targets Docker. O smoke test dos containers valida `/up`, API, SPA, asset gerado, bloqueio de arquivos privados e backup íntegro. O workflow não publica imagens nem faz deploy na VPS.

## Produção

Seguir o [runbook de implantação e recuperação](deploy/README.md). O Compose publica somente **127.0.0.1:8088**; PHP-FPM não possui porta no host. O Nginx recebe apenas `public/` e seus assets, sem código, dados ou credenciais. O volume `atlas-storage` mantém o SQLite em `/var/www/html/storage/app/atlas.sqlite`, sessões e cache entre recriações. Não usar `docker compose down --volumes` no ambiente real.

A imagem não contém `.env`, banco local ou `APP_KEY`. A chave deve ser gerada uma vez e mantida no ambiente real. O perfil opcional `tunnel` lê o token de um arquivo secreto; nenhum token é fornecido neste repositório. A definição do usuário SSH e do acesso à VPS continua pendente, assim como a implantação real.

**Fronteira de proxy:** somente o cloudflared em `172.30.88.3`, na rede dedicada `172.30.88.0/29`, pode informar `CF-Connecting-IP`. O Nginx converte esse valor para o `REMOTE_ADDR` do FastCGI e define HTTPS/443 apenas para esse conector. Headers X-Forwarded-* recebidos são descartados. Assim, o throttle do Laravel distingue visitantes sem ampliar `trustProxies` no backend. O acesso direto por loopback/gateway não pode forjar o IP ou o esquema. O modo de produção usa o perfil `tunnel`; ver pré-requisitos e testes no runbook.
