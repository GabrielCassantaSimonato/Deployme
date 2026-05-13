<?php
namespace app; //classe responsável por ter as rotas do sistema

use MF\init\Bootstrap;

class route extends Bootstrap
{ //classe route que herda a classe bootstrap
    protected function initRoutes()
    { //preparação das rotas para ir ao controller
        $routes['home'] = array('route' => '/', 'controller' => 'IndexController', 'action' => 'index');
        $routes['signUpStudent'] = array('route' => '/signUpStudent', 'controller' => 'IndexController', 'action' => 'signUpStudent');
        $routes['signUpRecruiter'] = array('route' => '/signUpRecruiter', 'controller' => 'IndexController', 'action' => 'signUpRecruiter');
        $routes['lgpd'] = array('route' => '/lgpd', 'controller' => 'IndexController', 'action' => 'lgpd');
        $routes['login'] = array('route' => '/login', 'controller' => 'IndexController', 'action' => 'login');
        $routes['studentRegister'] = array('route' => '/studentRegister', 'controller' => 'IndexController', 'action' => 'studentRegister');
        $routes['recruiterRegister'] = array('route' => '/recruiterRegister', 'controller' => 'IndexController', 'action' => 'recruiterRegister');
        $routes['timeline'] = array('route' => '/timeline', 'controller' => 'AppController', 'action' => 'timeline');
        $routes['successRegister'] = array('route' => '/successRegister', 'controller' => 'IndexController', 'action' => 'successRegister');
        $routes['resumeAnalyzer'] = array('route' => '/resumeAnalyzer', 'controller' => 'IAController', 'action' => 'resumeAnalyzer');
        $routes['auth'] = array('route' => '/auth', 'controller' => 'AuthController', 'action' => 'auth');
        $routes['logout'] = array('route' => '/logout', 'controller' => 'AuthController', 'action' => 'logout');
        $routes['loginAdmin'] = array('route' => '/loginAdmin', 'controller' => 'IndexController', 'action' => 'loginAdmin');
        $routes['admin'] = array('route' => '/admin', 'controller' => 'AdminController', 'action' => 'dashboard');
        $this->setRoutes($routes);//seta a rota no objeto
    }
}
?>