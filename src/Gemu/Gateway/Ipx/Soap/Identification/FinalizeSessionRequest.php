<?php

namespace Gemu\Gateway\Ipx\Soap\Identification;

/**
 * Class FinalizeSessionRequest
 * @package Gemu\Gateway\Ipx\Soap\Identification
 */
class FinalizeSessionRequest
{
    public $correlationId;
    public $sessionId;
    public $username;
    public $password;
}
