<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 7/17/14
 * Time: 12:39 PM
 */

namespace Main\Context;


interface LanguageInterface {
    public function getLang();
    public function getDefaultLang();
    public function isDefaultLang();
}