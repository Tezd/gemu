<?php

namespace Gemu\Gateway\Ipx\Soap;

use Gemu\Core\Gateway\EndPoint\Emulator\Handler;

/**
 * Class Subscription
 * @package Gemu\Gateway\Ipx\Soap
 */
class Subscription
{
    use Handler;

    /**
     * @param array $request
     *
     * @return array
     */
    protected function createSubscription(array $request)
    {
        $params = $this->loadParams();
        return array(
            'correlationId' => $request['correlationId'],
            'subscriptionId' => $request['correlationId'],
            'subscriptionStatus' => 1,
            'subscriptionStatusMessage' => '',
            'operator' => $params['config']['operator'],
            'operatorNetworkCode' => 'NTWRK',
            'VAT' => 6.99,
            'responseCode' => 0,
            'reasonCode' => 0,
            'responseMessage' => ''
        );
    }

    /**
     * @param array $request
     *
     * @return array
     */
    protected function getSubscriptionStatus(array $request)
    {
        return array(
            'correlationId' => $request['correlationId'],
            'subscriptionStatus' => 1,
            'subscriptionStatusMessage' => 'The subscription is active and ready for use',
            'responseCode' => 0,
            'responseMessage' => ''
        );
    }

    /**
     * @param array $request
     *
     * @return array
     */
    protected function authorizePayment(array $request)
    {
        return array(
            'correlationId' => $request['correlationId'],
            'sessionId' => $request['correlationId'],
            'authorizationLevel' => 1,
            'responseCode' => 0,
            'reasonCode' => 0,
            'responseMessage' => '',
            'temporaryError' => false,
        );
    }

    /**
     * @param array $request
     *
     * @return array
     */
    protected function capturePayment(array $request)
    {
        return array(
            'correlationId' => $request['correlationId'],
            'transactionId' => $request['correlationId'],
            'responseCode' => 0,
            'reasonCode' => 0,
            'responseMessage' => '',
            'temporaryError' => false,
            'billingStatus' => 2,
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
