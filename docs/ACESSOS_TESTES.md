# 🔐 Acessos e Dados de Teste

> **Nota de contexto:** este projeto é **CodeIgniter 3 + Ion Auth** (não Laravel).
> Não existem `Seeders`/`artisan` — os dados de teste vêm dos scripts de
> inicialização do MySQL usados pelo Docker:
> [`docker/mysql-init/01-schema.sql`](../docker/mysql-init/01-schema.sql) (login/usuários),
> [`docker/mysql-init/02-modulos-comerciais.sql`](../docker/mysql-init/02-modulos-comerciais.sql)
> (clientes, produtos, vendas, financeiro etc.) e
> [`docker/mysql-init/03-os-estoque.sql`](../docker/mysql-init/03-os-estoque.sql)
> (ordens de serviço + histórico de estoque). Esses scripts são executados
> **apenas na primeira vez** que o volume do banco (`db_data`) é criado.

## 1. Acesso ao Sistema (Usuários de Teste)

| Perfil | E-mail / Usuário | Senha | Permissão / Detalhes |
| --- | --- | --- | --- |
| Administrador | `admin@admin.com` | `password` | Grupo `Administrador` (id 1) no Ion Auth. Acesso total, incluindo a tela de gestão de usuários (`/usuarios`), restrita a esse grupo. |

> Não há usuário de teste seedado para o grupo **Vendedores** (id 2) — esse
> grupo existe na tabela `groups`, mas nenhum usuário é criado nele por
> padrão. Para testar esse perfil, crie um usuário em `/usuarios/add`
> selecionando o tipo/grupo "Vendedores".

## 2. URLs Principais

| Ambiente | Aplicação (Home) | Login |
| --- | --- | --- |
| **Docker** | `http://localhost:8090/` | `http://localhost:8090/login` |
| **Local** (Laragon/XAMPP, sem Docker) | `http://localhost/controle_comercial/` | `http://localhost/controle_comercial/login` |

> A porta `8090` é a definida em `.env` (`APP_PORT`). Se você alterou essa
> variável, ajuste a URL acima. phpMyAdmin fica em `http://localhost:8091/`
> (variável `PMA_PORT`).

## 3. Rotas do sistema

Este projeto é um painel interno (não há vitrine pública para clientes).
Todos os módulos abaixo têm listagem, cadastro, edição e exclusão
(`/<rota>`, `/<rota>/add`, `/<rota>/editar/<id>`, `/<rota>/apagar/<id>`),
exceto onde indicado.

| Módulo | Rota base | Observação |
| --- | --- | --- |
| Home / Dashboard | `/` , `/home` | Indicadores + últimas vendas |
| Login | `/login`, `/login/logout` | Público / logado |
| Usuários (login) | `/usuarios` | Somente grupo Administrador (id 1) |
| Clientes | `/clientes` | |
| Fornecedores | `/fornecedores` | |
| Vendedores (cadastro comercial) | `/vendedores` | Distinto dos usuários de login |
| Transportadoras | `/transportadoras` | |
| Categorias | `/categorias` | |
| Marcas | `/marcas` | |
| Produtos / Estoque | `/produtos` | Categoria e marca via select; baixa/repõe estoque automaticamente em vendas e OS |
| Movimentação de estoque | `/produtos/movimentar/<id>` (ajuste manual), `/relatorios/estoque` (histórico) | Toda entrada/saída/ajuste fica registrada, com saldo resultante |
| Vendas | `/vendas`, `/vendas/add`, `/vendas/ver/<id>`, `/vendas/recibo/<id>` | Mestre-detalhe (itens); sem edição, só criar/ver/apagar. Recibo = comprovante interno, **não é NF-e** |
| Ordens de Serviço | `/ordens_servico`, `/ordens_servico/add`, `/ordens_servico/ver/<id>` (troca status ali), `/ordens_servico/recibo/<id>` | Itens tipo Serviço (texto livre) ou Peça (produto, baixa estoque ao concluir) |
| Contas a Pagar | `/contas_pagar` | |
| Contas a Receber | `/contas_receber` | |
| Relatórios | `/relatorios/clientes`, `/relatorios/produtos`, `/relatorios/vendas`, `/relatorios/ordens_servico`, `/relatorios/estoque`, `/relatorios/contas_pagar`, `/relatorios/contas_receber` | Somente leitura |
| Configurações do sistema | `/config` | Somente grupo Administrador (id 1) |

**Sobre o "recibo" de Vendas/OS:** é um comprovante interno para impressão
(botão 🖨️ na tela de detalhe), **não é uma Nota Fiscal Eletrônica**. Emitir
NF-e de verdade exige certificado digital e integração com a SEFAZ — fora do
escopo de um sistema local. O documento deixa isso explícito para não
confundir o cliente final.

## 4. Validação do Acesso

| Verificação | Resultado Esperado |
| --- | --- |
| Containers (`controle_comercial_app`, `controle_comercial_db`, `controle_comercial_pma`) | `docker compose ps` → status `running`/`healthy` |
| `GET /` (sem login) | Redireciona (HTTP 302/303) para `/login` |
| `GET /login` | HTTP `200` |
| Login com `admin@admin.com` / `password` | Redireciona para `/home` (HTTP 200), cookie `identity` definido |
| `GET /usuarios` logado como admin | HTTP `200`, lista de usuários |

## 5. Carregar Dados de Teste

Como não há `artisan`/seeders, o reset é feito recriando o volume do MySQL —
o `docker-entrypoint-initdb.d` roda `01-schema.sql` e `02-modulos-comerciais.sql`
de novo automaticamente na próxima subida, com: 5 clientes, 3 fornecedores,
3 vendedores, 2 transportadoras, 4 categorias, 4 marcas, 10 produtos, 6 vendas
(com itens) e algumas contas a pagar/receber — todos fictícios.

**Com Docker (reset completo do banco):**
```bash
docker compose down -v
docker compose up -d --build
```

**Rodando localmente (sem Docker, ex.: Laragon):**
Importe manualmente o dump em [`blog_comercial.sql`](../blog_comercial.sql)
(ou o schema equivalente em [`docker/mysql-init/01-schema.sql`](../docker/mysql-init/01-schema.sql))
no seu MySQL local, no banco `blog_comercial`.

---

### 📝 Observações

- Use estas credenciais **apenas** em ambiente local ou Docker de
  desenvolvimento — o hash de senha do admin é o mesmo em qualquer máquina
  que rodar este projeto a partir do repositório.
- `docker compose down -v` apaga o volume `db_data` e todos os dados
  cadastrados manualmente durante os testes (usuários extras criados via
  `/usuarios/add`, sessões, etc.) — os dados voltam ao estado inicial do
  `01-schema.sql`.
