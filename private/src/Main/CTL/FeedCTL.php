<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 9/2/14
 * Time: 1:07 PM
 */

namespace Main\CTL;
use Main\Service\FeedService;
use Main\Service\NewsService;


/**
 * @Restful
 * @uri /feed
 */
class FeedCTL extends BaseCTL {
    protected $service = null;
    public function getService(){
        if(is_null($this->service)){
            $this->service = FeedService::instance($this->getCtx());
        }
        return $this->service;
    }

    /**
     * @GET
     */
    public function gets(){
        return $this->getService()->gets($this->reqInfo->params());
    }
} 