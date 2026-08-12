<?php
defined('BASEPATH') or exit('No direct script access allowed');

class OrdemServico_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Estoque_model');
    }

    public function get_all()
    {
        return $this->db
            ->select('ordens_servico.*, clientes.nome AS cliente_nome, vendedores.nome AS vendedor_nome')
            ->from('ordens_servico')
            ->join('clientes', 'clientes.id = ordens_servico.cliente_id', 'left')
            ->join('vendedores', 'vendedores.id = ordens_servico.vendedor_id', 'left')
            ->order_by('ordens_servico.data_abertura', 'DESC')
            ->get()->result();
    }

    public function get($id)
    {
        return $this->db
            ->select('ordens_servico.*, clientes.nome AS cliente_nome, vendedores.nome AS vendedor_nome')
            ->from('ordens_servico')
            ->join('clientes', 'clientes.id = ordens_servico.cliente_id', 'left')
            ->join('vendedores', 'vendedores.id = ordens_servico.vendedor_id', 'left')
            ->where('ordens_servico.id', $id)
            ->get()->row();
    }

    public function get_itens($ordem_id)
    {
        return $this->db
            ->select('ordem_servico_itens.*, produtos.nome AS produto_nome')
            ->from('ordem_servico_itens')
            ->join('produtos', 'produtos.id = ordem_servico_itens.produto_id', 'left')
            ->where('ordem_servico_itens.ordem_id', $ordem_id)
            ->get()->result();
    }

    /**
     * Cria a OS e seus itens numa transação. Itens do tipo "peca" têm o preço
     * sempre lido da tabela produtos (nunca confia no formulário); itens do
     * tipo "servico" usam a descrição/valor informados livremente, pois não
     * existe um "cadastro de serviços" nesta versão do sistema.
     *
     * @param array $os_data  ['cliente_id', 'vendedor_id', 'descricao_problema', 'data_abertura', 'status']
     * @param array $itens    lista de ['tipo' => 'servico'|'peca', 'produto_id'?, 'descricao'?, 'quantidade', 'valor_unitario'?]
     * @return int|false
     */
    public function criar($os_data, $itens)
    {
        $itens = array_values(array_filter($itens, function ($item) {
            return (int) $item['quantidade'] > 0 && ($item['tipo'] === 'peca' ? !empty($item['produto_id']) : !empty($item['descricao']));
        }));

        if (empty($itens)) {
            return false;
        }

        $this->db->trans_begin();

        $os_data['total'] = 0;
        $os_data['estoque_baixado'] = ($os_data['status'] === 'concluida') ? 1 : 0;
        $this->db->insert('ordens_servico', $os_data);
        $ordem_id = $this->db->insert_id();

        $total = 0;
        foreach ($itens as $item) {
            $quantidade = (int) $item['quantidade'];

            if ($item['tipo'] === 'peca') {
                $produto = $this->db->where('id', $item['produto_id'])->get('produtos')->row();
                if (!$produto) {
                    continue;
                }
                $descricao = $produto->nome;
                $valor_unitario = $produto->preco;
                $produto_id = $produto->id;
            } else {
                $descricao = $item['descricao'];
                $valor_unitario = (float) $item['valor_unitario'];
                $produto_id = null;
            }

            $subtotal = $valor_unitario * $quantidade;
            $total += $subtotal;

            $this->db->insert('ordem_servico_itens', array(
                'ordem_id' => $ordem_id,
                'tipo' => $item['tipo'],
                'produto_id' => $produto_id,
                'descricao' => $descricao,
                'quantidade' => $quantidade,
                'valor_unitario' => $valor_unitario,
                'subtotal' => $subtotal,
            ));

            if ($produto_id && $os_data['estoque_baixado']) {
                $this->Estoque_model->ajustar($produto_id, -$quantidade, 'saida', 'Ordem de Serviço #' . $ordem_id . ' - peça', 'ordem_servico', $ordem_id);
            }
        }

        $this->db->where('id', $ordem_id)->update('ordens_servico', array('total' => $total));

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return false;
        }

        $this->db->trans_commit();
        return $ordem_id;
    }

    /**
     * Muda o status da OS. Se estiver entrando em "concluida" e ainda não
     * tiver baixado estoque, baixa agora (peças usadas saem do estoque só
     * quando o serviço é de fato concluído — não na abertura). Se estiver
     * saindo de "concluida" para outro status (ex.: reaberta por engano),
     * devolve o estoque.
     */
    public function mudar_status($id, $novo_status)
    {
        $os = $this->get($id);
        if (!$os) {
            return false;
        }

        $this->db->trans_begin();

        $data = array('status' => $novo_status);
        if ($novo_status === 'concluida') {
            $data['data_conclusao'] = date('Y-m-d');
        }

        if ($novo_status === 'concluida' && !$os->estoque_baixado) {
            foreach ($this->get_itens($id) as $item) {
                if ($item->produto_id) {
                    $this->Estoque_model->ajustar($item->produto_id, -(int) $item->quantidade, 'saida', 'Ordem de Serviço #' . $id . ' - peça', 'ordem_servico', $id);
                }
            }
            $data['estoque_baixado'] = 1;
        } elseif ($novo_status !== 'concluida' && $os->estoque_baixado) {
            foreach ($this->get_itens($id) as $item) {
                if ($item->produto_id) {
                    $this->Estoque_model->ajustar($item->produto_id, (int) $item->quantidade, 'entrada', 'Reabertura da Ordem de Serviço #' . $id, 'ordem_servico', $id);
                }
            }
            $data['estoque_baixado'] = 0;
        }

        $this->db->where('id', $id)->update('ordens_servico', $data);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return false;
        }

        $this->db->trans_commit();
        return true;
    }

    public function apagar($id)
    {
        $os = $this->get($id);

        if ($os && $os->estoque_baixado) {
            foreach ($this->get_itens($id) as $item) {
                if ($item->produto_id) {
                    $this->Estoque_model->ajustar($item->produto_id, (int) $item->quantidade, 'entrada', 'Exclusão da Ordem de Serviço #' . $id, 'ordem_servico', $id);
                }
            }
        }

        // ordem_servico_itens tem FK ON DELETE CASCADE para ordens_servico.
        return $this->db->where('id', $id)->delete('ordens_servico');
    }
}
