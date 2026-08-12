<?php

/**
 * Bootstrap dos testes de integração. Importante: estes testes NÃO
 * carregam o CodeIgniter em processo — eles batem via HTTP no app já
 * publicado pelo Docker (ver tests/Support/Http.php) e conferem o
 * resultado direto no MySQL (tests/Support/Db.php). Por isso funcionam com
 * o PHPUnit/PHP local (8.3, via Composer) mesmo a aplicação rodando em
 * PHP 7.4 dentro do container — nenhum código do CodeIgniter é incluído
 * aqui, só chamadas de rede.
 *
 * Pré-requisito: `docker compose up -d` já rodando (ver docs/ACESSOS_TESTES.md).
 */

require __DIR__ . '/../vendor/autoload.php';

// Limpa sobra de dados de uma execução anterior que tenha sido interrompida
// no meio (ex.: Ctrl+C) — garante que a suíte sempre começa de um estado
// limpo, sem herdar lixo de "Produto PHPUnit ..." de uma rodada passada.
\Tests\Support\Cleanup::tudo();
