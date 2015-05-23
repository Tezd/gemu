<?php

namespace Gemu\Core;

use Gemu\Core\Cache;
use Gemu\Core\Error\NonExistingGateway;
use Predis\Client;

/**
 * @todo add Redis config support
 * Class Factory
 * @package Gemu\Core
 */
class Factory
{
    const GATEWAY_NAMESPACE_PREFIX = '\\Gemu\\Gateway\\';

    /**
     * @param $gatewayName
     *
     * @return \Gemu\Core\Gateway
     * @throws \Gemu\Core\Error\NonExistingGateway
     */
    public function getGateway($gatewayName)
    {
        $gatewayClass = self::GATEWAY_NAMESPACE_PREFIX.$gatewayName;
        if(!class_exists($gatewayClass)){
            throw new NonExistingGateway($gatewayName, self::GATEWAY_NAMESPACE_PREFIX);
        }
        return new $gatewayClass($this->getCache());
    }

    /**
     * @return \Gemu\Core\Cache
     */
    public function getCache()
    {
        return new Cache(new Client());
    }
}
