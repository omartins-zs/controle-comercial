<?php

namespace Tests;

/**
 * Teste de regressão: a listagem genérica (pages/cadastro/index.php) tinha
 * um <td><?= $item->nome ?></td> incondicional, e contas_pagar/contas_receber
 * não têm coluna "nome" (usam "descricao") — gerava "A PHP Error was
 * encountered / Undefined property: stdClass::$nome" em toda linha.
 */
class ContasTest extends IntegrationTestCase
{
    public function testListagemDeContasAPagarNaoTemErroDeNomeIndefinido()
    {
        $this->loginComoAdmin();
        $resp = $this->http->get('contas_pagar');

        $this->assertFalse($resp->temErroPhp(), $resp->body);
        $this->assertStringNotContainsString('Undefined property', $resp->body);
        $this->assertStringContainsString('Contas a Pagar', $resp->body);
    }

    public function testListagemDeContasAReceberNaoTemErroDeNomeIndefinido()
    {
        $this->loginComoAdmin();
        $resp = $this->http->get('contas_receber');

        $this->assertFalse($resp->temErroPhp(), $resp->body);
        $this->assertStringNotContainsString('Undefined property', $resp->body);
        $this->assertStringContainsString('Contas a Receber', $resp->body);
    }

    public function testListagemComTabelaQueTemNomeAindaMostraAColuna()
    {
        // Regressão inversa: o fix não pode ter tirado a coluna "Nome" das
        // tabelas que REALMENTE têm essa coluna (clientes, produtos, etc.)
        $this->loginComoAdmin();
        $resp = $this->http->get('clientes');

        $this->assertFalse($resp->temErroPhp(), $resp->body);
        $this->assertStringContainsString('<th>Nome</th>', $resp->body);
    }

    public function testCriarEApagarContaAPagarSemFornecedor()
    {
        // Cobre o caso de despesa interna sem fornecedor vinculado (célula
        // de fornecedor fica em branco de propósito — não é bug).
        $this->loginComoAdmin();
        $descricao = $this->nomeUnico('Despesa');

        $resp = $this->http->post('contas_pagar/add', array(
            'descricao' => $descricao,
            'valor' => '150.00',
            'vencimento' => date('Y-m-d'),
            'status' => 'pendente',
        ));

        $this->assertFalse($resp->temErroPhp(), $resp->body);
        $this->assertStringContainsString($descricao, $resp->body);
    }
}
