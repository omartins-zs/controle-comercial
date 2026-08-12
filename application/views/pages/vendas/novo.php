<div class="container-fluid">

    <div class="row mb-2">
        <div class="col-12">
            <h1 class="h3 fw-light text-secondary mb-0">Nova venda</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mt-1 mb-0">
                    <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('vendas') ?>">Vendas</a></li>
                    <li class="breadcrumb-item active">Nova venda</li>
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

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form action="" method="post" id="form_venda">
                <div class="row g-3">
                    <div class="col-lg-4">
                        <label class="form-label fw-semibold">Cliente <span class="text-danger">*</span></label>
                        <select name="cliente_id" class="form-select" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($clientes as $cliente) : ?>
                                <option value="<?= $cliente->id ?>"
                                        <?= (set_value('cliente_id') == $cliente->id) ? 'selected' : '' ?>>
                                    <?= $cliente->nome ?>
                                </option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <div class="col-lg-4">
                        <label class="form-label fw-semibold">Vendedor</label>
                        <select name="vendedor_id" class="form-select">
                            <option value="">Selecione...</option>
                            <?php foreach ($vendedores as $vendedor) : ?>
                                <option value="<?= $vendedor->id ?>"
                                        <?= (set_value('vendedor_id') == $vendedor->id) ? 'selected' : '' ?>>
                                    <?= $vendedor->nome ?>
                                </option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label fw-semibold">Data <span class="text-danger">*</span></label>
                        <input type="date" name="data_venda" class="form-control"
                               value="<?= set_value('data_venda', date('Y-m-d')) ?>" required>
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            <option value="concluida">Concluída</option>
                            <option value="orcamento">Orçamento</option>
                            <option value="cancelada">Cancelada</option>
                        </select>
                    </div>
                </div>

                <hr class="my-4">
                <h5 class="mb-3">Itens da venda</h5>

                <div class="table-responsive">
                    <table class="table table-bordered" id="tabela-itens">
                        <thead class="table-light">
                            <tr>
                                <th style="width:55%">Produto</th>
                                <th style="width:20%">Quantidade</th>
                                <th style="width:15%">Preço unit. (R$)</th>
                                <th style="width:10%"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="linha-item">
                                <td>
                                    <select name="produto_id[]" class="form-select select-produto" required>
                                        <option value="">Selecione...</option>
                                        <?php foreach ($produtos as $produto) : ?>
                                            <option value="<?= $produto->id ?>"
                                                    data-preco="<?= $produto->preco ?>">
                                                <?= $produto->nome ?> (estoque: <?= $produto->estoque_qtd ?>)
                                            </option>
                                        <?php endforeach ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="quantidade[]" class="form-control" value="1" min="1" required>
                                </td>
                                <td>
                                    <span class="preco-unit text-muted">-</span>
                                </td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <button type="button" class="btn btn-secondary mb-4" id="btn-add-item">
                    <i class="fa fa-plus me-1"></i> Adicionar item
                </button>

                <hr>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-check me-1"></i> Salvar venda
                    </button>
                    <a href="<?= base_url('vendas') ?>" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    // jQuery só carrega no footer.php, após esta view — usa 'load' para garantir.
    window.addEventListener('load', function () {
        function atualizarPreco(select) {
            var $select = $(select);
            var preco = $select.find(':selected').data('preco');
            $select.closest('tr').find('.preco-unit')
                .text(preco ? 'R$ ' + parseFloat(preco).toFixed(2).replace('.', ',') : '-');
        }

        $(document).on('change', '.select-produto', function () { atualizarPreco(this); });

        $('#btn-add-item').on('click', function () {
            var $linha = $('.linha-item').first().clone();
            $linha.find('select').val('');
            $linha.find('input[type=number]').val(1);
            $linha.find('.preco-unit').text('-');
            $linha.find('td').last().html(
                '<button type="button" class="btn btn-sm btn-danger btn-remover-item">' +
                '<i class="fa fa-trash"></i></button>'
            );
            $('#tabela-itens tbody').append($linha);
        });

        $(document).on('click', '.btn-remover-item', function () {
            if ($('.linha-item').length > 1) $(this).closest('tr').remove();
        });
    });
</script>
