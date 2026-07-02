<?php

namespace app\model;

use MF\Model\Model;

class Seguidores extends Model
{
    public function seguir($usuario_id, $seguindo_id)
    {
        $query = "
        INSERT IGNORE INTO seguidores (
            usuario_id,
            seguindo_id
        ) VALUES (
            :usuario_id,
            :seguindo_id
        )
    ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':usuario_id', $usuario_id);
        $stmt->bindValue(':seguindo_id', $seguindo_id);

        return $stmt->execute();
    }

    public function deixarDeSeguir($usuario_id, $seguindo_id)
    {
        $query = "
            DELETE FROM seguidores
            WHERE usuario_id = :usuario_id
              AND seguindo_id = :seguindo_id
        ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':usuario_id', $usuario_id);
        $stmt->bindValue(':seguindo_id', $seguindo_id);

        return $stmt->execute();
    }

    public function estaSeguindo($usuario_id, $seguindo_id)
    {
        $query = "
            SELECT COUNT(*) total
            FROM seguidores
            WHERE usuario_id = :usuario_id
              AND seguindo_id = :seguindo_id
        ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':usuario_id', $usuario_id);
        $stmt->bindValue(':seguindo_id', $seguindo_id);

        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC)['total'];
    }

    public function listarSeguindo($usuario_id)
    {
        $query = "
            SELECT
                u.id,
                u.nome,
                u.foto,
                u.tipo
            FROM seguidores s
            INNER JOIN usuarios u ON u.id = s.seguindo_id
            WHERE s.usuario_id = :usuario_id
            ORDER BY u.nome
        ";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':usuario_id', $usuario_id);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function listarSeguidores($usuario_id)
    {
        $query = "
            SELECT
                u.id,
                u.nome,
                u.foto,
                u.tipo
            FROM seguidores s
            INNER JOIN usuarios u ON u.id = s.usuario_id
            WHERE s.seguindo_id = :usuario_id
            ORDER BY u.nome
        ";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':usuario_id', $usuario_id);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function totalSeguidores($usuario_id)
    {
        $query = "
            SELECT COUNT(*) total
            FROM seguidores
            WHERE seguindo_id = :usuario_id
        ";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':usuario_id', $usuario_id);
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC)['total'];
    }

    public function totalSeguindo($usuario_id)
    {
        $query = "
            SELECT COUNT(*) total
            FROM seguidores
            WHERE usuario_id = :usuario_id
        ";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':usuario_id', $usuario_id);
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC)['total'];
    }
}