<?php
namespace app\model;

use MF\model\Model;

class Senioridade extends Model
{
    //lista as senioridades para o cadastro de recrutadores
    public function getSenioridades()
    {
        $query = "SELECT id, senioridade FROM senioridades ORDER BY id";
        return $this->db->query($query)->fetchAll(\PDO::FETCH_ASSOC);
    }

}

?>