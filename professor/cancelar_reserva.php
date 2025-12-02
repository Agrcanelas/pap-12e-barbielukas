<?php
/**
 * Cancelar Reserva
 * Professor pode cancelar as suas próprias reservas
 */

session_start();
require_once '../config/database.php';
require_once '../config/log.php'; // ← NOVO: Incluir sistema de logs

// Verificar se está autenticado
if (!isset($_SESSION['utilizador_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Receber ID da reserva
$reserva_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($reserva_id == 0) {
    header('Location: minhas_reservas.php?erro=id');
    exit();
}

try {
    // Buscar dados da reserva antes de cancelar (para o log)
    $sql_buscar = "SELECT r.*, e.nome as espaco_nome 
                   FROM reserva r
                   JOIN espaco e ON r.espaco_id = e.espaco_id
                   WHERE r.reserva_id = :reserva_id 
                   AND r.utilizador_id = :utilizador_id";
    
    $stmt_buscar = $pdo->prepare($sql_buscar);
    $stmt_buscar->bindParam(':reserva_id', $reserva_id);
    $stmt_buscar->bindParam(':utilizador_id', $_SESSION['utilizador_id']);
    $stmt_buscar->execute();
    
    $reserva = $stmt_buscar->fetch();
    
    if (!$reserva) {
        header('Location: minhas_reservas.php?erro=nao_encontrada');
        exit();
    }
    
    // Cancelar reserva (mudar estado)
    $sql = "UPDATE reserva 
            SET estado = 'cancelada' 
            WHERE reserva_id = :reserva_id 
            AND utilizador_id = :utilizador_id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':reserva_id', $reserva_id);
    $stmt->bindParam(':utilizador_id', $_SESSION['utilizador_id']);
    
    if ($stmt->execute()) {
        // ✅ NOVO: Registar no log
        $descricao = "Cancelou reserva de {$reserva['espaco_nome']} do dia " . date('d/m/Y', strtotime($reserva['data']));
        $detalhes = [
            'reserva_id' => $reserva_id,
            'espaco' => $reserva['espaco_nome'],
            'data' => $reserva['data'],
            'hora_inicio' => $reserva['hora_inicio'],
            'hora_fim' => $reserva['hora_fim'],
            'turma' => $reserva['turma']
        ];
        registarLog($pdo, $_SESSION['utilizador_id'], 'reserva_cancelada', $descricao, $detalhes);
        
        header('Location: minhas_reservas.php?sucesso=cancelada');
    } else {
        header('Location: minhas_reservas.php?erro=bd');
    }
    
} catch (PDOException $e) {
    header('Location: minhas_reservas.php?erro=bd');
}
?>