<?php

namespace Gemu\Core\Gateway\Response;

use Gemu\Core\Cache;
use Gemu\Core\Error\BadEndPoint;
use Symfony\Component\HttpFoundation\Request;

/**
 * @todo create base class called EndPoint
 * Class Emulator
 * @package Gemu\Gateway\Response
 */
abstract class Emulator
{
    /**
     * @type \Gemu\Core\Cache
     */
    protected $cache;

    /**
     * @type array
     */
    protected $params;

    /**
     * @param \Gemu\Core\Cache $cache
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
        $this->params = array();
    }

    /**
     * @todo Refactor this
     *
     * @param $endPoint
     * @param array $params
     *
     * @return string
     */
    protected function makeUrl($endPoint, array $params = array())
    {
        $url = join('',
            array(
                'http://', $_SERVER['HTTP_HOST'], $_SERVER['SCRIPT_NAME'], $endPoint,
            )
        );
        return $url. (empty($params) ? '' : '?'.http_build_query($params));
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return mixed
     * @throws \Gemu\Core\Error\BadEndPoint
     */
    public function emulate(Request $request)
    {
        $endPoint = $this->getEndPoint($request);
        if(!method_exists($this, $endPoint)) {
            throw new BadEndPoint(get_called_class(), $endPoint);
        }
        $this->initParams($request);
        $this->cache->pushLog(
            $this->params['rid'],
            sprintf(
                '%s endpoint invoked',
                $endPoint
            )
        );
        return $this->$endPoint();
    }

    /**
     * Get method which we need to invoke inside emulator based on request
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return string
     */
    protected abstract function getEndPoint(Request $request);

    /**
     * Initializes parameters for use inside of other emulator invoked methods
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return void
     */
    protected abstract function initParams(Request $request);
}
