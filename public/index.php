<?php
require_once "../vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createImmutable('../');
$dotenv->load();
$route = new \app\route;
?>