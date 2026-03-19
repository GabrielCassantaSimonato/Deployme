<?php
namespace MF\controller;

abstract class Action{
    protected $view;

        public function __construct(){
            $this->view = new \stdClass(); //classe nativa do php para criar objetos padrões
        }
        protected function render($view,$layout = 'layout'){ //método que renderiza a view
            $this->view->page = $view;
            if(file_exists("../app/view/".$layout.".phtml")){
            require_once("../app/view/".$layout.".phtml");
            }else{
                $this->content();
            }
        }
        protected function content(){ //lógica de renderização do conteúdo (reaproveitamento de layouts)
            $classAtual = get_class($this);
            $classAtual = str_replace('app\\controller\\','',$classAtual);
            $classAtual = strtolower(str_replace('IndexController','Index',$classAtual));
            require_once "../app/view/".$classAtual."/".$this->view->page.".phtml";
        }
}
?>