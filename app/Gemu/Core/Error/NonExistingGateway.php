<?php

namespace Gemu\Core\Error;

class NonExistingGateway extends \Exception
{
    public function __construct($gatewayName, $gatewayPrefix)
    {
        parent::__construct(
            sprintf(
                'Gateway [%s] doesn\'t exist.'.PHP_EOL.
                'Maybe you forgot to add it to %s ?',
                $gatewayName,
                $gatewayPrefix
            )
        );
    }
}
