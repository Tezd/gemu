<?php

namespace Gemu\Gateway\Ipx\Soap;

use Gemu\Core\Cache;
use Gemu\Gateway\Ipx\Soap\Subscription\CreateSubscriptionRequest;

class Subscription
{
    protected $cache;

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }
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
}

