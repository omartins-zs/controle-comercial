<div class="container-fluid">

    <div class="row mb-2">
        <div class="col-12">
            <h1 class="h3 fw-light text-secondary mb-0">Movimentar estoque</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mt-1 mb-0">
                    <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('produtos') ?>">Produtos</a></li>
                    <li class="breadcrumb-item active">Movimentar — <?= html_escape($produto->nome) ?></li>
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

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <p class="mb-3">
                        <strong>Produto:</strong> <?= html_escape($produto->nome) ?> &nbsp;
                        <span class="badge bg-secondary"><?= $produto->estoque_qtd ?> em estoque</span>
                    </p>

                    <form action="" method="post">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tipo</label>
                            <select name="tipo" class="form-select">
                                <option value="entrada">Entrada (compra, devolução, etc.)</option>
                                <option value="saida">Saída (perda, quebra, etc. — fora de venda/OS)</option>
                                <option value="ajuste">Ajuste (correção de contagem/inventário)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Quantidade</label>
                            <input type="number" name="quantidade" class="form-control"
                                   min="1" value="<?= set_value('quantidade', 1) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Motivo</label>
                            <input type="text" name="motivo" class="form-control"
                                   placeholder="Ex.: Compra do fornecedor X, nota 1234"
                                   value="<?= set_value('motivo') ?>">
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fa fa-check me-1"></i> Registrar movimentação
                            </button>
                            <a href="<?= base_url('produtos') ?>" class="btn btn-secondary">Voltar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <h5 class="mb-3">Últimas movimentações deste produto</h5>
            <div class="card shadow-sm border-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Data</th>
                                <th>Tipo</th>
                                <th>Quantidade</th>
                                <th>Motivo</th>
                                <th>Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($historico)) : ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        Nenhuma movimentação registrada ainda.
                                    </td>
                                </tr>
                            <?php else : ?>
                                <?php foreach ($historico as $mov) : ?>
                                    <tr>
                                        <td><?= data_hora_br($mov->created_at) ?></td>
                                        <td><?= status_badge($mov->tipo) ?></td>
                                        <td><?= ($mov->tipo === 'saida' ? '-' : '+') . $mov->quantidade ?></td>
                                        <td><?= html_escape($mov->motivo) ?></td>
                                        <td><?= $mov->estoque_resultante ?></td>
                                    </tr>
                                <?php endforeach ?>
                            <?php endif ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
