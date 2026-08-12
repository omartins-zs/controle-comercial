<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . 'core/Cadastro_base.php';

class Produtos extends Cadastro_base
{
    protected $table = 'produtos';
    protected $rota = 'produtos';
    protected $titulo_singular = 'produto';
    protected $titulo_plural = 'Produtos';

    protected $campos_lista = array(
        array('key' => 'categoria_nome', 'label' => 'Categoria'),
        array('key' => 'marca_nome', 'label' => 'Marca'),
        array('key' => 'preco', 'label' => 'Preço (R$)'),
        array('key' => 'estoque_qtd', 'label' => 'Estoque'),
    );

    protected $campos_form = array(
        array('key' => 'nome', 'label' => 'Nome do produto', 'type' => 'text', 'rules' => 'required|min_length[2]'),
        array('key' => 'categoria_id', 'label' => 'Categoria', 'type' => 'select', 'options_table' => 'categorias'),
        array('key' => 'marca_id', 'label' => 'Marca', 'type' => 'select', 'options_table' => 'marcas'),
        array('key' => 'preco', 'label' => 'Preço (R$)', 'type' => 'number', 'rules' => 'required|numeric'),
        array('key' => 'estoque_qtd', 'label' => 'Quantidade em estoque', 'type' => 'number', 'rules' => 'required|integer'),
        array('key' => 'ativo', 'label' => 'Ativo', 'type' => 'checkbox'),
    );

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Estoque_model');
    }

    // Sobrescreve o index() genérico: traz nome da categoria/marca via JOIN
    // (a listagem genérica não sabe fazer join) e usa uma view própria com um
    // botão extra de "Movimentar estoque" por produto.
    public function index()
    {
        $dados['titulo'] = $this->titulo_plural;
        $dados['items'] = $this->db
            ->select('produtos.*, categorias.nome AS categoria_nome, marcas.nome AS marca_nome')
            ->from('produtos')
            ->join('categorias', 'categorias.id = produtos.categoria_id', 'left')
            ->join('marcas', 'marcas.id = produtos.marca_id', 'left')
            ->order_by('produtos.nome', 'ASC')
            ->get()->result();
        $dados['campos'] = $this->campos_lista;
        $dados['base_route'] = $this->rota;
        $this->load->vars($dados);

        $this->load->view('templates/header');
        $this->load->view('pages/produtos/index');
        $this->load->view('templates/footer');
    }

    // Sobrescreve o add() genérico só para logar o estoque inicial no
    // histórico (se vier maior que zero) — sem isso o produto nasceria com
    // saldo já preenchido mas nenhuma movimentação explicando de onde veio.
    public function add()
    {
        $this->_setar_regras();

        if ($this->form_validation->run() == TRUE) {
            $data = $this->_dados_post();
            $produto_id = $this->Cadastro_model->insert_get_id($data);

            $estoque_inicial = (int) $data['estoque_qtd'];
            if ($estoque_inicial > 0) {
                $this->db->insert('estoque_movimentos', array(
                    'produto_id' => $produto_id,
                    'tipo' => 'entrada',
                    'quantidade' => $estoque_inicial,
                    'motivo' => 'Estoque inicial do cadastro',
                    'referencia_tipo' => 'manual',
                    'referencia_id' => null,
                    'estoque_resultante' => $estoque_inicial,
                ));
            }

            setar_msg('msgsucess', 'Produto cadastrado com sucesso.', 'sucesso');
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

    // Sobrescreve o editar() genérico só para registrar no histórico de
    // estoque quando a quantidade for alterada direto pela tela de edição
    // (além do ajuste manual dedicado em movimentar()) — assim qualquer
    // caminho que mude o saldo deixa rastro em estoque_movimentos.
    public function editar($id = null)
    {
        $produto_antes = $id ? $this->Cadastro_model->get($id) : null;
        if (!$produto_antes) {
            setar_msg('msgerro', 'Produto não encontrado.', 'erro');
            redirect($this->rota);
        }

        $this->_setar_regras();

        if ($this->form_validation->run() == TRUE) {
            $data = $this->_dados_post();
            $novo_estoque = (int) $data['estoque_qtd'];
            $diferenca = $novo_estoque - (int) $produto_antes->estoque_qtd;

            // Deixa o Cadastro_model gravar tudo, mas some via Estoque_model
            // depois, senão o valor bruto (não a diferença) seria contabilizado.
            $this->Cadastro_model->update($id, $data);

            if ($diferenca !== 0) {
                // Só registra o movimento (não altera o saldo de novo — já foi
                // gravado pelo update acima): grava direto na tabela de
                // histórico com o saldo final.
                $this->db->insert('estoque_movimentos', array(
                    'produto_id' => $id,
                    'tipo' => 'ajuste',
                    'quantidade' => abs($diferenca),
                    'motivo' => 'Ajuste manual via edição de cadastro',
                    'referencia_tipo' => 'manual',
                    'referencia_id' => null,
                    'estoque_resultante' => $novo_estoque,
                ));
            }

            setar_msg('msgsucess', 'Produto atualizado com sucesso.', 'sucesso');
            redirect($this->rota, 'refresh');
        }

        $dados['titulo'] = 'Editar ' . $this->titulo_singular;
        $dados['item'] = $produto_antes;
        $dados['campos_form'] = $this->_campos_form_com_options();
        $dados['base_route'] = $this->rota;
        $this->load->vars($dados);

        $this->load->view('templates/header');
        $this->load->view('pages/cadastro/form');
        $this->load->view('templates/footer');
    }

    /**
     * Ajuste manual de estoque (entrada/saída/ajuste), fora do fluxo de
     * vendas/OS. Único lugar, junto de editar() acima, que altera
     * produtos.estoque_qtd fora de uma venda/OS — sempre logado.
     */
    public function movimentar($id = null)
    {
        $produto = $id ? $this->Cadastro_model->get($id) : null;
        if (!$produto) {
            setar_msg('msgerro', 'Produto não encontrado.', 'erro');
            redirect($this->rota);
        }

        $this->form_validation->set_rules('tipo', 'Tipo', 'required|in_list[entrada,saida,ajuste]');
        $this->form_validation->set_rules('quantidade', 'Quantidade', 'required|integer|greater_than[0]');
        $this->form_validation->set_rules('motivo', 'Motivo', 'required|min_length[3]');

        if ($this->form_validation->run() == TRUE) {
            $tipo = $this->input->post('tipo');
            $quantidade = (int) $this->input->post('quantidade');
            $delta = ($tipo === 'saida') ? -$quantidade : $quantidade;

            $this->Estoque_model->ajustar($id, $delta, $tipo, $this->input->post('motivo'), 'manual', null);
            setar_msg('msgsucess', 'Movimentação registrada com sucesso.', 'sucesso');
            redirect($this->rota, 'refresh');
        }

        $dados['titulo'] = 'Movimentar estoque — ' . $produto->nome;
        $dados['produto'] = $produto;
        $dados['historico'] = $this->Estoque_model->historico($id, 20);
        $this->load->vars($dados);

        $this->load->view('templates/header');
        $this->load->view('pages/produtos/movimentar');
        $this->load->view('templates/footer');
    }

    protected function bloqueio_apagar($id)
    {
        if ($this->Cadastro_model->em_uso_por('venda_itens', 'produto_id', $id)) {
            return 'Este produto já foi usado em vendas e não pode ser excluído.';
        }
        if ($this->Cadastro_model->em_uso_por('ordem_servico_itens', 'produto_id', $id)) {
            return 'Este produto já foi usado em ordens de serviço e não pode ser excluído.';
        }
        if ($this->Cadastro_model->em_uso_por('estoque_movimentos', 'produto_id', $id)) {
            return 'Este produto possui histórico de movimentação de estoque e não pode ser excluído.';
        }
        return false;
    }
}
