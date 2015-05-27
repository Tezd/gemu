<?php

namespace Gemu\Core\Gateway\Response;

use Gemu\Core\Error\BadEndPoint;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Service
 * @package Gemu\Core\Gateway\Response
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
        return $this->prepare($request)->invokeEndPoint();
    }
}
