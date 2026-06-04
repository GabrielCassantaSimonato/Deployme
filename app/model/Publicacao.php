<?php

namespace app\model;

use MF\Model\Model;

class Publicacao extends Model
{
    private $id;
    private $usuario_id;
    private $conteudo;
    private $imagem;
    private $tipo;

    public function __get($atributo)
    {
        return $this->$atributo;
    }

    public function __set($atributo, $valor)
    {
        $this->$atributo = $valor;
    }

    // SALVAR PUBLICAÇÃO
    public function salvar()
    {

        $query = "

            INSERT INTO publicacoes
            (
                usuario_id,
                conteudo,
                imagem,
                tipo
            )

            VALUES
            (
                :usuario_id,
                :conteudo,
                :imagem,
                :tipo
            )

        ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':usuario_id', $this->__get('usuario_id'));
        $stmt->bindValue(':conteudo', $this->__get('conteudo'));
        $stmt->bindValue(':imagem', $this->__get('imagem'));
        $stmt->bindValue(':tipo', $this->__get('tipo'));

        $stmt->execute();

        return $this->db->lastInsertId();

    }

    // SALVAR VAGA
    public function salvarVaga($dados)
    {

        $query = "

            INSERT INTO vagas
            (
                publicacao_id,
                titulo,
                empresa,
                localizacao,
                modalidade,
                salario,
                descricao
            )

            VALUES
            (
                :publicacao_id,
                :titulo,
                :empresa,
                :localizacao,
                :modalidade,
                :salario,
                :descricao
            )

        ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':publicacao_id', $dados['publicacao_id']);
        $stmt->bindValue(':titulo', $dados['titulo']);
        $stmt->bindValue(':empresa', $dados['empresa']);
        $stmt->bindValue(':localizacao', $dados['localizacao']);
        $stmt->bindValue(':modalidade', $dados['modalidade']);
        $stmt->bindValue(':salario', $dados['salario']);
        $stmt->bindValue(':descricao', $dados['descricao']);

        $stmt->execute();

    }

    public function listarPublicacoes()
    {
        $query = "
        SELECT
            p.*,
            u.nome,
            u.foto,
            e.cidade,
            e.uf,
            COALESCE(v.empresa, r.empresa) AS empresa,
            v.titulo,
            v.localizacao,
            v.modalidade,
            v.salario
        FROM publicacoes p
        INNER JOIN usuarios u
            ON u.id = p.usuario_id
        LEFT JOIN estudantes e
            ON e.usuario_id = u.id
        LEFT JOIN recrutadores r
            ON r.usuario_id = u.id
        LEFT JOIN vagas v
            ON v.publicacao_id = p.id
        ORDER BY p.created_at DESC
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getPublicacoesUsuario()
    {
        $query = "
        SELECT
            p.*,
            u.nome,
            u.foto,
            v.titulo,
            v.empresa,
            v.localizacao,
            v.modalidade,
            v.salario
        FROM publicacoes p
        INNER JOIN usuarios u 
            ON u.id = p.usuario_id
        LEFT JOIN vagas v 
            ON v.publicacao_id = p.id
        WHERE p.usuario_id = :usuario_id
        ORDER BY p.created_at DESC
    ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':usuario_id', $this->__get('usuario_id'));

        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function updatePost()
    {
        $query = "
        UPDATE publicacoes
        SET
            conteudo = :conteudo
    ";

        if ($this->__get('imagem') != '') {
            $query .= ", imagem = :imagem ";
        }

        $query .= "
        WHERE id = :id
        AND usuario_id = :usuario_id
    ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':conteudo', $this->__get('conteudo'));
        $stmt->bindValue(':id', $this->__get('id'));
        $stmt->bindValue(':usuario_id', $this->__get('usuario_id'));

        if ($this->__get('imagem') != '') {
            $stmt->bindValue(':imagem', $this->__get('imagem'));
        }

        return $stmt->execute();
    }
    public function updateVacancy($dados)
    {
        $query = "
        UPDATE vagas
        SET
            titulo = :titulo,
            empresa = :empresa,
            localizacao = :localizacao,
            modalidade = :modalidade,
            salario = :salario,
            descricao = :descricao
        WHERE 
            publicacao_id = :publicacao_id
    ";

        $stmt = $this->db->prepare($query);

        // Mapeamos os valores diretamente do array $dados que o Controller enviou
        $stmt->bindValue(':publicacao_id', $dados['publicacao_id']);
        $stmt->bindValue(':titulo', $dados['titulo']);
        $stmt->bindValue(':empresa', $dados['empresa']);
        $stmt->bindValue(':localizacao', $dados['localizacao']);
        $stmt->bindValue(':modalidade', $dados['modalidade']);
        $stmt->bindValue(':salario', $dados['salario']);
        $stmt->bindValue(':descricao', $dados['descricao']);

        return $stmt->execute();
    }

    public function excluirPost()
    {
        $query = "
        DELETE FROM publicacoes
        WHERE id = :id
        AND usuario_id = :usuario_id
    ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':id', $this->__get('id'));
        $stmt->bindValue(':usuario_id', $this->__get('usuario_id'));

        return $stmt->execute();
    }

    public function excluirVaga()
    {
        // PRIMEIRO EXCLUI A VAGA
        $query = "
        DELETE FROM vagas
        WHERE publicacao_id = :publicacao_id
    ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':publicacao_id', $this->__get('id'));

        $stmt->execute();

        // DEPOIS EXCLUI A PUBLICAÇÃO
        $query2 = "
        DELETE FROM publicacoes
        WHERE id = :id
        AND usuario_id = :usuario_id
    ";

        $stmt2 = $this->db->prepare($query2);

        $stmt2->bindValue(':id', $this->__get('id'));
        $stmt2->bindValue(':usuario_id', $this->__get('usuario_id'));

        return $stmt2->execute();
    }
}