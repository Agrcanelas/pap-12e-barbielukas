-- =============================================
-- SISTEMA DE RESERVAS DE ESPAÇOS
-- Agrupamento de Escolas Canelas
-- =============================================

-- Criar base de dados
CREATE DATABASE IF NOT EXISTS sistema_reservas;
USE sistema_reservas;

-- =============================================
-- TABELA: UTILIZADOR
-- =============================================
CREATE TABLE utilizador (
    utilizador_id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    tipo ENUM('professor', 'admin') NOT NULL DEFAULT 'professor',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- TABELA: ESPAÇO
-- =============================================
CREATE TABLE espaco (
    espaco_id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE,
    capacidade INT NOT NULL,
    tipo_espaco VARCHAR(50) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- TABELA: RESERVA
-- =============================================
CREATE TABLE reserva (
    reserva_id INT AUTO_INCREMENT PRIMARY KEY,
    utilizador_id INT NOT NULL,
    espaco_id INT NOT NULL,
    turma VARCHAR(50) NOT NULL,
    nome_professor VARCHAR(100) NOT NULL,
    data DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fim TIME NOT NULL,
    estado ENUM('confirmada', 'cancelada') NOT NULL DEFAULT 'confirmada',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilizador_id) REFERENCES utilizador(utilizador_id) ON DELETE CASCADE,
    FOREIGN KEY (espaco_id) REFERENCES espaco(espaco_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- TABELA: LOG_ACOES (Auditoria)
-- =============================================
CREATE TABLE log_acoes (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    utilizador_id INT NOT NULL,
    tipo_acao VARCHAR(50) NOT NULL,
    descricao TEXT NOT NULL,
    detalhes TEXT,
    data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    FOREIGN KEY (utilizador_id) REFERENCES utilizador(utilizador_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- ÍNDICES PARA PERFORMANCE
-- =============================================
CREATE INDEX idx_tipo_acao ON log_acoes(tipo_acao);
CREATE INDEX idx_data_hora ON log_acoes(data_hora);
CREATE INDEX idx_utilizador ON log_acoes(utilizador_id);
CREATE INDEX idx_reserva_espaco ON reserva(espaco_id);
CREATE INDEX idx_reserva_usuario ON reserva(utilizador_id);
CREATE INDEX idx_reserva_data ON reserva(data);

-- =============================================
-- DADOS DE TESTE
-- =============================================

-- Admin
INSERT INTO utilizador (nome, email, password, tipo) VALUES 
('Administrador', 'admin@canelas.pt', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Professores (password: professor123)
INSERT INTO utilizador (nome, email, password, tipo) VALUES 
('Professor Teste', 'professor@canelas.pt', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'professor'),
('Diana Carneiro', 'diana.carneiro@canelas.pt', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'professor'),
('Rosa Espirito Santo', 'rosa.espirito.santo@canelas.pt', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'professor'),
('Patricia Carvalhais', 'patricia.carvalhais@canelas.pt', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'professor');

-- Espaços
INSERT INTO espaco (nome, capacidade, tipo_espaco) VALUES 
('Sala 101', 30, 'Sala Normal'),
('Sala 102', 25, 'Sala Normal'),
('Laboratório de Informática', 20, 'Laboratório'),
('Laboratório de Química', 15, 'Laboratório'),
('Auditório', 100, 'Auditório'),
('Biblioteca', 40, 'Biblioteca');

-- Exemplo de Reservas
INSERT INTO reserva (utilizador_id, espaco_id, turma, nome_professor, data, hora_inicio, hora_fim, estado) VALUES 
(2, 1, '12ºA', 'Professor Teste', '2025-12-15', '08:15', '09:05', 'confirmada'),
(2, 1, '12ºA', 'Professor Teste', '2025-12-15', '09:10', '10:00', 'confirmada'),
(3, 2, '11ºB', 'Diana Carneiro', '2025-12-16', '10:20', '11:10', 'confirmada'),
(4, 3, '10ºC', 'Rosa Espirito Santo', '2025-12-17', '14:10', '15:00', 'confirmada');

-- Exemplo de Logs
INSERT INTO log_acoes (utilizador_id, tipo_acao, descricao, detalhes, ip_address) VALUES 
(1, 'utilizador_criado', 'Criou o utilizador ''Professor Teste'' (professor@canelas.pt)', '{\"nome\": \"Professor Teste\", \"email\": \"professor@canelas.pt\", \"tipo\": \"professor\"}', '127.0.0.1'),
(2, 'reserva_criada', 'Criou reserva para Sala 101 no dia 15/12/2025', '{\"espaco\": \"Sala 101\", \"data\": \"2025-12-15\", \"hora_inicio\": \"08:15\", \"hora_fim\": \"09:05\", \"turma\": \"12ºA\"}', '127.0.0.1'),
(1, 'espaco_criado', 'Criou o espaço ''Sala 101''', '{\"nome\": \"Sala 101\", \"capacidade\": 30, \"tipo_espaco\": \"Sala Normal\"}', '127.0.0.1');

-- =============================================
-- VERIFICAR DADOS
-- =============================================
SELECT COUNT(*) as total_utilizadores FROM utilizador;
SELECT COUNT(*) as total_espacos FROM espaco;
SELECT COUNT(*) as total_reservas FROM reserva;
SELECT COUNT(*) as total_logs FROM log_acoes;