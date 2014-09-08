<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 9/8/14
 * Time: 3:00 PM
 */

namespace Main\Service;


use Main\DB;
use Main\Helper\MongoHelper;
use Main\Helper\URL;

class UserNotifyService extends BaseService {
    public function __construct($ctx){
        $this->setContext($ctx);

        $this->db = DB::getDB();
        $this->collection = $this->db->notify;
    }

    public static function instance($ctx){
        if(is_null(self::$instance)){
            self::$instance = new self($ctx);
        }
        return self::$instance;
    }

    public function gets($userId, $options){
        $default = array(
            "page"=> 1,
            "limit"=> 15
        );
        $options = array_merge($default, $options);

        $skip = ($options['page']-1)*$options['limit'];

        $userId = MongoHelper::mongoId($userId);
        $condition = ['user_id' => $userId];

        $cursor = $this->collection
            ->find($condition, ['created_at', 'opened', 'object', 'preview_content', 'preview_header'])
            ->limit((int)$options['limit'])
            ->skip((int)$skip)
            ->sort(['seq'=> -1]);

        $data = [];
        foreach($cursor as $item){
            $item['created_at'] = MongoHelper::timeToStr($item['created_at']);
            MongoHelper::standardIdEntity($item);
            $data[] = $item;
        }

        $total = $this->collection->count($condition);
        $length = $cursor->count(true);

        $res = [
            'length'=> $length,
            'total'=> $total,
            'data'=> $data,
            'paging'=> [
                'page'=> (int)$options['page'],
                'limit'=> (int)$options['limit']
            ]
        ];

        $pagingLength = $total/(int)$options['limit'];
        $pagingLength = floor($pagingLength) + 1;
        $res['paging']['length'] = $pagingLength;
        $res['paging']['current'] = (int)$options['page'];
        if(((int)$options['page'] * (int)$options['limit']) < $total){
            $nextQueryString = http_build_query(['page'=> (int)$options['page']+1, 'limit'=> (int)$options['limit']]);
            $res['paging']['next'] = URL::absolute('/user/notify/'.MongoHelper::standardId($userId).'?'.$nextQueryString);
        }

        return $res;
    }

    public function unopened($userId){
        $userId = MongoHelper::mongoId($userId);
        $count = $this->collection->count(['user_id'=> $userId, 'opened'=> false]);

        return ['length'=> $count];
    }

    public function read($id){
        $id = MongoHelper::mongoId($id);
        $this->collection->update(['_id'=> $id], ['$set'=> ['opened'=> true]]);

        return ['success'=> true];
    }
}