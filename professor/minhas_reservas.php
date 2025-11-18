<?php
/**
 * Minhas Reservas
 * Visualizar e gerir as reservas do professor
 */

session_start();
$base_path = '../';

// Verificar se está autenticado
if (!isset($_SESSION['utilizador_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

require_once '../config/database.php';

// Verificar se há mensagem de sucesso
$sucesso = isset($_GET['sucesso']) ? $_GET['sucesso'] : '';
$erro = isset($_GET['erro']) ? $_GET['erro'] : '';

// Filtro (futuras ou todas)
$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'futuras';

try {
    // Buscar reservas do professor
    if ($filtro == 'todas') {
        $sql = "SELECT r.*, e.nome as espaco_nome, e.tipo_espaco
                FROM reserva r
                JOIN espaco e ON r.espaco_id = e.espaco_id
                WHERE r.utilizador_id = :utilizador_id
                ORDER BY r.data DESC, r.hora_inicio DESC";
    } else {
        // Apenas futuras
        $sql = "SELECT r.*, e.nome as espaco_nome, e.tipo_espaco
                FROM reserva r
                JOIN espaco e ON r.espaco_id = e.espaco_id
                WHERE r.utilizador_id = :utilizador_id
                AND r.data >= CURDATE()
                ORDER BY r.data ASC, r.hora_inicio ASC";
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':utilizador_id', $_SESSION['utilizador_id']);
    $stmt->execute();
    $reservas = $stmt->fetchAll();
    
} catch (PDOException $e) {
    die("Erro ao buscar reservas: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Reservas - Sistema de Reservas</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <div class="container">
        
        <div class="page-header">
            <h1>📋 Minhas Reservas</h1>
            <p>Gerir as suas reservas de espaços</p>
        </div>

        <!-- Mensagens -->
        <?php if ($sucesso == 'criada'): ?>
        <div class="alert alert-sucesso">
            <strong>✅ Sucesso!</strong> Reserva criada com sucesso!
        </div>
        <?php elseif ($sucesso == 'cancelada'): ?>
        <div class="alert alert-sucesso">
            <strong>✅ Sucesso!</strong> Reserva cancelada com sucesso!
        </div>
        <?php elseif ($erro == 'cancelar'): ?>
        <div class="alert alert-erro">
            <strong>❌ Erro!</strong> Não foi possível cancelar a reserva.
        </div>
        <?php endif; ?>

        <!-- Filtros -->
        <div class="filtros-reservas">
            <a href="?filtro=futuras" class="btn-filtro <?php echo $filtro == 'futuras' ? 'ativo' : ''; ?>">
                📅 Próximas Reservas
            </a>
            <a href="?filtro=todas" class="btn-filtro <?php echo $filtro == 'todas' ? 'ativo' : ''; ?>">
                📚 Todas as Reservas
            </a>
        </div>

        <!-- Lista de Reservas -->
        <?php if (count($reservas) > 0): ?>
        <div class="reservas-grid">
            <?php foreach ($reservas as $reserva): ?>
            <?php
                $data_reserva = strtotime($reserva['data']);
                $hoje = strtotime(date('Y-m-d'));
                $eh_passada = $data_reserva < $hoje;
                $eh_hoje = $data_reserva == $hoje;
            ?>
            <div class="reserva-card <?php echo $eh_passada ? 'passada' : ''; ?>">
                
                <!-- Cabeçalho do Card -->
                <div class="reserva-card-header">
                    <div class="reserva-data-badge <?php echo $eh_hoje ? 'hoje' : ''; ?>">
                        <span class="dia"><?php echo date('d', $data_reserva); ?></span>
                        <span class="mes"><?php echo strftime('%b', $data_reserva); ?></span>
                        <span class="ano"><?php echo date('Y', $data_reserva); ?></span>
                    </div>
                    <div class="reserva-status">
                        <?php if ($reserva['estado'] == 'cancelada'): ?>
                            <span class="badge badge-cancelada">Cancelada</span>
                        <?php elseif ($eh_passada): ?>
                            <span class="badge badge-passada">Concluída</span>
                        <?php elseif ($eh_hoje): ?>
                            <span class="badge badge-hoje">Hoje</span>
                        <?php else: ?>
                            <span class="badge badge-confirmada">Confirmada</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Corpo do Card -->
                <div class="reserva-card-body">
                    <h3><?php echo htmlspecialchars($reserva['espaco_nome']); ?></h3>
                    <p class="tipo-espaco"><?php echo htmlspecialchars($reserva['tipo_espaco']); ?></p>
                    
                    <div class="reserva-detalhes">
                        <div class="detalhe-item">
                            <span class="icone">🕒</span>
                            <span class="texto">
                                <?php echo substr($reserva['hora_inicio'], 0, 5); ?> - 
                                <?php echo substr($reserva['hora_fim'], 0, 5); ?>
                            </span>
                        </div>
                        
                        <div class="detalhe-item">
                            <span class="icone">👥</span>
                            <span class="texto">Turma: <?php echo htmlspecialchars($reserva['turma']); ?></span>
                        </div>
                        
                        <div class="detalhe-item">
                            <span class="icone">👨‍🏫</span>
                            <span class="texto"><?php echo htmlspecialchars($reserva['nome_professor']); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Rodapé do Card -->
                <?php if ($reserva['estado'] == 'confirmada' && !$eh_passada): ?>
                <div class="reserva-card-footer">
                    <button 
                        onclick="confirmarCancelamento(<?php echo $reserva['reserva_id']; ?>)" 
                        class="btn btn-cancelar"
                    >
                        🗑️ Cancelar Reserva
                    </button>
                </div>
                <?php endif; ?>

            </div>
            <?php endforeach; ?>
        </div>
        
        <?php else: ?>
        <div class="sem-reservas">
            <p>📭 Não tem reservas <?php echo $filtro == 'todas' ? '' : 'futuras'; ?>.</p>
            <a href="calendario.php" class="btn btn-primary">Fazer Nova Reserva</a>
        </div>
        <?php endif; ?>

    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
    function confirmarCancelamento(reservaId) {
        if (confirm('⚠️ Tem a certeza que deseja cancelar esta reserva?')) {
            window.location.href = 'cancelar_reserva.php?id=' + reservaId;
        }
    }
    </script>

</body>
</html>