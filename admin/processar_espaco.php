<?php
/**
 * Processar Espaço
 * Criar, editar ou remover espaços
 */

session_start();
require_once '../config/database.php';

// Verificar se está autenticado e é admin
if (!isset($_SESSION['utilizador_id']) || $_SESSION['tipo'] != 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

// Determinar ação
$acao = isset($_POST['acao']) ? $_POST['acao'] : (isset($_GET['acao']) ? $_GET['acao'] : '');

try {
    
    // ============================================
    // CRIAR ESPAÇO
    // ============================================
    if ($acao == 'criar' && $_SERVER['REQUEST_METHOD'] == 'POST') {
        
        $nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
        $tipo_espaco = isset($_POST['tipo_espaco']) ? trim($_POST['tipo_espaco']) : '';
        $capacidade = isset($_POST['capacidade']) ? (int)$_POST['capacidade'] : 0;
        
        // Validar
        if (empty($nome) || empty($tipo_espaco) || $capacidade <= 0) {
            header('Location: gerir_espacos.php?erro=campos');
            exit();
        }
        
        // Inserir
        $sql = "INSERT INTO espaco (nome, tipo_espaco, capacidade, ativo) 
                VALUES (:nome, :tipo_espaco, :capacidade, 1)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':tipo_espaco', $tipo_espaco);
        $stmt->bindParam(':capacidade', $capacidade);
        $stmt->execute();
        
        header('Location: gerir_espacos.php?sucesso=criado');
        exit();
    }
    
    // ============================================
    // EDITAR ESPAÇO
    // ============================================
    elseif ($acao == 'editar' && $_SERVER['REQUEST_METHOD'] == 'POST') {
        
        $espaco_id = isset($_POST['espaco_id']) ? (int)$_POST['espaco_id'] : 0;
        $nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
        $tipo_espaco = isset($_POST['tipo_espaco']) ? trim($_POST['tipo_espaco']) : '';
        $capacidade = isset($_POST['capacidade']) ? (int)$_POST['capacidade'] : 0;
        $ativo = isset($_POST['ativo']) ? 1 : 0;
        
        // Validar
        if ($espaco_id == 0 || empty($nome) || empty($tipo_espaco) || $capacidade <= 0) {
            header('Location: gerir_espacos.php?erro=campos');
            exit();
        }
        
        // Atualizar
        $sql = "UPDATE espaco 
                SET nome = :nome, 
                    tipo_espaco = :tipo_espaco, 
                    capacidade = :capacidade,
                    ativo = :ativo
                WHERE espaco_id = :espaco_id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':tipo_espaco', $tipo_espaco);
        $stmt->bindParam(':capacidade', $capacidade);
        $stmt->bindParam(':ativo', $ativo);
        $stmt->bindParam(':espaco_id', $espaco_id);
        $stmt->execute();
        
        header('Location: gerir_espacos.php?sucesso=editado');
        exit();
    }
    
    // ============================================
    // REMOVER ESPAÇO
    // ============================================
    elseif ($acao == 'remover' && isset($_GET['id'])) {
        
        $espaco_id = (int)$_GET['id'];
        
        if ($espaco_id == 0) {
            header('Location: gerir_espacos.php?erro=id');
            exit();
        }
        
        // Remover espaço (CASCADE irá remover as reservas associadas automaticamente)
        $sql = "DELETE FROM espaco WHERE espaco_id = :espaco_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':espaco_id', $espaco_id);
        $stmt->execute();
        
        header('Location: gerir_espacos.php?sucesso=removido');
        exit();
    }
    
    // ============================================
    // AÇÃO INVÁLIDA
    // ============================================
    else {
        header('Location: gerir_espacos.php?erro=acao');
        exit();
    }
    
} catch (PDOException $e) {
    header('Location: gerir_espacos.php?erro=bd');
    exit();
}
?>