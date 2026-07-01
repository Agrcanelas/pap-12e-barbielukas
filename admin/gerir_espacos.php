<?php
/**
 * Gerir Espaços
 * Adicionar, editar e remover espaços
 */

session_start();
$base_path = '../';

// Verificar se está autenticado e é admin
if (!isset($_SESSION['utilizador_id']) || $_SESSION['tipo'] != 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

require_once '../config/database.php';

// Mensagens
$sucesso = isset($_GET['sucesso']) ? $_GET['sucesso'] : '';
$erro = isset($_GET['erro']) ? $_GET['erro'] : '';

// Buscar todos os espaços
try {
    $sql = "SELECT * FROM espaco ORDER BY nome ASC";
    $espacos = $pdo->query($sql)->fetchAll();
} catch (PDOException $e) {
    die("Erro ao buscar espaços: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerir Espaços - Sistema de Reservas</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <div class="container">
        
        <div class="page-header">
            <h1>🏫 Gerir Espaços</h1>
            <p>Adicionar, editar ou remover espaços do sistema</p>
        </div>

        <!-- Mensagens -->
        <?php if ($sucesso == 'criado'): ?>
        <div class="alert alert-sucesso">
            <strong>✅ Sucesso!</strong> Espaço criado com sucesso!
        </div>
        <?php elseif ($sucesso == 'editado'): ?>
        <div class="alert alert-sucesso">
            <strong>✅ Sucesso!</strong> Espaço atualizado com sucesso!
        </div>
        <?php elseif ($sucesso == 'removido'): ?>
        <div class="alert alert-sucesso">
            <strong>✅ Sucesso!</strong> Espaço removido com sucesso!
        </div>
        <?php elseif ($erro): ?>
        <div class="alert alert-erro">
            <strong>❌ Erro!</strong> Não foi possível realizar a operação.
        </div>
        <?php endif; ?>

        <!-- Botão Adicionar -->
        <div class="acoes-topo">
            <button onclick="abrirModal()" class="btn btn-primary">
                ➕ Adicionar Novo Espaço
            </button>
        </div>

        <!-- Lista de Espaços -->
        <?php if (count($espacos) > 0): ?>
        <div class="tabela-container">
            <table class="tabela">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Tipo</th>
                        <th>Capacidade</th>
                        <th>Estado</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($espacos as $espaco): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($espaco['nome']); ?></strong></td>
                        <td><?php echo htmlspecialchars($espaco['tipo_espaco']); ?></td>
                        <td><?php echo $espaco['capacidade']; ?> pessoas</td>
                        <td>
                            <?php if (($espaco['ativo'] ?? 1)): ?>
                                <span class="badge badge-confirmada">Ativo</span>
                            <?php else: ?>
                                <span class="badge badge-cancelada">Inativo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button 
                                onclick='editarEspaco(<?php echo json_encode($espaco); ?>)' 
                                class="btn-acao btn-editar"
                                title="Editar"
                            >
                                ✏️
                            </button>
                            <button 
                                onclick="confirmarRemocao(<?php echo $espaco['espaco_id']; ?>, '<?php echo htmlspecialchars($espaco['nome']); ?>')" 
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
            <p>📭 Ainda não há espaços cadastrados.</p>
        </div>
        <?php endif; ?>

    </div>

    <!-- Modal Adicionar/Editar Espaço -->
    <div id="modalEspaco" class="modal">
        <div class="modal-content">
            <span class="modal-fechar" onclick="fecharModal()">&times;</span>
            
            <h2 id="modalTitulo">Adicionar Espaço</h2>

            <form id="formEspaco" method="POST" action="processar_espaco.php">
                <input type="hidden" name="acao" id="inputAcao" value="criar">
                <input type="hidden" name="espaco_id" id="inputEspacoId">
                
                <!-- Nome -->
                <div class="form-group">
                    <label for="nome">Nome do Espaço *</label>
                    <input type="text" name="nome" id="inputNome" placeholder="Ex: Sala 101" required>
                </div>

                <!-- Tipo -->
                <div class="form-group">
                    <label for="tipo_espaco">Tipo de Espaço *</label>
                    <select name="tipo_espaco" id="inputTipo" required>
                        <option value="">Selecione</option>
                        <option value="Sala Normal">Sala Normal</option>
                        <option value="Laboratório">Laboratório</option>
                        <option value="Auditório">Auditório</option>
                        <option value="Biblioteca">Biblioteca</option>
                        <option value="Ginásio">Ginásio</option>
                        <option value="Sala de Reunião">Sala de Reunião</option>
                        <option value="Salas Informatica">F0:14</option>
                        <option value="Outro">Outro</option>
                    </select>
                </div>

                <!-- Capacidade -->
                <div class="form-group">
                    <label for="capacidade">Capacidade (nº de pessoas) *</label>
                    <input type="number" name="capacidade" id="inputCapacidade" min="1" max="500" required>
                </div>

                <!-- Ativo (só aparece ao editar) -->
                <div class="form-group" id="grupoAtivo" style="display: none;">
                    <label>
                        <input type="checkbox" name="ativo" id="inputAtivo" value="1">
                        Espaço ativo (disponível para reservas)
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-block" id="btnSubmit">
                    Adicionar Espaço
                </button>
            </form>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
    const modal = document.getElementById('modalEspaco');
    const form = document.getElementById('formEspaco');
    const modalTitulo = document.getElementById('modalTitulo');
    const btnSubmit = document.getElementById('btnSubmit');
    
    // Abrir modal para criar
    function abrirModal() {
        modalTitulo.textContent = 'Adicionar Espaço';
        btnSubmit.textContent = 'Adicionar Espaço';
        document.getElementById('inputAcao').value = 'criar';
        document.getElementById('grupoAtivo').style.display = 'none';
        form.reset();
        modal.style.display = 'block';
    }
    
    // Abrir modal para editar
    function editarEspaco(espaco) {
        modalTitulo.textContent = 'Editar Espaço';
        btnSubmit.textContent = 'Guardar Alterações';
        document.getElementById('inputAcao').value = 'editar';
        document.getElementById('inputEspacoId').value = espaco.espaco_id;
        document.getElementById('inputNome').value = espaco.nome;
        document.getElementById('inputTipo').value = espaco.tipo_espaco;
        document.getElementById('inputCapacidade').value = espaco.capacidade;
        document.getElementById('inputAtivo').checked = espaco.ativo == 1;
        document.getElementById('grupoAtivo').style.display = 'block';
        modal.style.display = 'block';
    }
    
    // Fechar modal
    function fecharModal() {
        modal.style.display = 'none';
        form.reset();
    }
    
    // Confirmar remoção
    function confirmarRemocao(id, nome) {
        if (confirm('⚠️ Tem a certeza que deseja remover o espaço "' + nome + '"?\n\nISTO IRÁ TAMBÉM REMOVER TODAS AS RESERVAS ASSOCIADAS!')) {
            window.location.href = 'processar_espaco.php?acao=remover&id=' + id;
        }
    }
    
    // Fechar ao clicar fora
    window.onclick = function(event) {
        if (event.target == modal) {
            fecharModal();
        }
    }
    </script>

</body>
</html>