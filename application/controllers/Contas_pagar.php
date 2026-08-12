<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . 'core/Cadastro_base.php';

class Contas_pagar extends Cadastro_base
{
    protected $table = 'contas_pagar';
    protected $rota = 'contas_pagar';
    protected $titulo_singular = 'conta a pagar';
    protected $titulo_plural = 'Contas a Pagar';
    protected $order_by = 'vencimento';

    protected $campos_lista = array(
        array('key' => 'fornecedor_nome', 'label' => 'Fornecedor'),
        array('key' => 'descricao', 'label' => 'Descrição'),
        array('key' => 'valor', 'label' => 'Valor (R$)'),
        array('key' => 'vencimento', 'label' => 'Vencimento'),
        array('key' => 'status', 'label' => 'Status'),
    );

    protected $campos_form = array(
        array('key' => 'descricao', 'label' => 'Descrição', 'type' => 'text', 'rules' => 'required|min_length[3]'),
        array('key' => 'fornecedor_id', 'label' => 'Fornecedor', 'type' => 'select', 'options_table' => 'fornecedores'),
        array('key' => 'valor', 'label' => 'Valor (R$)', 'type' => 'number', 'rules' => 'required|numeric'),
        array('key' => 'vencimento', 'label' => 'Vencimento', 'type' => 'date', 'rules' => 'required'),
        array('key' => 'status', 'label' => 'Status', 'type' => 'select', 'options_static' => array(
            array('id' => 'pendente', 'nome' => 'Pendente'),
            array('id' => 'pago', 'nome' => 'Pago'),
        )),
        array('key' => 'pago_em', 'label' => 'Pago em', 'type' => 'date'),
    );

    public function index()
    {
        $dados['titulo'] = $this->titulo_plural;
        $dados['items'] = $this->db
            ->select('contas_pagar.*, fornecedores.nome AS fornecedor_nome')
            ->from('contas_pagar')
            ->join('fornecedores', 'fornecedores.id = contas_pagar.fornecedor_id', 'left')
            ->order_by('contas_pagar.' . $this->order_by, 'ASC')
            ->get()->result();
        $dados['campos'] = $this->campos_lista;
        $dados['base_route'] = $this->rota;
        $this->load->vars($dados);

        $this->load->view('templates/header');
        $this->load->view('pages/cadastro/index');
        $this->load->view('templates/footer');
    }
}
