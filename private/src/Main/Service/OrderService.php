<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 9/2/14
 * Time: 8:28 AM
 */

namespace Main\Service;


use Main\DataModel\Image;
use Main\DB;
use Main\Helper\ArrayHelper;
use Main\Helper\MongoHelper;
use Main\Helper\ResponseHelper;
use Valitron\Validator;

class OrderService extends BaseService {
    public function __construct($ctx){
        $this->setContext($ctx);

        $this->db = DB::getDB();
        $this->collection = $this->db->orders;
        $this->productService = new ProductService($ctx);
        $this->userService = new UserService($ctx);
    }

    public function add($params){
        $user = $this->getContext()->getUser();
        if(is_null($user)){
            return ResponseHelper::requireAuthorize();
        }

        // validate
        $v = new Validator($params);
        $v->rule('required', ['orders', 'name', 'phone']);

        if(!$v->validate()){
            return ResponseHelper::validateError($v->errors());
        }

        $insert = ArrayHelper::filterKey(['name', 'phone'], $params);
        MongoHelper::standardIdEntity($user);
        $insert['user'] = ArrayHelper::filterKey(['id', 'display_name', 'picture'], $user);

        if(!is_array($params['orders'])){
            return ResponseHelper::validateError(['orders'=> ['orders must be array']]);
        }

        $insert['orders'] = array();
        foreach($params['orders'] as $key=> $value){
            $v = new Validator($value);
            $v->rule('required', ['id', 'amount', 'note']);

            if(!$v->validate()){
                return ResponseHelper::validateError($v->errors());
            }

            $product = $this->productService->get($value['id']);
            $insert['orders'][] = [
                'product'=> $product,
                'amount'=> $value['amount'],
                'total'=> $product['price']*$value['amount']
            ];
        }
        //end validate

        $insert['created_at'] = new \MongoTimestamp();

        $this->collection->insert($insert);
        MongoHelper::standardIdEntity($insert);
        return $insert;
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
            ->find($condition)
            ->limit((int)$options['limit'])
            ->skip((int)$skip)
            ->sort(['created_at'=> -1]);

        $data = [];
        foreach($cursor as $key=> $item){
            if(isset($item['user']['picture'])){
                $item['user']['picture'] = Image::load($item['thumb'])->toArrayResponse();
            }
            $item['created_at'] = date('Y-m-d H:i:s', $item['created_at']);
            MongoHelper::standardIdEntity($item);
            $data[] = $item;
        }

        $total = $this->collection->count($condition);
        $length = $cursor->count(true);

        return [
            'length'=> $length,
            'total'=> $total,
            'data'=> $data,
            'paging'=> [
                'page'=> (int)$options['page'],
                'limit'=> (int)$options['limit']
            ]
        ];
    }
}