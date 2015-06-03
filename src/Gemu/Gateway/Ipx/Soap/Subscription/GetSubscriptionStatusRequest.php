<?php

namespace Gemu\Gateway\Ipx\Soap\Subscription;

/**
 * Class GetSubscriptionStatusRequest
 * @package Gemu\Gateway\Ipx\Soap\Subscription
 */
class GetSubscriptionStatusRequest
{
    /**
     * @type string
     */
    public $correlationId;
    /**
     * @type string
     */
    public $consumerId;
    /**
     * @type string
     */
    public $subscriptionId;
    /**
     * @type string
     */
    public $username;
    /**
     * @type string
     */
    public $password;
}
