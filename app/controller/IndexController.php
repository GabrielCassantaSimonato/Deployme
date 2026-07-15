<?php

namespace app\controller;

use MF\controller\Action;
use MF\model\Container;
use app\service\EmailService;

class IndexController extends Action
{
    /**
     * Renderiza a página inicial (landing page) da plataforma.
     */
    public function index()
    {
        $this->render('index');
    }

    /**
     * Renderiza o formulário de cadastro de estudantes.
     * 
     * Carrega as tabelas auxiliares necessárias para alimentar os campos de seleção
     * (cursos, universidades, gêneros, semestres) antes de exibir a view de cadastro.
     */
    public function signUpStudent()
    {
        $this->carregarDadosFormulario();
        $this->render('signUpStudent');
    }

    /**
     * Renderiza o formulário de cadastro de recrutadores.
     * 
     * Carrega as informações necessárias para preencher os elementos dinâmicos da view,
     * incluindo as opções de senioridade e gêneros cadastrados.
     */
    public function signUpRecruiter()
    {
        $this->carregarDadosFormulario();
        $this->render('signUpRecruiter');
    }

    /**
     * Processa a submissão e o registro do cadastro de um estudante.
     * 
     * Valida os campos fornecidos, realiza o upload da foto de perfil e do arquivo de currículo,
     * criptografa a senha do usuário, armazena os registros nas tabelas de usuário e estudante,
     * e dispara um e-mail de boas-vindas após a persistência bem-sucedida.
     */
    public function studentRegister()
    {
        $usuario = Container::getModel('Usuario');
        $estudante = Container::getModel('Estudante');

        $usuario->__set('nome', $_POST['nome'] ?? null);
        $usuario->__set('email', $_POST['email'] ?? null);
        $usuario->__set('senha', $_POST['senha'] ?? null);
        $usuario->__set('tipo', 'estudante');
        $usuario->__set('genero_id', $_POST['genero_id'] ?? null);

        $erros = $usuario->validarCadastro();
        $fotoNome = null;

        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == 0) {
            $ext = pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION);
            $fotoNome = uniqid() . '.' . $ext;
            $destino = $_SERVER['DOCUMENT_ROOT'] . '/uploads/fotos/' . $fotoNome;

            move_uploaded_file(
                $_FILES['foto_perfil']['tmp_name'],
                $destino
            );
            $usuario->__set('foto', $fotoNome ?? null);
        }

        $curriculoNome = null;

        if (isset($_FILES['curriculo']) && $_FILES['curriculo']['error'] == 0) {
            $ext = pathinfo($_FILES['curriculo']['name'], PATHINFO_EXTENSION);

            $curriculoNome = uniqid() . '.pdf';
            $destino = $_SERVER['DOCUMENT_ROOT'] . '/uploads/currículos/' . $curriculoNome;

            move_uploaded_file(
                $_FILES['curriculo']['tmp_name'],
                $destino
            );
        }

        if (!empty($erros)) {
            $this->carregarDadosFormulario();

            $this->view->erros = $erros;
            $this->view->dados = $_POST;

            $this->render('signUpStudent');
            return;
        }

        $usuario->__set('senha', password_hash($_POST['senha'], PASSWORD_DEFAULT));

        $usuario_id = $usuario->salvarUsuario();

        $estudante->__set('usuario_id', $usuario_id);
        $estudante->__set('universidade_id', $_POST['universidade_id'] ?? null);
        $estudante->__set('curso_id', $_POST['curso_id'] ?? null);
        $estudante->__set('semestre_id', $_POST['semestre_id'] ?? null);
        $estudante->__set('github', $_POST['github'] ?? null);
        $estudante->__set('cep', $_POST['cep'] ?? null);
        $estudante->__set('rua', $_POST['rua'] ?? null);
        $estudante->__set('bairro', $_POST['bairro'] ?? null);
        $estudante->__set('cidade', $_POST['cidade'] ?? null);
        $estudante->__set('complemento', $_POST['complemento'] ?? null);
        $estudante->__set('uf', $_POST['uf'] ?? null);
        $estudante->__set('curriculo', $curriculoNome ?? null);

        $estudante->salvarEstudante();
        EmailService::enviarBoasVindas($_POST['email'], $_POST['nome']);

        header('Location: /successRegister');
        exit;
    }

    /**
     * Processa a submissão e o registro do cadastro de um recrutador.
     * 
     * Efetua a validação das credenciais básicas, gerencia o upload opcional de foto de perfil,
     * persiste o registro do usuário com criptografia hash na senha e adiciona as
     * informações exclusivas de sua atuação corporativa na tabela de recrutadores.
     */
    public function recruiterRegister()
    {
        $usuario = Container::getModel('Usuario');
        $recrutador = Container::getModel('Recrutador');

        $usuario->__set('nome', $_POST['nome'] ?? null);
        $usuario->__set('email', $_POST['email'] ?? null);
        $usuario->__set('senha', $_POST['senha'] ?? null);
        $usuario->__set('tipo', 'recrutador');
        $usuario->__set('genero_id', $_POST['genero_id'] ?? null);

        $erros = $usuario->validarCadastro();
        $fotoNome = null;

        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == 0) {
            $ext = pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION);
            $fotoNome = uniqid() . '.' . $ext;
            $destino = $_SERVER['DOCUMENT_ROOT'] . '/uploads/fotos/' . $fotoNome;

            move_uploaded_file(
                $_FILES['foto_perfil']['tmp_name'],
                $destino
            );
            $usuario->__set('foto', $fotoNome ?? null);
        }

        if (!empty($erros)) {
            $this->view->erros = $erros;
            $this->view->dados = $_POST;
            $this->carregarDadosFormulario();

            $this->render('signUpRecruiter');
            return;
        }

        $usuario->__set('senha', password_hash($_POST['senha'], PASSWORD_DEFAULT));
        $usuario_id = $usuario->salvarUsuario();

        $recrutador->__set('usuario_id', $usuario_id);
        $recrutador->__set('empresa', $_POST['empresa'] ?? null);
        $recrutador->__set('senioridade_id', $_POST['senioridade_id'] ?? null);

        $recrutador->salvarRecrutador();
        EmailService::enviarBoasVindas($_POST['email'], $_POST['nome']);

        header('Location: /successRegister');
        exit;
    }

    /**
     * Renderiza a página de confirmação de cadastro realizado com sucesso.
     */
    public function successRegister()
    {
        $this->render('successRegister');
    }

    /**
     * Renderiza a página informativa de políticas de privacidade e LGPD da plataforma.
     */
    public function lgpd()
    {
        $this->render('lgpd');
    }

    /**
     * Renderiza a tela de autenticação para usuários gerais.
     */
    public function login()
    {
        $this->render('login');
    }

    /**
     * Renderiza a tela de login exclusiva para administradores.
     */
    public function loginAdmin()
    {
        $this->render('loginAdmin');
    }

    /**
     * Carrega tabelas de domínio para popular listas de seleção dos formulários de cadastro.
     * 
     * Executa consultas aos modelos de Cursos, Universidades, Gêneros, Semestres
     * e Senioridade e injeta os resultados nos parâmetros de visualização do controller.
     */
    private function carregarDadosFormulario()
    {
        $curso = Container::getModel('Curso');
        $universidade = Container::getModel('Universidade');
        $genero = Container::getModel('Genero');
        $semestre = Container::getModel('Semestre');
        $senioridade = Container::getModel('Senioridade');

        $this->view->cursos = $curso->getCursos();
        $this->view->universidades = $universidade->getUniversidades();
        $this->view->generos = $genero->getGeneros();
        $this->view->semestres = $semestre->getSemestres();
        $this->view->senioridade = $senioridade->getSenioridades();
    }

    /**
     * Renderiza a página de solicitação de recuperação de senha.
     */
    public function forgotPassword()
    {
        $this->render('forgotPassword');
    }

    /**
     * Executa a validação e alteração de senha de um usuário existente.
     * 
     * Confirma a correspondência entre as entradas de senha digitadas, valida a restrição de tamanho,
     * verifica a existência do e-mail informado e atualiza o hash da senha no banco de dados.
     */
    public function resetPassword()
    {
        $usuario = Container::getModel('Usuario');

        $email = trim($_POST['email']);
        $senha = $_POST['senha'];
        $confirmarSenha = $_POST['confirmar_senha'];

        if (strlen($senha) < 8) {
            header(
                'Location: /forgotPassword?erro=' .
                urlencode('A senha deve ter no mínimo 8 caracteres.')
            );
            exit;
        }

        if ($senha !== $confirmarSenha) {
            header(
                'Location: /forgotPassword?erro=' .
                urlencode('As senhas não coincidem.')
            );
            exit;
        }

        $usuario->__set('email', $email);
        $usuarioEncontrado = $usuario->buscarPorEmail();

        if (!$usuarioEncontrado) {
            header(
                'Location: /forgotPassword?erro=' .
                urlencode('E-mail não encontrado.')
            );
            exit;
        }

        $usuario->__set('email', $email);
        $usuario->__set('senha', password_hash($senha, PASSWORD_DEFAULT));

        $usuario->atualizarSenha();

        header('Location: /login?password=updated');
        exit;
    }
}