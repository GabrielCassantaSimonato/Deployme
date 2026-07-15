<?php

namespace app\model;

use MF\model\Model;

class Admin extends Model
{
    /**
     * Consolida as métricas gerais para o dashboard do administrador.
     * 
     * Agrupa em um array associativo os totalizadores de usuários, estudantes,
     * recrutadores, contas bloqueadas, publicações gerais e vagas ativas no sistema.
     */
    public function dashboard()
    {
        return [
            'usuarios' => $this->totalUsuarios(),
            'estudantes' => $this->totalEstudantes(),
            'recrutadores' => $this->totalRecrutadores(),
            'bloqueados' => $this->totalBloqueados(),
            'publicacoes' => $this->totalPublicacoes(),
            'vagas' => $this->totalVagas()
        ];
    }

    /**
     * Calcula a quantidade total de usuários cadastrados no banco de dados.
     */
    public function totalUsuarios()
    {
        $query = "
            SELECT COUNT(*) total
            FROM usuarios
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC)['total'];
    }

    /**
     * Calcula a quantidade total de usuários com o perfil de estudante.
     */
    public function totalEstudantes()
    {
        $query = "
            SELECT COUNT(*) total
            FROM usuarios
            WHERE tipo = 'estudante'
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC)['total'];
    }

    /**
     * Calcula a quantidade total de usuários com o perfil de recrutador.
     */
    public function totalRecrutadores()
    {
        $query = "
            SELECT COUNT(*) total
            FROM usuarios
            WHERE tipo = 'recrutador'
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC)['total'];
    }

    /**
     * Calcula a quantidade total de usuários que estão com a conta bloqueada.
     */
    public function totalBloqueados()
    {
        $query = "
            SELECT COUNT(*) total
            FROM usuarios
            WHERE status = 'bloqueado'
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC)['total'];
    }

    /**
     * Calcula a quantidade total de publicações realizadas na plataforma.
     */
    public function totalPublicacoes()
    {
        $query = "
            SELECT COUNT(*) total
            FROM publicacoes
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC)['total'];
    }

    /**
     * Calcula a quantidade total de vagas de emprego registradas no sistema.
     */
    public function totalVagas()
    {
        $query = "
            SELECT COUNT(*) total
            FROM vagas
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC)['total'];
    }

    /**
     * Recupera a listagem completa de todos os usuários cadastrados na base de dados.
     * 
     * Retorna colunas essenciais como identificador, nome, e-mail, foto de perfil,
     * nível de acesso, status da conta e data de criação de cada usuário cadastrado.
     */
    public function listarUsuarios()
    {
        $query = "
        SELECT
            id,
            nome,
            email,
            foto,
            tipo,
            status,
            criado_em
        FROM usuarios";

        $stmt = $this->db->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}