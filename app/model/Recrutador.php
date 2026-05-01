<?php
namespace app\model;

use MF\model\Model;

class Recrutador extends Model {

    private $usuario_id;
    private $empresa;
    private $senioridade_id;

    public function __get($atributo) {
        return $this->$atributo;
    }

    public function __set($atributo, $valor) {
        $this->$atributo = $valor;
    }

    public function salvarRecrutador() {

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
}
?>