<?php

namespace Gemu\Core;

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
     * @return \Gemu\Core\Gateway
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
            function ($part) use ($gatewayName) {
                $partClass = '\\Gemu\\Gateway\\'.$gatewayName.$part;
                if (!class_exists($partClass)) {
                    throw new NonExistingGatewayPart($partClass);
                }
                return $partClass;
            },
            [
                'emulator' => '\\Emulator',
                'service' => '\\Service'
            ]
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
