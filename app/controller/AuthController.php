<?php

namespace app\controller;

use MF\controller\Action;
use MF\model\Container;

class AuthController extends Action
{

    public function auth()
    {

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $email = $_POST['email'] ?? null;
        $senha = $_POST['senha'] ?? null;

        $usuario = Container::getModel('Usuario');

        $usuario->__set('email', $email);

        $dadosUsuario = $usuario->buscarPorEmail();

        // usuário existe?
        if (!$dadosUsuario) {

            header('Location: /login?erro=1');
            exit;
        }

        // valida senha
        if (!password_verify($senha, $dadosUsuario['senha'])) {

            header('Location: /login?erro=1');
            exit;
        }

        // SESSÃO PRINCIPAL
        $_SESSION['id'] = $dadosUsuario['id'];
        $_SESSION['nome'] = $dadosUsuario['nome'];
        $_SESSION['tipo'] = $dadosUsuario['tipo'];
        $_SESSION['foto_perfil'] = $dadosUsuario['foto'];

        // ESTUDANTE
        if ($dadosUsuario['tipo'] == 'estudante') {

            $estudante = Container::getModel('Estudante');

            $dadosEstudante =
                $estudante->buscarPorUsuario($dadosUsuario['id']);

            $_SESSION['cidade'] =
                $dadosEstudante['cidade'];

            $_SESSION['estado'] =
                $dadosEstudante['uf'];
        }

        // RECRUTADOR
        if ($dadosUsuario['tipo'] == 'recrutador') {

            $recrutador = Container::getModel('Recrutador');

            $dadosRecrutador =
                $recrutador->buscarPorUsuario($dadosUsuario['id']);

            $_SESSION['empresa'] =
                $dadosRecrutador['empresa'];
        }

        // REDIRECIONAMENTO
        header('Location: /timeline');
        exit;
    }
    public function logout()
    {
        session_start();

        session_destroy();

        header('Location: /');
        exit;
    }
}