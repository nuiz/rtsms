<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 7/16/14
 * Time: 3:33 PM
 */

namespace Main\CTL;
use Main\Service\ContactService;

/**
 * @Restful
 * @uri /contact
 */
class ContactCTL extends BaseCTL {
    /**
     * @GET
     */
    public function get(){
        $contact = ContactService::instance()->get($this->getCtx());
        unset($contact['_id']);
        return $contact;
    }
    /**
     * @PUT
     */
    public function edit(){
        $contact = ContactService::instance()->edit($this->reqInfo->params(), $this->getCtx());
        unset($contact['_id']);
        return $contact;
    }
}