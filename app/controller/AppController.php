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
        $moderacao = Container::getModel('ModeracaoAI');

        $analise = $moderacao->analisar(
            $_POST['conteudo']
        );
        if (!$analise['aprovado']) {

            header(
                'Location: /timeline?moderacao=bloqueado&motivo=' .
                urlencode($analise['motivo'])
            );

            exit;
        }

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

    public function updatePost()
    {
        Auth::validarAutenticacao();

        $publicacao = Container::getModel('Publicacao');
        $moderacao = Container::getModel('ModeracaoAI');

        $resultado = $moderacao->analisar($_POST['conteudo']);

        if (!$resultado['aprovado']) {

            header(
                'Location: /timeline?moderacao=bloqueado&motivo=' .
                urlencode($resultado['motivo'])
            );

            exit;
        }

        $publicacao->__set('id', $_POST['id']);
        $publicacao->__set('conteudo', $_POST['conteudo']);
        $publicacao->__set('usuario_id', $_SESSION['id']);

        $imagem = '';

        if (!empty($_FILES['imagem']['name'])) {

            $imagem = md5(time()) . '.jpg';

            move_uploaded_file(
                $_FILES['imagem']['tmp_name'],
                'uploads/publicacoes/' . $imagem
            );
        }

        $publicacao->__set('imagem', $imagem);

        $publicacao->updatePost();

        header('Location: /timeline?edit=post_sucesso');
    }

    public function updateVacancy()
    {
        Auth::validarAutenticacao();

        $imagem = '';

        // UPLOAD (Alinhado com o padrão usado na criação)
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

        // 1. ATUALIZA A PUBLICAÇÃO BASE (Tabela publicacoes)
        // Aproveitamos o mesmo método que você já usa para editar posts comuns
        $publicacao->__set('id', $_POST['id']);
        $publicacao->__set('usuario_id', $_SESSION['id']);
        $publicacao->__set('conteudo', $_POST['conteudo']);
        $publicacao->__set('imagem', $imagem);

        $publicacao->updatePost();

        // 2. ATUALIZA OS DETALHES DA VAGA (Tabela vagas)
        // Passamos o array $dados exatamente como fazemos no salvarVaga()
        $publicacao->updateVacancy([

            'publicacao_id' => $_POST['id'],
            // Atenção: na criação você usou 'titulo_vaga', mas no HTML do modal de 
            // edição o input se chama apenas 'titulo'. Mantive 'titulo' aqui.
            'titulo' => $_POST['titulo'],
            'empresa' => $_POST['empresa'],
            'localizacao' => $_POST['localizacao'],
            'modalidade' => $_POST['modalidade'],
            'salario' => $_POST['salario'],
            'descricao' => $_POST['conteudo']

        ]);

        header('Location: /timeline?edit=vaga_sucesso');
    }

    public function deletePost()
    {
        Auth::validarAutenticacao();

        $publicacao = Container::getModel('Publicacao');

        $publicacao->__set('id', $_GET['id']);
        $publicacao->__set('usuario_id', $_SESSION['id']);

        $publicacao->excluirPost();

        header('Location: /timeline?delete=success');
    }

    public function deleteVacancy()
    {
        Auth::validarAutenticacao();

        $vaga = Container::getModel('Publicacao');

        $vaga->__set('id', $_GET['id']);
        $vaga->__set('usuario_id', $_SESSION['id']);

        $vaga->excluirVaga();

        header('Location: /timeline?delete=success');
    }
}
?>