<?php
/**
 * Logout
 * Termina a sessão do utilizador e redireciona para o login
 */

session_start();

// Destruir todas as variáveis de sessão
$_SESSION = array();

// Destruir o cookie de sessão (se existir)
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// Destruir a sessão
session_destroy();

// Redirecionar para o login com mensagem
header('Location: login.php?erro=logout');
exit();
?>