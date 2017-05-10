<?php

namespace TzLion\Wiktionator;

abstract class Wiktionator {

    /**
     * @param array|null $dbConnectionDetails
     * @return Wiktionator
     */
    public static function getInstance( $dbConnectionDetails = null )
    {
        if ( $dbConnectionDetails && DbWiktionator::canConnect( $dbConnectionDetails ) ) {
            return new DbWiktionator( $dbConnectionDetails );
        } else {
            return new ApiWiktionator();
        }
    }

    private function __construct()
    {

    }

    /**
     * @param string $word
     * @return string
     */
    public abstract function getWordPage( $word );

    /**
     * @param string $word
     * @param string $category
     * @return bool
     */
    public abstract function isWordInCategory( $word, $category );

    /**
     * @param string $word
     * @return array
     */
    public abstract function getWordCategories( $word );

    /**
     * @param string $category
     * @return string
     */
    public abstract function getRandomWordInCategory( $category );

    /**
     * @param string $word
     * @param string $lang
     * @return bool
     */
    public function wordExistsInLanguage( $word, $lang = 'English' )
    {
        $cats = $this->getWordCategories($word) ?: [];
        $langName = str_replace(' ','[ _]',preg_quote($lang));
        $langRegex = "/^(Category:)?$langName/";
        foreach( $cats as $cat ) {
            if ( preg_match($langRegex, $cat) ) {
                return true;
            }
        }
        return false;
    }
}