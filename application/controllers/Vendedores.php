<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . 'core/Cadastro_base.php';

/**
 * Cadastro de Vendedores (entidade comercial, distinta dos usuários de login
 * gerenciados em Usuarios.php/Ion Auth). Usado para atribuir vendas e calcular
 * comissão — não tem relação com acesso ao sistema.
 */
class Vendedores extends Cadastro_base
{
    protected $table = 'vendedores';
    protected $rota = 'vendedores';
    protected $titulo_singular = 'vendedor';
    protected $titulo_plural = 'Vendedores';

    protected $campos_lista = array(
        array('key' => 'email', 'label' => 'E-mail'),
        array('key' => 'telefone', 'label' => 'Telefone'),
        array('key' => 'comissao_percentual', 'label' => 'Comissão (%)'),
    );

    protected $campos_form = array(
        array('key' => 'nome', 'label' => 'Nome', 'type' => 'text', 'rules' => 'required|min_length[3]'),
        array('key' => 'email', 'label' => 'E-mail', 'type' => 'email', 'rules' => 'valid_email'),
        array('key' => 'telefone', 'label' => 'Telefone', 'type' => 'text'),
        array('key' => 'comissao_percentual', 'label' => 'Comissão (%)', 'type' => 'number', 'rules' => 'numeric'),
        array('key' => 'ativo', 'label' => 'Ativo', 'type' => 'checkbox'),
    );

    protected function bloqueio_apagar($id)
    {
        if ($this->Cadastro_model->em_uso_por('vendas', 'vendedor_id', $id)) {
            return 'Este vendedor possui vendas registradas e não pode ser excluído.';
        }
        return false;
    }
}
