<?php

namespace app\controller;

use MF\controller\Action;
use app\middleware\Auth;
use MF\model\Container;

class AdminController extends Action
{
    /**
     * Renderiza o painel administrativo (dashboard).
     * 
     * Valida se o usuário está autenticado e se possui a regra de administrador.
     * Recupera as métricas e dados consolidados do modelo Admin e renderiza a view correspondente.
     */
    public function admin()
    {
        Auth::validarPermissao('admin');

        if ($_SESSION['tipo'] != 'admin') {
            header('Location: /');
            exit;
        }

        $admin = Container::getModel('Admin');

        $this->view->dashboard = $admin->dashboard();

        $this->render('dashboard');
    }

    /**
     * Renderiza a linha do tempo (timeline) de moderação do administrador.
     * 
     * Carrega todas as publicações do sistema para análise administrativa, incluindo
     * o total de curtidas, status de curtida do próprio admin, comentários e
     * totalizadores de interações para cada publicação listada.
     */
    public function timelineAdmin()
    {
        Auth::validarPermissao('admin');

        $publicacao = Container::getModel('Publicacao');

        $publicacoes = $publicacao->listarPublicacoesAdmin();

        $curtida = Container::getModel('Curtida');
        $comentario = Container::getModel('Comentario');

        foreach ($publicacoes as &$pub) {
            $pub['curtidas'] = $curtida->totalCurtidas($pub['id'])['total'];

            $pub['curtido'] = $curtida->usuarioCurtiu($_SESSION['id'], $pub['id']) ? true : false;

            $pub['comentarios'] = $comentario->listarComentarios($pub['id']);

            $pub['total_comentarios'] = $comentario->totalComentarios($pub['id'])['total'];
        }

        $this->view->publicacoes = $publicacoes;

        $this->render('timelineAdmin');
    }

    /**
     * Remove uma publicação de forma forçada pelo painel administrativo.
     * 
     * Verifica a autenticação e permissão de admin, recupera o ID da publicação
     * via parâmetro GET e executa a exclusão definitiva, redirecionando o fluxo em seguida.
     */
    public function deletePostAdmin()
    {
        Auth::validarPermissao('admin');

        $publicacao = Container::getModel('Publicacao');

        $publicacao->__set('id', $_GET['id']);

        $publicacao->excluirPostAdmin();

        header('Location: /timelineAdmin?delete=success');
        exit;
    }

    /**
     * Bloqueia a conta de um usuário diretamente pela timeline de moderação.
     * 
     * Protege a rota garantindo perfil admin, desativa a conta do usuário com base no
     * ID fornecido via GET e retorna para a timeline administrativa exibindo a mensagem de sucesso.
     */
    public function blockUserTimeline()
    {
        Auth::validarPermissao('admin');

        $usuario = Container::getModel('Usuario');

        $usuario->desativarConta($_GET['id']);

        header('Location: /timelineAdmin?blocked=success');
        exit;
    }

    /**
     * Renderiza a listagem de gerenciamento de usuários para o administrador.
     * 
     * Valida os privilégios de acesso e recupera todos os usuários cadastrados
     * na plataforma para exibição e controle na view administrativa.
     */
    public function adminUsers()
    {
        Auth::validarPermissao('admin');

        $admin = Container::getModel('Admin');

        $this->view->usuarios = $admin->listarUsuarios();

        $this->render('adminUsers');
    }

    /**
     * Bloqueia a conta de um usuário através da lista geral de usuários.
     * 
     * Executa o processo de desativação de conta com base no ID recebido via GET
     * e redireciona o fluxo de volta para o painel de listagem de usuários.
     */
    public function blockUser()
    {
        Auth::validarPermissao('admin');

        $usuario = Container::getModel('Usuario');

        $usuario->desativarConta($_GET['id']);

        header('Location: /adminUsers?success=blocked');
        exit;
    }

    /**
     * Desbloqueia (reativa) a conta de um usuário suspenso.
     * 
     * Valida a requisição, executa a ação de reativação de conta com o ID
     * fornecido por parâmetro GET e atualiza a view de listagem de usuários.
     */
    public function unblockUser()
    {
        Auth::validarPermissao('admin');

        $usuario = Container::getModel('Usuario');

        $usuario->reativarConta($_GET['id']);

        header('Location: /adminUsers?success=unblocked');
        exit;
    }
}