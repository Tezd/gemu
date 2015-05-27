<?php

namespace Gemu\Core\Gateway;

use Gemu\Core\Gateway\Response\Service;
use Gemu\Core\Gateway\Response\Emulator;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Generic
 * @package Gemu\Core\Gateway
 */
class Generic
{
    /**
     * @type \Gemu\Core\Gateway\Response\Emulator
     */
    protected $emulator;
    /**
     * @type \Gemu\Core\Gateway\Response\Service
     */
    protected $service;

    /**
     * @param \Gemu\Core\Gateway\Response\Emulator $emulator
     * @param \Gemu\Core\Gateway\Response\Service $service
     */
    public function __construct(Emulator $emulator,Service $service)
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
