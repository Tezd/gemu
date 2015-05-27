<?php

namespace Gemu\Gateway\Ipx\Soap;

use Gemu\Gateway\Ipx\Soap\Identification\CheckStatusRequest;
use Gemu\Gateway\Ipx\Soap\Identification\CreateSessionRequest;
use Gemu\Core\Cache;
use Gemu\Gateway\Ipx\Soap\Identification\FinalizeSessionRequest;

class Identification
{

    protected $cache;

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    public function createSession(CreateSessionRequest $request)
    {
        $params = $this->cache->loadParams($request->correlationId);
        $params['return_url'] = $request->returnURL;
        $this->cache->saveParams($request->correlationId, $params);
        return array(
            'correlationId' => $request->correlationId,
            'sessionId' => uniqid(),
            'redirectURL' => 'http://172.19.0.2/gemu/emulate/Ipx/returnUrl?rid='.$request->correlationId,
            'responseCode' => 0,
            'responseMessage' => '',
        );
    }

    public function checkStatus(CheckStatusRequest $request)
    {
        return array(
            'correlationId' => $request->correlationId,
            'statusCode' => 1,
            'statusReasonCode' => 0,
            'statusMessage' => '',
            'responseCode' => 0,
            'responseMessage' => '',
        );
    }

    public function finalizeSession(FinalizeSessionRequest $request)
    {
        $params = $this->cache->loadParams($request->correlationId);
        return array(
            'correlationId' => $request->correlationId,
            'transactionId' => uniqid(),
            'consumerId' => $params['config']['msisdn'],
            'operator' => $params['config']['operator'],
            'operatorNetworkCode' => 'NTWRK',
            'country' => 'ES',
            'countryName' => 'ESPAIN',
            'responseCode' => 0,
            'responseMessage' => '',
        );
    }
}
