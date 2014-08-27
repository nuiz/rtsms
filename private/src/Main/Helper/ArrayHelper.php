<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 7/24/14
 * Time: 5:37 PM
 */

namespace Main\Helper;


class ArrayHelper {
    public static function ArrayGetPath($arr, $oldKey = null){
        $data = array();
        foreach($arr as $key => $value){
            $newKey = $key;
            if(!is_null($oldKey)){
                $newKey = $oldKey.".".$newKey;
            }

            if(is_array($value)){
                $data = array_merge($data, self::ArrayGetPath($value, $newKey));
            }
            else {
                $data[$newKey] = $value;
            }
        }
        return $data;
    }

    public static function filterKey($keys, $params){
//        $allowed = array("phone", "website", "email", "info", "location");
        return array_intersect_key($params, array_flip($keys));
    }
}