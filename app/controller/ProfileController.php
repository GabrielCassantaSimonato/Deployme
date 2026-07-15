<?php

namespace app\controller;

use MF\controller\Action;
use MF\model\Container;
use app\middleware\Auth;

class ProfileController extends Action
{
    /**
     * Renderiza o perfil de um usuário no sistema.
     * 
     * Identifica se a visualização é do próprio perfil logado ou de terceiros (modo leitura),
     * busca as informações completas do usuário, carrega suas publicações com os dados
     * associados de curtidas e comentários, e lista as contas que ele está seguindo.
     */
    public function profile()
    {
        Auth::validarAutenticacao();

        $usuarioModel = Container::getModel('Usuario');

        if (isset($_GET['id']) && !empty($_GET['id'])) {
            $id = $_GET['id'];

            if ($id == $_SESSION['id'] || (isset($_SESSION['tipo']) && $_SESSION['tipo'] == 'admin')) {
                $modoLeitura = false;
            } else {
                $modoLeitura = true;
            }
        } else {
            $id = $_SESSION['id'];
            $modoLeitura = false;
        }

        $dadosUsuario = $usuarioModel->buscarUsuarioCompleto($id);

        if (!$dadosUsuario) {
            echo "Usuário não encontrado.";
            exit;
        }

        $this->view->dadosUsuario = $dadosUsuario;
        $this->view->modo_leitura = $modoLeitura;

        $publicacao = Container::getModel('Publicacao');
        $publicacao->__set('usuario_id', $id);

        $publicacoes = $publicacao->getPublicacoesUsuario();

        $curtida = Container::getModel('Curtida');
        $comentario = Container::getModel('Comentario');

        foreach ($publicacoes as &$pub) {
            $resultado = $curtida->totalCurtidas($pub['id']);

            $pub['curtidas'] = $resultado['total'] ?? 0;
            $pub['curtido'] = $curtida->usuarioCurtiu($_SESSION['id'], $pub['id']) ? true : false;
            $pub['comentarios'] = $comentario->listarComentarios($pub['id']);
            $pub['total_comentarios'] = $comentario->totalComentarios($pub['id'])['total'];
        }

        $this->view->publicacoes = $publicacoes;

        $seguidor = Container::getModel('Seguidores');
        $this->view->seguindo = $seguidor->listarSeguindo($id);

        $this->render('profile');
    }

    /**
     * Renderiza o formulário de edição do perfil do usuário.
     * 
     * Carrega as informações do usuário logado na sessão ativa, além de buscar
     * as listas globais de universidades, cursos e semestres para preenchimento dos campos.
     */
    public function editProfile()
    {
        Auth::validarAutenticacao();

        $usuarioModel = Container::getModel('Usuario');
        $universidadeModel = Container::getModel('Universidade');
        $cursoModel = Container::getModel('Curso');
        $semestreModel = Container::getModel('Semestre');

        $this->view->universidades = $universidadeModel->getUniversidades();
        $this->view->cursos = $cursoModel->getCursos();
        $this->view->semestres = $semestreModel->getSemestres();

        $dadosUsuario = $usuarioModel->buscarUsuarioCompleto($_SESSION['id']);
        $this->view->dadosUsuario = $dadosUsuario;

        $this->render('editProfile');
    }

    /**
     * Processa a atualização de dados cadastrais e de mídia do usuário.
     * 
     * Valida a unicidade do e-mail de destino, gerencia o upload de novas fotos ou currículos,
     * atualiza o registro do perfil no banco de dados com os dados específicos do tipo de usuário
     * (estudante ou recrutador) e sincroniza as novas informações na sessão.
     */
    public function updateProfile()
    {
        Auth::validarAutenticacao();

        $id = $_SESSION['id'];
        $nome = $_POST['nome'];
        $email = $_POST['email'];

        $dados = [
            'id' => $id,
            'nome' => $nome,
            'email' => $email
        ];

        if ($_SESSION['tipo'] == 'estudante') {
            $dados['github'] = $_POST['github'];
            $dados['cep'] = $_POST['cep'];
            $dados['rua'] = $_POST['rua'];
            $dados['bairro'] = $_POST['bairro'];
            $dados['complemento'] = $_POST['complemento'];
            $dados['cidade'] = $_POST['cidade'];
            $dados['uf'] = $_POST['uf'];
            $dados['universidade_id'] = $_POST['universidade_id'];
            $dados['curso_id'] = $_POST['curso_id'];
            $dados['semestre_id'] = $_POST['semestre_id'];
        }

        if ($_SESSION['tipo'] == 'recrutador') {
            $dados['empresa'] = $_POST['empresa'];
        }

        if (!empty($_FILES['foto']['name'])) {
            $extensaoFoto = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $foto = uniqid() . '.' . $extensaoFoto;

            move_uploaded_file(
                $_FILES['foto']['tmp_name'],
                "uploads/fotos/" . $foto
            );

            $dados['foto'] = $foto;
            $_SESSION['foto_perfil'] = $foto;
        }

        if ($_SESSION['tipo'] == 'estudante' && !empty($_FILES['curriculo']['name'])) {
            $extensaoCurriculo = pathinfo($_FILES['curriculo']['name'], PATHINFO_EXTENSION);
            $curriculo = uniqid() . '.' . $extensaoCurriculo;

            move_uploaded_file(
                $_FILES['curriculo']['tmp_name'],
                "uploads/currículos/" . $curriculo
            );

            $dados['curriculo'] = $curriculo;
        }

        $usuarioModel = Container::getModel('Usuario');
        $usuarioModel->__set('email', $email);
        $usuarioEmail = $usuarioModel->buscarPorEmail();

        if ($usuarioEmail && $usuarioEmail['id'] != $id) {
            $_SESSION['error'] = "Este e-mail já está sendo utilizado.";
            header('Location: /editProfile');
            exit;
        }

        $usuarioModel->updateProfile($dados);

        $_SESSION['nome'] = $nome;
        $_SESSION['email'] = $email;

        if (isset($dados['foto'])) {
            $_SESSION['foto_perfil'] = $dados['foto'];
        }

        if ($_SESSION['tipo'] == 'estudante') {
            $_SESSION['github'] = $dados['github'];
            $_SESSION['cep'] = $dados['cep'];
            $_SESSION['rua'] = $dados['rua'];
            $_SESSION['bairro'] = $dados['bairro'];
            $_SESSION['complemento'] = $dados['complemento'];
            $_SESSION['cidade'] = $dados['cidade'];
            $_SESSION['uf'] = $dados['uf'];

            if (isset($dados['curriculo'])) {
                $_SESSION['curriculo'] = $dados['curriculo'];
            }
        }

        if ($_SESSION['tipo'] == 'recrutador') {
            $_SESSION['empresa'] = $dados['empresa'];
        }

        $_SESSION['success'] = "Perfil updated com sucesso!";
        header('Location: /editProfile');
        exit;
    }

    /**
     * Altera o status de ativação da conta de um usuário (bloquear ou reativar).
     * 
     * Identifica o destinatário da ação (uma requisição administrativa externa ou o próprio usuário),
     * aplica a modificação para o novo status solicitado e encerra a sessão ativa caso
     * o usuário logado tenha bloqueado a própria conta.
     */
    public function deactivateAccount()
    {
        Auth::validarAutenticacao();

        $usuarioModel = Container::getModel('Usuario');

        $idParaAlterar = isset($_REQUEST['id']) && $_SESSION['tipo'] == 'admin' ? $_REQUEST['id'] : $_SESSION['id'];
        $acao = isset($_GET['acao']) ? $_GET['acao'] : 'bloquear';

        $novoStatus = ($acao === 'ativar') ? 'ativo' : 'bloqueado';

        $usuarioModel->desativarConta($idParaAlterar, $novoStatus);

        if ($idParaAlterar == $_SESSION['id'] && $novoStatus === 'bloqueado') {
            session_destroy();
            $logout = true;
        } else {
            $logout = false;
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'logout' => $logout
        ]);
        exit;
    }
}