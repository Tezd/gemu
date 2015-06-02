<?php

namespace Gemu\Gateway\Ipx\Soap\Subscription;

/**
 * Class CreateSubscriptionRequest
 * @package Gemu\Gateway\Ipx\Soap\Subscription
 */
class CreateSubscriptionRequest
{
    public $correlationId;
    public $consumerId;
    public $referenceId;
    public $tariffClass;
    public $serviceName;
    public $serviceCategory;
    public $serviceMetaData;
    public $eventCount;
    public $duration;
    public $frequencyInterval;
    public $frequencyCount;
    public $serviceId;
    public $initialCharge;
    public $billingMode;
    public $VAT;
    public $campaignName;
    public $username;
    public $password;
}
