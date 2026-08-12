<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Centraliza toda alteração de estoque + o registro do histórico
 * (estoque_movimentos). Usado por Venda_model, OrdemServico_model e pelo
 * ajuste manual em Produtos::movimentar() — assim toda baixa/entrada de
 * estoque do sistema, seja de onde vier, sempre deixa rastro no histórico.
 */
class Estoque_model extends CI_Model
{
    /**
     * @param int    $produto_id
     * @param int    $delta            positivo = entrada, negativo = saída
     * @param string $tipo             'entrada' | 'saida' | 'ajuste'
     * @param string $motivo
     * @param string $referencia_tipo  'venda' | 'ordem_servico' | 'manual' | null
     * @param int    $referencia_id
     * @return int|false  novo saldo em estoque, ou false se o produto não existe
     */
    public function ajustar($produto_id, $delta, $tipo, $motivo, $referencia_tipo = null, $referencia_id = null)
    {
        $produto = $this->db->where('id', $produto_id)->get('produtos')->row();
        if (!$produto) {
            return false;
        }

        // GREATEST(0, ...) evita saldo negativo mesmo em saídas maiores que o
        // disponível (não bloqueia a operação — só não deixa o campo negativo).
        $this->db->set('estoque_qtd', 'GREATEST(0, estoque_qtd + (' . (int) $delta . '))', FALSE)
            ->where('id', $produto_id)
            ->update('produtos');

        $novo_saldo = (int) $this->db->select('estoque_qtd')->where('id', $produto_id)->get('produtos')->row()->estoque_qtd;

        $this->db->insert('estoque_movimentos', array(
            'produto_id' => $produto_id,
            'tipo' => $tipo,
            'quantidade' => abs($delta),
            'motivo' => $motivo,
            'referencia_tipo' => $referencia_tipo,
            'referencia_id' => $referencia_id,
            'estoque_resultante' => $novo_saldo,
        ));

        return $novo_saldo;
    }

    public function historico($produto_id = null, $limit = 200)
    {
        $this->db
            ->select('estoque_movimentos.*, produtos.nome AS produto_nome')
            ->from('estoque_movimentos')
            ->join('produtos', 'produtos.id = estoque_movimentos.produto_id', 'left')
            ->order_by('estoque_movimentos.created_at', 'DESC')
            ->order_by('estoque_movimentos.id', 'DESC')
            ->limit($limit);

        if ($produto_id) {
            $this->db->where('estoque_movimentos.produto_id', $produto_id);
        }

        return $this->db->get()->result();
    }
}
