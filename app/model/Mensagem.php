<?php

namespace app\model;

use MF\Model\Model;

class Mensagem extends Model
{
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
    public function editarMensagem($id, $usuarioId, $mensagem, $novaImagem = null, $excluirImagem = false)
    {
        // Busca a mensagem
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

        // -----------------------------
        // Remove imagem antiga
        // -----------------------------
        if (($excluirImagem || $novaImagem) && !empty($imagemAtual)) {

            $arquivo = __DIR__ .
                "/../../public/uploads/chat/" .
                $imagemAtual;

            if (file_exists($arquivo)) {
                unlink($arquivo);
            }

            $imagemAtual = null;
        }

        // -----------------------------
        // Se foi enviada uma nova imagem
        // -----------------------------
        if ($novaImagem) {
            $imagemAtual = $novaImagem;
        }

        // -----------------------------
        // Atualiza a mensagem
        // -----------------------------
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