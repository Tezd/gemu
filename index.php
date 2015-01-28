<?php

require_once __DIR__.'/vendor/autoload.php';

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
        'Username',
        'Version',
    ),
);

$keyPrefix = "netm::emulator::";

$app->match('/transport/PaymentGateway', function (Request $request) use ($requestTypes, $keyPrefix) {
    $requestId = $request->get('RequestID');
    $requestType = $request->get('RequestType');

    $params = array();
    $params['TransactionID'] = $requestId;

    foreach ($requestTypes[$requestType] as $param) {
        $params[$param] = $request->get($param);
    }

    $redis = new \LibSam\Cache\RedisCache();
    $redis->set($keyPrefix.$requestId, json_encode($params));


    if ($requestType == 'PrepareInfo') {
        $s = <<<HERE
<?xml version="1.0" encoding="ISO-8859-1"?>
<Response type="PrepareInfo">
  <StatusCode>0</StatusCode>
  <StatusText>OK</StatusText>
  <RequestID>${requestId}</RequestID>
  <TransactionID>${requestId}</TransactionID>
  <InfoURL>http://172.19.0.2/netm-emulator/index.php/detectinfo</InfoURL>
</Response>
HERE;
        return new Response($s, 200, array('Content-Type' => 'application/xml'));
    } else if ($requestType == 'PrepareSubscription') {

        $s = <<<HERE
<?xml version="1.0" encoding="ISO-8859-1"?>
<Response type="PrepareSubscription">
  <StatusCode>0</StatusCode>
  <StatusText>OK</StatusText>
  <TransactionType>synchronous</TransactionType>
  <RequestID>${requestId}</RequestID>
  <TransactionID>${requestId}</TransactionID>
  <ValidityPeriod>216000</ValidityPeriod>
  <PaymentURL>http://pgw.wap.net-m.net/pgw/io/cp/detect/1511111254/31dd9a2130825406be857a71ef0abdb3</PaymentURL>
</Response>
HERE;

        return new Response($s, 200, array('Content-Type' => 'application/xml'));
    } else if ($requestType == 'CheckTan') {

        $s = '<?xml version="1.0" encoding="ISO-8859-1"?>
<Response type="CheckTan">
  <StatusCode>0</StatusCode>
  <StatusText>PIN correct</StatusText>
  <TransactionType>synchronous</TransactionType>
  <RequestID>${requestId}</RequestID>
  <TransactionID>${requestId}</TransactionID>
</Response>';

        return new Response($s, 200, array('Content-Type' => 'application/xml'));
    } else if ($requestType == 'QueryInfo') {

        $s = '<?xml version="1.0" encoding="ISO-8859-1"?>
<Response type="QueryInfo">
  <StatusCode>0</StatusCode>
  <StatusText>OK</StatusText>
  <RequestID>2362e55c</RequestID>
  <TransactionID>P1408202022</TransactionID>
  <OperatorID>1</OperatorID>
  <PaymentOperatorID>1</PaymentOperatorID>
  <Description>SUPERBATTERY</Description>
  <Destination>00491711049392</Destination>
  <TransactionStatusCode>1</TransactionStatusCode>
  <TransactionStatusText>Transmitted TAN to MS, waiting for TAN input</TransactionStatusText>
  <Subscriptions>
    <Number>1</Number>
    <Subscription>
      <SubscriptionID>S0466154956</SubscriptionID>
      <SubscriptionStatusCode>1</SubscriptionStatusCode>
      <SubscriptionStatusText>Subscription ready</SubscriptionStatusText>
      <ServiceID>web_de_abo</ServiceID>
      <ServiceType>web</ServiceType>
      <SubscriptionFee>699</SubscriptionFee>
      <ItemFee>0</ItemFee>
      <Currency>EURO-CENT</Currency>
      <VAT>19.0</VAT>
      <OperatorID>1</OperatorID>
      <PaymentOperatorID>1</PaymentOperatorID>
      <Description>BEEMOBI - SWINGCOPTERS</Description>
      <StartTimestamp>2014-12-22 05:19:16</StartTimestamp>
      <LastPeriodFeeTimestamp>2014-12-22 05:19:53</LastPeriodFeeTimestamp>
    </Subscription>
  </Subscriptions>
</Response>';

        return new Response($s, 200, array('Content-Type' => 'application/xml'));
    }


    return new Response('hello world');
});

$app->match('/detectinfo', function (Request $request) use ($requestTypes, $keyPrefix) {
    $redis = new \LibSam\Cache\RedisCache();
    $requestId = $request->get('rid');
    $params = json_decode($redis->get($keyPrefix.$requestId), true);

    return new RedirectResponse($params['CustomerURL'].'?'.http_build_query(
        array(
            'trid' => $params['TransactionID'],
            'code' => '151',
            'rid' => $params['RequestID'],
        )
    ));
});


$app->match('/rid', function (Request $request) use ($requestTypes, $keyPrefix) {
    $redis = new \LibSam\Cache\RedisCache();
    $requestId = $request->get('rid');
    $params = json_decode($redis->get($keyPrefix.$requestId), true);
    die(var_dump($params));
});


$app['debug'] = true;
$app->run();
