<?php

namespace Db\Sql;

use Phalcon\Mvc\Model\Query;
use Michelf\MarkdownExtra;

class Posts extends \Base\Model
{
    public $id;
    public $user_id;
    public $title;
    public $slug;
    public $excerpt;
    public $body;
    public $tags;
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

    static function getPublished( $limit = 25, $offset = 0 )
    {
        return \Db\Sql\Posts::query()
            ->where( 'is_deleted = 0' )
            ->where( 'status = "published"' )
            ->orderBy( 'post_date desc' )
            ->limit( $limit, $offset )
            ->execute();
    }

    /**
     * Returns the count of active posts
     */
    static function getCount()
    {
        return \Db\Sql\Posts::count([
            "is_deleted = ?0 and status = ?1",
            "bind" => [ 0, 'published' ]
        ]);
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
     * Retrieves the category icon
     */
    function getCategoryIcon()
    {
        $icons = [
            'events' => [ 'fa-calendar', 'red' ],
            'news' => [ 'fa-bullhorn', 'blue' ],
            'release-notes' => [ 'fa-file-text-o', 'teal' ],
            'product-updates' => [ 'fa-bookmark', 'teal' ],
            'tips-tricks' => [ 'fa-magic', 'green' ],
            'education' => [ 'fa-book', 'purple' ],
            'technology' => [ 'fa-mobile', 'purple' ],
            'webinars' => [ 'fa-desktop', 'orange' ],
            'ilc' => [ 'fa-trophy', 'orange' ],
            'spotlights' => [ 'fa-lightbulb-o', 'green' ],
            'default' => [ 'fa-file-text-o', 'blue' ]];
        $category = $this->getCategory();

        if ( ! isset( $icons[ $category->slug ] ) )
        {
            return new \Base\Object([
                'class' => $icons[ 'default' ][ 0 ],
                'color' => $icons[ 'default' ][ 1 ]
            ]);
        }

        return new \Base\Object([
            'class' => $icons[ $category->slug ][ 0 ],
            'color' => $icons[ $category->slug ][ 1 ]
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
        return $this->textToHtml( $this->body );
    }

    /**
     * Get the Markdown version of the excerpt text
     */
    function getHtmlExcerpt()
    {
        $html = $this->textToHtml( $this->excerpt );

        // strip block level elements
        return strip_tags( $html, '<a><i><span><br>' );
    }

    /**
     * Converts markdown text to html
     */
    private function textToHtml( $text )
    {
        // get html from markdown
        $html = MarkdownExtra::defaultTransform( trim( $text ) );

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
            '<iframe width="%s" height="%s" scrolling="no" frameborder="no" '.
                'src="%s/%s%s"></iframe>',
            '100%',
            '166',
            'https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks',
            '$1', // preg variable of cloud ID
            '&amp;color=ff5500&amp;auto_play=false&amp;hide_related=false&amp;show_artwork=true' );
        $html = preg_replace( "/\[\#soundcloud:(.*?)\]/", $soundcloudEmbed, $html );

        // process any storify embeds
        $storifyEmbed = sprintf(
            '<div class="storify">'.
                '<iframe src="//storify.com/TeachBoost/%s/embed?header=false&amp;border=false" '.
                    'width="%s" height=%s frameborder=no allowtransparency=true>'.
                '</iframe>'.
                '<script src="//storify.com/TeachBoost/%s.js?header=false&amp;border=false"></script>'.
                '<noscript>[<a href="//storify.com/TeachBoost/%s" target="_blank">'.
                    'View the story "#%s" on Storify</a>]</noscript>'.
            '</div>',
            '$1',
            '100%',
            '750',
            '$1',
            '$1',
            '$1' );
        $html = preg_replace( "/\[\#storify:(.*?)\]/", $storifyEmbed, $html );

        // process any images
        $img = '<img src="$1" alt="" title="" />';
        $html = preg_replace( "/\[\#image:(.*?)\]/", $img, $html );

        // process any popup links
        $popup = '<a href="$2" target="_blank">$1</a>';
        $html = preg_replace( "/\[\#popup:\((.*?)\)\((.*?)\)\]/", $popup, $html );

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
