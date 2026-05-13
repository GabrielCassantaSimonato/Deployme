<?php

namespace app\model;

use MF\model\Model;

class Usuario extends Model
{

    private $id;
    private $nome;
    private $email;
    private $senha;
    private $tipo;
    private $genero_id;
    private $foto;
    private $criado_em;

    // GET e SET mágico
    public function __get($atributo)
    {
        return $this->$atributo;
    }

    public function __set($atributo, $valor)
    {
        $this->$atributo = $valor;
    }

    public function salvarUsuario()
    {
        $query = "INSERT INTO usuarios 
        (nome, email, senha, tipo, genero_id, foto, criado_em)
        VALUES 
        (:nome, :email, :senha, :tipo, :genero_id, :foto, NOW())";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':nome', $this->__get('nome'));
        $stmt->bindValue(':email', $this->__get('email'));
        $stmt->bindValue(':senha', $this->__get('senha'));
        $stmt->bindValue(':tipo', $this->__get('tipo'));
        $stmt->bindValue(':genero_id', $this->__get('genero_id'));
        $stmt->bindValue(':foto', $this->__get('foto'));

        $stmt->execute();

        return $this->db->lastInsertId();
    }

    public function emailExiste()
    {
        $query = "SELECT id FROM usuarios WHERE email = :email";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':email', $this->__get('email'));
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    public function validarCadastro()
    {

        $erros = [];

        if (empty($this->__get('nome'))) {
            $erros['nome'] = 'Nome é obrigatório';
        }

        if (empty($this->__get('email'))) {
            $erros['email'] = 'Email é obrigatório';
        }

        if ($this->emailExiste()) {
            $erros['email'] = 'Email já cadastrado';
        }

        if (strlen($this->__get('senha')) < 8) {
            $erros['senha'] = 'Senha deve ter no mínimo 8 caracteres';
        }

        return $erros;
    }

    public function buscarPorEmail()
    {
        $query = "
        SELECT *
        FROM usuarios
        WHERE email = :email
    ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':email', $this->__get('email'));

        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}

?>