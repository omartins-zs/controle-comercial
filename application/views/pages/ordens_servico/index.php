<div class="container-fluid">

    <div class="row mb-2">
        <div class="col-12">
            <h1 class="h3 fw-light text-secondary mb-0">Ordens de Serviço</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mt-1 mb-0">
                    <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                    <li class="breadcrumb-item active">Ordens de Serviço</li>
                </ol>
            </nav>
            <hr class="mt-2">
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12 text-end">
            <a href="<?= base_url('ordens_servico/add') ?>" class="btn btn-success">
                <i class="fa fa-plus-square me-1"></i> Nova OS
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
                        <th>Responsável</th>
                        <th>Abertura</th>
                        <th>Status</th>
                        <th>Total (R$)</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ordens as $os) : ?>
                        <tr>
                            <td><?= $os->id ?></td>
                            <td><?= $os->cliente_nome ?: '-' ?></td>
                            <td><?= $os->vendedor_nome ?: '-' ?></td>
                            <td><?= data_br($os->data_abertura) ?></td>
                            <td><?= status_badge($os->status) ?></td>
                            <td><?= number_format($os->total, 2, ',', '.') ?></td>
                            <td class="text-end text-nowrap">
                                <a href="<?= base_url('ordens_servico/ver/' . $os->id) ?>"
                                   title="Ver detalhes"
                                   class="btn btn-sm btn-primary">
                                    <i class="fa fa-eye"></i>
                                </a>
                                <a href="<?= base_url('ordens_servico/apagar/' . $os->id) ?>"
                                   title="Apagar"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Tem certeza que deseja excluir esta ordem de serviço?')">
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
