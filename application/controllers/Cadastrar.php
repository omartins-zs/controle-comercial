<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller público de auto-cadastro.
 *
 * Qualquer pessoa acessa /cadastrar, preenche nome/e-mail/senha e o sistema
 * cria a conta já ativa (active = 1). O usuário entra imediatamente.
 *
 * Grupo padrão: 2 (Vendedor). O admin pode mover para grupo 1 depois.
 */
class Cadastrar extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library(['session', 'form_validation', 'ion_auth']);
        $this->load->helper(['url', 'form', 'funcao']);
    }

    public function index()
    {
        // Se já está logado não precisa se cadastrar
        if ($this->ion_auth->logged_in()) {
            redirect('home', 'refresh');
            return;
        }

        $dados['titulo'] = 'Solicitar Acesso';
        $this->load->vars($dados);

        if ($this->input->method() !== 'post') {
            $this->_exibir_form();
            return;
        }

        // Validação
        $this->form_validation->set_rules('nome',  'Nome',               'required|trim|min_length[3]|max_length[100]');
        $this->form_validation->set_rules('email', 'E-mail',             'required|trim|valid_email|is_unique[users.email]',
            ['is_unique' => 'Este e-mail já está cadastrado no sistema.']);
        $this->form_validation->set_rules('senha', 'Senha',              'required|min_length[8]|max_length[20]',
            ['min_length' => 'A senha deve ter pelo menos 8 caracteres.']);
        $this->form_validation->set_rules('senha2', 'Confirmação de senha', 'required|matches[senha]',
            ['matches' => 'As senhas não conferem.']);

        if ($this->form_validation->run() !== TRUE) {
            $this->_exibir_form();
            return;
        }

        $nome  = $this->input->post('nome',  TRUE);
        $email = $this->input->post('email', TRUE);
        $senha = $this->input->post('senha');

        // ion_auth->register(identity, password, email, additional_data, groups)
        $user_id = $this->ion_auth->register(
            $email,                    // identity (email)
            $senha,
            $email,
            ['username' => $nome],
            [2]                        // grupo Vendedor
        );

        if ($user_id) {
            // Conta já ativa (ion_auth cria com active=1 por padrão)
            setar_msg(
                'msgsucess',
                'Conta criada com sucesso! Faça o login para entrar.',
                'sucesso'
            );
            redirect('login', 'refresh');
        } else {
            setar_msg('msgerro', 'Erro ao criar o cadastro. Tente novamente.', 'erro');
            $this->_exibir_form();
        }
    }

    private function _exibir_form()
    {
        $this->load->view('templates/header');
        $this->load->view('pages/cadastrar');
        $this->load->view('templates/footer');
    }
}
