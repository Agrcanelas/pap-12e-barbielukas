<?php
/**
 * Calendário de Reservas
 * Visualizar disponibilidade e fazer reservas
 */

session_start();
$base_path = '../';

// Verificar se está autenticado e é professor
if (!isset($_SESSION['utilizador_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

if ($_SESSION['tipo'] != 'professor') {
    header('Location: ../admin/index.php');
    exit();
}

require_once '../config/database.php';

// Obter mês e ano (padrão: mês atual)
$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : date('n');
$ano = isset($_GET['ano']) ? (int)$_GET['ano'] : date('Y');

// Validar mês e ano
if ($mes < 1) { $mes = 12; $ano--; }
if ($mes > 12) { $mes = 1; $ano++; }

// Informações do mês
$primeiro_dia = mktime(0, 0, 0, $mes, 1, $ano);
$numero_dias = date('t', $primeiro_dia);
$dia_semana_inicio = date('N', $primeiro_dia); // 1 = Segunda, 7 = Domingo
$nome_mes = strftime('%B', $primeiro_dia);

// Buscar todos os espaços
try {
    $sql_espacos = "SELECT * FROM espaco ORDER BY nome";
    $espacos = $pdo->query($sql_espacos)->fetchAll();
    
    // Buscar ocupação de todos os espaços neste mês
    $primeiro_dia_mes = sprintf('%04d-%02d-01', $ano, $mes);
    $ultimo_dia_mes = sprintf('%04d-%02d-%02d', $ano, $mes, $numero_dias);
    
    // Tempos letivos (50 min cada)
    $tempos_dia = [
        ['inicio' => '08:15', 'fim' => '09:05'],
        ['inicio' => '09:10', 'fim' => '10:00'],
        ['inicio' => '10:20', 'fim' => '11:10'],
        ['inicio' => '11:15', 'fim' => '12:05'],
        ['inicio' => '12:10', 'fim' => '13:00'],
        ['inicio' => '13:15', 'fim' => '14:05'],
        ['inicio' => '14:10', 'fim' => '15:00'],
        ['inicio' => '15:05', 'fim' => '15:55'],
        ['inicio' => '16:15', 'fim' => '17:05'],
        ['inicio' => '17:10', 'fim' => '18:00']
    ];
    
    $sql_ocupacao = "SELECT espaco_id, data, hora_inicio, hora_fim
                     FROM reserva 
                     WHERE estado = 'confirmada' 
                     AND data BETWEEN :data_inicio AND :data_fim";
    
    $stmt_ocupacao = $pdo->prepare($sql_ocupacao);
    $stmt_ocupacao->bindParam(':data_inicio', $primeiro_dia_mes);
    $stmt_ocupacao->bindParam(':data_fim', $ultimo_dia_mes);
    $stmt_ocupacao->execute();
    $reservas_mes = $stmt_ocupacao->fetchAll();
    
    // Calcular tempos ocupados por espaço/data
    $ocupacao_mapa = [];
    foreach ($reservas_mes as $reserva) {
        $espaco_id = $reserva['espaco_id'];
        $data = $reserva['data'];
        $hora_inicio = $reserva['hora_inicio'];
        $hora_fim = $reserva['hora_fim'];
        
        if (!isset($ocupacao_mapa[$espaco_id])) {
            $ocupacao_mapa[$espaco_id] = [];
        }
        if (!isset($ocupacao_mapa[$espaco_id][$data])) {
            $ocupacao_mapa[$espaco_id][$data] = 0;
        }
        
        // Contar quantos tempos esta reserva ocupa
        foreach ($tempos_dia as $tempo) {
            $tempo_inicio = $tempo['inicio'] . ':00';
            $tempo_fim = $tempo['fim'] . ':00';
            
            // Se a reserva sobrepõe este tempo
            if ($hora_inicio < $tempo_fim && $hora_fim > $tempo_inicio) {
                $ocupacao_mapa[$espaco_id][$data]++;
            }
        }
    }
    
} catch (PDOException $e) {
    die("Erro ao buscar espaços: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendário - Sistema de Reservas</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <div class="container">
        
        <div class="page-header">
            <h1>📆 Calendário de Reservas</h1>
            <p>Selecione um dia para ver a disponibilidade</p>
        </div>

        <!-- Navegação do Calendário -->
        <div class="calendario-nav">
            <a href="?mes=<?php echo $mes-1; ?>&ano=<?php echo $ano; ?>" class="btn-nav">← Mês Anterior</a>
            <h2><?php echo ucfirst($nome_mes) . ' ' . $ano; ?></h2>
            <a href="?mes=<?php echo $mes+1; ?>&ano=<?php echo $ano; ?>" class="btn-nav">Próximo Mês →</a>
        </div>

        <!-- Filtro de Espaço -->
        <div class="filtro-espaco-container">
            <label for="filtroEspaco">🏫 Filtrar por espaço:</label>
            <select id="filtroEspaco" onchange="filtrarPorEspaco()">
                <option value="">📅 Todos os espaços (visão geral)</option>
                <?php foreach ($espacos as $espaco): ?>
                <option value="<?php echo $espaco['espaco_id']; ?>">
                    <?php echo htmlspecialchars($espaco['nome']); ?> (<?php echo $espaco['tipo_espaco']; ?>)
                </option>
                <?php endforeach; ?>
            </select>
            <div id="legendaOcupacao" class="legenda-ocupacao" style="display: none;">
                <span class="legenda-item"><span class="cor-livre"></span> Livre (0-3 tempos)</span>
                <span class="legenda-item"><span class="cor-parcial"></span> Parcialmente ocupado (4-7 tempos)</span>
                <span class="legenda-item"><span class="cor-ocupado"></span> Muito ocupado (8-10 tempos)</span>
            </div>
        </div>

        <!-- Calendário -->
        <div class="calendario">
            <!-- Cabeçalho dos dias da semana -->
            <div class="calendario-header">
                <div class="dia-semana">Seg</div>
                <div class="dia-semana">Ter</div>
                <div class="dia-semana">Qua</div>
                <div class="dia-semana">Qui</div>
                <div class="dia-semana">Sex</div>
                <div class="dia-semana">Sáb</div>
                <div class="dia-semana">Dom</div>
            </div>

            <!-- Dias do mês -->
            <div class="calendario-body">
                <?php
                // Espaços vazios antes do primeiro dia
                for ($i = 1; $i < $dia_semana_inicio; $i++) {
                    echo '<div class="dia-vazio"></div>';
                }

                // Dias do mês
                $hoje = date('Y-m-d');
                for ($dia = 1; $dia <= $numero_dias; $dia++) {
                    $data_completa = sprintf('%04d-%02d-%02d', $ano, $mes, $dia);
                    $eh_passado = strtotime($data_completa) < strtotime($hoje);
                    $eh_hoje = $data_completa == $hoje;
                    
                    $classe = 'dia';
                    if ($eh_passado) $classe .= ' dia-passado';
                    if ($eh_hoje) $classe .= ' dia-hoje';
                    
                    // Adicionar atributos de ocupação para cada espaço
                    $ocupacao_data = [];
                    foreach ($espacos as $espaco) {
                        $espaco_id = $espaco['espaco_id'];
                        if (isset($ocupacao_mapa[$espaco_id][$data_completa])) {
                            $tempos_ocupados = $ocupacao_mapa[$espaco_id][$data_completa];
                            // Classificar: 8+ tempos = vermelho, 4-7 = laranja, 0-3 = verde
                            if ($tempos_ocupados >= 8) {
                                $nivel = 'ocupado';
                            } elseif ($tempos_ocupados >= 4) {
                                $nivel = 'parcial';
                            } else {
                                $nivel = 'livre';
                            }
                            $ocupacao_data[] = $espaco_id . ':' . $nivel;
                        } else {
                            $ocupacao_data[] = $espaco_id . ':livre';
                        }
                    }
                    
                    echo '<div class="' . $classe . '" data-data="' . $data_completa . '" data-ocupacao="' . implode('|', $ocupacao_data) . '">';
                    echo '<span class="numero-dia">' . $dia . '</span>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>

    </div>

    <!-- Modal para selecionar horário e espaço -->
    <div id="modalReserva" class="modal">
        <div class="modal-content modal-reserva-melhorado">
            <span class="modal-fechar">&times;</span>
            
            <div class="modal-header-custom">
                <h2>📅 Fazer Reserva</h2>
                <p id="dataEscolhida" class="data-selecionada"></p>
            </div>

            <form id="formReserva" method="POST" action="processar_reserva.php">
                <input type="hidden" name="data" id="inputData">
                
                <div class="form-section">
                    <h3>🏫 Selecionar Espaço</h3>
                    
                    <!-- Selecionar Espaço -->
                    <div class="form-group-custom">
                        <label for="espaco">
                            <span class="label-icon">📍</span>
                            Espaço *
                        </label>
                        <select name="espaco_id" id="espaco" required>
                            <option value="">Selecione um espaço</option>
                            <?php foreach ($espacos as $espaco): ?>
                            <option value="<?php echo $espaco['espaco_id']; ?>">
                                <?php echo htmlspecialchars($espaco['nome']); ?> 
                                (Cap: <?php echo $espaco['capacidade']; ?> | <?php echo $espaco['tipo_espaco']; ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-section">
                    <h3>⏰ Selecionar Horário</h3>
                    <p class="ajuda-texto">💡 Pode selecionar um ou mais tempos consecutivos</p>
                    
                    <div class="horario-grid">
                        <!-- Selecionar Hora Início -->
                        <div class="form-group-custom">
                            <label for="hora_inicio">
                                <span class="label-icon">▶️</span>
                                Hora de Início *
                            </label>
                            <select name="hora_inicio" id="hora_inicio" required>
                                <option value="">Selecione</option>
                                <option value="08:15">⏰ 08:15 (1º Tempo)</option>
                                <option value="09:10">⏰ 09:10 (2º Tempo)</option>
                                <option value="10:20">⏰ 10:20 (3º Tempo)</option>
                                <option value="11:15">⏰ 11:15 (4º Tempo)</option>
                                <option value="12:10">⏰ 12:10 (5º Tempo)</option>
                                <option value="13:15">⏰ 13:15 (6º Tempo)</option>
                                <option value="14:10">⏰ 14:10 (7º Tempo)</option>
                                <option value="15:05">⏰ 15:05 (8º Tempo)</option>
                                <option value="16:15">⏰ 16:15 (9º Tempo)</option>
                                <option value="17:10">⏰ 17:10 (10º Tempo)</option>
                            </select>
                        </div>

                        <!-- Selecionar Hora Fim -->
                        <div class="form-group-custom">
                            <label for="hora_fim">
                                <span class="label-icon">⏹️</span>
                                Hora de Fim *
                            </label>
                            <select name="hora_fim" id="hora_fim" required>
                                <option value="">Selecione</option>
                                <option value="09:05">⏰ 09:05 (fim 1º Tempo)</option>
                                <option value="10:00">⏰ 10:00 (fim 2º Tempo)</option>
                                <option value="11:10">⏰ 11:10 (fim 3º Tempo)</option>
                                <option value="12:05">⏰ 12:05 (fim 4º Tempo)</option>
                                <option value="13:00">⏰ 13:00 (fim 5º Tempo)</option>
                                <option value="14:05">⏰ 14:05 (fim 6º Tempo)</option>
                                <option value="15:00">⏰ 15:00 (fim 7º Tempo)</option>
                                <option value="15:55">⏰ 15:55 (fim 8º Tempo)</option>
                                <option value="17:05">⏰ 17:05 (fim 9º Tempo)</option>
                                <option value="18:00">⏰ 18:00 (fim 10º Tempo)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>📝 Informações da Aula</h3>
                    
                    <div class="info-grid">
                        <!-- Turma -->
                        <div class="form-group-custom">
                            <label for="turma">
                                <span class="label-icon">👥</span>
                                Turma *
                            </label>
                            <input type="text" name="turma" id="turma" placeholder="Ex: 12ºA" required>
                        </div>

                        <!-- Nome do Professor (pré-preenchido) -->
                        <div class="form-group-custom">
                            <label for="nome_professor">
                                <span class="label-icon">👨‍🏫</span>
                                Professor *
                            </label>
                            <input type="text" name="nome_professor" id="nome_professor" 
                                   value="<?php echo htmlspecialchars($_SESSION['nome']); ?>" required>
                        </div>
                    </div>
                </div>

                <!-- Visualização de disponibilidade -->
                <div id="disponibilidade" class="disponibilidade-info-melhorada">
                    <p>🔍 Selecione o espaço e horário para verificar disponibilidade</p>
                </div>

                <button type="submit" class="btn-submit-melhorado">
                    <span>✅</span> Confirmar Reserva
                </button>
            </form>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="../assets/js/calendario.js"></script>

</body>
</html>