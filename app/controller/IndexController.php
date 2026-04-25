<?php
    namespace app\controller;
    use MF\controller\Action;
    use MF\model\Container;

    class IndexController extends Action{
        public function index(){
            $this->render('index');
        }
        public function signUpStudent(){
            $this->render('signUpstudent');
        }
        public function signUpRecruiter(){
            $this->render('signUpRecruiter');
        }
        public function lgpd(){
            $this->render('lgpd');
        }
        public function loginSelection(){
            $this->render('loginSelection');
        }
        public function studentLogin(){
            $this->render('studentLogin');
        }
        public function recruiterLogin(){
            $this->render('recruiterLogin');
        }
    }
?>