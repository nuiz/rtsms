<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 8/22/14
 * Time: 3:09 PM
 */

namespace Main\CTL;
use Main\Service\UserService;


/**
 * @Restful
 * @uri /me
 */
class MeCTL extends BaseCTL {
    protected $service = null;
    public function getService(){
        if(is_null($this->service)){
            $this->service = new UserService($this->getCtx());
        }
        return $this->service;
    }
    /**
     * @GET
     */
    public function me(){
        return $this->getService()->me($this->reqInfo->params());
    }
} 