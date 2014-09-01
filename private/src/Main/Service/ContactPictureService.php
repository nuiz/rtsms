<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 8/20/14
 * Time: 2:56 PM
 */

namespace Main\Service;


use Main\DataModel\Image;
use Main\DB;
use Main\Helper\MongoHelper;

class ContactPictureService extends BaseService {
    public function __construct($ctx){
        $this->setContext($ctx);

        $db = DB::getDB();
        $this->collection = $db->contacts_pictures;
    }

    public function getEntity($options = []){
        $entity = $this->collection->findOne([], $options);
        if(is_null($entity)){
            $entity = ['pictures'=> []];
            $this->collection->insert($entity);
        }
        return $entity;
    }

    public function gets($params){
        $default = ["page"=> 1, "limit"=> 15];
        $options = array_merge($default, $params);
        $arg = $this->collection->aggregate([
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
            $entity = $this->getEntity(['pictures'=> ['$slice'=> $slice]]);
            $data = Image::loads($entity['pictures'])->toArrayResponse();
        }

        // reverse data
        $data = array_reverse($data);

        return array(
            'length'=> count($data),
            'total'=> (int)$total,
            'data'=> $data,
            'paging'=> array(
                'page'=> (int)$options['page'],
                'limit'=> (int)$options['limit']
            )
        );
    }

    public function add($params){
        $res = [];

        $entity = $this->getEntity();
        foreach($params['pictures'] as $key => $value){
            $img = Image::upload($value);
            $item = $img->toArray();
            $this->collection->update(['_id'=> $entity['_id']], ['$push'=> ['pictures'=> $item]]);
            $res[] = Image::load($item)->toArrayResponse();
        }
        return $res;
    }

    public function delete($params){
        $res = [];

        $entity = $this->getEntity();
        $id = [];
        foreach($params['id'] as $key => $value){
            $this->collection->update(['_id'=> $entity['_id']], ['$pull'=> ['pictures'=> ['id'=> $value]]]);
            $res[] = $value;
        }
        return $res;
    }
}