<div class="container-fluid">

    <div class="row mb-2">
        <div class="col-12">
            <h1 class="h3 fw-light text-secondary mb-0">Vendas</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mt-1 mb-0">
                    <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                    <li class="breadcrumb-item active">Vendas</li>
                </ol>
            </nav>
            <hr class="mt-2">
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12 text-end">
            <a href="<?= base_url('vendas/add') ?>" class="btn btn-success">
                <i class="fa fa-plus-square me-1"></i> Nova venda
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
                        <th>Cliente</th>
                        <th>Vendedor</th>
                        <th>Data</th>
                        <th>Status</th>
                        <th>Total (R$)</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vendas as $venda) : ?>
                        <tr>
                            <td><?= $venda->id ?></td>
                            <td><?= $venda->cliente_nome ?: '-' ?></td>
                            <td><?= $venda->vendedor_nome ?: '-' ?></td>
                            <td><?= data_br($venda->data_venda) ?></td>
                            <td><?= status_badge($venda->status) ?></td>
                            <td><?= number_format($venda->total, 2, ',', '.') ?></td>
                            <td class="text-end text-nowrap">
                                <a href="<?= base_url('vendas/ver/' . $venda->id) ?>"
                                   title="Ver detalhes"
                                   class="btn btn-sm btn-primary">
                                    <i class="fa fa-eye"></i>
                                </a>
                                <a href="<?= base_url('vendas/apagar/' . $venda->id) ?>"
                                   title="Apagar"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Tem certeza que deseja excluir esta venda?')">
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
