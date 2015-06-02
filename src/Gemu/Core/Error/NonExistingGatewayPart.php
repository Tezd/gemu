<?php

namespace Gemu\Core\Error;

/**
 * Class NonExistingGatewayPart
 * @package Gemu\Core\Error
 */
class NonExistingGatewayPart extends \Exception
{
    /**
     * @param string $gatewayPartName
     */
    public function __construct($gatewayPartName)
    {
        parent::__construct(
            sprintf(
                'Gateway part [%s] doesn\'t exist.'.PHP_EOL.
                'Maybe you forgot to add it to?',
                $gatewayPartName
            )
        );
    }
}
