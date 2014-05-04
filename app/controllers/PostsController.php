<?php

namespace Controllers;

use \Suin\RSSWriter\Feed as Feed,
    \Suin\RSSWriter\Channel as Channel,
    \Suin\RSSWriter\Item as Item;

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
        $year = $this->dispatcher->getParam( 'year' );
        $month = $this->dispatcher->getParam( 'month' );
        $slug = $this->dispatcher->getParam( 'slug' );

        // find the post by slug
        $post = \Db\Sql\Posts::getBySlug( $slug );

        if ( ! $post )
        {
            $this->dispatcher->forward([
                'controller' => 'error',
                'action' => 'show404' ]);
        }

        // get category name and slug
        $category = \Db\Sql\Categories::getByID( $post->category_id );

        $this->data->post = $post;
        $this->data->category = $category;
        $this->data->pageTitle = $post->title;
        $this->view->pick( 'posts/show' );
    }

    public function rssAction()
    {
        // create the feed
        $feed = new Feed();

        // create the channel
        $channel = new Channel();
        $channel
            ->title( 'TeachBoost Blog' )
            ->description(
                'Teacher development and coaching redefined. '.
                'Get TeachBoost for your school or district at '.
                'teachboost.com.' )
            ->url( $this->config->paths->baseUri )
            ->appendTo( $feed );

        // get the most recent posts
        $posts = \Db\Sql\Posts::getActive( 20 );

        foreach ( $posts as $post )
        {
            $item = new Item();
            $item
                ->title( $post->title )
                ->description( $post->excerpt )
                ->url( $post->getPath() )
                ->appendTo( $channel );
        }

        header( 'Content-Type: application/rss+xml; charset=utf-8' );
        echo $feed;
        exit;
    }
}