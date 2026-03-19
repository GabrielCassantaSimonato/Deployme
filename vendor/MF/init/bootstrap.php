<?php
namespace MF\init; //classe responsável por inicializar a aplicação

abstract class Bootstrap{ //classes abstratas são classes que não podem ser instanciadas
    private $routes;
    public function __construct(){//método construtor que será instanciado após a criação do objeto
        $this->initRoutes();//roda o método initRoutes
        $this->run($this->getUrl());//roda o método run
    }
    public function getRoutes(){ //get
        return $this->routes;
    }
    public function setRoutes(Array $routes){//set
        $this->routes = $routes;
    }
     protected function run($url){ //lógica para direcionar a rota ao controller
        foreach($this->getRoutes() as $key=>$route){
            if($url == $route['route']){
                $class = "app\\controller\\".$route['controller'];
                $controller = new $class;
                $action = $route['action'];
                $controller->$action();
            }
        }
    }
    protected function getUrl(){//pega a url atual
        return parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
    }
    abstract protected function initRoutes();//função será construída na classe filha
}

?>