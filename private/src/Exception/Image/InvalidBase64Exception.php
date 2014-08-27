<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 7/19/14
 * Time: 11:41 AM
 */

namespace Exception\Image;


class InvalidBase64Exception extends \Exception {
    protected $message = "Invalid base64 image string";
} 