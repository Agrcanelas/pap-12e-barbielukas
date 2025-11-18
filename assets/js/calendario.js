/**
 * JavaScript do Calendário
 * Torna o calendário interativo com verificação de disponibilidade
 */

// Função para filtrar por espaço (ocupação visual)
function filtrarPorEspaco() {
    console.log('Filtrar por espaço chamado'); // DEBUG
    
    const espacoId = document.getElementById('filtroEspaco').value;
    const dias = document.querySelectorAll('.dia:not(.dia-passado)');
    const legenda = document.getElementById('legendaOcupacao');
    
    console.log('Espaço selecionado:', espacoId); // DEBUG
    console.log('Total de dias:', dias.length); // DEBUG
    
    if (espacoId === '') {
        // Mostrar todos sem cores
        dias.forEach(function(dia) {
            dia.classList.remove('dia-livre', 'dia-parcial', 'dia-ocupado');
        });
        legenda.style.display = 'none';
    } else {
        // Mostrar cores de ocupação do espaço selecionado
        legenda.style.display = 'flex';
        
        dias.forEach(function(dia) {
            const ocupacao = dia.getAttribute('data-ocupacao');
            console.log('Ocupação do dia:', ocupacao); // DEBUG
            
            if (!ocupacao) {
                console.log('Sem dados de ocupação'); // DEBUG
                return;
            }
            
            // Procurar ocupação deste espaço
            const espacos = ocupacao.split('|');
            let nivelOcupacao = 'livre';
            
            espacos.forEach(function(item) {
                const partes = item.split(':');
                if (partes[0] == espacoId) {
                    nivelOcupacao = partes[1];
                    console.log('Nível encontrado:', nivelOcupacao); // DEBUG
                }
            });
            
            // Remover classes anteriores
            dia.classList.remove('dia-livre', 'dia-parcial', 'dia-ocupado');
            
            // Adicionar classe de ocupação
            dia.classList.add('dia-' + nivelOcupacao);
        });
    }
}

// Esperar o DOM carregar
document.addEventListener('DOMContentLoaded', function() {
    
    // Elementos
    const modal = document.getElementById('modalReserva');
    const fecharModal = document.querySelector('.modal-fechar');
    const dias = document.querySelectorAll('.dia:not(.dia-passado)');
    const inputData = document.getElementById('inputData');
    const dataEscolhida = document.getElementById('dataEscolhida');
    const selectEspaco = document.getElementById('espaco');
    const selectHoraInicio = document.getElementById('hora_inicio');
    const selectHoraFim = document.getElementById('hora_fim');
    const disponibilidadeDiv = document.getElementById('disponibilidade');
    const formReserva = document.getElementById('formReserva');
    
    // Abrir modal ao clicar num dia
    dias.forEach(function(dia) {
        dia.addEventListener('click', function() {
            const data = this.getAttribute('data-data');
            
            // Formatar data para mostrar
            const partes = data.split('-');
            const dataFormatada = partes[2] + '/' + partes[1] + '/' + partes[0];
            
            // Preencher modal
            inputData.value = data;
            dataEscolhida.textContent = 'Data selecionada: ' + dataFormatada;
            
            // Abrir modal
            modal.style.display = 'block';
        });
    });
    
    // Fechar modal ao clicar no X
    fecharModal.addEventListener('click', function() {
        modal.style.display = 'none';
        limparFormulario();
    });
    
    // Fechar modal ao clicar fora
    window.addEventListener('click', function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
            limparFormulario();
        }
    });
    
    // Verificar disponibilidade quando mudar espaço ou horário
    selectEspaco.addEventListener('change', verificarDisponibilidade);
    selectHoraInicio.addEventListener('change', verificarDisponibilidade);
    selectHoraFim.addEventListener('change', verificarDisponibilidade);
    
    // Prevenir submissão se não estiver disponível
    formReserva.addEventListener('submit', function(e) {
        const disponibilidadeTexto = disponibilidadeDiv.textContent;
        
        if (disponibilidadeTexto.includes('já reservado') || disponibilidadeTexto.includes('já existe')) {
            e.preventDefault();
            mostrarAlertaErro('❌ Não é possível reservar! O espaço está ocupado neste horário.');
            return false;
        }
        
        if (!disponibilidadeTexto.includes('✅')) {
            e.preventDefault();
            mostrarAlertaErro('⚠️ Por favor, verifique a disponibilidade antes de confirmar!');
            return false;
        }
    });
    
    // Função para verificar disponibilidade (AJAX)
    function verificarDisponibilidade() {
        const data = inputData.value;
        const espacoId = selectEspaco.value;
        const horaInicio = selectHoraInicio.value;
        const horaFim = selectHoraFim.value;
        
        // Validar campos
        if (!data || !espacoId || !horaInicio || !horaFim) {
            disponibilidadeDiv.innerHTML = '<p>🔍 Preencha todos os campos para verificar disponibilidade</p>';
            disponibilidadeDiv.className = 'disponibilidade-info-melhorada';
            return;
        }
        
        // Validar se hora fim é maior que hora início
        if (horaFim <= horaInicio) {
            disponibilidadeDiv.innerHTML = '<p class="erro">⚠️ A hora de fim deve ser maior que a hora de início!</p>';
            disponibilidadeDiv.className = 'disponibilidade-info-melhorada erro';
            return;
        }
        
        // Fazer pedido AJAX para verificar disponibilidade
        disponibilidadeDiv.innerHTML = '<p>🔄 A verificar disponibilidade...</p>';
        disponibilidadeDiv.className = 'disponibilidade-info-melhorada';
        
        // Criar pedido
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'verificar_disponibilidade.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        xhr.onload = function() {
            if (xhr.status === 200) {
                const resposta = JSON.parse(xhr.responseText);
                
                if (resposta.disponivel) {
                    // DISPONÍVEL - Mostrar sucesso
                    let html = '<p class="sucesso">✅ Espaço disponível neste horário!</p>';
                    
                    // Mostrar horários livres do dia (informativo)
                    if (resposta.horarios_livres && resposta.horarios_livres.length > 0) {
                        html += '<div class="horarios-livres-info">';
                        html += '<p><strong>📅 Horários livres em ' + resposta.espaco_nome + ' neste dia:</strong></p>';
                        html += '<ul>';
                        resposta.horarios_livres.forEach(function(horario) {
                            html += '<li>' + horario + '</li>';
                        });
                        html += '</ul>';
                        html += '</div>';
                    }
                    
                    disponibilidadeDiv.innerHTML = html;
                    disponibilidadeDiv.className = 'disponibilidade-info-melhorada sucesso';
                    
                } else {
                    // NÃO DISPONÍVEL - Mostrar erro e alternativas
                    let html = '<p class="erro">❌ Erro! Já existe uma reserva neste horário!</p>';
                    
                    // Mostrar horários livres como alternativa
                    if (resposta.horarios_livres && resposta.horarios_livres.length > 0) {
                        html += '<div class="horarios-livres">';
                        html += '<p><strong>⏰ Horários ainda disponíveis em ' + resposta.espaco_nome + ':</strong></p>';
                        html += '<ul>';
                        resposta.horarios_livres.forEach(function(horario) {
                            html += '<li>✅ ' + horario + '</li>';
                        });
                        html += '</ul>';
                        html += '<p class="dica">💡 Escolha um destes horários para fazer a reserva</p>';
                        html += '</div>';
                    } else {
                        html += '<p class="aviso">⚠️ Este espaço está totalmente ocupado neste dia.</p>';
                    }
                    
                    disponibilidadeDiv.innerHTML = html;
                    disponibilidadeDiv.className = 'disponibilidade-info-melhorada erro';
                    
                    // Mostrar alerta visual
                    mostrarAlertaErro('❌ Espaço ocupado! Veja os horários disponíveis abaixo.');
                }
            }
        };
        
        // Enviar dados
        const dados = 'data=' + data + 
                     '&espaco_id=' + espacoId + 
                     '&hora_inicio=' + horaInicio + 
                     '&hora_fim=' + horaFim;
        xhr.send(dados);
    }
    
    // Função para mostrar alerta de erro (pop-up)
    function mostrarAlertaErro(mensagem) {
        // Criar elemento de alerta se não existir
        let alerta = document.getElementById('alertaFlutuante');
        
        if (!alerta) {
            alerta = document.createElement('div');
            alerta.id = 'alertaFlutuante';
            alerta.className = 'alerta-flutuante';
            document.body.appendChild(alerta);
        }
        
        // Definir mensagem e mostrar
        alerta.textContent = mensagem;
        alerta.classList.add('mostrar');
        
        // Esconder após 4 segundos
        setTimeout(function() {
            alerta.classList.remove('mostrar');
        }, 4000);
    }
    
    // Limpar formulário
    function limparFormulario() {
        formReserva.reset();
        disponibilidadeDiv.innerHTML = '<p>🔍 Selecione o espaço e horário para verificar disponibilidade</p>';
        disponibilidadeDiv.className = 'disponibilidade-info-melhorada';
    }
    
});