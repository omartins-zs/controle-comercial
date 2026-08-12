<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Home extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		if (!$this->ion_auth->logged_in()) {
			setar_msg('msgerro', 'Erro: Você precisa estar logado no sistema.', 'erro');
			redirect('login');
		}
	}

	public function index()
	{
		// Titulo da aba no navegador
		$dados["titulo"] = "Inicial";

		// Indicadores rápidos do dashboard
		$dados['total_clientes'] = $this->db->count_all('clientes');
		$dados['total_produtos'] = $this->db->count_all('produtos');
		$dados['total_vendas'] = $this->db->count_all('vendas');
		$dados['total_a_receber'] = (float) $this->db->select_sum('valor')->where('status', 'pendente')->get('contas_receber')->row()->valor;
		$dados['total_a_pagar'] = (float) $this->db->select_sum('valor')->where('status', 'pendente')->get('contas_pagar')->row()->valor;
		$dados['ultimas_vendas'] = $this->db
			->select('vendas.*, clientes.nome AS cliente_nome')
			->from('vendas')
			->join('clientes', 'clientes.id = vendas.cliente_id', 'left')
			->order_by('vendas.data_venda', 'DESC')
			->limit(5)
			->get()->result();

		// Passa um conjunto de variaveis para as views
		$this->load->vars($dados);

		$this->load->view('templates/header');
		$this->load->view('pages/index');
		$this->load->view('templates/footer');
	}
}
