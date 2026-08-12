<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . 'core/Cadastro_base.php';

class Categorias extends Cadastro_base
{
    protected $table = 'categorias';
    protected $rota = 'categorias';
    protected $titulo_singular = 'categoria';
    protected $titulo_plural = 'Categorias';

    protected $campos_lista = array();

    protected $campos_form = array(
        array('key' => 'nome', 'label' => 'Nome', 'type' => 'text', 'rules' => 'required|min_length[2]'),
        array('key' => 'ativo', 'label' => 'Ativo', 'type' => 'checkbox'),
    );

    protected function bloqueio_apagar($id)
    {
        if ($this->Cadastro_model->em_uso_por('produtos', 'categoria_id', $id)) {
            return 'Esta categoria possui produtos vinculados e não pode ser excluída.';
        }
        return false;
    }
}
