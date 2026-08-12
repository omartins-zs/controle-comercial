<div class="container-fluid">

    <div class="row mb-2">
        <div class="col-12">
            <h1 class="h3 fw-light text-secondary mb-0">Usuários cadastrados</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mt-1 mb-0">
                    <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                    <li class="breadcrumb-item active">Usuários</li>
                </ol>
            </nav>
            <hr class="mt-2">
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12 text-end">
            <a href="<?= base_url('usuarios/add') ?>" class="btn btn-success">
                <i class="fa fa-plus-square me-1"></i> Novo usuário
            </a>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <?php get_msg('msgerro'); get_msg('msgsucess'); ?>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover table-bordered mb-0" id="datatable">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user) : ?>
                        <tr>
                            <td><?= $user->id ?></td>
                            <td><?= html_escape($user->username) ?></td>
                            <td><?= html_escape($user->email) ?></td>
                            <td class="text-end text-nowrap">
                                <a href="<?= base_url('usuarios/editar/' . $user->id) ?>"
                                   title="Editar usuário"
                                   class="btn btn-sm btn-warning">
                                    <i class="fa fa-pencil"></i>
                                </a>
                                <a href="<?= base_url('usuarios/apagar/' . $user->id) ?>"
                                   title="Apagar cadastro"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Tem certeza que deseja excluir este usuário?')">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
