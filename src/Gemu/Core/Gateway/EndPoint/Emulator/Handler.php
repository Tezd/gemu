<?php

namespace Gemu\Core\Gateway\EndPoint\Emulator;

use Gemu\Core\Cache;

/**
 * Class Handler
 * @package Gemu\Core\Gateway\EndPoint\Emulator
 */
trait Handler
{
    /**
     * @type \Gemu\Core\Cache
     */
    protected $cache;

    protected $transaction_key;

    /**
     * @param \Gemu\Core\Cache $cache
     */
    public function __construct(Cache $cache)
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
    abstract protected function getTransactionKey($name, array $data);

    /**
     * @param mixed $rawData
     *
     * @return array
     */
    abstract protected function getData($rawData);

    /**
     * @return array
     */
    protected function loadParams()
    {
        return $this->cache->loadParams($this->transaction_key);
    }

    /**
     * @param array $params
     *
     * @return string
     */
    protected function updateParams(array $params)
    {
        return $this->cache->updateParams(
            $this->transaction_key,
            $params
        );
    }

    /**
     * @param array $logEntry
     *
     * @return string
     */
    protected function pushLog($scope, $logEntry)
    {
        return $this->cache->pushLog(
            $this->transaction_key,
            json_encode(
                [
                    $scope => $logEntry
                ]
            )
        );
    }

    /**
     * @param string $info
     *
     * @return string
     */
    protected function pushInfo($info)
    {
        return $this->pushLog('info', $info);
    }

    /**
     * @todo buffer
     * @param string $name
     * @param array $arguments
     */
    public function __call($name, array $arguments)
    {
        if(!method_exists($this, $name)) {
            throw new \BadFunctionCallException(
                sprintf(
                    'Class[%s] doesn\'t support method[%s]. Maybe you forgot to add it?',
                    static::class,
                    $name
                )
            );
        }
        $data = $this->getData($arguments[0]);
        $this->transaction_key = $this->getTransactionKey($name, $data);
        $result = $this->$name($data);
        $this->pushLog('endpoint', $name);
        $this->pushLog('request', $data);
        $this->pushLog('response', $result);
        return $result;
    }


}
