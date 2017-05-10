<?php

namespace TzLion\Wiktionator;

class ApiWiktionator extends Wiktionator
{
    const EN_WIKTIONARY_API_URL = 'http://en.wiktionary.org/w/api.php';

    private function jsonQuery($queryParts)
    {
        $url = $this->buildQueryUrl('json',$queryParts);
        return json_decode(file_get_contents($url),true);
    }

    private function xmlQuery($queryParts)
    {
        $url = $this->buildQueryUrl('xml',$queryParts);
        return simplexml_load_string(file_get_contents($url));
    }

    private function buildQueryUrl($format,$queryParts)
    {
        $queryParts['action'] = 'query';
        $queryParts['format'] = $format;
        return self::EN_WIKTIONARY_API_URL . '?' . http_build_query($queryParts);
    }

    public function getWordPage( $word )
    {
        $queryParts = [ 'rawcontinue' => 'iguess', 'titles' => strtolower($word), 'rvprop' => 'content',
                        'prop' => 'revisions', 'redirect' => 1 ];
        $stuff = $this->xmlQuery($queryParts);
        return (string)$stuff->query->pages->page->revisions->rev;
    }

    public function isWordInCategory($word, $category)
    {
        $category = str_replace( " ", "_", $category );
        $queryParts = ['prop'=>'categories','clcategories'=>'Category:'.$category,'titles'=>$word];
        $query = $this->jsonQuery($queryParts)['query'];
        $pg = reset($query["pages"]);
        if ( isset( $pg["categories"] )) return true;
        return false;
    }

    public function getWordCategories($word)
    {
        $queryParts = [ 'prop' => 'categories', 'cllimit' => 500, 'titles' => $word ];
        $query = $this->jsonQuery($queryParts)['query'];
        $page = reset($query["pages"]);
        if ( !isset($page['categories']) ) {
            return null;
        }
        $catCallback = function($item) {
            return preg_replace('~^Category:~','',$item['title']);
        };
        return array_map($catCallback,$page['categories']);
    }

    public function getRandomWordInCategory($category)
    {
        $category = str_replace( " ", "_", $category );
        $sortkey = $this->generateRandomSortKey();
        $queryParts = ['list'=>'categorymembers','cmtitle'=>'Category:'.$category,'cmprop'=>'title','cmnamespace'=>0,
                       'cmtype'=>'page','cmlimit'=>500,'cmsort'=>'sortkey','cmstartsortkeyprefix'=>$sortkey];
        $words = $this->jsonQuery($queryParts)['query'];
        $cm = $words['categorymembers'];

        return Util::randomFromArray($cm)['title'];
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