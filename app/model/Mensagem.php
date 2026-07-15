<?php

namespace app\model;

use MF\Model\Model;

class Mensagem extends Model
{
    /**
     * Recupera o histórico de mensagens de uma determinada conversa.
     * 
     * Retorna o conteúdo das mensagens, dados de arquivos de imagem anexados
     * e o nome e foto de perfil do remetente, ordenando de forma cronológica.
     */
    public function buscarMensagens($conversa_id)
    {
        $query = "
            SELECT
                m.*,
                u.nome,
                u.foto
            FROM mensagens m
            INNER JOIN usuarios u ON u.id = m.remetente_id
            WHERE m.conversa_id = :conversa_id
            ORDER BY m.created_at ASC
        ";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':conversa_id', $conversa_id);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Salva uma nova mensagem enviada no chat.
     * 
     * Registra o conteúdo textual, a referência da conversa, o remetente
     * e o caminho de imagem anexa (caso exista) na tabela de mensagens.
     */
    public function salvarMensagem($conversa_id, $remetente_id, $mensagem, $imagem = null)
    {
        $query = "
        INSERT INTO mensagens (
            conversa_id,
            remetente_id,
            mensagem,
            imagem
        ) VALUES (
            :conversa_id,
            :remetente_id,
            :mensagem,
            :imagem
        )
    ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(
            ':conversa_id',
            $conversa_id
        );

        $stmt->bindValue(
            ':remetente_id',
            $remetente_id
        );

        $stmt->bindValue(
            ':mensagem',
            $mensagem
        );

        $stmt->bindValue(
            ':imagem',
            $imagem
        );

        return $stmt->execute();
    }

    /**
     * Sinaliza todas as mensagens pendentes recebidas em uma conversa como lidas.
     * 
     * Altera o status do marcador de leitura de mensagens enviadas por outros usuários
     * dentro de uma conversa ativa para o usuário atualmente logado.
     */
    public function marcarComoLida($conversa_id, $usuario_logado)
    {
        $query = "
        UPDATE mensagens
        SET lida = 1
        WHERE conversa_id = :conversa_id
          AND remetente_id <> :usuario_logado
          AND lida = 0
    ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':conversa_id', $conversa_id);
        $stmt->bindValue(':usuario_logado', $usuario_logado);

        return $stmt->execute();
    }

    /**
     * Retorna a quantidade de mensagens ainda não lidas recebidas de um contato.
     * 
     * Contabiliza as mensagens pendentes enviadas pelo contato selecionado direcionadas
     * para o usuário atualmente autenticado na plataforma.
     */
    public function contarMensagensNaoLidas($usuario_logado, $usuario_id)
    {
        $query = "
        SELECT COUNT(*) AS total
        FROM mensagens m
        INNER JOIN conversas c ON c.id = m.conversa_id
        WHERE (
            (c.usuario1_id = :usuario_logado AND c.usuario2_id = :usuario)
            OR
            (c.usuario2_id = :usuario_logado AND c.usuario1_id = :usuario)
        )
        AND m.remetente_id <> :usuario_logado
        AND m.lida = 0
    ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':usuario_logado', $usuario_logado);
        $stmt->bindValue(':usuario', $usuario_id);

        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC)['total'];
    }

    /**
     * Exclui uma mensagem de chat específica enviada pelo usuário.
     * 
     * Remove o registro correspondente no banco de dados assegurando que apenas
     * o remetente original possua permissão para deletar a mensagem.
     */
    public function deleteMessage($id, $usuario)
    {
        $query = "
        DELETE FROM mensagens
        WHERE id = :id
        AND remetente_id = :usuario
    ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(
            ':id',
            $id,
            \PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':usuario',
            $usuario,
            \PDO::PARAM_INT
        );

        return $stmt->execute();
    }

    /**
     * Aplica alterações de texto e gerenciamento de arquivos anexos em uma mensagem.
     * 
     * Permite editar o conteúdo escrito, gerencia a remoção de arquivos físicos antigos
     * de imagem do diretório do servidor e atualiza os campos na tabela de mensagens.
     */
    public function editarMensagem($id, $usuarioId, $mensagem, $novaImagem = null, $excluirImagem = false)
    {
        $query = "
        SELECT imagem
        FROM mensagens
        WHERE id = :id
        AND remetente_id = :usuario
    ";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(":id", $id);
        $stmt->bindValue(":usuario", $usuarioId);
        $stmt->execute();

        $registro = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$registro) {
            return false;
        }

        $imagemAtual = $registro['imagem'];

        if (($excluirImagem || $novaImagem) && !empty($imagemAtual)) {
            $arquivo = __DIR__ .
                "/../../public/uploads/chat/" .
                $imagemAtual;

            if (file_exists($arquivo)) {
                unlink($arquivo);
            }

            $imagemAtual = null;
        }

        if ($novaImagem) {
            $imagemAtual = $novaImagem;
        }

        $query = "
        UPDATE mensagens
        SET
            mensagem = :mensagem,
            imagem = :imagem,
            updated_at = NOW()
        WHERE
            id = :id
        AND
            remetente_id = :usuario
    ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(":mensagem", $mensagem);
        $stmt->bindValue(":imagem", $imagemAtual);
        $stmt->bindValue(":id", $id);
        $stmt->bindValue(":usuario", $usuarioId);

        $stmt->execute();

        return $imagemAtual;
    }
}