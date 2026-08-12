<div class="container-fluid">

    <div class="row mb-2">
        <div class="col-12">
            <h1 class="h3 fw-light text-secondary mb-0">Nova Ordem de Serviço</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mt-1 mb-0">
                    <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('ordens_servico') ?>">Ordens de Serviço</a></li>
                    <li class="breadcrumb-item active">Nova OS</li>
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
            <form action="" method="post" id="form_os">
                <div class="row g-3 mb-3">
                    <div class="col-lg-4">
                        <label class="form-label fw-semibold">Cliente <span class="text-danger">*</span></label>
                        <select name="cliente_id" class="form-select" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($clientes as $cliente) : ?>
                                <option value="<?= $cliente->id ?>"><?= $cliente->nome ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <div class="col-lg-3">
                        <label class="form-label fw-semibold">Responsável</label>
                        <select name="vendedor_id" class="form-select">
                            <option value="">Selecione...</option>
                            <?php foreach ($vendedores as $vendedor) : ?>
                                <option value="<?= $vendedor->id ?>"><?= $vendedor->nome ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label fw-semibold">Abertura <span class="text-danger">*</span></label>
                        <input type="date" name="data_abertura" class="form-control"
                               value="<?= set_value('data_abertura', date('Y-m-d')) ?>" required>
                    </div>
                    <div class="col-lg-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            <option value="aberta">Aberta</option>
                            <option value="em_andamento">Em andamento</option>
                            <option value="concluida">Concluída (já baixa peças do estoque)</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Descrição do problema / solicitação</label>
                    <textarea name="descricao_problema" class="form-control" rows="2"><?= set_value('descricao_problema') ?></textarea>
                </div>

                <hr class="my-4">
                <h5 class="mb-3">Itens (serviços e/ou peças)</h5>

                <div class="table-responsive">
                    <table class="table table-bordered" id="tabela-itens-os">
                        <thead class="table-light">
                            <tr>
                                <th style="width:15%">Tipo</th>
                                <th style="width:30%">Descrição / Peça</th>
                                <th style="width:15%">Qtd.</th>
                                <th style="width:20%">Valor unit. (R$)</th>
                                <th style="width:10%"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="linha-item-os">
                                <td>
                                    <select name="tipo_item[]" class="form-select select-tipo-item">
                                        <option value="servico">Serviço</option>
                                        <option value="peca">Peça</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="descricao_item[]"
                                           class="form-control campo-servico"
                                           placeholder="Descrição do serviço">
                                    <select name="produto_id[]"
                                            class="form-select campo-peca select-produto-os"
                                            style="display:none">
                                        <option value="">Selecione a peça...</option>
                                        <?php foreach ($produtos as $produto) : ?>
                                            <option value="<?= $produto->id ?>"
                                                    data-preco="<?= $produto->preco ?>">
                                                <?= $produto->nome ?> (estoque: <?= $produto->estoque_qtd ?>)
                                            </option>
                                        <?php endforeach ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="quantidade[]" class="form-control" value="1" min="1">
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="valor_unitario[]"
                                           class="form-control campo-valor" value="0">
                                </td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <button type="button" class="btn btn-secondary mb-4" id="btn-add-item-os">
                    <i class="fa fa-plus me-1"></i> Adicionar item
                </button>

                <hr>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-check me-1"></i> Salvar Ordem de Serviço
                    </button>
                    <a href="<?= base_url('ordens_servico') ?>" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    // jQuery só carrega no footer.php — usa 'load' para garantir que $ existe.
    window.addEventListener('load', function () {
        function alternarCampos($linha) {
            var tipo = $linha.find('.select-tipo-item').val();
            if (tipo === 'peca') {
                $linha.find('.campo-servico').hide().prop('required', false);
                $linha.find('.campo-peca').show();
                $linha.find('.campo-valor').prop('readonly', true);
            } else {
                $linha.find('.campo-servico').show();
                $linha.find('.campo-peca').hide();
                $linha.find('.campo-valor').prop('readonly', false);
            }
        }

        $(document).on('change', '.select-tipo-item', function () {
            alternarCampos($(this).closest('tr'));
        });

        $(document).on('change', '.select-produto-os', function () {
            var preco = $(this).find(':selected').data('preco');
            if (preco !== undefined) {
                $(this).closest('tr').find('.campo-valor').val(parseFloat(preco).toFixed(2));
            }
        });

        $('#btn-add-item-os').on('click', function () {
            var $linha = $('.linha-item-os').first().clone();
            $linha.find('select.select-tipo-item').val('servico');
            $linha.find('input[type=text]').val('');
            $linha.find('select.select-produto-os').val('');
            $linha.find('input[name="quantidade[]"]').val(1);
            $linha.find('.campo-valor').val(0);
            $linha.find('td').last().html(
                '<button type="button" class="btn btn-sm btn-danger btn-remover-item-os">' +
                '<i class="fa fa-trash"></i></button>'
            );
            $('#tabela-itens-os tbody').append($linha);
            alternarCampos($linha);
        });

        $(document).on('click', '.btn-remover-item-os', function () {
            if ($('.linha-item-os').length > 1) $(this).closest('tr').remove();
        });
    });
</script>
