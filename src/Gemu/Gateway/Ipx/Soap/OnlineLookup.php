<?php

namespace Gemu\Gateway\Ipx\Soap;

use Gemu\Core\Cache;
use Gemu\Gateway\Ipx\Soap\OnlineLookup\ResolveClientIPRequest;

/**
 * Class OnlineLookup
 * @package Gemu\Gateway\Ipx\Soap
 */
class OnlineLookup
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
     * @param \Gemu\Gateway\Ipx\Soap\OnlineLookup\ResolveClientIPRequest $request
     *
     * @return array
     */
    public function resolveClientIP(ResolveClientIPRequest $request)
    {
        $params = $this->cache->loadParams($request->correlationId);

        if ($params['config']['flow'] == '3g') {
            $this->cache->pushLog($request->correlationId, 'Moving to 3g flow');
            $responseCode = 0;
            $operator = $params['config']['operator'];
        } else {
            $this->cache->pushLog($request->correlationId, 'Moving to wifi flow');
            $responseCode = 3;
            $operator = '';
        }
        return array(
            'correlationId' => $request->correlationId,
            'lookupId' => uniqid(),
            'operator' => $operator,
            'operatorNetworkCode' => 'NTWRK',
            'country' => 'ES',
            'countryName' => 'Espain',
            'responseCode' => $responseCode,
            'responseMessage' => '',
        );
    }
}
