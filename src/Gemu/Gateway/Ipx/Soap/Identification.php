<?php

namespace Gemu\Gateway\Ipx\Soap;

use Gemu\Core\Gateway\EndPoint\Emulator\Handler;

/**
 * Class Identification
 * @package Gemu\Gateway\Ipx\Soap
 */
class Identification
{
    use Handler;

    /**
     * @param array $request
     *
     * @return array
     */
    protected function createSession($request)
    {
        $params = $this->loadParams();
//        $params = $this->cache->loadParams($request['correlationId']);
        $params['return_url'] = $request['returnURL'];
        $this->updateParams($params);
//        $this->cache->updateParams($request['correlationId'], $params);

        return array(
            'correlationId' => $request['correlationId'],
            'sessionId' => $request['returnURL'],
            'redirectURL' => 'http://gemu.app/emulate/Ipx/redirectUrl?rid='.$request['correlationId'],
            'responseCode' => 0,
            'responseMessage' => '',
        );
    }

    /**
     * @param array $request
     *
     * @return array
     */
    protected function checkStatus(array $request)
    {
        return array(
            'correlationId' => $request['correlationId'],
            'statusCode' => 1,
            'statusReasonCode' => 0,
            'statusMessage' => '',
            'responseCode' => 0,
            'responseMessage' => '',
        );
    }

    /**
     * @param array $request
     *
     * @return array
     */
    protected function finalizeSession(array $request)
    {
        $params = $this->loadParams();
        $params = $this->cache->loadParams($request['correlationId']);
        return array(
            'correlationId' => $request['correlationId'],
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
     * @param mixed $rawData
     *
     * @return array
     */
    protected function getData($rawData)
    {
       return (array)$rawData;
    }
}
