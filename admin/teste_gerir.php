<?php
session_start();

echo "<h1>Teste Gerir Utilizadores</h1>";
echo "<p>Sessão iniciada!</p>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

require_once '../config/database.php';

$sql = "SELECT * FROM utilizador WHERE tipo = 'professor'";
$utilizadores = $pdo->query($sql)->fetchAll();

echo "<h2>Utilizadores encontrados: " . count($utilizadores) . "</h2>";
foreach ($utilizadores as $user) {
    echo "- " . htmlspecialchars($user['nome']) . " (" . htmlspecialchars($user['email']) . ")<br>";
}
?>