-- Módulos comerciais (Clientes, Estoque, Vendas, Financeiro, Configuração).
-- Executado automaticamente pelo MySQL apenas na primeira inicialização do
-- volume (docker-entrypoint-initdb.d). Para bancos já existentes, rode este
-- arquivo manualmente (ex.: via phpMyAdmin, ou
-- `docker exec -i <container_db> mysql --default-character-set=utf8mb4 -uroot -p<senha> blog_comercial < 02-modulos-comerciais.sql`).
--
-- SET NAMES abaixo garante que os acentos dos dados fictícios (João, Comércio,
-- Informática etc.) sejam interpretados como UTF-8 pela sessão do cliente que
-- executa este script, independente de qual charset padrão esse cliente usa
-- — sem isso, alguns clientes MySQL (ex.: versões que usam latin1 como
-- default) gravam o texto com encoding duplicado ("Ã£" em vez de "ã").
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `categorias` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(100) NOT NULL,
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `marcas` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(100) NOT NULL,
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `produtos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(150) NOT NULL,
  `categoria_id` INT UNSIGNED DEFAULT NULL,
  `marca_id` INT UNSIGNED DEFAULT NULL,
  `preco` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `estoque_qtd` INT NOT NULL DEFAULT 0,
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_produtos_categoria` (`categoria_id`),
  KEY `fk_produtos_marca` (`marca_id`),
  CONSTRAINT `fk_produtos_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_produtos_marca` FOREIGN KEY (`marca_id`) REFERENCES `marcas` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `clientes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(150) NOT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `telefone` VARCHAR(20) DEFAULT NULL,
  `documento` VARCHAR(20) DEFAULT NULL,
  `endereco` VARCHAR(200) DEFAULT NULL,
  `cidade` VARCHAR(100) DEFAULT NULL,
  `uf` VARCHAR(2) DEFAULT NULL,
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `fornecedores` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(150) NOT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `telefone` VARCHAR(20) DEFAULT NULL,
  `documento` VARCHAR(20) DEFAULT NULL,
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `vendedores` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(150) NOT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `telefone` VARCHAR(20) DEFAULT NULL,
  `comissao_percentual` DECIMAL(5,2) NOT NULL DEFAULT 0,
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `transportadoras` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(150) NOT NULL,
  `documento` VARCHAR(20) DEFAULT NULL,
  `telefone` VARCHAR(20) DEFAULT NULL,
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `vendas` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `cliente_id` INT UNSIGNED DEFAULT NULL,
  `vendedor_id` INT UNSIGNED DEFAULT NULL,
  `data_venda` DATE NOT NULL,
  `status` ENUM('orcamento','concluida','cancelada') NOT NULL DEFAULT 'concluida',
  `total` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_vendas_cliente` (`cliente_id`),
  KEY `fk_vendas_vendedor` (`vendedor_id`),
  CONSTRAINT `fk_vendas_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_vendas_vendedor` FOREIGN KEY (`vendedor_id`) REFERENCES `vendedores` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `venda_itens` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `venda_id` INT UNSIGNED NOT NULL,
  `produto_id` INT UNSIGNED DEFAULT NULL,
  `quantidade` INT NOT NULL DEFAULT 1,
  `preco_unitario` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `fk_itens_venda` (`venda_id`),
  KEY `fk_itens_produto` (`produto_id`),
  CONSTRAINT `fk_itens_venda` FOREIGN KEY (`venda_id`) REFERENCES `vendas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_itens_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `contas_pagar` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `fornecedor_id` INT UNSIGNED DEFAULT NULL,
  `descricao` VARCHAR(200) NOT NULL,
  `valor` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `vencimento` DATE NOT NULL,
  `status` ENUM('pendente','pago') NOT NULL DEFAULT 'pendente',
  `pago_em` DATE DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pagar_fornecedor` (`fornecedor_id`),
  CONSTRAINT `fk_pagar_fornecedor` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedores` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `contas_receber` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `cliente_id` INT UNSIGNED DEFAULT NULL,
  `descricao` VARCHAR(200) NOT NULL,
  `valor` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `vencimento` DATE NOT NULL,
  `status` ENUM('pendente','recebido') NOT NULL DEFAULT 'pendente',
  `recebido_em` DATE DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_receber_cliente` (`cliente_id`),
  CONSTRAINT `fk_receber_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `configuracoes` (
  `id` INT UNSIGNED NOT NULL,
  `site_nome` VARCHAR(150) NOT NULL DEFAULT 'Sistema Comercial',
  `admin_email` VARCHAR(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------------------
-- Dados fictícios de teste (idempotente: só insere se as tabelas estiverem vazias)
-- --------------------------------------------------------------------

INSERT INTO `categorias` (`nome`) SELECT * FROM (SELECT 'Informática' UNION SELECT 'Papelaria' UNION SELECT 'Móveis' UNION SELECT 'Limpeza') AS t
WHERE NOT EXISTS (SELECT 1 FROM `categorias` LIMIT 1);

INSERT INTO `marcas` (`nome`) SELECT * FROM (SELECT 'Genérica' UNION SELECT 'Dell' UNION SELECT 'HP' UNION SELECT 'Faber-Castell') AS t
WHERE NOT EXISTS (SELECT 1 FROM `marcas` LIMIT 1);

INSERT INTO `produtos` (`nome`, `categoria_id`, `marca_id`, `preco`, `estoque_qtd`)
SELECT * FROM (
  SELECT 'Notebook 15"' AS nome, 1 AS categoria_id, 2 AS marca_id, 3200.00 AS preco, 8 AS estoque_qtd UNION
  SELECT 'Mouse sem fio', 1, 1, 45.90, 40 UNION
  SELECT 'Teclado ABNT2', 1, 3, 89.90, 25 UNION
  SELECT 'Monitor 24"', 1, 3, 799.00, 12 UNION
  SELECT 'Caderno universitário', 2, 4, 22.50, 100 UNION
  SELECT 'Caixa de lápis de cor', 2, 4, 18.00, 60 UNION
  SELECT 'Cadeira de escritório', 3, 1, 450.00, 15 UNION
  SELECT 'Mesa para computador', 3, 1, 620.00, 6 UNION
  SELECT 'Álcool em gel 500ml', 4, 1, 12.90, 200 UNION
  SELECT 'Detergente 500ml', 4, 1, 4.50, 300
) AS t
WHERE NOT EXISTS (SELECT 1 FROM `produtos` LIMIT 1);

INSERT INTO `clientes` (`nome`, `email`, `telefone`, `documento`, `endereco`, `cidade`, `uf`)
SELECT * FROM (
  SELECT 'Papelaria Central Ltda' AS nome, 'contato@papelariacentral.com.br' AS email, '(11) 3222-1000' AS telefone, '12.345.678/0001-90' AS documento, 'Rua das Flores, 100' AS endereco, 'São Paulo' AS cidade, 'SP' AS uf UNION
  SELECT 'João da Silva', 'joao.silva@example.com', '(11) 98888-1111', '123.456.789-00', 'Av. Brasil, 500', 'São Paulo', 'SP' UNION
  SELECT 'Maria Oliveira ME', 'maria@oliveiraeireli.com.br', '(21) 97777-2222', '987.654.321-00', 'Rua Rio Branco, 45', 'Rio de Janeiro', 'RJ' UNION
  SELECT 'Tech Solutions Informática', 'compras@techsolutions.com.br', '(31) 3344-5566', '23.456.789/0001-11', 'Av. Afonso Pena, 900', 'Belo Horizonte', 'MG' UNION
  SELECT 'Comércio Nordeste Distribuidora', 'financeiro@nordestedist.com.br', '(85) 3211-4455', '34.567.890/0001-22', 'Rua do Comércio, 300', 'Fortaleza', 'CE'
) AS t
WHERE NOT EXISTS (SELECT 1 FROM `clientes` LIMIT 1);

INSERT INTO `fornecedores` (`nome`, `email`, `telefone`, `documento`)
SELECT * FROM (
  SELECT 'Distribuidora ABC Suprimentos' AS nome, 'vendas@abcsuprimentos.com.br' AS email, '(11) 4002-8922' AS telefone, '45.678.901/0001-33' AS documento UNION
  SELECT 'Import Tech Peças e Componentes', 'contato@importtech.com.br', '(11) 3055-7788', '56.789.012/0001-44' UNION
  SELECT 'Papel & Cia Indústria', 'comercial@papelcia.com.br', '(19) 3211-9900', '67.890.123/0001-55'
) AS t
WHERE NOT EXISTS (SELECT 1 FROM `fornecedores` LIMIT 1);

INSERT INTO `vendedores` (`nome`, `email`, `telefone`, `comissao_percentual`)
SELECT * FROM (
  SELECT 'Carlos Pereira' AS nome, 'carlos.pereira@sistemacomercial.com' AS email, '(11) 96666-3333' AS telefone, 5.00 AS comissao_percentual UNION
  SELECT 'Fernanda Souza', 'fernanda.souza@sistemacomercial.com', '(11) 95555-4444', 4.50 UNION
  SELECT 'Ricardo Lima', 'ricardo.lima@sistemacomercial.com', '(11) 94444-5555', 6.00
) AS t
WHERE NOT EXISTS (SELECT 1 FROM `vendedores` LIMIT 1);

INSERT INTO `transportadoras` (`nome`, `documento`, `telefone`)
SELECT * FROM (
  SELECT 'Rápido Entregas Transportes' AS nome, '78.901.234/0001-66' AS documento, '(11) 3900-1122' AS telefone UNION
  SELECT 'LogEx Logística Expressa', '89.012.345/0001-77', '(11) 3800-3344'
) AS t
WHERE NOT EXISTS (SELECT 1 FROM `transportadoras` LIMIT 1);

INSERT INTO `vendas` (`cliente_id`, `vendedor_id`, `data_venda`, `status`, `total`)
SELECT * FROM (
  SELECT 1 AS cliente_id, 1 AS vendedor_id, CURDATE() - INTERVAL 20 DAY AS data_venda, 'concluida' AS status, 3245.90 AS total UNION
  SELECT 2, 2, CURDATE() - INTERVAL 15 DAY, 'concluida', 89.90 UNION
  SELECT 3, 1, CURDATE() - INTERVAL 10 DAY, 'concluida', 799.00 UNION
  SELECT 4, 3, CURDATE() - INTERVAL 7 DAY, 'concluida', 1070.00 UNION
  SELECT 5, 2, CURDATE() - INTERVAL 3 DAY, 'orcamento', 22.50 UNION
  SELECT 1, 1, CURDATE() - INTERVAL 1 DAY, 'concluida', 17.40
) AS t
WHERE NOT EXISTS (SELECT 1 FROM `vendas` LIMIT 1);

INSERT INTO `venda_itens` (`venda_id`, `produto_id`, `quantidade`, `preco_unitario`, `subtotal`)
SELECT * FROM (
  SELECT 1 AS venda_id, 1 AS produto_id, 1 AS quantidade, 3200.00 AS preco_unitario, 3200.00 AS subtotal UNION
  SELECT 1, 2, 1, 45.90, 45.90 UNION
  SELECT 2, 2, 1, 89.90, 89.90 UNION
  SELECT 3, 4, 1, 799.00, 799.00 UNION
  SELECT 4, 7, 1, 450.00, 450.00 UNION
  SELECT 4, 8, 1, 620.00, 620.00 UNION
  SELECT 5, 5, 1, 22.50, 22.50 UNION
  SELECT 6, 9, 1, 12.90, 12.90 UNION
  SELECT 6, 10, 1, 4.50, 4.50
) AS t
WHERE NOT EXISTS (SELECT 1 FROM `venda_itens` LIMIT 1);

INSERT INTO `contas_pagar` (`fornecedor_id`, `descricao`, `valor`, `vencimento`, `status`, `pago_em`)
SELECT * FROM (
  SELECT 1 AS fornecedor_id, 'Compra de suprimentos de escritório' AS descricao, 1500.00 AS valor, CURDATE() + INTERVAL 10 DAY AS vencimento, 'pendente' AS status, NULL AS pago_em UNION
  SELECT 2, 'Peças e componentes de informática', 3800.00, CURDATE() + INTERVAL 20 DAY, 'pendente', NULL UNION
  SELECT 3, 'Papel para impressão (lote mensal)', 620.00, CURDATE() - INTERVAL 5 DAY, 'pago', CURDATE() - INTERVAL 2 DAY UNION
  SELECT NULL, 'Aluguel do galpão', 4200.00, CURDATE() + INTERVAL 5 DAY, 'pendente', NULL
) AS t
WHERE NOT EXISTS (SELECT 1 FROM `contas_pagar` LIMIT 1);

INSERT INTO `contas_receber` (`cliente_id`, `descricao`, `valor`, `vencimento`, `status`, `recebido_em`)
SELECT * FROM (
  SELECT 1 AS cliente_id, 'Venda #1 - Notebook e mouse' AS descricao, 3245.90 AS valor, CURDATE() + INTERVAL 15 DAY AS vencimento, 'pendente' AS status, NULL AS recebido_em UNION
  SELECT 3, 'Venda #3 - Monitor 24"', 799.00, CURDATE() - INTERVAL 3 DAY, 'recebido', CURDATE() - INTERVAL 1 DAY UNION
  SELECT 4, 'Venda #4 - Móveis de escritório', 1070.00, CURDATE() + INTERVAL 8 DAY, 'pendente', NULL
) AS t
WHERE NOT EXISTS (SELECT 1 FROM `contas_receber` LIMIT 1);

INSERT INTO `configuracoes` (`id`, `site_nome`, `admin_email`)
SELECT 1, 'Sistema Comercial', 'admin@admin.com'
WHERE NOT EXISTS (SELECT 1 FROM `configuracoes` WHERE `id` = 1);
