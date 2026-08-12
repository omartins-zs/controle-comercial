<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Usuarios extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Verifica se está logado no sistema
        if (!$this->ion_auth->logged_in()) {
            setar_msg('msgerro', 'Erro: Você precisa estar logado no sistema.', 'erro');
            redirect('login');
        }
        // Verifica se o usuario e admin | Function pega do nome do banco | Usado pelo Id e in_group

        # single group (by id)
        if (!$this->ion_auth->in_group(1)) {
            // $this->session->set_flashdata('message', 'You must be part of the group 1 to view this page');
            setar_msg('msgerro', 'Erro: Você precisa ser um administrador para acessar essa pagina', 'erro');
            redirect('home');
        }

        $this->load->model('ion_auth_model');
    }

    public function index()
    {
        $dados['users'] = $this->ion_auth->users()->result(); // get all users

        // Titulo da aba no navegador
        $dados["titulo"] = "Usuarios";

        // Pega os dados do Model

        // Passa um conjunto de variaveis para as views
        $this->load->vars($dados);

        $this->load->view('templates/header');
        $this->load->view('pages/usuarios/index');
        $this->load->view('templates/footer');
    }
    public function add()
    {
        // Titulo da aba no navegador
        $dados["titulo"] = "Novo usuario";

        // Pega os dados do Model

        // Passa um conjunto de variaveis para as views
        $this->load->vars($dados);

        // Validando os inputs
        // min_length[8] para bater com a política declarada em application/config/ion_auth.php ($config['min_password_length'])
        $this->form_validation->set_rules('nome_usuario', 'Nome', 'required|min_length[4]', array('min_length' => 'O campo nome de usuário deve ter pelo menos 4 caractere(s).'));
        $this->form_validation->set_rules('email_usuario', 'E-mail', 'required|valid_email|is_unique[users.email]');

        $this->form_validation->set_rules('senha_usuario', 'Senha', 'required|min_length[8]|max_length[20]', array('min_length' => 'O campo senha deve ter pelo menos 8 caractere(s) e no máximo 20.', 'max_length' => 'O campo senha deve ter pelo menos 8 caractere(s) e no máximo 20.'));
        $this->form_validation->set_rules('senha_usuario2', 'Confirmar senha', 'required|matches[senha_usuario]', array('matches' => 'O campo senha não confere.'));

        if ($this->form_validation->run() == TRUE) {
            $username = $this->input->post('nome_usuario');
            $password = $this->input->post('senha_usuario');
            $email = $this->input->post('email_usuario');
            $tipo = $this->input->post('tipo_usuario');

            // Passei o username como adicional pq a funçao nao funcionou 100% e não trouxe o nome cadastrado
            $additional_data = array(
                'username' => $username,
            );

            // Identidade configurada em ion_auth.php é 'email' — precisa ser o 1º parâmetro do register()
            $group = array($tipo);
            $this->ion_auth->register($email, $password, $email, $additional_data, $group);
            setar_msg('msgsucess', 'Cadastro realizado com sucesso.', 'sucesso');
            redirect('usuarios', 'refresh');
        } else {
            $this->load->view('templates/header');
            $this->load->view('pages/usuarios/novo');
            $this->load->view('templates/footer');
        }
    }

    public function editar($id = null)
    {
        if (!$id || !$this->ion_auth->user($id)->row()) {
            setar_msg('msgerro', 'Usuário não encontrado.', 'erro');
            redirect('usuarios');
        }

        // Validando os inputs (senha é opcional na edição: só troca se preenchida).
        // Não uso is_unique[users.email] aqui: essa versão do CI3 não suporta excluir
        // o próprio registro da checagem, então flagaria falso-positivo ao salvar sem
        // trocar o e-mail. A duplicidade é checada manualmente abaixo.
        $this->form_validation->set_rules('nome_usuario', 'Nome', 'required|min_length[4]', array('min_length' => 'O campo nome de usuário deve ter pelo menos 4 caractere(s).'));
        $this->form_validation->set_rules('email_usuario', 'E-mail', 'required|valid_email');
        $this->form_validation->set_rules('senha_usuario', 'Senha', 'min_length[8]|max_length[20]', array('min_length' => 'O campo senha deve ter pelo menos 8 caractere(s) e no máximo 20.', 'max_length' => 'O campo senha deve ter pelo menos 8 caractere(s) e no máximo 20.'));
        $this->form_validation->set_rules('senha_usuario2', 'Confirmar senha', 'matches[senha_usuario]', array('matches' => 'O campo senha não confere.'));

        if ($this->form_validation->run() == TRUE) {
            $email = $this->input->post('email_usuario');
            $duplicado = $this->db->where('email', $email)->where('id !=', $id)->get('users')->row();

            if ($duplicado) {
                setar_msg('msgerro', 'Este e-mail já está em uso por outro usuário.', 'erro');
            } else {
                $data = array(
                    'username' => $this->input->post('nome_usuario'),
                    'email' => $email,
                );

                $password = $this->input->post('senha_usuario');
                if (!empty($password)) {
                    $data['password'] = $password;
                }

                if ($this->ion_auth->update($id, $data)) {
                    $tipo = $this->input->post('tipo_usuario');
                    if ($tipo) {
                        // Remove de todos os grupos atuais e adiciona só no grupo selecionado
                        $this->ion_auth->remove_from_group(null, $id);
                        $this->ion_auth->add_to_group(array($tipo), $id);
                    }
                    setar_msg('msgsucess', 'Usuário atualizado com sucesso.', 'sucesso');
                    redirect('usuarios', 'refresh');
                }

                setar_msg('msgerro', 'Erro ao atualizar usuário: ' . $this->ion_auth->errors(), 'erro');
            }
        }

        // Titulo da aba no navegador
        $dados['titulo'] = 'Editar usuário';
        $dados['user'] = $this->ion_auth->user($id)->row();
        $user_groups = $this->ion_auth->get_users_groups($id)->result();
        $dados['user_group_id'] = !empty($user_groups) ? $user_groups[0]->id : null;
        $this->load->vars($dados);

        $this->load->view('templates/header');
        $this->load->view('pages/usuarios/editar');
        $this->load->view('templates/footer');
    }

    public function apagar($id = null)
    {
        if (!$id || !$this->ion_auth->user($id)->row()) {
            setar_msg('msgerro', 'Usuário não encontrado.', 'erro');
            redirect('usuarios');
        }

        // Não deixa o usuário logado apagar a própria conta por essa tela
        if ((int) $id === (int) $this->ion_auth->get_user_id()) {
            setar_msg('msgerro', 'Você não pode excluir o usuário com o qual está logado.', 'erro');
            redirect('usuarios');
        }

        if ($this->ion_auth->delete_user($id)) {
            setar_msg('msgsucess', 'Usuário removido com sucesso.', 'sucesso');
        } else {
            setar_msg('msgerro', 'Erro ao remover usuário: ' . $this->ion_auth->errors(), 'erro');
        }

        redirect('usuarios', 'refresh');
    }
}
