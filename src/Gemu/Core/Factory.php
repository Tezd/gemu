<?php

namespace Gemu\Core;

use Gemu\Core\Cache\Proxy;
use Gemu\Core\Error\NonExistingGatewayPart;
use Predis\Client;

/**
 * @todo add Redis config support
 * Class Factory
 * @package Gemu\Core
 */
class Factory
{
    /**
     * @param string $gatewayName
     *
     * @return \Gemu\Core\Gateway\EndPoint
     * @throws \Gemu\Core\Error\NonExistingGatewayPart
     */
    public function getEmulator($gatewayName)
    {
        return $this->createGatewayEndPoint($gatewayName, '\\Emulator', [ new Proxy($this->getCache()) ]);
    }

    /**
     * @param string $gatewayName
     *
     * @return \Gemu\Core\Gateway\EndPoint
     * @throws \Gemu\Core\Error\NonExistingGatewayPart
     */
    public function getService($gatewayName)
    {
        return $this->createGatewayEndPoint($gatewayName, '\\Service');
    }

    /**
     * @param string $gatewayName
     * @param string $endPointPart
     * @param array $params
     *
     * @return \Gemu\Core\Gateway\EndPoint
     * @throws \Gemu\Core\Error\NonExistingGatewayPart
     */
    protected function createGatewayEndPoint($gatewayName, $endPointPart, array $params = [])
    {
        $gatewayPartClass = '\\Gemu\\Gateway\\'.$gatewayName.$endPointPart;
        if (!class_exists($gatewayPartClass)) {
            throw new NonExistingGatewayPart($gatewayPartClass);
        }
        return $params ?
            call_user_func_array(array($gatewayPartClass, 'getInstance'), $params) :
            new $gatewayPartClass;
    }

    /**
     * @return \Gemu\Core\Cache
     */
    public function getCache()
    {
        return new Cache(
            new Client('tcp://redis.local:6379')
        );
    }
}
