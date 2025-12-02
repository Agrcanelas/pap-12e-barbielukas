<?php
/**
 * Processar Reserva
 * Criar nova reserva no sistema
 */

session_start();
require_once '../config/database.php';
require_once '../config/log.php'; // ← NOVO: Incluir sistema de logs

// Verificar se está autenticado
if (!isset($_SESSION['utilizador_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Verificar se é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: calendario.php?erro=metodo');
    exit();
}

// Receber dados
$espaco_id = isset($_POST['espaco_id']) ? (int)$_POST['espaco_id'] : 0;
$data = isset($_POST['data']) ? $_POST['data'] : '';
$hora_inicio = isset($_POST['hora_inicio']) ? $_POST['hora_inicio'] : '';
$hora_fim = isset($_POST['hora_fim']) ? $_POST['hora_fim'] : '';
$turma = isset($_POST['turma']) ? trim($_POST['turma']) : '';
$nome_professor = isset($_POST['nome_professor']) ? trim($_POST['nome_professor']) : '';

// Validar dados
if ($espaco_id == 0 || empty($data) || empty($hora_inicio) || empty($hora_fim) || empty($turma)) {
    header('Location: calendario.php?erro=dados');
    exit();
}

try {
    // Verificar se já existe reserva neste horário
    $sql_verifica = "SELECT COUNT(*) as conflitos 
                     FROM reserva 
                     WHERE espaco_id = :espaco_id 
                     AND data = :data 
                     AND estado = 'confirmada'
                     AND (
                         (hora_inicio < :hora_fim AND hora_fim > :hora_inicio)
                     )";
    
    $stmt_verifica = $pdo->prepare($sql_verifica);
    $stmt_verifica->bindParam(':espaco_id', $espaco_id);
    $stmt_verifica->bindParam(':data', $data);
    $stmt_verifica->bindParam(':hora_inicio', $hora_inicio);
    $stmt_verifica->bindParam(':hora_fim', $hora_fim);
    $stmt_verifica->execute();
    
    $resultado = $stmt_verifica->fetch();
    
    if ($resultado['conflitos'] > 0) {
        header('Location: calendario.php?erro=conflito');
        exit();
    }
    
    // Buscar nome do espaço para o log
    $sql_espaco = "SELECT nome FROM espaco WHERE espaco_id = :espaco_id";
    $stmt_espaco = $pdo->prepare($sql_espaco);
    $stmt_espaco->bindParam(':espaco_id', $espaco_id);
    $stmt_espaco->execute();
    $espaco = $stmt_espaco->fetch();
    $nome_espaco = $espaco ? $espaco['nome'] : 'Espaço desconhecido';
    
    // Criar reserva
    $sql = "INSERT INTO reserva (utilizador_id, espaco_id, turma, nome_professor, data, hora_inicio, hora_fim, estado) 
            VALUES (:utilizador_id, :espaco_id, :turma, :nome_professor, :data, :hora_inicio, :hora_fim, 'confirmada')";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':utilizador_id', $_SESSION['utilizador_id']);
    $stmt->bindParam(':espaco_id', $espaco_id);
    $stmt->bindParam(':turma', $turma);
    $stmt->bindParam(':nome_professor', $nome_professor);
    $stmt->bindParam(':data', $data);
    $stmt->bindParam(':hora_inicio', $hora_inicio);
    $stmt->bindParam(':hora_fim', $hora_fim);
    
    if ($stmt->execute()) {
        // ✅ NOVO: Registar no log
        $descricao = "Criou reserva para {$nome_espaco} no dia " . date('d/m/Y', strtotime($data));
        $detalhes = [
            'espaco' => $nome_espaco,
            'data' => $data,
            'hora_inicio' => $hora_inicio,
            'hora_fim' => $hora_fim,
            'turma' => $turma
        ];
        registarLog($pdo, $_SESSION['utilizador_id'], 'reserva_criada', $descricao, $detalhes);
        
        header('Location: calendario.php?sucesso=reserva_criada');
    } else {
        header('Location: calendario.php?erro=bd');
    }
    
} catch (PDOException $e) {
    header('Location: calendario.php?erro=bd');
}
?>