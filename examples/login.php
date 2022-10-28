<?php 

require '../vendor/autoload.php';

use Core\Api\Shoonya;

$api = new Shoonya();

if($api->login()) {
    // algo logic ..........!
    
}

if($api->logout()) {
    // logic .....
}