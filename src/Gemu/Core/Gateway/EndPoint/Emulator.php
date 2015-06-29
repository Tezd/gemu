<?php

namespace Gemu\Core\Gateway\EndPoint;

use Gemu\Core\Cache\Proxy;
use Gemu\Core\Gateway\EndPoint;

/**
 * Class Emulator
 * @package Gemu\Core\Gateway\EndPoint\Emulator
 */
abstract class Emulator extends EndPoint
{
    /**
     * @type \Gemu\Core\Cache\Proxy
     */
    protected $cache;

    /**
     * @param \Gemu\Core\Cache\Proxy $cache
     */
    public function __construct(Proxy $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param \Gemu\Core\Cache\Proxy $cache
     *
     * @return static
     */
    public static function getInstance(Proxy $cache)
    {
        return new static($cache);
    }

    /**
     * @param string $name
     *
     * @param array $data
     *
     * @return string
     */
    abstract protected function getTransactionId($name, array $data);

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

    /**
     * @todo buffer
     * @param string $name
     * @param array $arguments
     */
    public function __call($name, array $arguments)
    {
        if (!method_exists($this, $name)) {
            throw new \BadFunctionCallException(
                sprintf(
                    'Class[%s] doesn\'t support method[%s]. Maybe you forgot to add it?',
                    static::class,
                    $name
                )
            );
        }
        $data = $this->getData($arguments[0]);
        $transaction_id = $this->getTransactionId($name, $data);
        $this->cache->setTransactionId($transaction_id);
        $result = $this->$name($transaction_id, $data);
        $this->cache->pushLog('endpoint', $name);
        $this->cache->pushLog('request', $data);
        $this->cache->pushLog('response', $result);
        return $result;
    }
}
