<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 8/23/14
 * Time: 2:16 PM
 */

namespace Main\CTL;


use Main\Service\UserHistoryService;

/**
 * @Restful
 * @uri /user/history
 */
class UserHistoryCTL extends BaseCTL {
    protected $service = null;
    public function getService(){
        if(is_null($this->service)){
            $this->service = new UserHistoryService($this->getCtx());
        }
        return $this->service;
    }

    /**
     * @GET
     * @uri /[h:id]
     */
    public function get(){
        return $this->getService()->gets($this->reqInfo->urlParam('id'), $this->reqInfo->params());
    }
}