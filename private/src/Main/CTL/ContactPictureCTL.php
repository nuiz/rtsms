<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 8/20/14
 * Time: 4:46 PM
 */

namespace Main\CTL;
use Main\Service\ContactPictureService;
use Main\Service\ContactService;


/**
 * @Restful
 * @uri /contact/picture
 */
class ContactPictureCTL extends BaseCTL {
    protected $service = null;
    public function getService(){
        if(is_null($this->service)){
            $this->service = new ContactPictureService($this->getCtx());
        }
        return $this->service;
    }

    /**
     * @GET
     */
    public function get(){
        $items = $this->getService()->gets($this->reqInfo->params());
        return $items;
    }

    /**
     * @POST
     */
    public function add(){
        $items = $this->getService()->add($this->reqInfo->params());
        return $items;
    }

    /**
     * @DELETE
     */
    public function deletes(){
        $items = $this->getService()->delete($this->reqInfo->params());
        return $items;
    }
}