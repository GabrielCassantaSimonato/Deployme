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

    /**
     * Recupera de forma dinâmica o valor de um atributo privado da classe.
     */
    public function __get($atributo)
    {
        return $this->$atributo;
    }

    /**
     * Atribui dinamicamente um valor a um atributo privado da classe.
     */
    public function __set($atributo, $valor)
    {
        $this->$atributo = $valor;
    }

    /**
     * Registra uma nova publicação geral no sistema.
     * 
     * Insere o conteúdo textual, referência de imagem opcional e o tipo de post
     * na tabela publicacoes, retornando o ID do registro inserido.
     */
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

    /**
     * Salva as especificações de uma vaga de trabalho na tabela correspondente.
     */
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

    /**
     * Lista as publicações para o feed do usuário logado.
     * 
     * Traz as publicações próprias e das contas que o usuário segue, mapeando dados
     * detalhados de vagas e informações completas de compartilhamentos anteriores.
     */
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
                po.id AS post_original_id,
                po.tipo AS post_original_tipo,
                po.conteudo AS post_original_conteudo,
                po.imagem AS post_original_imagem,
                uo.nome AS autor_original_nome,
                uo.foto AS autor_original_foto,
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
            LEFT JOIN publicacoes po ON po.id = p.publicacao_original_id
            LEFT JOIN usuarios uo ON uo.id = po.usuario_id
            LEFT JOIN vagas vo ON vo.publicacao_id = po.id
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

    /**
     * Lista todas as publicações do sistema para a moderação administrativa.
     * 
     * Retorna o conjunto completo de posts e seus dados correlacionados de vagas
     * e compartilhamentos sem aplicar filtros de relacionamento social ou amizades.
     */
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
            po.id AS post_original_id,
            po.tipo AS post_original_tipo,
            po.conteudo AS post_original_conteudo,
            po.imagem AS post_original_imagem,
            uo.nome AS autor_original_nome,
            uo.foto AS autor_original_foto,
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

    /**
     * Retorna a lista de publicações geradas por um perfil de usuário específico.
     * 
     * Carrega as postagens pertencentes à conta consultada para renderização
     * do feed de perfil exclusivo.
     */
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

    /**
     * Altera o conteúdo de texto e imagem de uma publicação existente.
     * 
     * Realiza a atualização garantindo o vínculo de propriedade da postagem.
     */
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

    /**
     * Atualiza os detalhes descritivos de uma vaga de emprego ativa.
     */
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

    /**
     * Exclui em lote uma publicação base do tipo post do banco de dados.
     * 
     * Executa o processo dentro de uma transação SQL para assegurar a deleção em cascata
     * de compartilhamentos, curtidas, comentários, notificações associadas e da postagem principal.
     */
    public function excluirPost()
    {
        try {
            $this->db->beginTransaction();

            $this->excluirCompartilhamentos();

            $query = "
            DELETE FROM curtidas
            WHERE publicacao_id = :id
        ";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':id', $this->__get('id'));
            $stmt->execute();

            $query = "
            DELETE FROM comentarios
            WHERE publicacao_id = :id
        ";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':id', $this->__get('id'));
            $stmt->execute();

            $query = "
            DELETE FROM notificacoes
            WHERE referencia_id = :id
        ";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':id', $this->__get('id'));
            $stmt->execute();

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

    /**
     * Remove uma publicação de vaga e seus metadados de forma transacional.
     * 
     * Realiza a remoção em cascata de compartilhamentos, candidaturas correlacionadas,
     * dados de vagas, comentários, curtidas e notificações, concluindo com a postagem principal.
     */
    public function excluirVaga()
    {
        try {
            $this->db->beginTransaction();

            $this->excluirCompartilhamentos();

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
                $query = "
                DELETE FROM candidaturas
                WHERE vaga_id = :vaga_id
            ";

                $stmt = $this->db->prepare($query);
                $stmt->bindValue(':vaga_id', $vaga['id']);
                $stmt->execute();

                $query = "
                DELETE FROM vagas
                WHERE id = :vaga_id
            ";

                $stmt = $this->db->prepare($query);
                $stmt->bindValue(':vaga_id', $vaga['id']);
                $stmt->execute();
            }

            $query = "
            DELETE FROM curtidas
            WHERE publicacao_id = :id
        ";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':id', $this->__get('id'));
            $stmt->execute();

            $query = "
            DELETE FROM comentarios
            WHERE publicacao_id = :id
        ";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':id', $this->__get('id'));
            $stmt->execute();

            $query = "
            DELETE FROM notificacoes
            WHERE referencia_id = :id
        ";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':id', $this->__get('id'));
            $stmt->execute();

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

    /**
     * Registra o compartilhamento de uma publicação efetuado por um usuário.
     */
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

    /**
     * Remove todos os compartilhamentos vinculados a uma publicação específica.
     */
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

    /**
     * Retorna o conjunto completo de vagas registradas na plataforma.
     */
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

    /**
     * Busca os metadados descritivos de uma vaga com base em seu ID de publicação.
     */
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

    /**
     * Lista todas as vagas e os totais de candidaturas associadas de um recrutador.
     */
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

    /**
     * Busca os metadados de uma vaga de emprego utilizando seu identificador exclusivo.
     */
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

    /**
     * Recupera o identificador do autor da publicação pelo ID do post.
     */
    public function getById($id)
    {
        $query = "SELECT usuario_id FROM publicacoes WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Executa a exclusão forçada de uma publicação pelo administrador.
     * 
     * Processa a deleção completa de compartilhamentos, dados de candidaturas e vagas,
     * comentários, curtidas e notificações sem validar posse do registro.
     */
    public function excluirPostAdmin()
    {
        try {
            $this->db->beginTransaction();

            $query = "
            DELETE FROM publicacoes
            WHERE publicacao_original_id = :id
        ";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':id', $this->__get('id'));
            $stmt->execute();

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
                $query = "
                DELETE FROM candidaturas
                WHERE vaga_id = :vaga_id
            ";

                $stmt = $this->db->prepare($query);
                $stmt->bindValue(':vaga_id', $vaga['id']);
                $stmt->execute();

                $query = "
                DELETE FROM vagas
                WHERE id = :vaga_id
            ";

                $stmt = $this->db->prepare($query);
                $stmt->bindValue(':vaga_id', $vaga['id']);
                $stmt->execute();
            }

            $query = "
            DELETE FROM curtidas
            WHERE publicacao_id = :id
        ";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':id', $this->__get('id'));
            $stmt->execute();

            $query = "
            DELETE FROM comentarios
            WHERE publicacao_id = :id
        ";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':id', $this->__get('id'));
            $stmt->execute();

            $query = "
            DELETE FROM notificacoes
            WHERE referencia_id = :id
        ";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':id', $this->__get('id'));
            $stmt->execute();

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