CREATE DATABASE IF NOT EXISTS dojo_ventura CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dojo_ventura;

-- 1. Tabela de Utilizadores (Administradores)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    ativo TINYINT(1) DEFAULT 1, -- 1 = Ativo, 0 = Inativo
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Tabela de Configurações Gerais (Chave / Valor)
CREATE TABLE IF NOT EXISTS configuracoes (
    chave VARCHAR(50) PRIMARY KEY,
    valor TEXT NOT NULL,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. Tabela de Redes Sociais (Abordagem Dinâmica)
CREATE TABLE IF NOT EXISTS redes_sociais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,          -- Ex: Instagram, TikTok, Facebook, YouTube
    url VARCHAR(255) NOT NULL,
    icone VARCHAR(50) DEFAULT 'bi-link', -- Classe CSS do ícone (ex: FontAwesome/Bootstrap Icons)
    ordem INT DEFAULT 0,                 -- Para definir a sequência de exibição
    ativo TINYINT(1) DEFAULT 1,          -- 1 = Exibir no site, 0 = Ocultar
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 4. Tabela de Atletas
CREATE TABLE IF NOT EXISTS atletas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    categoria VARCHAR(100) NOT NULL,      -- Ex: Sub-21, Sênior, Kata, Kumite
    faixa VARCHAR(50) DEFAULT 'Branca',    -- Graduação no Karatê
    foto VARCHAR(255) DEFAULT 'assets/img/default-atleta.png',
    bio TEXT NULL,                         -- Breve história ou perfil do atleta
    ativo TINYINT(1) DEFAULT 1,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 5. Tabela de Competições / Histórico do Atleta
CREATE TABLE IF NOT EXISTS competicoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    atleta_id INT NOT NULL,
    torneio VARCHAR(255) NOT NULL,
    colocacao VARCHAR(100) NOT NULL,      -- Ex: 1º Lugar, Medalha de Ouro
    data_evento DATE NOT NULL,             -- Alterado para DATE para permitir ordenação por data
    ativo TINYINT(1) DEFAULT 1,
    FOREIGN KEY (atleta_id) REFERENCES atletas(id) ON DELETE CASCADE,
    INDEX idx_atleta_id (atleta_id)
) ENGINE=InnoDB;

-- 6. Tabela de Trufas / Produtos
CREATE TABLE IF NOT EXISTS trufas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sabor VARCHAR(100) NOT NULL,
    descricao TEXT NULL,
    preco DECIMAL(10,2) DEFAULT 0.00,
    foto VARCHAR(255) DEFAULT 'assets/img/default-trufa.png',
    ativo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

-- 7. Tabela da Galeria de Fotos
CREATE TABLE IF NOT EXISTS galeria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NULL,
    caminho_imagem VARCHAR(255) NOT NULL,
    ativo TINYINT(1) DEFAULT 1,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ========================================================
-- INSERÇÃO DE DADOS INICIAIS
-- ========================================================

-- Opção 1: Redes sociais na tabela de configurações gerais
INSERT INTO configuracoes (chave, valor) VALUES 
('pix_chave', '21408475000191'),
('whatsapp_numero', '5518988026646'),
('localizacao_endereco', 'Presidente Prudente - SP'),
('localizacao_maps_link', 'https://maps.google.com'),
('sobre_texto', 'Somos uma equipe dedicada de karatecas buscando representar nossa cidade e estado nos principais campeonatos do país.'),
('instagram_link', 'https://www.instagram.com/dojoventura'),
('facebook_link', 'https://www.facebook.com/dojoventura'),
('tiktok_link', 'https://www.tiktok.com/@dojoventura')
ON DUPLICATE KEY UPDATE valor=VALUES(valor);

-- Opção 2: Redes sociais na tabela dedicada 'redes_sociais'
INSERT INTO redes_sociais (nome, url, icone, ordem, ativo) VALUES 
('Instagram', 'https://www.instagram.com/dojoventura', 'bi-instagram', 1, 1),
('Facebook', 'https://www.facebook.com/dojoventura', 'bi-facebook', 2, 1),
('TikTok', 'https://www.tiktok.com/@dojoventura', 'bi-tiktok', 3, 1)
ON DUPLICATE KEY UPDATE url=VALUES(url);

-- Utilizador Admin Padrão (Email: admin@dojo.com | Senha: admin123)
INSERT INTO usuarios (nome, email, senha, ativo) VALUES 
('Administrador', 'admin@dojo.com', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 1)
ON DUPLICATE KEY UPDATE nome=VALUES(nome), senha=VALUES(senha);