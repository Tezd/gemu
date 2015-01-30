<?php

class Operator
{
    public static function isHostedExternaly($operatorId)
    {
        return $operatorId > 2;
    }
}

function toRedis($requestId, $params)
{
    $redis = new \LibSam\Cache\RedisCache();
    $redis->set('netm::emulator::'.$requestId, json_encode($params), 3600);
}

function fromRedis($requestId)
{
    $redis = new \LibSam\Cache\RedisCache();
    return json_decode($redis->get('netm::emulator::'.$requestId), true);
}

function PrepareInfo($requestId)
{
    $params = fromRedis($requestId);
    $infoUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/netm-emulator/index.php/detectinfo';

    $s = <<<HERE
<?xml version="1.0" encoding="ISO-8859-1"?>
<Response type="PrepareInfo">
  <StatusCode>0</StatusCode>
  <StatusText>OK</StatusText>
  <RequestID>${requestId}</RequestID>
  <TransactionID>${requestId}</TransactionID>
  <InfoURL>${infoUrl}</InfoURL>
</Response>
HERE;

    return $s;
}


function PrepareSubscription($requestId)
{
    $params = fromRedis($requestId);
    $infoUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/netm-emulator/index.php/paymenturl?rid=' . $requestId;

    if ($params['config']['low_balance']) {
        $statusCode = 305;
        $statusText = 'Low credit';
    } else {
        $statusCode = 0;
        $statusText = 'OK';
    }

    if(Operator::isHostedExternaly($params['config']['operator']))
    {
        $fContent = file_get_contents('optin.html');
        $fContent = str_replace('$BANNER', $params['PurchaseBanner'], $fContent);
        $fContent = str_replace('$IMAGE', $params['PurchaseImage'], $fContent);
        $fContent = str_replace('$URL', $infoUrl, $fContent);
        return $fContent;
    }

    $s = <<<HERE
<?xml version="1.0" encoding="ISO-8859-1"?>
<Response type="PrepareSubscription">
  <StatusCode>${statusCode}</StatusCode>
  <StatusText>${statusText}</StatusText>
  <TransactionType>synchronous</TransactionType>
  <RequestID>${requestId}</RequestID>
  <TransactionID>${requestId}</TransactionID>
  <ValidityPeriod>216000</ValidityPeriod>
  <PaymentURL>${infoUrl}</PaymentURL>
</Response>
HERE;

    return $s;
}

function CheckTan($requestId)
{
    $params = fromRedis($requestId);

    if ($params['Tan'] == '1234') {
        $statusCode = 0;
        $statusText = 'PIN correct';
    } else {
        $statusCode = 250;
        $statusText = 'PIN wrong';
    }

    $s = <<<HERE
<?xml version="1.0" encoding="ISO-8859-1"?>
<Response type="CheckTan">
  <StatusCode>$statusCode</StatusCode>
  <StatusText>$statusText</StatusText>
  <TransactionType>synchronous</TransactionType>
  <RequestID>$requestId</RequestID>
  <TransactionID>$requestId</TransactionID>
</Response>
HERE;

    return $s;
}

function QueryInfo($requestId)
{
    $params = fromRedis($requestId);

    $now = date('Y-m-d H:i:s');

    if ($params['config']['flow'] == 'wap') {
        $msisdn = $params['config']['msisdn'];
    } else {
        $msisdn = $params['Destination'];
    }

    if (!isset($params['Description'])) {
        $params['Description'] = '';
        $params['SubscriptionFee'] = '';
    }

    $s = <<<HERE
<?xml version="1.0" encoding="ISO-8859-1"?>
<Response type="QueryInfo">
  <StatusCode>0</StatusCode>
  <StatusText>OK</StatusText>
  <RequestID>${requestId}</RequestID>
  <TransactionID>${requestId}</TransactionID>
  <OperatorID>{$params['config']['operator']}</OperatorID>
  <PaymentOperatorID>{$params['config']['operator']}</PaymentOperatorID>
  <Description>{$params['Description']}</Description>
  <Destination>${msisdn}</Destination>
  <TransactionStatusCode>1</TransactionStatusCode>
  <TransactionStatusText>Transmitted TAN to MS, waiting for TAN input</TransactionStatusText>
  <Subscriptions>
    <Number>1</Number>
    <Subscription>
      <SubscriptionID>${requestId}</SubscriptionID>
      <SubscriptionStatusCode>1</SubscriptionStatusCode>
      <SubscriptionStatusText>Subscription ready</SubscriptionStatusText>
      <ServiceID>web_de_abo</ServiceID>
      <ServiceType>web</ServiceType>
      <SubscriptionFee>{$params['SubscriptionFee']}</SubscriptionFee>
      <ItemFee>0</ItemFee>
      <Currency>EURO-CENT</Currency>
      <VAT>19.0</VAT>
      <OperatorID>1</OperatorID>
      <PaymentOperatorID>1</PaymentOperatorID>
      <Description>{$params['Description']}</Description>
      <StartTimestamp>${now}</StartTimestamp>
      <LastPeriodFeeTimestamp>${now}</LastPeriodFeeTimestamp>
    </Subscription>
  </Subscriptions>
</Response>
HERE;

    return $s;
}
