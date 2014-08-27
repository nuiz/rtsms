<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 6/28/14
 * Time: 11:30 AM
 */

namespace Main\Service;

use Main\Context\ContextInterface;

abstract class BaseService {
    protected $_version = 1;

    /** @var ContextInterface $ctx */
    protected $ctx;

    protected static $instance;

    protected function __construct() { }

    final private function __clone() { }

    public function setContext(ContextInterface $ctx){
        $this->ctx = $ctx;
    }

    public function getContext(){
        return $this->ctx;
    }
}