<div class="container-fluid">

    <div class="row mb-2">
        <div class="col-12">
            <h1 class="h3 fw-light text-secondary mb-0">Editar Usuário</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mt-1 mb-0">
                    <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('usuarios') ?>">Usuários</a></li>
                    <li class="breadcrumb-item active">Editar usuário</li>
                </ol>
            </nav>
            <hr class="mt-2">
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-12">
            <?php erros_validacao() ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <form id="form_editar" action="" method="post">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tipo de usuário</label>
                            <select name="tipo_usuario" class="form-select">
                                <option value="1" <?= ($user_group_id == 1) ? 'selected' : '' ?>>Administrador</option>
                                <option value="2" <?= ($user_group_id == 2) ? 'selected' : '' ?>>Vendedor</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nome do usuário</label>
                            <input type="text" class="form-control" name="nome_usuario"
                                   value="<?= set_value('nome_usuario', $user->username) ?>"
                                   placeholder="Nome completo">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">E-mail</label>
                            <input type="email" class="form-control" name="email_usuario"
                                   value="<?= set_value('email_usuario', $user->email) ?>"
                                   placeholder="E-mail do usuário">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Senha</label>
                            <input type="password" class="form-control" name="senha_usuario"
                                   placeholder="Deixe em branco para manter a senha atual">
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Repetir senha</label>
                            <input type="password" class="form-control" name="senha_usuario2"
                                   placeholder="Confirmar senha">
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fa fa-check me-1"></i> Salvar
                            </button>
                            <a href="<?= base_url('usuarios') ?>" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
