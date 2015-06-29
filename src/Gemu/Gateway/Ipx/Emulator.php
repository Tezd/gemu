<?php

namespace Gemu\Gateway\Ipx;

use Gemu\Core\Gateway\EndPoint\Emulator\Soap as SoapEmulator;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class Emulator
 * @package Gemu\Gateway\Ipx
 */
final class Emulator extends SoapEmulator
{
    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function redirectUrl()
    {
        $params = $this->cache->loadParams();
        return new RedirectResponse($params['return_url']);
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
        return $data['rid'];
    }
}
