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
            INSERT INTO publicacoes (
                usuario_id,
                conteudo,
                imagem,
                tipo
            ) VALUES (
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
            INSERT INTO vagas (
                publicacao_id,
                titulo,
                empresa,
                localizacao,
                modalidade,
                salario,
                descricao
            ) VALUES (
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

    public function listarPublicacoes($usuarioLogado)
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
                v.salario,
                /* Dados do Post Original */
                po.id AS post_original_id,
                po.tipo AS post_original_tipo,
                po.conteudo AS post_original_conteudo,
                po.imagem AS post_original_imagem,
                /* Autor Original */
                uo.nome AS autor_original_nome,
                uo.foto AS autor_original_foto,
                /* Dados da Vaga Original */
                vo.titulo AS post_original_titulo,
                vo.empresa AS post_original_empresa,
                vo.localizacao AS post_original_localizacao,
                vo.modalidade AS post_original_modalidade,
                vo.salario AS post_original_salario
            FROM publicacoes p
            INNER JOIN usuarios u ON u.id = p.usuario_id
            LEFT JOIN estudantes e ON e.usuario_id = u.id
            LEFT JOIN recrutadores r ON r.usuario_id = u.id
            LEFT JOIN vagas v ON v.publicacao_id = p.id
            /* Compartilhamentos */
            LEFT JOIN publicacoes po ON po.id = p.publicacao_original_id
            LEFT JOIN usuarios uo ON uo.id = po.usuario_id
            LEFT JOIN vagas vo ON vo.publicacao_id = po.id
            /* Pessoas seguidas */
            LEFT JOIN seguidores s ON s.seguindo_id = p.usuario_id AND s.usuario_id = :usuarioLogado
            WHERE p.usuario_id = :usuarioLogado
               OR s.usuario_id IS NOT NULL
            ORDER BY p.created_at DESC
        ";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':usuarioLogado', $usuarioLogado);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function listarPublicacoesAdmin()
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
            v.salario,

            /* Dados do Post Original */
            po.id AS post_original_id,
            po.tipo AS post_original_tipo,
            po.conteudo AS post_original_conteudo,
            po.imagem AS post_original_imagem,

            /* Autor Original */
            uo.nome AS autor_original_nome,
            uo.foto AS autor_original_foto,

            /* Dados da Vaga Original */
            vo.titulo AS post_original_titulo,
            vo.empresa AS post_original_empresa,
            vo.localizacao AS post_original_localizacao,
            vo.modalidade AS post_original_modalidade,
            vo.salario AS post_original_salario

        FROM publicacoes p

        INNER JOIN usuarios u
            ON u.id = p.usuario_id

        LEFT JOIN estudantes e
            ON e.usuario_id = u.id

        LEFT JOIN recrutadores r
            ON r.usuario_id = u.id

        LEFT JOIN vagas v
            ON v.publicacao_id = p.id

        /* Compartilhamentos */
        LEFT JOIN publicacoes po
            ON po.id = p.publicacao_original_id

        LEFT JOIN usuarios uo
            ON uo.id = po.usuario_id

        LEFT JOIN vagas vo
            ON vo.publicacao_id = po.id

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
                e.cidade,
                e.uf,
                v.titulo,
                v.empresa,
                v.localizacao,
                v.modalidade,
                v.salario,
                -- Dados do post original (para compartilhamentos)
                p_orig.conteudo     AS post_original_conteudo,
                p_orig.imagem       AS post_original_imagem,
                p_orig.tipo         AS post_original_tipo,
                u_orig.nome         AS autor_original_nome,
                u_orig.foto         AS autor_original_foto,
                v_orig.titulo       AS post_original_titulo,
                v_orig.empresa      AS post_original_empresa,
                v_orig.localizacao  AS post_original_localizacao,
                v_orig.modalidade   AS post_original_modalidade,
                v_orig.salario      AS post_original_salario
            FROM publicacoes p
            INNER JOIN usuarios u ON u.id = p.usuario_id
            LEFT JOIN estudantes e ON e.usuario_id = u.id
            LEFT JOIN vagas v ON v.publicacao_id = p.id
            -- JOINs do post original
            LEFT JOIN publicacoes p_orig ON p_orig.id = p.publicacao_original_id
            LEFT JOIN usuarios u_orig ON u_orig.id = p_orig.usuario_id
            LEFT JOIN vagas v_orig ON v_orig.publicacao_id = p_orig.id
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
            SET conteudo = :conteudo
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
            WHERE publicacao_id = :publicacao_id
        ";

        $stmt = $this->db->prepare($query);

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
        try {

            $this->db->beginTransaction();

            // Compartilhamentos
            $this->excluirCompartilhamentos();

            // Curtidas
            $query = "
            DELETE FROM curtidas
            WHERE publicacao_id = :id
        ";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':id', $this->__get('id'));
            $stmt->execute();

            // Comentários
            $query = "
            DELETE FROM comentarios
            WHERE publicacao_id = :id
        ";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':id', $this->__get('id'));
            $stmt->execute();

            // Notificações
            $query = "
            DELETE FROM notificacoes
            WHERE referencia_id = :id
        ";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':id', $this->__get('id'));
            $stmt->execute();

            // Publicação
            $query = "
            DELETE FROM publicacoes
            WHERE id = :id
              AND usuario_id = :usuario_id
        ";

            $stmt = $this->db->prepare($query);

            $stmt->bindValue(':id', $this->__get('id'));
            $stmt->bindValue(':usuario_id', $this->__get('usuario_id'));

            $stmt->execute();

            $this->db->commit();

            return true;

        } catch (\Exception $e) {

            $this->db->rollBack();

            throw $e;

        }
    }

    public function excluirVaga()
    {
        try {

            $this->db->beginTransaction();

            // Compartilhamentos
            $this->excluirCompartilhamentos();

            // Descobre a vaga
            $query = "
            SELECT id
            FROM vagas
            WHERE publicacao_id = :id
        ";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':id', $this->__get('id'));
            $stmt->execute();

            $vaga = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($vaga) {

                // Candidaturas
                $query = "
                DELETE FROM candidaturas
                WHERE vaga_id = :vaga_id
            ";

                $stmt = $this->db->prepare($query);
                $stmt->bindValue(':vaga_id', $vaga['id']);
                $stmt->execute();

                // Vaga
                $query = "
                DELETE FROM vagas
                WHERE id = :vaga_id
            ";

                $stmt = $this->db->prepare($query);
                $stmt->bindValue(':vaga_id', $vaga['id']);
                $stmt->execute();
            }

            // Curtidas
            $query = "
            DELETE FROM curtidas
            WHERE publicacao_id = :id
        ";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':id', $this->__get('id'));
            $stmt->execute();

            // Comentários
            $query = "
            DELETE FROM comentarios
            WHERE publicacao_id = :id
        ";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':id', $this->__get('id'));
            $stmt->execute();

            // Notificações
            $query = "
            DELETE FROM notificacoes
            WHERE referencia_id = :id
        ";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':id', $this->__get('id'));
            $stmt->execute();

            // Publicação
            $query = "
            DELETE FROM publicacoes
            WHERE id = :id
              AND usuario_id = :usuario_id
        ";

            $stmt = $this->db->prepare($query);

            $stmt->bindValue(':id', $this->__get('id'));
            $stmt->bindValue(':usuario_id', $this->__get('usuario_id'));

            $stmt->execute();

            $this->db->commit();

            return true;

        } catch (\Exception $e) {

            $this->db->rollBack();

            throw $e;

        }
    }
    public function compartilhar($usuario_id, $publicacao_original_id)
    {
        $query = "
            INSERT INTO publicacoes (
                usuario_id,
                publicacao_original_id,
                created_at
            ) VALUES (
                :usuario_id,
                :publicacao_original_id,
                NOW()
            )
        ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':usuario_id', $usuario_id);
        $stmt->bindValue(':publicacao_original_id', $publicacao_original_id);

        return $stmt->execute();
    }
    public function excluirCompartilhamentos()
    {
        $query = "
        DELETE FROM publicacoes
        WHERE publicacao_original_id = :id
    ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':id', $this->__get('id'));

        return $stmt->execute();
    }

    public function listarTodasVagas()
    {
        $query = "
            SELECT
                v.*,
                u.nome,
                u.foto,
                r.empresa
            FROM vagas v
            INNER JOIN publicacoes p ON p.id = v.publicacao_id
            INNER JOIN usuarios u ON u.id = p.usuario_id
            INNER JOIN recrutadores r ON r.usuario_id = u.id
            ORDER BY v.id DESC
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function buscarVagaPorPublicacao($publicacao_id)
    {
        $query = "
            SELECT
                v.*,
                u.nome,
                u.foto,
                r.empresa
            FROM vagas v
            INNER JOIN publicacoes p ON p.id = v.publicacao_id
            INNER JOIN usuarios u ON u.id = p.usuario_id
            INNER JOIN recrutadores r ON r.usuario_id = u.id
            WHERE v.publicacao_id = :publicacao_id
        ";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':publicacao_id', $publicacao_id);
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function listarMinhasVagas($usuario_id)
    {
        $query = "
            SELECT
                v.id,
                v.publicacao_id,
                v.titulo,
                v.localizacao,
                v.modalidade,
                v.salario,
                v.created_at,
                COUNT(c.id) AS total_candidatos
            FROM vagas v
            INNER JOIN publicacoes p ON p.id = v.publicacao_id
            LEFT JOIN candidaturas c ON c.vaga_id = v.id
            WHERE p.usuario_id = :usuario_id
            GROUP BY v.id
            ORDER BY v.created_at DESC
        ";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':usuario_id', $usuario_id);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function buscarVagaPorId($vaga_id)
    {
        $query = "
            SELECT v.*
            FROM vagas v
            WHERE v.id = :vaga_id
        ";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':vaga_id', $vaga_id);
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    public function getById($id)
    {
        $query = "SELECT usuario_id FROM publicacoes WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    public function excluirPostAdmin()
    {
        try {

            $this->db->beginTransaction();

            // ===========================================
            // EXCLUI COMPARTILHAMENTOS DA PUBLICAÇÃO
            // ===========================================

            $query = "
            DELETE FROM publicacoes
            WHERE publicacao_original_id = :id
        ";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':id', $this->__get('id'));
            $stmt->execute();

            // ===========================================
            // VERIFICA SE É UMA VAGA
            // ===========================================

            $query = "
            SELECT id
            FROM vagas
            WHERE publicacao_id = :id
        ";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':id', $this->__get('id'));
            $stmt->execute();

            $vaga = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($vaga) {

                // =======================================
                // EXCLUI CANDIDATURAS
                // =======================================

                $query = "
                DELETE FROM candidaturas
                WHERE vaga_id = :vaga_id
            ";

                $stmt = $this->db->prepare($query);
                $stmt->bindValue(':vaga_id', $vaga['id']);
                $stmt->execute();

                // =======================================
                // EXCLUI VAGA
                // =======================================

                $query = "
                DELETE FROM vagas
                WHERE id = :vaga_id
            ";

                $stmt = $this->db->prepare($query);
                $stmt->bindValue(':vaga_id', $vaga['id']);
                $stmt->execute();
            }

            // ===========================================
            // CURTIDAS
            // ===========================================

            $query = "
            DELETE FROM curtidas
            WHERE publicacao_id = :id
        ";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':id', $this->__get('id'));
            $stmt->execute();

            // ===========================================
            // COMENTÁRIOS
            // ===========================================

            $query = "
            DELETE FROM comentarios
            WHERE publicacao_id = :id
        ";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':id', $this->__get('id'));
            $stmt->execute();

            // ===========================================
            // NOTIFICAÇÕES
            // ===========================================

            $query = "
            DELETE FROM notificacoes
            WHERE referencia_id = :id
        ";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':id', $this->__get('id'));
            $stmt->execute();

            // ===========================================
            // PUBLICAÇÃO
            // ===========================================

            $query = "
            DELETE FROM publicacoes
            WHERE id = :id
        ";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':id', $this->__get('id'));
            $stmt->execute();

            $this->db->commit();

            return true;

        } catch (\Exception $e) {

            $this->db->rollBack();

            throw $e;
        }
    }
}