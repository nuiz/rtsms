<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 8/22/14
 * Time: 2:35 PM
 */

namespace Main\CTL;
use Main\Service\OAuthService;

/**
 * @Restful
 * @uri /oauth
 */
class OAuthCTL extends BaseCTL {
    protected $service = null;
    public function getService(){
        if(is_null($this->service)){
            $this->service = new OAuthService($this->getCtx());
        }
        return $this->service;
    }

    /**
     * @POST
     * @uri /facebook
     */
    public function facebook(){
        return $this->getService()->loginFacebook($this->reqInfo->params());
    }

    /**
     * @POST
     * @uri /password
     */
    public function password(){
        return $this->getService()->loginPassword($this->reqInfo->params());
    }

    /**
     * @POST
     * @uri /admin
     */
    public function admin(){
        return $this->getService()->loginPasswordAdmin($this->reqInfo->params());
    }
}