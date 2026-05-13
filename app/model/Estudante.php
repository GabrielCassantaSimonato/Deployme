<?php

namespace app\model;

use MF\model\Model;

class Estudante extends Model
{

    private $id;
    private $usuario_id;
    private $universidade_id;
    private $curso_id;
    private $semestre_id;
    private $github;
    private $cep;
    private $rua;
    private $bairro;
    private $cidade;
    private $complemento;
    private $uf;
    private $curriculo;

    public function __get($atributo)
    {
        return $this->$atributo;
    }

    public function __set($atributo, $valor)
    {
        $this->$atributo = $valor;
    }

    public function salvarEstudante()
    {
        $query = "INSERT INTO estudantes 
        (usuario_id, universidade_id, curso_id, semestre_id, github, cep, rua, bairro, cidade, complemento, uf, curriculo)
        VALUES 
        (:usuario_id, :universidade_id, :curso_id, :semestre_id, :github, :cep, :rua, :bairro, :cidade, :complemento, :uf, :curriculo)";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':usuario_id', $this->__get('usuario_id'));
        $stmt->bindValue(':universidade_id', $this->__get('universidade_id'));
        $stmt->bindValue(':curso_id', $this->__get('curso_id'));
        $stmt->bindValue(':semestre_id', $this->__get('semestre_id'));
        $stmt->bindValue(':github', $this->__get('github'));
        $stmt->bindValue(':cep', $this->__get('cep'));
        $stmt->bindValue(':rua', $this->__get('rua'));
        $stmt->bindValue(':bairro', $this->__get('bairro'));
        $stmt->bindValue(':cidade', $this->__get('cidade'));
        $stmt->bindValue(':complemento', $this->__get('complemento'));
        $stmt->bindValue(':uf', $this->__get('uf'));
        $stmt->bindValue(':curriculo', $this->__get('curriculo'));

        return $stmt->execute();
    }

    public function buscarPorUsuario($usuario_id)
    {
        $query = "
        SELECT cidade, uf
        FROM estudantes
        WHERE usuario_id = :usuario_id
    ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':usuario_id', $usuario_id);

        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}

?>