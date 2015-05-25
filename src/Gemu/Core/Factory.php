<?php

namespace Gemu\Core;

use Gemu\Core\Cache;
use Gemu\Core\Gateway\Generic as Gateway;
use Gemu\Core\Error\NonExistingGatewayPart;
use Predis\Client;

/**
 * @todo add Redis config support
 * Class Factory
 * @package Gemu\Core
 */
class Factory
{
    const GATEWAY_NAMESPACE_PREFIX = '\\Gemu\\Gateway\\';
//    const GATEWAY_PART_REQUEST_PARSER = '\\Request\\Parser';
    const GATEWAY_PART_RESPONSE_EMULATOR = '\\Emulator';
    const GATEWAY_PART_RESPONSE_SERVICE = '\\Service';

    /**
     * @param $gatewayName
     *
     * @return \Gemu\Core\Gateway\Generic
     */
    public function getGateway($gatewayName)
    {
        $parts = $this->getGatewayParts($gatewayName);
        return new Gateway(
            new $parts['emulator']($this->getCache()),
            new $parts['service']()
        );
    }

    /**
     * @param string $gatewayName
     *
     * @return array
     */
    protected function getGatewayParts($gatewayName)
    {
        return @array_map(
            function($part) use ($gatewayName) {
                $partClass = Factory::GATEWAY_NAMESPACE_PREFIX.$gatewayName.$part;
                if(!class_exists($partClass)) {
                    throw new NonExistingGatewayPart($partClass);
                }
                return $partClass;
            },
            array(
                'emulator' => self::GATEWAY_PART_RESPONSE_EMULATOR,
                'service' => self::GATEWAY_PART_RESPONSE_SERVICE
            )
        );
    }

    /**
     * @return \Gemu\Core\Cache
     */
    public function getCache()
    {
        return new Cache(new Client());
    }
}
