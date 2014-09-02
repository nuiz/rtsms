<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 8/28/14
 * Time: 12:22 PM
 */

namespace Main\CTL;
use Main\Service\OrderService;

/**
 * @Restful
 * @uri /order
 */
class OrderCTL extends BaseCTL {
    protected $service = null;
    public function getService(){
        if(is_null($this->service)){
            $this->service = new OrderService($this->getCtx());
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
     * @POST
     */
    public function add(){
        return $this->getService()->add($this->reqInfo->params());
    }
}