<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 7/15/14
 * Time: 11:27 AM
 */

namespace Main\CTL;


use Main\Context\Context;
use Main\Context\ContextInterface;
use Main\Http\RequestInfo;

class BaseCTL {
    /**
     * @var RequestInfo $reqInfo
     * @var ContextInterface $ctx;
     */
    public $reqInfo, $ctx;
    public function __construct(RequestInfo $reqInfo){
        $this->reqInfo = $reqInfo;
        $this->ctx = new Context();
        $lang = $this->ctx->getDefaultLang();
        if($reqInfo->hasInput('lang')){
            $lang = $reqInfo->input('lang');
        }
        $consumerType = "normal";
        if($reqInfo->input('consumer_key')=="admin"){
            $consumerType = "admin";
        }
        $this->ctx->setConsumerType($consumerType);
        $this->ctx->setLang($lang);
    }

    public function getCtx(){
        return $this->ctx;
    }
}