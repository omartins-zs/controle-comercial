<?php

namespace Tests;

use Tests\Support\Db;

class ProdutosEstoqueTest extends IntegrationTestCase
{
    public function testCriarProdutoComEstoqueInicialLogaMovimentoDeEntrada()
    {
        $this->loginComoAdmin();
        $nome = $this->nomeUnico('Produto');

        $resp = $this->http->post('produtos/add', array(
            'nome' => $nome,
            'preco' => '19.90',
            'estoque_qtd' => '15',
        ));
        $this->assertFalse($resp->temErroPhp(), $resp->body);

        $produto = Db::linha('SELECT id, estoque_qtd FROM produtos WHERE nome = ?', array($nome));
        $this->assertNotNull($produto);
        $this->assertSame(15, (int) $produto->estoque_qtd);

        $mov = Db::linha(
            "SELECT * FROM estoque_movimentos WHERE produto_id = ? AND motivo = 'Estoque inicial do cadastro'",
            array($produto->id)
        );
        $this->assertNotNull($mov, 'Deveria existir um movimento de entrada para o estoque inicial');
        $this->assertSame('entrada', $mov->tipo);
        $this->assertSame(15, (int) $mov->quantidade);
        $this->assertSame(15, (int) $mov->estoque_resultante);

        return $produto->id;
    }

    public function testAjusteManualDeEstoqueEntradaESaidaFicamNoHistorico()
    {
        $this->loginComoAdmin();
        $nome = $this->nomeUnico('Produto');
        $this->http->post('produtos/add', array('nome' => $nome, 'preco' => '10', 'estoque_qtd' => '20'));
        $produto = Db::linha('SELECT id FROM produtos WHERE nome = ?', array($nome));

        // Entrada manual (+7)
        $respEntrada = $this->http->post('produtos/movimentar/' . $produto->id, array(
            'tipo' => 'entrada',
            'quantidade' => '7',
            'motivo' => 'Compra de reposição (teste automatizado)',
        ));
        $this->assertFalse($respEntrada->temErroPhp(), $respEntrada->body);

        $apos1 = Db::linha('SELECT estoque_qtd FROM produtos WHERE id = ?', array($produto->id));
        $this->assertSame(27, (int) $apos1->estoque_qtd);

        // Saída manual (-5)
        $respSaida = $this->http->post('produtos/movimentar/' . $produto->id, array(
            'tipo' => 'saida',
            'quantidade' => '5',
            'motivo' => 'Perda/quebra (teste automatizado)',
        ));
        $this->assertFalse($respSaida->temErroPhp(), $respSaida->body);

        $apos2 = Db::linha('SELECT estoque_qtd FROM produtos WHERE id = ?', array($produto->id));
        $this->assertSame(22, (int) $apos2->estoque_qtd);

        $historico = Db::linhas('SELECT tipo, quantidade, estoque_resultante FROM estoque_movimentos WHERE produto_id = ? ORDER BY id ASC', array($produto->id));
        $this->assertCount(3, $historico, 'Esperado: entrada inicial + entrada manual + saída manual');
        $this->assertSame('entrada', $historico[1]->tipo);
        $this->assertSame(27, (int) $historico[1]->estoque_resultante);
        $this->assertSame('saida', $historico[2]->tipo);
        $this->assertSame(22, (int) $historico[2]->estoque_resultante);
    }

    public function testEditarQuantidadeDireitoNoCadastroTambemLogaAjuste()
    {
        $this->loginComoAdmin();
        $nome = $this->nomeUnico('Produto');
        $this->http->post('produtos/add', array('nome' => $nome, 'preco' => '10', 'estoque_qtd' => '10'));
        $produto = Db::linha('SELECT id FROM produtos WHERE nome = ?', array($nome));

        $resp = $this->http->post('produtos/editar/' . $produto->id, array(
            'nome' => $nome,
            'preco' => '10',
            'estoque_qtd' => '3', // baixou de 10 para 3 — diferença de -7
        ));
        $this->assertFalse($resp->temErroPhp(), $resp->body);

        $mov = Db::linha(
            "SELECT * FROM estoque_movimentos WHERE produto_id = ? AND motivo = 'Ajuste manual via edição de cadastro'",
            array($produto->id)
        );
        $this->assertNotNull($mov, 'Editar a quantidade direto no cadastro deveria logar um ajuste');
        $this->assertSame(7, (int) $mov->quantidade);
        $this->assertSame(3, (int) $mov->estoque_resultante);
    }

    public function testNaoDeixaApagarProdutoComHistoricoDeMovimentacao()
    {
        $this->loginComoAdmin();
        $nome = $this->nomeUnico('Produto');
        $this->http->post('produtos/add', array('nome' => $nome, 'preco' => '10', 'estoque_qtd' => '5'));
        $produto = Db::linha('SELECT id FROM produtos WHERE nome = ?', array($nome));

        $resp = $this->http->get('produtos/apagar/' . $produto->id);

        $this->assertStringContainsString('não pode ser excluído', $resp->body);
        $this->assertNotNull(Db::linha('SELECT id FROM produtos WHERE id = ?', array($produto->id)));
    }
}
