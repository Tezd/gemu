<?php

namespace Gemu\Core;

use Predis\Client;

class Cache
{
    /**
     * @type \Predis\Client
     */
    protected $redis;

    const LOG_PREFIX = 'emulator:log';
    const PARAMS_PREFIX = 'emulator:params';
    const EXPIRE = 3600;

    /**
     * @param \Predis\Client $redis
     */
    public function __construct(Client $redis)
    {
        $this->redis = $redis;
    }

    /**
     * @param string $id
     * @param array $logEntry
     *
     * @return string
     */
    public function pushLog($id, array $logEntry)
    {
        $key = $this->getKey(self::LOG_PREFIX, $id);
        $this->redis->rpush($key, json_encode($logEntry));
        $this->redis->expire($key, self::EXPIRE);
        return $id;
    }

    /**
     * @param string $id
     *
     * @return mixed
     */
    public function popLog($id)
    {
        return
            $this->redis->lpop(
                $this->getKey(
                    self::LOG_PREFIX,
                    $id
                )
            );
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function saveParams(array $params)
    {
        return $this->updateParams(md5(uniqid(mt_rand(), true)), $params);
    }

    /**
     * @param string $id
     * @param array $params
     *
     * @return string
     */
    public function updateParams($id, array $params)
    {
        $key = $this->getKey(self::PARAMS_PREFIX, $id);
        $this->redis->set($key, json_encode($params));
        $this->redis->expire($key, self::EXPIRE);
        return $id;
    }

    /**
     * @param string $id
     *
     * @return array
     */
    public function loadParams($id)
    {
        return (array)json_decode(
            $this->redis->get(
                $this->getKey(
                    self::PARAMS_PREFIX,
                    $id
                )
            ),
            true
        );
    }

    /**
     * @param string $scope
     * @param string $id
     *
     * @return string
     */
    protected function getKey($scope, $id)
    {
        return sprintf('%s:%s', $scope, $id);
    }
}
