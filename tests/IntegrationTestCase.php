<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Tests\Support\Http;
use Tests\Support\Db;

/**
 * Base comum dos testes de integração: sobem um cliente HTTP novo por
 * teste (sem cookies compartilhados entre testes, pra não vazar sessão de
 * um teste pro outro) e dão acesso a helpers de login/limpeza.
 */
abstract class IntegrationTestCase extends TestCase
{
    /** @var Http */
    protected $http;

    protected function setUp(): void
    {
        parent::setUp();
        $this->http = new Http();
    }

    protected function loginComoAdmin()
    {
        $resp = $this->http->post('login', array(
            'login' => 'admin@admin.com',
            'senha' => 'password',
        ));
        $this->assertStringNotContainsString('Os dados de acesso estão incorretos', $resp->body, 'Login do admin de teste falhou — confira docs/ACESSOS_TESTES.md');
        return $resp;
    }

    /**
     * Cria (via HTTP, como admin) um usuário de teste no grupo informado e
     * devolve um Http já logado como ele. group 1 = Administrador, 2 =
     * Vendedores (ver docs/ACESSOS_TESTES.md).
     */
    protected function criarUsuarioEDeslogar($grupoId, $emailUnico, $senha = 'senha12345')
    {
        $this->loginComoAdmin();
        $this->http->post('usuarios/add', array(
            'nome_usuario' => 'teste_' . substr(md5($emailUnico), 0, 8),
            'email_usuario' => $emailUnico,
            'senha_usuario' => $senha,
            'senha_usuario2' => $senha,
            'tipo_usuario' => $grupoId,
        ));

        $novoHttp = new Http();
        $novoHttp->post('login', array('login' => $emailUnico, 'senha' => $senha));
        return $novoHttp;
    }

    protected function apagarUsuarioPorEmail($email)
    {
        $user = Db::linha('SELECT id FROM users WHERE email = ?', array($email));
        if ($user) {
            $this->loginComoAdmin();
            $this->http->post('usuarios/apagar/' . $user->id, array());
        }
    }

    protected function emailUnico($prefixo)
    {
        return $prefixo . '_' . bin2hex(random_bytes(4)) . '@teste.local';
    }

    protected function nomeUnico($prefixo)
    {
        return $prefixo . ' PHPUnit ' . bin2hex(random_bytes(4));
    }
}
