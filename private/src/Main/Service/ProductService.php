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
use Main\Helper\NodeHelper;
use Main\Helper\ResponseHelper;
use Main\Helper\URL;
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
            ['pictures'=> ['$slice'=> [0, 1]], 'name'=> 1, 'detail'=> 1, 'price'=> 1, 'parent'=> 1]);
        if(is_null($entity)){
            return ResponseHelper::notFound();
        }
        $entity['thumb'] = Image::load($entity['pictures'][0])->toArrayResponse();
        unset($entity['pictures']);
//        foreach($entity['pictures'] as $key=> $value){
//            $entity['pictures'][$key] = Image::load($value)->toArrayResponse();
//        }

        // set parent_id
        if(!is_null($entity['parent'])){
            $entity['parent_id'] = MongoHelper::standardId($entity['parent']['id']);
        }
        unset($entity['parent']);

        MongoHelper::standardIdEntity($entity);
        $entity['node'] = NodeHelper::product($entity['id']);
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
        $insert['created_at'] = new \MongoTimestamp();
        $insert['updated_at'] = $insert['created_at'];

        $match = ['parent'=> null];
        if(!is_null($insert['parent'])){
            $match = ['parent.id'=> $insert['parent']['id']];
        }
        $agg = $this->collection->aggregate([
            ['$match'=> $match],
            ['$group'=> ['_id'=> null, 'max'=> ['$max'=> '$seq']]]
        ]);
        $insert['seq'] = (int)@$agg['result'][0]['max'] + 1;
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
        $entity = $this->collection->findOne($condition, ['name', 'detail', 'price']);
        if(is_null($entity)){
            return ResponseHelper::notFound();
        }

        $set = ArrayHelper::filterKey(['name', 'detail', 'price'], $params);
        if(isset($set['price'])){
            $set['price'] = (int)$set['price'];
        }
        if(isset($params['parent_id'])){
            $parentId = MongoHelper::mongoId($params['parent_id']);
            if($this->collection->count(['_id'=> $parentId, 'type'=> 'product']) == 0){
                return ResponseHelper::notFound('Not found parent folder');
            }
            $set['parent'] = ['id'=> $parentId];
        }

        if(count($set) > 0){
            $this->collection->update($condition, ['$set'=> $set]);
        }

        return $this->get($id);
    }

    public function getPictures($id, $params){
        $id = MongoHelper::mongoId($id);
        if($this->collection->count(['_id'=> $id, 'type'=> 'product']) == 0){
            return ResponseHelper::notFound();
        }

//        $this->collection->update(['_id'=> $id], ['$setOnInsert'=> ['history'=> []]], ['upsert'=> true]);

        $default = ["page"=> 1, "limit"=> 15];
        $options = array_merge($default, $params);
        $arg = $this->collection->aggregate([
            ['$match'=> ['_id'=> $id, 'type'=> 'product']],
            ['$project'=> ['pictures'=> 1]],
            ['$unwind'=> '$pictures'],
            ['$group'=> ['_id'=> null, 'total'=> ['$sum'=> 1]]]
        ]);

        $total = (int)@$arg['result'][0]['total'];
        $limit = (int)$options['limit'];
        $page = (int)$options['page'];

        $slice = MongoHelper::createSlice($page, $limit, $total);

        if($slice[1] == 0){
            $data = [];
        }
        else {
            $entity = $this->collection->findOne(['_id'=> $id, 'type'=> 'product'], ['pictures'=> ['$slice'=> $slice]]);
            $data = Image::loads($entity['pictures'])->toArrayResponse();
        }

        // reverse data
        $data = array_reverse($data);

        return array(
            'length'=> count($data),
            'total'=> $total,
            'data'=> $data,
            'paging'=> array(
                'page'=> (int)$options['page'],
                'limit'=> (int)$options['limit']
            )
        );
    }

    public function addPictures($id, $params){
        $id = MongoHelper::mongoId($id);
        $v = new Validator($params);
        $v->rule('required', ['pictures']);
        if(!$v->validate()){
            return ResponseHelper::validateError($v->errors());
        }

        if($this->collection->count(['_id'=> $id, 'type'=> 'product']) == 0){
            return ResponseHelper::notFound();
        }

        $res = [];
        foreach($params['pictures'] as $value){
            $img = Image::upload($value);
            $this->collection->update(['_id'=> $id, 'type'=> 'product'], ['$push'=> ['pictures'=> $img->toArray()]]);
            $res[] = $img->toArrayResponse();
        }

        return $res;
    }

    public function deletePictures($id, $params){
        $id = MongoHelper::mongoId($id);
        $v = new Validator($params);
        $v->rule('required', ['id']);
        if(!$v->validate()){
            return ResponseHelper::validateError($v->errors());
        }

        if($this->collection->count(['_id'=> $id, 'type'=> 'product']) == 0){
            return ResponseHelper::notFound();
        }

        $res = [];
        foreach($params['id'] as $value){
            $arg = $this->collection->aggregate([
                ['$match'=> ['_id'=> $id, 'type'=> 'product']],
                ['$project'=> ['pictures'=> 1]],
                ['$unwind'=> '$pictures'],
                ['$group'=> ['_id'=> null, 'total'=> ['$sum'=> 1]]]
            ]);

            $total = (int)@$arg['result'][0]['total'];
            if($total==1){
                break;
            }

            $this->collection->update(['_id'=> $id, 'type'=> 'product'], ['$pull'=> ['pictures'=> ['id'=> $value]]]);
            $res[] = $value;
        }

        return $res;
    }

    public function delete($id){
        $id = MongoHelper::mongoId($id);
        $condition = ['_id'=> $id, 'type'=> 'product'];
        $this->collection->remove($condition);
        return ['success'=> true];
    }
}