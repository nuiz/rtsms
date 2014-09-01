<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 8/30/14
 * Time: 4:31 PM
 */

namespace Main\CTL;
use Main\Service\ProductService;


/**
 * @Restful
 * @uri /product
 */
class ProductCTL extends BaseCTL {
    protected $service = null;
    public function getService(){
        if(is_null($this->service)){
            $this->service = new ProductService($this->getCtx());
        }
        return $this->service;
    }

    /**
     * @POST
     */
    public function add(){
        return $this->getService()->add($this->reqInfo->params());
    }

    /**
     * @GET
     * @uri /[h:id]
     */
    public function get(){
        return $this->getService()->get($this->reqInfo->urlParam('id'));
    }
} 