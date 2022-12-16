<?php

/**
 * 
 * @name getPortfolio
 * @author Shubham Chaudhary <ghost.jat@gamil.com> 
 */

require_once 'vendor/autoload.php';

use Core\Shoonya;

$api = new Shoonya();

if ($api->login()) {
    echo 'Successfully Login....' . PHP_EOL;
    $i = 0;
    while($i < 2) {
        getUpdate();
        sleep(5);
        system('clear');
        $i++;
    }
}

function getUpdate() {
    global $api;
    $portfolio = $api->getHoldings();
    foreach ($portfolio as $key => $data) {
        $ltp = $api->getLTP($data->exch_tsym[1]->tsym);
        $ival = $data->npoadqty*$data->upldprc;
        $cval = $ltp*$data->npoadqty;
        $pl = $cval - $ival;
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
}
 if($api->logout()){
    echo 'Successfully Logout....' .PHP_EOL;
}