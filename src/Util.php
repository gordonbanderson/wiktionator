<?php

namespace TzLion\Wiktionator;

class Util
{
    public static function randomCharFromString($str)
    {
        return $str[mt_rand(0,strlen($str)-1)];
    }
    public static function randomFromArray($array)
    {
        return $array[mt_rand(0,count($array)-1)];
    }
}
