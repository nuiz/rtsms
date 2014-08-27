<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 7/16/14
 * Time: 3:58 PM
 */

namespace Main\Service;


use Main\Context\Context;
use Main\Context\ContextInterface;
use Main\DataModel\Image;
use Main\DB;
use Main\Helper\ArrayHelper;
use Main\Helper\MongoHelper;

class StampStyleService extends BaseService {
    private $collection;

    public function __construct(ContextInterface $ctx){
        $this->setContext($ctx);
        $this->db = DB::getDB();
        $this->collection = $this->db->stamp_style;
    }

    public function get(ContextInterface $ctx = null){
        if(is_null($ctx))
            $ctx = $this->getContext();

        $entity = $this->collection->findOne();
        if(is_null($entity)){
            $entity = $this->insertWhenEmpty($ctx);
        }

        MongoHelper::removeId($entity);
        $entity['icon'] = Image::load($entity['icon'])->toArrayResponse();
        $entity['background'] = Image::load($entity['background'])->toArrayResponse();
        $entity['poster'] = Image::load($entity['poster'])->toArrayResponse();

        return $entity;
    }

    public function edit(array $params, ContextInterface $ctx = null){
        if(is_null($ctx))
            $ctx = $this->getContext();

        $entity = $this->collection->findOne();
        if(is_null($entity)){
            $entity = $this->insertWhenEmpty($ctx);
        }

        $allowed = ["poster", "icon", "background", "condition_info"];
        $set = ArrayHelper::filterKey($allowed, $params);
        if(count($set)==0){
            MongoHelper::removeId($entity);
            return $entity;
        }
        foreach($set as $key => $value){
            if($key != 'condition_info'){
                $img = Image::upload($value);
                $set[$key] = $img->toArray();
            }
        }

        if(count($set) > 0){
            $set = ArrayHelper::ArrayGetPath($set);
            $this->collection->update(array("_id"=> $entity['_id']), array('$set'=> $set));
        }

        $entity = $this->get();
        return $entity;
    }

    private function insertWhenEmpty(ContextInterface $ctx = null){
        if(is_null($ctx))
            $ctx = $this->getContext();


        $entity = [
            "icon"=> Image::upload(base64_encode(file_get_contents('private/default/stamp/stamp.png')))->toArray(),
            "background"=> Image::upload(base64_encode(file_get_contents('private/default/stamp/bg_member.png')))->toArray(),
            "poster"=> Image::upload(base64_encode(file_get_contents('private/default/stamp/membercard.png')))->toArray(),
            "condition_info"=> "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum."
        ];

        $this->collection->insert($entity);
        return $entity;
    }
}