<?php

namespace app\model;

use MF\model\Model;

class Admin extends Model
{

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

}