<?php

namespace Gemu\Gateway\Ipx;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class Service
 * @package Gemu\Gateway\Ipx
 */
class Service extends \Gemu\Core\Gateway\Response\Service
{
    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
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
