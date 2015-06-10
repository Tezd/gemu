<?php

namespace Gemu\Gateway\Ipx;

use Gemu\Core\Gateway\EndPoint\Emulator\Soap as SoapEmulator;
use Gemu\Gateway\Ipx\Soap\Identification;
use Gemu\Gateway\Ipx\Soap\OnlineLookup;
use Gemu\Gateway\Ipx\Soap\Subscription;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class Emulator
 * @package Gemu\Gateway\Ipx
 */
class Emulator extends SoapEmulator
{
    /**
     * @param array $data
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function redirectUrl(array $data)
    {
        $params = $this->loadParams();
        return new RedirectResponse(
            $params['return_url']
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
        return $data['rid'];
    }
}
