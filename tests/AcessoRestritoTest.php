<?php

namespace Tests;

class AcessoRestritoTest extends IntegrationTestCase
{
    public function testUsuarioDoGrupoVendedoresNaoAcessaGestaoDeUsuarios()
    {
        $email = $this->emailUnico('vendedor');
        $httpVendedor = $this->criarUsuarioEDeslogar(2, $email); // 2 = Vendedores

        $resp = $httpVendedor->get('usuarios');

        $this->assertStringContainsString('precisa ser um administrador', $resp->body);
        $this->assertStringContainsString('/home', $resp->caminhoFinal());

        $this->apagarUsuarioPorEmail($email);
    }

    public function testUsuarioDoGrupoVendedoresAcessaModulosComuns()
    {
        $email = $this->emailUnico('vendedor');
        $httpVendedor = $this->criarUsuarioEDeslogar(2, $email);

        $resp = $httpVendedor->get('clientes');

        $this->assertFalse($resp->temErroPhp(), $resp->body);
        $this->assertStringContainsString('Clientes', $resp->body);
        $this->assertStringNotContainsString('id="form-login"', $resp->body, 'Usuário deveria conseguir acessar módulos comuns');

        $this->apagarUsuarioPorEmail($email);
    }

    public function testAdministradorAcessaGestaoDeUsuarios()
    {
        $this->loginComoAdmin();
        $resp = $this->http->get('usuarios');

        $this->assertFalse($resp->temErroPhp(), $resp->body);
        $this->assertStringContainsString('admin@admin.com', $resp->body);
    }
}
