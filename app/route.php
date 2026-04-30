<?php
namespace app; //classe responsável por ter as rotas do sistema
use MF\init\Bootstrap;

class route extends Bootstrap{ //classe route que herda a classe bootstrap
    protected function initRoutes(){ //preparação das rotas para ir ao controller
        $routes['home'] = Array('route'=>'/','controller'=>'IndexController','action'=>'index');
        $routes['signUpStudent'] = Array('route'=>'/signUpStudent','controller'=>'IndexController','action'=>'signUpStudent');
        $routes['signUpRecruiter'] = Array('route'=>'/signUpRecruiter','controller'=>'IndexController','action'=>'signUpRecruiter');
        $routes['lgpd'] = Array('route'=>'/lgpd','controller'=>'IndexController','action'=>'lgpd');
        $routes['loginSelection'] = Array('route'=>'/loginSelection','controller'=>'IndexController','action'=>'loginSelection');
        $routes['studentLogin'] = Array('route'=>'/studentLogin','controller'=>'IndexController','action'=>'studentLogin');
        $routes['recruiterLogin'] = Array('route'=>'/recruiterLogin','controller'=>'IndexController','action'=>'recruiterLogin');
        $routes['studentRegister'] = Array('route'=>'/studentRegister','controller'=>'IndexController','action'=>'studentRegister');
        $routes['timeline'] = Array('route'=>'/timeline','controller'=>'AppController','action'=>'timeline');
        $routes['successRegister'] = Array('route'=>'/successRegister','controller'=>'IndexController','action'=>'successRegister');
        $this->setRoutes($routes);//seta a rota no objeto
    }
}
?>