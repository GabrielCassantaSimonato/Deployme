<?php

namespace app\controller;

use MF\controller\Action;
use MF\model\Container;

class AuthController extends Action
{
    /**
     * Realiza a autenticação do usuário no sistema.
     * 
     * Verifica as credenciais (e-mail e senha), valida o status da conta,
     * inicia a sessão e armazena os dados do usuário (estudante, recrutador ou admin),
     * redirecionando-o para a respectiva tela inicial (timeline).
     */
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

        if (!$dadosUsuario) {
            header('Location: /login?erro=1');
            exit;
        }

        if ($dadosUsuario['status'] === 'bloqueado') {
            header('Location: /login?erro=bloqueado');
            exit;
        }

        if (!password_verify($senha, $dadosUsuario['senha'])) {
            header('Location: /login?erro=1');
            exit;
        }

        $_SESSION['id'] = $dadosUsuario['id'];
        $_SESSION['nome'] = $dadosUsuario['nome'];
        $_SESSION['tipo'] = $dadosUsuario['tipo'];
        $_SESSION['foto_perfil'] = $dadosUsuario['foto'];
        $_SESSION['email'] = $dadosUsuario['email'];

        if ($dadosUsuario['tipo'] == 'estudante') {
            $estudante = Container::getModel('Estudante');
            $dadosEstudante = $estudante->buscarPorUsuario($dadosUsuario['id']);

            $_SESSION['cidade'] = $dadosEstudante['cidade'];
            $_SESSION['uf'] = $dadosEstudante['uf'];
        }

        if ($dadosUsuario['tipo'] == 'recrutador') {
            $recrutador = Container::getModel('Recrutador');
            $dadosRecrutador = $recrutador->buscarPorUsuario($dadosUsuario['id']);

            $_SESSION['empresa'] = $dadosRecrutador['empresa'];
        }

        if ($dadosUsuario['tipo'] == 'admin') {
            header('Location: /timelineAdmin');
            exit;
        }

        header('Location: /timeline');
        exit;
    }

    /**
     * Encerra a sessão do usuário atual.
     * 
     * Inicia o contexto da sessão para garantir o acesso, destrói todos os dados
     * registrados na sessão ativa e redireciona o usuário para a página inicial (home).
     */
    public function logout()
    {
        session_start();
        session_destroy();

        header('Location: /');
        exit;
    }

    /**
     * Renderiza a view de reativação de conta.
     * 
     * Exibe a página com o formulário para que o usuário possa solicitar
     * a recuperação/reativação de seu perfil no sistema.
     */
    public function reactivateAccount()
    {
        $this->render('reactivateAccount');
    }

    /**
     * Processa a ação de reativação de conta enviada pelo formulário.
     * 
     * Valida os campos recebidos (e-mail e aceite do termo de LGPD), busca o usuário
     * correspondente, verifica se a conta já está ativa e, caso esteja inativa/desativada,
     * executa a reativação antes de redirecionar para a tela de login.
     */
    public function reactivateAccountAction()
    {
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $lgpd = isset($_POST['lgpd']) ? true : false;

        if (empty($email) || !$lgpd) {
            header('Location: /reactivateAccount?error=dados_invalidos');
            exit;
        }

        $usuario = Container::getModel('Usuario');
        $usuario->__set('email', $email);

        $dadosUsuario = $usuario->buscarPorEmail();

        $usuario_id = is_array($dadosUsuario) ? ($dadosUsuario['id'] ?? null) : $usuario->__get('id');
        $usuario_status = is_array($dadosUsuario) ? ($dadosUsuario['status'] ?? null) : $usuario->__get('status');

        if (!$usuario_id) {
            header('Location: /reactivateAccount?error=usuario_nao_encontrado');
            exit;
        }

        if ($usuario_status == 'ativo') {
            header('Location: /reactivateAccount?info=ja_ativo');
            exit;
        }

        $usuario->reativarConta($usuario_id);

        header('Location: /login?reactivated=success');
        exit;
    }
}