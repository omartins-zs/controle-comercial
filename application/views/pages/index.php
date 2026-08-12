<div class="container-fluid">

    <div class="row mb-3">
        <div class="col-12">
            <h1 class="h3 fw-light text-secondary mb-0">Visão Geral</h1>
            <hr class="mt-2">
        </div>
    </div>

    <!-- Flash messages -->
    <div class="row">
        <div class="col-12">
            <?php get_msg('msgerro'); get_msg('msgsucess'); ?>
        </div>
    </div>

    <!-- Cartões de indicadores -->
    <div class="row g-3 mb-4">

        <div class="col-lg-3 col-md-6">
            <div class="card border-0 border-top border-primary border-3 shadow-sm h-100">
                <div class="card-body text-center py-4">
                    <div class="fs-2 fw-bold text-primary"><?= $total_clientes ?></div>
                    <div class="text-muted small">Clientes cadastrados</div>
                </div>
                <a href="<?= base_url('clientes') ?>"
                   class="card-footer text-center text-decoration-none text-primary bg-light small py-2">
                    Ver detalhes <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card border-0 border-top border-info border-3 shadow-sm h-100">
                <div class="card-body text-center py-4">
                    <div class="fs-2 fw-bold text-info"><?= $total_produtos ?></div>
                    <div class="text-muted small">Produtos em estoque</div>
                </div>
                <a href="<?= base_url('produtos') ?>"
                   class="card-footer text-center text-decoration-none text-info bg-light small py-2">
                    Ver detalhes <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card border-0 border-top border-success border-3 shadow-sm h-100">
                <div class="card-body text-center py-4">
                    <div class="fs-2 fw-bold text-success"><?= $total_vendas ?></div>
                    <div class="text-muted small">Vendas realizadas</div>
                </div>
                <a href="<?= base_url('vendas') ?>"
                   class="card-footer text-center text-decoration-none text-success bg-light small py-2">
                    Ver detalhes <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card border-0 border-top border-warning border-3 shadow-sm h-100">
                <div class="card-body text-center py-4">
                    <div class="fw-semibold">
                        <div>R$ <?= number_format($total_a_receber, 2, ',', '.') ?> a receber</div>
                        <div class="text-danger">R$ <?= number_format($total_a_pagar, 2, ',', '.') ?> a pagar</div>
                    </div>
                </div>
                <a href="<?= base_url('contas_receber') ?>"
                   class="card-footer text-center text-decoration-none text-warning bg-light small py-2">
                    Ver financeiro <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

    </div>

    <!-- Últimas vendas -->
    <div class="row">
        <div class="col-12">
            <h5 class="mb-3">Últimas vendas</h5>
            <?php if (empty($ultimas_vendas)) : ?>
                <p class="text-muted">
                    Nenhuma venda registrada ainda.
                    <a href="<?= base_url('vendas/add') ?>">Registrar a primeira venda</a>.
                </p>
            <?php else : ?>
                <div class="card shadow-sm border-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Cliente</th>
                                    <th>Data</th>
                                    <th>Status</th>
                                    <th>Total (R$)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ultimas_vendas as $venda) : ?>
                                    <tr>
                                        <td>
                                            <a href="<?= base_url('vendas/ver/' . $venda->id) ?>">#<?= $venda->id ?></a>
                                        </td>
                                        <td><?= $venda->cliente_nome ?: '-' ?></td>
                                        <td><?= data_br($venda->data_venda) ?></td>
                                        <td><?= status_badge($venda->status) ?></td>
                                        <td><?= number_format($venda->total, 2, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif ?>
        </div>
    </div>

</div>
