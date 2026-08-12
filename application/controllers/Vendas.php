<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Vendas extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->ion_auth->logged_in()) {
            setar_msg('msgerro', 'Erro: Você precisa estar logado no sistema.', 'erro');
            redirect('login');
        }
        $this->load->model('Venda_model');
    }

    public function index()
    {
        $dados['titulo'] = 'Venda de produtos';
        $dados['vendas'] = $this->Venda_model->get_all();
        $this->load->vars($dados);

        $this->load->view('templates/header');
        $this->load->view('pages/vendas/index');
        $this->load->view('templates/footer');
    }

    public function add()
    {
        $this->form_validation->set_rules('cliente_id', 'Cliente', 'required');
        $this->form_validation->set_rules('data_venda', 'Data da venda', 'required');
        $this->form_validation->set_rules('produto_id[]', 'Produto', 'required');
        $this->form_validation->set_rules('quantidade[]', 'Quantidade', 'required|integer|greater_than[0]');

        if ($this->form_validation->run() == TRUE) {
            $venda_data = array(
                'cliente_id' => $this->input->post('cliente_id'),
                'vendedor_id' => $this->input->post('vendedor_id') ?: null,
                'data_venda' => $this->input->post('data_venda'),
                'status' => $this->input->post('status') ?: 'concluida',
            );

            $produtos_post = $this->input->post('produto_id');
            $quantidades_post = $this->input->post('quantidade');
            $itens = array();
            foreach ($produtos_post as $i => $produto_id) {
                $itens[] = array(
                    'produto_id' => $produto_id,
                    'quantidade' => isset($quantidades_post[$i]) ? $quantidades_post[$i] : 0,
                );
            }

            $venda_id = $this->Venda_model->criar($venda_data, $itens);
            if ($venda_id) {
                setar_msg('msgsucess', 'Venda registrada com sucesso.', 'sucesso');
                redirect('vendas', 'refresh');
            }

            setar_msg('msgerro', 'Não foi possível registrar a venda: selecione ao menos um produto com quantidade válida.', 'erro');
        }

        $dados['titulo'] = 'Nova venda';
        $dados['clientes'] = $this->db->select('id, nome')->from('clientes')->where('ativo', 1)->order_by('nome')->get()->result();
        $dados['vendedores'] = $this->db->select('id, nome')->from('vendedores')->where('ativo', 1)->order_by('nome')->get()->result();
        $dados['produtos'] = $this->db->select('id, nome, preco, estoque_qtd')->from('produtos')->where('ativo', 1)->order_by('nome')->get()->result();
        $this->load->vars($dados);

        $this->load->view('templates/header');
        $this->load->view('pages/vendas/novo');
        $this->load->view('templates/footer');
    }

    public function ver($id = null)
    {
        $venda = $id ? $this->Venda_model->get($id) : null;
        if (!$venda) {
            setar_msg('msgerro', 'Venda não encontrada.', 'erro');
            redirect('vendas');
        }

        $dados['titulo'] = 'Venda #' . $venda->id;
        $dados['venda'] = $venda;
        $dados['itens'] = $this->Venda_model->get_itens($id);
        $this->load->vars($dados);

        $this->load->view('templates/header');
        $this->load->view('pages/vendas/ver');
        $this->load->view('templates/footer');
    }

    /**
     * Comprovante interno de venda para impressão. NÃO é uma Nota Fiscal
     * Eletrônica (NF-e) — emitir uma NF-e de verdade exige certificado
     * digital e integração com a SEFAZ, fora do escopo deste sistema.
     * É só um recibo/comprovante para o cliente, sem valor fiscal.
     */
    public function recibo($id = null)
    {
        $venda = $id ? $this->Venda_model->get($id) : null;
        if (!$venda) {
            setar_msg('msgerro', 'Venda não encontrada.', 'erro');
            redirect('vendas');
        }

        $dados['venda'] = $venda;
        $dados['itens'] = $this->Venda_model->get_itens($id);
        $dados['config'] = $this->db->where('id', 1)->get('configuracoes')->row();
        $this->load->vars($dados);

        $this->load->view('pages/vendas/recibo');
    }

    public function apagar($id = null)
    {
        if (!$id || !$this->Venda_model->get($id)) {
            setar_msg('msgerro', 'Venda não encontrada.', 'erro');
            redirect('vendas');
        }

        $this->Venda_model->apagar($id);
        setar_msg('msgsucess', 'Venda removida com sucesso.', 'sucesso');
        redirect('vendas', 'refresh');
    }
}
