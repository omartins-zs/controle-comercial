<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Setar a FLASHDATA
function setar_msg($id, $msg, $tipo)
{
    if ($id) {
        $CI = &get_instance();
        switch ($tipo) {
            case 'erro':
                $CI->session->set_flashdata($id,
                    '<div class="alert alert-danger alert-dismissible fade show" role="alert">'
                    . $msg
                    . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>'
                    . '</div>');
                break;

            case 'sucesso':
                $CI->session->set_flashdata($id,
                    '<div class="alert alert-success alert-dismissible fade show" role="alert">'
                    . $msg
                    . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>'
                    . '</div>');
                break;
        }
    }
    return FALSE;
}

// Exibir a FLASHDATA
function get_msg($id, $printar = TRUE)
{
    $CI = &get_instance();
    if ($CI->session->flashdata($id)) {
        if ($printar) {
            echo $CI->session->flashdata($id);
            return TRUE;
        }
    }
    // return FALSE;
}

// Exibir a FLASHDATA

function erros_validacao()
{
    if (validation_errors()) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">'
            . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>';
        echo validation_errors('<p class="mb-0">', '</p>');
        echo '</div>';
    }
}

/**
 * URL de um asset (assets/...) com cache-busting baseado na data de
 * modificacao do arquivo (?v=<timestamp>) - o navegador so busca de novo
 * quando o arquivo realmente muda, diferente de usar date()/time() (que
 * forca redownload em toda requisicao, mesmo sem alteracao nenhuma).
 * Sem isso, depois de um `docker compose up --build`, o navegador do
 * usuario podia continuar servindo JS/CSS antigo do cache por dias -
 * exatamente o que aconteceu com assets/js/main.js nesta sessao.
 */
function asset_url($caminho_relativo)
{
    $caminho_absoluto = FCPATH . $caminho_relativo;
    $versao = is_file($caminho_absoluto) ? filemtime($caminho_absoluto) : time();
    return base_url($caminho_relativo) . "?v=" . $versao;
}

/**
 * Data no padrão brasileiro (dd/mm/aaaa). Recebe o que vem do MySQL
 * (Y-m-d ou Y-m-d H:i:s) e devolve pronto pra exibir; string vazia/nula
 * vira o placeholder, pra não imprimir "01/01/1970" em campo não preenchido.
 */
function data_br($data, $vazio = "-")
{
    if (empty($data) || $data === "0000-00-00" || $data === "0000-00-00 00:00:00") {
        return $vazio;
    }
    $ts = strtotime($data);
    return $ts ? date("d/m/Y", $ts) : $vazio;
}

/** Data e hora no padrão brasileiro (dd/mm/aaaa HH:MM). */
function data_hora_br($data, $vazio = "-")
{
    if (empty($data) || $data === "0000-00-00 00:00:00") {
        return $vazio;
    }
    $ts = strtotime($data);
    return $ts ? date("d/m/Y H:i", $ts) : $vazio;
}

/**
 * Rótulo amigável de um status guardado no banco em snake_case minúsculo
 * ("em_andamento" -> "Em andamento"). Centralizado aqui pra listagem,
 * detalhe e recibo mostrarem sempre o mesmo texto.
 */
function status_label($status)
{
    if ($status === null || $status === "") {
        return "-";
    }
    $mapa = array(
        "aberta" => "Aberta",
        "em_andamento" => "Em andamento",
        "concluida" => "Concluída",
        "cancelada" => "Cancelada",
        "orcamento" => "Orçamento",
        "pendente" => "Pendente",
        "pago" => "Pago",
        "recebido" => "Recebido",
        "entrada" => "Entrada",
        "saida" => "Saída",
        "ajuste" => "Ajuste",
    );
    if (isset($mapa[$status])) {
        return $mapa[$status];
    }
    return ucfirst(str_replace("_", " ", $status));
}

/**
 * Mesmo rótulo do status_label(), porém embrulhado num badge colorido do
 * Bootstrap (verde = finalizado, amarelo = em aberto, vermelho = cancelado)
 * pra dar leitura visual rápida nas listagens.
 */
function status_badge($status)
{
    // Bootstrap 5: bg-warning e bg-info precisam de text-dark (fundo claro).
    $classes = array(
        "concluida"    => "bg-success text-white",
        "pago"         => "bg-success text-white",
        "recebido"     => "bg-success text-white",
        "entrada"      => "bg-success text-white",
        "aberta"       => "bg-warning text-dark",
        "em_andamento" => "bg-info text-dark",
        "orcamento"    => "bg-info text-dark",
        "pendente"     => "bg-warning text-dark",
        "ajuste"       => "bg-warning text-dark",
        "cancelada"    => "bg-danger text-white",
        "saida"        => "bg-danger text-white",
    );
    $cls = isset($classes[$status]) ? $classes[$status] : "bg-secondary text-white";
    return "<span class=\"badge {$cls}\">" . status_label($status) . "</span>";
}

/**
 * Formata o valor de uma coluna de listagem conforme o nome da coluna:
 * datas (vencimento, data_*, *_em, created_at) saem em dd/mm/aaaa e status
 * vira badge colorido. Usado pelas views genericas (cadastro/index.php e
 * relatorios/index.php), que nao sabem de antemao quais colunas recebem.
 */
function formatar_celula($chave, $valor)
{
    if ($valor === null || $valor === "") {
        return "-";
    }

    if ($chave === "status" || $chave === "tipo") {
        return status_badge($valor);
    }

    if ($chave === "created_at" || $chave === "updated_at") {
        return data_hora_br($valor);
    }

    // vencimento, data_venda, data_abertura, pago_em, recebido_em, ...
    if ($chave === "vencimento"
        || strpos($chave, "data_") === 0
        || substr($chave, -3) === "_em") {
        return data_br($valor);
    }

    return html_escape($valor);
}
