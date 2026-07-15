<?php

namespace app\model;

use MF\model\Model;

class Recrutador extends Model
{
    private $usuario_id;
    private $empresa;
    private $senioridade_id;

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
     * Salva as informações específicas de perfil de um recrutador.
     * 
     * Insere o nome da empresa e o nível de senioridade na tabela recrutadores,
     * vinculando-os ao identificador do usuário gerado no cadastro.
     */
    public function salvarRecrutador()
    {
        $query = "INSERT INTO recrutadores 
        (usuario_id, empresa, senioridade_id)
        VALUES 
        (:usuario_id, :empresa, :senioridade_id)";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':usuario_id', $this->__get('usuario_id'));
        $stmt->bindValue(':empresa', $this->__get('empresa'));
        $stmt->bindValue(':senioridade_id', $this->__get('senioridade_id'));

        return $stmt->execute();
    }

    /**
     * Busca o nome da empresa associada a um recrutador específico.
     */
    public function buscarPorUsuario($usuario_id)
    {
        $query = "
        SELECT empresa
        FROM recrutadores
        WHERE usuario_id = :usuario_id
    ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':usuario_id', $usuario_id);

        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}