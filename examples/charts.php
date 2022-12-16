<?php
require 'vendor/autoload.php';

/**
 * @name ChartIQ
 * @author Shubham Chaudhary <ghost.jat@gmail.com>
 */

use gtk\core;
use gtk\webView;
use gtk\widget\window;
use Core\Shoonya;

$api = new Shoonya();
if($api->login()) {
    echo 'Successfully Login....' . PHP_EOL;
    $url = 'https://trade.shoonya.com/';
    $chart = ['FCIQ/chartiq.html?','NorenCharts/?'];

    $window = new window();
    $window->set_title('Shoonya-php');
    $window->set_default_size(1140, 760);
    $webView = new webView();
    $window->add($webView);
    
    $cred = $api->getSessionData();
    $encode = base64_encode('user=' .$cred->uid .'&token=' . $cred->jKey .'&exch_tsym=BSE:CUB:CUB&p=Web').PHP_EOL; 
    $webView->loadURL($url.$chart[1].$encode);
    $window->show_all();
    $window->connect('delete-event', function()use($api){
        if($api->logout()) {
            echo 'Successfully logout....' . PHP_EOL;
            core::main_quit();
        }
    });
    core::main();
}
