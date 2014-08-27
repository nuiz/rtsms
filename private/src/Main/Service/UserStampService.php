<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 8/25/14
 * Time: 10:41 AM
 */

namespace Main\Service;


use Main\DB;
use Main\Helper\ArrayHelper;
use Main\Helper\MongoHelper;
use Main\Helper\ResponseHelper;
use Valitron\Validator;

class UserStampService extends BaseService {
    protected $fields = ["point", "newest"];

    public function __construct($ctx){
        $this->setContext($ctx);

        $this->db = DB::getDB();
        $this->collection = $this->db->users;
        $this->userHisService = new UserHistoryService($ctx);
    }

    public function get($id){
        $stamp = $this->getStamp($id);
        if(is_null($stamp)){
            return ResponseHelper::notFound();
        }

        return $stamp;
    }

//    public function edit($id, $params){
//        $id = MongoHelper::mongoId($id);
//        $stamp = $this->getStamp($id);
//        if(is_null($stamp)){
//            return ResponseHelper::notFound();
//        }
//
//        $stamp = ArrayHelper::filterKey($this->fields, $params);
//
//        if(count($stamp) > 0){
//            $set = ['stamp'=> $stamp];
//            $set = ArrayHelper::ArrayGetPath($set);
//            $this->collection->update(['_id'=> $id], ['$set'=> $set]);
//        }
//
//        return $this->get($id);
//    }

    public function getStamp($id){
        $id = MongoHelper::mongoId($id);
        $entity = $this->collection->findOne(['_id'=> $id], ['stamp']);
        if(is_null($entity)){
            return null;
        }

        if(isset($entity['stamp'])){
            return $entity['stamp'];
        }

        $stamp = [
            'point'=> 0,
            'newest'=> 0
        ];
        $set = ['stamp'=> $stamp];
        $set = ArrayHelper::ArrayGetPath($set);
        $this->collection->update(['_id'=> $entity['_id']], ['$set'=> $set]);

        return $stamp;
    }

    public function addPoint($id, $params){
        $v = new Validator($params);
        $v->rule('required', ['point', 'password']);
        if(!$v->validate()){
            return ResponseHelper::validateError($v->errors());
        }

        $stamp = $this->getStamp($id);
        if(is_null($stamp)){
            return ResponseHelper::notFound();
        }

        // hard code for password
        if($params['password'] != '123456'){
            return ResponseHelper::validateError(['password'=> 'wrong password']);
        }

        $id = MongoHelper::mongoId($id);

        $stamp['point'] = $stamp['point'] + $params['point'];
        $stamp['newest'] = (int)$params['point'];

        $set = ['stamp'=> $stamp];
        $set = ArrayHelper::ArrayGetPath($set);
        $this->collection->update(['_id'=> $id], ['$set'=> $set]);

        $this->userHisService->add($id, ['message'=> 'ได้ทำการเพิ่มแต้ม '.$params['point'].' แต้ม']);

        return $this->get($id);
    }
}