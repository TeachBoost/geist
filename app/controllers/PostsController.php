<?php

namespace Controllers;

class PostsController extends \Base\Controller
{
    public function beforeExecuteRoute()
    {
        $this->checkLoggedIn = FALSE;

        return parent::beforeExecuteRoute();
    }

    public function showAction()
    {
        // load the params
        //
        $year = $this->dispatcher->getParam( 'year' );
        $month = $this->dispatcher->getParam( 'month' );
        $slug = $this->dispatcher->getParam( 'slug' );

        // find the post by slug
        //
        $post = \Db\Sql\Posts::getBySlug( $slug );

        if ( ! $post )
        {
            $this->dispatcher->forward([
                'controller' => 'error',
                'action' => 'show404' ]);
        }

        // get category name and slug
        //
        $category = \Db\Sql\Categories::getByID( $post->category_id );

        $this->data->post = $post;
        $this->data->category = $category;
        $this->data->pageTitle = $post->title;
        $this->view->pick( 'posts/show' );
    }
}