<?php

namespace Gemu\Gateway\Ipx\Soap;

use Gemu\Core\Cache;
use Gemu\Gateway\Ipx\Soap\Subscription\AuthorizePaymentRequest;
use Gemu\Gateway\Ipx\Soap\Subscription\CapturePaymentRequest;
use Gemu\Gateway\Ipx\Soap\Subscription\CreateSubscriptionRequest;
use Gemu\Gateway\Ipx\Soap\Subscription\GetSubscriptionStatusRequest;

/**
 * Class Subscription
 * @package Gemu\Gateway\Ipx\Soap
 */
class Subscription
{
    /**
     * @type \Gemu\Core\Cache
     */
    protected $cache;

    /**
     * @param \Gemu\Core\Cache $cache
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param \Gemu\Gateway\Ipx\Soap\Subscription\CreateSubscriptionRequest $request
     *
     * @return array
     */
    public function createSubscription(CreateSubscriptionRequest $request)
    {
        $this->cache->pushLog($request->correlationId, 'CreateSubscription');
        $params = $this->cache->loadParams($request->correlationId);
        return array(
            'correlationId' => $request->correlationId,
            'subscriptionId' => $request->correlationId,
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
     * @param \Gemu\Gateway\Ipx\Soap\Subscription\GetSubscriptionStatusRequest $request
     *
     * @return array
     */
    public function getSubscriptionStatus(GetSubscriptionStatusRequest $request)
    {
        $this->cache->pushLog($request->correlationId, 'getSubscriptionStatus');
        return array(
            'correlationId' => $request->correlationId,
            'subscriptionStatus' => 1,
            'subscriptionStatusMessage' => 'The subscription is active and ready for use',
            'responseCode' => 0,
            'responseMessage' => ''
        );
    }


    /**
     * @param \Gemu\Gateway\Ipx\Soap\Subscription\AuthorizePaymentRequest $request
     *
     * @return array
     */
    public function authorizePayment(AuthorizePaymentRequest $request)
    {
        $this->cache->pushLog($request->correlationId, 'authorizePayment');
        return array(
            'correlationId' => $request->correlationId,
            'sessionId' => $request->correlationId,
            'authorizationLevel' => 1,
            'responseCode' => 0,
            'reasonCode' => 0,
            'responseMessage' => '',
            'temporaryError' => false,
        );
    }

    /**
     * @param \Gemu\Gateway\Ipx\Soap\Subscription\CapturePaymentRequest $request
     *
     * @return array
     */
    public function capturePayment(CapturePaymentRequest $request)
    {
        $this->cache->pushLog($request->correlationId, 'capturePayment');
        return array(
            'correlationId' => $request->correlationId,
            'transactionId' => $request->correlationId,
            'responseCode' => 0,
            'reasonCode' => 0,
            'responseMessage' => '',
            'temporaryError' => false,
            'billingStatus' => 2,
        );
    }
}
