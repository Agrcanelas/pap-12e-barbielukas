<?php
/**
 * Todas as Reservas (Admin)
 * Visualizar todas as reservas do sistema
 */

session_start();
$base_path = '../';

// Verificar se está autenticado e é admin
if (!isset($_SESSION['utilizador_id']) || $_SESSION['tipo'] != 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

require_once '../config/database.php';

// Mensagens
$sucesso = isset($_GET['sucesso']) ? $_GET['sucesso'] : '';

// Filtros
$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'futuras';
$espaco_filtro = isset($_GET['espaco']) ? (int)$_GET['espaco'] : 0;

try {
    // Buscar espaços para o filtro
    $sql_espacos = "SELECT * FROM espaco WHERE ativo = 1 ORDER BY nome";
    $espacos = $pdo->query($sql_espacos)->fetchAll();
    
    // Montar query conforme filtros
    $sql = "SELECT r.*, e.nome as espaco_nome, u.nome as professor_nome
            FROM reserva r
            JOIN espaco e ON r.espaco_id = e.espaco_id
            JOIN utilizador u ON r.utilizador_id = u.utilizador_id
            WHERE 1=1";
    
    $params = [];
    
    // Filtro por período
    if ($filtro == 'futuras') {
        $sql .= " AND r.data >= CURDATE()";
    } elseif ($filtro == 'hoje') {
        $sql .= " AND r.data = CURDATE()";
    } elseif ($filtro == 'passadas') {
        $sql .= " AND r.data < CURDATE()";
    }
    
    // Filtro por espaço
    if ($espaco_filtro > 0) {
        $sql .= " AND r.espaco_id = :espaco_id";
        $params[':espaco_id'] = $espaco_filtro;
    }
    
    // Filtro por estado
    if (isset($_GET['estado']) && $_GET['estado'] != '') {
        $sql .= " AND r.estado = :estado";
        $params[':estado'] = $_GET['estado'];
    }
    
    $sql .= " ORDER BY r.data DESC, r.hora_inicio DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $reservas = $stmt->fetchAll();
    
    // Contar reservas por estado
    $sql_stats = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN estado = 'confirmada' THEN 1 ELSE 0 END) as confirmadas,
                    SUM(CASE WHEN estado = 'cancelada' THEN 1 ELSE 0 END) as canceladas
                  FROM reserva";
    $stats = $pdo->query($sql_stats)->fetch();
    
} catch (PDOException $e) {
    die("Erro ao buscar reservas: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todas as Reservas - Sistema de Reservas</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <div class="container">
        
        <div class="page-header">
            <h1>📋 Todas as Reservas</h1>
            <p>Visualizar e gerir todas as reservas do sistema</p>
        </div>

        <!-- Mensagem -->
        <?php if ($sucesso == 'cancelada'): ?>
        <div class="alert alert-sucesso">
            <strong>✅ Sucesso!</strong> Reserva cancelada com sucesso!
        </div>
        <?php endif; ?>

        <!-- Estatísticas Rápidas -->
        <div class="stats-mini">
            <div class="stat-mini">
                <strong><?php echo $stats['total']; ?></strong>
                <span>Total</span>
            </div>
            <div class="stat-mini confirmadas">
                <strong><?php echo $stats['confirmadas']; ?></strong>
                <span>Confirmadas</span>
            </div>
            <div class="stat-mini canceladas">
                <strong><?php echo $stats['canceladas']; ?></strong>
                <span>Canceladas</span>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filtros-container">
            <form method="GET" class="filtros-form">
                
                <!-- Período -->
                <div class="filtro-grupo">
                    <label>📅 Período:</label>
                    <select name="filtro" onchange="this.form.submit()">
                        <option value="futuras" <?php echo $filtro == 'futuras' ? 'selected' : ''; ?>>Próximas</option>
                        <option value="hoje" <?php echo $filtro == 'hoje' ? 'selected' : ''; ?>>Hoje</option>
                        <option value="todas" <?php echo $filtro == 'todas' ? 'selected' : ''; ?>>Todas</option>
                        <option value="passadas" <?php echo $filtro == 'passadas' ? 'selected' : ''; ?>>Passadas</option>
                    </select>
                </div>

                <!-- Espaço -->
                <div class="filtro-grupo">
                    <label>🏫 Espaço:</label>
                    <select name="espaco" onchange="this.form.submit()">
                        <option value="0">Todos</option>
                        <?php foreach ($espacos as $espaco): ?>
                        <option value="<?php echo $espaco['espaco_id']; ?>" <?php echo $espaco_filtro == $espaco['espaco_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($espaco['nome']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Estado -->
                <div class="filtro-grupo">
                    <label>🔄 Estado:</label>
                    <select name="estado" onchange="this.form.submit()">
                        <option value="">Todos</option>
                        <option value="confirmada" <?php echo isset($_GET['estado']) && $_GET['estado'] == 'confirmada' ? 'selected' : ''; ?>>Confirmadas</option>
                        <option value="cancelada" <?php echo isset($_GET['estado']) && $_GET['estado'] == 'cancelada' ? 'selected' : ''; ?>>Canceladas</option>
                    </select>
                </div>

                <!-- Botão Limpar -->
                <a href="todas_reservas.php" class="btn-limpar-filtros">🔄 Limpar Filtros</a>

            </form>
        </div>

        <!-- Tabela de Reservas -->
        <?php if (count($reservas) > 0): ?>
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
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservas as $reserva): ?>
                    <?php
                        $data_reserva = strtotime($reserva['data']);
                        $hoje = strtotime(date('Y-m-d'));
                        $eh_passada = $data_reserva < $hoje;
                    ?>
                    <tr>
                        <td>
                            <strong><?php echo date('d/m/Y', $data_reserva); ?></strong>
                            <?php if ($reserva['data'] == date('Y-m-d')): ?>
                                <span class="badge badge-hoje" style="margin-left: 5px;">Hoje</span>
                            <?php endif; ?>
                        </td>
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
                        <td>
                            <?php if ($reserva['estado'] == 'confirmada' && !$eh_passada): ?>
                            <button 
                                onclick="confirmarCancelamento(<?php echo $reserva['reserva_id']; ?>)" 
                                class="btn-acao btn-remover"
                                title="Cancelar Reserva"
                            >
                                🗑️
                            </button>
                            <?php else: ?>
                                <span style="color: #999;">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="info-total">
            <p>📊 Mostrando <strong><?php echo count($reservas); ?></strong> reserva(s)</p>
        </div>
        
        <?php else: ?>
        <div class="sem-resultados">
            <p>📭 Não há reservas com os filtros selecionados.</p>
        </div>
        <?php endif; ?>

    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
    function confirmarCancelamento(reservaId) {
        if (confirm('⚠️ Tem a certeza que deseja cancelar esta reserva?')) {
            window.location.href = 'cancelar_reserva_admin.php?id=' + reservaId;
        }
    }
    </script>

</body>
</html>