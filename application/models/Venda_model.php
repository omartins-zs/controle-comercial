<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Venda_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Estoque_model');
    }

    public function get_all()
    {
        return $this->db
            ->select('vendas.*, clientes.nome AS cliente_nome, vendedores.nome AS vendedor_nome')
            ->from('vendas')
            ->join('clientes', 'clientes.id = vendas.cliente_id', 'left')
            ->join('vendedores', 'vendedores.id = vendas.vendedor_id', 'left')
            ->order_by('vendas.data_venda', 'DESC')
            ->get()->result();
    }

    public function get($id)
    {
        return $this->db
            ->select('vendas.*, clientes.nome AS cliente_nome, vendedores.nome AS vendedor_nome')
            ->from('vendas')
            ->join('clientes', 'clientes.id = vendas.cliente_id', 'left')
            ->join('vendedores', 'vendedores.id = vendas.vendedor_id', 'left')
            ->where('vendas.id', $id)
            ->get()->row();
    }

    public function get_itens($venda_id)
    {
        return $this->db
            ->select('venda_itens.*, produtos.nome AS produto_nome')
            ->from('venda_itens')
            ->join('produtos', 'produtos.id = venda_itens.produto_id', 'left')
            ->where('venda_itens.venda_id', $venda_id)
            ->get()->result();
    }

    /**
     * Cria a venda e seus itens numa transação. Os preços são sempre lidos do
     * banco (tabela produtos) no momento da gravação — nunca confia em preço
     * vindo do formulário, para não permitir manipulação de valor pelo cliente.
     *
     * @param array $venda_data  ['cliente_id', 'vendedor_id', 'data_venda', 'status']
     * @param array $itens       lista de ['produto_id' => int, 'quantidade' => int]
     * @return int|false  id da venda criada, ou false em caso de erro
     */
    public function criar($venda_data, $itens)
    {
        $itens = array_values(array_filter($itens, function ($item) {
            return !empty($item['produto_id']) && (int) $item['quantidade'] > 0;
        }));

        if (empty($itens)) {
            return false;
        }

        $this->db->trans_begin();

        $venda_data['total'] = 0;
        $this->db->insert('vendas', $venda_data);
        $venda_id = $this->db->insert_id();

        // Só baixa estoque em venda efetivamente concluída — um "orçamento"
        // não deve reservar/consumir produto, e "cancelada" também não.
        $baixar_estoque = (isset($venda_data['status']) && $venda_data['status'] === 'concluida');

        $total = 0;
        foreach ($itens as $item) {
            $produto = $this->db->where('id', $item['produto_id'])->get('produtos')->row();
            if (!$produto) {
                continue;
            }
            $quantidade = (int) $item['quantidade'];
            $subtotal = $produto->preco * $quantidade;
            $total += $subtotal;

            $this->db->insert('venda_itens', array(
                'venda_id' => $venda_id,
                'produto_id' => $produto->id,
                'quantidade' => $quantidade,
                'preco_unitario' => $produto->preco,
                'subtotal' => $subtotal,
            ));

            if ($baixar_estoque) {
                $this->Estoque_model->ajustar($produto->id, -$quantidade, 'saida', 'Venda #' . $venda_id, 'venda', $venda_id);
            }
        }

        $this->db->where('id', $venda_id)->update('vendas', array('total' => $total));

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return false;
        }

        $this->db->trans_commit();
        return $venda_id;
    }

    public function apagar($id)
    {
        $venda = $this->get($id);

        // Se a venda estava concluída (baixou estoque na criação), devolve a
        // quantidade ao apagar — senão o estoque fica permanentemente errado.
        if ($venda && $venda->status === 'concluida') {
            foreach ($this->get_itens($id) as $item) {
                if ($item->produto_id) {
                    $this->Estoque_model->ajustar($item->produto_id, (int) $item->quantidade, 'entrada', 'Estorno da Venda #' . $id, 'venda', $id);
                }
            }
        }

        // venda_itens tem FK ON DELETE CASCADE para vendas, então apagar a
        // venda já remove os itens automaticamente.
        return $this->db->where('id', $id)->delete('vendas');
    }
}
