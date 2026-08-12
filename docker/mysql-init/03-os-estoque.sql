-- Ordem de Serviço + histórico de movimentação de estoque.
-- Executado automaticamente na primeira inicialização do volume. Para bancos
-- já existentes, rode manualmente:
-- docker exec -i <container_db> mysql --default-character-set=utf8mb4 -uroot -p<senha> blog_comercial < 03-os-estoque.sql
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `estoque_movimentos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `produto_id` INT UNSIGNED DEFAULT NULL,
  `tipo` ENUM('entrada','saida','ajuste') NOT NULL,
  `quantidade` INT NOT NULL,
  `motivo` VARCHAR(200) DEFAULT NULL,
  `referencia_tipo` VARCHAR(20) DEFAULT NULL,
  `referencia_id` INT UNSIGNED DEFAULT NULL,
  `estoque_resultante` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_mov_produto` (`produto_id`),
  CONSTRAINT `fk_mov_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ordens_servico` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `cliente_id` INT UNSIGNED DEFAULT NULL,
  `vendedor_id` INT UNSIGNED DEFAULT NULL,
  `descricao_problema` VARCHAR(255) DEFAULT NULL,
  `data_abertura` DATE NOT NULL,
  `data_conclusao` DATE DEFAULT NULL,
  `status` ENUM('aberta','em_andamento','concluida','cancelada') NOT NULL DEFAULT 'aberta',
  `estoque_baixado` TINYINT(1) NOT NULL DEFAULT 0,
  `total` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_os_cliente` (`cliente_id`),
  KEY `fk_os_vendedor` (`vendedor_id`),
  CONSTRAINT `fk_os_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_os_vendedor` FOREIGN KEY (`vendedor_id`) REFERENCES `vendedores` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ordem_servico_itens` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ordem_id` INT UNSIGNED NOT NULL,
  `tipo` ENUM('servico','peca') NOT NULL DEFAULT 'servico',
  `produto_id` INT UNSIGNED DEFAULT NULL,
  `descricao` VARCHAR(200) NOT NULL,
  `quantidade` INT NOT NULL DEFAULT 1,
  `valor_unitario` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `fk_ositem_ordem` (`ordem_id`),
  KEY `fk_ositem_produto` (`produto_id`),
  CONSTRAINT `fk_ositem_ordem` FOREIGN KEY (`ordem_id`) REFERENCES `ordens_servico` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ositem_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------------------
-- Dados fictícios (idempotente)
-- --------------------------------------------------------------------

INSERT INTO `ordens_servico` (`cliente_id`, `vendedor_id`, `descricao_problema`, `data_abertura`, `data_conclusao`, `status`, `estoque_baixado`, `total`)
SELECT * FROM (
  SELECT 2 AS cliente_id, 1 AS vendedor_id, 'Notebook não liga - suspeita de fonte' AS descricao_problema, CURDATE() - INTERVAL 6 DAY AS data_abertura, CURDATE() - INTERVAL 2 DAY AS data_conclusao, 'concluida' AS status, 1 AS estoque_baixado, 120.00 AS total UNION
  SELECT 3, 2, 'Instalação de rede interna no escritório', CURDATE() - INTERVAL 3 DAY, NULL, 'em_andamento', 0, 350.00 UNION
  SELECT 4, 3, 'Manutenção preventiva em impressoras', CURDATE() - INTERVAL 1 DAY, NULL, 'aberta', 0, 90.00
) AS t
WHERE NOT EXISTS (SELECT 1 FROM `ordens_servico` LIMIT 1);

INSERT INTO `ordem_servico_itens` (`ordem_id`, `tipo`, `produto_id`, `descricao`, `quantidade`, `valor_unitario`, `subtotal`)
SELECT * FROM (
  SELECT 1 AS ordem_id, 'servico' AS tipo, NULL AS produto_id, 'Diagnóstico e mão de obra' AS descricao, 1 AS quantidade, 80.00 AS valor_unitario, 80.00 AS subtotal UNION
  SELECT 1, 'peca', 2, 'Mouse sem fio (substituição)', 1, 40.00, 40.00 UNION
  SELECT 2, 'servico', NULL, 'Passagem de cabos e configuração de switch', 1, 350.00, 350.00 UNION
  SELECT 3, 'servico', NULL, 'Limpeza e troca de peças de desgaste', 1, 90.00, 90.00
) AS t
WHERE NOT EXISTS (SELECT 1 FROM `ordem_servico_itens` LIMIT 1);

-- Histórico de estoque coerente com o saldo ATUAL de cada produto (definido
-- em 02-modulos-comerciais.sql). Cada produto afetado por uma venda/OS
-- fictícia recebe uma "entrada" inicial anterior às saídas, de forma que a
-- aritmética (entrada - saídas = estoque_resultante final) bate com o valor
-- real gravado em produtos.estoque_qtd — evita um histórico que conta uma
-- história diferente do saldo que a tela de Produtos mostra.
INSERT INTO `estoque_movimentos` (`produto_id`, `tipo`, `quantidade`, `motivo`, `referencia_tipo`, `referencia_id`, `estoque_resultante`, `created_at`)
SELECT * FROM (
  -- Produto 1 (Notebook, atual=8): +9 estoque inicial, -1 venda#1 = 8
  SELECT 1 AS produto_id, 'entrada' AS tipo, 9 AS quantidade, 'Estoque inicial' AS motivo, 'manual' AS referencia_tipo, NULL AS referencia_id, 9 AS estoque_resultante, (CURDATE() - INTERVAL 25 DAY) AS created_at UNION
  SELECT 1, 'saida', 1, 'Venda #1', 'venda', 1, 8, (CURDATE() - INTERVAL 20 DAY) UNION
  -- Produto 2 (Mouse, atual=40): +43 estoque inicial, -1 venda#1, -1 venda#2, -1 OS#1 = 40
  SELECT 2, 'entrada', 43, 'Estoque inicial', 'manual', NULL, 43, (CURDATE() - INTERVAL 25 DAY) UNION
  SELECT 2, 'saida', 1, 'Venda #1', 'venda', 1, 42, (CURDATE() - INTERVAL 20 DAY) UNION
  SELECT 2, 'saida', 1, 'Venda #2', 'venda', 2, 41, (CURDATE() - INTERVAL 15 DAY) UNION
  SELECT 2, 'saida', 1, 'Ordem de Serviço #1 - peça', 'ordem_servico', 1, 40, (CURDATE() - INTERVAL 2 DAY) UNION
  -- Produto 4 (Monitor, atual=12): +13 estoque inicial, -1 venda#3 = 12
  SELECT 4, 'entrada', 13, 'Estoque inicial', 'manual', NULL, 13, (CURDATE() - INTERVAL 25 DAY) UNION
  SELECT 4, 'saida', 1, 'Venda #3', 'venda', 3, 12, (CURDATE() - INTERVAL 10 DAY) UNION
  -- Produto 7 (Cadeira, atual=15): +16 estoque inicial, -1 venda#4 = 15
  SELECT 7, 'entrada', 16, 'Estoque inicial', 'manual', NULL, 16, (CURDATE() - INTERVAL 25 DAY) UNION
  SELECT 7, 'saida', 1, 'Venda #4', 'venda', 4, 15, (CURDATE() - INTERVAL 7 DAY) UNION
  -- Produto 8 (Mesa, atual=6): +7 estoque inicial, -1 venda#4 = 6
  SELECT 8, 'entrada', 7, 'Estoque inicial', 'manual', NULL, 7, (CURDATE() - INTERVAL 25 DAY) UNION
  SELECT 8, 'saida', 1, 'Venda #4', 'venda', 4, 6, (CURDATE() - INTERVAL 7 DAY) UNION
  -- Produto 9 (Álcool em gel, atual=200): +201 estoque inicial, -1 venda#6 = 200
  SELECT 9, 'entrada', 201, 'Estoque inicial', 'manual', NULL, 201, (CURDATE() - INTERVAL 25 DAY) UNION
  SELECT 9, 'saida', 1, 'Venda #6', 'venda', 6, 200, (CURDATE() - INTERVAL 1 DAY) UNION
  -- Produto 10 (Detergente, atual=300): +301 estoque inicial, -1 venda#6 = 300
  SELECT 10, 'entrada', 301, 'Estoque inicial', 'manual', NULL, 301, (CURDATE() - INTERVAL 25 DAY) UNION
  SELECT 10, 'saida', 1, 'Venda #6', 'venda', 6, 300, (CURDATE() - INTERVAL 1 DAY)
) AS t
WHERE NOT EXISTS (SELECT 1 FROM `estoque_movimentos` LIMIT 1);
