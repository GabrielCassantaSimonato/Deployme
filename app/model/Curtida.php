<?php

namespace app\model;

use MF\Model\Model;

class Curtida extends Model
{
    /**
     * Registra uma curtida em uma publicação.
     * 
     * Insere uma nova linha de relação contendo o identificador do usuário
     * e o identificador da publicação correspondente na tabela de curtidas.
     */
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

    /**
     * Remove uma curtida previamente registrada em uma publicação.
     * 
     * Executa a exclusão da linha de relação entre o usuário e a publicação
     * na tabela de curtidas.
     */
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

    /**
     * Verifica se um usuário específico já curtiu uma determinada publicação.
     * 
     * Consulta a tabela de curtidas e retorna o registro correspondente
     * caso a relação entre usuário e publicação exista na base.
     */
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

    /**
     * Calcula o número total de curtidas acumuladas em uma publicação.
     */
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