<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Model genérico de CRUD simples, usado pelos módulos de cadastro
 * (Clientes, Fornecedores, Vendedores, Transportadoras, Categorias, Marcas).
 * Cada controller instancia com a tabela correspondente:
 *   $this->load->model('Cadastro_model');
 *   $this->Cadastro_model->set_table('clientes');
 */
class Cadastro_model extends CI_Model
{
    protected $table;

    public function set_table($table)
    {
        $this->table = $table;
        return $this;
    }

    public function get_all($order_by = 'nome', $only_active = false)
    {
        $this->db->select('*')->from($this->table);
        if ($only_active) {
            $this->db->where('ativo', 1);
        }
        return $this->db->order_by($order_by, 'ASC')->get()->result();
    }

    public function get($id)
    {
        return $this->db->where('id', $id)->get($this->table)->row();
    }

    public function insert($data)
    {
        return $this->db->insert($this->table, $data);
    }

    /**
     * Como insert(), mas devolve o id gerado (útil quando o controller
     * precisa dele na sequência, ex.: logar um movimento de estoque ligado
     * ao produto recém-criado).
     */
    public function insert_get_id($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        return $this->db->where('id', $id)->update($this->table, $data);
    }

    public function delete($id)
    {
        return $this->db->where('id', $id)->delete($this->table);
    }

    public function em_uso_por($fk_table, $fk_column, $id)
    {
        // Usado para impedir exclusão de um registro que é referenciado em outra tabela
        // (ex.: não deixar apagar uma categoria com produtos vinculados).
        return $this->db->where($fk_column, $id)->count_all_results($fk_table) > 0;
    }
}
