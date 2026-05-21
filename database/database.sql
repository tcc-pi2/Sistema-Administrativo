CREATE DATABASE IF NOT EXISTS gastrotech_admin
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_general_ci;

USE gastrotech_admin;

DROP TABLE IF EXISTS itens_pedido;
DROP TABLE IF EXISTS pedidos;
DROP TABLE IF EXISTS produtos;
DROP TABLE IF EXISTS categorias;
DROP TABLE IF EXISTS movimentos_caixa;
DROP TABLE IF EXISTS caixas;
DROP TABLE IF EXISTS configuracoes_sistema;
DROP TABLE IF EXISTS totens;
DROP TABLE IF EXISTS administradores;

CREATE TABLE administradores (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL UNIQUE,
  senha_hash VARCHAR(255) NOT NULL,
  permissao ENUM('Administrador', 'Cardapio', 'Atendimento', 'Leitura') NOT NULL DEFAULT 'Leitura',
  status ENUM('Ativo', 'Inativo') NOT NULL DEFAULT 'Ativo',
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE totens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(30) NOT NULL UNIQUE,
  localizacao VARCHAR(120) NOT NULL,
  status ENUM('Online', 'Offline', 'Manutencao') NOT NULL DEFAULT 'Online',
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE configuracoes_sistema (
  id INT AUTO_INCREMENT PRIMARY KEY,
  chave VARCHAR(80) NOT NULL UNIQUE,
  valor VARCHAR(120) NOT NULL,
  descricao VARCHAR(180) NULL,
  atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE caixas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  saldo_inicial DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  status ENUM('Aberto', 'Fechado') NOT NULL DEFAULT 'Aberto',
  aberto_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  fechado_em TIMESTAMP NULL
);

CREATE TABLE movimentos_caixa (
  id INT AUTO_INCREMENT PRIMARY KEY,
  caixa_id INT NOT NULL,
  tipo ENUM('Entrada', 'Saida') NOT NULL,
  descricao VARCHAR(180) NOT NULL,
  forma_pagamento ENUM('Dinheiro', 'Pix', 'Cartao') NOT NULL DEFAULT 'Dinheiro',
  valor DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_movimentos_caixa_caixas
    FOREIGN KEY (caixa_id)
    REFERENCES caixas(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
);

CREATE TABLE categorias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(120) NOT NULL,
  descricao VARCHAR(180) NULL,
  ordem INT NOT NULL DEFAULT 0,
  status ENUM('Ativo', 'Inativo') NOT NULL DEFAULT 'Ativo',
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE produtos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  categoria_id INT NULL,
  nome VARCHAR(140) NOT NULL,
  descricao VARCHAR(255) NULL,
  opcoes VARCHAR(160) NULL,
  opcoes_personalizacao TEXT NULL,
  ingredientes TEXT NULL,
  tags VARCHAR(180) NULL,
  alergenos VARCHAR(180) NULL,
  porcao VARCHAR(120) NULL,
  calorias INT NULL,
  imagem_url VARCHAR(255) NULL,
  estoque INT NOT NULL DEFAULT 0,
  preco DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  tempo_preparo_min INT NOT NULL DEFAULT 10,
  destaque TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('Ativo', 'Inativo') NOT NULL DEFAULT 'Ativo',
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_produtos_categorias
    FOREIGN KEY (categoria_id)
    REFERENCES categorias(id)
    ON DELETE SET NULL
    ON UPDATE CASCADE
);

CREATE TABLE pedidos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  totem_id INT NOT NULL,
  codigo_retirada VARCHAR(20) NOT NULL,
  nome_cliente VARCHAR(100) NULL,
  status_pedido ENUM('Recebido', 'Em preparo', 'Pronto', 'Retirado', 'Cancelado') NOT NULL DEFAULT 'Recebido',
  status_pagamento ENUM('Pendente', 'Pago', 'Cancelado') NOT NULL DEFAULT 'Pendente',
  forma_pagamento ENUM('Pix', 'Cartao', 'Dinheiro') NOT NULL,
  subtotal DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  tempo_estimado_min INT NOT NULL DEFAULT 0,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_pedidos_totens
    FOREIGN KEY (totem_id)
    REFERENCES totens(id)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
);

CREATE TABLE itens_pedido (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pedido_id INT NOT NULL,
  produto_id INT NULL,
  nome_produto VARCHAR(140) NOT NULL,
  observacao VARCHAR(180) NULL,
  quantidade INT NOT NULL DEFAULT 1,
  preco_unitario DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  CONSTRAINT fk_itens_pedido_pedidos
    FOREIGN KEY (pedido_id)
    REFERENCES pedidos(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT fk_itens_pedido_produtos
    FOREIGN KEY (produto_id)
    REFERENCES produtos(id)
    ON DELETE SET NULL
    ON UPDATE CASCADE
);

INSERT INTO administradores (nome, email, senha_hash, permissao, status) VALUES
('Usuario Admin', 'admin@gastrotech.com', '$2y$10$troque-este-hash-no-php', 'Administrador', 'Ativo'),
('Marina Costa', 'marina@gastrotech.com', '$2y$10$troque-este-hash-no-php', 'Cardapio', 'Ativo'),
('Rafael Lima', 'rafael@gastrotech.com', '$2y$10$troque-este-hash-no-php', 'Atendimento', 'Ativo');

INSERT INTO totens (codigo, localizacao, status) VALUES
('TOTEM-01', 'Entrada principal', 'Online'),
('TOTEM-02', 'Balcao lateral', 'Online'),
('TOTEM-03', 'Area externa', 'Manutencao');

INSERT INTO configuracoes_sistema (chave, valor, descricao) VALUES
('nome_loja', 'GastroTech', 'Nome exibido no totem e no painel'),
('logo_loja', '../assets/brand/gastrotech-logo.jpg', 'Logo padrao da loja'),
('tempo_espera_min', '18', 'Previsao em minutos exibida ao cliente apos confirmar o pedido');

INSERT INTO caixas (saldo_inicial, status) VALUES
(150.00, 'Aberto');

INSERT INTO movimentos_caixa (caixa_id, tipo, descricao, forma_pagamento, valor) VALUES
(1, 'Entrada', 'Troco inicial reforcado', 'Dinheiro', 50.00),
(1, 'Saida', 'Compra de embalagens', 'Dinheiro', 22.50);

INSERT INTO categorias (nome, descricao, ordem, status) VALUES
('Combos', 'Promocoes com lanche, acompanhamento e bebida', 1, 'Ativo'),
('Hamburgueres', 'Lanches principais do cardapio', 2, 'Ativo'),
('Porcoes', 'Acompanhamentos para compartilhar', 3, 'Ativo'),
('Bebidas', 'Bebidas geladas', 4, 'Ativo'),
('Sobremesas', 'Opcoes doces', 5, 'Ativo'),
('Adicionais', 'Molhos e extras', 6, 'Ativo');

INSERT INTO produtos (categoria_id, nome, descricao, opcoes, opcoes_personalizacao, ingredientes, tags, alergenos, porcao, calorias, imagem_url, estoque, preco, tempo_preparo_min, destaque, status) VALUES
(1, 'Combo Smash', 'Smash burger na chapa, batata sequinha e refrigerante gelado.', 'Burger,Batata,Refri ou suco', 'Bebida do combo: Coca-Cola, Guarana, Fanta Laranja, Suco de laranja, Suco de uva | Gelo: Com gelo, Sem gelo', 'pao brioche, blend bovino 120g, cheddar, picles, molho especial, batata palito, refrigerante lata', 'Mais vendido,Completo', 'gluten,leite', '1 lanche + batata + bebida', 920, '../assets/images/menu/combo-smash.jpg', 24, 32.90, 18, 1, 'Ativo'),
(2, 'X-Bacon Artesanal', 'Pao brioche tostado, bacon crocante, cheddar e molho da casa.', 'Pao brioche,Bacon,Cheddar', NULL, 'pao brioche, hamburguer artesanal, bacon em tiras, queijo cheddar, cebola caramelizada, molho da casa', 'Artesanal,Chapa', 'gluten,leite', '1 lanche artesanal', 680, '../assets/images/menu/x-bacon-artesanal.jpg', 18, 24.90, 16, 1, 'Ativo'),
(3, 'Batata Suprema', 'Batata crocante coberta com cheddar cremoso e bacon.', 'Cheddar,Bacon,Media', NULL, 'batata palito, creme de cheddar, bacon crocante, cebolinha, tempero da casa', 'Compartilhar,Crocante', 'leite', 'porcao media 350g', 540, '../assets/images/menu/batata-suprema.jpg', 12, 18.00, 10, 0, 'Ativo'),
(4, 'Bebida Gelada', 'Lata 350ml ou suco gelado para acompanhar qualquer pedido.', 'Coca-Cola,Guarana,Fanta,Suco', 'Sabor da bebida: Coca-Cola, Guarana, Fanta Laranja, Suco de laranja, Suco de uva | Gelo: Com gelo, Sem gelo', 'bebida escolhida, gelo opcional', 'Gelado,Rapido', '', 'lata 350ml ou copo 500ml', 140, '../assets/images/menu/bebida-gelada.jpg', 42, 6.00, 2, 0, 'Ativo'),
(5, 'Milkshake Chocolate', 'Milkshake cremoso de chocolate com chantilly e calda.', '400ml,Chocolate', NULL, 'sorvete de chocolate, leite, calda de chocolate, chantilly, raspas de chocolate', 'Cremoso,Doce', 'leite', 'copo 400ml', 460, '../assets/images/menu/milkshake-chocolate.jpg', 7, 16.90, 8, 1, 'Ativo'),
(6, 'Molho Extra', 'Pote extra para escolher barbecue defumado ou maionese verde.', 'Barbecue,Maionese verde', NULL, 'barbecue defumado, maionese verde, ervas frescas, especiarias', 'Extra,Molhos', 'ovo', 'pote 40ml', 95, '../assets/images/menu/molho-extra.jpg', 35, 3.50, 1, 0, 'Ativo');

INSERT INTO pedidos (totem_id, codigo_retirada, nome_cliente, status_pedido, status_pagamento, forma_pagamento, subtotal, tempo_estimado_min) VALUES
(1, 'A102', 'Cliente Totem', 'Recebido', 'Pago', 'Pix', 38.90, 18),
(2, 'A103', 'Pedido Balcao', 'Em preparo', 'Pago', 'Cartao', 49.80, 16),
(1, 'A104', 'Retirada Rapida', 'Pronto', 'Pago', 'Pix', 32.90, 18);

INSERT INTO itens_pedido (pedido_id, produto_id, nome_produto, observacao, quantidade, preco_unitario) VALUES
(1, 1, 'Combo Smash', 'Sem cebola', 1, 32.90),
(1, 4, 'Bebida Gelada', 'Sabor da bebida: Guarana | Gelo: Sem gelo', 1, 6.00),
(2, 2, 'X-Bacon Artesanal', NULL, 2, 24.90),
(3, 1, 'Combo Smash', NULL, 1, 32.90);
