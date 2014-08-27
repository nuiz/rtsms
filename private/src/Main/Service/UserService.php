<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 8/21/14
 * Time: 3:46 PM
 */

namespace Main\Service;


use Main\DataModel\Image;
use Main\DB;
use Main\Helper\ArrayHelper;
use Main\Helper\MongoHelper;
use Main\Helper\ResponseHelper;
use Valitron\Validator;

class UserService extends BaseService {
    protected $fields = ["username", "email", "password", "gender", "birth_date", "picture", "mobile", "website"];

    public function __construct($ctx){
        $this->setContext($ctx);

        $this->db = DB::getDB();
        $this->collection = $this->db->users;
    }

    public function add($params){
        $allow = ["username", "email", "password", "gender", "birth_date"];
        $entity = ArrayHelper::filterKey($allow, $params);

//        Add rule
//        Validator::addRule('ruleName', function($field, $value, $params = []){
//            if(true)
//                return true;
//            return false;
//        });

        $v = new Validator($entity);
        $v->rule('required', ["username", "email", "password", "gender", "birth_date"]);
//        $v->rule('required', ["username", "email", "password", "gender"]);
        $v->rule('email', ["email"]);
        $v->rule('lengthBetween', 'username', 4, 32);
        $v->rule('lengthBetween', 'password', 4, 32);
        $v->rule('in', 'gender', ['male', 'female']);
        $v->rule('date', 'birth_date');

        if(!$v->validate()) {
            return ResponseHelper::validateError($v->errors());
        }

        if($this->collection->count(['username'=> $entity['username']]) != 0){
            return ResponseHelper::validateError(['username'=> ['Duplicate username']]);
        }

        $entity['password'] = md5($entity['password']);
        $entity['display_name'] = $entity['username'];
        $entity['birth_date'] = new \MongoTimestamp($entity['birth_date']);

        // set website,mobile to ''
        $entity['website'] = null;
        $entity['mobile'] = null;

        $this->collection->insert($entity);
        MongoHelper::standardIdEntity($entity);

        unset($entity['password']);

        return $entity;
    }

    public function edit($id, $params){
        $allow = ["email", "gender", "birth_date", "website", "mobile"];
        $set = ArrayHelper::filterKey($allow, $params);
        $v = new Validator($set);
        $v->rule('email', 'email');
        $v->rule('in', 'gender', ['male', 'female']);
        $v->rule('date', 'birth_date');
        if(!$v->validate()){
            return ResponseHelper::validateError($v->errors());
        }
        if(isset($params['picture'])){
            $img = Image::upload($params['picture']);
            $set['picture'] = $img->toArray();
        }
        $set = ArrayHelper::ArrayGetPath($set);

        if(isset($set['birth_date'])){
            $set['birth_date'] = new \MongoTimestamp(strtotime($set['birth_date']));
        }

        if(count($set)>0){
            $id = MongoHelper::mongoId($id);
            $this->collection->update(['_id'=> $id], ['$set'=> $set]);
        }

        return $this->get($id);
    }

    public function changePassword($id, $params){
        $id = MongoHelper::mongoId($id);

        $v = new Validator($params);
        $v->rule('required', ['new_password', 'old_password']);
        if(!$v->validate()){
            return ResponseHelper::validateError($v->errors());
        }

        $entity = $this->collection->findOne(['_id'=> $id], ['password']);
        if(is_null($entity)){
            return ResponseHelper::notFound();
        }

        if((md5($params['old_password']) != $entity['password']) && isset($entity['password'])){
            return ResponseHelper::validateError(['password'=> ['Password not match']]);
        }
        $this->collection->update(['_id'=> $id], ['password'=> md5($params['password'])]);

        return ['success'=> true];
    }

    public function get($id){
        $id = MongoHelper::mongoId($id);

        $entity = $this->collection->findOne(['_id'=> $id], $this->fields);
        if(is_null($entity)){
            return ResponseHelper::notFound();
        }

        MongoHelper::standardIdEntity($entity);
        $entity['birth_date'] = MongoHelper::timeToInt($entity['birth_date']);
        return $entity;
    }

    public function me($params){
        $v = new Validator($params);
        $v->rule('required', 'access_token');
        if(!$v->validate()){
            return ResponseHelper::validateError($v->errors());
        }

        $tokenEntity = $this->db->access_tokens->findOne(['access_token'=> $params['access_token']]);
        if(is_null($tokenEntity)){
            return ResponseHelper::notAuthorize();
        }

        return $this->get($tokenEntity['_id']);
    }
}