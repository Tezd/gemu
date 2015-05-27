<?php

namespace Gemu\Gateway\Ipx\Soap;

use Gemu\Core\Cache;
use Gemu\Gateway\Ipx\Soap\OnlineLookup\ResolveClientIPRequest;

class OnlineLookup
{
    protected $cache;

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    public function resolveClientIP(ResolveClientIPRequest $request)
    {
        $params = $this->cache->loadParams($request->correlationId);

        if ($params['config']['flow'] == '3g') {
            $responseCode = 0;
            $operator = $params['config']['operator'];
        }
        else {
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
