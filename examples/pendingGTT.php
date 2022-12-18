<?php

require 'vendor/autoload.php';

use Core\Shoonya;

$brokerApi = new Shoonya();
if($brokerApi->login()) {
    echo 'Login successfully!' .PHP_EOL;
    $gttData = $brokerApi->getPendingGtt();
    foreach ($gttData as $gtt) {
        $data[] = [$gtt->tsym,$gtt->exch,$gtt->al_id,$gtt->trantype,$gtt->qty,$gtt->d,$gtt->prc,$gtt->prd];
    }
    $table = new Console_Table();
    echo $table->fromArray(['Tsym','Exch','Id','Typ','Qty','Trig','Prc','Prd'], $data);
}

if($brokerApi->logout()){
    echo 'Logout successfully!' .PHP_EOL;
}
