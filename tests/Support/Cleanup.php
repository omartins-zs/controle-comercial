<?php

namespace Tests\Support;

/**
 * Remove tudo que os testes criam (identificado pelo padrão de nome vindo
 * de IntegrationTestCase::nomeUnico()/emailUnico(), sempre contendo
 * "PHPUnit" ou "@teste.local") — na ordem certa pra não bater em FK.
 * Roda uma vez antes da suíte (bootstrap.php, limpa sobra de uma execução
 * anterior que tenha falhado no meio) e uma vez depois (ZZZCleanupTest,
 * o último arquivo em ordem alfabética) pra devolver o banco de dev limpo.
 */
class Cleanup
{
    public static function tudo()
    {
        $db = Db::conexao();

        $clientesTeste = 'SELECT id FROM clientes WHERE nome LIKE \'%PHPUnit%\'';
        $produtosTeste = 'SELECT id FROM produtos WHERE nome LIKE \'%PHPUnit%\'';

        // Pedidos/vendas de clientes de teste (e os itens deles) primeiro.
        $db->exec("DELETE FROM ordem_servico_itens WHERE ordem_id IN (SELECT id FROM ordens_servico WHERE cliente_id IN ($clientesTeste))");
        $db->exec("DELETE FROM ordens_servico WHERE cliente_id IN ($clientesTeste)");
        $db->exec("DELETE FROM venda_itens WHERE venda_id IN (SELECT id FROM vendas WHERE cliente_id IN ($clientesTeste))");
        $db->exec("DELETE FROM vendas WHERE cliente_id IN ($clientesTeste)");

        // Histórico de estoque dos produtos de teste, depois os produtos.
        $db->exec("DELETE FROM estoque_movimentos WHERE produto_id IN ($produtosTeste)");
        $db->exec("DELETE FROM produtos WHERE nome LIKE '%PHPUnit%'");

        // Contas a pagar/receber e clientes/fornecedores de teste.
        $db->exec("DELETE FROM contas_receber WHERE cliente_id IN ($clientesTeste)");
        $db->exec("DELETE FROM clientes WHERE nome LIKE '%PHPUnit%'");
        $db->exec("DELETE FROM contas_pagar WHERE descricao LIKE '%PHPUnit%'");
        $db->exec("DELETE FROM fornecedores WHERE nome LIKE '%PHPUnit%'");

        // Usuários de login criados pelos testes de acesso.
        $idsUsuarios = $db->query("SELECT id FROM users WHERE email LIKE '%@teste.local'")->fetchAll(\PDO::FETCH_COLUMN);
        foreach ($idsUsuarios as $id) {
            $db->exec('DELETE FROM users_groups WHERE user_id = ' . (int) $id);
            $db->exec('DELETE FROM users WHERE id = ' . (int) $id);
        }
    }
}
