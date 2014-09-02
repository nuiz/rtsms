<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 7/17/14
 * Time: 3:06 PM
 */

namespace Main\Context;


use Main\DB;

class Context implements ContextInterface {
    private $lang = "en", $defaultLang = "en", $consumer_type = "public", $user = null;

    public function getLang()
    {
        return $this->lang;
    }

    public function setLang($lang)
    {
        return $this->lang = $lang;
    }

    public function getDefaultLang()
    {
        return $this->defaultLang;
    }

    public function isDefaultLang()
    {
        return $this->defaultLang == $this->lang;
    }

    /**
     * @param string $consumer_type
     */
    public function setConsumerType($consumer_type)
    {
        $this->consumer_type = $consumer_type;
    }

    /**
     * @return string
     */
    public function getConsumerType()
    {
        return $this->consumer_type;
    }

    public function isAdminConsumer()
    {
        return $this->getConsumerType() == "admin";
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return null|mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    public function setAccessToken($token){
        $db = DB::getDB();

        $tokenEntity = $db->access_tokens->findOne(['access_token'=> $token]);
        if(is_null($tokenEntity)){
            return false;
        }

        $user = $db->users->findOne(['_id'=> $tokenEntity['_id']], ['display_name', 'email']);
        if(is_null($user)){
            return false;
        }

        $this->setUser($user);
        return $user;
    }
}