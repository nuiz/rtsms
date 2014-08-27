<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 7/17/14
 * Time: 3:19 PM
 */

namespace Main\Context;


interface ContextInterface extends LanguageInterface {
    public function getConsumerType();
    public function isAdminConsumer();
}