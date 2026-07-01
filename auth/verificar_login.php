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
    $tipos_validos = ['professor', 'admin'];
    $tipo_selecionado = isset($_POST['tipo_selecionado']) && in_array($_POST['tipo_selecionado'], $tipos_validos) ? $_POST['tipo_selecionado'] : '';
    $login_url = 'login.php' . (!empty($tipo_selecionado) ? '?tipo=' . urlencode($tipo_selecionado) : '');
    $separador_erro = !empty($tipo_selecionado) ? '&' : '?';
    $email_admin = 'admin@canelas.pt';
    $email_normalizado = strtolower($email);
    
    // Validar se os campos estão preenchidos
    if (empty($email) || empty($password)) {
        header('Location: ' . $login_url . $separador_erro . 'erro=campos');
        exit();
    }

    // Separar de forma explícita as entradas de admin e professor
    if ($tipo_selecionado == 'admin' && $email_normalizado !== $email_admin) {
        header('Location: ' . $login_url . $separador_erro . 'erro=email_admin');
        exit();
    }

    if ($tipo_selecionado == 'professor' && $email_normalizado === $email_admin) {
        header('Location: ' . $login_url . $separador_erro . 'erro=email_professor');
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
                if (!empty($tipo_selecionado) && $utilizador['tipo'] !== $tipo_selecionado) {
                    header('Location: ' . $login_url . $separador_erro . 'erro=tipo_utilizador');
                    exit();
                }
                
                session_regenerate_id(true);
                
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
                header('Location: ' . $login_url . $separador_erro . 'erro=credenciais');
                exit();
            }
            
        } else {
            // Email não encontrado
            header('Location: ' . $login_url . $separador_erro . 'erro=credenciais');
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