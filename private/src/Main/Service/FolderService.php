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

class FolderService extends BaseService {
    protected $fields = ["name", "detail", "thumb"];

    public function __construct($ctx){
        $this->setContext($ctx);

        $this->db = DB::getDB();
        $this->collection = $this->db->node;
    }

    public function get($id){
        $id = MongoHelper::mongoId($id);
        $entity = $this->collection->findOne(['_id'=> $id, 'type'=> 'folder'], ['name', 'detail', 'thumb']);
        if(is_null($entity)){
            return ResponseHelper::notFound();
        }
        $entity['thumb'] = Image::load($entity['thumb'])->toArrayResponse();

        MongoHelper::standardIdEntity($entity);
        return $entity;
    }

    public function add($params, $root = null){
        $v = new Validator($params);
        $v->rule('required', ['name', 'detail', 'thumb']);

        if(!$v->validate()){
            return ResponseHelper::validateError($v->errors());
        }

        $insert = ArrayHelper::filterKey(['name', 'detail', 'thumb'], $params);

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
        $insert['thumb'] = Image::upload($insert['thumb'])->toArray();
        $insert['type'] = 'folder';

        if(!is_null($root)){
            $insert['root'] = $root;
        }

        $this->collection->insert($insert);
        return $this->get($insert['_id']);
    }

    public function edit($id, $params){
        $id = MongoHelper::mongoId($id);
        $condition = ['_id'=> $id, 'type'=> 'folder'];
        $entity = $this->collection->findOne($condition, ['name', 'detail', 'thumb']);
        if(is_null($entity)){
            return ResponseHelper::notFound();
        }

        $set = ArrayHelper::filterKey(['name', 'detail', 'thumb'], $params);
        if(isset($params['parent_id'])){
            $parentId = MongoHelper::mongoId($params['parent_id']);
            if($this->collection->count(['_id'=> $parentId, 'type'=> 'folder']) == 0){
                return ResponseHelper::notFound('Not found parent folder');
            }
            $set['parent'] = ['id'=> $parentId];
        }
        if(isset($set['thumb'])){
            $set['thumb'] = Image::upload($set['thumb'])->toArray();
        }

        if(count($set) > 0){
            $this->collection->update(['_id'=> $id, 'type'=> 'folder'], ['$set'=> $set]);
        }

        return $this->get($id);
    }

    public function delete($id){
        $id = MongoHelper::mongoId($id);
        $condition = ['_id'=> $id, 'type'=> 'folder'];
        $this->collection->remove($condition);
        return ['success'=> true];
    }
}