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
}