<?php

namespace Gemu\Gateway\NetM;

use Gemu\Core\Gateway\EndPoint\Emulator as BaseEmulator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @todo create proper xml
 * @todo make functions private
 * @todo make function names better
 * Class Emulator
 * @package Gemu\Gateway\NetM
 */
final class Emulator extends BaseEmulator
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return null|string
     */
    protected function getEndPoint(Request $request)
    {
        return $request->get('RequestType') ?
            $request->get('RequestType') :
            parent::getEndPoint($request);
    }

    /**
     * @param string $endPoint
     *
     * @return string
     */
    protected function getLocalUrl($endPoint)
    {
        return 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'].$endPoint;
    }

    /**
     * @param array $params
     */
    protected function mergeParams(array &$params)
    {
        $params = array_merge($params, $this->cache->loadParams());
        $this->cache->updateParams($params);
    }

    /**
     * @param string $transaction_id
     * @param array $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function prepareSubscription($transaction_id, array $request)
    {
        $this->mergeParams($request);

        $paymentUrl = $this->makeUrl(
            $this->getLocalUrl('/emulate/NetM/paymenturl'),
            [ 'rid' => $transaction_id ]
        );

        if (!empty($request['config']['low_balance'])) {
            $statusCode = 305;
            $statusText = 'Low credit';
        } else {
            $statusCode = 0;
            $statusText = 'OK';
        }

        if ($request['config']['operator'] > 2) {
            $this->cache->pushInfo('Operator hosts optin1 and optin2 pages.');
            $paymentUrl = $this->makeUrl(
                $this->getLocalUrl('/emulate/NetM/optin'),
                [ 'rid' => $transaction_id ]
            );
        }

        // special case of o2 wifi
        if ($request['config']['operator'] == 4 && $request['config']['flow'] == 'wifi') {
            $paymentUrl = $this->makeUrl(
                $this->getLocalUrl('/emulate/NetM/o2msisdn'),
                [ 'rid' => $transaction_id ]
            );
        }

        $s = <<<HERE
<?xml version="1.0" encoding="ISO-8859-1"?>
<Response type="PrepareSubscription">
  <StatusCode>$statusCode</StatusCode>
  <StatusText>$statusText</StatusText>
  <TransactionType>synchronous</TransactionType>
  <RequestID>$transaction_id</RequestID>
  <TransactionID>$transaction_id</TransactionID>
  <ValidityPeriod>216000</ValidityPeriod>
  <PaymentURL>$paymentUrl</PaymentURL>
</Response>
HERE;

        return new Response($s);
    }

    /**
     * @param string $transaction_id
     * @param array $params
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function checkTan($transaction_id, array $params)
    {
        $this->mergeParams($params);

        if ($params['Tan'] == '1234') {
            $statusCode = 0;
            $statusText = 'PIN correct';
            $this->cache->pushInfo('Pin verified');
            $subId = substr($transaction_id, 0, 48);
            $this->cache->pushInfo('Subscription successful. Subscription ID: ' . $subId);
        } else {
            $statusCode = 250;
            $statusText = 'PIN wrong';
            $this->cache->pushInfo('Invalid pin');
        }

        $s = <<<HERE
<?xml version="1.0" encoding="ISO-8859-1"?>
<Response type="CheckTan">
  <StatusCode>$statusCode</StatusCode>
  <StatusText>$statusText</StatusText>
  <TransactionType>synchronous</TransactionType>
  <RequestID>$transaction_id</RequestID>
  <TransactionID>$transaction_id</TransactionID>
</Response>
HERE;

        return new Response($s);
    }

    /**
     * @param string $transaction_id
     * @param array $params
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function queryInfo($transaction_id, array $params)
    {
        $this->mergeParams($params);
        $now = date('Y-m-d H:i:s');

        if ($params['config']['flow'] == '3g' || $params['config']['operator'] == 4) {
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
  <RequestID>$transaction_id</RequestID>
  <TransactionID>$transaction_id</TransactionID>
  <OperatorID>{$params['config']['operator']}</OperatorID>
  <PaymentOperatorID>{$params['config']['operator']}</PaymentOperatorID>
  <Description>{$params['Description']}</Description>
  <Destination>${msisdn}</Destination>
  <TransactionStatusCode>1</TransactionStatusCode>
  <TransactionStatusText>Transmitted TAN to MS, waiting for TAN input</TransactionStatusText>
  <Subscriptions>
    <Number>1</Number>
    <Subscription>
      <SubscriptionID>$transaction_id</SubscriptionID>
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
      <StartTimestamp>$now</StartTimestamp>
      <LastPeriodFeeTimestamp>$now</LastPeriodFeeTimestamp>
    </Subscription>
  </Subscriptions>
</Response>
HERE;
        return new Response($s);
    }

    /**
     * @param string $transaction_id
     * @param array $params
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function prepareInfo($transaction_id, array $params)
    {
        $this->mergeParams($params);
        $infoUrl = $this->makeUrl($this->getLocalUrl('/emulate/NetM/detectinfo'));
        $s = <<<HERE
<?xml version="1.0" encoding="ISO-8859-1"?>
<Response type="PrepareInfo">
  <StatusCode>0</StatusCode>
  <StatusText>OK</StatusText>
  <RequestID>$transaction_id</RequestID>
  <TransactionID>$transaction_id</TransactionID>
  <InfoURL>${infoUrl}</InfoURL>
</Response>
HERE;
        return new Response($s);
    }

    /**
     * @param string $transaction_id
     * @param array $params
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function paymentUrl($transaction_id, array $params)
    {
        $this->mergeParams($params);

        $url = $this->makeUrl($this->getLocalUrl('/emulate/NetM/confirm'), [ 'rid' => $transaction_id ]);

        if ($params['config']['operator'] > 2) {
            $fContent = file_get_contents('optin.html');
            $fContent = str_replace('$TITLE', 'Opt-in 2', $fContent);
            $fContent = str_replace('$BANNER', $params['PurchaseBanner'], $fContent);
            $fContent = str_replace('$IMAGE', $params['PurchaseImage'], $fContent);
            $fContent = str_replace('$URL', $url, $fContent);
            return new Response($fContent);
        }

        $contents = file_get_contents($params['ConfirmationURL']);
        $contents = str_replace('$PRODUCT_URL', $url, $contents);

        $this->cache->pushInfo('Going to Optin2 page.');

        return new Response($contents);
    }

    /**
     * @param string $transaction_id
     * @param array $params
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function detectInfo($transaction_id, array $params)
    {
        $this->mergeParams($params);

        if ($params['config']['flow'] == '3g') {
            $this->cache->pushInfo(
                sprintf(
                    'MSISDN detected as %s. Initiating  3G flow.',
                    $params['config']['msisdn']
                )
            );
            $code = 0;
        } else {
            $this->cache->pushInfo('MSISDN not detected. Initiating Wifi flow.');
            $code = 151;
        }

        return new RedirectResponse(
            $params['CustomerURL'].'?'.http_build_query(
                [
                    'trid' => $transaction_id,
                    'code' => $code,
                    'rid' => $transaction_id
                ]
            )
        );
    }

    /**
     * @param string $transaction_id
     * @param array $params
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function optIn($transaction_id, array $params)
    {
        $this->mergeParams($params);
        $url = $this->makeUrl($this->getLocalUrl('/emulate/NetM/paymenturl'), [ 'rid' => $transaction_id ]);

        $fContent = file_get_contents('optin.html');
        $fContent = str_replace('$TITLE', 'Opt-in 1', $fContent);
        $fContent = str_replace('$BANNER', $params['PurchaseBanner'], $fContent);
        $fContent = str_replace('$IMAGE', $params['PurchaseImage'], $fContent);
        $fContent = str_replace('$URL', $url, $fContent);
        $this->cache->pushInfo('Going to Optin1 page.');

        return new Response($fContent);
    }

    /**
     * @param string $transaction_id
     * @param array $params
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function o2msisdn($transaction_id, array $params)
    {
        $this->mergeParams($params);
        return new RedirectResponse(
            $this->makeUrl(
                $params['ProductURL'],
                [
                    'rid' => $transaction_id,
                    'code' => 0,
                    'sid' => $transaction_id,
                ]
            )
        );
    }

    /**
     * @param string $transaction_id
     * @param array $params
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function confirm($transaction_id, array $params)
    {
        $this->mergeParams($params);
        $subId = substr($transaction_id, 0, 48);
        $this->cache->pushInfo('Subscription successful. Subscription ID: ' . $subId);
        return new RedirectResponse(
            $this->makeUrl(
                $params['ProductURL'],
                [ 'rid' => $transaction_id ]
            )
        );
    }

    /**
     * @param string $name
     *
     * @param array $data
     *
     * @return string
     */
    protected function getTransactionId($name, array $data)
    {
        switch(strtolower($name))
        {
            case 'o2msisdn':
            case 'confirm':
            case 'optin':
            case 'detectinfo':
            case 'paymenturl':
                return $data['rid'];
            case 'prepareinfo':
            case 'queryinfo':
            case 'checktan':
            case 'preparesubscription':
                return $data['RequestID'];
        }
    }
}
