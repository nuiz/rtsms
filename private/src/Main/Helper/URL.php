<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 7/19/14
 * Time: 4:47 PM
 */

namespace Main\Helper;


class URL {
    public static function absolute($url){
        return "http://192.168.0.111/rtsms".$url;
    }

    public static function share($url){
        return "http://share".$url;
    }
}