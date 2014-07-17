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
        $post->title = $filter->sanitize( get( $data, 'title' ), 'striptags' );
        $post->body = $filter->sanitize( get( $data, 'body' ), 'striptags' );
        $post->excerpt = $filter->sanitize( get( $data, 'excerpt' ), 'striptags' );
        $post->tags = $filter->sanitize( get( $data, 'tags' ), 'striptags' );
        $post->category_id = $filter->sanitize( get( $data, 'category' ), 'striptags' );
        $post->user_id = $filter->sanitize( get( $data, 'user_id' ), 'striptags' );
        $post->post_date = date_str(
            get( $data, 'post_date' ),
            DATE_DATABASE,
            TRUE );

        // set up status filter
        $filter->add(
            'status',
            function ( $value ) {
                return ( in_array( $value, [ 'draft', 'published' ] ) )
                    ? $value
                    : 'draft';
            });
        $post->status = $filter->sanitize( get( $data, 'status' ), 'status' );

        // set the slug if there isn't one
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