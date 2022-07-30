<?php

require_once 'vendor/autoload.php';

date_default_timezone_set('Asia/Kolkata');

use Core\Api\Shoonya;

$api = new Shoonya();
if($api->login()) {
    echo 'Successfully Login....' .PHP_EOL;
    $holdings = $api->getPositions();
    print_r($holdings);
}



if($api->logout()){
    echo 'Successfully Logout....' .PHP_EOL;
}


