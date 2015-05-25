<?php

namespace Gemu\Core;

use Predis\Client;

class Cache
{
    /**
     * @type \Predis\Client
     */
    protected $redis;

    const LOG_PREFIX = 'emulator:log:';
    const PARAMS_PREFIX = 'emulator:params:';

    /**
     * @param \Predis\Client $redis
     */
    public function __construct(Client $redis)
    {
        $this->redis = $redis;
    }

    /**
     * @param string $key
     * @param string $data
     *
     * @return int
     */
    public function pushLog($key, $data)
    {
        return $this->redis->rpush(
            $this->getKey(self::LOG_PREFIX, $key),
            $data
        );
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
     * @param string $key
     * @param array $value
     *
     * @return mixed
     */
    public function saveParams($key, array $value)
    {
        return $this->redis->set(
            $this->getKey(
                self::PARAMS_PREFIX,
                $key
            ),
            json_encode($value)
        );
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
                    self::PARAMS_PREFIX, $key
                )
            )
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
