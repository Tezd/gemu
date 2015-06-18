<?php

namespace Gemu\Core\Gateway\EndPoint\Emulator;

use Gemu\Core\Cache\Proxy;

/**
 * Class Handler
 * @package Gemu\Core\Gateway\EndPoint\Emulator
 */
trait Handler
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
     * @param string $name
     *
     * @param array $data
     *
     * @return string
     */
    abstract protected function getTransactionId($name, array $data);

    /**
     * @param mixed $rawData
     *
     * @return array
     */
    abstract protected function getData($rawData);

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
