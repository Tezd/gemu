<?php

namespace Gemu\Gateway\Ipx\Soap;

use Gemu\Core\Gateway\EndPoint\Emulator;

/**
 * Class Identification
 * @package Gemu\Gateway\Ipx\Soap
 */
final class Identification extends Emulator
{
    /**
     * @param string $transactionId
     * @param array $request
     *
     * @return array
     */
    protected function createSession($transactionId, array $request)
    {
        $params = $this->cache->loadParams();
        $params['return_url'] = $request['returnURL'];
        $this->cache->updateParams($params);
        return [
            'correlationId' => $transactionId,
            'sessionId' => $request['returnURL'],
            'redirectURL' => 'http://gemu.app/emulate/Ipx/redirectUrl?rid='.$transactionId,
            'responseCode' => 0,
            'responseMessage' => '',
        ];
    }

    /**
     *
     * @param string $transactionId
     *
     * @return array
     */
    protected function checkStatus($transactionId)
    {
        return [
            'correlationId' => $transactionId,
            'statusCode' => 1,
            'statusReasonCode' => 0,
            'statusMessage' => '',
            'responseCode' => 0,
            'responseMessage' => '',
        ];
    }

    /**
     * @param string $transactionId
     *
     * @return array
     */
    protected function finalizeSession($transactionId)
    {
        $params = $this->cache->loadParams();
        return [
            'correlationId' => $transactionId,
            'transactionId' => uniqid(),
            'consumerId' => $params['config']['msisdn'],
            'operator' => $params['config']['operator'],
            'operatorNetworkCode' => 'NTWRK',
            'country' => 'ES',
            'countryName' => 'ESPAIN',
            'responseCode' => 0,
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
     * @param mixed $rawData
     *
     * @return array
     */
    protected function getData($rawData)
    {
        return (array)$rawData;
    }
}
