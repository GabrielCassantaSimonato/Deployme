<?php
namespace MF\controller;

abstract class Action {

    protected $view;

    public function __construct() {
        $this->view = new \stdClass();
    }

    protected function render($view, $layout = 'layout') {
        $this->view->page = $view;

        $layoutPath = "../app/view/" . $layout . ".phtml";

        if (file_exists($layoutPath)) {
            require_once $layoutPath;
        } else {
            $this->content();
        }
    }

    protected function content() {

        // pega o nome completo da classe
        $classAtual = get_class($this);

        // remove namespace
        $classAtual = str_replace('app\\controller\\', '', $classAtual);

        // remove "Controller"
        $classAtual = str_replace('Controller', '', $classAtual);

        // deixa minúsculo
        $classAtual = strtolower($classAtual);

        // monta caminho da view
        $viewPath = "../app/view/" . $classAtual . "/" . $this->view->page . ".phtml";

        if (!file_exists($viewPath)) {
            die("View não encontrada: " . $viewPath);
        }

        require_once $viewPath;
    }
}
?>