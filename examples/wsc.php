<?php

require 'vendor/autoload.php';

use Core\Shoonya;

$loop = React\EventLoop\Loop::get();
$hs = json_encode(['t' => 'h']);

$api = new Shoonya();
if ($api->login()) {
    echo 'Loggedin ....!' . PHP_EOL;
    echo 'attemping to connect the webscoket server...' . PHP_EOL;

    \Ratchet\Client\connect('wss://api.shoonya.com/NorenWSTP/')->then(function ($conn) use ($loop, $hs, $api) {
        $loop->addPeriodicTimer(3000, function () use ($conn, $hs) {
            $conn->send($hs);
        });

        $conn->send($api->connectWS());

        $conn->on('message', function ($msg) use ($conn, $api) {
            $res = json_decode($msg);
            echo "Received: {$res->t}\n";
            switch ($res->t) {
                case 'ck':
                    echo 'Conected TO webSocketServer' . PHP_EOL;
                    $conn->send($api->subscribe('BSE|543257',$api::FeedSnapQuoate));
                    break;
                case 'tk' || 'tf':
                    echo 'touchline task' . PHP_EOL;
                    cls();
                    print_r($res);
                    break;
                case 'dk' || 'df':
                    file_put_contents('irfc.json', $msg, FILE_APPEND);
                    system('clear');
                    print_r($res);
                    break;
                case 'om':
                    break;
                default:
                    $conn->send($api->unsubscribe('BSE|543257',$api::FeedSnapQuoate));
                    $conn->close();
                    break;
            }
        });
    }, function ($e) use ($api) {
                echo "Could not connect: {$e->getMessage()}\n";
                echo ($api->loadChart()) ? 'logout succesfully' . PHP_EOL : 'failed to logout' . PHP_EOL;
            });
    #$loop->run();
}

function trigger($data) {
  foreach($data as $single) {
    $implode[] = implode(', ', $single);
  }
  echo implode(', ', $implode) .PHP_EOL;
}

function rcvQuotes($data) {
    
}

function rcvOrders($data) {
    
}

function open($data) {
    global $api;
    $instruments = 'NSE|22#BSE|500400';
    $api->subscribe($instruments);
}

function cls() {
    print("\033[2J\033[;H");
}
