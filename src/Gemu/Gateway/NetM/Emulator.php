<?php

namespace Gemu\Gateway\NetM;

use Gemu\Core\Gateway\Response\Emulator as BaseEmulator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @todo create proper xml
 * Class Emulator
 * @package Gemu\Gateway\NetM
 */
class Emulator extends BaseEmulator
{
    /**
     * Get method which we need to invoke inside emulator based on request
     * @return string
     */
    protected function getEndPoint(Request $request)
    {
        return ($request->get('RequestType') ? $request->get('RequestType') : $request->attributes->get('endPoint'));
    }

    /**
     * Initializes parameters for use inside of other emulator invoked methods
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return void
     */
    protected function initParams(Request $request)
    {
        $rid = $request->get('RequestID');
        $this->params = array_merge(
            $this->cache->loadParams($rid),
            $request->query->all(),
            $request->request->all()
        );
        $this->params['config'] = json_decode(
            base64_decode(
                $rid,
                true
            ),
            true
        );
    }

    private function QueryInfo()
    {
        $now = date('Y-m-d H:i:s');

        if ($this->params['config']['flow'] == '3g' || $this->params['config']['operator'] == 4) {
            $msisdn = $this->params['config']['msisdn'];
        } else {
            $msisdn = $this->params['Destination'];
        }

        if (!isset($this->params['Description'])) {
            $this->params['Description'] = '';
            $this->params['SubscriptionFee'] = '';
        }

        $s = <<<HERE
<?xml version="1.0" encoding="ISO-8859-1"?>
<Response type="QueryInfo">
  <StatusCode>0</StatusCode>
  <StatusText>OK</StatusText>
  <RequestID>{$this->params['rid']}</RequestID>
  <TransactionID>{$this->params['rid']}</TransactionID>
  <OperatorID>{$this->params['config']['operator']}</OperatorID>
  <PaymentOperatorID>{$this->params['config']['operator']}</PaymentOperatorID>
  <Description>{$this->params['Description']}</Description>
  <Destination>${msisdn}</Destination>
  <TransactionStatusCode>1</TransactionStatusCode>
  <TransactionStatusText>Transmitted TAN to MS, waiting for TAN input</TransactionStatusText>
  <Subscriptions>
    <Number>1</Number>
    <Subscription>
      <SubscriptionID>{$this->params['RequestID']}</SubscriptionID>
      <SubscriptionStatusCode>1</SubscriptionStatusCode>
      <SubscriptionStatusText>Subscription ready</SubscriptionStatusText>
      <ServiceID>web_de_abo</ServiceID>
      <ServiceType>web</ServiceType>
      <SubscriptionFee>{$this->params['SubscriptionFee']}</SubscriptionFee>
      <ItemFee>0</ItemFee>
      <Currency>EURO-CENT</Currency>
      <VAT>19.0</VAT>
      <OperatorID>1</OperatorID>
      <PaymentOperatorID>1</PaymentOperatorID>
      <Description>{$this->params['Description']}</Description>
      <StartTimestamp>${now}</StartTimestamp>
      <LastPeriodFeeTimestamp>${now}</LastPeriodFeeTimestamp>
    </Subscription>
  </Subscriptions>
</Response>
HERE;

        return new Response($s);
    }

    public function prepareInfo()
    {
        var_dump($this->params); exit();
        $infoUrl = $this->makeUrl('/emulate/NetM/detectinfo');
        $s = <<<HERE
<?xml version="1.0" encoding="ISO-8859-1"?>
<Response type="PrepareInfo">
  <StatusCode>0</StatusCode>
  <StatusText>OK</StatusText>
  <RequestID>{$this->params['rid']}</RequestID>
  <TransactionID>{$this->params['rid']}</TransactionID>
  <InfoURL>${infoUrl}</InfoURL>
</Response>
HERE;
        return new Response($s);
    }

    protected function detectInfo()
    {
        if ($this->params['config']['flow'] == '3g') {
            $this->cache->pushLog($this->params['rid'], sprintf("MSISDN detected as %s. Initiating  3G flow.", $this->params['config']['msisdn']));
            $code = '0';
        } else {
            $this->cache->pushLog($this->params['rid'], 'MSISDN not detected. Initiating Wifi flow.');
            $code = '151';
        }
        return new RedirectResponse(
            $this->params['CustomerURL'].'?'.http_build_query(
                array(
                    'trid' => $this->params['TransactionID'],
                    'code' => $code,
                    'rid' => $this->params['RequestID'],
                )
            )
        );
    }

}
