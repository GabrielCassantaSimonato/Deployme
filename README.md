# 🚀 Deployme

> Plataforma web desenvolvida para aproximar estudantes da área de Tecnologia da Informação e recrutadores, oferecendo uma rede social profissional integrada à Inteligência Artificial para análise de currículos e moderação automática de conteúdo.

---

# 📖 Sobre o projeto

O **Deployme** é uma aplicação web desenvolvida como Trabalho de Conclusão de Curso (TCC) em Engenharia de Software.

A plataforma surgiu da necessidade de oferecer um ambiente especializado para estudantes de Tecnologia da Informação que buscam oportunidades de estágio e desenvolvimento profissional, reunindo funcionalidades de rede social, divulgação de vagas e comunicação entre candidatos e empresas.

Além dos recursos tradicionais de uma rede social, o sistema utiliza agentes inteligentes baseados na API do Google Gemini para automatizar tarefas como análise semântica de currículos e moderação de publicações.

---

# 🎯 Objetivos

O Deployme possui como principais objetivos:

- conectar estudantes de TI com empresas recrutadoras;
- facilitar a divulgação de oportunidades de estágio;
- incentivar o networking entre estudantes;
- automatizar processos utilizando Inteligência Artificial;
- promover um ambiente seguro através da moderação automática de conteúdo.

---

# 🏗 Arquitetura

O sistema foi desenvolvido utilizando uma arquitetura **Monolítica**, baseada no padrão **Model-View-Controller (MVC)**.

Essa arquitetura foi escolhida por proporcionar:

- separação entre regras de negócio e interface;
- facilidade de manutenção;
- reutilização de código;
- menor complexidade de implantação;
- melhor adequação ao escopo do projeto.

```
Usuário
    │
    ▼
 Controller
    │
    ▼
   Model
    │
    ▼
 Banco de Dados
    ▲
    │
   View
```

---

# 💻 Tecnologias utilizadas

## Backend

- PHP 7.4+
- MVC
- PDO
- Composer

## Frontend

- HTML5
- CSS3
- JavaScript
- Bootstrap 5
- Bootstrap Icons
- AJAX
- Fetch API

## Banco de Dados

- MySQL

## Inteligência Artificial

- Google Gemini API (modelo **gemini-2.5-flash**)

---

# 📦 Dependências

O projeto utiliza o Composer para gerenciamento das bibliotecas.

Dependências instaladas:

| Biblioteca          | Finalidade                                   |
| ------------------- | -------------------------------------------- |
| smalot/pdfparser    | Extração de informações de currículos em PDF |
| vlucas/phpdotenv    | Gerenciamento de variáveis de ambiente       |
| phpmailer/phpmailer | Envio de e-mails da plataforma               |

---

# ⚙️ Requisitos

Para executar o sistema é necessário possuir:

| Software | Versão          |
| -------- | --------------- |
| PHP      | 7.4 ou superior |
| Apache   | 2.4+            |
| MySQL    | 8.0+            |
| Composer | 2.x             |
| Git      | Última versão   |

---

# 🔧 Ambiente de desenvolvimento

O projeto foi desenvolvido utilizando:

| Serviço | Porta    |
| ------- | -------- |
| Apache  | **8080** |
| MySQL   | **3306** |

Após iniciar ambos os serviços, o sistema poderá ser acessado pelo navegador.

---

# 🌐 Executando o projeto

## Clone o repositório

```bash
git clone https://github.com/SEU-USUARIO/Deployme.git
```

Entre na pasta do projeto:

```bash
cd Deployme
```

Instale as dependências:

```bash
composer install
```

---

# 🗄 Banco de Dados

Crie um banco MySQL.

Exemplo:

```sql
CREATE DATABASE deployme
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

Importe posteriormente o script SQL disponibilizado no projeto.

Configure a conexão com o banco de dados.

Exemplo:

```
Host: localhost
Porta: 3306
Banco: deployme
Usuário: root
Senha:
```

---

# 🔐 Variáveis de ambiente

O sistema utiliza o pacote **PHP Dotenv**.

Crie um arquivo chamado:

```
.env
```

Exemplo:

```env
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=deployme
DB_USERNAME=root
DB_PASSWORD=

GEMINI_API_KEY=SUA_CHAVE

EMAIL_HOST=smtp.gmail.com
EMAIL_PORT=587
EMAIL_USERNAME=seuemail@gmail.com
EMAIL_PASSWORD=suasenha
```

---

# ▶️ Inicialização

Com Apache e MySQL iniciados:

```
Apache  -> Porta 8080
MySQL   -> Porta 3306
```

Acesse:

```
http://localhost:8080/Deployme/public
```

---

# 📁 Estrutura do projeto

```
Deployme
│
├── app
│   ├── controller
│   ├── middleware
│   ├── model
│   └── view
│
├── config
│
├── public
│   ├── css
│   ├── js
│   ├── img
│   ├── uploads
│   │   ├── currículos
│   │   ├── fotos
│   │   └── publicacoes
│   └── index.php
│
├── vendor
│
├── composer.json
│
└── README.md
```

---

# 🗃 Banco de Dados

Principais tabelas:

- usuarios
- estudantes
- recrutadores
- universidades
- cursos
- semestres
- senioridades
- publicacoes
- vagas
- candidaturas
- comentarios
- curtidas
- seguidores
- chats
- mensagens
- notificacoes

---

# 👨‍🎓 Funcionalidades para estudantes

- Cadastro
- Login
- Recuperação de senha
- Reativação de conta
- Edição de perfil
- Upload de foto
- Upload de currículo
- Integração com GitHub
- Publicação de conteúdos
- Curtidas
- Comentários
- Compartilhamentos
- Chat privado
- Visualização de vagas
- Candidatura em vagas
- Exclusão de publicações
- Desativação de conta

---

# 🏢 Funcionalidades para recrutadores

- Cadastro
- Login
- Recuperação de senha
- Publicação de vagas
- Gerenciamento das vagas publicadas
- Visualização de candidatos
- Perfil empresarial
- Chat com estudantes

---

# 👨‍💼 Funcionalidades administrativas

O administrador possui um painel exclusivo contendo:

- Dashboard administrativo
- Estatísticas gerais
- Gerenciamento de usuários
- Bloqueio de usuários
- Reativação de usuários
- Visualização completa dos perfis
- Visualização de todas as publicações
- Exclusão de publicações
- Moderação da plataforma

---

# 🤖 Inteligência Artificial

O Deployme integra agentes inteligentes utilizando a API Google Gemini.

## Análise de currículos

A IA realiza análise semântica considerando:

- linguagens de programação;
- frameworks;
- tecnologias;
- competências técnicas;
- experiências profissionais;
- compatibilidade com vagas.

## Moderação automática

As publicações podem ser avaliadas automaticamente para identificar:

- spam;
- conteúdos ofensivos;
- linguagem inadequada;
- violações das políticas da plataforma.

---

# 💼 Sistema de vagas

Os recrutadores podem:

- publicar vagas;
- editar vagas;
- excluir vagas;
- acompanhar candidatos.

Os estudantes podem:

- visualizar vagas;
- candidatar-se;
- acompanhar suas candidaturas.

---

# 💬 Sistema de chat

A plataforma possui um sistema de mensagens privadas entre usuários.

Recursos:

- histórico de conversas;
- comunicação em tempo real;
- interface responsiva.

---

# 🔒 Segurança

O sistema implementa diversas práticas de segurança:

- Hash de senhas utilizando Password Hash API;
- Sessões PHP;
- Prepared Statements (PDO);
- Controle de autenticação;
- Controle de autorização por tipo de usuário;
- Proteção contra SQL Injection;
- Gerenciamento de variáveis sensíveis através do PHP Dotenv.

---

# 🔌 APIs utilizadas

O sistema integra-se aos seguintes serviços:

- Google Gemini API
- GitHub API

---

# 📊 Características técnicas

- Arquitetura Monolítica
- Padrão MVC
- Programação Orientada a Objetos
- Autoload PSR-4
- Banco de dados MySQL
- Bootstrap 5
- Integração com Inteligência Artificial
- Sistema de autenticação baseado em Sessões PHP

---

# 🚧 Trabalhos futuros

Como continuidade do projeto, pretende-se implementar:

- dashboard administrativo com gráficos estatísticos;
- gerenciamento administrativo de vagas;
- sistema de denúncias de publicações;
- auditoria de ações administrativas;
- métricas de desempenho dos agentes de IA;
- notificações administrativas;
- controle refinado de permissões.

---

# 👨‍💻 Autor

**Gabriel Simonato**

Trabalho de Conclusão de Curso

Engenharia de Software

Universidade de São Paulo – ESALQ

---

# 📄 Licença

Este projeto foi desenvolvido exclusivamente para fins acadêmicos como Trabalho de Conclusão de Curso em Engenharia de Software.
