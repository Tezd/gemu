<?php

require_once __DIR__.'/vendor/autoload.php';
require_once 'functions.php';

$requestId = $_GET['rid'];

header("Content-Type: text/event-stream\n\n");

while (1) {
    $log = getLog($requestId);

    if ($log) {
        echo "data: " . $log . "\n\n";
    }

    ob_flush();
    flush();

    usleep(250000);
}

?>

