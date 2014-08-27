<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 7/17/14
 * Time: 2:32 PM
 */

/*
function detectRequestBody() {
    $rawInput = fopen('php://input', 'r');
    $tempStream = fopen('php://temp', 'r+');
    stream_copy_to_stream($rawInput, $tempStream);
    rewind($tempStream);

    return $tempStream;
}

//print_r(stream_get_contents(detectRequestBody()));
print_r($_SERVER['CONTENT_TYPE']);
*/

$info = array();
if(true && @$info['test']){
    echo 'yes';
}
else {
    echo 'no';
}