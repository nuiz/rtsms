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
use Main\Helper\ArrayHelper;
use Main\Helper\MongoHelper;
use Main\Helper\ResponseHelper;
use Valitron\Validator;

class ActivityService extends BaseService {
    protected $fields = ['name', 'message', 'datetime', 'thumb'];

    public function __construct($ctx){
        $this->setContext($ctx);

        $this->db = DB::getDB();
        $this->collection = $this->db->activity;
    }

    public function add($params){
        $v = new Validator($params);
        $v->rule('required', ['name', 'message', 'datetime', 'thumb']);
        $v->rule('date', ['datetime']);

        if(!$v->validate()){
            return ResponseHelper::validateError($v->errors());
        }

        $insert = ArrayHelper::filterKey(['name', 'message', 'datetime', 'thumb'], $params);
        $insert['thumb'] = Image::upload($params['thumb'])->toArray();
        $insert['datetime'] = new \MongoDate($insert['datetime']);

        // insert created_at, updated_at
        $insert['created_at'] = new \MongoDate();
        $insert['updated_at'] = $insert['created_at'];

        $this->collection->insert($insert);

        return $this->get($insert['_id']);
    }

    public function edit($id, $params){
        $id = MongoHelper::mongoId($id);

        $v = new Validator($params);
        $v->rule('date', ['datetime']);

        if(!$v->validate()){
            return ResponseHelper::validateError($v->errors());
        }

        $set = ArrayHelper::filterKey(['name', 'message', 'datetime', 'thumb'], $params);
        if(isset($set['thumb'])){
            $set['thumb'] = Image::upload($params['thumb'])->toArray();
        }
        if(isset($set['datetime'])){
            $set['datetime'] = new \MongoDate($set['datetime']);
        }
        if(count($set) > 0){
            // update updated_at
            $set['updated_at'] = new \MongoDate();
            $this->collection->update(['_id'=> $id], ['$set'=> $set]);
        }
        return $this->get($id);
    }

    public function get($id){
        $id = MongoHelper::mongoId($id);

        $entity = $this->collection->findOne(['_id'=> $id], $this->fields);
        $entity['thumb'] = Image::load($entity['thumb'])->toArrayResponse();
        MongoHelper::standardIdEntity($entity);
        return $entity;
    }

    public function gets($options = array()){
        $default = array(
            "page"=> 1,
            "limit"=> 15
        );
        $options = array_merge($default, $options);

        $skip = ($options['page']-1)*$options['limit'];
        $condition = [];

        $cursor = $this->collection
            ->find($condition, $this->fields)
            ->limit((int)$options['limit'])
            ->skip((int)$skip)
            ->sort([$options['created_at']=> -1]);

        $data = [];
        foreach($cursor as $item){
            $item['thumb'] = Image::load($item['thumb'])->toArrayResponse();
            MongoHelper::standardIdEntity($item);
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

        return ['success'=> true];
    }
}