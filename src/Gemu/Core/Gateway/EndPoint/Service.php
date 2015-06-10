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
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Gemu\Core\Error\BadEndPoint
     */
    public function handle(Request $request)
    {
        return $this->invokeEndPoint($request);
    }

    /**
     * @param string $name
     * @param array $arguments
     */
    public function __call($name, array $arguments)
    {
        return $this->$name($arguments[0]);
    }
}
