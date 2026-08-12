<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller base para os módulos de cadastro simples (Clientes, Fornecedores,
 * Vendedores, Transportadoras, Categorias, Marcas). Não é autoload automático
 * do CodeIgniter (não segue o prefixo MY_ de core override) — cada controller
 * concreto precisa fazer `require_once APPPATH . 'core/Cadastro_base.php';`
 * antes de declarar sua classe. Reduz repetição de index/add/editar/apagar.
 */
class Cadastro_base extends CI_Controller
{
    protected $table;             // nome da tabela no banco
    protected $rota;              // segmento de rota, ex.: 'clientes'
    protected $titulo_singular;   // ex.: 'cliente'
    protected $titulo_plural;     // ex.: 'Clientes'
    protected $order_by = 'nome';
    protected $campos_lista = array(); // colunas exibidas na listagem: array('key'=>'email','label'=>'E-mail')
    protected $campos_form = array();  // campos do formulário: array('key','label','type','rules'?,'options_table'?)

    public function __construct()
    {
        parent::__construct();

        if (!$this->ion_auth->logged_in()) {
            setar_msg('msgerro', 'Erro: Você precisa estar logado no sistema.', 'erro');
            redirect('login');
        }

        $this->load->model('Cadastro_model');
        $this->Cadastro_model->set_table($this->table);
    }

    public function index()
    {
        $dados['titulo'] = $this->titulo_plural;
        $dados['items'] = $this->Cadastro_model->get_all($this->order_by);
        $dados['campos'] = $this->campos_lista;
        $dados['base_route'] = $this->rota;
        $this->load->vars($dados);

        $this->load->view('templates/header');
        $this->load->view('pages/cadastro/index');
        $this->load->view('templates/footer');
    }

    public function add()
    {
        $this->_setar_regras();

        if ($this->form_validation->run() == TRUE) {
            $this->Cadastro_model->insert($this->_dados_post());
            setar_msg('msgsucess', ucfirst($this->titulo_singular) . ' cadastrado com sucesso.', 'sucesso');
            redirect($this->rota, 'refresh');
        }

        $dados['titulo'] = 'Novo(a) ' . $this->titulo_singular;
        $dados['item'] = null;
        $dados['campos_form'] = $this->_campos_form_com_options();
        $dados['base_route'] = $this->rota;
        $this->load->vars($dados);

        $this->load->view('templates/header');
        $this->load->view('pages/cadastro/form');
        $this->load->view('templates/footer');
    }

    public function editar($id = null)
    {
        $item = $id ? $this->Cadastro_model->get($id) : null;
        if (!$item) {
            setar_msg('msgerro', ucfirst($this->titulo_singular) . ' não encontrado.', 'erro');
            redirect($this->rota);
        }

        $this->_setar_regras();

        if ($this->form_validation->run() == TRUE) {
            $this->Cadastro_model->update($id, $this->_dados_post());
            setar_msg('msgsucess', ucfirst($this->titulo_singular) . ' atualizado com sucesso.', 'sucesso');
            redirect($this->rota, 'refresh');
        }

        $dados['titulo'] = 'Editar ' . $this->titulo_singular;
        $dados['item'] = $item;
        $dados['campos_form'] = $this->_campos_form_com_options();
        $dados['base_route'] = $this->rota;
        $this->load->vars($dados);

        $this->load->view('templates/header');
        $this->load->view('pages/cadastro/form');
        $this->load->view('templates/footer');
    }

    public function apagar($id = null)
    {
        if (!$id || !$this->Cadastro_model->get($id)) {
            setar_msg('msgerro', ucfirst($this->titulo_singular) . ' não encontrado.', 'erro');
            redirect($this->rota);
        }

        // Hook opcional: controllers filhos podem sobrescrever bloqueio_apagar()
        // para impedir exclusão de registros referenciados em outra tabela.
        $bloqueio = $this->bloqueio_apagar($id);
        if ($bloqueio) {
            setar_msg('msgerro', $bloqueio, 'erro');
            redirect($this->rota);
        }

        $this->Cadastro_model->delete($id);
        setar_msg('msgsucess', ucfirst($this->titulo_singular) . ' removido com sucesso.', 'sucesso');
        redirect($this->rota, 'refresh');
    }

    protected function bloqueio_apagar($id)
    {
        return false; // sem bloqueio por padrão; sobrescrever nos filhos quando necessário
    }

    protected function _setar_regras()
    {
        foreach ($this->campos_form as $campo) {
            if (!empty($campo['rules'])) {
                $this->form_validation->set_rules($campo['key'], $campo['label'], $campo['rules']);
            }
        }
    }

    protected function _dados_post()
    {
        $data = array();
        foreach ($this->campos_form as $campo) {
            if ($campo['type'] === 'checkbox') {
                $data[$campo['key']] = $this->input->post($campo['key']) ? 1 : 0;
            } else {
                $data[$campo['key']] = $this->input->post($campo['key']);
            }
        }
        return $data;
    }

    protected function _campos_form_com_options()
    {
        $campos = $this->campos_form;
        foreach ($campos as &$campo) {
            if ($campo['type'] !== 'select') {
                continue;
            }
            if (isset($campo['options_table'])) {
                $campo['options'] = $this->db->select('id, nome')->from($campo['options_table'])->order_by('nome')->get()->result();
            } elseif (isset($campo['options_static'])) {
                // Opções fixas (ex.: status), sem consulta ao banco. Convertido para
                // objeto porque a view genérica espera $opt->id / $opt->nome.
                $campo['options'] = array_map(function ($o) {
                    return (object) $o;
                }, $campo['options_static']);
            }
        }
        return $campos;
    }
}
