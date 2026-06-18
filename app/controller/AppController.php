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

        // SEGUIDORES
        $seguidores = Container::getModel('Seguidores');
        $this->view->totalSeguidores = $seguidores->totalSeguidores($_SESSION['id']);
        $this->view->totalSeguindo = $seguidores->totalSeguindo($_SESSION['id']);

        // PUBLICAÇÕES
        $publicacao = Container::getModel('Publicacao');

        $publicacoes = $publicacao->listarPublicacoes($_SESSION['id']);

        // CURTIDAS, COMENTÁRIOS
        $curtida = Container::getModel('Curtida');
        $comentario = Container::getModel('Comentario');

        foreach ($publicacoes as &$pub) {

            $pub['curtidas'] = $curtida->totalCurtidas($pub['id'])['total'];

            $pub['curtido'] = $curtida->usuarioCurtiu($_SESSION['id'], $pub['id']) ? true : false;

            $pub['comentarios'] = $comentario->listarComentarios($pub['id']);

            $pub['total_comentarios'] = $comentario->totalComentarios($pub['id'])['total'];
        }

        $this->view->publicacoes = $publicacoes;
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
    public function like()
    {
        Auth::validarAutenticacao();

        $curtida = Container::getModel('Curtida');

        $curtida->curtir(
            $_SESSION['id'],
            $_POST['publicacao']
        );

        $total = $curtida->totalCurtidas(
            $_POST['publicacao']
        );

        echo json_encode([
            'status' => 'ok',
            'total' => $total['total']
        ]);

        exit;
    }

    public function unlike()
    {
        Auth::validarAutenticacao();

        $curtida = Container::getModel('Curtida');

        $curtida->descurtir(
            $_SESSION['id'],
            $_POST['publicacao']
        );

        $total = $curtida->totalCurtidas(
            $_POST['publicacao']
        );

        echo json_encode([
            'status' => 'ok',
            'total' => $total['total']
        ]);

        exit;
    }

    public function comment()
    {
        Auth::validarAutenticacao();

        $comentario = Container::getModel('Comentario');

        $comentario->comentar(
            $_SESSION['id'],
            $_POST['publicacao_id'],
            $_POST['comentario']
        );

        // SE FOR AJAX
        if (
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
        ) {

            $comentarioModel = Container::getModel('Comentario');

            echo json_encode([
                'success' => true,
                'nome' => $_SESSION['nome'],
                'foto' => !empty($_SESSION['foto_perfil'])
                    ? '/uploads/fotos/' . $_SESSION['foto_perfil']
                    : '/uploads/fotos/default-user.png',
                'comentario' => $_POST['comentario'],
                'data' => date('d/m/Y H:i'),
                'total_comentarios' =>
                    $comentarioModel->totalComentarios(
                        $_POST['publicacao_id']
                    )['total']
            ]);

            exit;
        }

        // FUNCIONAMENTO NORMAL
        header('Location: /timeline');
    }

    public function deleteComment()
    {
        Auth::validarAutenticacao();

        $comentario = Container::getModel('Comentario');

        $comentario->delete(
            $_GET['id'],
            $_SESSION['id']
        );

        if (
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
        ) {

            $comentarioModel =
                Container::getModel('Comentario');

            echo json_encode([
                'success' => true,
                'publicacao_id' => $_GET['publicacao_id'],
                'total_comentarios' =>
                    $comentarioModel
                        ->totalComentarios(
                            $_GET['publicacao_id']
                        )['total']
            ]);

            exit;
        }

        header('Location: /timeline');
    }

    public function editComment()
    {
        Auth::validarAutenticacao();

        $comentario = Container::getModel('Comentario');

        $comentario->editar(
            $_POST['id'],
            $_SESSION['id'],
            $_POST['comentario']
        );

        // Retorna um JSON de sucesso para o nosso JavaScript ler
        header('Content-Type: application/json');
        echo json_encode(['sucesso' => true]);
        exit;
    }

    public function share()
    {
        Auth::validarAutenticacao();

        $publicacao = Container::getModel('Publicacao');
        $publicacao->compartilhar($_SESSION['id'], $_GET['id']);
        header('Location:/timeline?share=sucesso');
    }

    public function people()
    {
        Auth::validarAutenticacao();

        $usuario = Container::getModel('Usuario');

        $this->view->usuarios =
            $usuario->listarUsuarios();

        $this->render('people');
    }

    public function follow()
    {
        Auth::validarAutenticacao();

        $seguidor = Container::getModel('Seguidores');
        $seguidor->seguir($_SESSION['id'], $_POST['usuario_id']);
        echo json_encode(['success' => true]);
        exit;
    }

    public function unfollow()
    {
        Auth::validarAutenticacao();

        $seguidor =
            Container::getModel(
                'Seguidores'
            );

        $seguidor->deixarDeSeguir(
            $_SESSION['id'],
            $_POST['usuario_id']
        );

        header(
            'Content-Type: application/json'
        );

        echo json_encode([
            'success' => true
        ]);

        exit;
    }

    public function followers()
    {
        Auth::validarAutenticacao();

        $seguidor =
            Container::getModel(
                'Seguidores'
            );

        $this->view->seguidores =
            $seguidor->listarSeguidores(
                $_SESSION['id']
            );

        $this->view->seguindo =
            $seguidor->listarSeguindo(
                $_SESSION['id']
            );

        $this->render(
            'followers'
        );
    }

    public function viewVacancies()
    {
        Auth::validarAutenticacao();

        $vaga = Container::getModel('Publicacao');

        $this->view->vagas = $vaga->listarTodasVagas();

        $this->render('viewVacancies');
    }

    public function vacancyDetails()
    {
        Auth::validarAutenticacao();

        $vaga = Container::getModel('Publicacao');
        $this->view->vaga = $vaga->buscarVagaPorPublicacao($_GET['id']);
        $estudante = Container::getModel('Estudante');
        $this->view->curriculo = $estudante->buscarCurriculo($_SESSION['id']);
        $this->render('vacancyDetails');
    }

    public function applyVacancy()
    {
        Auth::validarAutenticacao();

        $candidatura =
            Container::getModel(
                'Candidatura'
            );

        $jaExiste =
            $candidatura->jaCandidatou(

                $_POST['vaga_id'],

                $_SESSION['id']

            );

        $publicacao =

            $candidatura->buscarPublicacaoDaVaga(

                $_POST['vaga_id']

            );

        $publicacao_id =

            $publicacao['publicacao_id'];

        if ($jaExiste) {

            header(

                'Location: /vacancyDetails?id=' .

                $publicacao_id .

                '&alreadyApplied=1'

            );

            exit;
        }

        $candidatura->salvar(

            $_POST['vaga_id'],

            $_SESSION['id'],

            $_POST['email'],

            $_POST['celular'],

            $_POST['github'] ?? null,

            $_POST['curriculo']

        );

        header(

            'Location: /vacancyDetails?id=' .

            $publicacao_id .

            '&successApply=1'

        );

        exit;
    }

    public function myApplications()
    {
        Auth::validarAutenticacao();

        $candidatura =

            Container::getModel(
                'Candidatura'
            );

        $this->view->candidaturas =

            $candidatura->listarMinhasCandidaturas(

                $_SESSION['id']

            );

        $this->render(
            'myApplications'
        );
    }

    public function myVacancies()
    {
        Auth::validarAutenticacao();

        $publicacao =

            Container::getModel(
                'Publicacao'
            );

        $this->view->vagas =

            $publicacao->listarMinhasVagas(

                $_SESSION['id']

            );

        $this->render(
            'myVacancies'
        );
    }

    public function vacancyCandidates()
    {
        Auth::validarAutenticacao();

        $vaga_id =

            $_GET['id'];

        $candidatura =

            Container::getModel(
                'Candidatura'
            );

        $publicacao =

            Container::getModel(
                'Publicacao'
            );

        $this->view->vaga =

            $publicacao->buscarVagaPorId(
                $vaga_id
            );

        $this->view->candidatos =

            $candidatura->listarCandidatos(
                $vaga_id
            );

        $this->render(
            'vacancyCandidates'
        );
    }

}
?>