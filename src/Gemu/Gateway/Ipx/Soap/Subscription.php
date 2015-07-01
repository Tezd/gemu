<?php

namespace Gemu\Gateway\Ipx\Soap;

use Gemu\Core\Gateway\EndPoint\Emulator;
use Gemu\Core\Gateway\EndPoint\Request;

/**
 * Class Subscription
 * @package Gemu\Gateway\Ipx\Soap
 */
final class Subscription extends Emulator
{
    /**
     * @param \Gemu\Core\Gateway\EndPoint\Request $request
     *
     * @return array
     */
    protected function createSubscription(Request $request)
    {
        $params = $this->cache->loadParams();
        return [
            'correlationId' => $request->getTransactionId(),
            'subscriptionId' => $request->getTransactionId(),
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
     * @param \Gemu\Core\Gateway\EndPoint\Request $request
     *
     * @return array
     */
    protected function getSubscriptionStatus(Request $request)
    {
        return [
            'correlationId' => $request->getTransactionId(),
            'subscriptionStatus' => 1,
            'subscriptionStatusMessage' => 'The subscription is active and ready for use',
            'responseCode' => 0,
            'responseMessage' => '',
        ];
    }

    /**
     * @param \Gemu\Core\Gateway\EndPoint\Request $request
     *
     * @return array
     */
    protected function authorizePayment(Request $request)
    {
        return [
            'correlationId' => $request->getTransactionId(),
            'sessionId' => $request->getTransactionId(),
            'authorizationLevel' => 1,
            'responseCode' => 0,
            'reasonCode' => 0,
            'responseMessage' => '',
            'temporaryError' => false,
        ];
    }

    /**
     * @param \Gemu\Core\Gateway\EndPoint\Request $request
     *
     * @return array
     */
    protected function capturePayment(Request $request)
    {
        return [
            'correlationId' => $request->getTransactionId(),
            'transactionId' => $request->getTransactionId(),
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
