<?php
namespace app\model;

use MF\model\Model;

class Genero extends Model
{
    //lista os gêneros para o cadastro de estudantes e recrutadores
    public function getGeneros()
    {
        $query = "SELECT id, genero FROM generos ORDER BY genero";
        return $this->db->query($query)->fetchAll(\PDO::FETCH_ASSOC);
    }

}

?>