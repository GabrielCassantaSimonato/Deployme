<?php

namespace app\model;

use MF\Model\Model;
use MF\model\Container;
use PDO;

class Notificacao extends Model
{

    public function criar(
        $usuarioDestino,
        $usuarioOrigem,
        $tipo,
        $referenciaId = null
    ) {

        $query = "

            INSERT INTO notificacoes
            (

                usuario_destino,
                usuario_origem,
                tipo,
                referencia_id

            )

            VALUES
            (

                :destino,
                :origem,
                :tipo,
                :referencia

            )

        ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':destino', $usuarioDestino);
        $stmt->bindValue(':origem', $usuarioOrigem);
        $stmt->bindValue(':tipo', $tipo);
        $stmt->bindValue(':referencia', $referenciaId);

        return $stmt->execute();

    }

    public function listar($usuarioId)
    {
        $sql = "
    SELECT
        n.id,
        n.tipo,
        n.lida,
        DATE_FORMAT(n.created_at, '%d/%m/%Y %H:%i') as created_at,
        u.id as usuario_id,
        u.nome,
        u.foto,
        -- Mapeia cada tipo para a sua frase correspondente
        CASE 
            WHEN n.tipo = 'follow' THEN CONCAT('<strong>', u.nome, '</strong> começou a seguir você.')
            WHEN n.tipo = 'like' THEN CONCAT('<strong>', u.nome, '</strong> curtiu sua publicação.')
            WHEN n.tipo = 'comment' THEN CONCAT('<strong>', u.nome, '</strong> comentou na sua publicação.')
            WHEN n.tipo = 'share' THEN CONCAT('<strong>', u.nome, '</strong> compartilhou sua publicação.')
            ELSE 'Nova interação no seu perfil.'
        END as mensagem

    FROM notificacoes n

    LEFT JOIN usuarios u
        ON u.id = n.usuario_origem

    WHERE n.usuario_destino = :usuario

    ORDER BY n.created_at DESC

    LIMIT 20
";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":usuario", $usuarioId);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function contarNaoLidas($usuario)
    {

        $query = "

            SELECT COUNT(*) AS total

            FROM notificacoes

            WHERE usuario_destino = :usuario

            AND lida = 0

        ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':usuario', $usuario);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);

    }

    public function marcarComoLida($id)
    {

        $query = "

            UPDATE notificacoes

            SET lida = 1

            WHERE id = :id

        ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':id', $id);

        return $stmt->execute();

    }

    public static function salvar($usuarioDestino, $usuarioOrigem, $tipo, $referenciaId = null)
    {
        // Usa o Container do próprio framework para pegar a instância do model e chamar o criar
        $notificacao = Container::getModel('Notificacao');
        return $notificacao->criar($usuarioDestino, $usuarioOrigem, $tipo, $referenciaId);
    }


    public function excluir($id)
    {

        $query = "

            DELETE FROM notificacoes

            WHERE id = :id

        ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':id', $id);

        return $stmt->execute();

    }



}