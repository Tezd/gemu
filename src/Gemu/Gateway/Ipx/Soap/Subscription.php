<?php

namespace Gemu\Gateway\Ipx\Soap;

use Gemu\Core\Gateway\EndPoint\Emulator;

/**
 * Class Subscription
 * @package Gemu\Gateway\Ipx\Soap
 */
final class Subscription extends Emulator
{
    /**
     * @param string $transactionId
     *
     * @return array
     */
    protected function createSubscription($transactionId)
    {
        $params = $this->cache->loadParams();
        return [
            'correlationId' => $transactionId,
            'subscriptionId' => $transactionId,
            'subscriptionStatus' => 1,
            'subscriptionStatusMessage' => '',
            'operator' => $params['config']['operator'],
            'operatorNetworkCode' => 'NTWRK',
            'VAT' => 6.99,
            'responseCode' => 0,
            'reasonCode' => 0,
            'responseMessage' => '',
        ];
    }

    /**
     * @param string $transactionId
     *
     * @return array
     */
    protected function getSubscriptionStatus($transactionId)
    {
        return [
            'correlationId' => $transactionId,
            'subscriptionStatus' => 1,
            'subscriptionStatusMessage' => 'The subscription is active and ready for use',
            'responseCode' => 0,
            'responseMessage' => '',
        ];
    }

    /**
     * @param string $transaction_id
     *
     * @return array
     */
    protected function authorizePayment($transaction_id)
    {
        return [
            'correlationId' => $transaction_id,
            'sessionId' => $transaction_id,
            'authorizationLevel' => 1,
            'responseCode' => 0,
            'reasonCode' => 0,
            'responseMessage' => '',
            'temporaryError' => false,
        ];
    }

    /**
     * @param string $transaction_id
     *
     * @return array
     */
    protected function capturePayment($transaction_id)
    {
        return [
            'correlationId' => $transaction_id,
            'transactionId' => $transaction_id,
            'responseCode' => 0,
            'reasonCode' => 0,
            'responseMessage' => '',
            'temporaryError' => false,
            'billingStatus' => 2,
        ];
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
        return $data['correlationId'];
    }

    /**
     * @param mixed $rawData
     *
     * @return array
     */
    protected function getData($rawData)
    {
        return (array)$rawData;
    }
}
