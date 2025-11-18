<?php
/**
 * Verificar Disponibilidade
 * Verifica se um espaço está disponível num determinado horário
 * Retorna JSON com disponibilidade e horários livres
 */

session_start();
require_once '../config/database.php';

// Verificar se está autenticado
if (!isset($_SESSION['utilizador_id'])) {
    echo json_encode(['erro' => 'Não autenticado']);
    exit();
}

// Receber dados via POST
$data = isset($_POST['data']) ? $_POST['data'] : '';
$espaco_id = isset($_POST['espaco_id']) ? (int)$_POST['espaco_id'] : 0;
$hora_inicio = isset($_POST['hora_inicio']) ? $_POST['hora_inicio'] : '';
$hora_fim = isset($_POST['hora_fim']) ? $_POST['hora_fim'] : '';

// Validar dados
if (empty($data) || $espaco_id == 0 || empty($hora_inicio) || empty($hora_fim)) {
    echo json_encode(['disponivel' => false, 'mensagem' => 'Dados inválidos']);
    exit();
}

try {
    // Verificar se existe conflito de horário
    $sql = "SELECT COUNT(*) as conflitos 
            FROM reserva 
            WHERE espaco_id = :espaco_id 
            AND data = :data 
            AND estado = 'confirmada'
            AND (
                (hora_inicio < :hora_fim AND hora_fim > :hora_inicio)
            )";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':espaco_id', $espaco_id, PDO::PARAM_INT);
    $stmt->bindParam(':data', $data, PDO::PARAM_STR);
    $stmt->bindParam(':hora_inicio', $hora_inicio, PDO::PARAM_STR);
    $stmt->bindParam(':hora_fim', $hora_fim, PDO::PARAM_STR);
    $stmt->execute();
    
    $resultado = $stmt->fetch();
    $conflitos = $resultado['conflitos'];
    
    // Buscar todas as reservas deste espaço neste dia
    $sql_reservas = "SELECT hora_inicio, hora_fim, turma 
                     FROM reserva 
                     WHERE espaco_id = :espaco_id 
                     AND data = :data 
                     AND estado = 'confirmada'
                     ORDER BY hora_inicio";
    
    $stmt_reservas = $pdo->prepare($sql_reservas);
    $stmt_reservas->bindParam(':espaco_id', $espaco_id, PDO::PARAM_INT);
    $stmt_reservas->bindParam(':data', $data, PDO::PARAM_STR);
    $stmt_reservas->execute();
    $reservas_existentes = $stmt_reservas->fetchAll();
    
    // Calcular horários livres (08:15 - 18:00)
    $horarios_livres = [];
    
    // Blocos de horário da escola (início e fim de cada tempo)
    $blocos_escola = [
        ['inicio' => '08:15:00', 'fim' => '09:05:00', 'nome' => '1º Tempo'],
        ['inicio' => '09:10:00', 'fim' => '10:00:00', 'nome' => '2º Tempo'],
        ['inicio' => '10:20:00', 'fim' => '11:10:00', 'nome' => '3º Tempo'],
        ['inicio' => '11:15:00', 'fim' => '12:05:00', 'nome' => '4º Tempo'],
        ['inicio' => '12:10:00', 'fim' => '13:00:00', 'nome' => '5º Tempo'],
        ['inicio' => '13:15:00', 'fim' => '14:05:00', 'nome' => '6º Tempo'],
        ['inicio' => '14:10:00', 'fim' => '15:00:00', 'nome' => '7º Tempo'],
        ['inicio' => '15:05:00', 'fim' => '15:55:00', 'nome' => '8º Tempo'],
        ['inicio' => '16:15:00', 'fim' => '17:05:00', 'nome' => '9º Tempo'],
        ['inicio' => '17:10:00', 'fim' => '18:00:00', 'nome' => '10º Tempo']
    ];
    
    if (count($reservas_existentes) == 0) {
        // Nenhuma reserva, todo o dia está livre
        $horarios_livres[] = '08:15 - 18:00 (Todos os tempos disponíveis)';
    } else {
        // Verificar quais blocos estão livres
        foreach ($blocos_escola as $bloco) {
            $bloco_livre = true;
            
            // Verificar se este bloco conflita com alguma reserva existente
            foreach ($reservas_existentes as $reserva) {
                // Se a reserva ocupa este bloco
                if ($reserva['hora_inicio'] < $bloco['fim'] && $reserva['hora_fim'] > $bloco['inicio']) {
                    $bloco_livre = false;
                    break;
                }
            }
            
            if ($bloco_livre) {
                $horarios_livres[] = substr($bloco['inicio'], 0, 5) . ' - ' . substr($bloco['fim'], 0, 5) . ' (' . $bloco['nome'] . ')';
            }
        }
    }
    
    // Buscar nome do espaço
    $sql_espaco = "SELECT nome FROM espaco WHERE espaco_id = :espaco_id";
    $stmt_espaco = $pdo->prepare($sql_espaco);
    $stmt_espaco->bindParam(':espaco_id', $espaco_id);
    $stmt_espaco->execute();
    $espaco = $stmt_espaco->fetch();
    
    // Retornar resultado
    if ($conflitos > 0) {
        echo json_encode([
            'disponivel' => false, 
            'mensagem' => 'Espaço já reservado neste horário!',
            'horarios_livres' => $horarios_livres,
            'espaco_nome' => $espaco['nome']
        ]);
    } else {
        echo json_encode([
            'disponivel' => true, 
            'mensagem' => 'Espaço disponível!',
            'horarios_livres' => $horarios_livres,
            'espaco_nome' => $espaco['nome']
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode(['disponivel' => false, 'mensagem' => 'Erro: ' . $e->getMessage()]);
}
?>