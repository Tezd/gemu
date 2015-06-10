<?php

namespace Gemu\Gateway\NetM;

use Gemu\Core\Gateway\EndPoint\Service as BaseService;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class Service
 * @package Gemu\Gateway\NetM
 */
class Service extends BaseService
{
    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    protected function operators()
    {
        return new JsonResponse(
            array(
                1 => 'T-Mobile',
                2 => 'Vodafone',
                3 => 'E-Plus',
                4 => 'O2'
            )
        );
    }
}
