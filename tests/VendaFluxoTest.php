<?php

namespace Tests;

use Tests\Support\Db;

class VendaFluxoTest extends IntegrationTestCase
{
    private function criarClienteEProduto($estoqueInicial = 20)
    {
        $nomeCliente = $this->nomeUnico('Cliente');
        $nomeProduto = $this->nomeUnico('Produto');

        $this->http->post('clientes/add', array('nome' => $nomeCliente));
        $this->http->post('produtos/add', array('nome' => $nomeProduto, 'preco' => '25.00', 'estoque_qtd' => (string) $estoqueInicial));

        $cliente = Db::linha('SELECT id FROM clientes WHERE nome = ?', array($nomeCliente));
        $produto = Db::linha('SELECT id FROM produtos WHERE nome = ?', array($nomeProduto));

        return array($cliente->id, $produto->id);
    }

    public function testVendaConcluidaBaixaEstoqueELogaMovimento()
    {
        $this->loginComoAdmin();
        list($clienteId, $produtoId) = $this->criarClienteEProduto(20);

        $resp = $this->http->post('vendas/add', array(
            'cliente_id' => $clienteId,
            'data_venda' => date('Y-m-d'),
            'status' => 'concluida',
            'produto_id' => array($produtoId),
            'quantidade' => array(4),
        ));
        $this->assertFalse($resp->temErroPhp(), $resp->body);

        $produto = Db::linha('SELECT estoque_qtd FROM produtos WHERE id = ?', array($produtoId));
        $this->assertSame(16, (int) $produto->estoque_qtd, 'Venda concluída deveria ter baixado 4 unidades do estoque');

        $venda = Db::linha('SELECT id, total FROM vendas WHERE cliente_id = ? ORDER BY id DESC LIMIT 1', array($clienteId));
        $this->assertSame('100.00', $venda->total, 'Total deveria ser 4 x 25.00 = 100.00');

        $mov = Db::linha("SELECT * FROM estoque_movimentos WHERE referencia_tipo = 'venda' AND referencia_id = ?", array($venda->id));
        $this->assertNotNull($mov);
        $this->assertSame('saida', $mov->tipo);
        $this->assertSame(4, (int) $mov->quantidade);

        // Comprovante deve abrir sem erro (não é NF-e, mas tem que renderizar)
        $recibo = $this->http->get('vendas/recibo/' . $venda->id);
        $this->assertFalse($recibo->temErroPhp(), $recibo->body);
        $this->assertStringContainsString('não possui valor fiscal', $recibo->body);
    }

    public function testOrcamentoNaoBaixaEstoque()
    {
        $this->loginComoAdmin();
        list($clienteId, $produtoId) = $this->criarClienteEProduto(10);

        $this->http->post('vendas/add', array(
            'cliente_id' => $clienteId,
            'data_venda' => date('Y-m-d'),
            'status' => 'orcamento',
            'produto_id' => array($produtoId),
            'quantidade' => array(3),
        ));

        $produto = Db::linha('SELECT estoque_qtd FROM produtos WHERE id = ?', array($produtoId));
        $this->assertSame(10, (int) $produto->estoque_qtd, 'Orçamento não deveria alterar o estoque');
    }

    public function testApagarVendaConcluidaDevolveEstoque()
    {
        $this->loginComoAdmin();
        list($clienteId, $produtoId) = $this->criarClienteEProduto(10);

        $this->http->post('vendas/add', array(
            'cliente_id' => $clienteId,
            'data_venda' => date('Y-m-d'),
            'status' => 'concluida',
            'produto_id' => array($produtoId),
            'quantidade' => array(6),
        ));
        $venda = Db::linha('SELECT id FROM vendas WHERE cliente_id = ? ORDER BY id DESC LIMIT 1', array($clienteId));
        $this->assertSame(4, (int) Db::linha('SELECT estoque_qtd FROM produtos WHERE id = ?', array($produtoId))->estoque_qtd);

        $resp = $this->http->get('vendas/apagar/' . $venda->id);
        $this->assertFalse($resp->temErroPhp(), $resp->body);

        $produto = Db::linha('SELECT estoque_qtd FROM produtos WHERE id = ?', array($produtoId));
        $this->assertSame(10, (int) $produto->estoque_qtd, 'Apagar a venda deveria devolver as 6 unidades ao estoque');
        $this->assertNull(Db::linha('SELECT id FROM vendas WHERE id = ?', array($venda->id)));
    }
}
