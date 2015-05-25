<?php

namespace Gemu\Core\Error;

class NonExistingGatewayPart extends \Exception
{
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
