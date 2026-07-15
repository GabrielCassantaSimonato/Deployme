<?php

namespace app\model;

use MF\Model\Model;

class Candidatura extends Model
{
    /**
     * Registra uma nova candidatura de um estudante a uma vaga.
     * 
     * Armazena as informações básicas de contato (e-mail e celular), link do GitHub
     * e o arquivo do currículo na tabela candidaturas, associando-os aos identificadores da vaga e do estudante.
     */
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

    /**
     * Verifica se o estudante já possui uma candidatura ativa para uma vaga específica.
     * 
     * Realiza a contagem de registros correspondentes ao par vaga e estudante
     * e retorna a quantidade encontrada para impedir duplicidade de inscrições.
     */
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

    /**
     * Recupera o identificador da publicação associada a uma determinada vaga.
     */
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

    /**
     * Retorna a lista de todas as candidaturas efetuadas por um estudante específico.
     * 
     * Consolida dados estruturados incluindo a data de envio da candidatura, o status atual,
     * as informações da vaga e o nome da empresa recrutadora associada.
     */
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

    /**
     * Lista todos os candidatos inscritos em uma vaga de trabalho anunciada.
     * 
     * Retorna os dados básicos fornecidos na inscrição juntamente com o nome,
     * foto de perfil e o currículo do estudante cadastrado na tabela de perfil.
     */
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

    /**
     * Altera o status atual (ex: em análise, aprovado, reprovado) de uma candidatura.
     */
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

    /**
     * Exclui o registro de candidatura de um estudante em uma vaga específica.
     * 
     * Utilizado quando o candidato opta por desistir da candidatura no processo seletivo.
     */
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