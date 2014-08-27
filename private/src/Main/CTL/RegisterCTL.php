<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 8/23/14
 * Time: 2:41 PM
 */

namespace Main\CTL;
use Main\Service\UserService;


/**
 * @Restful
 * @uri /register
 */
class RegisterCTL extends BaseCTL {
    protected $service = null;
    public function getService(){
        if(is_null($this->service)){
            $this->service = new UserService($this->getCtx());
        }
        return $this->service;
    }
    /**
     * @POST
     */
    public function add(){
        return $this->getService()->add($this->reqInfo->params());
    }
} 