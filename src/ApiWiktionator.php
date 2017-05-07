<?php

namespace TzLion\Wiktionator;

class ApiWiktionator extends Wiktionator
{
    public function getWordPage( $word )
    {
        $xml = file_get_contents ("http://en.wiktionary.org/w/api.php?rawcontinue=iguess&format=xml&action=query&titles=".urlencode(strtolower($word))."&rvprop=content&prop=revisions&redirects=1" );
        $stuff = simplexml_load_string($xml);
        return (string)$stuff->query->pages->page->revisions->rev;
    }

    public function isWordInCategory($word, $category)
    {
        $category = urlencode( str_replace( " ", "_", $category ) );
        $url = "http://en.wiktionary.org/w/api.php?action=query&prop=categories&format=json" .
            "&clcategories=Category%3A".($category)."&titles=".urlencode($word);
        $query = json_decode( file_get_contents($url),true);
        $pg = reset($query["query"]["pages"]);
        if ( isset( $pg["categories"] )) return true;
        return false;
    }

    public function getWordCategories($word)
    {
        $url = "https://en.wiktionary.org/w/api.php?action=query&prop=categories&format=json&cllimit=500&titles=" . urlencode( $word );
        $query = json_decode( file_get_contents($url),true);
        $page = reset($query["query"]["pages"]);
        return $page['categories'];
    }

    public function getRandomWordInCategory($category)
    {
        $category = urlencode( str_replace( " ", "_", $category ) );
        $sortkey = $this->generateRandomSortKey();
        $url = "http://en.wiktionary.org/w/api.php?action=query&list=categorymembers&format=json" .
            "&cmtitle=Category%3A{$category}&cmprop=title&cmnamespace=0&cmtype=page&cmlimit=50&cmsort=sortkey" .
            "&cmstartsortkeyprefix=" . urlencode($sortkey);

        $words = json_decode( file_get_contents($url),true);
        $cm = $words['query']['categorymembers'];

        return Util::randomFromArray($cm)['title'];
    }

    public function wordExistsInLanguage($word, $lang='en')
    {
        throw new \Exception( "Not implemented here" );
    }

    private function generateRandomSortkey()
    {
        $chars = "qwweerrttyyuuiiooppaassddffgghhjjkkllzxccvvbbnnmm";
        $chars = "$0123456789" . $chars. $chars . $chars . $chars . $chars . $chars;
        $rw = Util::randomCharFromString($chars);

        $chars = "qwertyuiopasdfghjklzxcvbnmaaaaaeeeeeiiiiiooooouuuuu -";
        $chars = $chars. $chars . $chars . $chars . $chars . $chars .
            $chars. $chars . $chars . $chars . $chars . $chars .
            $chars. $chars . $chars . $chars . $chars . $chars .
            "0123456789";
        for($x=0;$x<10;$x++) $rw .= Util::randomCharFromString($chars);

        $rw = substr($rw,0,mt_rand(4,11));

        return $rw;
    }
}