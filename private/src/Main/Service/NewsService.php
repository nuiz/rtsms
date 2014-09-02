<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 9/2/14
 * Time: 12:31 PM
 */

namespace Main\Service;


use Main\DataModel\Image;
use Main\DB;
use Main\Helper\ArrayHelper;
use Main\Helper\MongoHelper;
use Main\Helper\ResponseHelper;
use Valitron\Validator;

class NewsService extends BaseService {
    protected static $instance = null;
    protected $fields = ["name", "detail", "thumb"];

    public function __construct($ctx){
        $this->setContext($ctx);

        $this->db = DB::getDB();
        $this->collection = $this->db->news;
    }

    public static function instance($ctx){
        if(is_null(self::$instance)){
            self::$instance = new self($ctx);
        }
        return self::$instance;
    }

    public function add($params){
        $v = new Validator($params);
        $v->rule('required', ['name', 'detail', 'thumb']);

        if(!$v->validate()){
            return ResponseHelper::validateError($v->errors());
        }

        $insert = ArrayHelper::filterKey(['name', 'detail', 'thumb'], $params);
        $insert['thumb'] = Image::upload($params['thumb'])->toArray();

        // insert created_at, updated_at
        $insert['created_at'] = new \MongoTimestamp();
        $insert['updated_at'] = $insert['created_at'];

        $this->collection->insert($insert);
        FeedService::instance($this->getContext())->add($insert['_id'], 'news', $insert['created_at']);

        return $this->get($insert['_id']);
    }

    public function gets($params){
        $default = array(
            "page"=> 1,
            "limit"=> 15
        );
        $options = array_merge($default, $params);

        $skip = ($options['page']-1)*$options['limit'];
        $condition = [];

        $cursor = $this->collection
            ->find($condition, $this->fields)
            ->limit((int)$options['limit'])
            ->skip((int)$skip)
            ->sort(['created_at'=> -1]);

        $data = [];
        foreach($cursor as $item){
            $item['thumb'] = Image::load($item['thumb'])->toArrayResponse();
            MongoHelper::standardIdEntity($item);
            $data[] = $item;
        }

        $total = $this->collection->count($condition);
        $length = $cursor->count(true);

        return [
            'length'=> $length,
            'total'=> $total,
            'data'=> $data,
            'paging'=> [
                'page'=> (int)$options['page'],
                'limit'=> (int)$options['limit']
            ]
        ];
    }

    public function get($id){
        $id = MongoHelper::mongoId($id);

        $entity = $this->collection->findOne(['_id'=> $id], $this->fields);
        $entity['thumb'] = Image::load($entity['thumb'])->toArrayResponse();
        MongoHelper::standardIdEntity($entity);
        return $entity;
    }

    public function edit($id, $params){
        $id = MongoHelper::mongoId($id);

        $set = ArrayHelper::filterKey(['name', 'detail', 'thumb'], $params);
        if(isset($set['thumb'])){
            $set['thumb'] = Image::upload($params['thumb'])->toArray();
        }
        if(count($set) > 0){
            // update updated_at
            $set['updated_at'] = new \MongoTimestamp();
            $this->collection->update(['_id'=> $id], ['$set'=> $set]);
        }
        return $this->get($id);
    }

    public function delete($id){
        $id = MongoHelper::mongoId($id);
        $this->collection->remove(['_id'=> $id]);
        FeedService::instance($this->getContext())->delete($id);

        return ['success'=> true];
    }
}