<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 8/23/14
 * Time: 2:16 PM
 */

namespace Main\CTL;


use Main\Service\UserStampService;

/**
 * @Restful
 * @uri /user/stamp
 */
class UserStampCTL extends BaseCTL {
    protected $service = null;
    public function getService(){
        if(is_null($this->service)){
            $this->service = new UserStampService($this->getCtx());
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
     * @POST
     * @uri /add/[h:id]
     */
    public function addPoint(){
        return $this->getService()->addPoint($this->reqInfo->urlParam('id'), $this->reqInfo->params());
    }

    /**
     * @POST
     * @uri /redeem/[h:id]
     */
    public function redeem(){
        return $this->getService()->redeem($this->reqInfo->urlParam('id'), $this->reqInfo->params());
    }
}