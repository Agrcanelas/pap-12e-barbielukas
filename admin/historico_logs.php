<?php
/**
 * Histórico de Logs
 * Visualizar todas as ações realizadas no sistema
 */

session_start();
$base_path = '../';

// Verificar se está autenticado e é admin
if (!isset($_SESSION['utilizador_id']) || $_SESSION['tipo'] != 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

require_once '../config/database.php';

// Filtros
$filtro_tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$filtro_utilizador = isset($_GET['utilizador']) ? (int)$_GET['utilizador'] : 0;
$filtro_data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : '';
$filtro_data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : '';

// Paginação
$por_pagina = 50;
$pagina_atual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_atual - 1) * $por_pagina;

try {
    // Construir query com filtros
    $sql = "SELECT l.*, u.nome as utilizador_nome 
            FROM log_acoes l
            JOIN utilizador u ON l.utilizador_id = u.utilizador_id
            WHERE 1=1";
    
    $params = [];
    
    // Filtro por tipo de ação
    if (!empty($filtro_tipo)) {
        $sql .= " AND l.tipo_acao = :tipo_acao";
        $params[':tipo_acao'] = $filtro_tipo;
    }
    
    // Filtro por utilizador
    if ($filtro_utilizador > 0) {
        $sql .= " AND l.utilizador_id = :utilizador_id";
        $params[':utilizador_id'] = $filtro_utilizador;
    }
    
    // Filtro por data início
    if (!empty($filtro_data_inicio)) {
        $sql .= " AND DATE(l.data_hora) >= :data_inicio";
        $params[':data_inicio'] = $filtro_data_inicio;
    }
    
    // Filtro por data fim
    if (!empty($filtro_data_fim)) {
        $sql .= " AND DATE(l.data_hora) <= :data_fim";
        $params[':data_fim'] = $filtro_data_fim;
    }
    
    // Contar total de registos
    $sql_count = str_replace("l.*, u.nome as utilizador_nome", "COUNT(*) as total", $sql);
    $stmt_count = $pdo->prepare($sql_count);
    foreach ($params as $key => $value) {
        $stmt_count->bindValue($key, $value);
    }
    $stmt_count->execute();
    $total_registos = $stmt_count->fetch()['total'];
    $total_paginas = ceil($total_registos / $por_pagina);
    
    // Buscar logs com paginação
    $sql .= " ORDER BY l.data_hora DESC LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $por_pagina, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $logs = $stmt->fetchAll();
    
    // Buscar lista de utilizadores para o filtro
    $sql_users = "SELECT utilizador_id, nome FROM utilizador ORDER BY nome ASC";
    $utilizadores = $pdo->query($sql_users)->fetchAll();
    
} catch (PDOException $e) {
    die("Erro ao buscar logs: " . $e->getMessage());
}

// Função para traduzir tipo de ação
function traduzirTipoAcao($tipo) {
    $traducoes = [
        'reserva_criada' => '📅 Reserva Criada',
        'reserva_cancelada' => '❌ Reserva Cancelada',
        'espaco_criado' => '🏫 Espaço Criado',
        'espaco_editado' => '✏️ Espaço Editado',
        'espaco_removido' => '🗑️ Espaço Removido',
        'utilizador_criado' => '👤 Utilizador Criado',
        'utilizador_editado' => '✏️ Utilizador Editado',
        'utilizador_removido' => '🗑️ Utilizador Removido',
        'password_resetada' => '🔑 Password Resetada',
        'login_sucesso' => '✅ Login',
        'login_falhado' => '❌ Login Falhado'
    ];
    
    return $traducoes[$tipo] ?? $tipo;
}

// Função para cor do badge
function corBadge($tipo) {
    $cores = [
        'reserva_criada' => 'badge-sucesso',
        'reserva_cancelada' => 'badge-cancelada',
        'espaco_criado' => 'badge-sucesso',
        'espaco_editado' => 'badge-aviso',
        'espaco_removido' => 'badge-cancelada',
        'utilizador_criado' => 'badge-sucesso',
        'utilizador_editado' => 'badge-aviso',
        'utilizador_removido' => 'badge-cancelada',
        'password_resetada' => 'badge-aviso'
    ];
    
    return $cores[$tipo] ?? 'badge';
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico de Logs - Sistema de Reservas</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <div class="container">
        
        <div class="page-header">
            <h1>📋 Histórico de Ações</h1>
            <p>Registo de todas as ações realizadas no sistema</p>
        </div>

        <!-- Filtros -->
        <div class="filtros-container">
            <form method="GET" class="filtros-form">
                
                <div class="filtro-grupo">
                    <label>Tipo de Ação</label>
                    <select name="tipo">
                        <option value="">Todas</option>
                        <option value="reserva_criada" <?php echo $filtro_tipo == 'reserva_criada' ? 'selected' : ''; ?>>📅 Reserva Criada</option>
                        <option value="reserva_cancelada" <?php echo $filtro_tipo == 'reserva_cancelada' ? 'selected' : ''; ?>>❌ Reserva Cancelada</option>
                        <option value="espaco_criado" <?php echo $filtro_tipo == 'espaco_criado' ? 'selected' : ''; ?>>🏫 Espaço Criado</option>
                        <option value="espaco_editado" <?php echo $filtro_tipo == 'espaco_editado' ? 'selected' : ''; ?>>✏️ Espaço Editado</option>
                        <option value="espaco_removido" <?php echo $filtro_tipo == 'espaco_removido' ? 'selected' : ''; ?>>🗑️ Espaço Removido</option>
                        <option value="utilizador_criado" <?php echo $filtro_tipo == 'utilizador_criado' ? 'selected' : ''; ?>>👤 Utilizador Criado</option>
                        <option value="utilizador_editado" <?php echo $filtro_tipo == 'utilizador_editado' ? 'selected' : ''; ?>>✏️ Utilizador Editado</option>
                        <option value="utilizador_removido" <?php echo $filtro_tipo == 'utilizador_removido' ? 'selected' : ''; ?>>🗑️ Utilizador Removido</option>
                        <option value="password_resetada" <?php echo $filtro_tipo == 'password_resetada' ? 'selected' : ''; ?>>🔑 Password Resetada</option>
                    </select>
                </div>

                <div class="filtro-grupo">
                    <label>Utilizador</label>
                    <select name="utilizador">
                        <option value="">Todos</option>
                        <?php foreach ($utilizadores as $user): ?>
                        <option value="<?php echo $user['utilizador_id']; ?>" <?php echo $filtro_utilizador == $user['utilizador_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($user['nome']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filtro-grupo">
                    <label>Data Início</label>
                    <input type="date" name="data_inicio" value="<?php echo htmlspecialchars($filtro_data_inicio); ?>">
                </div>

                <div class="filtro-grupo">
                    <label>Data Fim</label>
                    <input type="date" name="data_fim" value="<?php echo htmlspecialchars($filtro_data_fim); ?>">
                </div>

                <button type="submit" class="btn btn-primary">Filtrar</button>
                <a href="historico_logs.php" class="btn-limpar-filtros">Limpar</a>
            </form>

            <div class="info-total">
                <p><strong><?php echo $total_registos; ?></strong> registo(s) encontrado(s)</p>
            </div>
        </div>

        <!-- Tabela de Logs -->
        <?php if (count($logs) > 0): ?>
        <div class="tabela-container">
            <table class="tabela">
                <thead>
                    <tr>
                        <th>Data/Hora</th>
                        <th>Tipo de Ação</th>
                        <th>Utilizador</th>
                        <th>Descrição</th>
                        <th>IP</th>
                        <th>Detalhes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td>
                            <strong><?php echo date('d/m/Y', strtotime($log['data_hora'])); ?></strong><br>
                            <small><?php echo date('H:i:s', strtotime($log['data_hora'])); ?></small>
                        </td>
                        <td>
                            <span class="badge <?php echo corBadge($log['tipo_acao']); ?>">
                                <?php echo traduzirTipoAcao($log['tipo_acao']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($log['utilizador_nome']); ?></td>
                        <td><?php echo htmlspecialchars($log['descricao']); ?></td>
                        <td><small><?php echo htmlspecialchars($log['ip_address'] ?? 'N/A'); ?></small></td>
                        <td>
                            <?php if ($log['detalhes']): ?>
                            <button onclick="verDetalhes(<?php echo htmlspecialchars($log['log_id']); ?>, <?php echo htmlspecialchars($log['detalhes']); ?>)" class="btn-acao" title="Ver Detalhes">
                                ℹ️
                            </button>
                            <?php else: ?>
                            <small style="color: #999;">-</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        <?php if ($total_paginas > 1): ?>
        <div style="text-align: center; margin-top: 30px;">
            <?php if ($pagina_atual > 1): ?>
            <a href="?pagina=<?php echo $pagina_atual - 1; ?>&tipo=<?php echo $filtro_tipo; ?>&utilizador=<?php echo $filtro_utilizador; ?>&data_inicio=<?php echo $filtro_data_inicio; ?>&data_fim=<?php echo $filtro_data_fim; ?>" class="btn btn-secondary">← Anterior</a>
            <?php endif; ?>
            
            <span style="margin: 0 15px;">Página <?php echo $pagina_atual; ?> de <?php echo $total_paginas; ?></span>
            
            <?php if ($pagina_atual < $total_paginas): ?>
            <a href="?pagina=<?php echo $pagina_atual + 1; ?>&tipo=<?php echo $filtro_tipo; ?>&utilizador=<?php echo $filtro_utilizador; ?>&data_inicio=<?php echo $filtro_data_inicio; ?>&data_fim=<?php echo $filtro_data_fim; ?>" class="btn btn-secondary">Seguinte →</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <div class="sem-resultados">
            <p>📭 Nenhum log encontrado com os filtros selecionados.</p>
        </div>
        <?php endif; ?>

    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
    function verDetalhes(logId, detalhes) {
        const detalhesFormatados = JSON.stringify(detalhes, null, 2);
        alert('Detalhes do Log #' + logId + ':\n\n' + detalhesFormatados);
    }
    </script>

</body>
</html>