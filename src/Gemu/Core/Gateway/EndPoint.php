<?php

namespace Gemu\Core\Gateway;

use Gemu\Core\Error\BadEndPoint;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class EndPoint
 * @package Gemu\Core\Gateway
 */
abstract class EndPoint
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Gemu\Core\Error\BadEndPoint
     */
    public function handle(Request $request)
    {
        if ($endPoint = $this->getEndPoint($request)) {
            return $this->__call($endPoint, array($request));
        }
        throw new BadEndPoint(static::class, $endPoint);
    }

    /**
     * @param string $name
     * @param array $arguments
     */
    abstract public function __call($name, array $arguments);

    /**
     * Get method which we need to invoke inside emulator based on request
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return null|string
     */
    protected function getEndPoint(Request $request)
    {
        return $request->attributes->get('endPoint');
    }
}
