<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Reservas - Agrupamento de Escolas Canelas</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #C41E3A 0%, #4A90E2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .landing-container {
            background: white;
            max-width: 900px;
            width: 100%;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: slideUp 0.6s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header-section {
            background: linear-gradient(135deg, #C41E3A 0%, #E91E63 100%);
            color: white;
            padding: 50px 40px;
            text-align: center;
        }

        .logo {
            max-width: 200px;
            height: auto;
            margin-bottom: 20px;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.2));
        }

        .header-section h1 {
            font-size: 36px;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .header-section p {
            font-size: 18px;
            opacity: 0.95;
            line-height: 1.6;
        }

        .content-section {
            padding: 50px 40px;
        }

        .welcome-text {
            text-align: center;
            margin-bottom: 40px;
        }

        .welcome-text h2 {
            color: #C41E3A;
            font-size: 28px;
            margin-bottom: 20px;
        }

        .welcome-text p {
            color: #666;
            font-size: 16px;
            line-height: 1.8;
            max-width: 700px;
            margin: 0 auto;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 50px;
        }

        .feature-item {
            text-align: center;
            padding: 20px;
            background: #F8F9FA;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .feature-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .feature-item .icon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .feature-item h3 {
            color: #333;
            font-size: 18px;
            margin-bottom: 10px;
        }

        .feature-item p {
            color: #666;
            font-size: 14px;
        }

        .login-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .login-card {
            background: white;
            border: 3px solid #E0E0E0;
            border-radius: 16px;
            padding: 40px 30px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            display: block;
        }

        .login-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
        }

        .login-card.professor {
            border-color: #4A90E2;
        }

        .login-card.professor:hover {
            border-color: #4A90E2;
            background: linear-gradient(135deg, #F0F8FF 0%, #E3F2FD 100%);
        }

        .login-card.admin {
            border-color: #C41E3A;
        }

        .login-card.admin:hover {
            border-color: #C41E3A;
            background: linear-gradient(135deg, #FFF0F3 0%, #FFE4E9 100%);
        }

        .login-card .card-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }

        .login-card h3 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #333;
        }

        .login-card p {
            color: #666;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .login-card .btn {
            background: #4A90E2;
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .login-card.admin .btn {
            background: #C41E3A;
        }

        .login-card .btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .footer-section {
            background: #F8F9FA;
            padding: 30px;
            text-align: center;
            color: #666;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .header-section h1 {
                font-size: 28px;
            }

            .header-section p {
                font-size: 16px;
            }

            .welcome-text h2 {
                font-size: 24px;
            }

            .content-section {
                padding: 30px 20px;
            }

            .login-buttons {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <div class="landing-container">
        
        <!-- Header -->
        <div class="header-section">
            <img src="assets/images/logo.png" alt="Logo Agrupamento de Escolas Canelas" class="logo">
            <h1>Sistema de Reservas de Espaços</h1>
            <p>Agrupamento de Escolas Canelas</p>
        </div>

        <!-- Content -->
        <div class="content-section">
            
            <!-- Welcome Message -->
            <div class="welcome-text">
                <h2>Bem-vindo ao Sistema de Gestão de Reservas</h2>
                <p>
                    Plataforma digital para facilitar a reserva e gestão de salas, laboratórios e outros espaços da escola. 
                    Sistema intuitivo, rápido e eficiente para professores e administradores.
                </p>
            </div>

            <!-- Features -->
            <div class="features">
                <div class="feature-item">
                    <div class="icon">📅</div>
                    <h3>Calendário Interativo</h3>
                    <p>Visualize disponibilidade em tempo real</p>
                </div>
                <div class="feature-item">
                    <div class="icon">⚡</div>
                    <h3>Reservas Rápidas</h3>
                    <p>Processo simples e intuitivo</p>
                </div>
                <div class="feature-item">
                    <div class="icon">🔒</div>
                    <h3>Seguro</h3>
                    <p>Acesso controlado e protegido</p>
                </div>
                <div class="feature-item">
                    <div class="icon">📊</div>
                    <h3>Gestão Completa</h3>
                    <p>Controlo total de espaços e reservas</p>
                </div>
            </div>

            <!-- Login Buttons -->
            <div class="login-buttons">
                
                <!-- Professor Login -->
                <a href="auth/login.php" class="login-card professor">
                    <div class="card-icon">👨‍🏫</div>
                    <h3>Sou Professor</h3>
                    <p>Aceda ao sistema para fazer e gerir as suas reservas de espaços</p>
                    <span class="btn">Entrar como Professor</span>
                </a>

                <!-- Admin Login -->
                <a href="auth/login.php" class="login-card admin">
                    <div class="card-icon">🔧</div>
                    <h3>Sou Administrador</h3>
                    <p>Aceda ao painel de gestão completa do sistema</p>
                    <span class="btn">Entrar como Admin</span>
                </a>

            </div>

        </div>

        <!-- Footer -->
        <div class="footer-section">
            <p>&copy; 2025 Agrupamento de Escolas Canelas | Sistema de Reservas</p>
            <p style="margin-top: 5px; font-size: 12px;">Desenvolvido como Prova de Aptidão Profissional</p>
        </div>

    </div>

</body>
</html>