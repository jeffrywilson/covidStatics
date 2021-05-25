<?php

namespace App;

class Helper
{
    public static function iso3_code($key)
    {
        return config('iso3.'.$key);
    }

    public static function get_http_response_code($url) {
        try{
            $headers = get_headers($url);
            return substr($headers[0], 9, 3);
        } catch(\Exception $e) {
            return "";
        }
    }
}