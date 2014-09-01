<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 8/30/14
 * Time: 1:56 PM
 */

namespace Main\CTL;

use Main\Service\NodeService;

/**
 * @Restful
 * @uri /node
 */
class NodeCTL extends BaseCTL {
    protected $service = null;
    public function getService(){
        if(is_null($this->service)){
            $this->service = new NodeService($this->getCtx());
        }
        return $this->service;
    }

    /**
     * @GET
     */
    public function gets(){
        return $this->getService()->gets($this->reqInfo->params());
    }

    /**
     * @GET
     * @uri /food
     */
    public function getsFood(){
        return $this->getService()->getsRoot($this->reqInfo->params(), 'food');
    }

    /**
     * @GET
     * @uri /drink
     */
    public function getsDrink(){
        return $this->getService()->getsRoot($this->reqInfo->params(), 'drink');
    }

    /**
     * @GET
     * @uri /gallery
     */
    public function getsGallery(){
        return $this->getService()->getsRoot($this->reqInfo->params(), 'gallery');
    }

    /**
     * @GET
     * @uri /[h:id]/children
     */
    public function getsChildren(){
        $params = $this->reqInfo->params();
        return $this->getService()->gets($params);
    }
}