<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . 'core/Cadastro_base.php';

class Fornecedores extends Cadastro_base
{
    protected $table = 'fornecedores';
    protected $rota = 'fornecedores';
    protected $titulo_singular = 'fornecedor';
    protected $titulo_plural = 'Fornecedores';

    protected $campos_lista = array(
        array('key' => 'email', 'label' => 'E-mail'),
        array('key' => 'telefone', 'label' => 'Telefone'),
        array('key' => 'documento', 'label' => 'CNPJ'),
    );

    protected $campos_form = array(
        array('key' => 'nome', 'label' => 'Nome / Razão Social', 'type' => 'text', 'rules' => 'required|min_length[3]'),
        array('key' => 'email', 'label' => 'E-mail', 'type' => 'email', 'rules' => 'valid_email'),
        array('key' => 'telefone', 'label' => 'Telefone', 'type' => 'text'),
        array('key' => 'documento', 'label' => 'CNPJ', 'type' => 'text'),
        array('key' => 'ativo', 'label' => 'Ativo', 'type' => 'checkbox'),
    );

    protected function bloqueio_apagar($id)
    {
        if ($this->Cadastro_model->em_uso_por('contas_pagar', 'fornecedor_id', $id)) {
            return 'Este fornecedor possui contas a pagar vinculadas e não pode ser excluído.';
        }
        return false;
    }
}
