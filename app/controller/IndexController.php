<?php
namespace app\controller;

use MF\controller\Action;
use MF\model\Container;

class IndexController extends Action
{
    public function index()
    {
        $this->render('index');
    }
    public function signUpStudent()
    {
        $this->carregarDadosFormulario();
        $this->render('signUpStudent');
    }

    // =========================
    // FORM RECRUTADOR (GET)
    // =========================
    public function signUpRecruiter()
    {
        $this->carregarDadosFormulario();

        $this->render('signUpRecruiter');
    }

    // =========================
    // CADASTRO ESTUDANTE (POST)
    // =========================
    public function studentRegister()
    {

        $usuario = Container::getModel('Usuario');
        $estudante = Container::getModel('Estudante');

        // =========================
        // DADOS USUÁRIO
        // =========================
        $usuario->__set('nome', $_POST['nome'] ?? null);
        $usuario->__set('email', $_POST['email'] ?? null);
        $usuario->__set('senha', $_POST['senha'] ?? null);
        $usuario->__set('tipo', 'estudante');
        $usuario->__set('genero_id', $_POST['genero_id'] ?? null);

        // =========================
        // VALIDAÇÃO
        // =========================
        $erros = $usuario->validarCadastro();

        if (!empty($erros)) {

            // Recarrega selects
            $this->carregarDadosFormulario();

            // Mantém dados e erros
            $this->view->erros = $erros;
            $this->view->dados = $_POST;

            $this->render('signUpStudent');
            return; // 🚨 ESSENCIAL
        }

        // =========================
        // HASH SENHA
        // =========================
        $usuario->__set('senha', password_hash($_POST['senha'], PASSWORD_DEFAULT));

        // =========================
        // SALVA USUÁRIO
        // =========================
        $usuario_id = $usuario->salvarUsuario();

        // =========================
        // DADOS ESTUDANTE
        // =========================
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

        // =========================
        // SALVA ESTUDANTE
        // =========================
        $estudante->salvarEstudante();

        // =========================
        // REDIRECT FINAL
        // =========================
        header('Location: /successRegister');
        exit;
    }

    public function recruiterRegister()
    {
        $usuario = Container::getModel('Usuario');
        $recrutador = Container::getModel('Recrutador');
        $genero = Container::getModel('Genero');

        // =========================
        // USUÁRIO
        // =========================
        $usuario->__set('nome', $_POST['nome']);
        $usuario->__set('email', $_POST['email']);
        $usuario->__set('senha', $_POST['senha']);
        $usuario->__set('tipo', 'recrutador');
        $usuario->__set('genero_id', $_POST['genero_id'] ?? null);

        $erros = $usuario->validarCadastro();

        if (!empty($erros)) {
            $this->view->erros = $erros;
            $this->view->dados = $_POST;
            $this->carregarDadosFormulario();

            $this->render('signUpRecruiter');
            return;
        }

        // senha segura
        $usuario->__set('senha', password_hash($_POST['senha'], PASSWORD_DEFAULT));

        $usuario_id = $usuario->salvarUsuario();

        // =========================
        // RECRUTADOR
        // =========================
        $recrutador->__set('usuario_id', $usuario_id);
        $recrutador->__set('empresa', $_POST['empresa']);
        $recrutador->__set('senioridade_id', $_POST['senioridade_id']);

        $recrutador->salvarRecrutador();

        header('Location: /successRegister');
        exit;
    }

    // =========================
    // TELA SUCESSO
    // =========================
    public function successRegister()
    {
        $this->render('successRegister');
    }

    // =========================
    // OUTRAS TELAS
    // =========================
    public function lgpd()
    {
        $this->render('lgpd');
    }

    public function loginSelection()
    {
        $this->render('loginSelection');
    }

    public function studentLogin()
    {
        $this->render('studentLogin');
    }

    public function recruiterLogin()
    {
        $this->render('recruiterLogin');
    }

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
}