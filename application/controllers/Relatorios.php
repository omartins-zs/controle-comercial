<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Relatórios somente-leitura, reaproveitando os dados já cadastrados nos
 * outros módulos. Sem geração de PDF/Excel — apenas listagem em tela.
 */
class Relatorios extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->ion_auth->logged_in()) {
            setar_msg('msgerro', 'Erro: Você precisa estar logado no sistema.', 'erro');
            redirect('login');
        }
    }

    public function clientes()
    {
        $this->_render('Relatório de Clientes', array(
            array('key' => 'email', 'label' => 'E-mail'),
            array('key' => 'telefone', 'label' => 'Telefone'),
            array('key' => 'cidade', 'label' => 'Cidade'),
            array('key' => 'uf', 'label' => 'UF'),
        ), $this->db->select('*')->from('clientes')->order_by('nome')->get()->result());
    }

    public function produtos()
    {
        $items = $this->db
            ->select('produtos.*, categorias.nome AS categoria_nome, marcas.nome AS marca_nome')
            ->from('produtos')
            ->join('categorias', 'categorias.id = produtos.categoria_id', 'left')
            ->join('marcas', 'marcas.id = produtos.marca_id', 'left')
            ->order_by('produtos.nome')
            ->get()->result();

        $this->_render('Relatório de Produtos / Estoque', array(
            array('key' => 'categoria_nome', 'label' => 'Categoria'),
            array('key' => 'marca_nome', 'label' => 'Marca'),
            array('key' => 'preco', 'label' => 'Preço (R$)'),
            array('key' => 'estoque_qtd', 'label' => 'Estoque'),
        ), $items);
    }

    public function vendas()
    {
        $this->load->model('Venda_model');
        $this->_render('Relatório de Vendas', array(
            array('key' => 'cliente_nome', 'label' => 'Cliente'),
            array('key' => 'vendedor_nome', 'label' => 'Vendedor'),
            array('key' => 'data_venda', 'label' => 'Data'),
            array('key' => 'status', 'label' => 'Status'),
            array('key' => 'total', 'label' => 'Total (R$)'),
        ), $this->Venda_model->get_all());
    }

    public function ordens_servico()
    {
        $this->load->model('OrdemServico_model');
        $this->_render('Relatório de Ordens de Serviço', array(
            array('key' => 'cliente_nome', 'label' => 'Cliente'),
            array('key' => 'vendedor_nome', 'label' => 'Responsável'),
            array('key' => 'data_abertura', 'label' => 'Abertura'),
            array('key' => 'status', 'label' => 'Status'),
            array('key' => 'total', 'label' => 'Total (R$)'),
        ), $this->OrdemServico_model->get_all());
    }

    public function estoque()
    {
        $this->load->model('Estoque_model');
        $this->_render('Histórico de Movimentação de Estoque', array(
            array('key' => 'produto_nome', 'label' => 'Produto'),
            array('key' => 'tipo', 'label' => 'Tipo'),
            array('key' => 'quantidade', 'label' => 'Quantidade'),
            array('key' => 'motivo', 'label' => 'Motivo'),
            array('key' => 'estoque_resultante', 'label' => 'Saldo resultante'),
            array('key' => 'created_at', 'label' => 'Data'),
        ), $this->Estoque_model->historico());
    }

    public function contas_pagar()
    {
        $items = $this->db
            ->select('contas_pagar.*, fornecedores.nome AS fornecedor_nome')
            ->from('contas_pagar')
            ->join('fornecedores', 'fornecedores.id = contas_pagar.fornecedor_id', 'left')
            ->order_by('contas_pagar.vencimento')
            ->get()->result();

        $this->_render('Relatório de Contas a Pagar', array(
            array('key' => 'fornecedor_nome', 'label' => 'Fornecedor'),
            array('key' => 'descricao', 'label' => 'Descrição'),
            array('key' => 'valor', 'label' => 'Valor (R$)'),
            array('key' => 'vencimento', 'label' => 'Vencimento'),
            array('key' => 'status', 'label' => 'Status'),
        ), $items);
    }

    public function contas_receber()
    {
        $items = $this->db
            ->select('contas_receber.*, clientes.nome AS cliente_nome')
            ->from('contas_receber')
            ->join('clientes', 'clientes.id = contas_receber.cliente_id', 'left')
            ->order_by('contas_receber.vencimento')
            ->get()->result();

        $this->_render('Relatório de Contas a Receber', array(
            array('key' => 'cliente_nome', 'label' => 'Cliente'),
            array('key' => 'descricao', 'label' => 'Descrição'),
            array('key' => 'valor', 'label' => 'Valor (R$)'),
            array('key' => 'vencimento', 'label' => 'Vencimento'),
            array('key' => 'status', 'label' => 'Status'),
        ), $items);
    }

    private function _render($titulo, $campos, $items)
    {
        $dados['titulo'] = $titulo;
        $dados['campos'] = $campos;
        $dados['items'] = $items;
        $this->load->vars($dados);

        $this->load->view('templates/header');
        $this->load->view('pages/relatorios/index');
        $this->load->view('templates/footer');
    }
}
