<?php
/**
 * Configuração da Base de Dados
 * Sistema de Reservas de Espaços - Agrupamento de Escolas Canelas
 */

// Configurações do servidor de base de dados
define('DB_HOST', 'localhost');        // Servidor (no XAMPP é sempre localhost)
define('DB_USER', 'root');             // Utilizador (no XAMPP é sempre root)
define('DB_PASS', '');                 // Password (no XAMPP está vazia por defeito)
define('DB_NAME', 'sistema_reservas'); // Nome da base de dados que criaste

// Criar ligação à base de dados
try {
    // Usar PDO (mais seguro que mysqli)
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    // Ligação bem-sucedida
    // (Não mostrar mensagem para não aparecer em todas as páginas)
    
} catch (PDOException $e) {
    // Se houver erro na ligação, mostrar mensagem
    die("Erro na ligação à base de dados: " . $e->getMessage());
}

/**
 * NOTAS IMPORTANTES:
 * 
 * 1. Este ficheiro deve ser incluído no topo de TODAS as páginas PHP que precisem
 *    de aceder à base de dados, usando:
 *    require_once '../config/database.php';
 * 
 * 2. Depois de incluires este ficheiro, tens acesso à variável $pdo para fazer
 *    consultas à base de dados de forma segura.
 * 
 * 3. PDO é mais seguro que mysqli porque previne SQL Injection automaticamente
 *    quando usas prepared statements.
 * 
 * EXEMPLO DE USO:
 * 
 * // No topo da tua página PHP:
 * require_once '../config/database.php';
 * 
 * // Fazer uma consulta:
 * $sql = "SELECT * FROM espaco WHERE ativo = 1";
 * $stmt = $pdo->query($sql);
 * $espacos = $stmt->fetchAll();
 */
?>