<?php
namespace app\controller;
use MF\controller\Action;
use MF\model\Container;
use app\middleware\Auth;

class AppController extends Action
{

    public function timeline()
    {
        Auth::validarAutenticacao();
        $this->render('timeline');
    }
}
?>