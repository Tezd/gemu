<?php

namespace Gemu\Gateway\NetM;

use Gemu\Core\Gateway\EndPoint\Emulator as BaseEmulator;
use Gemu\Core\Gateway\EndPoint\Request as EndPointRequest;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
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
    protected function getEndPoint(SymfonyRequest $request)
    {
        return $request->get('RequestType') ?
            $request->get('RequestType') :
            parent::getEndPoint($request);
    }



    /**
     * @param \Gemu\Core\Gateway\EndPoint\Request $request
     */
    protected function mergeParams(EndPointRequest $request)
    {
        $request->add($this->cache->loadParams());
        $this->cache->updateParams($request->all());
    }

    /**
     * @param \Gemu\Core\Gateway\EndPoint\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function prepareSubscription(EndPointRequest $request)
    {
        $this->mergeParams($request);

        $transaction_id = $request->getTransactionId();
        $paymentUrl = $this->getLocalUrl(
            '/emulate/NetM/paymenturl',
            [ 'rid' => $transaction_id ]
        );

        if (!empty($request->getDeep('config[low_balance]'))) {
            $statusCode = 305;
            $statusText = 'Low credit';
        } else {
            $statusCode = 0;
            $statusText = 'OK';
        }

        if ($request->getDeep('config[operator]') > 2) {
            $this->cache->pushInfo('Operator hosts optin1 and optin2 pages.');
            $paymentUrl = $this->getLocalUrl(
                '/emulate/NetM/optin',
                [ 'rid' => $transaction_id ]
            );
        }

        // special case of o2 wifi
        if ($request->getDeep('config[operator]') == 4 && $request->getDeep('config[flow]') == 'wifi') {
            $paymentUrl = $this->getLocalUrl(
                '/emulate/NetM/o2msisdn',
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
     * @param \Gemu\Core\Gateway\EndPoint\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function checkTan(EndPointRequest $request)
    {
        $this->mergeParams($request);
        $transaction_id = $request->getTransactionId();
        if ($request->get('Tan') == '1234') {
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
     * @param \Gemu\Core\Gateway\EndPoint\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function queryInfo(EndPointRequest $request)
    {
        $this->mergeParams($request);
        $now = date('Y-m-d H:i:s');

        if ($request->getDeep('config[flow]') == '3g'
            || $request->getDeep('config[operator]' == 4)) {
            $msisdn = $request->getDeep('config[msisdn]');
        } else {
            $msisdn = $request->getDeep('Destination');
        }

//        if (!isset($params['Description'])) {
//            $params['Description'] = '';
//            $params['SubscriptionFee'] = '';
//        }

        $s = <<<HERE
<?xml version="1.0" encoding="ISO-8859-1"?>
<Response type="QueryInfo">
  <StatusCode>0</StatusCode>
  <StatusText>OK</StatusText>
  <RequestID>{$request->getTransactionId()}</RequestID>
  <TransactionID>{$request->getTransactionId()}</TransactionID>
  <OperatorID>{$request->getDeep('config[operator]')}</OperatorID>
  <PaymentOperatorID>{$request->getDeep('config[operator]')}</PaymentOperatorID>
  <Description>{$request->get('Description')}</Description>
  <Destination>$msisdn</Destination>
  <TransactionStatusCode>1</TransactionStatusCode>
  <TransactionStatusText>Transmitted TAN to MS, waiting for TAN input</TransactionStatusText>
  <Subscriptions>
    <Number>1</Number>
    <Subscription>
      <SubscriptionID>{$request->getTransactionId()}</SubscriptionID>
      <SubscriptionStatusCode>1</SubscriptionStatusCode>
      <SubscriptionStatusText>Subscription ready</SubscriptionStatusText>
      <ServiceID>web_de_abo</ServiceID>
      <ServiceType>web</ServiceType>
      <SubscriptionFee>{$request->get('SubscriptionFee')}</SubscriptionFee>
      <ItemFee>0</ItemFee>
      <Currency>EURO-CENT</Currency>
      <VAT>19.0</VAT>
      <OperatorID>1</OperatorID>
      <PaymentOperatorID>1</PaymentOperatorID>
      <Description>{$request->get('Description')}</Description>
      <StartTimestamp>$now</StartTimestamp>
      <LastPeriodFeeTimestamp>$now</LastPeriodFeeTimestamp>
    </Subscription>
  </Subscriptions>
</Response>
HERE;
        return new Response($s);
    }

    /**
     * @param \Gemu\Core\Gateway\EndPoint\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function prepareInfo(EndPointRequest $request)
    {
        $this->mergeParams($request);
        $infoUrl = $this->getLocalUrl('/emulate/NetM/detectinfo');
        $s = <<<HERE
<?xml version="1.0" encoding="ISO-8859-1"?>
<Response type="PrepareInfo">
  <StatusCode>0</StatusCode>
  <StatusText>OK</StatusText>
  <RequestID>{$request->getTransactionId()}</RequestID>
  <TransactionID>{$request->getTransactionId()}</TransactionID>
  <InfoURL>$infoUrl</InfoURL>
</Response>
HERE;
        return new Response($s);
    }

    /**
     * @param \Gemu\Core\Gateway\EndPoint\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function paymentUrl(EndPointRequest $request)
    {
        $this->mergeParams($request);

        $url = $this->getLocalUrl(
            '/emulate/NetM/confirm',
            [ 'rid' => $request->getTransactionId() ]
        );

        if ($request->getDeep('config[operator]') > 2) {
            $fContent = file_get_contents('optin.html');
            $fContent = str_replace('$TITLE', 'Opt-in 2', $fContent);
            $fContent = str_replace('$BANNER', $request->get('PurchaseBanner'), $fContent);
            $fContent = str_replace('$IMAGE', $request->get('PurchaseImage'), $fContent);
            $fContent = str_replace('$URL', $url, $fContent);
            return new Response($fContent);
        }

        $contents = file_get_contents($request->get('ConfirmationURL'));
        $contents = str_replace('$PRODUCT_URL', $url, $contents);

        $this->cache->pushInfo('Going to Optin2 page.');

        return new Response($contents);
    }

    /**
     * @param \Gemu\Core\Gateway\EndPoint\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function detectInfo(EndPointRequest $request)
    {

        $this->mergeParams($request);
        if ($request->getDeep('config[flow]') == '3g') {
            $this->cache->pushInfo(
                sprintf(
                    'MSISDN detected as %s. Initiating  3G flow.',
                    $request->getDeep('config[msisdn]')
                )
            );
            $code = 0;
        } else {
            $this->cache->pushInfo('MSISDN not detected. Initiating Wifi flow.');
            $code = 151;
        }

        return new RedirectResponse(
            $request->get('CustomerURL').'?'.http_build_query(
                [
                    'trid' => $request->getTransactionId(),
                    'code' => $code,
                    'rid' => $request->getTransactionId()
                ]
            )
        );
    }

    /**
     * @param \Gemu\Core\Gateway\EndPoint\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function optIn(EndPointRequest $request)
    {
        $this->mergeParams($request);
        $url = $this->getLocalUrl(
            '/emulate/NetM/paymenturl',
            [ 'rid' => $request->getTransactionId() ]
        );

        $fContent = file_get_contents('optin.html');
        $fContent = str_replace('$TITLE', 'Opt-in 1', $fContent);
        $fContent = str_replace('$BANNER', $request->get('PurchaseBanner'), $fContent);
        $fContent = str_replace('$IMAGE', $request->get('PurchaseImage'), $fContent);
        $fContent = str_replace('$URL', $url, $fContent);
        $this->cache->pushInfo('Going to Optin1 page.');

        return new Response($fContent);
    }

    /**
     * @param \Gemu\Core\Gateway\EndPoint\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function o2msisdn(EndPointRequest $request)
    {
        $this->mergeParams($request);
        return new RedirectResponse(
            $this->makeUrl(
                $request->get('ProductURL'),
                [
                    'rid' => $request->getTransactionId(),
                    'code' => 0,
                    'sid' => $request->getTransactionId(),
                ]
            )
        );
    }

    /**
     * @param \Gemu\Core\Gateway\EndPoint\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function confirm(EndPointRequest $request)
    {
        $this->mergeParams($request);
        $subId = substr($request->getTransactionId(), 0, 48);
        $this->cache->pushInfo('Subscription successful. Subscription ID: ' . $subId);
        return new RedirectResponse(
            $this->makeUrl(
                $request->get('ProductURL'),
                [ 'rid' => $request->getTransactionId() ]
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
