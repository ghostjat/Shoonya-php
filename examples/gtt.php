<?php
/**
 * @package Shoonya-php
 * @category GTT Order
 * @author Shubham Chaudhary <ghost.jat@gamil.com> 
 */

require 'vendor/autoload.php';

$api = new Core\Shoonya();

if($api->login()) {
    echo 'Successfully Login....' . PHP_EOL;
    $portfolio = $api->getHoldings();
    foreach ($portfolio as $key => $data) {
        $ltp = $api->getLTP($data->exch_tsym[1]->tsym);
        $ival = $data->npoadqty*$data->upldprc;
        $cval = $ltp*$data->npoadqty;
        $pl = round($cval - $ival,2);
        $p[] = [$data->exch_tsym[1]->tsym, $data->npoadqty,
                $ltp,$data->upldprc, $ival,$cval, $pl, round(($pl/$ival)*100, 2)];
        $tival[] = $ival;
        $tcval[] = $cval;
    }
    $tival = array_sum($tival);
    $tcval = array_sum($tcval);
    $pl = $tcval - $tival;
    $plpcnt = round(($pl/$tival) *100, 2);
    echo " Total Ival:- { $tival } Total Cval:- { $tcval } Total PL :- { $pl } PL% { $plpcnt }" .PHP_EOL;
    $table = new Console_Table();
    echo $table->fromArray(['Tysm','Qty','Ltp','Avgp','Invst','Cval','PL','PL%'],$p);
    $con = false;
    $cont = readline('you want to place GTT/GTC Y/N  ');
    if ($cont == 'Y' || $cont == 'y') {
        $con = true;
        $cmd = new stdClass();
    }
    while($con==true) {
        echo 'enter scrip code' .PHP_EOL;
        $cmd->tsym = readline('');
        if(!empty($cmd->tsym)) {
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
                            echo 'You enterd following deatils for GTT order ' . PHP_EOL;
                            echo "place ". (($cmd->bs == 'B'|| $cmd->bs == 'b') ? "BUY": "SELL") ."-GTT for $cmd->qty of $cmd->tsym with Price $cmd->bp and Tigger Price $cmd->tp" . PHP_EOL;
                            $cmd->confm = readline('to punch order please type Y ');
                            if ($cmd->confm == 'Y' || $cmd->confm == 'y') {
                                $cmd->gtt = $api->gttOrder((($cmd->bs == 'B'|| $cmd->bs == 'b') ? $api::Buy: $api::Sell), $api::Delivery, 'BSE', $cmd->tsym, $cmd->tp, $cmd->qty, $cmd->bp, $api::AITG);
                                if ($cmd->gtt) {
                                    echo 'gtt order has been placed for'. $cmd->tsym .'@ ' . date('h:i:m') . PHP_EOL;
                                    $api->telegram('gtt order has been placed for'. $cmd->tsym .'@ ' . date('h:i:m'));
                                }
                            }
                        }
                    }
                }
            }
            
        }
        $cont = readline('you want to place more odere Y/N  ');
        if($cont == 'N'||$cont == 'n') {
            $con = false;
        }
    }
}


if($api->logout()){
    echo 'Successfully Logout....' .PHP_EOL;
}