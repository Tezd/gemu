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
class Emulator extends BaseEmulator
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
        $params = array_merge($params, $this->loadParams());
        $this->updateParams($params);
    }

    /**
     * @param array $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function prepareSubscription(array $request)
    {
        $this->mergeParams($request);

        $paymentUrl = $this->makeUrl(
            $this->getLocalUrl('/emulate/NetM/paymenturl'),
            [ 'rid' => $this->transaction_key ]
        );

        if (!empty($request['config']['low_balance'])) {
            $statusCode = 305;
            $statusText = 'Low credit';
        } else {
            $statusCode = 0;
            $statusText = 'OK';
        }

        if ($request['config']['operator'] > 2) {
            $this->pushInfo('Operator hosts optin1 and optin2 pages.');
            $paymentUrl = $this->makeUrl(
                $this->getLocalUrl('/emulate/NetM/optin'),
                [ 'rid' => $this->transaction_key ]
            );
        }

        // special case of o2 wifi
        if ($request['config']['operator'] == 4 && $request['config']['flow'] == 'wifi') {
            $paymentUrl = $this->makeUrl(
                $this->getLocalUrl('/emulate/NetM/o2msisdn'),
                [ 'rid' => $this->transaction_key ]
            );
        }

        $s = <<<HERE
<?xml version="1.0" encoding="ISO-8859-1"?>
<Response type="PrepareSubscription">
  <StatusCode>$statusCode</StatusCode>
  <StatusText>$statusText</StatusText>
  <TransactionType>synchronous</TransactionType>
  <RequestID>{$this->transaction_key}</RequestID>
  <TransactionID>{$this->transaction_key}</TransactionID>
  <ValidityPeriod>216000</ValidityPeriod>
  <PaymentURL>$paymentUrl</PaymentURL>
</Response>
HERE;

        return new Response($s);
    }

    /**
     * @param array $params
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function checkTan(array $params)
    {
        $this->mergeParams($params);

        if ($params['Tan'] == '1234') {
            $statusCode = 0;
            $statusText = 'PIN correct';
            $this->pushInfo('Pin verified');
            $subId = substr($this->transaction_key, 0, 48);
            $this->pushInfo('Subscription successful. Subscription ID: ' . $subId);
        } else {
            $statusCode = 250;
            $statusText = 'PIN wrong';
            $this->pushInfo('Invalid pin');
        }

        $s = <<<HERE
<?xml version="1.0" encoding="ISO-8859-1"?>
<Response type="CheckTan">
  <StatusCode>$statusCode</StatusCode>
  <StatusText>$statusText</StatusText>
  <TransactionType>synchronous</TransactionType>
  <RequestID>{$this->transaction_key}</RequestID>
  <TransactionID>{$this->transaction_key}</TransactionID>
</Response>
HERE;

        return new Response($s);
    }

    /**
     * @param array $params
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function queryInfo(array $params)
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
  <RequestID>{$this->transaction_key}</RequestID>
  <TransactionID>{$this->transaction_key}</TransactionID>
  <OperatorID>{$params['config']['operator']}</OperatorID>
  <PaymentOperatorID>{$params['config']['operator']}</PaymentOperatorID>
  <Description>{$params['Description']}</Description>
  <Destination>${msisdn}</Destination>
  <TransactionStatusCode>1</TransactionStatusCode>
  <TransactionStatusText>Transmitted TAN to MS, waiting for TAN input</TransactionStatusText>
  <Subscriptions>
    <Number>1</Number>
    <Subscription>
      <SubscriptionID>{$this->transaction_key}</SubscriptionID>
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
        return new Response($s);
    }

    protected function prepareInfo(array $params)
    {
        $this->mergeParams($params);
        $infoUrl = $this->makeUrl($this->getLocalUrl('/emulate/NetM/detectinfo'));
        $s = <<<HERE
<?xml version="1.0" encoding="ISO-8859-1"?>
<Response type="PrepareInfo">
  <StatusCode>0</StatusCode>
  <StatusText>OK</StatusText>
  <RequestID>{$this->transaction_key}</RequestID>
  <TransactionID>{$this->transaction_key}</TransactionID>
  <InfoURL>${infoUrl}</InfoURL>
</Response>
HERE;
        return new Response($s);
    }

    /**
     * @param array $params
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function paymentUrl(array $params)
    {
        $this->mergeParams($params);

        $url = $this->makeUrl($this->getLocalUrl('/emulate/NetM/confirm'), array('rid' => $this->transaction_key));

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

        $this->pushInfo('Going to Optin2 page.');

        return new Response($contents);
    }

    /**
     * @param array $params
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function detectInfo(array $params)
    {
        $this->mergeParams($params);

        if ($params['config']['flow'] == '3g') {
            $this->pushInfo(
                sprintf(
                    'MSISDN detected as %s. Initiating  3G flow.',
                    $params['config']['msisdn']
                )
            );
            $code = 0;
        } else {
            $this->pushInfo('MSISDN not detected. Initiating Wifi flow.');
            $code = 151;
        }

        return new RedirectResponse(
            $params['CustomerURL'].'?'.http_build_query(
                [
                    'trid' => $params['TransactionID'],
                    'code' => $code,
                    'rid' => $this->transaction_key,
                ]
            )
        );
    }

    /**
     * @param array $params
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function optIn(array $params)
    {
        $this->mergeParams($params);
        $url = $this->makeUrl($this->getLocalUrl('/emulate/NetM/paymenturl'), array('rid' => $this->transaction_key));

        $fContent = file_get_contents('optin.html');
        $fContent = str_replace('$TITLE', 'Opt-in 1', $fContent);
        $fContent = str_replace('$BANNER', $params['PurchaseBanner'], $fContent);
        $fContent = str_replace('$IMAGE', $params['PurchaseImage'], $fContent);
        $fContent = str_replace('$URL', $url, $fContent);
        $this->pushInfo('Going to Optin1 page.');

        return new Response($fContent);
    }

    /**
     * @param array $params
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function o2msisdn(array $params)
    {
        $this->mergeParams($params);
        return new RedirectResponse(
            $this->makeUrl(
                $params['ProductURL'],
                [
                    'rid' => $this->transaction_key,
                    'code' => 0,
                    'sid' => $this->transaction_key,
                ]
            )
        );
    }

    /**
     * @param array $params
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function confirm(array $params)
    {
        $this->mergeParams($params);
        $subId = substr($this->transaction_key, 0, 48);
        $this->pushInfo('Subscription successful. Subscription ID: ' . $subId);
        return new RedirectResponse(
            $this->makeUrl(
                $params['ProductURL'],
                [ 'rid' => $this->transaction_key ]
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
    protected function getTransactionKey($name, array $data)
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
