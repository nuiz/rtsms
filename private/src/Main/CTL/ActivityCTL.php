<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 9/2/14
 * Time: 1:07 PM
 */

namespace Main\CTL;
use Main\Service\ActivityService;


/**
 * @Restful
 * @uri /activity
 */
class ActivityCTL extends BaseCTL {
    protected $service = null;
    public function getService(){
        if(is_null($this->service)){
            $this->service = ActivityService::instance($this->getCtx());
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
} 