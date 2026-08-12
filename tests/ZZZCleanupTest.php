<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Tests\Support\Cleanup;
use Tests\Support\Db;

/**
 * "ZZZ" no nome é de propósito: PHPUnit varre os arquivos em ordem
 * alfabética, então este roda por último e devolve o banco de
 * desenvolvimento limpo (sem os registros "... PHPUnit ..." criados pelos
 * testes anteriores) ao final de uma execução completa da suíte.
 */
class ZZZCleanupTest extends TestCase
{
    public function testLimpaTudoQueASuiteCriou()
    {
        Cleanup::tudo();

        $this->assertNull(Db::linha("SELECT id FROM produtos WHERE nome LIKE '%PHPUnit%'"));
        $this->assertNull(Db::linha("SELECT id FROM clientes WHERE nome LIKE '%PHPUnit%'"));
        $this->assertNull(Db::linha("SELECT id FROM users WHERE email LIKE '%@teste.local'"));
    }
}
