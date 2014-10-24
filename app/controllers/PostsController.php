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

    /**
     * Present a paginated page of posts
     *
     * @param int $number Page number
     */
    public function pageAction( $number = "" )
    {
        if ( ! valid( $number ) )
        {
            return $this->show404();
        }

        // determine offset
        $offset = ( $number - 1 ) * 10;
        $postCount = \Db\Sql\Posts::getCount();

        // get the posts with the page offset
        $this->view->page = $number;
        $this->view->offset = $offset;
        $this->view->posts = \Db\Sql\Posts::getPublished( 10, $offset );
        $this->view->postCount = $postCount;
        $this->view->totalPages = ceil( $postCount / 10 );

        $this->view->pick( 'posts/page' );
    }

    /**
     * Display a particular post. This takes params from a stored
     * route in the routes file.
     */
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

        $this->data->pageTitle = $post->title;
        $this->data->metaDescription = $post->excerpt;
        $this->data->post = $post;
        $this->data->category = $category;
        $this->data->pageTitle = $post->title;
        $this->view->pick( 'posts/show' );
    }

    /**
     * Render the RSS feed
     */
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
        $posts = \Db\Sql\Posts::getPublished( 20 );

        foreach ( $posts as $post )
        {
            $item = new Item();
            $item
                ->title( $post->title )
                ->description( $post->getHtmlBody() )
                ->pubDate( strtotime( $post->post_date ) )
                ->category( $post->getCategory()->name )
                ->url( $post->getPath() )
                ->appendTo( $channel );
        }

        $response = new \Phalcon\Http\Response();
        $response->setHeader(
            'Content-Type',
            'application/rss+xml; charset=utf-8' );
        $response->setContent( $feed );
        $response->send();
        exit;
    }
}