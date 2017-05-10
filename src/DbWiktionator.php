<?php

namespace TzLion\Wiktionator;

use TzLion\NeoDb\NeoDb;

class DbWiktionator extends Wiktionator
{
    private $db;

    protected function __construct( $dbConnectionDetails )
    {
        $this->db = $this->connect( $dbConnectionDetails );
    }

    private static function connect( $dbConnectionDetails )
    {
        list( $host, $user, $pass, $name ) = $dbConnectionDetails;
        return new NeoDb( $host, $user, $pass, $name );
    }

    public static function canConnect( $dbConnectionDetails )
    {
        try {
            self::connect( $dbConnectionDetails );
        } catch ( \Exception $e ) {
            return false;
        }
        return true;
    }

    public function getWordPage( $word )
    {
        throw new \Exception( "Not implemented here" );
    }

    public function isWordInCategory($word, $category)
    {
        $catId = $this->getCatId( $category );
        $wordId = $this->getWordId( $word );
        return (bool) $this->db->fetch( NeoDb::F_ONE, "SELECT * FROM categorylinks WHERE cl_page = ? AND cl_cat = ?", [ $wordId, $catId ] );
    }

    public function getWordCategories($word)
    {
        $wordId = $this->getWordId( $word );
        if ( !$wordId ) {
            return null;
        }
        return $this->db->fetch( NeoDb::F_ALL, "SELECT cat_title AS title FROM categorylinks LEFT JOIN category ON cat_id = cl_cat WHERE cl_page = ?", [ $wordId ] );
    }

    public function getRandomWordInCategory($category)
    {
        $catId = $this->getCatId( $category );
        $wordId = $this->db->fetch( NeoDb::F_ONE, "SELECT cl_page FROM categorylinks WHERE cl_type = 'page' AND cl_cat = ? ORDER BY RAND() LIMIT 1", [ $catId ] );
        $res = $this->getWordTitle( $wordId );
        return $res;
    }

    private function getWordId( $word )
    {
        return $this->db->fetch( NeoDb::F_ONE, "SELECT page_id FROM page WHERE page_title = ? AND page_namespace = 0", [ $word ] );
    }

    private function getCatId( $cat )
    {
        $cat = str_replace( " ", "_", $cat );
        return $this->db->fetch( NeoDb::F_ONE, "SELECT cat_id FROM category WHERE cat_title = ?", [ $cat ] );
    }

    private function getWordTitle( $wordId )
    {
        $word = $this->db->fetch( NeoDb::F_ONE, "SELECT page_title FROM page WHERE page_id = ?", [ $wordId ] );
        return str_replace( "_", " ", $word );
    }

    private function getCatTitle( $catId )
    {
        $cat = $this->db->fetch( NeoDb::F_ONE, "SELECT cat_title FROM category WHERE cat_id = ?", [ $catId ] );
        return str_replace( "_", " ", $cat );
    }
}