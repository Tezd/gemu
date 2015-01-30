<?php

require_once __DIR__.'/vendor/autoload.php';
require_once 'functions.php';

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app = new Silex\Application();

$app->match('/transport/PaymentGateway', function (Request $request) {
    $requestType = $request->get('RequestType');

    $requestTypes = array(
        'CheckTan',
        'PrepareInfo',
        'PrepareSubscription',
        'QueryInfo',
    );

    if (!$requestType || !in_array($requestType, $requestTypes)) {
        throw new Exception(sprintf("$requestType missing"));
    }

    $requestId = $request->get('RequestID');

    $params = array();
    $params['TransactionID'] = $requestId;
    //$params['config'] = json_decode(base64_decode($requestId, true), true);
    $params['config'] = array();
    $params['config']['low_balance'] = false;
    $params['config']['flow'] = 'wap';
    $params['config']['msisdn'] = '00491711049388';
    $params['config']['operator'] = '2';

    foreach ($_REQUEST as $key => $value) {
        $params[$key] = $value;
    }

    $prevParams = fromRedis($requestId);
    if ($prevParams) {
        $params = array_merge($prevParams, $params);
    }

    toRedis($requestId, $params);

    return new Response($requestType($requestId), 200, array('Content-Type' => 'application/xml'));
});

$app->match('/detectinfo', function (Request $request) {
    $params = fromRedis($request->get('rid'));

    if ($params['config']['flow'] == 'wap') {
        $code = '0';
    } else {
        $code = '151';
    }

    return new RedirectResponse($params['CustomerURL'].'?'.http_build_query(
        array(
            'trid' => $params['TransactionID'],
            'code' => $code,
            'rid' => $params['RequestID'],
        )
    ));
});

$app->match('/paymenturl', function (Request $request) {
    $requestId = $request->get('rid');
    $params = fromRedis($requestId);

    $confirmationURL = 'http://172.19.0.2' . parse_url("http://mcb-test.sam-media.com:8082/mcb-ads/optin2.php/SWINGCOPTERS", PHP_URL_PATH);

    $contents = file_get_contents($confirmationURL);
    $url = $params['ProductURL'] . '?rid=' . $requestId;
    $contents = str_replace('$PRODUCT_URL', $url, $contents);

    return new Response($contents);
});

$app->match('/rid', function (Request $request) {
    $params = fromRedis($request->get('rid'));
    die(var_dump($params));
});


$app['debug'] = true;
$app->run();
