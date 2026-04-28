<?php
namespace app\model;

use MF\model\Model;

class Genero extends Model {

    public function getGeneros() {
        $query = "SELECT id, genero FROM generos ORDER BY genero";
        return $this->db->query($query)->fetchAll(\PDO::FETCH_ASSOC);
    }

}

?>