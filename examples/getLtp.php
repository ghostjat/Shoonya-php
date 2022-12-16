<?php


/**
 * 
 * @name getLtp
 * @author Shubham Chaudhary <ghost.jat@gamil.com> 
 */

require_once 'vendor/autoload.php';

use Core\Shoonya;

$api = new Shoonya();

if ($api->login()) {
    echo 'Successfully Login....' . PHP_EOL;
    $loop = null;
    if(strtotime(date('h:i:s')) < strtotime('15:30:00')) {
        $loop = true;
    }
    while ($loop) {
        $mw = $api->getLTP('NIFTYBEES', 'NSE');
        echo 'NiftyBees LTP :- ' . $mw . '@' . date('h:i:s') . PHP_EOL;
        $api->telegram('NiftyBees LTP :- ' . $mw . '@' . date('h:i:s'));
        sleep(30);
    }
}

if($api->logout()){
    echo 'Successfully Logout....' .PHP_EOL;
}

