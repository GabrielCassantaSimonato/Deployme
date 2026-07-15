<?php

namespace app\controller;

use MF\controller\Action;
use MF\model\Container;
use app\middleware\Auth;

class AppController extends Action
{
    /**
     * Renderiza a linha do tempo (timeline) principal do usuário.
     * 
     * Carrega as estatísticas de seguidores, lista as publicações relevantes,
     * e popula cada postagem com dados de curtidas, comentários e o status
     * de interação do usuário atual antes de exibir a view da timeline.
     */
    public function timeline()
    {
        Auth::validarAutenticacao();

        $seguidores = Container::getModel('Seguidores');
        $this->view->totalSeguidores = $seguidores->totalSeguidores($_SESSION['id']);
        $this->view->totalSeguindo = $seguidores->totalSeguindo($_SESSION['id']);

        $publicacao = Container::getModel('Publicacao');
        $publicacoes = $publicacao->listarPublicacoes($_SESSION['id']);

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

    /**
     * Cria uma nova publicação do tipo "post".
     * 
     * Processa o upload opcional de imagens anexadas, envia o conteúdo textual
     * para moderação automática por inteligência artificial e, caso aprovado,
     * persiste o registro no banco de dados.
     */
    public function post()
    {
        Auth::validarAutenticacao();
        $imagem = null;

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

    /**
     * Cria uma nova publicação do tipo "vaga".
     * 
     * Gerencia o upload da imagem de divulgação, insere o registro base
     * na tabela de publicações e armazena os metadados descritivos da vaga
     * (título, empresa, salário, localização, modalidade) na tabela de vagas.
     */
    public function vacancy()
    {
        Auth::validarAutenticacao();
        $imagem = null;

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
        $publicacao->__set('tipo', 'vaga');

        $publicacaoId = $publicacao->salvar();

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

    /**
     * Atualiza os dados de uma publicação do tipo "post".
     * 
     * Submete o novo texto à moderação automática por inteligência artificial,
     * trata a substituição opcional do arquivo de imagem e atualiza o post
     * no banco de dados com base no identificador fornecido.
     */
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

    /**
     * Atualiza os dados de uma publicação do tipo "vaga".
     * 
     * Executa a atualização da imagem e do texto base na tabela de publicações
     * e propaga as alterações de metadados da oportunidade na tabela relacionada de vagas.
     */
    public function updateVacancy()
    {
        Auth::validarAutenticacao();

        $imagem = '';

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

        $publicacao->__set('id', $_POST['id']);
        $publicacao->__set('usuario_id', $_SESSION['id']);
        $publicacao->__set('conteudo', $_POST['conteudo']);
        $publicacao->__set('imagem', $imagem);

        $publicacao->updatePost();

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

    /**
     * Exclui uma publicação do tipo "post" pertencente ao usuário.
     * 
     * Atribui as chaves de identificação do post e do proprietário logado,
     * remove o registro do banco de dados e redireciona de volta para a timeline.
     */
    public function deletePost()
    {
        Auth::validarAutenticacao();

        $publicacao = Container::getModel('Publicacao');
        $publicacao->__set('id', $_GET['id']);
        $publicacao->__set('usuario_id', $_SESSION['id']);

        $publicacao->excluirPost();

        header('Location: /timeline?delete=success');
    }

    /**
     * Exclui uma publicação de vaga e seus metadados.
     * 
     * Atribui o ID da postagem e valida a posse do registro antes de remover os
     * dados da tabela de publicações e seus dependentes na tabela de candidaturas e vagas.
     */
    public function deleteVacancy()
    {
        Auth::validarAutenticacao();

        $vaga = Container::getModel('Publicacao');
        $vaga->__set('id', $_GET['id']);
        $vaga->__set('usuario_id', $_SESSION['id']);

        $vaga->excluirVaga();

        header('Location: /timeline?delete=success');
    }

    /**
     * Registra uma curtida em uma publicação específica.
     * 
     * Adiciona o vínculo de curtida, emite uma notificação interna para o autor original
     * do post (caso a ação não seja própria) e devolve a contagem atualizada em JSON.
     */
    public function like()
    {
        Auth::validarAutenticacao();

        $publicacaoId = (int) $_POST['publicacao'];
        $usuarioOrigem = $_SESSION['id'];

        $curtida = Container::getModel('Curtida');
        $curtida->curtir($usuarioOrigem, $publicacaoId);

        $total = $curtida->totalCurtidas($publicacaoId);

        $publicacaoModel = Container::getModel('Publicacao');
        $post = $publicacaoModel->getById($publicacaoId);

        if ($post && isset($post['usuario_id']) && $post['usuario_id'] != $usuarioOrigem) {
            \app\model\Notificacao::salvar(
                $post['usuario_id'],
                $usuarioOrigem,
                'like',
                $publicacaoId
            );
        }

        echo json_encode([
            'status' => 'ok',
            'total' => $total['total']
        ]);

        exit;
    }

    /**
     * Remove uma curtida previamente efetuada.
     * 
     * Desvincula a curtida do usuário na publicação selecionada e responde via AJAX
     * com a quantidade total de marcações de gostei atualizadas no elemento correspondente.
     */
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

    /**
     * Adiciona um comentário em uma determinada publicação.
     * 
     * Insere a mensagem textual, dispara uma notificação ao proprietário do post
     * e devolve uma resposta imediata (via AJAX caso a requisição seja assíncrona,
     * ou efetuando redirecionamento para o fluxo padrão de páginas).
     */
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

        $publicacaoModel = Container::getModel('Publicacao');
        $post = $publicacaoModel->getById($publicacaoId);

        if ($post && isset($post['usuario_id']) && $post['usuario_id'] != $usuarioOrigem) {
            \app\model\Notificacao::salvar(
                $post['usuario_id'],
                $usuarioOrigem,
                'comment',
                $publicacaoId
            );
        }

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

        header('Location: /timeline');
    }

    /**
     * Remove um comentário inserido anteriormente pelo autor.
     * 
     * Deleta a linha do comentário assegurando o vínculo com o usuário autenticado.
     * Retorna o totalizador atualizado caso a exclusão ocorra em uma chamada assíncrona.
     */
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

    /**
     * Edita a mensagem de um comentário ativo.
     * 
     * Recebe o ID do comentário, o novo texto e aplica as atualizações no banco de dados,
     * retornando uma confirmação estruturada em JSON para o front-end.
     */
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

    /**
     * Registra o compartilhamento de uma publicação externa.
     * 
     * Clona ou vincula o identificador do post ao feed do usuário que compartilhou
     * e gera uma notificação associada ao publicador original do post.
     */
    public function share()
    {
        Auth::validarAutenticacao();

        $publicacaoId = (int) $_GET['id'];
        $usuarioOrigem = $_SESSION['id'];

        $publicacao = Container::getModel('Publicacao');
        $publicacao->compartilhar($usuarioOrigem, $publicacaoId);

        $post = $publicacao->getById($publicacaoId);

        if ($post && isset($post['usuario_id']) && $post['usuario_id'] != $usuarioOrigem) {
            \app\model\Notificacao::salvar(
                $post['usuario_id'],
                $usuarioOrigem,
                'share',
                $publicacaoId
            );
        }

        header('Location: /timeline?share=sucesso');
    }

    /**
     * Renderiza a listagem de membros ou pessoas recomendadas do sistema.
     * 
     * Recupera todos os perfis ativos e repassa o conjunto de dados para a view
     * dedicada à navegação social da rede.
     */
    public function people()
    {
        Auth::validarAutenticacao();

        $usuario = Container::getModel('Usuario');
        $this->view->usuarios = $usuario->listarUsuarios();

        $this->render('people');
    }

    /**
     * Estabelece uma conexão de amizade/seguimento entre dois usuários.
     * 
     * Assina a relação de seguimento no banco de dados e registra uma notificação
     * no perfil de destino avisando que há um novo seguidor.
     */
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

    /**
     * Remove a relação de seguimento estabelecida.
     * 
     * Executa o desligamento de conexões na base e sinaliza a conclusão
     * da ação estruturando um JSON de sucesso para o cliente.
     */
    public function unfollow()
    {
        Auth::validarAutenticacao();

        $seguidor = Container::getModel('Seguidores');
        $seguidor->deixarDeSeguir($_SESSION['id'], $_POST['usuario_id']);

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    /**
     * Renderiza a view de conexões de um usuário específico.
     * 
     * Consolida e carrega nos parâmetros de visualização os vetores populados de
     * usuários que seguem a conta ativa e de contas que o usuário logado está seguindo.
     */
    public function followers()
    {
        Auth::validarAutenticacao();

        $seguidor = Container::getModel('Seguidores');
        $this->view->seguidores = $seguidor->listarSeguidores($_SESSION['id']);
        $this->view->seguindo = $seguidor->listarSeguindo($_SESSION['id']);

        $this->render('followers');
    }

    /**
     * Renderiza o painel de listagem de vagas abertas.
     * 
     * Consulta todas as oportunidades ativas estruturadas e as disponibiliza
     * para pesquisa e consulta visual dos candidatos interessados.
     */
    public function viewVacancies()
    {
        Auth::validarAutenticacao();

        $vaga = Container::getModel('Publicacao');
        $this->view->vagas = $vaga->listarTodasVagas();

        $this->render('viewVacancies');
    }

    /**
     * Renderiza a view contendo as especificações completas de uma vaga de emprego.
     * 
     * Busca os dados detalhados da vaga com base em seu ID do post correspondente,
     * resgata as informações de perfil curricular do candidato logado e preenche a tela.
     */
    public function vacancyDetails()
    {
        Auth::validarAutenticacao();

        $vaga = Container::getModel('Publicacao');
        $this->view->vaga = $vaga->buscarVagaPorPublicacao($_GET['id']);

        $estudante = Container::getModel('Estudante');
        $this->view->curriculo = $estudante->buscarCurriculo($_SESSION['id']);

        $this->render('vacancyDetails');
    }

    /**
     * Inscreve um candidato em uma vaga disponível.
     * 
     * Verifica a duplicidade para impedir múltiplas candidaturas na mesma vaga pelo mesmo autor.
     * Captura os dados de contato, referências e currículo fornecidos e cria a candidatura.
     */
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

    /**
     * Renderiza a listagem de inscrições em processos seletivos do próprio usuário.
     * 
     * Filtra e consolida o status de todas as candidaturas que o usuário efetuou
     * na plataforma para acompanhamento centralizado.
     */
    public function myApplications()
    {
        Auth::validarAutenticacao();

        $candidatura = Container::getModel('Candidatura');
        $this->view->candidaturas = $candidatura->listarMinhasCandidaturas($_SESSION['id']);

        $this->render('myApplications');
    }

    /**
     * Lista todas as vagas publicadas pelo usuário logado.
     * 
     * Restringe as vagas com base no ID de usuário da sessão ativa e exibe a tela
     * administrativa de controle de anúncios do recrutador ou da empresa.
     */
    public function myVacancies()
    {
        Auth::validarAutenticacao();

        $publicacao = Container::getModel('Publicacao');
        $this->view->vagas = $publicacao->listarMinhasVagas($_SESSION['id']);

        $this->render('myVacancies');
    }

    /**
     * Exibe o quadro de candidatos cadastrados em uma vaga de trabalho anunciada.
     * 
     * Retorna as propriedades da vaga e recupera em uma lista estruturada os currículos
     * e dados de contato de todos os estudantes que submeteram candidatura à oportunidade.
     */
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

    /**
     * Atualiza o status atual do processo seletivo de uma candidatura.
     * 
     * Modifica o status (ex: aprovado, reprovado, em andamento) associado ao candidato
     * e recarrega a página de controle daquela vaga.
     */
    public function updateApplicationStatus()
    {
        Auth::validarAutenticacao();

        $candidatura = Container::getModel('Candidatura');
        $candidatura->alterarStatus($_POST['candidatura_id'], $_POST['status']);

        header('Location: /vacancyCandidates?id=' . $_POST['vaga_id']);
        exit;
    }

    /**
     * Cancela a candidatura de um estudante em uma determinada vaga.
     * 
     * Executa a remoção do registro de candidatura ativo e redireciona o usuário
     * para sua tela geral de candidaturas.
     */
    public function withdrawApplication()
    {
        Auth::validarAutenticacao();

        $candidatura = Container::getModel('Candidatura');
        $candidatura->desistir($_POST['vaga_id'], $_SESSION['id']);

        header('Location: /myApplications');
        exit;
    }

    /**
     * Renderiza e gerencia a interface interna de chat em tempo real.
     * 
     * Constrói a relação de contatos ativos no chat, anexa a última mensagem enviada,
     * contabiliza elementos não lidos e ordena a lista. Caso haja uma conversa selecionada,
     * renderiza o histórico de mensagens e marca as pendentes como lidas.
     */
    public function chat()
    {
        Auth::validarAutenticacao();

        $usuario = Container::getModel('Usuario');
        $this->view->usuarios = $usuario->listarUsuariosChat();

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

    /**
     * Busca ou inicia uma instância de conversa privada entre o usuário logado e outro membro.
     * 
     * Executa a validação da relação de amizade ou chat inicial, cria a sala na base de dados
     * caso não exista e redireciona para a view de chat com o ID da conversa parametrizado.
     */
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

    /**
     * Envia e persiste uma mensagem textual ou de mídia (imagem) na conversa ativa.
     * 
     * Filtra tipos permitidos de mídia, armazena arquivos anexados no diretório do servidor
     * correspondente e registra a nova mensagem na conversa referenciada.
     */
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

    /**
     * Carrega de maneira dinâmica (chamada assíncrona) o conjunto de mensagens de um chat.
     * 
     * Exige o envio do parâmetro da conversa na requisição, busca as novas interações
     * registradas e responde devolvendo a lista completa serializada em JSON.
     */
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

    /**
     * Retorna a lista de usuários habilitados para conversação em formato JSON.
     * 
     * Executa a autenticação e carrega os dados brutos de interações do banco de dados
     * para estruturar canais de chat dinâmicos no front-end.
     */
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

    /**
     * Remove uma mensagem enviada na janela de chat de forma lógica ou física.
     * 
     * Identifica a mensagem a ser eliminada, valida o autor do disparo da ação
     * de exclusão e responde com o status da transação em JSON.
     */
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

    /**
     * Permite a edição do conteúdo textual ou substituição de imagem de uma mensagem de chat.
     * 
     * Gerencia a deleção e o upload de novos anexos, valida o remetente original da mensagem
     * e reescreve os parâmetros textuais modificados devolvendo a estrutura modificada.
     */
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

    /**
     * Retorna em lote as notificações ativas pertencentes ao usuário.
     * 
     * Consulta a coleção de avisos recebidos (novas interações, likes, follows, etc.)
     * associados ao identificador logado e os disponibiliza de forma serializada em JSON.
     */
    public function loadNotifications()
    {
        Auth::validarAutenticacao();

        $notificacaoModel = Container::getModel('Notificacao');
        $notificacoes = $notificacaoModel->listar($_SESSION['id']);

        header('Content-Type: application/json');
        echo json_encode($notificacoes);
        exit;
    }

    /**
     * Altera o status de uma notificação pendente para lida.
     * 
     * Captura o identificador da notificação via POST, atualiza seu estado no
     * banco de dados e sinaliza a conclusão bem-sucedida via JSON.
     */
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