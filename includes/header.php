<?php
/**
 * Header/Menu do Sistema
 * Incluir em todas as páginas após login
 */

// Verificar se a sessão está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Determinar o caminho base (ajusta conforme a profundidade da página)
$base_path = (isset($base_path)) ? $base_path : '../';
?>

<header class="main-header">
    <div class="header-container">
        
        <!-- Logo e Título -->
        <div class="header-logo">
            <img src="<?php echo $base_path; ?>assets/images/logo.png" alt="Logo" class="header-logo-img">
            <span class="header-title">Sistema de Reservas</span>
        </div>

        <!-- Menu de Navegação -->
        <nav class="main-nav">
            <?php if ($_SESSION['tipo'] == 'professor'): ?>
                <!-- Menu para Professor -->
                <a href="<?php echo $base_path; ?>professor/index.php" class="nav-link">
                    <span>🏠</span> Início
                </a>
                <a href="<?php echo $base_path; ?>professor/calendario.php" class="nav-link">
                    <span>📆</span> Calendário
                </a>
                <a href="<?php echo $base_path; ?>professor/fazer_reserva.php" class="nav-link">
                    <span>➕</span> Nova Reserva
                </a>
                <a href="<?php echo $base_path; ?>professor/minhas_reservas.php" class="nav-link">
                    <span>📋</span> Minhas Reservas
                </a>
            <?php else: ?>
                <!-- Menu para Admin -->
                <a href="<?php echo $base_path; ?>admin/index.php" class="nav-link">
                    <span>🏠</span> Início
                </a>
                <a href="<?php echo $base_path; ?>admin/gerir_espacos.php" class="nav-link">
                    <span>🏫</span> Espaços
                </a>
                <a href="<?php echo $base_path; ?>admin/gerir_utilizadores.php" class="nav-link">
                    <span>👥</span> Utilizadores
                </a>
            <?php endif; ?>
        </nav>

        <!-- Informações do Utilizador -->
        <div class="user-menu">
            <span class="user-name"><?php echo htmlspecialchars($_SESSION['nome']); ?></span>
            <a href="<?php echo $base_path; ?>auth/logout.php" class="btn-logout" title="Terminar Sessão">
                🚪 Sair
            </a>
        </div>

    </div>
</header>