<?php
namespace app\controller;
use MF\controller\Action;
use MF\model\Container;

class AppController extends Action
{

    public function timeline()
    {
        session_start();
        $this->render('timeline');
    }
}
?>