<?php

namespace app\controller;

use MF\controller\Action;
use app\middleware\Auth;

class AdminController extends Action
{

    public function dashboard()
    {
        Auth::validarAdmin();
        if (
            !isset($_SESSION['id'])
            ||
            $_SESSION['tipo'] != 'admin'
        ) {

            header('Location: /');
            exit;
        }
        $this->render('dashboard');
    }
}