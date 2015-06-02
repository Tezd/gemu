<?php

namespace Gemu\Gateway\NetM;

use Gemu\Core\Gateway\Response\Emulator as BaseEmulator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @todo create proper xml
 * @todo make functions private
 * Class Emulator
 * @package Gemu\Gateway\NetM
 */
class Emulator extends BaseEmulator
{
    /**
     * @return null|string
     */
    protected function getEndPoint()
    {
        return $this->request->get('RequestType') ?
            $this->request->get('RequestType') :
            parent::getEndPoint();
    }

    /**
     * @param $endPoint
     *
     * @return string
     */
    protected function getLocalUrl($endPoint)
    {
        return 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'].$endPoint;
    }

    /**
     * Initializes parameters for use inside of other emulator invoked methods
     * @param string $transactionKey
     *
     * @return array
     */
    protected function initParams($transactionKey)
    {
        $params = array();
        $params['rid'] = $transactionKey;
        $params = array_merge(
            $params,
            $this->cache->loadParams($transactionKey),
            $this->request->query->all(),
            $this->request->request->all()
        );
        return $params;
    }

    /**
     * Calls initialize and persist params inside of cache for later use
     * @param $transactionKey
     *
     * @return array
     */
    protected function getParams($transactionKey)
    {
        $params = $this->initParams($transactionKey);
        $this->cache->updateParams($transactionKey, $params);
        return $params;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function PrepareSubscription()
    {
        $params = $this->getParams($this->request->get('RequestID'));
        $paymentUrl = $this->makeUrl($this->getLocalUrl('/emulate/NetM/paymenturl'), array('rid' => $params['rid']));

        if (!empty($params['config']['low_balance'])) {
            $statusCode = 305;
            $statusText = 'Low credit';
        } else {
            $statusCode = 0;
            $statusText = 'OK';
        }

        if ($params['config']['operator'] > 2) {
            $this->cache->pushLog($params['rid'], 'Operator hosts optin1 and optin2 pages.');
            $paymentUrl = $this->makeUrl($this->getLocalUrl('/emulate/NetM/optin'), array('rid' => $params['rid']));
        }

        // special case of o2 wifi
        if ($params['config']['operator'] == 4 && $params['config']['flow'] == 'wifi') {
            $paymentUrl = $this->makeUrl($this->getLocalUrl('/emulate/NetM/o2msisdn'), array('rid' => $params['rid']));
        }

        $s = <<<HERE
<?xml version="1.0" encoding="ISO-8859-1"?>
<Response type="PrepareSubscription">
  <StatusCode>$statusCode</StatusCode>
  <StatusText>$statusText</StatusText>
  <TransactionType>synchronous</TransactionType>
  <RequestID>{$params['rid']}</RequestID>
  <TransactionID>{$params['rid']}</TransactionID>
  <ValidityPeriod>216000</ValidityPeriod>
  <PaymentURL>$paymentUrl</PaymentURL>
</Response>
HERE;

        return new Response($s);
    }


    protected function CheckTan()
    {
        $params = $this->getParams($this->request->get('RequestID'));

        if ($params['Tan'] == '1234') {
            $statusCode = 0;
            $statusText = 'PIN correct';
            $this->cache->pushLog($params['rid'], 'Pin verified');
            $subId = substr($params['rid'], 0, 48);
            $this->cache->pushLog($params['rid'], 'Subscription successful. Subscription ID: ' . $subId);
        } else {
            $statusCode = 250;
            $statusText = 'PIN wrong';
            $this->cache->pushLog($params['rid'], 'Invalid pin');
        }

        $s = <<<HERE
<?xml version="1.0" encoding="ISO-8859-1"?>
<Response type="CheckTan">
  <StatusCode>$statusCode</StatusCode>
  <StatusText>$statusText</StatusText>
  <TransactionType>synchronous</TransactionType>
  <RequestID>{$params['rid']}</RequestID>
  <TransactionID>{$params['rid']}</TransactionID>
</Response>
HERE;

        return new Response($s);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function QueryInfo()
    {
        $now = date('Y-m-d H:i:s');

        $params = $this->getParams($this->request->get('RequestID'));

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
  <RequestID>{$params['rid']}</RequestID>
  <TransactionID>{$params['rid']}</TransactionID>
  <OperatorID>{$params['config']['operator']}</OperatorID>
  <PaymentOperatorID>{$params['config']['operator']}</PaymentOperatorID>
  <Description>{$params['Description']}</Description>
  <Destination>${msisdn}</Destination>
  <TransactionStatusCode>1</TransactionStatusCode>
  <TransactionStatusText>Transmitted TAN to MS, waiting for TAN input</TransactionStatusText>
  <Subscriptions>
    <Number>1</Number>
    <Subscription>
      <SubscriptionID>{$params['rid']}</SubscriptionID>
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

    protected function prepareInfo()
    {
        $params = $this->getParams($this->request->get('RequestID'));
        $infoUrl = $this->makeUrl($this->getLocalUrl('/emulate/NetM/detectinfo'));
        $s = <<<HERE
<?xml version="1.0" encoding="ISO-8859-1"?>
<Response type="PrepareInfo">
  <StatusCode>0</StatusCode>
  <StatusText>OK</StatusText>
  <RequestID>{$params['rid']}</RequestID>
  <TransactionID>{$params['rid']}</TransactionID>
  <InfoURL>${infoUrl}</InfoURL>
</Response>
HERE;
        return new Response($s);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function paymentUrl()
    {
        $params = $this->getParams($this->request->get('rid'));

        $url = $this->makeUrl($this->getLocalUrl('/emulate/NetM/confirm'), array('rid' => $params['rid']));

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

        $this->cache->pushLog($params['rid'], 'Going to Optin2 page.');

        return new Response($contents);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function detectInfo()
    {
        $params = $this->getParams($this->request->get('rid'));

        if ($params['config']['flow'] == '3g') {
            $this->cache->pushLog(
                $params['rid'],
                sprintf(
                    'MSISDN detected as %s. Initiating  3G flow.',
                    $params['config']['msisdn']
                )
            );
            $code = 0;
        } else {
            $this->cache->pushLog(
                $params['rid'],
                'MSISDN not detected. Initiating Wifi flow.'
            );
            $code = 151;
        }
        return new RedirectResponse(
            $params['CustomerURL'].'?'.http_build_query(
                array(
                    'trid' => $params['TransactionID'],
                    'code' => $code,
                    'rid' => $params['rid'],
                )
            )
        );
    }

    protected function optIn()
    {
        $params = $this->getParams($this->request->get('rid'));

        $url = $this->makeUrl($this->getLocalUrl('/emulate/NetM/paymenturl'), array('rid' => $params['rid']));

        $fContent = file_get_contents('optin.html');
        $fContent = str_replace('$TITLE', 'Opt-in 1', $fContent);
        $fContent = str_replace('$BANNER', $params['PurchaseBanner'], $fContent);
        $fContent = str_replace('$IMAGE', $params['PurchaseImage'], $fContent);
        $fContent = str_replace('$URL', $url, $fContent);

        $this->cache->pushLog($params['rid'], 'Going to Optin1 page.');

        return new Response($fContent);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function o2msisdn()
    {
        $params = $this->getParams($this->request->get('rid'));
        return new RedirectResponse(
            $this->makeUrl(
                $params['ProductURL'],
                array(
                    'rid' => $params['rid'],
                    'code' => 0,
                    'sid' => $params['rid'],
                )
            )
        );
    }

    protected function confirm()
    {
        $params = $this->getParams($this->request->get('rid'));
        $subId = substr($params['rid'], 0, 48);
        $this->cache->pushLog($params['rid'], 'Subscription successful. Subscription ID: ' . $subId);
        return new RedirectResponse(
            $this->makeUrl(
                $params['ProductURL'],
                array(
                    'rid' => $params['rid']
                )
            )
        );
    }
}
