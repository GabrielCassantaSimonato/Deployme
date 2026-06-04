<?php
require_once "../vendor/autoload.php";
date_default_timezone_set('America/Sao_Paulo');

$dotenv = Dotenv\Dotenv::createImmutable('../');
$dotenv->load();
$route = new \app\route;
?>