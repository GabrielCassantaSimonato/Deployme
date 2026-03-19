<?php

namespace app;
class Connection{//classe responsável pela conexão com o banco

public static function getDB(){
    try{
        $conn = new \PDO("mysql:host=localhost;dbname=deployme;charset=utf8","root","");
        return $conn;
    }catch(\PDOException $e){
        echo 'Connection failed: ' . $e->getMessage();
        return null;
    }
}

}

?>