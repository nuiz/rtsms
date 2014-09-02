<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 8/28/14
 * Time: 11:29 AM
 */

namespace Main\Service;


use Main\DataModel\Image;
use Main\DB;
use Main\Helper\ArrayHelper;
use Main\Helper\MongoHelper;
use Main\Helper\ResponseHelper;
use Main\Helper\URL;
use Valitron\Validator;

class NodeService extends BaseService {

    public function __construct($ctx){
        $this->setContext($ctx);

        $this->db = DB::getDB();
        $this->collection = $this->db->node;
        $this->folderService = new FolderService($ctx);
    }

    public function getRootFolder($name){
        $folder = $this->collection->findOne(['root'=> $name]);
        if(is_null($folder)){
            $b64 = base64_encode(file_get_contents('private/default/folder/leo_folder.jpg'));
            $folder = $this->folderService->add(['name'=> $name, 'detail'=> $name, 'thumb'=> $b64], $name);
        }
        else {
            MongoHelper::standardIdEntity($folder);
        }
        return $folder;
    }

    public function getsRoot($params, $name){
        $folder = $this->getRootFolder($name);
        $options = $params;
        $options['parent_id'] = $folder['id'];
        return $this->gets($options);
    }

    public function gets($options = array()){
        $default = array(
            "page"=> 1,
            "limit"=> 15
        );
        $options = array_merge($default, $options);

        $skip = ($options['page']-1)*$options['limit'];

        // condition parent_id
        $condition = ['parent'=> null];
        if(isset($options['parent_id'])){
            $condition = ['parent.id'=> MongoHelper::mongoId($options['parent_id'])];
        }

        $cursor = $this->collection
            ->find($condition, ['pictures'=> ['$slice'=> [0, 1]], 'name'=> 1, 'detail'=> 1, 'thumb'=> 1, 'price'=> 1, 'type'=> 1])
            ->limit((int)$options['limit'])
            ->skip((int)$skip)
            ->sort(['created_at'=> -1]);

        $data = [];
        foreach($cursor as $item){
            if($item['type']=='folder'){
                $item['thumb'] = Image::load($item['thumb'])->toArrayResponse();
                $item['node'] = [
                    'children'=> URL::absolute('/node/'.MongoHelper::standardId($item['_id'].'/children'))
                ];
            }
            else if($item['type']=='product'){
                $item['thumb'] = Image::load($item['pictures'][0])->toArrayResponse();
                $item['node'] = [
                    'pictures'=> URL::absolute('/product/'.MongoHelper::standardId($item['_id']).'/picture')
                ];
                unset($item['pictures']);
            }
            else if($item['type']=='gallery') {
                $item['thumb'] = Image::load($item['pictures'][0])->toArrayResponse();
                $item['node'] = [
                    'pictures'=> URL::absolute('/gallery/'.MongoHelper::standardId($item['_id']).'/picture')
                ];
                unset($item['pictures']);
            }
            unset($item['parent']);
            MongoHelper::standardIdEntity($item);
            $data[] = $item;
        }

        $total = $this->collection->count($condition);
        $length = $cursor->count(true);

        $res = array(
            'length'=> $length,
            'total'=> $total,
            'data'=> $data,
            'paging'=> [
                'page'=> (int)$options['page'],
                'limit'=> (int)$options['limit']
            ]
        );

        if($this->getContext()->isAdminConsumer()){
            $node = array(
                'parent'=> null,
            );
            $res['id'] = null;
            if(isset($options['parent_id'])){

                $parentId = $options['parent_id'];
                if(!($parentId instanceof \MongoId))
                    $parentId = new \MongoId($parentId);

                $condition['parent'] = \MongoDBRef::create("folders", $parentId);

                // set node
                $parent = $this->collection->findOne(array('_id'=> $parentId));
                if(is_null($parent['parent'])){
                    $node['parent'] = URL::absolute('/node');
                }
                else{
                    $node['parent'] = URL::absolute('/node/'.$parent['parent']['$id']->{'$id'}.'/children');
                }

                $res['id'] = $options['parent_id'];
            }
            $res['node'] = $node;
        }

        return $res;
    }
}