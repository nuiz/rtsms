<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 9/3/14
 * Time: 4:59 PM
 */

namespace Main\CTL;
use Main\DB;

/**
 * @Restful
 * @uri /develop
 */
class DevelopCTL extends BaseCTL {
    /**
     * @GET
     * @uri /node/timestamp
     */
    public function nodeTimeStamp(){
        $db = DB::getDB();
        $cursor = $db->node->find();
        $timestamp = time();
        foreach($cursor as $item){
            $id = $item['_id'];
            $timestamp++;
            if(!($item['created_at'] instanceof \MongoTimestamp)){
                $timestamp = new \MongoTimestamp();
                $db->node->update(['_id'=> $id], ['$set'=> ['created_at'=> $timestamp]]);
            }
            if(!($item['updated_at'] instanceof \MongoTimestamp)){
                $timestamp = new \MongoTimestamp();
                $db->node->update(['_id'=> $id], ['$set'=> ['updated_at'=> $timestamp]]);
            }
        }
    }
} 