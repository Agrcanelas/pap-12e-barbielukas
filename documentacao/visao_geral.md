PROJETO: Sistema Web de Reservas de Espaços
ESCOLA: Agrupamento de Escolas Canelas
TIPO: Prova de Aptidão Profissional (PAP)

=====================================================
DESCRIÇÃO GERAL
=====================================================

Sistema web funcional para gestão de reservas de salas e laboratórios escolares.
Resolve o problema da gestão manual que causava conflitos de horário.

PROBLEMA:
- Gestão manual de reservas (folhas Excel partilhadas)
- Conflitos de horário frequentes
- Dificuldade em consultar disponibilidade
- Falta de rastreabilidade

SOLUÇÃO:
- Plataforma digital centralizada
- Calendário interativo com validação em tempo real
- Autenticação segura (professor e admin)
- Sistema completo de auditoria/logs

=====================================================
TECNOLOGIAS UTILIZADAS
=====================================================

BACKEND: PHP (processamento no servidor)
FRONTEND: HTML5, CSS3, JavaScript (interface do utilizador)
BASE DE DADOS: MySQL (armazenamento)
SERVIDOR LOCAL: XAMPP (Apache + PHP + MySQL)
EDITOR: Visual Studio Code
NAVEGADORES: Chrome, Firefox

=====================================================
FUNCIONALIDADES IMPLEMENTADAS
=====================================================

1. AUTENTICAÇÃO
   - Login seguro com email e password
   - Dois tipos de utilizadores: Professor e Admin
   - Encriptação de passwords com bcrypt
   - Gestão de sessões PHP

2. CALENDÁRIO INTERATIVO (FUNCIONALIDADE PRINCIPAL)
   - Mostra dias do mês
   - Validação de disponibilidade em tempo real
   - Horários adaptados aos 10 tempos da escola (50 minutos cada)
   - Filtro visual de ocupação: 🟢Verde (livre) | 🟠Laranja (parcial) | 🔴Vermelho (ocupado)
   - Sugestão de horários livres quando há conflito

3. MÓDULO DE RESERVAS (PROFESSOR)
   - Criar reserva: selecionar espaço, data, horário, turma
   - Ver minhas reservas: com filtros (próximas/passadas/todas)
   - Cancelar reserva: apenas futuras
   - Validação de conflitos: impede duplas reservas

4. MÓDULO DE GESTÃO (ADMIN)
   - Gerir Espaços: adicionar, editar, remover
   - Gerir Utilizadores: adicionar, editar, remover professores
   - Ver Todas as Reservas: com filtros por período, espaço, estado
   - Resetar Passwords: gera automaticamente password temporária
   - Histórico de Logs: registo completo de todas as ações

5. SISTEMA DE LOGS/AUDITORIA
   - Regista automaticamente TODAS as ações
   - Guarda: quem fez, o quê, quando, detalhes técnicos, IP
   - Tipos de ação: reserva_criada, reserva_cancelada, espaco_criado, espaco_editado, 
     espaco_removido, utilizador_criado, utilizador_editado, utilizador_removido, password_resetada
   - Página de visualização com filtros (tipo, utilizador, período)

6. PÁGINA INICIAL (LANDING PAGE)
   - Boas-vindas
   - Explicação do sistema
   - 2 botões: "Sou Professor" e "Sou Administrador"
   - Design moderno com cores da escola

=====================================================
ESTRUTURA DA BASE DE DADOS
=====================================================

TABELA: utilizador
- utilizador_id (PK, auto-increment)
- nome (VARCHAR 100)
- email (VARCHAR 100, UNIQUE)
- password (VARCHAR 255, encriptado com bcrypt)
- tipo (ENUM: 'professor' ou 'admin')
- criado_em (TIMESTAMP)

TABELA: espaco
- espaco_id (PK, auto-increment)
- nome (VARCHAR 100, UNIQUE)
- capacidade (INT)
- tipo_espaco (VARCHAR 50)
- criado_em (TIMESTAMP)

TABELA: reserva
- reserva_id (PK, auto-increment)
- utilizador_id (FK → utilizador)
- espaco_id (FK → espaco)
- turma (VARCHAR 50)
- nome_professor (VARCHAR 100)
- data (DATE)
- hora_inicio (TIME)
- hora_fim (TIME)
- estado (ENUM: 'confirmada' ou 'cancelada')
- criado_em (TIMESTAMP)

TABELA: log_acoes
- log_id (PK, auto-increment)
- utilizador_id (FK → utilizador)
- tipo_acao (VARCHAR 50)
- descricao (TEXT)
- detalhes (TEXT, JSON)
- data_hora (TIMESTAMP, auto-preenchida)
- ip_address (VARCHAR 45)

=====================================================
ESTRUTURA DE PASTAS DO PROJETO
=====================================================

sistema-reservas/pap-12e-barbielukas/
├── index.php (página inicial/landing page)
├── admin/
│   ├── index.php (dashboard admin)
│   ├── gerir_espacos.php
│   ├── processar_espaco.php
│   ├── gerir_utilizadores.php
│   ├── processar_utilizador.php
│   ├── resetar_password_novo.php
│   ├── todas_reservas.php
│   └── historico_logs.php
├── professor/
│   ├── index.php (dashboard professor)
│   ├── calendario.php (FUNCIONALIDADE PRINCIPAL)
│   ├── processar_reserva.php
│   ├── minhas_reservas.php
│   └── cancelar_reserva.php
├── auth/
│   ├── login.php
│   ├── verificar_login.php
│   └── logout.php
├── config/
│   ├── database.php (conexão MySQL)
│   └── log.php (função registarLog())
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── script.js
│   └── images/
│       └── logo.png
├── includes/
│   ├── header.php (menu navegação)
│   └── footer.php
└── xampp/
    └── (servidor local Apache + MySQL + PHP)

=====================================================
SEGURANÇA IMPLEMENTADA
=====================================================

✅ Encriptação de Passwords: usando password_hash() e bcrypt
✅ Prevenção SQL Injection: usando Prepared Statements com PDO
✅ Controlo de Acesso: verificação de sessões, separação professor/admin
✅ Validações: no cliente (JavaScript) e no servidor (PHP)
✅ Autenticação: sistema de login com sessões PHP
✅ Auditoria: registo completo de todas as ações (IP, timestamp, detalhes)

=====================================================
O QUE PODE SER MEXIDO
=====================================================

✅ CAN BE CHANGED:
- CSS/Design (cores, fontes, layout)
- Textos e mensagens
- Mensagens de erro/sucesso
- Número de campos num formulário
- Nomes de ficheiros (PHP files)
- Adicionar novas funcionalidades simples
- Mudar ordem de elementos na interface
- Adicionar novos espaços/utilizadores

❌ CANNOT BE CHANGED (riscos):
- Lógica de autenticação
- Lógica de validação de conflitos de horário
- Estrutura da base de dados
- Nomes das tabelas (sem atualizar foreign keys)
- Nomes das colunas principais
- Sistema de logs (crítico para auditoria)
- Relacionamentos da BD (foreign keys)
- Encriptação de passwords
- Prevenção de SQL Injection (prepared statements)

=====================================================
FUNCIONALIDADES JÁ TESTADAS E FUNCIONAIS
=====================================================

✅ Login (professor e admin)
✅ Logout
✅ Calendário com validação em tempo real
✅ Criar reserva
✅ Cancelar reserva
✅ Ver minhas reservas (com filtros)
✅ Gerir espaços (CRUD completo)
✅ Gerir utilizadores (CRUD completo - sem alterar password ao editar)
✅ Resetar passwords (gera automaticamente)
✅ Ver todas as reservas (admin)
✅ Histórico de logs (com filtros rápidos: Todos, Hoje, Esta Semana, Este Mês, Últimos 30 dias)
✅ Página inicial/landing page
✅ Sistema responsivo (desktop)
✅ Design moderno com cores da escola

=====================================================
DADOS DE TESTE
=====================================================

ADMIN:
- Email: admin@canelas.pt
- Password: admin123

PROFESSORES:
- professor@canelas.pt (password: professor123)
- diana.carneiro@canelas.pt (password: professor123)
- rosa.espirito.santo@canelas.pt (password: professor123)
- patricia.carvalhais@canelas.pt (password: professor123)

ESPAÇOS:
- Sala 101, 102
- Laboratório de Informática
- Laboratório de Química
- Auditório
- Biblioteca

=====================================================
PRÓXIMAS ETAPAS
=====================================================

1. Completar Relatório da PAP (40-60 páginas)
2. Preparar apresentação oral
3. Testar tudo novamente (testes finais)
4. Criar screenshots para o relatório
5. Gerar documentação técnica final
6. Defender o projeto

=====================================================
NOTAS IMPORTANTES
=====================================================

- Projeto 100% funcional
- Sistema de logs implementado e a funcionar
- Página inicial criada e testada
- Autenticação segura
- Validação de horários implementada
- Sem dependências externas (apenas XAMPP)
- Código PHP puro (sem frameworks)
- Pronto para apresentação e defesa

=====================================================
CONTATOS/INFORMAÇÕES
=====================================================

Projeto: Sistema Web de Reservas de Espaços
Escola: Agrupamento de Escolas Canelas
Aluno: [Nome]
Turma: [Turma]
Ano: 2024/2025
Status: Completo e Funcional ✅