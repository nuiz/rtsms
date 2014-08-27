<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 8/22/14
 * Time: 10:46 AM
 */

namespace Main\Service;


use Main\DataModel\Image;
use Main\DB;
use Main\Helper\MongoHelper;
use Main\Helper\ResponseHelper;
use Valitron\Validator;

class OAuthService extends BaseService {
    public function __construct($ctx){
        $this->setContext($ctx);

        $this->db = DB::getDB();
        $this->collection = $this->db->access_tokens;
    }

    public function loginFacebook($params){
        $v = new Validator($params);
        $v->rule('required', 'facebook_token');

        if(!$v->validate()) {
            return ResponseHelper::validateError($v->errors());
        }

        \Unirest::verifyPeer(false);
        $f = \Unirest::get("https://graph.facebook.com/me?access_token=".$params['facebook_token']);
        $fData = json_decode(json_encode($f->body), true);

        if(isset($fData['error'])){
            return ResponseHelper::notAuthorize();
        }

        $v = new Validator($fData);
        $v->rule('required', ['id', 'gender', 'email', 'name']);

        if(!$v->validate()) {
            return ResponseHelper::validateError($v->errors());
        }

        $entity = $this->db->users->findOne(['fb_id'=> $fData['id']]);
        if(is_null($entity)){
            $entity = $this->createUser($fData);
        }

        $tokenEntity = $this->getTokenByUserId($entity['_id']);

        MongoHelper::removeId($tokenEntity);
        return $tokenEntity;
    }

    public function loginPassword($params){
        $v = new Validator($params);
        $v->rule('required', ['username', 'password']);
        if(!$v->validate()) {
            return ResponseHelper::validateError($v->errors());
        }
        $entity = $this->db->users->findOne(['username'=> $params['username']]);

        if(is_null($entity)){
            return ResponseHelper::notAuthorize('Username not found');
        }
        if(md5($params['password']) != $entity['password']){
            return ResponseHelper::notAuthorize('Password not match');
        }

        $tokenEntity = $this->getTokenByUserId($entity['_id']);

        MongoHelper::removeId($tokenEntity);
        return $tokenEntity;
    }

    public function createUser($fData){
        $entity['username'] = $fData['id'];
        $entity['fb_id'] = $fData['id'];
        $entity['gender'] = $fData['gender'];
        $entity['email'] = $fData['email'];
        $entity['display_name'] = $fData['name'];
        if(isset($fData['birthday'])){
            $entity['birth_date'] = new \MongoTimestamp(strtotime($fData['birthday']));
        }
        else {
            $entity['birth_date'] = null;
        }

        // set website,mobile
        $entity['website'] = isset($fData['website'])? $fData['website']: null;
        $entity['mobile'] = null;

        $imgData = file_get_contents("http://graph.facebook.com/{$fData['id']}/picture?type=large");
        $b64 = base64_encode($imgData);
        $img = Image::upload($b64);
        $entity['picture'] = $img->toArray();

        $this->db->users->insert($entity);

        return $entity;
    }

    public function getTokenByUserId($id){
        $id = MongoHelper::mongoId($id);
        $tokenEntity = $this->collection->findOne(['_id'=> $id]);
        if(is_null($tokenEntity)){
            $tokenEntity = [
                '_id'=> $id,
                'access_token'=> $this->generateToken(MongoHelper::standardId($id))
            ];
            $this->collection->insert($tokenEntity);
        }
        return $tokenEntity;
    }

    public function generateToken($id){
        return md5(uniqid($id, true));
    }
}