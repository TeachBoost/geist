<?php

namespace Controllers;

class IndexController extends \Base\Controller
{
    public function beforeExecuteRoute()
    {
        $this->checkLoggedIn = FALSE;

        return parent::beforeExecuteRoute();
    }

    public function indexAction()
    {
        $this->view->posts = \Db\Sql\Posts::getActive( 10 );
        $this->view->categories = \Db\Sql\Categories::getAll();
        $this->view->pick( 'home/index' );
    }
}