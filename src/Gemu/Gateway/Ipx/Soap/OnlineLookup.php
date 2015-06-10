<?php

namespace Gemu\Gateway\Ipx\Soap;

use Gemu\Core\Gateway\EndPoint\Emulator\Handler;
//use Gemu\Gateway\Ipx\Soap\OnlineLookup\ResolveClientIPRequest;

/**
 * Class OnlineLookup
 * @package Gemu\Gateway\Ipx\Soap
 */
class OnlineLookup
{
    use Handler;

    /**
     * @param array $request
     *
     * @return array
     */
    protected function resolveClientIP(array $request)
    {
        $params = $this->loadParams();
//        $params = $this->cache->loadParams($request['correlationId']);

        if ($params['config']['flow'] == '3g') {
            $this->pushInfo('Moving to 3g flow');
//            $this->cache->pushLog($request['correlationId'], 'Moving to 3g flow');
            $responseCode = 0;
            $operator = $params['config']['operator'];
        } else {
            $this->pushInfo('Moving to wifi flow');
//            $this->cache->pushLog($request['correlationId'], 'Moving to wifi flow');
            $responseCode = 3;
            $operator = '';
        }
        return array(
            'correlationId' => $request['correlationId'],
            'lookupId' => uniqid(),
            'operator' => $operator,
            'operatorNetworkCode' => 'NTWRK',
            'country' => 'ES',
            'countryName' => 'Espain',
            'responseCode' => $responseCode,
            'responseMessage' => '',
        );
    }

    /**
     * @param string $name
     *
     * @param array $data
     *
     * @return string
     */
    protected function getTransactionKey($name, array $data)
    {
        return $data['correlationId'];
    }

    /**
     * @param \stdClass $rawData
     *
     * @return array
     */
    protected function getData($rawData)
    {
        return (array)$rawData;
    }
}
