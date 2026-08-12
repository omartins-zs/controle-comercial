<?php

namespace Tests;

class RelatoriosTest extends IntegrationTestCase
{
    /** @dataProvider rotasDeRelatorio */
    public function testRelatorioAbreSemErro($rota, $tituloEsperado)
    {
        $this->loginComoAdmin();
        $resp = $this->http->get($rota);

        $this->assertFalse($resp->temErroPhp(), "{$rota}: " . $resp->body);
        $this->assertStringContainsString($tituloEsperado, $resp->body);
    }

    public function rotasDeRelatorio()
    {
        return array(
            'clientes' => array('relatorios/clientes', 'Relatório de Clientes'),
            'produtos' => array('relatorios/produtos', 'Relatório de Produtos'),
            'vendas' => array('relatorios/vendas', 'Relatório de Vendas'),
            'ordens_servico' => array('relatorios/ordens_servico', 'Relatório de Ordens de Serviço'),
            'estoque' => array('relatorios/estoque', 'Histórico de Movimentação de Estoque'),
            'contas_pagar' => array('relatorios/contas_pagar', 'Relatório de Contas a Pagar'),
            'contas_receber' => array('relatorios/contas_receber', 'Relatório de Contas a Receber'),
        );
    }

    public function testNenhumLinkDoMenuPrincipalAindaApontaParaPlaceholder()
    {
        // Regressão: o menu tinha vários href="#" de módulos "não implementados".
        $this->loginComoAdmin();
        $resp = $this->http->get('home');

        $this->assertStringNotContainsString('Módulo ainda não implementado', $resp->body);
    }
}
