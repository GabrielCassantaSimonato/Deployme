<?php

namespace app\controller;

use MF\controller\Action;
use MF\model\Container;
use app\middleware\Auth;
use app\services\NotificationService;

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

        $analise = $moderacao->analisar($_POST['conteudo']);

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
        $publicacao->__set('id', $_POST['id']);
        $publicacao->__set('usuario_id', $_SESSION['id']);
        $publicacao->__set('conteudo', $_POST['conteudo']);
        $publicacao->__set('imagem', $imagem);

        $publicacao->updatePost();

        // 2. ATUALIZA OS DETALHES DA VAGA (Tabela vagas)
        $publicacao->updateVacancy([
            'publicacao_id' => $_POST['id'],
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

        $publicacaoId = (int) $_POST['publicacao'];
        $usuarioOrigem = $_SESSION['id'];

        $curtida = Container::getModel('Curtida');
        $curtida->curtir($usuarioOrigem, $publicacaoId);

        $total = $curtida->totalCurtidas($publicacaoId);

        // --- LOGICA DE NOTIFICAÇÃO ---
        $publicacaoModel = Container::getModel('Publicacao');
        $post = $publicacaoModel->getById($publicacaoId); // Pega dados do post original

        // Notifica apenas se o dono do post existir e não for o próprio usuário curtindo seu post
        if ($post && isset($post['usuario_id']) && $post['usuario_id'] != $usuarioOrigem) {
            \app\model\Notificacao::salvar(
                $post['usuario_id'], // Destino: dono do post
                $usuarioOrigem,      // Origem: quem curtiu
                'like',
                $publicacaoId
            );
        }
        // -----------------------------

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
        $curtida->descurtir($_SESSION['id'], $_POST['publicacao']);

        $total = $curtida->totalCurtidas($_POST['publicacao']);

        echo json_encode([
            'status' => 'ok',
            'total' => $total['total']
        ]);

        exit;
    }

    public function comment()
    {
        Auth::validarAutenticacao();

        $publicacaoId = (int) $_POST['publicacao_id'];
        $usuarioOrigem = $_SESSION['id'];
        $textoComentario = $_POST['comentario'];

        $comentario = Container::getModel('Comentario');
        $comentario->comentar(
            $usuarioOrigem,
            $publicacaoId,
            $textoComentario
        );

        // --- LOGICA DE NOTIFICAÇÃO ---
        $publicacaoModel = Container::getModel('Publicacao');
        $post = $publicacaoModel->getById($publicacaoId);

        if ($post && isset($post['usuario_id']) && $post['usuario_id'] != $usuarioOrigem) {
            \app\model\Notificacao::salvar(
                $post['usuario_id'], // Destino: dono do post
                $usuarioOrigem,      // Origem: quem comentou
                'comment',
                $publicacaoId
            );
        }
        // -----------------------------

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
                'comentario' => $textoComentario,
                'data' => date('d/m/Y H:i'),
                'total_comentarios' => $comentarioModel->totalComentarios($publicacaoId)['total']
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
        $comentario->delete($_GET['id'], $_SESSION['id']);

        if (
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
        ) {
            $comentarioModel = Container::getModel('Comentario');

            echo json_encode([
                'success' => true,
                'publicacao_id' => $_GET['publicacao_id'],
                'total_comentarios' => $comentarioModel->totalComentarios($_GET['publicacao_id'])['total']
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

        header('Content-Type: application/json');
        echo json_encode(['sucesso' => true]);
        exit;
    }

    public function share()
    {
        Auth::validarAutenticacao();

        $publicacaoId = (int) $_GET['id'];
        $usuarioOrigem = $_SESSION['id'];

        $publicacao = Container::getModel('Publicacao');
        $publicacao->compartilhar($usuarioOrigem, $publicacaoId);

        // --- LOGICA DE NOTIFICAÇÃO ---
        $post = $publicacao->getById($publicacaoId);

        if ($post && isset($post['usuario_id']) && $post['usuario_id'] != $usuarioOrigem) {
            \app\model\Notificacao::salvar(
                $post['usuario_id'], // Destino: dono do post original
                $usuarioOrigem,      // Origem: quem compartilhou
                'share',
                $publicacaoId
            );
        }
        // -----------------------------

        header('Location: /timeline?share=sucesso');
    }

    public function people()
    {
        Auth::validarAutenticacao();

        $usuario = Container::getModel('Usuario');
        $this->view->usuarios = $usuario->listarUsuarios();

        $this->render('people');
    }

    public function follow()
    {
        Auth::validarAutenticacao();

        $usuarioDestino = (int) $_POST['usuario_id'];
        $usuarioOrigem = (int) $_SESSION['id'];

        $seguidor = Container::getModel('Seguidores');

        $seguiu = $seguidor->seguir(
            $usuarioOrigem,
            $usuarioDestino
        );

        if ($seguiu) {
            \app\model\Notificacao::salvar(
                $usuarioDestino,
                $usuarioOrigem,
                'follow'
            );
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true
        ]);
        exit;
    }

    public function unfollow()
    {
        Auth::validarAutenticacao();

        $seguidor = Container::getModel('Seguidores');
        $seguidor->deixarDeSeguir($_SESSION['id'], $_POST['usuario_id']);

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    public function followers()
    {
        Auth::validarAutenticacao();

        $seguidor = Container::getModel('Seguidores');
        $this->view->seguidores = $seguidor->listarSeguidores($_SESSION['id']);
        $this->view->seguindo = $seguidor->listarSeguindo($_SESSION['id']);

        $this->render('followers');
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

        $candidatura = Container::getModel('Candidatura');

        $jaExiste = $candidatura->jaCandidatou(
            $_POST['vaga_id'],
            $_SESSION['id']
        );

        $publicacao = $candidatura->buscarPublicacaoDaVaga($_POST['vaga_id']);
        $publicacao_id = $publicacao['publicacao_id'];

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

        $candidatura = Container::getModel('Candidatura');
        $this->view->candidaturas = $candidatura->listarMinhasCandidaturas($_SESSION['id']);

        $this->render('myApplications');
    }

    public function myVacancies()
    {
        Auth::validarAutenticacao();

        $publicacao = Container::getModel('Publicacao');
        $this->view->vagas = $publicacao->listarMinhasVagas($_SESSION['id']);

        $this->render('myVacancies');
    }

    public function vacancyCandidates()
    {
        Auth::validarAutenticacao();

        $vaga_id = $_GET['id'];
        $candidatura = Container::getModel('Candidatura');
        $publicacao = Container::getModel('Publicacao');

        $this->view->vaga = $publicacao->buscarVagaPorId($vaga_id);
        $this->view->candidatos = $candidatura->listarCandidatos($vaga_id);

        $this->render('vacancyCandidates');
    }

    public function updateApplicationStatus()
    {
        Auth::validarAutenticacao();

        $candidatura = Container::getModel('Candidatura');
        $candidatura->alterarStatus($_POST['candidatura_id'], $_POST['status']);

        header('Location: /vacancyCandidates?id=' . $_POST['vaga_id']);
        exit;
    }

    public function withdrawApplication()
    {
        Auth::validarAutenticacao();

        $candidatura = Container::getModel('Candidatura');
        $candidatura->desistir($_POST['vaga_id'], $_SESSION['id']);

        header('Location: /myApplications');
        exit;
    }

    public function chat()
    {
        Auth::validarAutenticacao();

        $usuario = Container::getModel('Usuario');
        $this->view->usuarios = $usuario->listarUsuariosChat();

        // MAPEIA E ATRIBUI DADOS DA ÚLTIMA MENSAGEM DE CADA USUÁRIO
        foreach ($this->view->usuarios as &$u) {
            $ultima = $usuario->buscarUltimaMensagem($u['id']);

            $u['ultima_mensagem'] = $ultima['mensagem'] ?? null;
            $u['ultima_data'] = $ultima['created_at'] ?? null;
            $u['foi_enviada_por_mim'] = ($ultima['remetente_id'] ?? null) == $_SESSION['id'];
            $mensagem = Container::getModel('Mensagem');
            $u['nao_lidas'] = $mensagem->contarMensagensNaoLidas(
                $_SESSION['id'],
                $u['id']
            );
        }

        // ORDENA A LISTA DE USUÁRIOS COM BASE NA DATA DA ÚLTIMA MENSAGEM (MAIS RECENTE PRIMEIRO)
        usort($this->view->usuarios, function ($a, $b) {
            $dataA = $a['ultima_data'] ?? '0000-00-00 00:00:00';
            $dataB = $b['ultima_data'] ?? '0000-00-00 00:00:00';

            return strtotime($dataB) - strtotime($dataA);
        });

        $this->view->mensagens = [];
        $this->view->conversaAtual = null;
        $this->view->usuarioSelecionado = null;

        if (isset($_GET['conversa'])) {
            $mensagem = Container::getModel('Mensagem');
            $this->view->conversaAtual = $_GET['conversa'];
            $this->view->mensagens = $mensagem->buscarMensagens($_GET['conversa']);
            $mensagem->marcarComoLida($_GET['conversa'], $_SESSION['id']);

            $conversaModel = Container::getModel('Conversa');
            $dadosConversa = $conversaModel->buscarPorId($_GET['conversa']);

            if ($dadosConversa) {
                $this->view->usuarioSelecionado = ($dadosConversa['usuario1_id'] == $_SESSION['id'])
                    ? $dadosConversa['usuario2_id']
                    : $dadosConversa['usuario1_id'];
            }
        }

        $this->render('chat');
    }

    public function openConversation()
    {
        Auth::validarAutenticacao();

        $conversa = Container::getModel('Conversa');
        $conversa_id = $conversa->buscarOuCriarConversa(
            $_SESSION['id'],
            $_GET['usuario']
        );

        header('Location: /chat?conversa=' . $conversa_id);
        exit;
    }

    public function sendMessage()
    {
        Auth::validarAutenticacao();

        $nomeImagem = null;

        if (
            isset($_FILES['imagem']) &&
            $_FILES['imagem']['error'] === UPLOAD_ERR_OK
        ) {

            $permitidos = [
                'image/jpeg',
                'image/png',
                'image/webp',
                'image/gif'
            ];

            if (
                in_array(
                    $_FILES['imagem']['type'],
                    $permitidos
                )
            ) {

                $extensao = pathinfo(
                    $_FILES['imagem']['name'],
                    PATHINFO_EXTENSION
                );

                $nomeImagem =
                    uniqid('chat_') .
                    '.' .
                    $extensao;

                move_uploaded_file(
                    $_FILES['imagem']['tmp_name'],
                    __DIR__ . '/../../public/uploads/chat/' . $nomeImagem
                );

            }

        }

        $mensagem = Container::getModel(
            'Mensagem'
        );

        $mensagem->salvarMensagem(

            $_POST['conversa_id'],

            $_SESSION['id'],

            $_POST['mensagem'],

            $nomeImagem

        );

        header(
            'Location: /chat?conversa=' .
            $_POST['conversa_id']
        );

        exit;
    }
    public function loadMessages()
    {
        Auth::validarAutenticacao();

        if (!isset($_GET['conversa'])) {
            http_response_code(400);
            echo json_encode([
                'erro' => 'Conversa não informada.'
            ]);
            exit;
        }

        $mensagem = Container::getModel('Mensagem');

        $mensagens = $mensagem->buscarMensagens($_GET['conversa']);

        header('Content-Type: application/json');

        echo json_encode($mensagens);

        exit;
    }

    public function loadConversations()
    {

        Auth::validarAutenticacao();

        $usuario = Container::getModel(

            'Usuario'

        );

        echo json_encode(

            $usuario->listarUsuariosChat()

        );

        exit;

    }
    public function deleteMessage()
    {

        Auth::validarAutenticacao();

        if (!isset($_POST['id'])) {

            http_response_code(400);

            echo json_encode([

                'status' => 'erro',

                'mensagem' => 'Mensagem inválida.'

            ]);

            exit;

        }

        $mensagem = Container::getModel(

            'Mensagem'

        );

        $resultado = $mensagem->deleteMessage(

            $_POST['id'],

            $_SESSION['id']

        );

        echo json_encode([

            'status' => $resultado ? 'ok' : 'erro'

        ]);

        exit;

    }
    public function editMessage()
    {
        Auth::validarAutenticacao();

        $id = $_POST['id'] ?? null;
        $mensagem = trim($_POST['mensagem'] ?? '');
        $excluirImagem = ($_POST['excluir_imagem'] ?? '0') == '1';

        $novaImagem = null;

        if (
            isset($_FILES['nova_imagem']) &&
            $_FILES['nova_imagem']['error'] === UPLOAD_ERR_OK
        ) {

            $permitidos = [
                'image/jpeg',
                'image/png',
                'image/webp',
                'image/gif'
            ];

            if (in_array($_FILES['nova_imagem']['type'], $permitidos)) {

                $extensao = pathinfo(
                    $_FILES['nova_imagem']['name'],
                    PATHINFO_EXTENSION
                );

                $novaImagem =
                    uniqid('chat_') .
                    '.' .
                    $extensao;

                move_uploaded_file(

                    $_FILES['nova_imagem']['tmp_name'],

                    __DIR__ .
                    '/../../public/uploads/chat/' .
                    $novaImagem

                );

            }

        }

        $mensagemModel = Container::getModel('Mensagem');

        $imagemFinal = $mensagemModel->editarMensagem(
            $id,
            $_SESSION['id'],
            $mensagem,
            $novaImagem,
            $excluirImagem
        );

        echo json_encode([
            "status" => "ok",
            "mensagem" => $mensagem,
            "imagem" => $imagemFinal
        ]);
    }
    public function loadNotifications()
    {
        Auth::validarAutenticacao();


        $notificacaoModel = Container::getModel('Notificacao');
        $notificacoes = $notificacaoModel->listar($_SESSION['id']);

        header('Content-Type: application/json');
        echo json_encode($notificacoes);
        exit;
    }
    public function readNotification()
    {
        Auth::validarAutenticacao();
        $notificacaoId = isset($_POST['id']) ? (int) $_POST['id'] : null;

        if ($notificacaoId) {
            $notificacao = Container::getModel('Notificacao');


            $notificacao->marcarComoLida($notificacaoId);
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

}