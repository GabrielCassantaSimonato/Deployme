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

        // PERFIL VIA URL
        if (isset($_GET['id']) && !empty($_GET['id'])) {
            $id = $_GET['id'];

            // O modo leitura será FALSE se for o próprio perfil OU se quem está logado for Admin
            if ($id == $_SESSION['id'] || (isset($_SESSION['tipo']) && $_SESSION['tipo'] == 'admin')) {
                $modoLeitura = false;
            } else {
                $modoLeitura = true;
            }
        } else {
            // PERFIL LOGADO
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

        // ==========================
        // PUBLICAÇÕES
        // ==========================
        $publicacao = Container::getModel('Publicacao');
        $publicacao->__set('usuario_id', $id);

        $publicacoes = $publicacao->getPublicacoesUsuario();

        // ==========================
        // CURTIDAS E COMENTÁRIOS
        // ==========================
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

        // ==========================
        // USUÁRIOS QUE ELE SEGUE
        // ==========================
        $seguidor = Container::getModel('Seguidores');
        $this->view->seguindo = $seguidor->listarSeguindo($id);

        // ==========================
        // RENDER
        // ==========================
        $this->render('profile');
    }

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

        // ESTUDANTE
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

        // RECRUTADOR
        if ($_SESSION['tipo'] == 'recrutador') {
            $dados['empresa'] = $_POST['empresa'];
        }

        // FOTO
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

        // CURRÍCULO
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

        // ATUALIZA SESSIONS
        $_SESSION['nome'] = $nome;
        $_SESSION['email'] = $email;

        // FOTO
        if (isset($dados['foto'])) {
            $_SESSION['foto_perfil'] = $dados['foto'];
        }

        // ESTUDANTE
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

        // RECRUTADOR
        if ($_SESSION['tipo'] == 'recrutador') {
            $_SESSION['empresa'] = $dados['empresa'];
        }

        $_SESSION['success'] = "Perfil updated com sucesso!";
        header('Location: /editProfile');
        exit;
    }

    public function deactivateAccount()
    {
        Auth::validarAutenticacao();

        $usuarioModel = Container::getModel('Usuario');

        // Define qual ID será alterado (Se for admin altera o passado por query string, se não, altera o próprio)
        $idParaAlterar = isset($_REQUEST['id']) && $_SESSION['tipo'] == 'admin' ? $_REQUEST['id'] : $_SESSION['id'];
        $acao = isset($_GET['acao']) ? $_GET['acao'] : 'bloquear';

        // Traduz o parâmetro recebido via JS para a string correta do Banco de Dados
        $novoStatus = ($acao === 'ativar') ? 'ativo' : 'bloqueado';

        // Certifique-se de que seu método desativarConta trate o segundo parâmetro string ('ativo' / 'bloqueado')
        $usuarioModel->desativarConta($idParaAlterar, $novoStatus);

        // Só destrói a sessão se a conta bloqueada for a do próprio usuário logado
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