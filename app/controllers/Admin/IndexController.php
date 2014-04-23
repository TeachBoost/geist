<?php

namespace Controllers\Admin;

class IndexController extends \Base\Controller
{
    public function beforeExecuteRoute()
    {
        $this->checkLoggedIn = TRUE;

        return parent::beforeExecuteRoute();
    }

    public function indexAction()
    {
        $this->redirect = "admin/articles";
    }
}