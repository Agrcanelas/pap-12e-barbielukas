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

    // Buscar reservas de amanhã para mostrar aviso uma vez por sessão
    $sql_amanha = "SELECT r.*, e.nome as espaco_nome
                   FROM reserva r
                   JOIN espaco e ON r.espaco_id = e.espaco_id
                   WHERE r.utilizador_id = :id
                   AND r.estado = 'confirmada'
                   AND r.data = DATE_ADD(CURDATE(), INTERVAL 1 DAY)
                   ORDER BY r.hora_inicio ASC";
    $stmt = $pdo->prepare($sql_amanha);
    $stmt->bindParam(':id', $_SESSION['utilizador_id']);
    $stmt->execute();
    $reservas_amanha = $stmt->fetchAll();

    $mostrar_aviso_amanha = count($reservas_amanha) > 0 && empty($_SESSION['aviso_reservas_amanha_mostrado']);
    if ($mostrar_aviso_amanha) {
        $_SESSION['aviso_reservas_amanha_mostrado'] = true;
    }
    
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
            <a href="calendario.php" class="btn btn-primary">Fazer Nova Reserva</a>
        </div>
        <?php endif; ?>

    </div>

    <?php if (!empty($mostrar_aviso_amanha)): ?>
    <div id="modalReservasAmanha" class="modal modal-alerta-reservas" style="display: block;">
        <div class="modal-content alerta-reservas-content">
            <button type="button" class="modal-fechar" onclick="fecharAvisoReservasAmanha()">&times;</button>

            <div class="alerta-reservas-header">
                <div class="alerta-reservas-icon">📌</div>
                <div>
                    <h2>Reservas para amanhã</h2>
                    <p>Tem <?php echo count($reservas_amanha); ?> reserva(s) marcada(s) para amanhã.</p>
                </div>
            </div>

            <div class="alerta-reservas-lista">
                <?php foreach ($reservas_amanha as $reserva): ?>
                <div class="alerta-reserva-item">
                    <strong><?php echo htmlspecialchars($reserva['espaco_nome']); ?></strong>
                    <span><?php echo substr($reserva['hora_inicio'], 0, 5); ?> - <?php echo substr($reserva['hora_fim'], 0, 5); ?></span>
                    <small>Turma: <?php echo htmlspecialchars($reserva['turma']); ?></small>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="alerta-reservas-acoes">
                <a href="minhas_reservas.php" class="btn btn-primary">Ver minhas reservas</a>
                <button type="button" class="btn btn-secondary" onclick="fecharAvisoReservasAmanha()">Fechar</button>
            </div>
        </div>
    </div>

    <script>
    function fecharAvisoReservasAmanha() {
        const modal = document.getElementById('modalReservasAmanha');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    window.addEventListener('click', function(event) {
        const modal = document.getElementById('modalReservasAmanha');
        if (modal && event.target === modal) {
            fecharAvisoReservasAmanha();
        }
    });
    </script>
    <?php endif; ?>
    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>

</body>
</html>