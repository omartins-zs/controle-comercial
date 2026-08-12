<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . 'core/Cadastro_base.php';

class Clientes extends Cadastro_base
{
    protected $table = 'clientes';
    protected $rota = 'clientes';
    protected $titulo_singular = 'cliente';
    protected $titulo_plural = 'Clientes';

    protected $campos_lista = array(
        array('key' => 'email', 'label' => 'E-mail'),
        array('key' => 'telefone', 'label' => 'Telefone'),
        array('key' => 'cidade', 'label' => 'Cidade'),
        array('key' => 'uf', 'label' => 'UF'),
    );

    protected $campos_form = array(
        array('key' => 'nome', 'label' => 'Nome / Razão Social', 'type' => 'text', 'rules' => 'required|min_length[3]'),
        array('key' => 'email', 'label' => 'E-mail', 'type' => 'email', 'rules' => 'valid_email'),
        array('key' => 'telefone', 'label' => 'Telefone', 'type' => 'text'),
        array('key' => 'documento', 'label' => 'CPF/CNPJ', 'type' => 'text'),
        array('key' => 'endereco', 'label' => 'Endereço', 'type' => 'text'),
        array('key' => 'cidade', 'label' => 'Cidade', 'type' => 'text'),
        array('key' => 'uf', 'label' => 'UF', 'type' => 'text'),
        array('key' => 'ativo', 'label' => 'Ativo', 'type' => 'checkbox'),
    );

    protected function bloqueio_apagar($id)
    {
        if ($this->Cadastro_model->em_uso_por('vendas', 'cliente_id', $id)) {
            return 'Este cliente possui vendas registradas e não pode ser excluído.';
        }
        return false;
    }
}
