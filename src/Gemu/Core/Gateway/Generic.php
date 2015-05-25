<?php

namespace Gemu\Core\Gateway;

use Gemu\Core\Gateway\Response\Service;
use Gemu\Core\Gateway\Response\Emulator;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Controller
 * @package Gemu\Core\MVC
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
     * @return mixed
     * @throws \Gemu\Core\Error\BadEndPoint
     */
    public function emulate(Request $request)
    {
        return $this->emulator->emulate($request);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return mixed
     */
    public function service(Request $request)
    {
        return $this->service->handle($request);
    }

//    /**
//     * @param \Symfony\Component\HttpFoundation\Request $request
//     *
//     * @return mixed
//     */
//    protected function getEndPoint(Request $request)
//    {
//        return $request->attributes->get('endPoint');
//    }

//    /**
//     * @param \Symfony\Component\HttpFoundation\Request $request
//     */
//    protected function initParams(Request $request)
//    {
//        $rid = $request->query->get('RequestID');
//        $this->params = array_merge(
//            $this->cache->loadParams($rid),
//            $request->query->all(),
//            $request->request->all()
//        );
//        $this->params['config'] = json_decode(
//            base64_decode(
//                $rid,
//                true
//            ),
//            true
//        );
//    }
}
