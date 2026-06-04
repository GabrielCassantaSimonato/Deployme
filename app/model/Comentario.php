<?php

namespace app\model;

use MF\Model\Model;

class Comentario extends Model
{
    public function comentar(
        $usuario_id,
        $publicacao_id,
        $comentario
    ) {
        $query = "
            INSERT INTO comentarios
            (
                usuario_id,
                publicacao_id,
                comentario
            )
            VALUES
            (
                :usuario_id,
                :publicacao_id,
                :comentario
            )
        ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':usuario_id', $usuario_id);
        $stmt->bindValue(':publicacao_id', $publicacao_id);
        $stmt->bindValue(':comentario', $comentario);

        return $stmt->execute();
    }

    public function listarComentarios($publicacao_id)
    {
        $query = "
            SELECT
                c.*,
                u.nome,
                u.foto
            FROM comentarios c
            INNER JOIN usuarios u
                ON u.id = c.usuario_id
            WHERE c.publicacao_id = :publicacao_id
            ORDER BY c.created_at ASC
        ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':publicacao_id', $publicacao_id);

        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function totalComentarios($publicacao_id)
    {
        $query = "
            SELECT COUNT(*) AS total
            FROM comentarios
            WHERE publicacao_id = :publicacao_id
        ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':publicacao_id', $publicacao_id);

        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function delete(
        $id,
        $usuario_id
    ) {
        $query = "
        DELETE FROM comentarios
        WHERE id = :id
        AND usuario_id = :usuario_id
    ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':id', $id);
        $stmt->bindValue(':usuario_id', $usuario_id);

        return $stmt->execute();
    }

    public function editar(
        $id,
        $usuario_id,
        $comentario
    ) {

        $query = "
        UPDATE comentarios
        SET comentario = :comentario
        WHERE id = :id
        AND usuario_id = :usuario_id
    ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':comentario', $comentario);
        $stmt->bindValue(':id', $id);
        $stmt->bindValue(':usuario_id', $usuario_id);

        return $stmt->execute();
    }
}