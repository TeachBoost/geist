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
    public $location;
    public $external_url;
    public $status;
    public $is_deleted;
    public $post_date;
    public $event_date;
    public $event_date_end;
    public $event_time;
    public $event_time_end;
    public $homepage_location;
    public $created_at;
    public $modified_at;

    private $images;
    private $image;

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
     * Pull the most recent X posts that are set to display
     * in the home page boxes or hero filmstrip.
     *
     * @param string $location 'boxes' or 'hero'
     * @param integer $limit
     */
    static function getByLocation( $location = 'boxes', $limit = 5 )
    {
        return \Db\Sql\Posts::query()
            ->where( 'is_deleted = 0' )
            ->andWhere( "status = 'published'" )
            ->andWhere( "homepage_location = :location:" )
            ->andWhere( "post_date <= :dateLimit:" )
            ->order( 'post_date desc' )
            ->limit( $limit )
            ->bind([
                'location' => $location,
                'dateLimit' => date_str( NULL, DATE_DATABASE ) ])
            ->execute();
    }

    /**
     * Returns posts between a given date range and for a given
     * set of categories. You can optionally limit these results.
     * The results are sorted by oldest first unless specified.
     *
     * @param array $params
     * @return \Db\Sql\Posts
     */
    static function getByCategoryDateRange( $params = array() )
    {
        $defaults = [
            'categories' => [ EVENTS, EXHIBITIONS ],
            'startDate' => date( DATE_DATABASE, mktime( 0, 0, 0 ) ),
            'endDate' => NULL,
            'startOperand' => '>=',
            'endOperand' => '<=',
            'ongoing' => FALSE,
            'sort' => 'event_date asc',
            'limit' => 9999,
            'offset' => 0 ];
        $options = array_merge( $defaults, $params );
        $dateWhereClauses = [];
        $bindings = [];

        // create our date where clause
        //
        if ( ! is_null( $options[ 'startDate' ] ) )
        {
            $string = ( $options[ 'ongoing' ] )
                ? "( p.event_date %s :startDate: )"
                : "( p.event_date %s :startDate: or p.event_date_end %s :startDate: )";
            $dateWhereClauses[] = sprintf(
                $string,
                $options[ 'startOperand' ],
                $options[ 'startOperand' ] );
            $bindings[ 'startDate' ] = $options[ 'startDate' ];
        }

        if ( ! is_null( $options[ 'endDate' ] ) )
        {
            $string = ( $options[ 'ongoing' ] )
                ? "( p.event_date_end %s :endDate: )"
                : "( p.event_date %s :endDate: or p.event_date_end %s :endDate: )";
            $dateWhereClauses[] = sprintf(
                $string,
                $options[ 'endOperand' ],
                $options[ 'endOperand' ] );
            $bindings[ 'endDate' ] = $options[ 'endDate' ];
        }

        // create the SQL statement
        //
        $phql = sprintf(
            "select p.* from \Db\Sql\Posts as p ".
            "inner join \Db\Sql\Relationships as r ".
            "  on p.id = r.object_id and r.object_type = '%s' ".
            "inner join \Db\Sql\Categories as c ".
            "  on c.id = r.property_id and r.property_type = '%s' ".
            "where c.slug in ('%s') ".
            "  and ( %s ) ".
            "order by p.%s ".
            "limit %s, %s",
            POST,
            CATEGORY,
            implode( ',', $options[ 'categories' ] ),
            implode( ' and ', $dateWhereClauses ),
            $options[ 'sort' ],
            $options[ 'offset' ],
            $options[ 'limit' ] );
        $query = new Query( $phql, self::getStaticDI() );

        return $query->execute( $bindings );
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
     * Returns the images for the post
     */
    function getImages()
    {
        if ( ! is_null( $this->images ) )
        {
            return $this->images;
        }

        $this->images = \Db\Sql\Images::query()
            ->where( 'post_id = :postId:' )
            ->andWhere( 'is_deleted = 0' )
            ->bind([ 'postId' => $this->id ])
            ->execute();

        return $this->images;
    }

    /**
     * Returns one (primary) image for the post
     */
    function getImage()
    {
        if ( ! is_null( $this->image ) )
        {
            return $this->image;
        }

        $this->image = \Db\Sql\Images::findFirst([
            'post_id = :postId: and is_deleted = 0',
            'bind' => [
                'postId' => $this->id ]
            ]);

        if ( ! $this->image )
        {
            $this->image = new \Db\Sql\Images();
        }

        return $this->image;
    }

    /**
     * Retrieves categories for the post.
     */
    function getCategories()
    {
        return \Db\Sql\Relationships::getProperties(
            '\Db\Sql\Categories',
            CATEGORY,
            $this->id,
            POST );
    }

    /**
     * Retrieves tags for the post
     */
    function getTags()
    {
        return \Db\Sql\Relationships::getProperties(
            '\Db\Sql\Tags',
            TAG,
            $this->id,
            POST );
    }

    /**
     * Retrieves artists for the post
     */
    function getArtists()
    {
        return \Db\Sql\Relationships::getProperties(
            '\Db\Sql\Artists',
            ARTIST,
            $this->id,
            POST );
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

    /**
     * Return a shortened version of the event's start and stop time
     */
    function getShortTime( $html = TRUE, $withLocation = FALSE )
    {
        if ( ! valid( $this->event_time, STRING ) )
        {
            return "";
        }

        $shortTime = ( substr( $this->event_time, 3, 2 ) == '00' )
            ? date( 'ga', strtotime( $this->event_time ) )
            : date( 'g:ia', strtotime( $this->event_time ) );

        if ( ! valid( $this->event_time_end, STRING ) )
        {
            return ( $withLocation && valid( $this->location, STRING ) )
                ? trim( $shortTime ." @ ". $this->location )
                : $shortTime;
        }

        $shortTime .= ( $html ) ? " &ndash; " : ' - ';
        $shortTime .= ( substr( $this->event_time_end, 3, 2 ) == '00' )
            ? date( 'ga', strtotime( $this->event_time_end ) )
            : date( 'g:ia', strtotime( $this->event_time_end ) );

        return ( $withLocation && valid( $this->location, STRING ) )
            ? trim( $shortTime ." @ ". $this->location )
            : $shortTime;
    }
}
