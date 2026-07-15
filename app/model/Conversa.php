<?php

namespace app\model;

use MF\Model\Model;

class Conversa extends Model
{
    /**
     * Busca um canal de conversa ativa entre dois usuários ou cria um novo registro caso não exista.
     * 
     * Realiza uma busca na tabela de conversas testando as duas combinações possíveis de remetente
     * e destinatário. Se encontrar, retorna o identificador do chat; caso contrário, insere
     * uma nova linha na tabela e retorna o ID gerado na inserção.
     */
    public function buscarOuCriarConversa($usuario1, $usuario2)
    {
        $query = "
            SELECT *
            FROM conversas
            WHERE (
                usuario1_id = :usuario1
                AND
                usuario2_id = :usuario2
            ) OR (
                usuario1_id = :usuario2
                AND
                usuario2_id = :usuario1
            )
        ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':usuario1', $usuario1);
        $stmt->bindValue(':usuario2', $usuario2);

        $stmt->execute();

        $conversa = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($conversa) {
            return $conversa['id'];
        }

        $query = "
            INSERT INTO conversas (
                usuario1_id,
                usuario2_id
            ) VALUES (
                :usuario1,
                :usuario2
            )
        ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':usuario1', $usuario1);
        $stmt->bindValue(':usuario2', $usuario2);

        $stmt->execute();

        return $this->db->lastInsertId();
    }

    /**
     * Recupera as informações de registro de uma conversa específica utilizando seu identificador único.
     * 
     * Retorna colunas como o ID da conversa e os identificadores dos dois usuários participantes da conversa.
     */
    public function buscarPorId($id)
    {
        $query = "
            SELECT id, usuario1_id, usuario2_id 
            FROM conversas 
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':id', $id);
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}