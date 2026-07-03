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
        // Conta bloqueada
        if ($dadosUsuario['status'] === 'bloqueado') {

            header('Location: /login?erro=bloqueado');
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
        $_SESSION['email'] = $dadosUsuario['email'];

        // ESTUDANTE
        if ($dadosUsuario['tipo'] == 'estudante') {
            $estudante = Container::getModel('Estudante');
            $dadosEstudante = $estudante->buscarPorUsuario($dadosUsuario['id']);

            $_SESSION['cidade'] = $dadosEstudante['cidade'];
            $_SESSION['uf'] = $dadosEstudante['uf'];
        }

        // RECRUTADOR
        if ($dadosUsuario['tipo'] == 'recrutador') {
            $recrutador = Container::getModel('Recrutador');
            $dadosRecrutador = $recrutador->buscarPorUsuario($dadosUsuario['id']);

            $_SESSION['empresa'] = $dadosRecrutador['empresa'];
        }

        // ADMIN
        if ($dadosUsuario['tipo'] == 'admin') {
            header('Location: /admin');
            exit;
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
    public function reactivateAccount()
    {
        $this->render('reactivateAccount');
    }
    public function reactivateAccountAction()
    {
        // 1. Captura os dados do formulário HTML
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $lgpd = isset($_POST['lgpd']) ? true : false;

        // 2. Validação básica de preenchimento e LGPD
        if (empty($email) || !$lgpd) {
            header('Location: /reactivateAccount?error=dados_invalidos');
            exit;
        }

        $usuario = Container::getModel('Usuario');

        // --- AJUSTE AQUI ---
        // Atribui o e-mail ao objeto antes de buscar (padrão do seu modelo)
        $usuario->__set('email', $email);
        // Se o seu framework não usar __set, tente: $usuario->email = $email;

        // 3. Usa o seu método existente que não recebe parâmetros
        $dadosUsuario = $usuario->buscarPorEmail();

        // Se o seu método buscarPorEmail retornar o próprio objeto preenchido em vez de um array,
        // nós pegamos o ID direto dele. Vamos garantir os dois cenários:
        $usuario_id = is_array($dadosUsuario) ? ($dadosUsuario['id'] ?? null) : $usuario->__get('id');
        $usuario_status = is_array($dadosUsuario) ? ($dadosUsuario['status'] ?? null) : $usuario->__get('status');

        if (!$usuario_id) {
            // Usuário não encontrado
            header('Location: /reactivateAccount?error=usuario_nao_encontrado');
            exit;
        }

        // 4. Verifica se a conta já está ativa
        if ($usuario_status == 'ativo') {
            header('Location: /reactivateAccount?info=ja_ativo');
            exit;
        }

        // 5. Se passou pelas validações, reativa a conta usando o ID encontrado
        $usuario->reativarConta($usuario_id);

        // 6. Redireciona para o login informando o sucesso
        header('Location: /login?reactivated=success');
        exit;
    }
}