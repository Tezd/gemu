<?php

namespace Gemu\Core\Gateway\EndPoint;

use Gemu\Core\Gateway\EndPoint;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Service
 * @package Gemu\Core\Gateway\EndPoint
 */
abstract class Service extends EndPoint
{
    /**
     * @param string $name
     * @param array $arguments
     */
    public function __call($name, array $arguments)
    {
        return $this->$name($arguments[0]);
    }
}
