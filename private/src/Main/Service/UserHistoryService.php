<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 8/25/14
 * Time: 3:09 PM
 */

namespace Main\Service;


use Main\DB;
use Main\Helper\MongoHelper;
use Main\Helper\ResponseHelper;
use Valitron\Validator;

class UserHistoryService extends BaseService {
    public function __construct($ctx){
        $this->setContext($ctx);

        $this->db = DB::getDB();
        $this->collection = $this->db->users_history;
    }

    public function gets($uid, $params){
        $uid = MongoHelper::mongoId($uid);
        if($this->db->users->count(['_id'=> $uid]) == 0){
            return ResponseHelper::notFound();
        }

        $this->collection->update(['_id'=> $uid], ['$setOnInsert'=> ['history'=> []]], ['upsert'=> true]);

        $default = ["page"=> 1, "limit"=> 15];
        $options = array_merge($default, $params);
        $arg = $this->collection->aggregate([
            ['$match'=> ['_id'=> $uid]],
            ['$project'=> ['history'=> 1]],
            ['$unwind'=> '$history'],
            ['$group'=> ['_id'=> null, 'total'=> ['$sum'=> 1]]]
        ]);

        $total = $arg['result'][0]['total'];
        $limit = (int)$options['limit'];
        $page = (int)$options['page'];

        $slice = MongoHelper::createSlice($page, $limit, $total);

        if($slice[1] == 0){
            $data = [];
        }
        else {
            $entity = $this->collection->findOne(['_id'=> $uid], ['history'=> ['$slice'=> $slice]]);
            $data = $entity['history'];
            foreach($data as $key => $value){
                $data[$key]['created_at'] = date('Y-m-d H:i:s', $value['created_at']->sec);
            }
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

    public function add($uid, $params){
        $uid = MongoHelper::mongoId($uid);
        if($this->db->users->count(['_id'=> $uid]) == 0){
            return ResponseHelper::notFound();
        }

        $this->collection->update(['_id'=> $uid], ['$setOnInsert'=> ['history'=> []]], ['upsert'=> true]);

        $now = new \MongoTimestamp();
        $obj = [
            'message'=> $params['message'],
            'created_at'=> $now
        ];
        $this->collection->update(['_id'=> $uid], ['$push'=> ['history'=> $obj]]);

        return $obj;
    }
} 