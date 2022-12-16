<?php

/**
 * 
 * @name get the option chain 
 * @author Shubham Chaudhary <ghost.jat@gamil.com> 
 */
require_once 'vendor/autoload.php';

use Core\Shoonya;

$api = new Shoonya();

if ($api->login()) {
    echo 'Successfully Login....' . PHP_EOL;
    $oc = $api->getOptionChain('NIFTY27OCT22F', 17650,10);
    $table = new Console_Table();
    echo $table->fromArray(['Opt', 'Token', 'Tsym','Ltp','Oi', 'Strprc','Oi','Ltp', 'Tsym', 'Token', 'Opt'],$oc);
}


if ($api->logout()) {
    echo 'Successfully Logout....' . PHP_EOL;
}