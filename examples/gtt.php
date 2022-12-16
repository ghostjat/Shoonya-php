<?php
/**
 * @package Shoonya-php
 * @category GTT Order
 * @author Shubham Chaudhary <ghost.jat@gamil.com> 
 */

require 'vendor/autoload.php';

$api = new Core\Shoonya();

if($api->login()) {
    echo 'Successfully Login....' . PHP_EOL;
    $gtt = $api->gttOrder($api::Sell,$api::Delivery,'BSE','TECHM', 1111,10,1200,$api::AITG);
    print_r($gtt) . PHP_EOL;
    if ($gtt) {
        $api->telegram('gtt order has been placed for TECHM @ ' . date('h:i:m'));
    }
}


if($api->logout()){
    echo 'Successfully Logout....' .PHP_EOL;
}