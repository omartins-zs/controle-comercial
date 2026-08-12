<!-- View genérica de formulário (novo/editar). Variáveis: $titulo, $item, $campos_form, $base_route -->

<div class="container-fluid">

    <div class="row mb-2">
        <div class="col-12">
            <h1 class="h3 fw-light text-secondary mb-0"><?= $titulo ?></h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mt-1 mb-0">
                    <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url($base_route) ?>">Voltar para a listagem</a></li>
                    <li class="breadcrumb-item active"><?= $titulo ?></li>
                </ol>
            </nav>
            <hr class="mt-2">
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <?php erros_validacao() ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <form action="" method="post">
                        <?php foreach ($campos_form as $campo) :
                            $valor_atual = (isset($item) && isset($item->{$campo['key']})) ? $item->{$campo['key']} : null;
                            $valor = set_value($campo['key'], (string) $valor_atual);
                        ?>
                            <div class="mb-3">
                                <label class="form-label fw-semibold"><?= $campo['label'] ?></label>

                                <?php if ($campo['type'] === 'select') : ?>
                                    <select name="<?= $campo['key'] ?>" class="form-select">
                                        <option value="">Selecione...</option>
                                        <?php foreach ($campo['options'] as $opt) : ?>
                                            <option value="<?= $opt->id ?>" <?= ((string) $valor === (string) $opt->id) ? 'selected' : '' ?>>
                                                <?= $opt->nome ?>
                                            </option>
                                        <?php endforeach ?>
                                    </select>

                                <?php elseif ($campo['type'] === 'textarea') : ?>
                                    <textarea name="<?= $campo['key'] ?>" class="form-control" rows="3"><?= $valor ?></textarea>

                                <?php elseif ($campo['type'] === 'checkbox') : ?>
                                    <div class="form-check">
                                        <input type="checkbox"
                                               name="<?= $campo['key'] ?>"
                                               value="1"
                                               class="form-check-input"
                                               id="chk_<?= $campo['key'] ?>"
                                               <?= ($valor_atual === null || (int) $valor_atual === 1) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="chk_<?= $campo['key'] ?>">Ativo</label>
                                    </div>

                                <?php else : ?>
                                    <input type="<?= $campo['type'] ?>"
                                           step="<?= $campo['type'] === 'number' ? '0.01' : '' ?>"
                                           class="form-control"
                                           name="<?= $campo['key'] ?>"
                                           value="<?= $valor ?>"
                                           placeholder="<?= $campo['label'] ?>">
                                <?php endif ?>
                            </div>
                        <?php endforeach ?>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fa fa-check me-1"></i> Salvar
                            </button>
                            <a href="<?= base_url($base_route) ?>" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
