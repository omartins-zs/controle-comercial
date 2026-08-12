<div class="container-fluid">

    <div class="row mb-2">
        <div class="col-12">
            <h1 class="h3 fw-light text-secondary mb-0">Venda #<?= $venda->id ?></h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mt-1 mb-0">
                    <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('vendas') ?>">Vendas</a></li>
                    <li class="breadcrumb-item active">Venda #<?= $venda->id ?></li>
                </ol>
            </nav>
            <hr class="mt-2">
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="small text-muted">Cliente</div>
                    <div class="fw-semibold"><?= $venda->cliente_nome ?: '-' ?></div>
                </div>
                <div class="col-md-3">
                    <div class="small text-muted">Vendedor</div>
                    <div class="fw-semibold"><?= $venda->vendedor_nome ?: '-' ?></div>
                </div>
                <div class="col-md-3">
                    <div class="small text-muted">Data</div>
                    <div class="fw-semibold"><?= data_br($venda->data_venda) ?></div>
                </div>
                <div class="col-md-3">
                    <div class="small text-muted">Status</div>
                    <div><?= status_badge($venda->status) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Produto</th>
                        <th>Quantidade</th>
                        <th class="text-end">Preço unit. (R$)</th>
                        <th class="text-end">Subtotal (R$)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($itens as $item) : ?>
                        <tr>
                            <td><?= $item->produto_nome ?: '(produto removido)' ?></td>
                            <td><?= $item->quantidade ?></td>
                            <td class="text-end"><?= number_format($item->preco_unitario, 2, ',', '.') ?></td>
                            <td class="text-end"><?= number_format($item->subtotal, 2, ',', '.') ?></td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="3" class="text-end">Total</th>
                        <th class="text-end"><?= number_format($venda->total, 2, ',', '.') ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="d-flex gap-2">
        <a href="<?= base_url('vendas') ?>" class="btn btn-secondary">
            <i class="fa fa-arrow-left me-1"></i> Voltar
        </a>
        <a href="<?= base_url('vendas/recibo/' . $venda->id) ?>"
           class="btn btn-info"
           target="_blank">
            <i class="fa fa-print me-1"></i> Emitir comprovante
        </a>
    </div>

</div>
