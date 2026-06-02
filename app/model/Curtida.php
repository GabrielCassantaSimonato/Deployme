<?php

namespace app\model;

use MF\Model\Model;

class Curtida extends Model
{
    public function curtir($usuario_id, $publicacao_id)
    {
        $query = "
            INSERT INTO curtidas
            (usuario_id, publicacao_id)
            VALUES
            (:usuario_id, :publicacao_id)
        ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':usuario_id', $usuario_id);
        $stmt->bindValue(':publicacao_id', $publicacao_id);

        return $stmt->execute();
    }

    public function descurtir($usuario_id, $publicacao_id)
    {
        $query = "
            DELETE FROM curtidas
            WHERE usuario_id = :usuario_id
            AND publicacao_id = :publicacao_id
        ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':usuario_id', $usuario_id);
        $stmt->bindValue(':publicacao_id', $publicacao_id);

        return $stmt->execute();
    }

    public function usuarioCurtiu($usuario_id, $publicacao_id)
    {
        $query = "
            SELECT id
            FROM curtidas
            WHERE usuario_id = :usuario_id
            AND publicacao_id = :publicacao_id
        ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':usuario_id', $usuario_id);
        $stmt->bindValue(':publicacao_id', $publicacao_id);

        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function totalCurtidas($publicacao_id)
    {
        $query = "
            SELECT COUNT(*) as total
            FROM curtidas
            WHERE publicacao_id = :publicacao_id
        ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':publicacao_id', $publicacao_id);

        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}