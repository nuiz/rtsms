<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 8/29/14
 * Time: 4:56 PM
 */

namespace Main\Service;


use Main\DataModel\Image;
use Main\DB;
use Main\Helper\ArrayHelper;
use Main\Helper\MongoHelper;
use Main\Helper\ResponseHelper;
use Valitron\Validator;

class ProductService extends BaseService {
    protected $fields = ["name", "detail", "pictures"];

    public function __construct($ctx){
        $this->setContext($ctx);

        $this->db = DB::getDB();
        $this->collection = $this->db->node;
    }

    public function get($id){
        $id = MongoHelper::mongoId($id);
        $entity = $this->collection->findOne(['_id'=> $id, 'type'=> 'product'],
            ['pictures'=> ['$slice'=> [0, 1]], 'name'=> 1, 'detail'=> 1, 'price'=> 1]);
        if(is_null($entity)){
            return ResponseHelper::notFound();
        }
        $entity['thumb'] = Image::load($entity['pictures'][0])->toArrayResponse();
        unset($entity['pictures']);
//        foreach($entity['pictures'] as $key=> $value){
//            $entity['pictures'][$key] = Image::load($value)->toArrayResponse();
//        }

        MongoHelper::standardIdEntity($entity);
        return $entity;
    }

    public function add($params){
        $v = new Validator($params);
        $v->rule('required', ['name', 'detail', 'pictures', 'price']);

        if(!$v->validate()){
            return ResponseHelper::validateError($v->errors());
        }
        if(!is_array($params['pictures'])){
            return ResponseHelper::validateError(['pictures'=> ['pictures must be array']]);
        }

        $insert = ArrayHelper::filterKey(['name', 'detail', 'pictures', 'price'], $params);

        if(empty($params['parent_id'])){
            $insert['parent'] = null;
        }
        else{
            $parentId = MongoHelper::mongoId($params['parent_id']);
            if($this->collection->count(['_id'=> $parentId, 'type'=> 'folder']) == 0){
                return ResponseHelper::notFound('Not found parent folder');
            }
            $insert['parent'] = ['id'=> $parentId];
        }

        // insert created_at, updated_at
        $insert['created_at'] = new \MongoDate();
        $insert['updated_at'] = new \MongoDate();

        $match = ['parent'=> null];
        if(!is_null($insert['parent'])){
            $match = ['parent.id'=> $insert['parent']['id']];
        }
        $agg = $this->collection->aggregate([
            ['$match'=> $match],
            ['$group'=> ['_id'=> null, 'max'=> ['$max'=> '$seq']]]
        ]);
        $insert['seq'] = (int)@$agg['result'][0]['max'] + 1;
//        $insert['thumb'] = Image::upload($insert['thumb'])->toArray();
        $insert['type'] = 'product';
        $insert['price'] = (int)$insert['price'];

        foreach($insert['pictures'] as $key=> $value){
            $insert['pictures'][$key] = Image::upload($value)->toArray();
        }

        $this->collection->insert($insert);
        return $this->get($insert['_id']);
    }

    public function edit($id, $params){
        $id = MongoHelper::mongoId($id);
        $condition = ['_id'=> $id, 'type'=> 'product'];
        $entity = $this->collection->findOne($condition, ['name', 'detail', 'thumb']);
        if(is_null($entity)){
            return ResponseHelper::notFound();
        }

        $set = ArrayHelper::filterKey(['name', 'detail', 'thumb'], $params);
        if(isset($params['parent_id'])){
            $parentId = MongoHelper::mongoId($params['parent_id']);
            if($this->collection->count(['_id'=> $parentId, 'type'=> 'product']) == 0){
                return ResponseHelper::notFound('Not found parent folder');
            }
            $set['parent'] = ['id'=> $parentId];
        }
        if(isset($set['thumb'])){
            $set['thumb'] = Image::upload($set['thumb'])->toArray();
        }

        if(count($set) > 0){
            $this->collection->update(['_id'=> $id, 'type'=> 'product'], ['$set'=> $set]);
        }

        return $this->get($id);
    }

    public function delete($id){
        $id = MongoHelper::mongoId($id);
        $condition = ['_id'=> $id, 'type'=> 'product'];
        $this->collection->remove($condition);
        return ['success'=> true];
    }
}