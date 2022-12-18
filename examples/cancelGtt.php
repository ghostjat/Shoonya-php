<?php

require 'vendor/autoload.php';

use Core\Shoonya;

$api = new Shoonya();
if ($api->login()) {
    echo 'Login successfully!' . PHP_EOL;
    $gttData = $api->getPendingGtt();
    foreach ($gttData as $gtt) {
        $data[$gtt->al_id] = [$gtt->tsym, $gtt->exch, $gtt->al_id, $gtt->trantype, $gtt->qty, $gtt->d, $gtt->prc, $gtt->prd];
    }
    $table = new Console_Table();
    echo $table->fromArray(['Tsym', 'Exch', 'Id', 'Typ', 'Qty', 'Trig', 'Prc', 'Prd'], $data);
    $cont = readline('you want to cancel GTT/GTC Y/N  ');
    $con = false;
    if ($cont == 'Y' || $cont == 'y') {
        $con = true;
        $cmd = new stdClass();
    }
    while ($con == true) {
         echo 'to cancle GTT enter the ID ' .PHP_EOL;
         $cmd->gttid = readline();
         if(!empty($cmd->gttid)) {
             $cmd->gtt = $api->cancelGtt($cmd->gttid);
             if ($cmd->gtt) {
                echo 'GTT has been canceled for ' . $cmd->gttid .  ' @ ' . date('h:i:m') . PHP_EOL;
                $api->telegram('gtt has been canceled for ' . $cmd->gttid .  '@ ' . date('h:i:m'));
            }
        }
        else {
            foreach ($data as $key => $data) {
                $cmd->gtt = $api->cancelGtt($key);
                if ($cmd->gtt) {
                    echo 'GTT has been canceled for ' . $key . ' @ ' . date('h:i:m') . PHP_EOL;
                    $api->telegram('gtt has been canceled for ' . $key . '@ ' . date('h:i:m'));
                }
            }
        }
        $cont = readline('you want to cancel more GTT Y/N  ');
        if ($cont == 'N' || $cont == 'n') {
            $con = false;
        }
    }
}

if ($api->logout()) {
    echo 'Logout successfully!' .PHP_EOL;
}