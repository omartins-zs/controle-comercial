<div class="container-fluid">

    <div class="row mb-2">
        <div class="col-12">
            <h1 class="h3 fw-light text-secondary mb-0">Ordem de Serviço #<?= $os->id ?></h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mt-1 mb-0">
                    <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('ordens_servico') ?>">Ordens de Serviço</a></li>
                    <li class="breadcrumb-item active">OS #<?= $os->id ?></li>
                </ol>
            </nav>
            <hr class="mt-2">
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <?php get_msg('msgerro'); get_msg('msgsucess'); ?>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="small text-muted">Cliente</div>
                    <div class="fw-semibold"><?= $os->cliente_nome ?: '-' ?></div>
                </div>
                <div class="col-md-3">
                    <div class="small text-muted">Responsável</div>
                    <div class="fw-semibold"><?= $os->vendedor_nome ?: '-' ?></div>
                </div>
                <div class="col-md-2">
                    <div class="small text-muted">Abertura</div>
                    <div class="fw-semibold"><?= data_br($os->data_abertura) ?></div>
                </div>
                <div class="col-md-2">
                    <div class="small text-muted">Conclusão</div>
                    <div class="fw-semibold"><?= data_br($os->data_conclusao) ?></div>
                </div>
                <div class="col-md-2">
                    <div class="small text-muted">Status</div>
                    <div><?= status_badge($os->status) ?></div>
                </div>
            </div>
            <?php if ($os->descricao_problema) : ?>
                <hr class="my-3">
                <div>
                    <span class="small text-muted">Descrição:</span>
                    <span><?= html_escape($os->descricao_problema) ?></span>
                </div>
            <?php endif ?>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tipo</th>
                        <th>Descrição</th>
                        <th>Quantidade</th>
                        <th class="text-end">Valor unit. (R$)</th>
                        <th class="text-end">Subtotal (R$)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($itens as $item) : ?>
                        <tr>
                            <td><?= $item->tipo === 'peca' ? 'Peça' : 'Serviço' ?></td>
                            <td><?= $item->produto_nome ?: html_escape($item->descricao) ?></td>
                            <td><?= $item->quantidade ?></td>
                            <td class="text-end"><?= number_format($item->valor_unitario, 2, ',', '.') ?></td>
                            <td class="text-end"><?= number_format($item->subtotal, 2, ',', '.') ?></td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="4" class="text-end">Total</th>
                        <th class="text-end"><?= number_format($os->total, 2, ',', '.') ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Alterar status -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h6 class="fw-semibold mb-3">Alterar status</h6>
            <form action="" method="post" class="d-flex align-items-center gap-3 flex-wrap">
                <select name="novo_status" class="form-select" style="max-width:220px">
                    <option value="aberta"       <?= $os->status === 'aberta'       ? 'selected' : '' ?>>Aberta</option>
                    <option value="em_andamento" <?= $os->status === 'em_andamento' ? 'selected' : '' ?>>Em andamento</option>
                    <option value="concluida"    <?= $os->status === 'concluida'    ? 'selected' : '' ?>>Concluída</option>
                    <option value="cancelada"    <?= $os->status === 'cancelada'    ? 'selected' : '' ?>>Cancelada</option>
                </select>
                <button type="submit" class="btn btn-primary">Atualizar status</button>
            </form>
            <p class="text-muted small mt-2 mb-0">
                Mudar para "Concluída" baixa as peças usadas do estoque (uma única vez).
                Sair de "Concluída" devolve o estoque.
            </p>
        </div>
    </div>

    <div class="d-flex gap-2">
        <a href="<?= base_url('ordens_servico') ?>" class="btn btn-secondary">
            <i class="fa fa-arrow-left me-1"></i> Voltar
        </a>
        <a href="<?= base_url('ordens_servico/recibo/' . $os->id) ?>"
           class="btn btn-info"
           target="_blank">
            <i class="fa fa-print me-1"></i> Emitir comprovante
        </a>
    </div>

</div>
