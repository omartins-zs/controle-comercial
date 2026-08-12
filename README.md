<div align="center">

<img src="https://skillicons.dev/icons?i=php,bootstrap,mysql,docker,jquery" height="60" alt="Stack do projeto" />

<h1>⚙️ Controle Comercial</h1>

<p>Sistema web de gestão comercial — vendas, estoque, financeiro e ordens de serviço — construído com CodeIgniter 3, Bootstrap 5 e autenticação Ion Auth.</p>

<cite>Solução completa para pequenas e médias empresas controlarem vendas, clientes, fornecedores, estoque, contas e relatórios em um único painel.</cite>

---

<h4>✅ Controle Comercial 🚀 Em desenvolvimento ⚙️</h4>

---

[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net/)
[![CodeIgniter](https://img.shields.io/badge/CodeIgniter-3.1.13-E44D26?style=flat-square&logo=codeigniter&logoColor=white)](https://codeigniter.com/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3.3-7952B3?style=flat-square&logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?style=flat-square&logo=docker&logoColor=white)](https://www.docker.com/)
[![PHPUnit](https://img.shields.io/badge/PHPUnit-9.6-6C9AC3?style=flat-square&logo=php&logoColor=white)](https://phpunit.de/)

</div>

---

## 🏗️ Arquitetura do Projeto

| Item | Detalhe |
|------|---------|
| **Tipo** | 🧱 Monólito MVC |
| **Padrão** | MVC (Model - View - Controller) |
| **Auth** | Ion Auth (sessões em banco de dados) |
| **Frontend** | Server-side rendering via PHP (sem SPA) |
| **Banco** | MySQL 8.0, inicializado automaticamente pelo Docker |
| **Container** | PHP 7.4 + Apache + MySQL + phpMyAdmin |

O backend renderiza as páginas PHP e as entrega prontas ao navegador. Não há API separada — todo o fluxo é controller → model → view dentro do CodeIgniter 3.

---

## 🔥 Pré-requisitos

- **Docker** 20+ e **Docker Compose** v2 ([Docker Desktop](https://www.docker.com/products/docker-desktop/) no Windows/Mac)
- **OU** PHP 7.4+, Apache/Nginx com `mod_rewrite`, MySQL 5.7+ (para rodar sem Docker)
- Composer (apenas para instalar PHPUnit em desenvolvimento)

---

## 🚀 Tecnologias Utilizadas

| Tecnologia | Versão | Uso |
|-----------|--------|-----|
| PHP | 7.4+ | Linguagem principal |
| CodeIgniter | 3.1.13 | Framework MVC |
| Ion Auth | 3.x | Autenticação e controle de grupos |
| Bootstrap | 5.3.3 | UI responsiva |
| jQuery | 3.7.1 | Interatividade e DataTables |
| DataTables | 1.13.8 | Tabelas com busca, ordenação e paginação |
| jQuery Mask | 1.14 | Máscaras de CPF/CNPJ/telefone/data |
| Font Awesome | 4.7 | Ícones |
| MySQL | 8.0 | Banco de dados relacional |
| Docker | 20+ | Containerização |
| Docker Compose | v2 | Orquestração dos containers |
| PHPUnit | 9.6 | Testes de integração via HTTP |

---

## 🔨 Funcionalidades

### 👤 Usuários & Autenticação
- Login e logout com sessão segura (Ion Auth)
- Auto-cadastro público (`/cadastrar`) — conta ativa imediatamente
- Gestão de usuários (criar, editar, ativar/desativar)
- Controle de grupos: Administrador e Vendedor

### 🛒 Vendas
- Registro de vendas com múltiplos itens e produtos
- Seleção de cliente, vendedor e forma de pagamento
- Controle de status (Orçamento → Concluída → Cancelada)
- Impressão de recibo
- Baixa automática no estoque ao fechar venda

### 🔧 Ordens de Serviço
- Abertura de OS com descrição e peças utilizadas
- Fluxo de status (Aberta → Em andamento → Concluída)
- Impressão de recibo de OS

### 📦 Controle de Estoque
- Cadastro de produtos com categoria, marca e estoque mínimo
- Entrada e saída manual de estoque com justificativa
- Relatório de movimentações

### 🏢 Cadastros
- Clientes (PF e PJ — CPF/CNPJ com máscara)
- Fornecedores, Vendedores, Transportadoras
- Categorias e Marcas de produtos

### 💰 Financeiro
- Contas a pagar (com fornecedor, vencimento e status)
- Contas a receber (com cliente, vencimento e status)
- Status Pendente / Pago / Recebido

### 📊 Relatórios
- Clientes, Produtos, Vendas, Ordens de Serviço
- Contas a pagar e a receber
- Movimentações de estoque

### ⚙️ Configuração
- Dados da empresa (nome, CNPJ, endereço, telefone)

---

## 🎯 Sobre o Projeto

Sistema desenvolvido para demonstrar boas práticas de desenvolvimento com PHP e o framework CodeIgniter 3. O projeto aplica arquitetura MVC limpa, base CRUD reutilizável (`Cadastro_base`), autenticação com Ion Auth, integração total com Bootstrap 5 (sem CSS manual), containerização Docker e suite de testes de integração via HTTP com PHPUnit.

Todo o ambiente de desenvolvimento sobe com um único comando (`docker compose up -d --build`), sem necessidade de configuração local de PHP, Apache ou MySQL.

---

## 📸 Preview

🚧 Preview não disponível no projeto.

---

## 📁 Documentação

A pasta [`docs/`](docs/) contém:
- [`ACESSOS_TESTES.md`](docs/ACESSOS_TESTES.md) — credenciais de acesso e usuários de teste

O arquivo [`DOCKER.md`](DOCKER.md) documenta toda a configuração Docker, variáveis de ambiente e comandos úteis.

O arquivo [`PERFORMANCE.md`](PERFORMANCE.md) documenta as otimizações de performance aplicadas (OPcache, compressão Apache).

---

## 💻 Como Executar

### ▶️ Com Docker (recomendado)

```bash
# 1. Clone o repositório
git clone https://github.com/omartins-zs/controle-comercial.git
cd controle-comercial

# 2. Copie as variáveis de ambiente
cp .env.example .env

# 3. Suba os containers (build automático)
docker compose up -d --build
```

A aplicação estará disponível em **http://localhost:8090**

| Serviço | URL | Credenciais |
|---------|-----|-------------|
| Aplicação | http://localhost:8090 | `admin@admin.com` / `password` |
| phpMyAdmin | http://localhost:8091 | `root` / senha do `.env` |

### 🛑 Parar os containers

```bash
docker compose down
```

### 🔄 Rebuild após mudanças no código

```bash
docker compose up -d --build
```

### 🗑️ Reset completo (apaga banco)

```bash
docker compose down -v
```

---

### ▶️ Sem Docker (Laragon / XAMPP)

```bash
# 1. Clone o repositório na pasta do servidor web
git clone https://github.com/omartins-zs/controle-comercial.git

# 2. Importe o banco de dados
# Use o arquivo blog_comercial.sql (ou os scripts em docker/mysql-init/)

# 3. Configure application/config/database.php
# Ajuste host, usuário, senha e banco

# 4. Acesse pelo navegador
# http://localhost/controle-comercial
```

> ⚠️ Verifique [`DOCKER.md`](DOCKER.md) para instruções completas e variáveis de ambiente disponíveis.

---

### 🧪 Rodar os Testes

```bash
# Instalar dependências de desenvolvimento
composer install

# Com o Docker rodando (app em localhost:8090)
vendor/bin/phpunit --testdox
```

Os testes são de **integração via HTTP** — fazem requisições reais contra o app rodando no Docker e validam o comportamento completo da aplicação.

---

## 🧱 Estrutura do Projeto

```
controle-comercial/
├── application/
│   ├── config/          # Configurações (DB, Ion Auth, rotas)
│   ├── controllers/     # Controllers MVC (Vendas, Clientes, Produtos…)
│   ├── core/            # Cadastro_base — CRUD genérico reutilizável
│   ├── helpers/         # funcao_helper (badges, datas, cache-busting)
│   ├── models/          # Models de negócio (Venda, Estoque, OS)
│   └── views/
│       ├── pages/       # Views por módulo (vendas/, clientes/, etc.)
│       └── templates/   # header.php e footer.php (layout geral)
├── assets/
│   ├── css/bs5/         # Bootstrap 5.3.3 + DataTables
│   ├── js/bs5/          # jQuery + Bootstrap bundle + DataTables
│   └── css/sistema.css  # Apenas o que o Bootstrap não cobre (sidebar fixa)
├── docker/
│   ├── mysql-init/      # SQLs executados automaticamente no primeiro boot
│   ├── php/             # php.ini e OPcache
│   └── apache/          # Configuração de performance
├── tests/               # Testes de integração PHPUnit
├── docs/                # Documentação adicional
├── Dockerfile
├── docker-compose.yml
└── .env.example
```

---

## 📝 Melhorias Futuras

- [ ] Migrar para CodeIgniter 4
- [ ] Adicionar gráficos no dashboard (Chart.js)
- [ ] Implementar relatórios em PDF
- [ ] Adicionar API REST para integração com apps mobile
- [ ] Sistema de notificações de contas vencendo
- [ ] Controle de permissões por módulo (além de grupo)

---

<div align="center">

Feito com ❤️ por **Gabriel Martins** 🚀

[![GitHub](https://img.shields.io/badge/GitHub-omartins--zs-181717?style=flat-square&logo=github)](https://github.com/omartins-zs)

</div>
