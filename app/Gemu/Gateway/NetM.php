<?php

namespace Gemu\Gateway;

use Gemu\Core\Gateway;

/**
 * Class NetM
 * @package Gemu\Gateway
 */
class NetM extends Gateway
{
    /**
     * @return mixed
     * @throws \Gemu\Core\Error\BadEndPoint
     */
    protected function PaymentGateway()
    {
        return $this->invokeEndpoint($this->request->get('RequestType'));
    }

    private function _initParams()
    {

    }

    private function CheckTan()
    {

    }

    private function PrepareInfo()
    {

    }
    private function PrepareSubscription()
    {

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
  <RequestID>{$this->params['RequestID']}</RequestID>
  <TransactionID>{$this->params['RequestID']}</TransactionID>
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

        return $s;
    }
}
