<?php

/**
 * 
 * @name get time series price
 * @author Shubham Chaudhary <ghost.jat@gamil.com> 
 */

require_once 'vendor/autoload.php';

use Core\Api\Shoonya;

$api = new Shoonya();

if ($api->login()) {
    echo 'Successfully Login....' . PHP_EOL;
    $data = $api->getTimePriceSeries('techm', '3-10-2022 00:00:00', '25-10-2022 00:00:00');
    foreach ($data as $key => $value) {
        $tp[] = [$value->time,$value->into,$value->inth,$value->intl,$value->intc,$value->intvwap,$value->v];
    }
    $table = new Console_Table();
    echo $table->fromArray(['Time','Open','High','Low','Close','Vwap','Vol'],$tp);
}


if($api->logout()){
    echo 'Successfully Logout....' .PHP_EOL;
}

