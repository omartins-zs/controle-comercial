<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<!-- Card de login — centralizado pelo <main> do header.php (pagina-publica) -->
<div class="card shadow-lg border-0" style="width:100%;max-width:420px;">
    <div class="card-body p-4 p-md-5">

        <div class="text-center mb-4">
            <div class="mb-3">
                <i class="fa fa-briefcase fa-3x text-primary"></i>
            </div>
            <h4 class="fw-bold">Sistema Comercial</h4>
            <p class="text-muted small mb-0">Entre com suas credenciais para continuar</p>
        </div>

        <?php get_msg('msgerro') ?>
        <?php get_msg('msgsucess') ?>

        <form action="" method="post" id="form-login">
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="fa fa-envelope-o"></i></span>
                <input type="email"
                       name="login"
                       id="login"
                       class="form-control"
                       value="<?= set_value('login') ?>"
                       placeholder="Digite seu e-mail"
                       autocomplete="username"
                       required
                       autofocus>
            </div>

            <div class="input-group mb-4">
                <span class="input-group-text"><i class="fa fa-lock"></i></span>
                <input type="password"
                       name="senha"
                       id="senha"
                       class="form-control"
                       placeholder="Digite sua senha"
                       autocomplete="current-password"
                       required>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fa fa-sign-in me-1"></i> ENTRAR
                </button>
            </div>
        </form>

        <div class="text-center mt-3 small text-muted">
            Não tem conta?
            <a href="<?= base_url('cadastrar') ?>" class="text-decoration-none fw-semibold">Solicitar acesso</a>
        </div>

    </div>
</div>
