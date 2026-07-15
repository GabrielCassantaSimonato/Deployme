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
     * Salva as informações específicas de perfil de um estudante.
     * 
     * Executa a inserção dos dados de endereço, acadêmicos, contato técnico e link do arquivo
     * de currículo na tabela estudantes com base nas propriedades carregadas na instância.
     */
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

    /**
     * Busca a localização (cidade e UF) associada a um estudante específico.
     */
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

    /**
     * Busca o nome do arquivo de currículo cadastrado para um determinado usuário.
     */
    public function buscarCurriculo($usuario_id)
    {
        $query = "
        SELECT curriculo
        FROM estudantes
        WHERE usuario_id = :usuario_id
    ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(
            ':usuario_id',
            $usuario_id
        );

        $stmt->execute();

        return $stmt->fetch(
            \PDO::FETCH_ASSOC
        );
    }
}