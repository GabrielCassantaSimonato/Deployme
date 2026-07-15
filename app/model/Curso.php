<?php
namespace app\model;
use MF\model\Model;
class Curso extends Model
{
    //Lista os cursos no cadastro do estudante
    public function getCursos()
    {
        $query = "SELECT id, nome FROM cursos ORDER BY nome";
        return $this->db->query($query)->fetchAll(\PDO::FETCH_ASSOC);
    }

}
?>