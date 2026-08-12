<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ordens_servico extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->ion_auth->logged_in()) {
            setar_msg('msgerro', 'Erro: Você precisa estar logado no sistema.', 'erro');
            redirect('login');
        }
        $this->load->model('OrdemServico_model');
    }

    public function index()
    {
        $dados['titulo'] = 'Ordens de Serviço';
        $dados['ordens'] = $this->OrdemServico_model->get_all();
        $this->load->vars($dados);

        $this->load->view('templates/header');
        $this->load->view('pages/ordens_servico/index');
        $this->load->view('templates/footer');
    }

    public function add()
    {
        $this->form_validation->set_rules('cliente_id', 'Cliente', 'required');
        $this->form_validation->set_rules('data_abertura', 'Data de abertura', 'required');
        $this->form_validation->set_rules('tipo_item[]', 'Tipo', 'required');

        if ($this->form_validation->run() == TRUE) {
            $os_data = array(
                'cliente_id' => $this->input->post('cliente_id'),
                'vendedor_id' => $this->input->post('vendedor_id') ?: null,
                'descricao_problema' => $this->input->post('descricao_problema'),
                'data_abertura' => $this->input->post('data_abertura'),
                'status' => $this->input->post('status') ?: 'aberta',
            );

            $tipos = $this->input->post('tipo_item');
            $produtos_post = $this->input->post('produto_id');
            $descricoes_post = $this->input->post('descricao_item');
            $valores_post = $this->input->post('valor_unitario');
            $quantidades_post = $this->input->post('quantidade');

            $itens = array();
            foreach ($tipos as $i => $tipo) {
                $itens[] = array(
                    'tipo' => $tipo,
                    'produto_id' => isset($produtos_post[$i]) ? $produtos_post[$i] : null,
                    'descricao' => isset($descricoes_post[$i]) ? $descricoes_post[$i] : '',
                    'valor_unitario' => isset($valores_post[$i]) ? $valores_post[$i] : 0,
                    'quantidade' => isset($quantidades_post[$i]) ? $quantidades_post[$i] : 0,
                );
            }

            $ordem_id = $this->OrdemServico_model->criar($os_data, $itens);
            if ($ordem_id) {
                setar_msg('msgsucess', 'Ordem de serviço registrada com sucesso.', 'sucesso');
                redirect('ordens_servico', 'refresh');
            }

            setar_msg('msgerro', 'Não foi possível registrar a ordem de serviço: informe ao menos um item válido.', 'erro');
        }

        $dados['titulo'] = 'Nova Ordem de Serviço';
        $dados['clientes'] = $this->db->select('id, nome')->from('clientes')->where('ativo', 1)->order_by('nome')->get()->result();
        $dados['vendedores'] = $this->db->select('id, nome')->from('vendedores')->where('ativo', 1)->order_by('nome')->get()->result();
        // estoque_qtd é necessário aqui: a view mostra "(estoque: N)" ao lado de
        // cada peça, pra quem abre a OS saber se tem a peça disponível.
        $dados['produtos'] = $this->db->select('id, nome, preco, estoque_qtd')->from('produtos')->where('ativo', 1)->order_by('nome')->get()->result();
        $this->load->vars($dados);

        $this->load->view('templates/header');
        $this->load->view('pages/ordens_servico/novo');
        $this->load->view('templates/footer');
    }

    public function ver($id = null)
    {
        $os = $id ? $this->OrdemServico_model->get($id) : null;
        if (!$os) {
            setar_msg('msgerro', 'Ordem de serviço não encontrada.', 'erro');
            redirect('ordens_servico');
        }

        if ($this->input->post('novo_status')) {
            $this->OrdemServico_model->mudar_status($id, $this->input->post('novo_status'));
            setar_msg('msgsucess', 'Status atualizado com sucesso.', 'sucesso');
            redirect('ordens_servico/ver/' . $id, 'refresh');
        }

        $dados['titulo'] = 'Ordem de Serviço #' . $os->id;
        $dados['os'] = $os;
        $dados['itens'] = $this->OrdemServico_model->get_itens($id);
        $this->load->vars($dados);

        $this->load->view('templates/header');
        $this->load->view('pages/ordens_servico/ver');
        $this->load->view('templates/footer');
    }

    /**
     * Comprovante interno da OS para impressão — não é uma Nota Fiscal
     * Eletrônica (mesma ressalva do Vendas::recibo()).
     */
    public function recibo($id = null)
    {
        $os = $id ? $this->OrdemServico_model->get($id) : null;
        if (!$os) {
            setar_msg('msgerro', 'Ordem de serviço não encontrada.', 'erro');
            redirect('ordens_servico');
        }

        $dados['os'] = $os;
        $dados['itens'] = $this->OrdemServico_model->get_itens($id);
        $dados['config'] = $this->db->where('id', 1)->get('configuracoes')->row();
        $this->load->vars($dados);

        $this->load->view('pages/ordens_servico/recibo');
    }

    public function apagar($id = null)
    {
        if (!$id || !$this->OrdemServico_model->get($id)) {
            setar_msg('msgerro', 'Ordem de serviço não encontrada.', 'erro');
            redirect('ordens_servico');
        }

        $this->OrdemServico_model->apagar($id);
        setar_msg('msgsucess', 'Ordem de serviço removida com sucesso.', 'sucesso');
        redirect('ordens_servico', 'refresh');
    }
}
