<?php
    namespace app\controller;
    use MF\controller\Action;
    use MF\model\Container;

    class IndexController extends Action{
        public function index(){
            $this->render('index');
        }
    }
?>