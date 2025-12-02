<?php
/**
 * Processar Espaço
 * Criar, editar ou remover espaços
 */

session_start();
require_once '../config/database.php';
require_once '../config/log.php'; // ← NOVO: Incluir sistema de logs

// Verificar se está autenticado e é admin
if (!isset($_SESSION['utilizador_id']) || $_SESSION['tipo'] != 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

// Receber ação
$acao = isset($_POST['acao']) ? $_POST['acao'] : (isset($_GET['acao']) ? $_GET['acao'] : '');

try {
    
    // ============================================
    // CRIAR ESPAÇO
    // ============================================
    if ($acao == 'criar') {
        $nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
        $tipo_espaco = isset($_POST['tipo_espaco']) ? trim($_POST['tipo_espaco']) : '';
        $capacidade = isset($_POST['capacidade']) ? (int)$_POST['capacidade'] : 0;
        
        if (empty($nome) || empty($tipo_espaco) || $capacidade <= 0) {
            header('Location: gerir_espacos.php?erro=dados');
            exit();
        }
        
        $sql = "INSERT INTO espaco (nome, tipo_espaco, capacidade) VALUES (:nome, :tipo_espaco, :capacidade)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':tipo_espaco', $tipo_espaco);
        $stmt->bindParam(':capacidade', $capacidade);
        
        if ($stmt->execute()) {
            // ✅ NOVO: Registar no log
            $descricao = "Criou o espaço '{$nome}'";
            $detalhes = [
                'nome' => $nome,
                'tipo_espaco' => $tipo_espaco,
                'capacidade' => $capacidade
            ];
            registarLog($pdo, $_SESSION['utilizador_id'], 'espaco_criado', $descricao, $detalhes);
            
            header('Location: gerir_espacos.php?sucesso=criado');
        } else {
            header('Location: gerir_espacos.php?erro=bd');
        }
    }
    
    // ============================================
    // EDITAR ESPAÇO
    // ============================================
    elseif ($acao == 'editar') {
        $espaco_id = isset($_POST['espaco_id']) ? (int)$_POST['espaco_id'] : 0;
        $nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
        $tipo_espaco = isset($_POST['tipo_espaco']) ? trim($_POST['tipo_espaco']) : '';
        $capacidade = isset($_POST['capacidade']) ? (int)$_POST['capacidade'] : 0;
        
        if ($espaco_id == 0 || empty($nome) || empty($tipo_espaco) || $capacidade <= 0) {
            header('Location: gerir_espacos.php?erro=dados');
            exit();
        }
        
        $sql = "UPDATE espaco SET nome = :nome, tipo_espaco = :tipo_espaco, capacidade = :capacidade WHERE espaco_id = :espaco_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':tipo_espaco', $tipo_espaco);
        $stmt->bindParam(':capacidade', $capacidade);
        $stmt->bindParam(':espaco_id', $espaco_id);
        
        if ($stmt->execute()) {
            // ✅ NOVO: Registar no log
            $descricao = "Editou o espaço '{$nome}'";
            $detalhes = [
                'espaco_id' => $espaco_id,
                'nome' => $nome,
                'tipo_espaco' => $tipo_espaco,
                'capacidade' => $capacidade
            ];
            registarLog($pdo, $_SESSION['utilizador_id'], 'espaco_editado', $descricao, $detalhes);
            
            header('Location: gerir_espacos.php?sucesso=editado');
        } else {
            header('Location: gerir_espacos.php?erro=bd');
        }
    }
    
    // ============================================
    // REMOVER ESPAÇO
    // ============================================
    elseif ($acao == 'remover') {
        $espaco_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($espaco_id == 0) {
            header('Location: gerir_espacos.php?erro=id');
            exit();
        }
        
        // Buscar nome do espaço antes de remover
        $sql_buscar = "SELECT nome FROM espaco WHERE espaco_id = :espaco_id";
        $stmt_buscar = $pdo->prepare($sql_buscar);
        $stmt_buscar->bindParam(':espaco_id', $espaco_id);
        $stmt_buscar->execute();
        $espaco = $stmt_buscar->fetch();
        $nome_espaco = $espaco ? $espaco['nome'] : 'Espaço desconhecido';
        
        $sql = "DELETE FROM espaco WHERE espaco_id = :espaco_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':espaco_id', $espaco_id);
        
        if ($stmt->execute()) {
            // ✅ NOVO: Registar no log
            $descricao = "Removeu o espaço '{$nome_espaco}'";
            $detalhes = [
                'espaco_id' => $espaco_id,
                'nome' => $nome_espaco
            ];
            registarLog($pdo, $_SESSION['utilizador_id'], 'espaco_removido', $descricao, $detalhes);
            
            header('Location: gerir_espacos.php?sucesso=removido');
        } else {
            header('Location: gerir_espacos.php?erro=bd');
        }
    }
    
    else {
        header('Location: gerir_espacos.php?erro=acao');
    }
    
} catch (PDOException $e) {
    header('Location: gerir_espacos.php?erro=bd');
}
?>