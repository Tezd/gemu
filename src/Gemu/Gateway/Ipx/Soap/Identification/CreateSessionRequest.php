<?php

namespace Gemu\Gateway\Ipx\Soap\Identification;

/**
 * Class CreateSessionRequest
 * @package Gemu\Gateway\Ipx\Soap\Identification
 */
class CreateSessionRequest
{
    public $correlationId;
    public $clientIPAddress;
    public $returnURL;
    public $serviceName;
    public $serviceCategory;
    public $serviceMetaData;
    public $language;
    public $campaignName;
    public $username;
    public $password;
}
