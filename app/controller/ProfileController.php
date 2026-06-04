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

            // SE FOR O PRÓPRIO PERFIL
            if ($id == $_SESSION['id']) {
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

        $publicacao = Container::getModel('Publicacao');

        $publicacao->__set('usuario_id', $id);

        $publicacoes = $publicacao->getPublicacoesUsuario();

        // ==========================
        // CURTIDAS,COMENTÁRIOS
        // ==========================
        $curtida = Container::getModel('Curtida');
        $comentario = Container::getModel('Comentario');

        foreach ($publicacoes as &$pub) {

            $resultado = $curtida->totalCurtidas($pub['id']);

            $pub['curtidas'] = $resultado['total'] ?? 0;

            $pub['curtido'] = $curtida->usuarioCurtiu(
                $_SESSION['id'],
                $pub['id']
            ) ? true : false;

            $pub['comentarios'] =
                $comentario->listarComentarios(
                    $pub['id']
                );

            $pub['total_comentarios'] =
                $comentario->totalComentarios(
                    $pub['id']
                )['total'];
        }

        $this->view->publicacoes = $publicacoes;

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

            $extensaoFoto = pathinfo(
                $_FILES['foto']['name'],
                PATHINFO_EXTENSION
            );

            $foto = uniqid() . '.' . $extensaoFoto;

            move_uploaded_file(
                $_FILES['foto']['tmp_name'],
                "uploads/fotos/" . $foto
            );

            $dados['foto'] = $foto;

            $_SESSION['foto_perfil'] = $foto;

        }

        // CURRÍCULO
        if (
            $_SESSION['tipo'] == 'estudante'
            &&
            !empty($_FILES['curriculo']['name'])
        ) {

            $extensaoCurriculo = pathinfo(
                $_FILES['curriculo']['name'],
                PATHINFO_EXTENSION
            );

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

        if (
            $usuarioEmail
            &&
            $usuarioEmail['id'] != $id
        ) {

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

        $_SESSION['success'] = "Perfil atualizado com sucesso!";

        header('Location: /editProfile');

        exit;
    }
}
