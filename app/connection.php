<?php

namespace app;
class Connection
{//classe responsável pela conexão com o banco

    public static function getDB()
    {
        try {
            $host = $_ENV['HOST'];
            $dbname = $_ENV['DBNAME'];
            $charset = $_ENV['CHARSET'];
            $user = $_ENV['USER'];
            $password = $_ENV['PASSWORD'];

            $conn = new \PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $user, $password);
            return $conn;
        } catch (\PDOException $e) {
            echo 'Connection failed: ' . $e->getMessage();
            return null;
        }
    }

}

?>