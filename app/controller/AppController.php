<?php
namespace app\controller;
use MF\controller\Action;
use MF\model\Container;
use app\middleware\Auth;

class AppController extends Action
{

    public function timeline()
    {
        Auth::validarAutenticacao();
        $publicacao = Container::getModel('Publicacao');

        $this->view->publicacoes =
            $publicacao->listarPublicacoes();

        $this->render('timeline');
    }
    public function post()
    {
        Auth::validarAutenticacao();
        $imagem = null;

        // UPLOAD IMAGEM
        if (!empty($_FILES['imagem']['name'])) {

            $extensao = pathinfo(
                $_FILES['imagem']['name'],
                PATHINFO_EXTENSION
            );

            $nomeImagem = uniqid() . '.' . $extensao;

            move_uploaded_file(
                $_FILES['imagem']['tmp_name'],
                'uploads/publicacoes/' . $nomeImagem
            );

            $imagem = $nomeImagem;

        }

        $publicacao = Container::getModel('Publicacao');

        $publicacao->__set('usuario_id', $_SESSION['id']);
        $publicacao->__set('conteudo', $_POST['conteudo']);
        $publicacao->__set('imagem', $imagem);
        $publicacao->__set('tipo', 'post');

        $publicacao->salvar();

        header('Location: /timeline?post=sucesso');
    }
    public function vacancy()
    {
        Auth::validarAutenticacao();
        $imagem = null;

        // UPLOAD
        if (!empty($_FILES['imagem']['name'])) {

            $extensao = pathinfo(
                $_FILES['imagem']['name'],
                PATHINFO_EXTENSION
            );

            $nomeImagem = uniqid() . '.' . $extensao;

            move_uploaded_file(
                $_FILES['imagem']['tmp_name'],
                'uploads/publicacoes/' . $nomeImagem
            );

            $imagem = $nomeImagem;

        }

        // SALVA PUBLICAÇÃO
        $publicacao = Container::getModel('Publicacao');

        $publicacao->__set('usuario_id', $_SESSION['id']);
        $publicacao->__set('conteudo', $_POST['conteudo']);
        $publicacao->__set('imagem', $imagem);
        $publicacao->__set('tipo', 'vaga');

        $publicacaoId = $publicacao->salvar();

        // SALVA VAGA
        $publicacao->salvarVaga([

            'publicacao_id' => $publicacaoId,
            'titulo' => $_POST['titulo_vaga'],
            'empresa' => $_POST['empresa'],
            'localizacao' => $_POST['localizacao'],
            'modalidade' => $_POST['modalidade'],
            'salario' => $_POST['salario'],
            'descricao' => $_POST['conteudo']

        ]);

        header('Location: /timeline?post=sucesso');

    }
}
?>