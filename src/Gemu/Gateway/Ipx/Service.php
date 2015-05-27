<?php

namespace Gemu\Gateway\Ipx;

use Symfony\Component\HttpFoundation\JsonResponse;

class Service extends \Gemu\Core\Gateway\Response\Service
{
    protected function operators()
    {
        return new JsonResponse(
            array(
                'AIRTEL' => 'Vodafone',
                'WHATEVER' => 'Yoigo',
            )
        );
    }
}
