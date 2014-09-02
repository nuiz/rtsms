<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 9/2/14
 * Time: 4:44 AM
 */

namespace Main\Service;


use Main\DataModel\Image;
use Main\DB;
use Main\Helper\MongoHelper;
use Main\Helper\ResponseHelper;

class FeedService extends BaseService {
    protected static $instance = null;
    protected $fields = ['type', 'created_at'];

    public function __construct($ctx){
        $this->setContext($ctx);

        $this->db = DB::getDB();
        $this->collection = $this->db->feed;
    }

    public static function instance($ctx){
        if(is_null(self::$instance)){
            self::$instance = new self($ctx);
        }
        return self::$instance;
    }

    public function add($id, $type, $create_at){
        $id = MongoHelper::mongoId($id);
        $entity = ['_id'=> $id, 'type'=> $type, 'created_at'=> $create_at];
        $this->collection->insert($entity);

        return $entity;
    }

    public function remove($id, $type){
        $id = MongoHelper::mongoId($id);
        $this->collection->remove(['_id'=> $id, 'type'=> $type]);

        return true;
    }

    public function gets($options = array()){
        $default = array(
            "page"=> 1,
            "limit"=> 15,
        );
        $options = array_merge($default, $options);

        $skip = ($options['page']-1)*$options['limit'];
        $condition = [];

        $cursor = $this->collection
            ->find($condition, $this->fields)
            ->limit((int)$options['limit'])
            ->skip((int)$skip)
            ->sort(['created_at'=> -1]);

        $data = [];
        foreach($cursor as $item){
            if($item['type']=='news'){
                $item = NewsService::instance($this->getContext())->get($item['_id']);
            }
            if($item['type']=='activity'){
                $item = ActivityService::instance($this->getContext())->get($item['_id']);
            }
            $data[] = $item;
        }

        $total = $this->collection->count($condition);
        $length = $cursor->count(true);

        return array(
            'length'=> $length,
            'total'=> $total,
            'data'=> $data,
            'paging'=> [
                'page'=> (int)$options['page'],
                'limit'=> (int)$options['limit']
            ]
        );
    }

    public function delete($id){
        $id = MongoHelper::mongoId($id);
        $this->collection->remove(['_id'=> $id]);

        return true;
    }
}