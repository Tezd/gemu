<?php

namespace Gemu\Core\Gateway\Response;

use Gemu\Core\Cache;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Emulator
 * @package Gemu\Gateway\Response
 */
abstract class Emulator extends EndPoint
{
    /**
     * @type \Gemu\Core\Cache
     */
    protected $cache;

    /**
     * @param \Gemu\Core\Cache $cache
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

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
        return $this->prepare($request)->invokeEndPoint();
    }
}
