<?php

namespace Gemu\Core\Cache;

use Gemu\Core\Cache;

/**
 * Class Proxy
 * @package Gemu\Core\Cache
 */
class Proxy
{
    /**
     * @type \Gemu\Core\Cache
     */
    protected $cache;

    /**
     * @type string
     */
    protected $transaction_id;

    /**
     * @param \Gemu\Core\Cache $cache
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param string $transactionId
     */
    public function setTransactionId($transactionId)
    {
        $this->transaction_id = $transactionId;
    }

    /**
     * @param string $scope
     * @param mixed $data
     *
     * @return string
     */
    public function pushLog($scope, $data)
    {
        return $this->cache->pushLog(
            $this->transaction_id,
            [
                'scope' => $scope,
                'data' => $data
            ]
        );
    }

    /**
     * @param string $data
     *
     * @return string
     */
    public function pushInfo($data)
    {
        return $this->pushLog('info', $data);
    }

    /**
     * @return mixed
     */
    public function popLog()
    {
        return $this->cache->popLog($this->transaction_id);
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function saveParams(array $params)
    {
        return $this->cache->saveParams($params);
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function updateParams(array $params)
    {
        return $this->cache->updateParams($this->transaction_id, $params);

    }

    /**
     * @return array
     */
    public function loadParams()
    {
        return $this->cache->loadParams($this->transaction_id);
    }

}
