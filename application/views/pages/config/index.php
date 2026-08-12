<div class="container-fluid">

    <div class="row mb-2">
        <div class="col-12">
            <h1 class="h3 fw-light text-secondary mb-0"><?= $titulo ?></h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mt-1 mb-0">
                    <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                    <li class="breadcrumb-item active">Configurações</li>
                </ol>
            </nav>
            <hr class="mt-2">
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <?php erros_validacao() ?>
            <?php get_msg('msgerro'); get_msg('msgsucess'); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <form action="" method="post">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nome do sistema</label>
                            <input type="text" class="form-control" name="site_nome"
                                   value="<?= set_value('site_nome', $config->site_nome) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">E-mail do administrador</label>
                            <input type="email" class="form-control" name="admin_email"
                                   value="<?= set_value('admin_email', $config->admin_email) ?>">
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-check me-1"></i> Salvar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
