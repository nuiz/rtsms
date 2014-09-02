<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 7/16/14
 * Time: 3:33 PM
 */

namespace Main\CTL;
use Main\Service\CalendarService;
use Main\Service\ContactService;

/**
 * @Restful
 * @uri /calendar
 */
class CalendarCTL extends BaseCTL {
    protected $service = null;
    public function getService(){
        if(is_null($this->service)){
            $this->service = CalendarService::instance($this->getCtx());
        }
        return $this->service;
    }

    /**
     * @GET
     */
    public function get(){
        return $this->getService()->gets($this->reqInfo->params());
    }
}