<?php

namespace app\model;

use MF\Model\Model;

class Seguidores extends Model
{
    /**
     * Cria uma nova relação de seguimento entre dois usuários.
     * 
     * Insere o vínculo na tabela de seguidores utilizando o comando IGNORE
     * para evitar duplicações caso a relação já exista no banco de dados.
     */
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

    /**
     * Remove a relação de seguimento existente entre dois usuários.
     */
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

    /**
     * Verifica se existe um vínculo de seguimento ativo entre o usuário de origem e o de destino.
     * 
     * Retorna a quantidade de registros encontrados (0 ou 1) para determinar o status da relação.
     */
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

    /**
     * Retorna a lista de todas as contas que o usuário selecionado está seguindo.
     * 
     * Conecta à tabela de usuários para trazer informações como nome, foto de perfil
     * e tipo de conta de cada perfil seguido, ordenando os resultados alfabeticamente.
     */
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

    /**
     * Retorna a lista de todos os usuários que seguem a conta informada.
     * 
     * Recupera dados como nome, foto de perfil e tipo de acesso dos seguidores associados
     * ao identificador fornecido, ordenando-os em ordem alfabética.
     */
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

    /**
     * Calcula o número total de seguidores (usuários que acompanham a conta) de um perfil.
     */
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

    /**
     * Calcula o total de perfis que o usuário selecionado está acompanhando no sistema.
     */
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