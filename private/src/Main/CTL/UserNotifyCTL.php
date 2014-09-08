<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 9/2/14
 * Time: 1:07 PM
 */

namespace Main\CTL;
use Main\Service\UserNotifyService;


/**
 * @Restful
 * @uri /user/notify
 */
class UserNotifyCTL extends BaseCTL {
    protected $service = null;
    public function getService(){
        if(is_null($this->service)){
            $this->service = UserNotifyService::instance($this->getCtx());
        }
        return $this->service;
    }

    /**
     * @GET
     * @uri /[h:id]
     */
    public function gets(){
        return $this->getService()->gets($this->reqInfo->urlParam('id'), $this->reqInfo->params());
    }

    /**
     * @GET
     * @POST
     * @uri /read/[h:id]
     */
    public function read(){
        return $this->getService()->read($this->reqInfo->urlParam('id'));
    }

    /**
     * @GET
     * @uri /unopened/[h:id]
     */
    public function unopened(){
        return $this->getService()->unopened($this->reqInfo->urlParam('id'));
    }
} 