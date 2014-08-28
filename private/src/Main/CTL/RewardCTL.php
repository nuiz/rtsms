<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 8/28/14
 * Time: 12:22 PM
 */

namespace Main\CTL;
use Main\Service\RewardService;

/**
 * @Restful
 * @uri /reward
 */
class RewardCTL extends BaseCTL {
    protected $service = null;
    public function getService(){
        if(is_null($this->service)){
            $this->service = new RewardService($this->getCtx());
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

    /**
     * @GET
     * @uri /[h:id]
     */
    public function get(){
        return $this->getService()->gets($this->reqInfo->urlParam('id'));
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
} 