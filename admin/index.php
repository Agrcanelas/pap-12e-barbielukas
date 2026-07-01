<?php
/**
 * Dashboard do Administrador
 * Visão geral do sistema
 */

session_start();
$base_path = '../';

// Verificar se está autenticado
if (!isset($_SESSION['utilizador_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Verificar se é admin
if ($_SESSION['tipo'] != 'admin') {
    header('Location: ../professor/index.php');
    exit();
}

require_once '../config/database.php';

try {
    // Estatísticas gerais
    
    // Total de espaços
    $sql_espacos = "SELECT COUNT(*) as total FROM espaco";
    $total_espacos = $pdo->query($sql_espacos)->fetch()['total'];
    
    // Total de utilizadores (professores)
    $sql_users = "SELECT COUNT(*) as total FROM utilizador WHERE tipo = 'professor'";
    $total_professores = $pdo->query($sql_users)->fetch()['total'];
    
    // Total de reservas ativas (futuras)
    $sql_reservas = "SELECT COUNT(*) as total FROM reserva WHERE estado = 'confirmada' AND data >= CURDATE()";
    $total_reservas = $pdo->query($sql_reservas)->fetch()['total'];
    
    // Reservas de hoje
    $sql_hoje = "SELECT COUNT(*) as total FROM reserva WHERE estado = 'confirmada' AND data = CURDATE()";
    $reservas_hoje = $pdo->query($sql_hoje)->fetch()['total'];
    
    // Espaço mais reservado
    $sql_popular = "SELECT e.nome, COUNT(r.reserva_id) as total_reservas 
                    FROM reserva r 
                    JOIN espaco e ON r.espaco_id = e.espaco_id 
                    WHERE r.estado = 'confirmada' AND r.data >= CURDATE()
                    GROUP BY e.espaco_id 
                    ORDER BY total_reservas DESC 
                    LIMIT 1";
    $stmt_popular = $pdo->query($sql_popular);
    $espaco_popular = $stmt_popular->rowCount() > 0 ? $stmt_popular->fetch() : null;
    
    // Últimas 5 reservas
    $sql_ultimas = "SELECT r.*, e.nome as espaco_nome, u.nome as professor_nome
                    FROM reserva r
                    JOIN espaco e ON r.espaco_id = e.espaco_id
                    JOIN utilizador u ON r.utilizador_id = u.utilizador_id
                    ORDER BY r.criado_em DESC
                    LIMIT 5";
    $ultimas_reservas = $pdo->query($sql_ultimas)->fetchAll();
    
} catch (PDOException $e) {
    die("Erro ao buscar estatísticas: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sistema de Reservas</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <div class="container">
        
        <div class="welcome-section">
            <h1>🔧 Painel de Administração</h1>
            <p>Bem-vindo, <?php echo htmlspecialchars($_SESSION['nome']); ?>!</p>
        </div>

        <!-- Estatísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">🏫</div>
                <div class="stat-info">
                    <h3><?php echo $total_espacos; ?></h3>
                    <p>Espaços Registados</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">👨‍🏫</div>
                <div class="stat-info">
                    <h3><?php echo $total_professores; ?></h3>
                    <p>Professores</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📅</div>
                <div class="stat-info">
                    <h3><?php echo $total_reservas; ?></h3>
                    <p>Reservas Futuras</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">⭐</div>
                <div class="stat-info">
                    <h3><?php echo $reservas_hoje; ?></h3>
                    <p>Reservas Hoje</p>
                </div>
            </div>
        </div>

        <!-- Espaço mais popular -->
        <?php if ($espaco_popular): ?>
        <div class="destaque-info">
            <h3>🏆 Espaço Mais Reservado</h3>
            <p><strong><?php echo htmlspecialchars($espaco_popular['nome']); ?></strong> com <?php echo $espaco_popular['total_reservas']; ?> reservas futuras</p>
        </div>
        <?php endif; ?>

        <!-- Ações Rápidas -->
        <div class="quick-actions">
            <h2>Ações Rápidas</h2>
            <div class="actions-grid">
                <a href="gerir_espacos.php" class="action-card">
                    <span class="action-icon">🏫</span>
                    <h3>Gerir Espaços</h3>
                    <p>Adicionar, editar ou remover espaços</p>
                </a>
                
                <a href="gerir_utilizadores.php" class="action-card">
                    <span class="action-icon">👥</span>
                    <h3>Gerir Utilizadores</h3>
                    <p>Adicionar ou remover professores</p>
                </a>
                
                <a href="todas_reservas.php" class="action-card">
                    <span class="action-icon">📋</span>
                    <h3>Todas as Reservas</h3>
                    <p>Ver todas as reservas do sistema</p>
                </a>
            </div>
        </div>

        <!-- Últimas Reservas -->
        <div class="ultimas-reservas-admin">
            <h2>📌 Últimas Reservas Criadas</h2>
            <?php if (count($ultimas_reservas) > 0): ?>
            <div class="tabela-container">
                <table class="tabela">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Horário</th>
                            <th>Espaço</th>
                            <th>Professor</th>
                            <th>Turma</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ultimas_reservas as $reserva): ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($reserva['data'])); ?></td>
                            <td>
                                <?php echo substr($reserva['hora_inicio'], 0, 5); ?> - 
                                <?php echo substr($reserva['hora_fim'], 0, 5); ?>
                            </td>
                            <td><?php echo htmlspecialchars($reserva['espaco_nome']); ?></td>
                            <td><?php echo htmlspecialchars($reserva['professor_nome']); ?></td>
                            <td><?php echo htmlspecialchars($reserva['turma']); ?></td>
                            <td>
                                <?php if ($reserva['estado'] == 'confirmada'): ?>
                                    <span class="badge badge-confirmada">Confirmada</span>
                                <?php else: ?>
                                    <span class="badge badge-cancelada">Cancelada</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p>Ainda não há reservas no sistema.</p>
            <?php endif; ?>
        </div>

    </div>

    <?php include '../includes/footer.php'; ?>

</body>
</html>