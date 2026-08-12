<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . 'core/Cadastro_base.php';

class Contas_receber extends Cadastro_base
{
    protected $table = 'contas_receber';
    protected $rota = 'contas_receber';
    protected $titulo_singular = 'conta a receber';
    protected $titulo_plural = 'Contas a Receber';
    protected $order_by = 'vencimento';

    protected $campos_lista = array(
        array('key' => 'cliente_nome', 'label' => 'Cliente'),
        array('key' => 'descricao', 'label' => 'Descrição'),
        array('key' => 'valor', 'label' => 'Valor (R$)'),
        array('key' => 'vencimento', 'label' => 'Vencimento'),
        array('key' => 'status', 'label' => 'Status'),
    );

    protected $campos_form = array(
        array('key' => 'descricao', 'label' => 'Descrição', 'type' => 'text', 'rules' => 'required|min_length[3]'),
        array('key' => 'cliente_id', 'label' => 'Cliente', 'type' => 'select', 'options_table' => 'clientes'),
        array('key' => 'valor', 'label' => 'Valor (R$)', 'type' => 'number', 'rules' => 'required|numeric'),
        array('key' => 'vencimento', 'label' => 'Vencimento', 'type' => 'date', 'rules' => 'required'),
        array('key' => 'status', 'label' => 'Status', 'type' => 'select', 'options_static' => array(
            array('id' => 'pendente', 'nome' => 'Pendente'),
            array('id' => 'recebido', 'nome' => 'Recebido'),
        )),
        array('key' => 'recebido_em', 'label' => 'Recebido em', 'type' => 'date'),
    );

    public function index()
    {
        $dados['titulo'] = $this->titulo_plural;
        $dados['items'] = $this->db
            ->select('contas_receber.*, clientes.nome AS cliente_nome')
            ->from('contas_receber')
            ->join('clientes', 'clientes.id = contas_receber.cliente_id', 'left')
            ->order_by('contas_receber.' . $this->order_by, 'ASC')
            ->get()->result();
        $dados['campos'] = $this->campos_lista;
        $dados['base_route'] = $this->rota;
        $this->load->vars($dados);

        $this->load->view('templates/header');
        $this->load->view('pages/cadastro/index');
        $this->load->view('templates/footer');
    }
}
