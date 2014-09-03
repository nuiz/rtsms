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
     * @DELETE
     * @uri /[h:id]
     */
    public function delete(){
        return $this->getService()->delete($this->reqInfo->urlParam('id'));
    }

    /**
     * @GET
     * @uri /[h:id]/picture
     */
    public function getPicture(){
        return $this->getService()->getPictures($this->reqInfo->urlParam('id'), $this->reqInfo->params());
    }

    /**
     * @POST
     * @uri /[h:id]/picture
     */
    public function postPicture(){
        return $this->getService()->addPictures($this->reqInfo->urlParam('id'), $this->reqInfo->params());
    }

    /**
     * @DELETE
     * @uri /[h:id]/picture
     */
    public function deletePicture(){
        return $this->getService()->deletePictures($this->reqInfo->urlParam('id'), $this->reqInfo->params());
    }
}