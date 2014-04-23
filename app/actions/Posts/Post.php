<?php

namespace Actions\Posts;

use \Db\Sql\Posts as Posts;

class Post extends \Base\Action
{
    /**
     * Creates a new blank post
     *
     * @return integer Post ID
     */
    public function create()
    {
        $auth = $this->getService( 'auth' );
        $post = new Posts();
        $post->initialize();
        $post->user_id = $auth->getUserId();
        $post->is_deleted = 0;
        $post->status = 'draft';

        if ( ! $this->save( $post ) )
        {
            return FALSE;
        }

        return $post->id;
    }

    /**
     * Saves a post
     *
     * @param array $data
     * @return boolean
     */
    public function edit( $data )
    {
        $util = $this->getService( 'util' );
        $filter = $this->getService( 'filter' );

        // check the post ID and verify that this post exists
        //
        if ( ! isset( $data[ 'id' ] )
            || ! valid( $data[ 'id' ], INT ) )
        {
            $util->addMessage( "You didn't specify a post ID.", INFO );
            return FALSE;
        }

        $post = Posts::findFirst( $data[ 'id' ] );

        if ( ! $post )
        {
            $util->addMessage( "That post couldn't be found.", INFO );
            return FALSE;
        }

        // apply the data params to the post and save it
        //
        $post->title = $filter->sanitize( get( $data, 'title' ), 'striptags' );
        $post->body = $filter->sanitize( get( $data, 'body' ), 'striptags' );
        $post->location = $filter->sanitize( get( $data, 'location' ), 'striptags' );
        $post->external_url = $filter->sanitize( get( $data, 'external_url' ), 'striptags' );
        $post->excerpt = $filter->sanitize( get( $data, 'excerpt' ), 'striptags' );
        $post->post_date = date_str(
            get( $data, 'post_date' ),
            DATE_DATABASE,
            TRUE );
        $post->event_date = date_str(
            get( $data, 'event_date' ),
            DATE_DATABASE,
            TRUE );
        $post->event_date_end = date_str(
            get( $data, 'event_date_end' ),
            DATE_DATABASE,
            TRUE );

        // save the times if they came in
        //
        $post->event_time = date_str(
            get( $data, 'event_time' ),
            DATE_TIME_DATABASE,
            TRUE );
        $post->event_time_end = date_str(
            get( $data, 'event_time_end' ),
            DATE_TIME_DATABASE,
            TRUE );

        // set up status filter
        //
        $filter->add(
            'status',
            function ( $value ) {
                return ( in_array( $value, [ 'draft', 'published' ] ) )
                    ? $value
                    : 'draft';
            });
        $post->status = $filter->sanitize( get( $data, 'status' ), 'status' );

        // set up homepage loc filter
        //
        $filter->add(
            'homepageLocation',
            function ( $value ) {
                return ( in_array( $value, [ 'hero', 'boxes' ] ) )
                    ? $value
                    : NULL;
            });
        $post->homepage_location = $filter->sanitize(
            get( $data, 'homepage_location' ),
            'homepageLocation' );

        // set the slug if there isn't one
        //
        if ( ! valid( $post->slug, STRING )
            && valid( $post->title, STRING ) )
        {
            $post->slug = $post->generateSlug();
        }

        if ( ! $this->save( $post ) )
        {
            return FALSE;
        }

        return $post;
    }

    /**
     * Deletes a post, i.e. marks it is_deleted
     */
    public function delete( $id )
    {
        $util = $this->getService( 'util' );
        $post = Posts::findFirst( $id );

        if ( ! $post )
        {
            $util->addMessage( "That post couldn't be found.", INFO );
            return FALSE;
        }

        $post->is_deleted = 1;

        return $this->save( $post );
    }

    /**
     * Saves a post, error handles
     *
     * @param \Db\Sql\Post $post
     * @return
     */
    private function save( &$post )
    {
        if ( $post->save() == FALSE )
        {
            $util = $this->getService( 'util' );
            $util->addMessage(
                "There was a problem saving your post.",
                INFO );

            foreach ( $post->getMessages() as $message )
            {
                $util->addMessage( $message, ERROR );
            }

            return FALSE;
        }

        return TRUE;
    }
}