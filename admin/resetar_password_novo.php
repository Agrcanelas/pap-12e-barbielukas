<?php
/**
 * Resetar Password
 * Admin pode resetar a password de qualquer professor
 */

session_start();
$base_path = '../';

require_once '../config/database.php';
require_once '../config/log.php'; // ← NOVO: Adicionar esta linha

// Verificar se está autenticado e é admin
if (!isset($_SESSION['utilizador_id']) || $_SESSION['tipo'] != 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

// Receber ID do utilizador
$utilizador_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($utilizador_id == 0) {
    header('Location: gerir_utilizadores.php?erro=id');
    exit();
}

try {
    // Buscar dados do utilizador
    $sql = "SELECT * FROM utilizador WHERE utilizador_id = :id AND tipo = 'professor'";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $utilizador_id);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        header('Location: gerir_utilizadores.php?erro=nao_encontrado');
        exit();
    }
    
    $utilizador = $stmt->fetch();
    
    // Gerar password temporária aleatória (forte e segura)
    $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%';
    $password_nova = '';
    $tamanho = 12;
    
    for ($i = 0; $i < $tamanho; $i++) {
        $password_nova .= $caracteres[random_int(0, strlen($caracteres) - 1)];
    }
    
    // Encriptar password
    $password_encriptada = password_hash($password_nova, PASSWORD_DEFAULT);
    
    // Atualizar na base de dados
    $sql_update = "UPDATE utilizador SET password = :password WHERE utilizador_id = :id";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->bindParam(':password', $password_encriptada);
    $stmt_update->bindParam(':id', $utilizador_id);
    $stmt_update->execute();

    // ✅ NOVO: Registar no log
    $descricao = "Resetou a password de '{$utilizador['nome']}'";
    $detalhes = [
        'utilizador_id' => $utilizador_id,
        'nome' => $utilizador['nome'],
        'email' => $utilizador['email']
    ];
    registarLog($pdo, $_SESSION['utilizador_id'], 'password_resetada', $descricao, $detalhes);
    
    // Sucesso
    $sucesso = true;
    
} catch (PDOException $e) {
    header('Location: gerir_utilizadores.php?erro=bd');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Resetada - Sistema de Reservas</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=99">
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <div class="container">
        
        <div class="reset-password-page">
            <!-- Card Principal -->
            <div class="reset-card-success">
                
                <!-- Ícone de Sucesso -->
                <div class="success-icon-big">
                    🔑
                </div>

                <h1>Password Resetada!</h1>
                <p class="subtitle-reset">A password de <strong><?php echo htmlspecialchars($utilizador['nome']); ?></strong> foi alterada com sucesso.</p>

                <!-- Box da Password -->
                <div class="password-reveal-box">
                    <label>🔐 Nova Password Temporária:</label>
                    <div class="password-display-area">
                        <code id="passwordTexto"><?php echo htmlspecialchars($password_nova); ?></code>
                    </div>
                    <button onclick="copiarPassword()" class="btn-copiar-grande" id="btnCopiar">
                        <span class="icon">📋</span>
                        <span class="texto">Copiar Password</span>
                    </button>
                </div>

                <!-- Informações do Utilizador -->
                <div class="user-info-box">
                    <div class="info-item">
                        <span class="info-label">👤 Nome:</span>
                        <span class="info-value"><?php echo htmlspecialchars($utilizador['nome']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">📧 Email:</span>
                        <span class="info-value"><?php echo htmlspecialchars($utilizador['email']); ?></span>
                    </div>
                </div>

                <!-- Instruções -->
                <div class="instructions-box">
                    <h3>📝 Próximos Passos:</h3>
                    <ol>
                        <li>Copie a password acima (clique no botão)</li>
                        <li>Informe o professor através de um canal seguro</li>
                        <li>O professor deve fazer login com estas credenciais</li>
                        <li>Recomende que altere a password após o primeiro acesso</li>
                    </ol>
                </div>

                <!-- Aviso Importante -->
                <div class="warning-box-big">
                    <div class="warning-icon">⚠️</div>
                    <div class="warning-text">
                        <strong>Atenção!</strong> Esta password só será mostrada UMA VEZ. Certifique-se de copiá-la antes de sair desta página!
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="action-buttons">
                    <a href="gerir_utilizadores.php" class="btn btn-secondary btn-large">
                        ← Voltar à Gestão
                    </a>
                    <button onclick="resetarOutro()" class="btn btn-primary btn-large">
                        🔑 Resetar Outro
                    </button>
                </div>

            </div>

        </div>

    </div>

    <?php include '../includes/footer.php'; ?>
    <script>
    function copiarPassword() {
        const texto = document.getElementById('passwordTexto').textContent;
        const btn = document.getElementById('btnCopiar');
        
        // Copiar para clipboard
        navigator.clipboard.writeText(texto).then(function() {
            // Feedback visual
            btn.innerHTML = '<span class="icon">✅</span><span class="texto">Copiado!</span>';
            btn.style.background = 'linear-gradient(135deg, #28A745, #20C997)';
            
            // Voltar ao normal após 2 segundos
            setTimeout(function() {
                btn.innerHTML = '<span class="icon">📋</span><span class="texto">Copiar Password</span>';
                btn.style.background = '';
            }, 2000);
        }).catch(function() {
            alert('Erro ao copiar. Por favor, copie manualmente.');
        });
    }
    
    function resetarOutro() {
        if (confirm('Deseja resetar a password de outro utilizador?')) {
            window.location.href = 'gerir_utilizadores.php';
        }
    }
    </script>

</body>
</html>