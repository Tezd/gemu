<?php

namespace Gemu\Gateway\Ipx;

use Gemu\Core\Gateway\EndPoint\Service as BaseService;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class Service
 * @package Gemu\Gateway\Ipx
 */
class Service extends BaseService
{
    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    protected function operators()
    {
        return new JsonResponse(
            [
                'AIRTEL' => 'Vodafone',
                'WHATEVER' => 'Yoigo',
            ]
        );
    }
}
