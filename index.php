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

    $params['config'] = json_decode(base64_decode($requestId, true), true);

    if ($params['config']) {
        $params['config']['low_balance'] = isset($params['config']['low_balance']);
    } else {
        $params['config'] = array();
        $params['config']['low_balance'] = false;
        $params['config']['flow'] = '3g';
        $params['config']['msisdn'] = '00491711049389';
        $params['config']['operator'] = '3';
    }

    putLog($requestId, "$requestType call received");

    foreach ($_REQUEST as $key => $value) {
        //putLog($requestId, "Got param: $key => $value");
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
    $requestId = $request->get('rid');
    $params = fromRedis($requestId);

    if ($params['config']['flow'] == '3g') {
        putLog($requestId, sprintf("MSISDN detected as %s. Initiating  3G flow.", $params['config']['msisdn']));
        $code = '0';
    } else {
        putLog($requestId, 'MSISDN not detected. Initiating Wifi flow.');
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

$app->match('/optin', function (Request $request) {
    $requestId = $request->get('rid');
    $params = fromRedis($requestId);

    $url = makeUrl('/paymenturl', $requestId);

    $fContent = file_get_contents('optin.html');
    $fContent = str_replace('$TITLE', 'Opt-in 1', $fContent);
    $fContent = str_replace('$BANNER', $params['PurchaseBanner'], $fContent);
    $fContent = str_replace('$IMAGE', $params['PurchaseImage'], $fContent);
    $fContent = str_replace('$URL', $url, $fContent);

    putLog($requestId, 'Going to Optin1 page.');

    return new Response($fContent);
});

$app->match('/paymenturl', function (Request $request) {
    $requestId = $request->get('rid');
    $params = fromRedis($requestId);

    $url = makeUrl('/confirm', $requestId);

    if (Operator::isHostedExternaly($params['config']['operator']))
    {
        $fContent = file_get_contents('optin.html');
        $fContent = str_replace('$TITLE', 'Opt-in 2', $fContent);
        $fContent = str_replace('$BANNER', $params['PurchaseBanner'], $fContent);
        $fContent = str_replace('$IMAGE', $params['PurchaseImage'], $fContent);
        $fContent = str_replace('$URL', $url, $fContent);
        return new Response($fContent);
    }

    $contents = file_get_contents($params['ConfirmationURL']);
    $contents = str_replace('$PRODUCT_URL', $url, $contents);

    putLog($requestId, 'Going to Optin2 page.');

    return new Response($contents);
});

$app->match('/confirm', function (Request $request) {
    $requestId = $request->get('rid');
    $params = fromRedis($requestId);

    $subId = substr($requestId, 0, 48);
    putLog($requestId, 'Subscription successful. Subscription ID: ' . $subId);

    $url = $params['ProductURL'] . '?rid=' . $requestId;
    return new RedirectResponse($url);
});

$app->match('/rid', function (Request $request) {
    $params = fromRedis($request->get('rid'));
    die(var_dump($params));
});


$app['debug'] = true;
$app->run();
