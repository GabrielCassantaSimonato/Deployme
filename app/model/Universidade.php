<?php
namespace app\model;
use MF\model\Model;
class Universidade extends Model
{
    //lista as universidades para o cadastro de estudantes
    public function getUniversidades()
    {
        $query = "SELECT id, nome FROM universidades ORDER BY nome";
        return $this->db->query($query)->fetchAll(\PDO::FETCH_ASSOC);
    }

}

?>