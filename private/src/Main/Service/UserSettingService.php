<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 8/23/14
 * Time: 2:01 PM
 */

namespace Main\Service;


use Main\DB;
use Main\Helper\ArrayHelper;
use Main\Helper\MongoHelper;
use Main\Helper\ResponseHelper;
use Valitron\Validator;

class UserSettingService extends BaseService {
    protected $fields = [
        'show_facebook',
        'show_email',
        'show_birth_date',
        'show_gender',
        'show_website',
        'show_mobile',

        'notify_update',
        'notify_message'
    ];

    public function __construct($ctx){
        $this->setContext($ctx);

        $this->db = DB::getDB();
        $this->collection = $this->db->users;
    }

    public function get($id){
        $id = MongoHelper::mongoId($id);
        $entity = $this->collection->findOne(['_id'=> $id], ['setting']);
        if(is_null($entity)){
            return ResponseHelper::notFound();
        }

        $setting = $this->getSetting($entity);
        return $setting;
    }

    public function edit($id, $params){
        $id = MongoHelper::mongoId($id);
        $entity = $this->collection->findOne(['_id'=> $id], ['setting']);
        if(is_null($entity)){
            return ResponseHelper::notFound();
        }
        $this->getSetting($entity);

        $setting = ArrayHelper::filterKey($this->fields, $params);

        if(count($setting) > 0){
            foreach($setting as $key=> $value){
                $setting[$key] = (bool)$value;
            }
            $set = ['setting'=> $setting];
            $set = ArrayHelper::ArrayGetPath($set);
            $this->collection->update(['_id'=> $entity['_id']], ['$set'=> $set]);
        }

        return $this->get($id);
    }

    public function getSetting($entity){
        if(isset($entity['setting'])){
            return $entity['setting'];
        }

        $setting = [
            'show_facebook'=> true,
            'show_email'=> true,
            'show_birth_date'=> true,
            'show_gender'=> true,
            'show_website'=> true,
            'show_mobile'=> true,

            'notify_update'=> true,
            'notify_message'=> true
        ];
        $set = ['setting'=> $setting];
        $set = ArrayHelper::ArrayGetPath($set);
        $this->collection->update(['_id'=> $entity['_id']], ['$set'=> $set]);

        return $setting;
    }
}