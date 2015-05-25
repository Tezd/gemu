<?php

namespace Gemu\Core\Gateway\Response;

use Gemu\Core\Error\BadEndPoint;
use Symfony\Component\HttpFoundation\Request;

abstract class Service
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return mixed
     * @throws \Gemu\Core\Error\BadEndPoint
     */
    public function handle(Request $request)
    {
        $endPoint = $request->attributes->get('endPoint');
        if(!method_exists($this, $endPoint)) {
            throw new BadEndPoint(get_called_class(), $endPoint);
        }
        return $this->$endPoint();
    }
}
