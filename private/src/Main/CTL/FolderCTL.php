<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 8/30/14
 * Time: 1:56 PM
 */

namespace Main\CTL;

use Main\Service\FolderService;

/**
 * @Restful
 * @uri /folder
 */
class FolderCTL extends BaseCTL {
    protected $service = null;
    public function getService(){
        if(is_null($this->service)){
            $this->service = new FolderService($this->getCtx());
        }
        return $this->service;
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
     * @POST
     */
    public function add(){
        return $this->getService()->add($this->reqInfo->params());
    }

    /**
     * @DELETE
     */
    public function delete(){
        return $this->getService()->delete($this->reqInfo->params());
    }
}