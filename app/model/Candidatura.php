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
            INSERT INTO candidaturas
            (
                vaga_id,
                estudante_id,
                email,
                celular,
                github,
                curriculo
            )
            VALUES
            (
                :vaga_id,
                :estudante_id,
                :email,
                :celular,
                :github,
                :curriculo
            )
        ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(
            ':vaga_id',
            $vaga_id
        );

        $stmt->bindValue(
            ':estudante_id',
            $estudante_id
        );

        $stmt->bindValue(
            ':email',
            $email
        );

        $stmt->bindValue(
            ':celular',
            $celular
        );

        $stmt->bindValue(
            ':github',
            $github
        );

        $stmt->bindValue(
            ':curriculo',
            $curriculo
        );

        return $stmt->execute();
    }

    public function jaCandidatou(
        $vaga_id,
        $estudante_id
    ) {

        $query = "
            SELECT COUNT(*) total
            FROM candidaturas
            WHERE vaga_id = :vaga_id
            AND estudante_id = :estudante_id
        ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(
            ':vaga_id',
            $vaga_id
        );

        $stmt->bindValue(
            ':estudante_id',
            $estudante_id
        );

        $stmt->execute();

        return $stmt->fetch(
            \PDO::FETCH_ASSOC
        )['total'];
    }
}