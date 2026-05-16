<?php

namespace app\controller;

use MF\controller\Action;
use MF\model\Container;
use app\middleware\Auth;

class ProfileController extends Action
{
    public function profile()
    {
        Auth::validarAutenticacao();

        $usuarioModel = Container::getModel('Usuario');

        $dadosUsuario = $usuarioModel->buscarUsuarioCompleto($_SESSION['id']);

        $this->view->dadosUsuario = $dadosUsuario;

        $this->render('profile');
    }
    public function editProfile()
    {

        Auth::validarAutenticacao();
        $usuarioModel = Container::getModel('Usuario');
        $dadosUsuario = $usuarioModel->buscarUsuarioCompleto($_SESSION['id']);

        $this->view->dadosUsuario = $dadosUsuario;
        $this->render('editProfile');
    }
}