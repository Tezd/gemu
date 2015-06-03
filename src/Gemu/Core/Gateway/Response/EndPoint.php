<?php

namespace Gemu\Core\Gateway\Response;

use Gemu\Core\Error\BadEndPoint;
use Symfony\Component\HttpFoundation\Request;

/**
 * @todo make possible to use it for soap
 * Class EndPoint
 * @package Gemu\Core\Gateway\Response
 */
abstract class EndPoint
{
    /**
     * @type \Symfony\Component\HttpFoundation\Request $request
     */
    protected $request;

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return $this
     */
    protected function prepare(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Gemu\Core\Error\BadEndPoint
     */
    protected function invokeEndPoint()
    {
        if (($endPoint = $this->getEndPoint($this->request))
            && method_exists($this, $endPoint)
        ) {
            return $this->$endPoint();
        }
        throw new BadEndPoint(get_called_class(), $endPoint);
    }

    /**
     * Get method which we need to invoke inside emulator based on request
     * @return null|string
     */
    protected function getEndPoint()
    {
        return $this->request->attributes->get('endPoint');
    }
}
