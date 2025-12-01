<?php
/**
 * Sistema de Log de Ações
 * Registar todas as ações importantes no sistema
 */

/**
 * Registar ação no log
 * 
 * @param PDO $pdo Conexão à base de dados
 * @param int $utilizador_id ID do utilizador que fez a ação
 * @param string $tipo_acao Tipo de ação (reserva_criada, espaco_editado, etc.)
 * @param string $descricao Descrição curta da ação
 * @param array $detalhes Detalhes adicionais (opcional)
 * @return bool Sucesso ou falha
 */
function registarLog($pdo, $utilizador_id, $tipo_acao, $descricao, $detalhes = null) {
    try {
        // Obter IP do utilizador
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
        
        // Converter detalhes para JSON se for array
        $detalhes_json = null;
        if ($detalhes !== null && is_array($detalhes)) {
            $detalhes_json = json_encode($detalhes, JSON_UNESCAPED_UNICODE);
        }
        
        // Inserir no log
        $sql = "INSERT INTO log_acoes (utilizador_id, tipo_acao, descricao, detalhes, ip_address) 
                VALUES (:utilizador_id, :tipo_acao, :descricao, :detalhes, :ip_address)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':utilizador_id', $utilizador_id, PDO::PARAM_INT);
        $stmt->bindParam(':tipo_acao', $tipo_acao, PDO::PARAM_STR);
        $stmt->bindParam(':descricao', $descricao, PDO::PARAM_STR);
        $stmt->bindParam(':detalhes', $detalhes_json, PDO::PARAM_STR);
        $stmt->bindParam(':ip_address', $ip_address, PDO::PARAM_STR);
        
        return $stmt->execute();
        
    } catch (PDOException $e) {
        // Log de erro (opcional - não bloquear a aplicação se falhar)
        error_log("Erro ao registar log: " . $e->getMessage());
        return false;
    }
}
?>