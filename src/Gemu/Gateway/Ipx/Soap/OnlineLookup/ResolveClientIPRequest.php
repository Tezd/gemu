<?php

namespace Gemu\Gateway\Ipx\Soap\OnlineLookup;

/**
 * Class ResolveClientIPRequest
 * @package Gemu\Gateway\Ipx\Soap\OnlineLookup
 */
class ResolveClientIPRequest
{
    public $correlationId;
    public $clientIPAddress;
    public $campaignName;
    public $username;
    public $password;
}
