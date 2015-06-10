<?php

namespace Gemu\Core\Error;

class BadSoapEndPoint extends \Exception
{
    /**
     * @param string $className
     * @param string $soapEndPointName
     * @param string $soapHandlerLookUpDirectory
     */
    public function __construct($className, $soapEndPointName, $soapHandlerLookUpDirectory)
    {
        parent::__construct(
            sprintf(
                'Class [%s] doesn\'t support SoapEndPoint [%s].'.PHP_EOL.
                'Maybe you forgot to create Soap handler class[%s]?',
                $className,
                $soapEndPointName,
                $soapHandlerLookUpDirectory
            )
        );
    }
}
