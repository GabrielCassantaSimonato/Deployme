<?php

namespace app\middleware;

class Auth
{
    /**
     * Valida se o usuário comum está autenticado no sistema.
     * 
     * Inicia a sessão caso ela ainda não esteja ativa, verifica a existência e
     * o preenchimento do identificador do usuário na sessão e, caso ausente,
     * redireciona o usuário para a tela de login exigindo autenticação.
     */
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

    /**
     * Valida se o usuário logado possui privilégios de administrador.
     * 
     * Inicia a sessão se necessário, assegura que o usuário esteja autenticado,
     * e valida se o tipo de perfil registrado corresponde a "admin". Caso não
     * atenda aos requisitos, redireciona para a página apropriada com mensagem de erro.
     */
    public static function validarAdmin()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (
            !isset($_SESSION['id'])
            ||
            empty($_SESSION['id'])
        ) {
            header('Location: /loginAdmin?auth=admin');
            exit;
        }

        if ($_SESSION['tipo'] != 'admin') {
            header('Location: /timeline?erro=sem_permissao');
            exit;
        }
    }

    public static function validarPermissao($tiposPermitidos)
    {
        self::validarAutenticacao();
        // Garante que seja sempre um array
        $tiposPermitidos = (array) $tiposPermitidos;

        if (!in_array($_SESSION['tipo'], $tiposPermitidos)) {

            $_SESSION['erro_permissao'] = "Você não possui permissão para acessar esta página.";

            // Volta para a página anterior
            if (!empty($_SERVER['HTTP_REFERER'])) {
                header('Location: ' . $_SERVER['HTTP_REFERER']);
            } else {
                header('Location: /timeline');
            }

            exit;
        }
    }
}