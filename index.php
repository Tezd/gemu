<?php

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;

$app = new Gemu\Core\Application();

/** @todo make that purpose be handled by gateway? */
$app->match('{purpose}/{gateway}/{endPoint}', function (Request $request, $purpose, $gateway) use ($app)
{
    return $app['gemu.factory']->getGateway($gateway)->$purpose($request);
})->assert('purpose', 'emulate|service');

$app->get('/', 'gemu.controller:homepage')->bind('homepage');

$app->get('remote/optin', 'gemu.controller:optin')->bind('optin');

$app->post('save/transaction', function(Request $request) use ($app) {
    return $app->json(
        array(
            'id' => $app['gemu.factory']
                ->getCache()
                ->saveParams(
                    array(
                        'config' => $request->request->all()
                    )
                )
        )
    );
});

//$app->get('log', function(Request $request) use ($app) {
//
//    header("Content-Type: text/event-stream\n\n");
//
//    /** @type \Gemu\Core\Cache $cache */
//    $cache = $app['gemu.factory']->getCache();
//    $transactionId = $request->query->get('transactionId');
//
//    while (1) {
//        $log = $cache->popLog($transactionId);
//
//        if ($log) {
//            echo "data: " . $log . "\n\n";
//        }
//
//        ob_flush();
//        flush();
//
//        usleep(250000);
//    }
//});

$app->run();
