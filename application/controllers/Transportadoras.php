<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . 'core/Cadastro_base.php';

class Transportadoras extends Cadastro_base
{
    protected $table = 'transportadoras';
    protected $rota = 'transportadoras';
    protected $titulo_singular = 'transportadora';
    protected $titulo_plural = 'Transportadoras';

    protected $campos_lista = array(
        array('key' => 'documento', 'label' => 'CNPJ'),
        array('key' => 'telefone', 'label' => 'Telefone'),
    );

    protected $campos_form = array(
        array('key' => 'nome', 'label' => 'Nome / Razão Social', 'type' => 'text', 'rules' => 'required|min_length[3]'),
        array('key' => 'documento', 'label' => 'CNPJ', 'type' => 'text'),
        array('key' => 'telefone', 'label' => 'Telefone', 'type' => 'text'),
        array('key' => 'ativo', 'label' => 'Ativo', 'type' => 'checkbox'),
    );
}
