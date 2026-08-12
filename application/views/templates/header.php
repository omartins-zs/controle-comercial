<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$logado        = $this->ion_auth->logged_in();
$usuario_logado = $logado ? $this->ion_auth->user()->row() : null;

$rota_atual    = $this->uri->segment(1) ?: 'home';
$rota_completa = trim($this->uri->uri_string(), '/');

function menu_ativo($rota_atual, $rota_completa, $alvo)
{
    if (strpos($alvo, '/') !== false) {
        return $rota_completa === $alvo ? ' ativo' : '';
    }
    return $rota_atual === $alvo ? ' ativo' : '';
}

function grupo_aberto($rota_atual, array $rotas)
{
    return in_array($rota_atual, $rotas, true);
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <?php if ($logado) : ?>
    <script>
        // Restaura colapso da sidebar antes do CSS pintar (sem flash)
        if (window.innerWidth >= 768 && localStorage.getItem('sidebarRecolhida') === '1') {
            document.documentElement.classList.add('sidebar-recolhida');
        }
    </script>
    <?php endif ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="<?= asset_url('assets/css/bs5/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('assets/font-awesome/css/font-awesome.min.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('assets/css/bs5/dataTables.bootstrap5.min.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('assets/css/sistema.css') ?>">
    <title><?= $titulo ?></title>
</head>

<body style="background: #eef2f5;">

<?php if ($logado) : ?>

    <!-- ======================================================= NAVBAR -->
    <nav class="navbar navbar-expand navbar-dark fixed-top px-3"
         style="background: #12618F; height: var(--navbar-height, 56px);">

        <!-- Celular: abre sidebar via JS (sem data-bs-toggle — sidebar é fixed, não offcanvas) -->
        <button class="btn btn-link text-white d-md-none p-1 me-2"
                id="btn-sidebar-mobile"
                type="button"
                aria-label="Abrir menu">
            <i class="fa fa-bars fa-lg"></i>
        </button>

        <!-- Desktop: colapsa/expande sidebar -->
        <button class="btn btn-link text-white d-none d-md-inline-block p-1 me-2"
                id="btn-sidebar-toggle"
                type="button"
                aria-label="Recolher menu">
            <i class="fa fa-bars fa-lg"></i>
        </button>

        <a class="navbar-brand fw-semibold me-auto" href="<?= site_url('home') ?>">
            <i class="fa fa-briefcase me-1"></i>
            <span class="d-none d-sm-inline">Sistema Comercial</span>
        </a>

        <div class="d-flex align-items-center gap-3">
            <span class="text-white opacity-75 small d-none d-sm-inline">
                <i class="fa fa-user-circle-o me-1"></i>
                <?= html_escape($usuario_logado->first_name ?: $usuario_logado->email) ?>
            </span>
            <a class="btn btn-danger btn-sm"
               href="<?= base_url('login/logout') ?>"
               title="Sair do sistema">
                <i class="fa fa-power-off"></i>
                <span class="d-none d-md-inline ms-1">Sair</span>
            </a>
        </div>
    </nav>

    <!-- ======================================================= SIDEBAR -->
    <!-- position:fixed via sistema.css. JS controla body.sidebar-aberta (mobile)
         e html.sidebar-recolhida (desktop). -->
    <nav id="sidebar" style="background: #1570A6;">

        <ul class="nav flex-column pt-2">

            <li class="nav-item">
                <a class="nav-link<?= menu_ativo($rota_atual, $rota_completa, 'home') ?>"
                   href="<?= base_url('home') ?>">
                    <i class="fa fa-home me-2" style="width:16px;text-align:center"></i> Visão Geral
                </a>
            </li>

            <!-- Vendas -->
            <li class="nav-item">
                <a class="nav-link d-flex justify-content-between align-items-center<?= grupo_aberto($rota_atual, ['vendas', 'ordens_servico']) ? '' : ' collapsed' ?>"
                   href="#grupo-vendas" data-bs-toggle="collapse"
                   aria-expanded="<?= grupo_aberto($rota_atual, ['vendas', 'ordens_servico']) ? 'true' : 'false' ?>">
                    <span><i class="fa fa-shopping-cart me-2" style="width:16px;text-align:center"></i> Vendas</span>
                    <i class="fa fa-angle-down sidebar-seta"></i>
                </a>
                <ul class="nav flex-column submenu collapse<?= grupo_aberto($rota_atual, ['vendas', 'ordens_servico']) ? ' show' : '' ?>" id="grupo-vendas">
                    <li class="nav-item">
                        <a class="nav-link<?= menu_ativo($rota_atual, $rota_completa, 'vendas') ?>" href="<?= base_url('vendas') ?>">Venda de produtos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link<?= menu_ativo($rota_atual, $rota_completa, 'ordens_servico') ?>" href="<?= base_url('ordens_servico') ?>">Ordem de serviço</a>
                    </li>
                </ul>
            </li>

            <!-- Cadastro -->
            <li class="nav-item">
                <a class="nav-link d-flex justify-content-between align-items-center<?= grupo_aberto($rota_atual, ['clientes', 'fornecedores', 'vendedores', 'transportadoras']) ? '' : ' collapsed' ?>"
                   href="#grupo-cadastro" data-bs-toggle="collapse"
                   aria-expanded="<?= grupo_aberto($rota_atual, ['clientes', 'fornecedores', 'vendedores', 'transportadoras']) ? 'true' : 'false' ?>">
                    <span><i class="fa fa-users me-2" style="width:16px;text-align:center"></i> Cadastro</span>
                    <i class="fa fa-angle-down sidebar-seta"></i>
                </a>
                <ul class="nav flex-column submenu collapse<?= grupo_aberto($rota_atual, ['clientes', 'fornecedores', 'vendedores', 'transportadoras']) ? ' show' : '' ?>" id="grupo-cadastro">
                    <li class="nav-item"><a class="nav-link<?= menu_ativo($rota_atual, $rota_completa, 'clientes') ?>" href="<?= base_url('clientes') ?>">Clientes</a></li>
                    <li class="nav-item"><a class="nav-link<?= menu_ativo($rota_atual, $rota_completa, 'fornecedores') ?>" href="<?= base_url('fornecedores') ?>">Fornecedores</a></li>
                    <li class="nav-item"><a class="nav-link<?= menu_ativo($rota_atual, $rota_completa, 'vendedores') ?>" href="<?= base_url('vendedores') ?>">Vendedores</a></li>
                    <li class="nav-item"><a class="nav-link<?= menu_ativo($rota_atual, $rota_completa, 'transportadoras') ?>" href="<?= base_url('transportadoras') ?>">Transportadoras</a></li>
                </ul>
            </li>

            <!-- Estoque -->
            <li class="nav-item">
                <a class="nav-link d-flex justify-content-between align-items-center<?= grupo_aberto($rota_atual, ['produtos', 'categorias', 'marcas']) ? '' : ' collapsed' ?>"
                   href="#grupo-estoque" data-bs-toggle="collapse"
                   aria-expanded="<?= grupo_aberto($rota_atual, ['produtos', 'categorias', 'marcas']) ? 'true' : 'false' ?>">
                    <span><i class="fa fa-cubes me-2" style="width:16px;text-align:center"></i> Controle de Estoque</span>
                    <i class="fa fa-angle-down sidebar-seta"></i>
                </a>
                <ul class="nav flex-column submenu collapse<?= grupo_aberto($rota_atual, ['produtos', 'categorias', 'marcas']) ? ' show' : '' ?>" id="grupo-estoque">
                    <li class="nav-item"><a class="nav-link<?= menu_ativo($rota_atual, $rota_completa, 'produtos') ?>" href="<?= base_url('produtos') ?>">Cadastro de produtos</a></li>
                    <li class="nav-item"><a class="nav-link<?= menu_ativo($rota_atual, $rota_completa, 'categorias') ?>" href="<?= base_url('categorias') ?>">Categorias</a></li>
                    <li class="nav-item"><a class="nav-link<?= menu_ativo($rota_atual, $rota_completa, 'marcas') ?>" href="<?= base_url('marcas') ?>">Marcas</a></li>
                    <li class="nav-item"><a class="nav-link<?= menu_ativo($rota_atual, $rota_completa, 'relatorios/estoque') ?>" href="<?= base_url('relatorios/estoque') ?>">Movimentações de estoque</a></li>
                </ul>
            </li>

            <!-- Financeiro -->
            <li class="nav-item">
                <a class="nav-link d-flex justify-content-between align-items-center<?= grupo_aberto($rota_atual, ['contas_pagar', 'contas_receber']) ? '' : ' collapsed' ?>"
                   href="#grupo-financeiro" data-bs-toggle="collapse"
                   aria-expanded="<?= grupo_aberto($rota_atual, ['contas_pagar', 'contas_receber']) ? 'true' : 'false' ?>">
                    <span><i class="fa fa-money me-2" style="width:16px;text-align:center"></i> Financeiro</span>
                    <i class="fa fa-angle-down sidebar-seta"></i>
                </a>
                <ul class="nav flex-column submenu collapse<?= grupo_aberto($rota_atual, ['contas_pagar', 'contas_receber']) ? ' show' : '' ?>" id="grupo-financeiro">
                    <li class="nav-item"><a class="nav-link<?= menu_ativo($rota_atual, $rota_completa, 'contas_pagar') ?>" href="<?= base_url('contas_pagar') ?>">Contas a pagar</a></li>
                    <li class="nav-item"><a class="nav-link<?= menu_ativo($rota_atual, $rota_completa, 'contas_receber') ?>" href="<?= base_url('contas_receber') ?>">Contas a receber</a></li>
                </ul>
            </li>

            <!-- Relatórios -->
            <li class="nav-item">
                <a class="nav-link d-flex justify-content-between align-items-center<?= grupo_aberto($rota_atual, ['relatorios']) ? '' : ' collapsed' ?>"
                   href="#grupo-relatorios" data-bs-toggle="collapse"
                   aria-expanded="<?= grupo_aberto($rota_atual, ['relatorios']) ? 'true' : 'false' ?>">
                    <span><i class="fa fa-file-text-o me-2" style="width:16px;text-align:center"></i> Relatórios</span>
                    <i class="fa fa-angle-down sidebar-seta"></i>
                </a>
                <ul class="nav flex-column submenu collapse<?= grupo_aberto($rota_atual, ['relatorios']) ? ' show' : '' ?>" id="grupo-relatorios">
                    <li class="nav-item"><a class="nav-link<?= menu_ativo($rota_atual, $rota_completa, 'relatorios/clientes') ?>" href="<?= base_url('relatorios/clientes') ?>">Clientes</a></li>
                    <li class="nav-item"><a class="nav-link<?= menu_ativo($rota_atual, $rota_completa, 'relatorios/produtos') ?>" href="<?= base_url('relatorios/produtos') ?>">Produtos</a></li>
                    <li class="nav-item"><a class="nav-link<?= menu_ativo($rota_atual, $rota_completa, 'relatorios/vendas') ?>" href="<?= base_url('relatorios/vendas') ?>">Vendas</a></li>
                    <li class="nav-item"><a class="nav-link<?= menu_ativo($rota_atual, $rota_completa, 'relatorios/ordens_servico') ?>" href="<?= base_url('relatorios/ordens_servico') ?>">Ordens de serviço</a></li>
                    <li class="nav-item"><a class="nav-link<?= menu_ativo($rota_atual, $rota_completa, 'relatorios/contas_pagar') ?>" href="<?= base_url('relatorios/contas_pagar') ?>">Contas a pagar</a></li>
                    <li class="nav-item"><a class="nav-link<?= menu_ativo($rota_atual, $rota_completa, 'relatorios/contas_receber') ?>" href="<?= base_url('relatorios/contas_receber') ?>">Contas a receber</a></li>
                </ul>
            </li>

            <!-- Configuração -->
            <li class="nav-item">
                <a class="nav-link d-flex justify-content-between align-items-center<?= grupo_aberto($rota_atual, ['config', 'usuarios']) ? '' : ' collapsed' ?>"
                   href="#grupo-config" data-bs-toggle="collapse"
                   aria-expanded="<?= grupo_aberto($rota_atual, ['config', 'usuarios']) ? 'true' : 'false' ?>">
                    <span><i class="fa fa-cog me-2" style="width:16px;text-align:center"></i> Configuração</span>
                    <i class="fa fa-angle-down sidebar-seta"></i>
                </a>
                <ul class="nav flex-column submenu collapse<?= grupo_aberto($rota_atual, ['config', 'usuarios']) ? ' show' : '' ?>" id="grupo-config">
                    <li class="nav-item"><a class="nav-link<?= menu_ativo($rota_atual, $rota_completa, 'config') ?>" href="<?= base_url('config') ?>">Sistema</a></li>
                    <li class="nav-item"><a class="nav-link<?= menu_ativo($rota_atual, $rota_completa, 'usuarios') ?>" href="<?= base_url('usuarios') ?>">Usuários</a></li>
                </ul>
            </li>

            <!-- Sair (mobile) -->
            <li class="nav-item d-md-none border-top border-white border-opacity-25 mt-2 pt-2">
                <a class="nav-link text-danger" href="<?= base_url('login/logout') ?>">
                    <i class="fa fa-power-off me-2" style="width:16px;text-align:center"></i> Sair
                </a>
            </li>

        </ul>
    </nav><!-- #sidebar -->

    <!-- Backdrop para fechar a sidebar no celular -->
    <div class="sidebar-backdrop" id="sidebar-backdrop"></div>

    <!-- Conteúdo da página (margem-left = largura da sidebar, via sistema.css) -->
    <main id="conteudo" class="p-3 p-md-4">

<?php else : ?>

    <!-- Página pública (login/cadastro) -->
    <main class="min-vh-100 d-flex align-items-center justify-content-center p-3"
          style="background: linear-gradient(135deg, #1570A6 0%, #0d4d72 100%);">

<?php endif ?>
