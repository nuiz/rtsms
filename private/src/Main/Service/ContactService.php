<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 7/16/14
 * Time: 3:58 PM
 */

namespace Main\Service;


use Main\Context\Context;
use Main\Context\ContextInterface;
use Main\DB;
use Main\Helper\ArrayHelper;

class ContactService extends BaseService {
    private $collection;
    protected static $instance = null;

    /** @return self */
    public static function instance() {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    protected function __construct(){
        $db = DB::getDB();
        $contacts = $db->contacts;
        $this->collection = $contacts;
    }

    public function get(ContextInterface $ctx = null){
        if(is_null($ctx))
            $ctx = $this->getContext();

        $entity = $this->collection->findOne();
        if(is_null($entity)){
            $entity = $this->insertWhenEmpty($ctx);
        }

        return $entity;
    }

    public function edit(array $params, ContextInterface $ctx = null){
        if(is_null($ctx))
            $ctx = $this->getContext();

        $entity = $this->collection->findOne();
        if(is_null($entity)){
            $entity = $this->insertWhenEmpty($ctx);
        }

        $allowed = ["phone", "website", "email", "info", "location"];
        $set = ArrayHelper::filterKey($allowed, $params);
        if(count($set)==0){
            return $entity;
        }
        if(isset($set['location'])){
            $allowed = ["lat", "lng"];
            $set['location'] = ArrayHelper::filterKey($allowed, $set["location"]);
        }

        $set = ArrayHelper::ArrayGetPath($set);

        $this->collection->update(array("_id"=> $entity['_id']), array('$set'=> $set));

        return $this->collection->findOne();
    }

    private function insertWhenEmpty(ContextInterface $ctx = null){
        if(is_null($ctx))
            $ctx = $this->getContext();

        $entity = [
            "phone"=> "088-888-8888",
            "website"=> "http://www.example.com",
            "email"=> "example@example.com",
            "info"=> "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.",
            "location"=> [
                "lat"=> "18.8100291",
                "lng"=> "99.0086707"
            ]
        ];

        $this->collection->insert($entity);
        return $entity;
    }
}