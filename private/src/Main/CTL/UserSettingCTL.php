<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 8/23/14
 * Time: 2:16 PM
 */

namespace Main\CTL;


use Main\Service\UserSettingService;

/**
 * @Restful
 * @uri /user/setting
 */
class UserSettingCTL extends BaseCTL {
    protected $service = null;
    public function getService(){
        if(is_null($this->service)){
            $this->service = new UserSettingService($this->getCtx());
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
}