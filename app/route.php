<?php
namespace app; //classe responsável por ter as rotas do sistema
use MF\init\Bootstrap;

class route extends Bootstrap{ //classe route que herda a classe bootstrap
    protected function initRoutes(){ //preparação das rotas para ir ao controller
        $routes['home'] = Array('route'=>'/','controller'=>'IndexController','action'=>'index');
        $routes['signUpStudent'] = Array('route'=>'/signUpStudent','controller'=>'IndexController','action'=>'signUpStudent');
        $routes['signUpRecruiter'] = Array('route'=>'/signUpRecruiter','controller'=>'IndexController','action'=>'signUpRecruiter');
        $this->setRoutes($routes);//seta a rota no objeto
    }
}
?>