<?php

namespace Controllers;

class IndexController extends \Base\Controller
{
    public function beforeExecuteRoute()
    {
        $this->checkLoggedIn = FALSE;

        return parent::beforeExecuteRoute();
    }

    /**
     * Home page
     */
    public function indexAction()
    {
        // get the posts and categories
        $this->view->posts = \Db\Sql\Posts::getPublished( 10 );
        $this->view->categories = \Db\Sql\Categories::getAll();

        // get the total count of posts for the pagination
        $this->view->postCount = \Db\Sql\Posts::getCount();

        $this->view->pick( 'home/index' );
    }
}