<?php

require_once __DIR__.'/vendor/autoload.php';
require_once 'functions.php';

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app = new Silex\Application();

$requestTypes = array(
    'PrepareInfo' => array(
        'CustomerURL',
        'M2M',
        'Password',
        'RequestID',
        'ServiceID',
        'ServiceType',
        'Username',
        'Version',
    ),
    'PrepareSubscription' => array(
        'Category',
        'ChargeFirstFee',
        'ConfirmationURL',
        'Currency',
        'Description',
        'Destination',
        'ErrorURL',
        'ItemFee',
        'M2M',
        'Password',
        'ProductURL',
        'PurchaseAboTool',
        'PurchaseBanner',
        'PurchaseContact',
        'PurchaseDescription',
        'PurchaseImage',
        'PurchaseImprint',
        'PurchaseTAC',
        'ReplyPath',
        'RequestID',
        'RequestType',
        'ServiceID',
        'ServiceType',
        'SubscriptionFee',
        'Username',
        'Version',
    ),
    'CheckTan' => array(
        'M2M',
        'Password',
        'RequestID',
        'RequestType',
        'ServiceID',
        'ServiceType',
        'Tan',
        'TransactionID',
        'Username',
        'Version',
    ),
    'QueryInfo' => array(
        'Destination',
        'M2M',
        'Password',
        'RequestID',
        'RequestType',
        'ServiceID',
        'ServiceType',
        'TransactionID',
        'Username',
        'Version',
    ),
);

$keyPrefix = "netm::emulator::";

$app->match('/transport/PaymentGateway', function (Request $request) use ($requestTypes, $keyPrefix) {
    $requestId = $request->get('RequestID');

    $requestType = $request->get('RequestType');

    if (!$requestType || !isset($requestTypes[$requestType])) {
        throw new Exception(sprintf("$requestType missing"));
    }

    $params = array();
    $params['TransactionID'] = $requestId;

    //$params['config'] = json_decode(base64_decode($requestId, true), true);
    $params['config'] = array();
    $params['config']['low_balance'] = false;
    $params['config']['flow'] = 'wap';
    $params['config']['msisdn'] = '00491711049395';

    foreach ($requestTypes[$requestType] as $param) {
        $params[$param] = $request->get($param);
    }

    $prevParams = fromRedis($requestId);
    if ($prevParams) {
        $params = array_merge($prevParams, $params);
    }

    toRedis($requestId, $params);

    return new Response($requestType($requestId), 200, array('Content-Type' => 'application/xml'));
});

$app->match('/detectinfo', function (Request $request) use ($requestTypes, $keyPrefix) {
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

$app->match('/paymenturl', function (Request $request) use ($requestTypes, $keyPrefix) {
    $requestId = $request->get('rid');
    $params = fromRedis($requestId);

    $confirmationURL = 'http://172.19.0.2' . parse_url("http://mcb-test.sam-media.com:8082/mcb-ads/optin2.php/SWINGCOPTERS", PHP_URL_PATH);

    $contents = file_get_contents($confirmationURL);
    $url = $params['ProductURL'] . '?rid=' . $requestId;
    $contents = str_replace('$PRODUCT_URL', $url, $contents);

    return new Response($contents);
});

$app->match('/rid', function (Request $request) use ($requestTypes, $keyPrefix) {
    $redis = new \LibSam\Cache\RedisCache();
    $requestId = $request->get('rid');
    $params = json_decode($redis->get($keyPrefix.$requestId), true);
    die(var_dump($params));
});


$app['debug'] = true;
$app->run();
