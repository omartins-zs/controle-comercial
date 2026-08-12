# Rodando o projeto com Docker

Este projeto (CodeIgniter 3 + Ion Auth) foi dockerizado para rodar em qualquer
máquina sem depender de Laragon/XAMPP local. A stack sobe 3 containers:

- **app** — PHP 7.4 + Apache servindo o CodeIgniter
- **db** — MySQL 8.0, com o schema e o usuário admin importados automaticamente
  na primeira inicialização (via `docker/mysql-init/01-schema.sql`)
- **phpmyadmin** — interface web para inspecionar o banco (opcional)

## Pré-requisitos

- Docker e Docker Compose instalados ([Docker Desktop](https://www.docker.com/products/docker-desktop/) no Windows/Mac, ou `docker` + `docker compose` no Linux)

## Como subir

```bash
cp .env.example .env
docker compose up -d --build
```

Isso vai:
1. Buildar a imagem PHP/Apache com as extensões necessárias (`mysqli`, `pdo_mysql`, `zip`) e `mod_rewrite` habilitado.
2. Criar o banco `blog_comercial` e importar automaticamente as tabelas do Ion Auth (`groups`, `users`, `users_groups`, `login_attempts`, `ci_sessions`) já com o usuário administrador.
3. Publicar a aplicação em `http://localhost:8090/` (porta configurável em `.env`).

## Acessos

| Serviço     | URL                                | Credenciais                          |
|-------------|-------------------------------------|----------------------------------------|
| Aplicação   | http://localhost:8090/              | `admin@admin.com` / `password`         |
| phpMyAdmin  | http://localhost:8091/              | usuário `root`, senha de `MYSQL_ROOT_PASSWORD` no `.env` |
| MySQL (host)| `localhost:3307` (ou o valor de `DB_EXTERNAL_PORT`) | usuário/senha em `.env` (`MYSQL_USER`/`MYSQL_PASSWORD`) |

> As portas padrão (8090/8091/3307) foram escolhidas para não colidir com
> outros projetos que já possam estar rodando no mesmo Docker. Ajuste
> livremente no `.env` se precisar.

## Variáveis de ambiente (`.env`)

Veja `.env.example` para a lista completa. As principais:

- `APP_PORT` — porta HTTP exposta da aplicação no host
- `APP_BASE_URL` — precisa bater com `APP_PORT` (usada pelo CodeIgniter para gerar links)
- `MYSQL_DATABASE` / `MYSQL_USER` / `MYSQL_PASSWORD` / `MYSQL_ROOT_PASSWORD`
- `CI_ENV` — `development` (padrão), `testing` ou `production`

## Comandos úteis

```bash
# ver logs da aplicação
docker compose logs -f app

# parar tudo
docker compose down

# parar e apagar também os dados do banco (reset completo)
docker compose down -v

# reconstruir a imagem depois de mudar o Dockerfile
docker compose up -d --build
```

## Persistência de dados

Os dados do MySQL ficam no volume nomeado `db_data`. Enquanto ele não for
removido (`docker compose down -v`), os dados sobrevivem a reinicializações
dos containers.

## Observações sobre o código

- `application/config/database.php` agora lê `DB_HOST`, `DB_USERNAME`,
  `DB_PASSWORD`, `DB_DATABASE` e `DB_PORT` do ambiente, com fallback para os
  valores originais (`localhost`/`root`/``/`blog_comercial`) — então o projeto
  continua funcionando normalmente fora do Docker (ex.: Laragon), sem precisar
  configurar nada.
- `application/config/config.php` lê `APP_BASE_URL` do ambiente, com fallback
  para a URL original.
- O schema em `docker/mysql-init/01-schema.sql` recria as tabelas do
  `blog_comercial.sql` original (sem as sessões antigas, que eram apenas
  lixo de sessões expiradas) e já cria o usuário administrador padrão.
