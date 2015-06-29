<?php

namespace Gemu\Gateway\Ipx\Soap;

use Gemu\Core\Gateway\EndPoint\Emulator;
use Gemu\Core\Gateway\EndPoint\Request;

/**
 * Class Identification
 * @package Gemu\Gateway\Ipx\Soap
 */
final class Identification extends Emulator
{
    /**
     * @param \Gemu\Core\Gateway\EndPoint\Request $request
     *
     * @return array
     */
    protected function createSession(Request $request)
    {
        $params = $this->cache->loadParams();
        $params['return_url'] = $request->get('returnURL');
        $this->cache->updateParams($params);
        return [
            'correlationId' => $request->getTransactionId(),
            'sessionId' => $request->get('returnURL'),
            'redirectURL' => 'http://gemu.app/emulate/Ipx/redirectUrl?rid='.$request->getTransactionId(),
            'responseCode' => 0,
            'responseMessage' => '',
        ];
    }

    /**
     * @param \Gemu\Core\Gateway\EndPoint\Request $request
     *
     * @return array
     */
    protected function checkStatus(Request $request)
    {
        return [
            'correlationId' => $request->getTransactionId(),
            'statusCode' => 1,
            'statusReasonCode' => 0,
            'statusMessage' => '',
            'responseCode' => 0,
            'responseMessage' => '',
        ];
    }

    /**
     * @param \Gemu\Core\Gateway\EndPoint\Request $request
     *
     * @return array
     */
    protected function finalizeSession(Request $request)
    {
        $params = $this->cache->loadParams();
        return [
            'correlationId' => $request->getTransactionId(),
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
