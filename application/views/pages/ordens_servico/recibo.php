<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Comprovante de OS #<?= $os->id ?></title>
    <style>
        body { font-family: Arial, sans-serif; color: #222; max-width: 700px; margin: 30px auto; padding: 0 15px; }
        .cabecalho { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 15px; }
        .cabecalho h1 { margin: 0; font-size: 20px; }
        .aviso-nao-fiscal { background: #fff3cd; border: 1px solid #d4a72c; padding: 8px 12px; font-size: 12px; margin-bottom: 15px; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; font-size: 13px; text-align: left; }
        th { background: #f0f0f0; }
        .text-right { text-align: right; }
        .total-row td { font-weight: bold; font-size: 14px; }
        .rodape { margin-top: 25px; font-size: 11px; color: #666; text-align: center; }
        .btn-imprimir { margin: 15px 0; }
        @media print { .btn-imprimir { display: none; } }
    </style>
</head>
<body>

    <div class="btn-imprimir">
        <button onclick="window.print()">🖨️ Imprimir / Salvar PDF</button>
    </div>

    <div class="cabecalho">
        <h1><?= htmlspecialchars($config->site_nome ?? 'Sistema Comercial') ?></h1>
        <div>Comprovante de Ordem de Serviço #<?= $os->id ?></div>
    </div>

    <div class="aviso-nao-fiscal">
        ⚠️ Este documento é um <strong>comprovante interno de serviço</strong> e <strong>não possui valor fiscal</strong>.
        Não substitui Nota Fiscal Eletrônica (NF-e) de serviço.
    </div>

    <p>
        <strong>Cliente:</strong> <?= htmlspecialchars($os->cliente_nome ?: '-') ?><br>
        <strong>Responsável:</strong> <?= htmlspecialchars($os->vendedor_nome ?: '-') ?><br>
        <strong>Abertura:</strong> <?= data_br($os->data_abertura) ?> &nbsp; <strong>Conclusão:</strong> <?= data_br($os->data_conclusao) ?><br>
        <strong>Status:</strong> <?= status_label($os->status) ?>
        <?php if (!empty($os->descricao_problema)) : ?>
            <br><strong>Descrição:</strong> <?= htmlspecialchars($os->descricao_problema) ?>
        <?php endif ?>
    </p>

    <table>
        <thead>
            <tr>
                <th>Tipo</th>
                <th>Descrição</th>
                <th>Qtd.</th>
                <th class="text-right">Valor unit. (R$)</th>
                <th class="text-right">Subtotal (R$)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($itens as $item) : ?>
                <tr>
                    <td><?= $item->tipo === 'peca' ? 'Peça' : 'Serviço' ?></td>
                    <td><?= htmlspecialchars($item->produto_nome ?: $item->descricao) ?></td>
                    <td><?= $item->quantidade ?></td>
                    <td class="text-right"><?= number_format($item->valor_unitario, 2, ',', '.') ?></td>
                    <td class="text-right"><?= number_format($item->subtotal, 2, ',', '.') ?></td>
                </tr>
            <?php endforeach ?>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4" class="text-right">TOTAL</td>
                <td class="text-right">R$ <?= number_format($os->total, 2, ',', '.') ?></td>
            </tr>
        </tfoot>
    </table>

    <div class="rodape">
        Documento gerado em <?= date('d/m/Y H:i') ?> — <?= htmlspecialchars($config->site_nome ?? 'Sistema Comercial') ?>
        <?= !empty($config->admin_email) ? ' — ' . htmlspecialchars($config->admin_email) : '' ?>
    </div>

</body>
</html>
