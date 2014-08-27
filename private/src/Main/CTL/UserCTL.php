<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 8/21/14
 * Time: 4:41 PM
 */

namespace Main\CTL;
use Main\Service\UserService;

/**
 * @Restful
 * @uri /user
 */
class UserCTL extends BaseCTL {
    protected $service = null;
    public function getService(){
        if(is_null($this->service)){
            $this->service = new UserService($this->getCtx());
        }
        return $this->service;
    }
    /**
     * @PUT
     * @uri /[h:id]
     */
    public function edit(){
        return $this->getService()->edit($this->reqInfo->urlParam('id'), $this->reqInfo->params());
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
     * @uri /change_password/[h:id]
     */
    public function changePassword(){
        return $this->getService()->get($this->reqInfo->urlParam('id'), $this->reqInfo->params());
    }
}