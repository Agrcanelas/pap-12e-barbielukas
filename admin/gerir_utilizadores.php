<?php
session_start();
$base_path = '../';

// Verificar autenticação
if (!isset($_SESSION['utilizador_id']) || $_SESSION['tipo'] != 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

require_once '../config/database.php';

// Mensagens
$sucesso = isset($_GET['sucesso']) ? $_GET['sucesso'] : '';
$erro = isset($_GET['erro']) ? $_GET['erro'] : '';

// Buscar utilizadores
try {
    $sql = "SELECT * FROM utilizador WHERE tipo = 'professor' ORDER BY nome ASC";
    $utilizadores = $pdo->query($sql)->fetchAll();
} catch (PDOException $e) {
    die("Erro ao buscar utilizadores: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerir Utilizadores - Sistema de Reservas</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <div class="container">
        
        <div class="page-header">
            <h1>👥 Gerir Utilizadores</h1>
            <p>Adicionar, editar ou remover professores do sistema</p>
        </div>

        <!-- Mensagens -->
        <?php if ($sucesso == 'criado'): ?>
        <div class="alert alert-sucesso">
            <strong>✅ Sucesso!</strong> Professor adicionado com sucesso!
        </div>
        <?php elseif ($sucesso == 'editado'): ?>
        <div class="alert alert-sucesso">
            <strong>✅ Sucesso!</strong> Professor atualizado com sucesso!
        </div>
        <?php elseif ($sucesso == 'removido'): ?>
        <div class="alert alert-sucesso">
            <strong>✅ Sucesso!</strong> Professor removido com sucesso!
        </div>
        <?php elseif ($erro == 'email_existe'): ?>
        <div class="alert alert-erro">
            <strong>❌ Erro!</strong> Já existe um utilizador com esse email!
        </div>
        <?php elseif ($erro): ?>
        <div class="alert alert-erro">
            <strong>❌ Erro!</strong> Não foi possível realizar a operação.
        </div>
        <?php endif; ?>

        <!-- Botão Adicionar -->
        <div class="acoes-topo">
            <button onclick="abrirModal()" class="btn btn-primary">
                ➕ Adicionar Novo Professor
            </button>
        </div>

        <!-- Lista de Utilizadores -->
        <?php if (count($utilizadores) > 0): ?>
        <div class="tabela-container">
            <table class="tabela">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($utilizadores as $user): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($user['nome']); ?></strong></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <button 
                                onclick='editarUtilizador(<?php echo json_encode($user); ?>)' 
                                class="btn-acao btn-editar"
                                title="Editar"
                            >
                                ✏️
                            </button>
                            <button 
                                onclick="resetarPassword(<?php echo $user['utilizador_id']; ?>, '<?php echo htmlspecialchars($user['nome']); ?>')" 
                                class="btn-acao btn-reset"
                                title="Resetar Password"
                            >
                                🔑
                            </button>
                            <button 
                                onclick="confirmarRemocao(<?php echo $user['utilizador_id']; ?>, '<?php echo htmlspecialchars($user['nome']); ?>')" 
                                class="btn-acao btn-remover"
                                title="Remover"
                            >
                                🗑️
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="sem-resultados">
            <p>📭 Ainda não há professores cadastrados.</p>
        </div>
        <?php endif; ?>

    </div>

    <!-- Modal Adicionar/Editar Utilizador -->
    <div id="modalUtilizador" class="modal">
        <div class="modal-content">
            <span class="modal-fechar" onclick="fecharModal()">&times;</span>
            
            <h2 id="modalTitulo">Adicionar Professor</h2>

            <form id="formUtilizador" method="POST" action="processar_utilizador.php">
                <input type="hidden" name="acao" id="inputAcao" value="criar">
                <input type="hidden" name="utilizador_id" id="inputUtilizadorId">
                
                <div class="form-group">
                    <label for="nome">Nome Completo *</label>
                    <input type="text" name="nome" id="inputNome" placeholder="Ex: João Silva" required>
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" name="email" id="inputEmail" placeholder="professor@canelas.pt" required>
                </div>

                <div class="form-group" id="grupoPassword">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="inputPassword" placeholder="Deixar vazio para usar padrão (professor123)" minlength="6">
                    <small>Password padrão: professor123</small>
                </div> 

                <button type="submit" class="btn btn-primary btn-block" id="btnSubmit">
                    Adicionar Professor
                </button>
            </form>
        </div>
    </div> 

    <?php include '../includes/footer.php'; ?>

    <script>
    const modal = document.getElementById('modalUtilizador');
    const form = document.getElementById('formUtilizador');
    const modalTitulo = document.getElementById('modalTitulo');
    const btnSubmit = document.getElementById('btnSubmit');
    
    function abrirModal() {
        modalTitulo.textContent = 'Adicionar Professor';
        btnSubmit.textContent = 'Adicionar Professor';
        document.getElementById('inputAcao').value = 'criar';
        document.getElementById('grupoPassword').style.display = 'block';
        document.getElementById('inputPassword').required = false;
        form.reset();
        modal.style.display = 'block';
    }
    
    function editarUtilizador(user) {
        modalTitulo.textContent = 'Editar Professor';
        btnSubmit.textContent = 'Guardar Alterações';
        document.getElementById('inputAcao').value = 'editar';
        document.getElementById('inputUtilizadorId').value = user.utilizador_id;
        document.getElementById('inputNome').value = user.nome;
        document.getElementById('inputEmail').value = user.email;
        document.getElementById('grupoPassword').style.display = 'none';
        modal.style.display = 'block';
    }
    
    function fecharModal() {
        modal.style.display = 'none';
        form.reset();
    }
    
    function resetarPassword(id, nome) {
        if (confirm('🔑 Resetar password de "' + nome + '"?\n\nUma nova password será gerada automaticamente.')) {
            window.location.href = 'resetar_password_novo.php?id=' + id;
        }
    }
    
    function confirmarRemocao(id, nome) {
        if (confirm('⚠️ Tem a certeza que deseja remover o professor "' + nome + '"?\n\nISTO IRÁ TAMBÉM REMOVER TODAS AS RESERVAS DESTE PROFESSOR!')) {
            window.location.href = 'processar_utilizador.php?acao=remover&id=' + id;
        }
    }
    
    window.onclick = function(event) {
        if (event.target == modal) {
            fecharModal();
        }
    }
    </script>

</body>
</html><?php