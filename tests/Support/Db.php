<?php

namespace Tests\Support;

use PDO;

/**
 * Acesso direto ao MySQL do Docker (porta exposta no host) — usado pelos
 * testes pra verificar o EFEITO real das ações feitas via HTTP (ex.: "a
 * venda baixou o estoque mesmo?"), e não só o retorno HTTP em si.
 */
class Db
{
    private static $pdo;

    public static function conexao()
    {
        if (!self::$pdo) {
            $host = getenv('APP_TEST_DB_HOST') ?: '127.0.0.1';
            $port = getenv('APP_TEST_DB_PORT') ?: '3307';
            $nome = getenv('APP_TEST_DB_NAME') ?: 'blog_comercial';
            $user = getenv('APP_TEST_DB_USER') ?: 'root';
            $pass = getenv('APP_TEST_DB_PASS') ?: 'root';

            self::$pdo = new PDO(
                "mysql:host={$host};port={$port};dbname={$nome};charset=utf8mb4",
                $user,
                $pass,
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
            );
        }
        return self::$pdo;
    }

    public static function linha($sql, array $params = array())
    {
        $stmt = self::conexao()->prepare($sql);
        $stmt->execute($params);
        $r = $stmt->fetch(PDO::FETCH_OBJ);
        return $r ?: null;
    }

    public static function linhas($sql, array $params = array())
    {
        $stmt = self::conexao()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public static function executar($sql, array $params = array())
    {
        $stmt = self::conexao()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public static function ultimoId()
    {
        return (int) self::conexao()->lastInsertId();
    }
}
