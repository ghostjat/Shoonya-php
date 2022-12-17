<?php

require 'vendor/autoload.php';

use Core\Shoonya;

$api = new Shoonya();

if($api->login()) {
    echo 'Login successfully!' .PHP_EOL;
    print_r($api->getPendingAlert());
}

if($api->logout()) {
    echo 'Logout successfully!' .PHP_EOL;
}
