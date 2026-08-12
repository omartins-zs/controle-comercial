<!-- Listagem de produtos com botão extra de movimentação de estoque.
     Produtos::index() usa esta view em vez da genérica (cadastro/index.php). -->

<div class="container-fluid">

    <div class="row mb-2">
        <div class="col-12">
            <h1 class="h3 fw-light text-secondary mb-0"><?= $titulo ?></h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mt-1 mb-0">
                    <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                    <li class="breadcrumb-item active"><?= $titulo ?></li>
                </ol>
            </nav>
            <hr class="mt-2">
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12 text-end">
            <a href="<?= base_url('relatorios/estoque') ?>" class="btn btn-secondary me-1">
                <i class="fa fa-history me-1"></i> Histórico
            </a>
            <a href="<?= base_url($base_route . '/add') ?>" class="btn btn-success">
                <i class="fa fa-plus-square me-1"></i> Novo
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
                        <?php foreach ($campos as $campo) : ?>
                            <th><?= $campo['label'] ?></th>
                        <?php endforeach ?>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item) : ?>
                        <tr>
                            <td><?= $item->id ?></td>
                            <td><?= html_escape($item->nome) ?></td>
                            <?php foreach ($campos as $campo) : ?>
                                <td><?= isset($item->{$campo['key']}) ? html_escape($item->{$campo['key']}) : '' ?></td>
                            <?php endforeach ?>
                            <td class="text-end text-nowrap">
                                <a href="<?= base_url($base_route . '/movimentar/' . $item->id) ?>"
                                   title="Movimentar estoque"
                                   class="btn btn-sm btn-info">
                                    <i class="fa fa-exchange"></i>
                                </a>
                                <a href="<?= base_url($base_route . '/editar/' . $item->id) ?>"
                                   title="Editar"
                                   class="btn btn-sm btn-warning">
                                    <i class="fa fa-pencil"></i>
                                </a>
                                <a href="<?= base_url($base_route . '/apagar/' . $item->id) ?>"
                                   title="Apagar"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Tem certeza que deseja excluir este registro?')">
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
