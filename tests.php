<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 7/15/14
 * Time: 3:14 PM
 */

require_once 'bootstrap.php';
$argv = array(
    '--configuration', 'private/tests/phpunit.xml',
    //'./unit',
);
$_SERVER['argv'] = $argv;

\PHPUnit_TextUI_Command::main(false);