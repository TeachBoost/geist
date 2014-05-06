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

    private $category;

    function initialize()
    {
        $this->setSource( 'posts' );
        $this->addBehavior( 'timestamp' );

        $this->category = NULL;
    }

    /**
     * Get all active posts (not deleted)
     */
    static function getActive( $limit = 25, $offset = 0 )
    {
        return \Db\Sql\Posts::query()
            ->where( 'is_deleted = 0' )
            ->orderBy( 'post_date desc' )
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
        if ( ! is_null( $this->category ) )
        {
            return $this->category;
        }

        $category = \Db\Sql\Categories::findFirst([
            'id = :id:',
            'bind' => [
                'id' => $this->category_id ]
            ]);
        $this->category = ( $category )
            ? $category
            : new \Db\Sql\Categories();

        return $this->category;
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
        // get html from markdown
        $html = Markdown::defaultTransform( $this->body );

        // process any icons. these take the form [#icon:name] and
        // should be replaced with font icon tags.
        $html = preg_replace(
            "/\[\#icon:(.*?)\]/",
            "<i class=\"fa fa-$1\"></i>",
            $html );

        // process any youtube videos
        $youtubeEmbed = sprintf(
            '<iframe width="%s" height="%s" src="%s/%s?rel=0" '.
                'frameborder="0" allowfullscreen></iframe>',
            '670',
            '380',
            '//www.youtube.com/embed',
            '$1' ); // preg variable of video ID
        $html = preg_replace( "/\[\#youtube:(.*?)\]/", $youtubeEmbed, $html );

        // process any vimeo videos
        $vimeoEmbed = sprintf(
            '<iframe src="%s/%s?%s" width="%s" height="%s"' .
                'frameborder="0" allowfullscreen></iframe>',
            '//player.vimeo.com/video',
            '$1', // preg variable of video ID
            'title=0&portrait=0&badge=0',
            '670',
            '380' );
        $html = preg_replace( "/\[\#vimeo:(.*?)\]/", $vimeoEmbed, $html );

        // process any soundcloud links
        $soundcloudEmbed = sprintf(
            '<iframe width="100%" height="450" scrolling="no" frameborder="no" '.
                'src="%s/%s%s"></iframe>',
            'https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks',
            '$1', // preg variable of cloud ID
            '&amp;auto_play=false&amp;hide_related=false&amp;visual=true' );
        $html = preg_replace( "/\[\#soundcloud:(.*?)\]/", $soundcloudEmbed, $html );

        // process any images
        $img = '<img src="$1" alt="" title="" />';
        $html = preg_replace( "/\[\#image:(.*?)\]/", $img, $html );

        return $html;
    }

    /**
     * Generate a slug based on the title
     */
    function generateSlug()
    {
        // replace non letter or digits by -, then trim, transliterate
        // utf8 characters, lowercase it, and remove unwanted characters.
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
