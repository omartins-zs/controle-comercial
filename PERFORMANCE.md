# Auditoria de Performance — Ambiente Docker

> Contexto real da stack (importante porque muda o que se aplica): **CodeIgniter 3
> + Ion Auth, servido por Apache + mod_php (`php:7.4-apache`), sem Nginx, sem
> PHP-FPM, sem Redis, sem Laravel.** Vários pontos clássicos de checklist
> Laravel/Nginx/FPM não existem nesta arquitetura — marcados como **N/A**
> abaixo, com a justificativa.

## 1. Diagnóstico (antes das mudanças)

| Item | Situação encontrada | Gravidade |
|---|---|---|
| OPcache | **Extensão nem instalada** na imagem base (`php -m` não listava `opcache`; nenhum `php.ini` era carregado — `php --ini` mostrava `Loaded Configuration File: (none)`) | 🔴 Alta — todo request recompilava ~50+ arquivos PHP do zero |
| `realpath_cache` | Default do PHP (4096K / 120s TTL) | 🟡 Média |
| Sessão | `sess_driver = 'database'` → 1 SELECT + 1 UPDATE/INSERT no MySQL por request só para ler/gravar sessão | 🟡 Média (soma latência de rede Docker a cada request) |
| Bind mount do código-fonte | **Não existe** — só `application/logs` e `application/cache` são bind-mounted; o resto é `COPY` no build | 🟢 Já era o cenário bom (evita o gargalo clássico de stat()/inotify lento do Docker Desktop no Windows) |
| `depends_on` do app → db | Já usava `condition: service_healthy` | 🟢 Já correto |
| Apache MPM prefork | Defaults da imagem: `StartServers 5`, `MaxRequestWorkers 150` | 🟡 Sobredimensionado para 1 dev local (desperdício de RAM, não de latência) |
| Bootstrap do container `app` | Nenhum script — só `apache2-foreground` direto | 🟡 Sem 2ª camada de espera pelo banco em restarts isolados |
| Healthcheck do `app` | Não existia (só o `db` tinha) | 🟡 |
| Nginx / upstream fastcgi / resolver / 502 pós-recreate | **N/A** — não há Nginx nem PHP-FPM nesta stack; Apache serve o PHP no mesmo processo, não há proxy reverso para "perder" o IP do upstream | — |
| `pm.max_children` / PHP-FPM | **N/A** — mod_php não usa FPM. Equivalente funcional: `StartServers`/`MaxRequestWorkers`/`MaxConnectionsPerChild` do Apache prefork | — |
| `APP_DEBUG` (estilo Laravel) | **N/A** — CI3 usa `ENVIRONMENT` (`development`/`production`), já lido de `$_SERVER['CI_ENV']` em [`index.php`](index.php:56) e sem custo estrutural equivalente ao debug mode do Laravel (sem query log/whoops pesados amarrados a ele) | — |
| Config/route/view cache (estilo Laravel `config:cache`) | **N/A** — CI3 não tem esse mecanismo; rotas/config são arrays PHP simples, já cobertos pelo OPcache | — |
| Redis | Não instalado/configurado — não foi forçado (conforme instrução: não empurrar solução quebrada) | — |

## 2. Alterações aplicadas

### 2.1 OPcache (maior ganho real)
- **Arquivo:** [`docker/php/opcache.ini`](docker/php/opcache.ini)
- Instalado via `docker-php-ext-install opcache` no [`Dockerfile`](Dockerfile).
- `opcache.validate_timestamps=0` — seguro *especificamente* porque o código
  é copiado para a imagem no build (não há bind mount do source); editar um
  arquivo sem rebuild não tem efeito de qualquer forma, então revalidar
  timestamp a cada request só custava tempo sem benefício. **Documentado no
  próprio arquivo** o que fazer se um bind mount de live-edit for adicionado
  depois (`validate_timestamps=1` + `revalidate_freq=0`).
- Resultado medido: 54 scripts em cache, **83% de hit rate** já nos primeiros
  requests, ~19MB de 128MB de memória usada.

### 2.2 PHP `local.ini`
- **Arquivo:** [`docker/php/local.ini`](docker/php/local.ini)
- `memory_limit=256M`, `post_max_size`/`upload_max_filesize=32M`,
  `date.timezone=America/Sao_Paulo` (evita fallback de timezone),
  `realpath_cache_size=4096K` / `realpath_cache_ttl=600` (de 120s → 600s,
  seguro pelo mesmo motivo do opcache: sem bind mount, o filesystem do app
  não muda em runtime), `error_log` apontando para
  `application/logs/php_errors.log` (que já é bind-mounted, então erros de
  PHP passam a ficar visíveis no host, junto dos logs do próprio CodeIgniter).

### 2.3 Sessão: `database` → `files` (só no Docker)
- **Arquivos:** [`application/config/config.php`](application/config/config.php),
  [`docker-compose.yml`](docker-compose.yml)
- `sess_driver`/`sess_save_path` agora leem `getenv('SESSION_DRIVER')` /
  `getenv('SESSION_SAVE_PATH')`, com fallback para os valores originais
  (`'database'` / `'ci_sessions'`) — **fora do Docker (Laragon) nada muda**.
- No `docker-compose.yml`, o serviço `app` define `SESSION_DRIVER=files` e
  `SESSION_SAVE_PATH=/var/lib/ci-sessions`, montado como **tmpfs** (RAM,
  efêmero — aceitável em dev/local, e mais rápido que disco).
- **Por quê:** cada request fazia leitura+gravação de sessão via MySQL
  (round-trip de rede dentro do Docker). Confirmado após a mudança: a tabela
  `ci_sessions` permanece com **0 linhas** mesmo após login e várias
  requisições — o I/O de sessão saiu inteiramente do banco.
- **Trade-off explicado:** driver `files`/tmpfs não sobrevive a um restart do
  container `app` (usuário precisa logar de novo) e não escalaria para
  múltiplas réplicas do app sem sessão compartilhada — irrelevante aqui
  (1 container local), mas documentado para não surpreender depois.

### 2.4 Apache (equivalente ao tuning de PHP-FPM)
- **Arquivo:** [`docker/apache/perf.conf`](docker/apache/perf.conf)
- Prefork reduzido de `StartServers 5 / MaxRequestWorkers 150` para
  `StartServers 2 / MaxRequestWorkers 30 / MaxConnectionsPerChild 1000`
  — evita reservar memória para 150 processos PHP num container de dev de
  1 usuário, e recicla processos periodicamente (proteção simples contra
  memory leak acumulado).
- `KeepAlive On` com `KeepAliveTimeout 5` — evita reabrir conexão TCP para
  cada asset (css/js) ao trocar de tela.

### 2.5 Bootstrap do container `app`
- **Arquivo:** [`docker/scripts/start-app.sh`](docker/scripts/start-app.sh), usado como `ENTRYPOINT`.
- Espera `db:3306` responder antes de subir o Apache — **segunda camada** de
  proteção além do `depends_on: condition: service_healthy` do compose
  (que não é reavaliado se só o container `app` for reiniciado/recriado
  isoladamente).
- Garante permissões de `application/cache`, `application/logs` e do
  diretório de sessões a cada boot (idempotente, não é um "rebuild de
  cache" caro — CI3 não tem cache de config/rota para aquecer).
- Healthcheck do `app` adicionado no `docker-compose.yml` (`curl -f
  http://localhost/login`).

### 2.6 `.dockerignore`
- Corrigido: a pasta `docker/` inteira estava sendo ignorada no build
  (incluía os próprios arquivos de config de performance!). Ajustado para
  ignorar só `docker/mysql-init` (não é necessário dentro da imagem da app).

## 3. Validação objetiva (medido nesta máquina)

| Verificação | Resultado |
|---|---|
| `docker compose ps` | 3/3 containers `healthy`/`running` |
| `php -m` no container `app` | `Zend OPcache` presente |
| OPcache hit rate após alguns requests | **83%**, 54 scripts cacheados, 19MB/128MB |
| `GET /` (sem sessão) | HTTP 307 → `/login`, **~6-7ms** |
| `GET /login` (aquecido) | HTTP 200, **~5-8ms** |
| `POST /login` (admin@admin.com/password) | HTTP 200, redireciona para `/home`, **~41ms** (dominado pelo hash bcrypt do Ion Auth — custo de segurança intencional, não gargalo de infra) |
| `GET /home` autenticado (aquecido) | HTTP 200, **~6-8ms** |
| Tabela `ci_sessions` após uso normal | 0 linhas (antes: 1 linha por sessão ativa + I/O por request) |
| Restart do `app` sem recriar o volume do banco | Dados preservados (`users` = 1 linha), app volta a responder em ~9s* |
| Cold start com volume novo (`down -v && up`) | Domina o tempo de inicialização do **MySQL** (import do schema), não do `app` |

\* Esse ~9s no restart do `app` **não é causado pelo nosso bootstrap** — os
logs mostram Apache recebendo `SIGWINCH` (sinal de restart gracioso) e
aguardando ~7s antes de "resuming normal operations", um comportamento
padrão do Apache ao receber esse sinal do Docker, independente da nossa
config. Não forcei um `SIGKILL`/timeout mais agressivo aqui porque isso
arriscaria cortar requests em andamento — decisão consciente de preservar
estabilidade sobre velocidade de restart.

## 4. O que foi preservado de propósito

- **Nenhum bind mount de código-fonte** foi adicionado. Isso é a decisão
  correta para performance no Docker Desktop/Windows (evita o gargalo
  clássico de `stat()`/`inotify` lento sobre NTFS via 9p/virtiofs) — só que
  como trade-off, **editar código exige rebuild da imagem**
  (`docker compose up -d --build`) para refletir no container. Se no futuro
  quiserem live-edit sem rebuild, isso precisa de bind mount + inverter
  `opcache.validate_timestamps` para `1` — documentado em
  [`docker/php/opcache.ini`](docker/php/opcache.ini).
- `depends_on: condition: service_healthy` do `db`, que já estava correto.
- `save_queries=TRUE` em [`application/config/database.php`](application/config/database.php)
  — mantive porque o custo é irrelevante no volume de queries desta app, e
  desligar reduziria a qualidade do profiling em dev sem ganho mensurável.
- `pconnect=FALSE` na conexão MySQL — **considerei e rejeitei** ligar
  conexões persistentes: com mod_php/prefork, `pconnect` pode reter locks/
  transações abertas entre requests de usuários diferentes que reusam o
  mesmo processo Apache. O ganho de latência (poucos ms de handshake TCP
  local) não compensa esse risco de correção para uma app que já responde
  em ~6ms.

## 5. Limitações honestas

- Docker Desktop no Windows adiciona uma camada de virtualização
  (WSL2/Hyper-V) para toda comunicação de rede entre containers — isso
  afeta `app↔db` mesmo sem bind mount, e não há como eliminar totalmente
  (é o preço de rodar Linux containers no Windows). O que fizemos foi
  **reduzir o número de round-trips** (sessão saindo do banco), não
  eliminar a virtualização em si.
- O restart do container `app` sozinho ainda leva alguns segundos por causa
  do comportamento de graceful-shutdown do próprio Apache diante de sinais
  do Docker — não é algo que o nosso script de bootstrap controle.
- Sem Redis instalado, sessão em arquivo/tmpfs é a melhor opção de baixa
  fricção para 1 container local; não escala para múltiplas instâncias do
  `app` sem uma solução de sessão compartilhada (Redis/DB) — não é o caso
  de uso atual, mas fica registrado.
- `docker-php-ext-install opcache` recompila a extensão durante o build —
  isso soma ~1min ao tempo de build da imagem (não ao runtime), custo pago
  uma vez por rebuild.

## 6. Arquivos criados/modificados

**Criados:**
- `docker/php/opcache.ini`
- `docker/php/local.ini`
- `docker/apache/perf.conf`
- `docker/scripts/start-app.sh`
- `PERFORMANCE.md` (este arquivo)

**Modificados:**
- `Dockerfile` — instala/habilita opcache e curl, copia as novas configs, usa `start-app.sh` como `ENTRYPOINT`
- `docker-compose.yml` — env `SESSION_DRIVER`/`SESSION_SAVE_PATH`, tmpfs de sessão, healthcheck do `app`, portas padrão alinhadas ao `.env.example`
- `application/config/config.php` — `sess_driver`/`sess_save_path` via env com fallback
- `.dockerignore` — não ignora mais `docker/php`, `docker/apache`, `docker/scripts`
