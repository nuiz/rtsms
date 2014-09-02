<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 9/2/14
 * Time: 12:20 PM
 */

namespace Main\Helper;

class NodeHelper {
    public static function folder($id){
        $id = MongoHelper::standardId($id);
        return [
            'children'=> URL::absolute('/node/'.$id.'/children')
        ];
    }

    public static function product($id){
        $id = MongoHelper::standardId($id);
        return [
            'pictures'=> URL::absolute('/product/'.$id.'/picture'),
            'share'=> URL::share('/product/'.$id)
        ];
    }

    public static function gallery($id){
        $id = MongoHelper::standardId($id);
        return [
            'pictures'=> URL::absolute('/gallery/'.$id.'/picture'),
            'share'=> URL::share('/gallery/'.$id)
        ];
    }
}