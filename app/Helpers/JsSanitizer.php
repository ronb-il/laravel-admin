<?php
/**
 * Created by PhpStorm.
 * User: Amir
 * Date: 4/1/2016
 * Time: 2:18 PM
 */

namespace App\Helpers;


class JsSanitizer
{
    public function jsSanitizer(){
        return $this;
    }

    public static function sanitizeString($string){
        $string =  str_replace("'", "\\'", $string);
        return str_replace("\n", "\\n", $string);
    }

    public static function santizieHtmlString($string){
        $string =  str_replace("<", "&lt;", $string);
        $string =  str_replace(">", "&gt;", $string);
        return str_replace("\"", "&quot;", $string);
    }

}
