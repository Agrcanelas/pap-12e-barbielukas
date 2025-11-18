<?php
/**
 * Cancelar Reserva
 * Marca uma reserva como cancelada
 */

session_start();
require_once '../config/database.php';

// Verificar se está autenticado
if (!isset($_SESSION['utilizador_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Receber ID da reserva
$reserva_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($reserva_id == 0) {
    header('Location: minhas_reservas.php?erro=cancelar');
    exit();
}

try {
    // Verificar se a reserva pertence ao utilizador
    $sql_verificar = "SELECT * FROM reserva 
                      WHERE reserva_id = :reserva_id 
                      AND utilizador_id = :utilizador_id";
    
    $stmt = $pdo->prepare($sql_verificar);
    $stmt->bindParam(':reserva_id', $reserva_id);
    $stmt->bindParam(':utilizador_id', $_SESSION['utilizador_id']);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        // Reserva não existe ou não pertence ao utilizador
        header('Location: minhas_reservas.php?erro=cancelar');
        exit();
    }
    
    // Cancelar a reserva (marcar como cancelada)
    $sql_cancelar = "UPDATE reserva 
                     SET estado = 'cancelada' 
                     WHERE reserva_id = :reserva_id";
    
    $stmt = $pdo->prepare($sql_cancelar);
    $stmt->bindParam(':reserva_id', $reserva_id);
    $stmt->execute();
    
    // Sucesso!
    header('Location: minhas_reservas.php?sucesso=cancelada');
    exit();
    
} catch (PDOException $e) {
    header('Location: minhas_reservas.php?erro=cancelar');
    exit();
}
?>