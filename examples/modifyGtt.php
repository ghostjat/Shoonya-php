<?php

require 'vendor/autoload.php';

use Core\Shoonya;

$api = new Shoonya();
if($api->login()) {
    echo 'Login successfully!' .PHP_EOL;
    $gttData = $api->getPendingGtt();
    foreach ($gttData as $gtt) {
        $data[] = [$gtt->tsym,$gtt->exch,$gtt->al_id,$gtt->trantype,$gtt->qty,$gtt->d,$gtt->prc,$gtt->prd];
    }
    $table = new Console_Table();
    echo $table->fromArray(['Tsym','Exch','Id','Typ','Qty','Trig','Prc','Prd'], $data);
    $con = false;
    $cont = readline('you want to modify GTT/GTC Y/N  ');
    if ($cont == 'Y' || $cont == 'y') {
        $con = true;
        $cmd = new stdClass();
    }
    while($con == true) {
        echo 'to modify GTT enter the ID ' .PHP_EOL;
        $cmd->gttid = readline();
        echo 'enter scrip code' .PHP_EOL;
        $cmd->tsym = readline();
        if(!empty($cmd->gttid) && !empty($cmd->tsym)) {
            echo 'for sell type S, for buy type B' .PHP_EOL;
            $cmd->bs = readline();
            if(!empty($cmd->bs)) {
                echo 'enter the quantity ' .PHP_EOL;
                $cmd->qty = readline('');
                if(!empty($cmd->qty)) {
                    echo 'enter tigger price for gtt' .PHP_EOL;
                    $cmd->tp = readline('');
                    if(!empty($cmd->tp)) {
                        echo 'enter buying/selling price' .PHP_EOL;
                        $cmd->bp = readline('');
                        if (!empty($cmd->bp)) {
                            echo 'You enterd following deatils to modiy GTT order with ID:- ' . $cmd->gttid . PHP_EOL;
                            echo "place ". (($cmd->bs == 'B'|| $cmd->bs == 'b') ? "BUY": "SELL") ."-GTT for $cmd->qty of $cmd->tsym with Price $cmd->bp and Tigger Price $cmd->tp" . PHP_EOL;
                            $cmd->confm = readline('to modify GTT please type Y ');
                            if ($cmd->confm == 'Y' || $cmd->confm == 'y') {
                                $cmd->gtt = $api->modifyGtt($cmd->gttid,(($cmd->bs == 'B'|| $cmd->bs == 'b') ? $api::Buy: $api::Sell), $api::Delivery, 'BSE', $cmd->tsym, $cmd->tp, $cmd->qty, $cmd->bp, $api::AITG);
                                if ($cmd->gtt) {
                                    echo 'GTT has been modified for '. $cmd->gttid . ' - ' . $cmd->tsym .' @ ' . date('h:i:m') . PHP_EOL;
                                    $api->telegram('gtt has been modified for ' . $cmd->gttid . ' - ' . $cmd->tsym .'@ ' . date('h:i:m'));
                                }
                            }
                        }
                    }
                }
            }
            
        }
        $cont = readline('you want to modify more GTT Y/N  ');
        if($cont == 'N'||$cont == 'n') {
            $con = false;
        }
    }
}

if($api->logout()){
    echo 'Logout successfully!' .PHP_EOL;
}

