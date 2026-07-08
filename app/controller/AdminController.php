<?php

namespace app\controller;

use MF\controller\Action;
use app\middleware\Auth;
use MF\model\Container;

class AdminController extends Action
{

    public function admin()
    {
        Auth::validarAutenticacao();

        if ($_SESSION['tipo'] != 'admin') {

            header('Location: /');
            exit;

        }

        $admin = Container::getModel('Admin');

        $this->view->dashboard = $admin->dashboard();

        $this->render('dashboard');
    }
    public function timelineAdmin()
    {
        Auth::validarAutenticacao();

        $publicacao = Container::getModel('Publicacao');

        $publicacoes =
            $publicacao->listarPublicacoesAdmin();

        $curtida = Container::getModel('Curtida');
        $comentario = Container::getModel('Comentario');

        foreach ($publicacoes as &$pub) {

            $pub['curtidas'] =
                $curtida->totalCurtidas($pub['id'])['total'];

            $pub['curtido'] =
                $curtida->usuarioCurtiu($_SESSION['id'], $pub['id']) ? true : false;

            $pub['comentarios'] =
                $comentario->listarComentarios($pub['id']);

            $pub['total_comentarios'] =
                $comentario->totalComentarios($pub['id'])['total'];

        }

        $this->view->publicacoes = $publicacoes;

        $this->render('timelineAdmin');
    }
    public function deletePostAdmin()
    {
        Auth::validarAutenticacao();

        if ($_SESSION['tipo'] != 'admin') {
            header('Location: /');
            exit;
        }

        $publicacao = Container::getModel('Publicacao');

        $publicacao->__set('id', $_GET['id']);

        $publicacao->excluirPostAdmin();

        header('Location: /timelineAdmin?delete=success');
        exit;
    }
    public function blockUser()
    {
        Auth::validarAutenticacao();

        if ($_SESSION['tipo'] != 'admin') {
            header('Location: /');
            exit;
        }

        $usuario = Container::getModel('Usuario');

        $usuario->desativarConta($_GET['id']);

        header('Location: /timelineAdmin?blocked=success');
        exit;
    }
}