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
     * @param $id
     * @param string $data
     *
     * @return string
     */
    public function pushLog($id, $data)
    {
        $key = $this->getKey(self::LOG_PREFIX, $id);
        $this->redis->rpush($key, $data);
        $this->redis->expire($key, self::EXPIRE);
        return $id;
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function popLog($key)
    {
        return
            $this->redis->lpop(
                $this->getKey(
                    self::LOG_PREFIX,
                    $key
                )
            );
    }

    /**
     * @param array $value
     *
     * @return string
     */
    public function saveParams(array $value)
    {
        return $this->updateParams(md5(uniqid(mt_rand(), true)), $value);
    }

    /**
     * @param string $id
     * @param array $value
     *
     * @return string
     */
    public function updateParams($id, array $value)
    {
        $key = $this->getKey(self::PARAMS_PREFIX, $id);
        $this->redis->set($key, json_encode($value));
        $this->redis->expire($key, self::EXPIRE);
        return $id;
    }

    /**
     * @param $key
     *
     * @return array
     */
    public function loadParams($key)
    {
        return (array)json_decode(
            $this->redis->get(
                $this->getKey(
                    self::PARAMS_PREFIX,
                    $key
                )
            ),
            true
        );
    }

    /**
     * @param $scope
     * @param $key
     *
     * @return string
     */
    protected function getKey($scope, $key)
    {
        return sprintf('%s:%s', $scope, $key);
    }
}
