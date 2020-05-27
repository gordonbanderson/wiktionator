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


    /**
     * @param string $word a word, such as 'dog'
     * @return array|mixed|null
     * @throws \Exception
     */
    public function getWordCategories($word)
    {
        $wordId = $this->getWordId( $word );
        error_log('WORD ID: ' . $word . ' --> ' . $wordId);
        if ( !$wordId ) {
            return null;
        }
        return $this->db->fetch( NeoDb::F_COLUMN, "SELECT cl_to AS title FROM categorylinks LEFT JOIN category ON cat_id = cl_to WHERE cl_from = ?", [ $wordId ] );
    }


    /**
     * @param string $category
     * @return string|string[]
     * @throws \Exception
     */
    public function getRandomWordInCategory($category)
    {
        $wordId = $this->db->fetch( NeoDb::F_ONE, "SELECT cl_from FROM categorylinks WHERE cl_type = 'page' AND cl_to = ? ORDER BY RAND() LIMIT 1", [ $category ] );
        $res = $this->getWordTitle( $wordId );
        return $res;
    }


    /**
     * @param string $category
     * @return string|string[]
     * @throws \Exception
     */
    public function getRandomWordsInCategory($category, $limit = 10)
    {
        $query = "SELECT cl_from FROM categorylinks WHERE cl_type = 'page' AND cl_to = ? ORDER BY RAND() LIMIT ? ";
        $wordIds = $this->db->fetch( NeoDb::F_COLUMN, $query, [ $category, $limit ] );
        $words = [];
        foreach($wordIds as $wordId) {
            $words[] = $this->getWordTitle($wordId);
        }
        return $words;
    }

    public function getWordsInCategory($category, $limit = 10, $offset=0)
    {
        $query = "SELECT cl_from FROM categorylinks WHERE cl_type = 'page' AND cl_to = ? LIMIT ? OFFSET ?";
        $wordIds = $this->db->fetch( NeoDb::F_COLUMN, $query, [ $category, $limit, $offset ] );
        $words = [];
        foreach($wordIds as $wordId) {
            $words[] = $this->getWordTitle($wordId);
        }
        return $words;
    }


    /**
     * Words are modelled as a table called page, the word is the page_title field
     *
     * @param string $word
     * @return array|mixed|null
     * @throws \Exception
     */
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