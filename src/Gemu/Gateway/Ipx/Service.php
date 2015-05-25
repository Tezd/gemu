<?php

namespace Gemu\Gateway\Ipx;

use Symfony\Component\HttpFoundation\JsonResponse;

class Service extends \Gemu\Core\Gateway\Response\Service
{
    protected function operators()
    {
        return new JsonResponse(
            array(
                1 => 'Derp',
                2 => 'Vodafone',
                3 => 'E-Plus',
                4 => 'O2'
            )
        );
    }
}
