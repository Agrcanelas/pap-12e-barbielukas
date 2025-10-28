<?php
/**
 * Página de Login
 * Sistema de Reservas de Espaços - Agrupamento de Escolas Canelas
 */

session_start();

// Se já estiver autenticado, redirecionar
if (isset($_SESSION['utilizador_id'])) {
    if ($_SESSION['tipo'] == 'admin') {
        header('Location: ../admin/index.php');
    } else {
        header('Location: ../professor/index.php');
    }
    exit();
}

// Verificar se há mensagem de erro
$erro = isset($_GET['erro']) ? $_GET['erro'] : '';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Reservas</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="login-page">
    
    <div class="login-container">
        <!-- Logo da Escola -->
        <div class="logo-container">
            <img src="../assets/images/logo.png" alt="Agrupamento de Escolas Canelas" class="logo">
        </div>

        <!-- Título -->
        <h1>Sistema de Reservas</h1>
        <p class="subtitle">Agrupamento de Escolas Canelas</p>

        <!-- Formulário de Login -->
        <form action="verificar_login.php" method="POST" class="login-form">
            
            <!-- Mensagens de Erro -->
            <?php if ($erro == 'credenciais'): ?>
                <div class="alert alert-erro">
                    <strong>Erro!</strong> Email ou password incorretos.
                </div>
            <?php elseif ($erro == 'campos'): ?>
                <div class="alert alert-erro">
                    <strong>Atenção!</strong> Preencha todos os campos.
                </div>
            <?php elseif ($erro == 'logout'): ?>
                <div class="alert alert-sucesso">
                    <strong>Sessão terminada!</strong> Faça login novamente.
                </div>
            <?php endif; ?>

            <!-- Campo Email -->
            <div class="form-group">
                <label for="email">Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="exemplo@canelas.pt" 
                    required 
                    autofocus
                >
            </div>

            <!-- Campo Password -->
            <div class="form-group">
                <label for="password">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="••••••••" 
                    required
                >
            </div>

            <!-- Botão de Login -->
            <button type="submit" class="btn btn-primary">
                Entrar
            </button>

        </form>

        <!-- Rodapé -->
        <div class="login-footer">
            <p>&copy; 2025 Agrupamento de Escolas Canelas</p>
        </div>
    </div>

</body>
</html>