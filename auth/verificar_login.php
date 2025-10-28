<?php
/**
 * Verificar Login
 * Processa o formulário de login e autentica o utilizador
 */

session_start();
require_once '../config/database.php';

// Verificar se o formulário foi submetido
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Receber dados do formulário
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    
    // Validar se os campos estão preenchidos
    if (empty($email) || empty($password)) {
        header('Location: login.php?erro=campos');
        exit();
    }
    
    try {
        // Procurar utilizador na base de dados pelo email
        $sql = "SELECT * FROM utilizador WHERE email = :email LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        
        // Verificar se encontrou o utilizador
        if ($stmt->rowCount() > 0) {
            $utilizador = $stmt->fetch();
            
            // Verificar a password
            // NOTA: A password na BD deve estar encriptada com password_hash()
            if (password_verify($password, $utilizador['password'])) {
                
                // Login bem-sucedido! Criar sessão
                $_SESSION['utilizador_id'] = $utilizador['utilizador_id'];
                $_SESSION['nome'] = $utilizador['nome'];
                $_SESSION['email'] = $utilizador['email'];
                $_SESSION['tipo'] = $utilizador['tipo'];
                
                // Redirecionar conforme o tipo de utilizador
                if ($utilizador['tipo'] == 'admin') {
                    header('Location: ../admin/index.php');
                } else {
                    header('Location: ../professor/index.php');
                }
                exit();
                
            } else {
                // Password incorreta
                header('Location: login.php?erro=credenciais');
                exit();
            }
            
        } else {
            // Email não encontrado
            header('Location: login.php?erro=credenciais');
            exit();
        }
        
    } catch (PDOException $e) {
        // Erro na base de dados
        die("Erro ao verificar login: " . $e->getMessage());
    }
    
} else {
    // Se tentar aceder diretamente sem POST, redirecionar para login
    header('Location: login.php');
    exit();
}
?>