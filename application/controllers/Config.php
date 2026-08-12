<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Config extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->ion_auth->logged_in()) {
            setar_msg('msgerro', 'Erro: Você precisa estar logado no sistema.', 'erro');
            redirect('login');
        }
        if (!$this->ion_auth->in_group(1)) {
            setar_msg('msgerro', 'Erro: Você precisa ser um administrador para acessar essa pagina', 'erro');
            redirect('home');
        }
    }

    public function index()
    {
        $this->form_validation->set_rules('site_nome', 'Nome do sistema', 'required|min_length[3]');
        $this->form_validation->set_rules('admin_email', 'E-mail do administrador', 'required|valid_email');

        if ($this->form_validation->run() == TRUE) {
            $this->db->where('id', 1)->update('configuracoes', array(
                'site_nome' => $this->input->post('site_nome'),
                'admin_email' => $this->input->post('admin_email'),
            ));
            setar_msg('msgsucess', 'Configurações atualizadas com sucesso.', 'sucesso');
            redirect('config', 'refresh');
        }

        $dados['titulo'] = 'Configurações do sistema';
        $dados['config'] = $this->db->where('id', 1)->get('configuracoes')->row();
        $this->load->vars($dados);

        $this->load->view('templates/header');
        $this->load->view('pages/config/index');
        $this->load->view('templates/footer');
    }
}
