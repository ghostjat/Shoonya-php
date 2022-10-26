<?php

/**
 * 
 * @name getPortfolio
 * @author Shubham Chaudhary <ghost.jat@gamil.com> 
 */

require_once 'vendor/autoload.php';

use Core\Api\Shoonya;

$api = new Shoonya();

if ($api->login()) {
    echo 'Successfully Login....' . PHP_EOL;
    $portfolio = $api->getHoldings();
    foreach ($portfolio as $key => $data) {
        $p[] = [$data->exch_tsym[1]->tsym, $data->npoadqty,$data->upldprc , 
            $data->npoadqty*$data->upldprc];
        
    }
    $table = new Console_Table();
    echo $table->fromArray(['tysm','qty','avgp','invst','%pl'],$p);
}


if($api->logout()){
    echo 'Successfully Logout....' .PHP_EOL;
}