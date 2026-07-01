<?php
/**
 * Recuperar Palavra-passe
 * Informa o professor sobre o processo seguro de reposição de palavra-passe
 */
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Palavra-passe - Sistema de Reservas</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-container recovery-container">
        <div class="logo-container">
            <img src="../assets/images/logo.png" alt="Agrupamento de Escolas Canelas" class="logo">
        </div>

        <h1>Recuperar Palavra-passe</h1>
        <p class="subtitle">Entrada de Professor</p>

        <div class="recovery-info">
            <p>
                Por motivos de segurança, a palavra-passe não pode ser consultada nem enviada automaticamente.
            </p>
            <p>
                Se se esqueceu da palavra-passe, contacte o administrador do sistema e peça a reposição da sua conta.
            </p>

            <div class="recovery-contact">
                <strong>Contacto do administrador:</strong>
                <span>admin@canelas.pt</span>
            </div>

            <p class="recovery-note">
                Indique o seu nome e email institucional para que o administrador consiga identificar a sua conta.
            </p>
        </div>

        <a href="login.php?tipo=professor" class="btn btn-primary btn-block">Voltar ao Login</a>

        <div class="login-footer">
            <p>&copy; 2025 Agrupamento de Escolas Canelas</p>
        </div>
    </div>
</body>
</html>