<?php

namespace Gemu\Core;

use Gemu\Core\Gateway\EndPoint\Service;
use Gemu\Core\Gateway\EndPoint\Emulator;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Gateway
 * @package Gemu\Core
 */
class Gateway
{
    /**
     * @type \Gemu\Core\Gateway\EndPoint\Emulator
     */
    protected $emulator;
    /**
     * @type \Gemu\Core\Gateway\EndPoint\Service
     */
    protected $service;

    /**
     * @param \Gemu\Core\Gateway\EndPoint\Emulator $emulator
     * @param \Gemu\Core\Gateway\EndPoint\Service $service
     */
    public function __construct(Emulator $emulator, Service $service)
    {
        $this->emulator = $emulator;
        $this->service = $service;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Gemu\Core\Error\BadEndPoint
     */
    public function emulate(Request $request)
    {
        return $this->emulator->emulate($request);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Gemu\Core\Error\BadEndPoint
     */
    public function service(Request $request)
    {
        return $this->service->handle($request);
    }
}
