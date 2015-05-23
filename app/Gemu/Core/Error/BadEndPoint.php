<?php

namespace Gemu\Core\Error;

/**
 * @todo make them be like real errors
 * Class BadEndPoint
 * @package Gemu\Core\Error
 */
class BadEndPoint extends \Exception
{
    /**
     * @param string $className
     * @param int $endPointName
     */
    public function __construct($className, $endPointName)
    {
        parent::__construct(
            sprintf(
                'Class [%s] doesn\'t support endPoint [%s].'.PHP_EOL.
                'Maybe you forgot to add method to that class?',
                $className,
                $endPointName
            )
        );
    }
}
