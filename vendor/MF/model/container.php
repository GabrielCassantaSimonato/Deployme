<?php

namespace MF\model;
use app\Connection;

class Container{
    public static function getModel($model){ //função que retornará a instância do model
        $class = "\\app\\model\\".$model;
        $conn = Connection::getDb(); //instância da conexão DB
        return new $class($conn);
    }
}
?>