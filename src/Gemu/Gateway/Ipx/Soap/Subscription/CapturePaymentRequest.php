<?php

namespace Gemu\Gateway\Ipx\Soap\Subscription;

/**
 * Class CapturePaymentRequest
 * @package Gemu\Gateway\Ipx\Soap\Subscription
 */
class CapturePaymentRequest
{
    /**
     * @type string
     */
    public $correlationId;
    /**
     * @type string
     */
    public $sessionId;
    /**
     * @type string
     */
    public $username;
    /**
     * @type string
     */
    public $password;
}
