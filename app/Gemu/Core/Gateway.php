<?php

namespace Gemu\Core;

use Gemu\Core\Cache;
use Gemu\Core\Error\BadEndPoint;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Controller
 * @package Gemu\Core\MVC
 */
class Gateway
{
    /**
     * @type array
     */
    protected $params;

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
        $this->params = array();
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return mixed
     * @throws \Gemu\Core\Error\BadEndPoint
     */
    public function handle(Request $request)
    {
        $endPoint = $this->getEndPoint($request);
        if(!method_exists($this, $endPoint)) {
            throw new BadEndPoint(get_called_class(), $endPoint);
        }
        $this->initParams($request);
        $this->cache->pushLog(
            $this->params['RequestID'],
            sprintf(
                '%s endpoint invoked',
                $endPoint
            )
        );
        return $this->$endPoint();
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return mixed
     */
    protected function getEndPoint(Request $request)
    {
        return $request->attributes->get('endPoint');
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    protected function initParams(Request $request)
    {
        $rid = $request->query->get('RequestID');
        $this->params = array_merge(
            $this->cache->loadParams($rid),
            $request->query->all(),
            $request->request->all()
        );
        $this->params['config'] = json_decode(
            base64_decode(
                $rid,
                true
            ),
            true
        );
    }
}
