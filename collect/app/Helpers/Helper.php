<?php

namespace App\Helpers;

use Route;

class Helper
{
    static function activeMenu($uri = '', $headercolor = false)
    {
        $active = '';
        if (Route::is($uri)) { 
            $active = 'active';
            if ($headercolor) $active = 'header2';
        }
        return $active;
    }
    static function getPageFromURL($uri = '')
    {
        if ($uri) {
            $arr = explode('=', $uri);
            if (isset($arr[1]))
                return $arr[1];
        }
    }
    static function getUserNM($UNMs = '')
    {
        if ($UNMs) {
           return substr($UNMs, 0, -1);
        }
    }
}
