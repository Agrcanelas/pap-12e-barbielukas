<?php
/**
 * Alterar Palavra-passe Própria
 * Permite ao utilizador autenticado alterar a sua palavra-passe
 */

session_start();
$base_path = '../';

if (!isset($_SESSION['utilizador_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/database.php';
require_once '../config/log.php';

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password_atual = isset($_POST['password_atual']) ? trim($_POST['password_atual']) : '';
    $password_nova = isset($_POST['password_nova']) ? trim($_POST['password_nova']) : '';
    $password_confirmar = isset($_POST['password_confirmar']) ? trim($_POST['password_confirmar']) : '';

    if (empty($password_atual) || empty($password_nova) || empty($password_confirmar)) {
        $erro = 'campos';
    } elseif (strlen($password_nova) < 6) {
        $erro = 'tamanho';
    } elseif ($password_nova !== $password_confirmar) {
        $erro = 'confirmacao';
    } elseif ($password_atual === $password_nova) {
        $erro = 'igual';
    } else {
        try {
            $sql = "SELECT password FROM utilizador WHERE utilizador_id = :id LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $_SESSION['utilizador_id'], PDO::PARAM_INT);
            $stmt->execute();
            $utilizador = $stmt->fetch();

            if (!$utilizador || !password_verify($password_atual, $utilizador['password'])) {
                $erro = 'atual';
            } else {
                $password_hash = password_hash($password_nova, PASSWORD_DEFAULT);

                $sql_update = "UPDATE utilizador SET password = :password WHERE utilizador_id = :id";
                $stmt_update = $pdo->prepare($sql_update);
                $stmt_update->bindParam(':password', $password_hash, PDO::PARAM_STR);
                $stmt_update->bindParam(':id', $_SESSION['utilizador_id'], PDO::PARAM_INT);
                $stmt_update->execute();

                registarLog(
                    $pdo,
                    $_SESSION['utilizador_id'],
                    'password_alterada',
                    "Alterou a própria palavra-passe",
                    [
                        'utilizador_id' => $_SESSION['utilizador_id'],
                        'email' => $_SESSION['email'],
                        'tipo' => $_SESSION['tipo']
                    ]
                );

                session_regenerate_id(true);
                $sucesso = 'alterada';
            }
        } catch (PDOException $e) {
            $erro = 'bd';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar Palavra-passe - Sistema de Reservas</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <div class="container">
        <div class="page-header">
            <h1>🔐 Alterar Palavra-passe</h1>
            <p>Atualize a sua palavra-passe de acesso ao sistema</p>
        </div>

        <div class="password-page">
            <div class="password-card">
                <?php if ($sucesso == 'alterada'): ?>
                    <div class="alert alert-sucesso">
                        <strong>Sucesso!</strong> A sua palavra-passe foi alterada com sucesso.
                    </div>
                <?php elseif ($erro == 'campos'): ?>
                    <div class="alert alert-erro">
                        <strong>Atenção!</strong> Preencha todos os campos.
                    </div>
                <?php elseif ($erro == 'tamanho'): ?>
                    <div class="alert alert-erro">
                        <strong>Atenção!</strong> A nova palavra-passe deve ter pelo menos 6 caracteres.
                    </div>
                <?php elseif ($erro == 'confirmacao'): ?>
                    <div class="alert alert-erro">
                        <strong>Atenção!</strong> A confirmação não corresponde à nova palavra-passe.
                    </div>
                <?php elseif ($erro == 'igual'): ?>
                    <div class="alert alert-erro">
                        <strong>Atenção!</strong> A nova palavra-passe deve ser diferente da palavra-passe atual.
                    </div>
                <?php elseif ($erro == 'atual'): ?>
                    <div class="alert alert-erro">
                        <strong>Erro!</strong> A palavra-passe atual está incorreta.
                    </div>
                <?php elseif ($erro == 'bd'): ?>
                    <div class="alert alert-erro">
                        <strong>Erro!</strong> Não foi possível alterar a palavra-passe. Tente novamente.
                    </div>
                <?php endif; ?>

                <form method="POST" action="alterar_password.php" class="password-form">
                    <div class="form-group">
                        <label for="password_atual">Palavra-passe atual *</label>
                        <input type="password" id="password_atual" name="password_atual" required autofocus>
                    </div>

                    <div class="form-group">
                        <label for="password_nova">Nova palavra-passe *</label>
                        <input type="password" id="password_nova" name="password_nova" minlength="6" required>
                        <small>Mínimo de 6 caracteres.</small>
                    </div>

                    <div class="form-group">
                        <label for="password_confirmar">Confirmar Nova palavra-passe *</label>
                        <input type="password" id="password_confirmar" name="password_confirmar" minlength="6" required>
                    </div>

                    <div class="password-actions">
                        <button type="submit" class="btn btn-primary">Guardar nova palavra-passe</button>
                        <?php if ($_SESSION['tipo'] == 'admin'): ?>
                            <a href="../admin/index.php" class="btn btn-secondary">Voltar</a>
                        <?php else: ?>
                            <a href="../professor/index.php" class="btn btn-secondary">Voltar</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

</body>
</html>