<?php

namespace Gemu\Gateway\Ipx\Soap;

use Gemu\Core\Gateway\EndPoint\Emulator;
use Gemu\Core\Gateway\EndPoint\Request;

/**
 * Class OnlineLookup
 * @package Gemu\Gateway\Ipx\Soap
 */
final class OnlineLookup extends Emulator
{
    /**
     * @param \Gemu\Core\Gateway\EndPoint\Request $request
     *
     * @return array
     */
    protected function resolveClientIP(Request $request)
    {
        $params = $this->cache->loadParams();

        if ($params['config']['flow'] == '3g') {
            $this->cache->pushInfo('Moving to 3g flow');
            $responseCode = 0;
            $operator = $params['config']['operator'];
        } else {
            $this->cache->pushInfo('Moving to wifi flow');
            $responseCode = 3;
            $operator = '';
        }
        return [
            'correlationId' => $request->getTransactionId(),
            'lookupId' => uniqid(),
            'operator' => $operator,
            'operatorNetworkCode' => 'NTWRK',
            'country' => 'ES',
            'countryName' => 'Espain',
            'responseCode' => $responseCode,
            'responseMessage' => '',
        ];
    }

    /**
     * @param string $name
     *
     * @param array $data
     *
     * @return string
     */
    protected function getTransactionId($name, array $data)
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
