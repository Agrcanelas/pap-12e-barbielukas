<?php
/**
 * Processar Utilizador
 * Criar, editar ou remover utilizadores (professores)
 */

session_start();
require_once '../config/database.php';
require_once '../config/log.php'; // ← NOVO: Incluir sistema de logs

// Verificar se está autenticado e é admin
if (!isset($_SESSION['utilizador_id']) || $_SESSION['tipo'] != 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

// Receber ação
$acao = isset($_POST['acao']) ? $_POST['acao'] : (isset($_GET['acao']) ? $_GET['acao'] : '');

try {
    
    // ============================================
    // CRIAR UTILIZADOR
    // ============================================
    if ($acao == 'criar') {
        $nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';
        
        if (empty($nome) || empty($email)) {
            header('Location: gerir_utilizadores.php?erro=dados');
            exit();
        }
        
        // Verificar se email já existe
        $sql_verifica = "SELECT COUNT(*) as total FROM utilizador WHERE email = :email";
        $stmt_verifica = $pdo->prepare($sql_verifica);
        $stmt_verifica->bindParam(':email', $email);
        $stmt_verifica->execute();
        $resultado = $stmt_verifica->fetch();
        
        if ($resultado['total'] > 0) {
            header('Location: gerir_utilizadores.php?erro=email_existe');
            exit();
        }
        
        // Gerar password padrão se vazio
        if (empty($password)) {
            $password = 'professor123';
        }
        
        // Encriptar password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO utilizador (nome, email, password, tipo) VALUES (:nome, :email, :password, 'professor')";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password_hash);
        
        if ($stmt->execute()) {
            // ✅ NOVO: Registar no log
            $descricao = "Criou o utilizador '{$nome}' ({$email})";
            $detalhes = [
                'nome' => $nome,
                'email' => $email,
                'tipo' => 'professor'
            ];
            registarLog($pdo, $_SESSION['utilizador_id'], 'utilizador_criado', $descricao, $detalhes);
            
            header('Location: gerir_utilizadores.php?sucesso=criado');
        } else {
            header('Location: gerir_utilizadores.php?erro=bd');
        }
    }
    
    // ============================================
    // EDITAR UTILIZADOR
    // ============================================
    elseif ($acao == 'editar') {
        $utilizador_id = isset($_POST['utilizador_id']) ? (int)$_POST['utilizador_id'] : 0;
        $nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        
        if ($utilizador_id == 0 || empty($nome) || empty($email)) {
            header('Location: gerir_utilizadores.php?erro=dados');
            exit();
        }
        
        // Verificar se email já existe (exceto o próprio)
        $sql_verifica = "SELECT COUNT(*) as total FROM utilizador WHERE email = :email AND utilizador_id != :utilizador_id";
        $stmt_verifica = $pdo->prepare($sql_verifica);
        $stmt_verifica->bindParam(':email', $email);
        $stmt_verifica->bindParam(':utilizador_id', $utilizador_id);
        $stmt_verifica->execute();
        $resultado = $stmt_verifica->fetch();
        
        if ($resultado['total'] > 0) {
            header('Location: gerir_utilizadores.php?erro=email_existe');
            exit();
        }
        
        $sql = "UPDATE utilizador SET nome = :nome, email = :email WHERE utilizador_id = :utilizador_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':utilizador_id', $utilizador_id);
        
         if ($stmt->execute()) {
            // ✅ NOVO: Registar no log
            $descricao = "Editou o utilizador '{$nome}' ({$email})";
            $detalhes = [
                'utilizador_id' => $utilizador_id,
                'nome' => $nome,
                'email' => $email
            ];
            registarLog($pdo, $_SESSION['utilizador_id'], 'utilizador_editado', $descricao, $detalhes);
            
            header('Location: gerir_utilizadores.php?sucesso=editado');
        } else {
            header('Location: gerir_utilizadores.php?erro=bd');
        } 
    }
    
    // ============================================
    // REMOVER UTILIZADOR
    // ============================================
    elseif ($acao == 'remover') {
        $utilizador_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($utilizador_id == 0) {
            header('Location: gerir_utilizadores.php?erro=id');
            exit();
        }
        
        // Não permitir remover o próprio admin
        if ($utilizador_id == $_SESSION['utilizador_id']) {
            header('Location: gerir_utilizadores.php?erro=proprio');
            exit();
        }
        
        // Buscar nome antes de remover
        $sql_buscar = "SELECT nome, email FROM utilizador WHERE utilizador_id = :utilizador_id";
        $stmt_buscar = $pdo->prepare($sql_buscar);
        $stmt_buscar->bindParam(':utilizador_id', $utilizador_id);
        $stmt_buscar->execute();
        $utilizador = $stmt_buscar->fetch();
        $nome_utilizador = $utilizador ? $utilizador['nome'] : 'Utilizador desconhecido';
        $email_utilizador = $utilizador ? $utilizador['email'] : '';
        
        $sql = "DELETE FROM utilizador WHERE utilizador_id = :utilizador_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':utilizador_id', $utilizador_id);
        
        if ($stmt->execute()) {
            // ✅ NOVO: Registar no log
            $descricao = "Removeu o utilizador '{$nome_utilizador}' ({$email_utilizador})";
            $detalhes = [
                'utilizador_id' => $utilizador_id,
                'nome' => $nome_utilizador,
                'email' => $email_utilizador
            ];
            registarLog($pdo, $_SESSION['utilizador_id'], 'utilizador_removido', $descricao, $detalhes);
            
            header('Location: gerir_utilizadores.php?sucesso=removido');
        } else {
            header('Location: gerir_utilizadores.php?erro=bd');
        }
    }
    
    else {
        header('Location: gerir_utilizadores.php?erro=acao');
    }
    
} catch (PDOException $e) {
    header('Location: gerir_utilizadores.php?erro=bd');
}
?>