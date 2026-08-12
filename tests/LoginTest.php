<?php

namespace Tests;

class LoginTest extends IntegrationTestCase
{
    public function testAcessarPaginaProtegidaSemLoginRedirecionaParaLogin()
    {
        $resp = $this->http->get('clientes');
        $this->assertStringContainsString('/login', $resp->caminhoFinal());
        $this->assertStringContainsString('id="form-login"', $resp->body);
    }

    public function testLoginComCredenciaisInvalidasMostraErroENaoEntra()
    {
        $resp = $this->http->post('login', array(
            'login' => 'admin@admin.com',
            'senha' => 'senha-errada-de-proposito',
        ));

        $this->assertStringContainsString('Os dados de acesso estão incorretos', $resp->body);
        $this->assertStringContainsString('id="form-login"', $resp->body);
    }

    public function testLoginComCredenciaisValidasEntraNaHome()
    {
        $resp = $this->loginComoAdmin();

        $this->assertStringContainsString('/home', $resp->caminhoFinal());
        $this->assertStringContainsString('Visão Geral', $resp->body);
        $this->assertFalse($resp->temErroPhp(), 'Home não deveria ter erro de PHP: ' . $resp->body);
    }

    public function testAcessarLoginJaLogadoRedirecionaParaHomeEmVezDeMostrarFormulario()
    {
        $this->loginComoAdmin();

        $resp = $this->http->get('login');

        $this->assertStringContainsString('/home', $resp->caminhoFinal());
        $this->assertStringNotContainsString('id="form-login"', $resp->body);
    }

    public function testLogoutVoltaAExigirLogin()
    {
        $this->loginComoAdmin();
        $this->http->get('login/logout');

        $resp = $this->http->get('clientes');
        $this->assertStringContainsString('/login', $resp->caminhoFinal());
    }
}
