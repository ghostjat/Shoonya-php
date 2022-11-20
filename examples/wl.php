<?php

require 'vendor/autoload.php';

use Core\Api\Shoonya;

$api = new Shoonya();
$scip = ['asahiindia', 'ashoka', 'gujalkali', 'cesc', 'pnb', 'jindalpoly'];
foreach ($scip as $value) {
    $code[] = 'BSE|' . $api->getScripToken($value);
}
$table = new Console_Table();
if($api->login()) {
    echo 'Successfully Login....' . PHP_EOL;
    $wl = $api->getWatchListNames();
    foreach ($wl as $w) {
        if($w == 5) {
            $add = $api->addScripWatchList("$w", $code);
            if ($add != false) {
                echo "fetching $w wl form server ... " . PHP_EOL;
                $scrip = $api->getWatchList($w);
                foreach ($scrip as $data) {
                    $wlData[] = [$data->tsym, $data->exch];
                }
                echo $table->fromArray(['Scrip', 'Exch'], $wlData) . PHP_EOL;
                unset($wlData);
            }
        }
    }
}

if($api->logout()){
    echo 'Successfully Logout....' .PHP_EOL;
}