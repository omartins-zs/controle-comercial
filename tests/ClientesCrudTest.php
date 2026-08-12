<?php

namespace Tests;

use Tests\Support\Db;

class ClientesCrudTest extends IntegrationTestCase
{
    public function testCicloCompletoCriarEditarApagar()
    {
        $this->loginComoAdmin();
        $nome = $this->nomeUnico('Cliente');
        $nomeEditado = $nome . ' (editado)';

        // Criar
        $respCriar = $this->http->post('clientes/add', array(
            'nome' => $nome,
            'email' => 'cliente@teste.local',
            'telefone' => '11999990000',
            'documento' => '111.111.111-11',
            'cidade' => 'São Paulo',
            'uf' => 'SP',
        ));
        $this->assertFalse($respCriar->temErroPhp(), $respCriar->body);
        $this->assertStringContainsString($nome, $respCriar->body, 'Cliente recém-criado deveria aparecer na listagem');

        $cliente = Db::linha('SELECT id FROM clientes WHERE nome = ?', array($nome));
        $this->assertNotNull($cliente, 'Cliente deveria existir no banco após o cadastro');
        $id = $cliente->id;

        // Editar
        $respEditar = $this->http->post('clientes/editar/' . $id, array(
            'nome' => $nomeEditado,
            'email' => 'cliente@teste.local',
            'telefone' => '11999990000',
            'documento' => '111.111.111-11',
            'cidade' => 'Rio de Janeiro',
            'uf' => 'RJ',
        ));
        $this->assertFalse($respEditar->temErroPhp(), $respEditar->body);
        $this->assertStringContainsString($nomeEditado, $respEditar->body);

        $atualizado = Db::linha('SELECT nome, cidade FROM clientes WHERE id = ?', array($id));
        $this->assertSame($nomeEditado, $atualizado->nome);
        $this->assertSame('Rio de Janeiro', $atualizado->cidade);

        // Apagar
        $respApagar = $this->http->get('clientes/apagar/' . $id);
        $this->assertFalse($respApagar->temErroPhp(), $respApagar->body);
        $this->assertNull(Db::linha('SELECT id FROM clientes WHERE id = ?', array($id)), 'Cliente deveria ter sido removido');
    }

    public function testNaoDeixaApagarClienteComVendaAssociada()
    {
        $this->loginComoAdmin();

        // Cliente #1 do seed (docker/mysql-init/02-modulos-comerciais.sql)
        // tem vendas associadas — não deve ser possível excluir.
        $antes = Db::linha('SELECT id FROM clientes WHERE id = 1');
        $this->assertNotNull($antes, 'Este teste espera o cliente id=1 do seed — banco não está com os dados de teste padrão?');

        $resp = $this->http->get('clientes/apagar/1');

        $this->assertStringContainsString('não pode ser excluído', $resp->body);
        $this->assertNotNull(Db::linha('SELECT id FROM clientes WHERE id = 1'), 'Cliente com venda associada não deveria ter sido removido');
    }
}
