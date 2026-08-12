<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . 'core/Cadastro_base.php';

class Marcas extends Cadastro_base
{
    protected $table = 'marcas';
    protected $rota = 'marcas';
    protected $titulo_singular = 'marca';
    protected $titulo_plural = 'Marcas';

    protected $campos_lista = array();

    protected $campos_form = array(
        array('key' => 'nome', 'label' => 'Nome', 'type' => 'text', 'rules' => 'required|min_length[2]'),
        array('key' => 'ativo', 'label' => 'Ativo', 'type' => 'checkbox'),
    );

    protected function bloqueio_apagar($id)
    {
        if ($this->Cadastro_model->em_uso_por('produtos', 'marca_id', $id)) {
            return 'Esta marca possui produtos vinculados e não pode ser excluída.';
        }
        return false;
    }
}
