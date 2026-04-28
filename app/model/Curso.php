<?php
namespace app\model;
use MF\model\Model;
class Curso extends Model {

    public function getCursos() {
        $query = "SELECT id, nome FROM cursos ORDER BY nome";
        return $this->db->query($query)->fetchAll(\PDO::FETCH_ASSOC);
    }

}
?>