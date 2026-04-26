<?php
namespace app\controller;
use MF\controller\Action;
use MF\model\Container;

class TimelineController extends Action
{

    public function timeline()
    {
        $this->render('timeline');
    }
}
?>