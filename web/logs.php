<?php

require_once __DIR__ . '/../vendor/autoload.php';

$transactionId = $_GET['transactionId'];

header("Content-Type: text/event-stream\n\n");

$cache = new \Gemu\Core\Cache(new Predis\Client());

//while (1) {
$log = $cache->popLog($transactionId);

if ($log) {
    echo "data: " . $log . "\n\n";
}
//    ob_flush();
//    flush();
////    sleep(1);
//}
