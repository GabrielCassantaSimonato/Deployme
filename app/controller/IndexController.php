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
        $curso = Container::getModel('Curso');
        $universidade = Container::getModel('Universidade');
        $genero = Container::getModel('Genero');
        $semestre = Container::getModel('Semestre');

        $this->view->cursos = $curso->getCursos();
        $this->view->universidades = $universidade->getUniversidades();
        $this->view->generos = $genero->getGeneros();
        $this->view->semestres = $semestre->getSemestres();

        $this->render('signupStudent');
    }
    public function signUpRecruiter()
    {
        $senioridade = Container::getModel('Senioridade');
        $this->view->senioridades = $senioridade->getSenioridades();
        $this->render('signUpRecruiter');
    }
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
    public function studentRegister()
    {
        $this->render('recruiterLogin');
    }
}
?>