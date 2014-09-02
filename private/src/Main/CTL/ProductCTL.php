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

    /**
     * @PUT
     * @uri /[h:id]
     */
    public function edit(){
        return $this->getService()->edit($this->reqInfo->urlParam('id'), $this->reqInfo->params());
    }

    /**
     * @DELETE
     * @uri /[h:id]
     */
    public function delete(){
        return $this->getService()->delete($this->reqInfo->urlParam('id'));
    }

    /**
     * @GET
     * @uri /[h:id]/picture
     */
    public function getPicture(){
        return $this->getService()->getPictures($this->reqInfo->urlParam('id'), $this->reqInfo->params());
    }

    /**
     * @POST
     * @uri /[h:id]/picture
     */
    public function postPicture(){
        return $this->getService()->addPictures($this->reqInfo->urlParam('id'), $this->reqInfo->params());
    }

    /**
     * @DELETE
     * @uri /[h:id]/picture
     */
    public function deletePicture(){
        return $this->getService()->deletePictures($this->reqInfo->urlParam('id'), $this->reqInfo->params());
    }
}