<?php

namespace Db\Sql;

use Phalcon\Mvc\Model\Query;
use Michelf\Markdown;

class Posts extends \Base\Model
{
    public $id;
    public $user_id;
    public $title;
    public $slug;
    public $excerpt;
    public $body;
    public $category_id;
    public $status;
    public $is_deleted;
    public $post_date;
    public $created_at;
    public $modified_at;

    function initialize()
    {
        $this->setSource( 'posts' );
        $this->addBehavior( 'timestamp' );

        $this->images = NULL;
        $this->image = NULL;
    }

    /**
     * Get all active posts (not deleted)
     */
    static function getActive( $limit = 25, $offset = 0 )
    {
        return \Db\Sql\Posts::query()
            ->where( 'is_deleted = 0' )
            ->order( 'created_at desc' )
            ->limit( $limit, $offset )
            ->execute();
    }

    /**
     * Return a post by slug
     *
     * @param string $slug
     * @return \Db\Sql\Post
     */
    static function getBySlug( $slug )
    {
        return \Db\Sql\Posts::findFirst([
            'slug = :slug:',
            'bind' => [
                'slug' => $slug ]
            ]);
    }

    /**
     * Retrieves category for the post.
     */
    function getCategory()
    {
        return \Db\Sql\Categories::findFirst([
            'id = :id:',
            'bind' => [
                'id' => $this->category_id ]
            ]);
    }

    /**
     * Retrieves the author for the post.
     */
    function getAuthor()
    {
        return \Db\Sql\Users::findFirst([
            'id = :id:',
            'bind' => [
                'id' => $this->user_id ]
            ]);
    }

    /**
     * Get the URL for the post.
     */
    function getPath()
    {
        $config = $this->getService( 'config' );

        return sprintf(
            "%s%s/%s",
            $config->paths->baseUri,
            date_str( $this->post_date, DATE_YEAR_MONTH_SLUG ),
            $this->slug );
    }

    /**
     * Get the Markdown version of the body text
     */
    function getHtmlBody()
    {
        return Markdown::defaultTransform( nl2br( $this->body ) );
    }

    /**
     * Generate a slug based on the title
     */
    function generateSlug()
    {
        // replace non letter or digits by -, then trim, transliterate
        // utf8 characters, lowercase it, and remove unwanted characters.
        //
        $slug = preg_replace( '~[^\\pL\d]+~u', '-', $this->title );
        $slug = trim( $slug, '-' );
        $slug = iconv( 'utf-8', 'us-ascii//TRANSLIT', $slug );
        $slug = strtolower( $slug );
        $slug = preg_replace( '~[^-\w]+~', '', $slug );

        if ( empty( $slug ) )
        {
            return NULL;
        }

        // check if this slug exists. if so, we need to keep incrementing
        // a counter on the end.
        //
        $checkSlug = $slug;
        $counter = 1;
        $slugOkay = FALSE;

        do {
            $post = \Db\Sql\Posts::getBySlug( $checkSlug );
            if ( $post ):
                $checkSlug = $slug .'-'. $counter;
                $counter++;
            else:
                $slugOkay = TRUE;
                $slug = $checkSlug;
            endif;
        }
        while ( ! $slugOkay );

        return $slug;
    }
}
