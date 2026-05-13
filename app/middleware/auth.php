<?php

namespace app\middleware;

class Auth
{
    // LOGIN OBRIGATÓRIO
    public static function validarAutenticacao()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (
            !isset($_SESSION['id'])
            ||
            empty($_SESSION['id'])
        ) {

            header('Location: /login?auth=required');
            exit;
        }
    }

    // SOMENTE ADMIN
    public static function validarAdmin()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // NÃO ESTÁ LOGADO
        if (
            !isset($_SESSION['id'])
            ||
            empty($_SESSION['id'])
        ) {

            header('Location: /loginAdmin?auth=admin');
            exit;
        }

        // NÃO É ADMIN
        if ($_SESSION['tipo'] != 'admin') {

            header('Location: /timeline?erro=sem_permissao');
            exit;
        }
    }
}