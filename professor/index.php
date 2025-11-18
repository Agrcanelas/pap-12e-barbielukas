<?php
/**
 * Dashboard do Professor
 * Página principal após login
 */

session_start();

// Definir caminho base para o header
$base_path = '../';

// Verificar se está autenticado
if (!isset($_SESSION['utilizador_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Verificar se é professor
if ($_SESSION['tipo'] != 'professor') {
    header('Location: ../admin/index.php');
    exit();
}

require_once '../config/database.php';

// Buscar estatísticas do professor
try {
    // Contar reservas ativas do professor
    $sql_reservas = "SELECT COUNT(*) as total FROM reserva 
                     WHERE utilizador_id = :id AND estado = 'confirmada' AND data >= CURDATE()";
    $stmt = $pdo->prepare($sql_reservas);
    $stmt->bindParam(':id', $_SESSION['utilizador_id']);
    $stmt->execute();
    $total_reservas = $stmt->fetch()['total'];
    
    // Buscar próximas reservas (próximos 7 dias)
    $sql_proximas = "SELECT r.*, e.nome as espaco_nome 
                     FROM reserva r 
                     JOIN espaco e ON r.espaco_id = e.espaco_id 
                     WHERE r.utilizador_id = :id 
                     AND r.estado = 'confirmada' 
                     AND r.data BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                     ORDER BY r.data ASC, r.hora_inicio ASC
                     LIMIT 5";
    $stmt = $pdo->prepare($sql_proximas);
    $stmt->bindParam(':id', $_SESSION['utilizador_id']);
    $stmt->execute();
    $proximas_reservas = $stmt->fetchAll();
    
} catch (PDOException $e) {
    die("Erro ao buscar dados: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Professor</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <!-- Header/Menu -->
    <?php include '../includes/header.php'; ?>

    <!-- Conteúdo Principal -->
    <div class="container">
        
        <!-- Boas-vindas -->
        <div class="welcome-section">
            <h1>Bem-vindo, <?php echo htmlspecialchars($_SESSION['nome']); ?>! 👋</h1>
            <p>Sistema de Reservas de Espaços</p>
        </div>

        <!-- Estatísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📅</div>
                <div class="stat-info">
                    <h3><?php echo $total_reservas; ?></h3>
                    <p>Reservas Ativas</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🏫</div>
                <div class="stat-info">
                    <h3><?php echo count($proximas_reservas); ?></h3>
                    <p>Próximos 7 dias</p>
                </div>
            </div>
        </div>

        <!-- Ações Rápidas -->
        <div class="quick-actions">
            <h2>Ações Rápidas</h2>
            <div class="actions-grid">
                <a href="calendario.php" class="action-card">
                    <span class="action-icon">📆</span>
                    <h3>Ver Calendário</h3>
                    <p>Consultar disponibilidade e fazer reservas</p>
                </a>
                
                <a href="minhas_reservas.php" class="action-card">
                    <span class="action-icon">📋</span>
                    <h3>Minhas Reservas</h3>
                    <p>Gerir e consultar as suas reservas</p>
                </a>
            </div>
        </div>

        <!-- Próximas Reservas -->
        <?php if (count($proximas_reservas) > 0): ?>
        <div class="proximas-reservas">
            <h2>Próximas Reservas</h2>
            <div class="reservas-list">
                <?php foreach ($proximas_reservas as $reserva): ?>
                <div class="reserva-item">
                    <div class="reserva-data">
                        <span class="dia"><?php echo date('d', strtotime($reserva['data'])); ?></span>
                        <span class="mes"><?php echo strftime('%b', strtotime($reserva['data'])); ?></span>
                    </div>
                    <div class="reserva-info">
                        <h4><?php echo htmlspecialchars($reserva['espaco_nome']); ?></h4>
                        <p>
                            <strong>Horário:</strong> 
                            <?php echo substr($reserva['hora_inicio'], 0, 5); ?> - 
                            <?php echo substr($reserva['hora_fim'], 0, 5); ?>
                        </p>
                        <p><strong>Turma:</strong> <?php echo htmlspecialchars($reserva['turma']); ?></p>
                    </div>
                    <div class="reserva-status">
                        <span class="badge badge-sucesso">Confirmada</span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="sem-reservas">
            <p>📭 Não tem reservas nos próximos 7 dias.</p>
            <a href="fazer_reserva.php" class="btn btn-primary">Fazer Nova Reserva</a>
        </div>
        <?php endif; ?>

    </div>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>

</body>
</html>