<?php

namespace Gemu\Core\Gateway\EndPoint;

use Gemu\Core\Gateway\EndPoint;
use Gemu\Core\Gateway\EndPoint\Emulator\Handler;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Emulator
 * @package Gemu\Core\Gateway\EndPoint\Emulator
 */
abstract class Emulator extends EndPoint
{
    use Handler;

    /**
     * @todo Refactor this
     *
     * @param string $url
     * @param array $params
     *
     * @return string
     */
    protected function makeUrl($url, array $params = array())
    {
        return $url. (empty($params) ? '' : '?'.http_build_query($params));
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Gemu\Core\Error\BadEndPoint
     */
    public function emulate(Request $request)
    {
        return $this->invokeEndPoint($request);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $rawData
     *
     * @return array
     */
    protected function getData($rawData)
    {
        return array_merge(
            $rawData->query->all(),
            $rawData->request->all()
        );
    }
}
