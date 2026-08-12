<?php

namespace Tests;

use Tests\Support\Db;

class OrdemServicoFluxoTest extends IntegrationTestCase
{
    private function criarClienteEProduto($estoqueInicial = 20)
    {
        $nomeCliente = $this->nomeUnico('Cliente');
        $nomeProduto = $this->nomeUnico('Produto');

        $this->http->post('clientes/add', array('nome' => $nomeCliente));
        $this->http->post('produtos/add', array('nome' => $nomeProduto, 'preco' => '40.00', 'estoque_qtd' => (string) $estoqueInicial));

        $cliente = Db::linha('SELECT id FROM clientes WHERE nome = ?', array($nomeCliente));
        $produto = Db::linha('SELECT id FROM produtos WHERE nome = ?', array($nomeProduto));

        return array($cliente->id, $produto->id);
    }

    private function criarOsAberta($clienteId, $produtoId, $qtdPeca = 2)
    {
        $this->http->post('ordens_servico/add', array(
            'cliente_id' => $clienteId,
            'data_abertura' => date('Y-m-d'),
            'status' => 'aberta',
            'descricao_problema' => 'Teste automatizado',
            'tipo_item' => array('servico', 'peca'),
            'descricao_item' => array('Mão de obra (teste)', ''),
            'valor_unitario' => array('50', '0'),
            'produto_id' => array('', $produtoId),
            'quantidade' => array(1, $qtdPeca),
        ));

        return Db::linha('SELECT * FROM ordens_servico WHERE cliente_id = ? ORDER BY id DESC LIMIT 1', array($clienteId));
    }

    public function testAbrirOsNaoBaixaEstoqueSoQuandoConcluiBaixa()
    {
        $this->loginComoAdmin();
        list($clienteId, $produtoId) = $this->criarClienteEProduto(20);

        $os = $this->criarOsAberta($clienteId, $produtoId, 3);
        $this->assertNotNull($os);
        $this->assertSame('0', (string) $os->estoque_baixado);
        $this->assertSame('170.00', $os->total, '1x mão de obra (50) + 3x peça a 40 cada (preço vem do produto, não do POST) = 50 + 120');
        $this->assertSame(20, (int) Db::linha('SELECT estoque_qtd FROM produtos WHERE id = ?', array($produtoId))->estoque_qtd, 'Abrir a OS não deveria alterar o estoque ainda');

        // Concluir -> baixa as peças
        $respStatus = $this->http->post('ordens_servico/ver/' . $os->id, array('novo_status' => 'concluida'));
        $this->assertFalse($respStatus->temErroPhp(), $respStatus->body);

        $produtoDepois = Db::linha('SELECT estoque_qtd FROM produtos WHERE id = ?', array($produtoId));
        $this->assertSame(17, (int) $produtoDepois->estoque_qtd, 'Concluir a OS deveria baixar as 3 peças do estoque');

        $osAtualizada = Db::linha('SELECT estoque_baixado FROM ordens_servico WHERE id = ?', array($os->id));
        $this->assertSame('1', (string) $osAtualizada->estoque_baixado);

        // Reabrir -> devolve
        $this->http->post('ordens_servico/ver/' . $os->id, array('novo_status' => 'aberta'));
        $produtoReaberta = Db::linha('SELECT estoque_qtd FROM produtos WHERE id = ?', array($produtoId));
        $this->assertSame(20, (int) $produtoReaberta->estoque_qtd, 'Reabrir a OS deveria devolver as peças ao estoque');

        // Comprovante abre sem erro
        $recibo = $this->http->get('ordens_servico/recibo/' . $os->id);
        $this->assertFalse($recibo->temErroPhp(), $recibo->body);
        $this->assertStringContainsString('não possui valor fiscal', $recibo->body);
    }

    public function testCriarOsJaConcluidaBaixaEstoqueNaHora()
    {
        $this->loginComoAdmin();
        list($clienteId, $produtoId) = $this->criarClienteEProduto(10);

        $this->http->post('ordens_servico/add', array(
            'cliente_id' => $clienteId,
            'data_abertura' => date('Y-m-d'),
            'status' => 'concluida',
            'tipo_item' => array('peca'),
            'descricao_item' => array(''),
            'valor_unitario' => array('0'),
            'produto_id' => array($produtoId),
            'quantidade' => array(2),
        ));

        $produto = Db::linha('SELECT estoque_qtd FROM produtos WHERE id = ?', array($produtoId));
        $this->assertSame(8, (int) $produto->estoque_qtd, 'OS criada já como concluída deveria baixar o estoque imediatamente');
    }

    public function testApagarOsConcluidaDevolveEstoque()
    {
        $this->loginComoAdmin();
        list($clienteId, $produtoId) = $this->criarClienteEProduto(10);
        $os = $this->criarOsAberta($clienteId, $produtoId, 2);
        $this->http->post('ordens_servico/ver/' . $os->id, array('novo_status' => 'concluida'));
        $this->assertSame(8, (int) Db::linha('SELECT estoque_qtd FROM produtos WHERE id = ?', array($produtoId))->estoque_qtd);

        $resp = $this->http->get('ordens_servico/apagar/' . $os->id);
        $this->assertFalse($resp->temErroPhp(), $resp->body);

        $produto = Db::linha('SELECT estoque_qtd FROM produtos WHERE id = ?', array($produtoId));
        $this->assertSame(10, (int) $produto->estoque_qtd, 'Apagar a OS concluída deveria devolver as peças ao estoque');
        $this->assertNull(Db::linha('SELECT id FROM ordens_servico WHERE id = ?', array($os->id)));
    }
}
