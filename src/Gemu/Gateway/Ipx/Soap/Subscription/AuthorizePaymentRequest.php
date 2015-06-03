<?php

namespace Gemu\Gateway\Ipx\Soap\Subscription;

/**
 * Class AuthorizePaymentRequest
 * @package Gemu\Gateway\Ipx\Soap\Subscription
 */
class AuthorizePaymentRequest
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
    public $serviceMetaData;
    /**
     * @type string
     */
    public $username;
    /**
     * @type string
     */
    public $password;
}
