<?php

namespace app\model;

use MF\Model\Model;

class Comentario extends Model
{
    /**
     * Insere um novo comentário em uma publicação.
     * 
     * Armazena o texto do comentário associando-o ao identificador da postagem
     * e ao usuário autor da ação.
     */
    public function comentar($usuario_id, $publicacao_id, $comentario)
    {
        $query = "
            INSERT INTO comentarios (
                usuario_id,
                publicacao_id,
                comentario
            ) VALUES (
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

    /**
     * Retorna a lista de comentários vinculados a uma publicação específica.
     * 
     * Consolida o conteúdo textual e a data de criação do comentário, trazendo
     * também o nome e a foto de perfil do autor para exibição na timeline.
     */
    public function listarComentarios($publicacao_id)
    {
        $query = "
            SELECT
                c.*,
                u.nome,
                u.foto
            FROM comentarios c
            INNER JOIN usuarios u ON u.id = c.usuario_id
            WHERE c.publicacao_id = :publicacao_id
            ORDER BY c.created_at ASC
        ";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':publicacao_id', $publicacao_id);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Calcula a quantidade total de comentários ativos em uma postagem.
     */
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

    /**
     * Exclui permanentemente um comentário específico do banco de dados.
     * 
     * Realiza a remoção validando se o autor da requisição é de fato o proprietário do comentário.
     */
    public function delete($id, $usuario_id)
    {
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

    /**
     * Atualiza o conteúdo textual de um comentário existente.
     * 
     * Altera o campo de texto certificando que a operação seja efetuada pelo autor original.
     */
    public function editar($id, $usuario_id, $comentario)
    {
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