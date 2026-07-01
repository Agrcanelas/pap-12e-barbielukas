<?php
/**
 * Página de Login
 * Sistema de Reservas de Espaços - Agrupamento de Escolas Canelas
 */

session_start();

$tipos_validos = ['professor', 'admin'];
$tipo_selecionado = isset($_GET['tipo']) && in_array($_GET['tipo'], $tipos_validos) ? $_GET['tipo'] : '';

function terminarSessaoAtual() {
    $_SESSION = array();

    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 42000, '/');
    }

    session_destroy();
}

// Se já estiver autenticado, só reaproveitar a sessão se o tipo escolhido for igual
if (isset($_SESSION['utilizador_id'])) {
    if (!empty($tipo_selecionado) && ($_SESSION['tipo'] ?? '') !== $tipo_selecionado) {
        terminarSessaoAtual();
        header('Location: login.php?tipo=' . urlencode($tipo_selecionado) . '&erro=tipo_sessao');
        exit();
    }

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
        <p class="subtitle">
            <?php if ($tipo_selecionado == 'professor'): ?>
                Entrada de Professor
            <?php elseif ($tipo_selecionado == 'admin'): ?>
                Entrada de Administrador
            <?php else: ?>
                Agrupamento de Escolas Canelas
            <?php endif; ?>
        </p>

        <!-- Formulário de Login -->
        <form action="verificar_login.php" method="POST" class="login-form">
            <input type="hidden" name="tipo_selecionado" value="<?php echo htmlspecialchars($tipo_selecionado); ?>">
            
            <!-- Mensagens de Erro -->
            <?php if ($erro == 'credenciais'): ?>
                <div class="alert alert-erro">
                    <strong>Erro!</strong> Email ou palavra-passe incorretos.
                </div>
            <?php elseif ($erro == 'campos'): ?>
                <div class="alert alert-erro">
                    <strong>Atenção!</strong> Preencha todos os campos.
                </div>
            <?php elseif ($erro == 'logout'): ?>
                <div class="alert alert-sucesso">
                    <strong>Sessão terminada!</strong> Faça login novamente.
                </div>
            <?php elseif ($erro == 'tipo_sessao'): ?>
                <div class="alert alert-aviso">
                    <strong>Atenção!</strong> A sessão anterior era de outro tipo. Faça login novamente.
                </div>
            <?php elseif ($erro == 'tipo_utilizador'): ?>
                <div class="alert alert-erro">
                    <strong>Acesso negado!</strong> Estas credenciais não pertencem ao tipo de acesso escolhido.
                </div>
            <?php elseif ($erro == 'email_admin'): ?>
                <div class="alert alert-erro">
                    <strong>Acesso negado!</strong> A entrada de administrador só aceita o email admin@canelas.pt.
                </div>
            <?php elseif ($erro == 'email_professor'): ?>
                <div class="alert alert-erro">
                    <strong>Acesso negado!</strong> O email admin@canelas.pt não pode ser usado na entrada de professor.
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
                <label for="password">Palavra-passe</label>
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

            <?php if ($tipo_selecionado == 'professor'): ?>
                <div class="forgot-password-link">
                    <a href="recuperar_palavra_passe.php">Esqueceu-se da palavra-passe?</a>
                </div>
            <?php endif; ?>

        </form>

        <!-- Rodapé -->
        <div class="login-footer">
            <p>&copy; 2025 Agrupamento de Escolas Canelas</p>
        </div>
    </div>

</body>
</html>