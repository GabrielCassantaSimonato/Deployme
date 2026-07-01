<?php

namespace app\model;

use MF\Model\Model;

class Candidatura extends Model
{
    public function salvar(
        $vaga_id,
        $estudante_id,
        $email,
        $celular,
        $github,
        $curriculo
    ) {
        $query = "
            INSERT INTO candidaturas (
                vaga_id,
                estudante_id,
                email,
                celular,
                github,
                curriculo
            ) VALUES (
                :vaga_id,
                :estudante_id,
                :email,
                :celular,
                :github,
                :curriculo
            )
        ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':vaga_id', $vaga_id);
        $stmt->bindValue(':estudante_id', $estudante_id);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':celular', $celular);
        $stmt->bindValue(':github', $github);
        $stmt->bindValue(':curriculo', $curriculo);

        return $stmt->execute();
    }

    public function jaCandidatou($vaga_id, $estudante_id)
    {
        $query = "
            SELECT COUNT(*) total
            FROM candidaturas
            WHERE vaga_id = :vaga_id
            AND estudante_id = :estudante_id
        ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':vaga_id', $vaga_id);
        $stmt->bindValue(':estudante_id', $estudante_id);

        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC)['total'];
    }

    public function buscarPublicacaoDaVaga($vaga_id)
    {
        $query = "
            SELECT publicacao_id
            FROM vagas
            WHERE id = :vaga_id
        ";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':vaga_id', $vaga_id);
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function listarMinhasCandidaturas($estudante_id)
    {
        $query = "
            SELECT
                c.id,
                c.created_at,
                c.status,
                v.id AS vaga_id,
                v.publicacao_id,
                v.titulo,
                v.localizacao,
                v.modalidade,
                r.empresa
            FROM candidaturas c
            INNER JOIN vagas v ON v.id = c.vaga_id
            INNER JOIN publicacoes p ON p.id = v.publicacao_id
            INNER JOIN usuarios u ON u.id = p.usuario_id
            INNER JOIN recrutadores r ON r.usuario_id = u.id
            WHERE c.estudante_id = :estudante_id
            ORDER BY c.created_at DESC
        ";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':estudante_id', $estudante_id);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function listarCandidatos($vaga_id)
    {
        $query = "
            SELECT
                c.*,
                u.nome,
                u.foto,
                e.curriculo AS curriculo_estudante
            FROM candidaturas c
            INNER JOIN usuarios u ON u.id = c.estudante_id
            INNER JOIN estudantes e ON e.usuario_id = u.id
            WHERE c.vaga_id = :vaga_id
            ORDER BY c.created_at DESC
        ";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':vaga_id', $vaga_id);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function alterarStatus($candidatura_id, $status)
    {
        $query = "
            UPDATE candidaturas 
            SET status = :status 
            WHERE id = :candidatura_id
        ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':candidatura_id', $candidatura_id);

        return $stmt->execute();
    }

    public function desistir($vaga_id, $estudante_id)
    {
        $query = "
            DELETE FROM candidaturas
            WHERE vaga_id = :vaga_id
            AND estudante_id = :estudante_id
        ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':vaga_id', $vaga_id);
        $stmt->bindValue(':estudante_id', $estudante_id);

        return $stmt->execute();
    }
}