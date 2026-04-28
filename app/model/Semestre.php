<?php
namespace app\model;

use MF\model\Model;

class Semestre extends Model {

    public function getSemestres() {
        $query = "SELECT id, semestre FROM semestres ORDER BY id";
        return $this->db->query($query)->fetchAll(\PDO::FETCH_ASSOC);
    }

}

?>