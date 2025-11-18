<?php
/**
 * Processar Reserva
 * Cria uma nova reserva na base de dados
 */

session_start();
require_once '../config/database.php';

// Verificar se está autenticado
if (!isset($_SESSION['utilizador_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Verificar se é POST
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: calendario.php');
    exit();
}

// Receber dados do formulário
$data = isset($_POST['data']) ? trim($_POST['data']) : '';
$espaco_id = isset($_POST['espaco_id']) ? (int)$_POST['espaco_id'] : 0;
$hora_inicio = isset($_POST['hora_inicio']) ? trim($_POST['hora_inicio']) : '';
$hora_fim = isset($_POST['hora_fim']) ? trim($_POST['hora_fim']) : '';
$turma = isset($_POST['turma']) ? trim($_POST['turma']) : '';
$nome_professor = isset($_POST['nome_professor']) ? trim($_POST['nome_professor']) : '';

// Validar dados
if (empty($data) || $espaco_id == 0 || empty($hora_inicio) || empty($hora_fim) || empty($turma) || empty($nome_professor)) {
    header('Location: calendario.php?erro=campos');
    exit();
}

// Validar se hora fim é maior que hora início
if ($hora_fim <= $hora_inicio) {
    header('Location: calendario.php?erro=horario');
    exit();
}

try {
    // Verificar novamente se está disponível (segurança)
    $sql_verificar = "SELECT COUNT(*) as conflitos 
                      FROM reserva 
                      WHERE espaco_id = :espaco_id 
                      AND data = :data 
                      AND estado = 'confirmada'
                      AND NOT (hora_fim <= :hora_inicio OR hora_inicio >= :hora_fim)";
    
    $stmt = $pdo->prepare($sql_verificar);
    $stmt->bindParam(':espaco_id', $espaco_id);
    $stmt->bindParam(':data', $data);
    $stmt->bindParam(':hora_inicio', $hora_inicio);
    $stmt->bindParam(':hora_fim', $hora_fim);
    $stmt->execute();
    
    $resultado = $stmt->fetch();
    
    if ($resultado['conflitos'] > 0) {
        // Espaço já ocupado
        header('Location: calendario.php?erro=ocupado');
        exit();
    }
    
    // Criar a reserva
    $sql_inserir = "INSERT INTO reserva (utilizador_id, espaco_id, turma, nome_professor, data, hora_inicio, hora_fim, estado) 
                    VALUES (:utilizador_id, :espaco_id, :turma, :nome_professor, :data, :hora_inicio, :hora_fim, 'confirmada')";
    
    $stmt = $pdo->prepare($sql_inserir);
    $stmt->bindParam(':utilizador_id', $_SESSION['utilizador_id']);
    $stmt->bindParam(':espaco_id', $espaco_id);
    $stmt->bindParam(':turma', $turma);
    $stmt->bindParam(':nome_professor', $nome_professor);
    $stmt->bindParam(':data', $data);
    $stmt->bindParam(':hora_inicio', $hora_inicio);
    $stmt->bindParam(':hora_fim', $hora_fim);
    $stmt->execute();
    
    // Reserva criada com sucesso!
    header('Location: minhas_reservas.php?sucesso=criada');
    exit();
    
} catch (PDOException $e) {
    die("Erro ao criar reserva: " . $e->getMessage());
}
?>