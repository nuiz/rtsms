<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 9/2/14
 * Time: 4:44 AM
 */

namespace Main\Service;


use Main\DataModel\Image;
use Main\DB;
use Main\Helper\ArrayHelper;
use Main\Helper\MongoHelper;
use Main\Helper\ResponseHelper;
use Main\Helper\URL;
use Valitron\Validator;

class ActivityService extends BaseService {
    protected $fields = ['name', 'detail', 'datetime', 'thumb'];

    public function __construct($ctx){
        $this->setContext($ctx);

        $this->db = DB::getDB();
        $this->collection = $this->db->activity;
    }

    public static function instance($ctx){
        if(is_null(self::$instance)){
            self::$instance = new self($ctx);
        }
        return self::$instance;
    }

    public function add($params){
        $v = new Validator($params);
        $v->rule('required', ['name', 'detail', 'datetime', 'thumb']);
        $v->rule('date', ['datetime']);

        if(!$v->validate()){
            return ResponseHelper::validateError($v->errors());
        }

        $insert = ArrayHelper::filterKey(['name', 'detail', 'datetime', 'thumb'], $params);
        $insert['thumb'] = Image::upload($params['thumb'])->toArray();
        $insert['datetime'] = new \MongoTimestamp(strtotime($insert['datetime']));

        // insert created_at, updated_at
        $insert['created_at'] = new \MongoTimestamp();
        $insert['updated_at'] = $insert['created_at'];

        $this->collection->insert($insert);
        FeedService::instance($this->getContext())->add($insert['_id'], 'activity', $insert['created_at']);

        return $this->get($insert['_id']);
    }

    public function edit($id, $params){
        $id = MongoHelper::mongoId($id);

        $v = new Validator($params);
        $v->rule('date', ['datetime']);

        if(!$v->validate()){
            return ResponseHelper::validateError($v->errors());
        }

        $set = ArrayHelper::filterKey(['name', 'detail', 'datetime', 'thumb'], $params);
        if(isset($set['datetime'])){
            $set['datetime'] = new \MongoTimestamp(strtotime($set['datetime']));
        }
        if(isset($set['thumb'])){
            $set['thumb'] = Image::upload($params['thumb'])->toArray();
        }
        if(count($set) > 0){
            // update updated_at
            $set['updated_at'] = new \MongoTimestamp();
            $this->collection->update(['_id'=> $id], ['$set'=> $set]);
        }
        return $this->get($id);
    }

    public function get($id){
        $id = MongoHelper::mongoId($id);

        $entity = $this->collection->findOne(['_id'=> $id], $this->fields);
        $entity['thumb'] = Image::load($entity['thumb'])->toArrayResponse();
        $entity['datetime'] = MongoHelper::timeToStr($entity['datetime']);
        MongoHelper::standardIdEntity($entity);
        return $entity;
    }

    public function gets($options = array()){
        $default = array(
            "page"=> 1,
            "limit"=> 15
        );
        $options = array_merge($default, $options);

        $skip = ($options['page']-1)*$options['limit'];
        $condition = [];

        $cursor = $this->collection
            ->find($condition, $this->fields)
            ->limit((int)$options['limit'])
            ->skip((int)$skip)
            ->sort(['created_at'=> -1]);

        $data = [];
        foreach($cursor as $item){
            $item['thumb'] = Image::load($item['thumb'])->toArrayResponse();
            $item['datetime'] = MongoHelper::timeToStr($item['datetime']);
            MongoHelper::standardIdEntity($item);
            $data[] = $item;
        }

        $total = $this->collection->count($condition);
        $length = $cursor->count(true);

        return array(
            'length'=> $length,
            'total'=> $total,
            'data'=> $data,
            'paging'=> [
                'page'=> (int)$options['page'],
                'limit'=> (int)$options['limit']
            ]
        );
    }

    public function delete($id){
        $id = MongoHelper::mongoId($id);
        $this->collection->remove(['_id'=> $id]);
        FeedService::instance($this->getContext())->delete($id);

        return ['success'=> true];
    }

    // ***************** comments ***********************

    public function addComment($id, $params){
        $id = MongoHelper::mongoId($id);
        $v = new Validator($params);
        $v->rule('required', ['message']);

        $user = $this->getContext()->getUser();
        if(is_null($user)){
            return ResponseHelper::requireAuthorize();
        }

        if(!$v->validate()){
            return ResponseHelper::validateError($v->errors());
        }

        $entity = $this->collection->findOne(['_id'=> $id], ['comments']);
        if(is_null($entity)){
            return ResponseHelper::notFound();
        }
        if(!isset($entity['comments'])){
            $this->collection->update(['_id'=> $id], ['$set'=>['comments'=> []]]);
        }

        $comment = [
            'id'=> new \MongoId(),
            'user_id'=> $user['_id'],
            'message'=> $params['message'],
            'created_at'=> new \MongoTimestamp()
        ];

        $this->collection->update(['_id'=> $id], ['$push'=> ['comments'=> $comment]]);
//        MongoHelper::standardIdEntity($comment);
        $comment['id'] = MongoHelper::standardId($comment['id']);
//                $comment['user_id'] = MongoHelper::standardId($comment['user_id']);

        $comment['user'] = $this->db->users->findOne(['_id'=> $comment['user_id']], ['display_name', 'picture']);
        $comment['user']['picture'] = Image::load($comment['user']['picture'])->toArrayResponse();
        MongoHelper::standardIdEntity($comment['user']);
        unset($comment['user_id']);

        $comment['created_at'] = MongoHelper::timeToStr($comment['created_at']);

        return $comment;
    }

    public function getComments($id, $params){
        $id = MongoHelper::mongoId($id);
        if($this->collection->count(['_id'=> $id]) == 0){
            return ResponseHelper::notFound();
        }

        $default = ["page"=> 1, "limit"=> 15];
        $options = array_merge($default, $params);
        $arg = $this->collection->aggregate([
            ['$match'=> ['_id'=> $id]],
            ['$project'=> ['comments'=> 1]],
            ['$unwind'=> '$comments'],
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
            $entity = $this->collection->findOne(['_id'=> $id], ['comments'=> ['$slice'=> $slice]]);
            $data = [];
            foreach($entity['comments'] as $key=> $value){
                $comment = $value;
                $comment['id'] = MongoHelper::standardId($comment['id']);
//                $comment['user_id'] = MongoHelper::standardId($comment['user_id']);

                $comment['user'] = $this->db->users->findOne(['_id'=> $comment['user_id']], ['display_name', 'picture']);
                $comment['user']['picture'] = Image::load($comment['user']['picture'])->toArrayResponse();
                MongoHelper::standardIdEntity($comment['user']);
                unset($comment['user_id']);

                $comment['created_at'] = MongoHelper::timeToStr($comment['created_at']);
                $data[] = $comment;
            }
        }

        // reverse data
        $data = array_reverse($data);

        $res = [
            'length'=> count($data),
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
            $res['paging']['next'] = URL::absolute('/activity/'.MongoHelper::standardId($id).'/comment?'.$nextQueryString);
        }

        return $res;
    }
}