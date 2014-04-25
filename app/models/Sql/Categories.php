<?php

namespace Db\Sql;

class Categories extends \Base\Model
{
    public $id;
    public $name;
    public $slug;
    public $created_at;

    function initialize()
    {
        $this->setSource( 'categories' );
        $this->addBehavior( 'timestamp' );
    }

    /**
     * Returns all categories with the given slugs
     *
     * @param array $slugs
     * @return array of \Db\Sql\Categories
     */
    static function getBySlugs( $slugs )
    {
        return \Db\Sql\Categories::query()
            ->inWhere( 'slug', $slugs )
            ->execute();
    }

    /**
     * Creates a slug based on the name
     */
    function generateSlug()
    {
        return self::slugify( $this->name );
    }

    /**
     * Creates a slug based on the name
     */
    static function slugify( $name )
    {
        return trim(
            strtolower(
                str_replace( ' ', '-', $name )
            ));
    }

    /**
     * Gets the category's name and slug by ID
     */
    static function getByID( $id )
    {
        return \Db\Sql\Categories::findFirst([
            'id = :id:',
            'bind' => [
                'id' => $id ]
            ]);
    }

        /**
     * Get all active posts (not deleted)
     */
    static function getAll()
    {
        return \Db\Sql\Categories::query()
            ->where( 'id > 0' )
            ->order( 'name asc' )
            ->execute();
    }
}
