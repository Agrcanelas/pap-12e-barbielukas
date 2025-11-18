<?php
/**
 * Cancelar Reserva (Admin)
 * Admin pode cancelar qualquer reserva
 */

session_start();
require_once '../config/database.php';

// Verificar se está autenticado e é admin
if (!isset($_SESSION['utilizador_id']) || $_SESSION['tipo'] != 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

// Receber ID da reserva
$reserva_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($reserva_id == 0) {
    header('Location: todas_reservas.php?erro=id');
    exit();
}

try {
    // Cancelar a reserva (admin pode cancelar qualquer uma)
    $sql = "UPDATE reserva SET estado = 'cancelada' WHERE reserva_id = :reserva_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':reserva_id', $reserva_id);
    $stmt->execute();
    
    // Sucesso
    header('Location: todas_reservas.php?sucesso=cancelada');
    exit();
    
} catch (PDOException $e) {
    header('Location: todas_reservas.php?erro=cancelar');
    exit();
}
?>