<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 9/1/14
 * Time: 3:29 PM
 */

namespace Main\CTL;
use Main\Service\GalleryService;


/**
 * @Restful
 * @uri /gallery
 */
class GalleryCTL extends BaseCTL {
    protected $service = null;
    public function getService(){
        if(is_null($this->service)){
            $this->service = new GalleryService($this->getCtx());
        }
        return $this->service;
    }
    /**
     * @POST
     */
    public function add(){
        return $this->getService()->add($this->reqInfo->params());
    }

    /**
     * @GET
     * @uri /[h:id]
     */
    public function get(){
        return $this->getService()->get($this->reqInfo->urlParam('id'));
    }

    /**
     * @GET
     * @uri /[h:id]/picture
     */
    public function getPicture(){
        return $this->getService()->get($this->reqInfo->urlParam('id'), $this->reqInfo->params());
    }
} 