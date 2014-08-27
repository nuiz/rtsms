<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 7/16/14
 * Time: 3:33 PM
 */

namespace Main\CTL;
use Main\Service\StampStyleService;

/**
 * @Restful
 * @uri /stamp/style
 */
class StampStyleCTL extends BaseCTL {
    protected $service = null;
    public function getService(){
        if(is_null($this->service)){
            $this->service = new StampStyleService($this->getCtx());
        }
        return $this->service;
    }

    /**
     * @GET
     */
    public function get(){
        return $this->getService()->get();
    }

    /**
     * @PUT
     */
    public function edit(){
        return $this->getService()->edit($this->reqInfo->params());
    }
}