<?php
/**
 * Created by PhpStorm.
 * User: Amir
 * Date: 10/2/2016
 * Time: 4:42 PM
 */

namespace App\Helpers;


class EnvironmentUtilities
{
    public static function getSiteURL() {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $domainName = $_SERVER['HTTP_HOST'];
        return $protocol.$domainName;
    }
}
