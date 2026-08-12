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

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover table-bordered mb-0" id="datatable">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <?php foreach ($campos as $campo) : ?>
                            <th><?= $campo['label'] ?></th>
                        <?php endforeach ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item) : ?>
                        <tr>
                            <td><?= $item->id ?></td>
                            <?php foreach ($campos as $campo) : ?>
                                <td><?= isset($item->{$campo['key']}) ? formatar_celula($campo['key'], $item->{$campo['key']}) : '' ?></td>
                            <?php endforeach ?>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
