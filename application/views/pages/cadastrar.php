<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<!-- Card de cadastro — centralizado pelo <main> do header.php (pagina-publica) -->
<div class="card shadow-lg border-0" style="width:100%;max-width:440px;">
    <div class="card-body p-4 p-md-5">

        <div class="text-center mb-4">
            <div class="mb-3">
                <i class="fa fa-user-plus fa-3x text-primary"></i>
            </div>
            <h4 class="fw-bold">Criar Conta</h4>
            <p class="text-muted small mb-0">
                Preencha os dados e acesse imediatamente o sistema.
            </p>
        </div>

        <?php get_msg('msgerro') ?>
        <?php get_msg('msgsucess') ?>
        <?php erros_validacao() ?>

        <form action="" method="post">

            <div class="input-group mb-3">
                <span class="input-group-text"><i class="fa fa-user"></i></span>
                <input type="text"
                       name="nome"
                       class="form-control"
                       value="<?= set_value('nome') ?>"
                       placeholder="Seu nome completo"
                       required
                       autofocus>
            </div>

            <div class="input-group mb-3">
                <span class="input-group-text"><i class="fa fa-envelope-o"></i></span>
                <input type="email"
                       name="email"
                       class="form-control"
                       value="<?= set_value('email') ?>"
                       placeholder="Seu e-mail"
                       required>
            </div>

            <div class="input-group mb-3">
                <span class="input-group-text"><i class="fa fa-lock"></i></span>
                <input type="password"
                       name="senha"
                       class="form-control"
                       placeholder="Senha (mín. 8 caracteres)"
                       required>
            </div>

            <div class="input-group mb-4">
                <span class="input-group-text"><i class="fa fa-lock"></i></span>
                <input type="password"
                       name="senha2"
                       class="form-control"
                       placeholder="Confirmar senha"
                       required>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fa fa-user-plus me-1"></i> CRIAR CONTA
                </button>
            </div>
        </form>

        <div class="text-center mt-3 small text-muted">
            Já tem conta?
            <a href="<?= base_url('login') ?>" class="text-decoration-none fw-semibold">Entrar</a>
        </div>

    </div>
</div>
