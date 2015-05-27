<?php

namespace Gemu\Gateway\Ipx;

use Gemu\Core\Gateway\Response\Emulator as BaseEmulator;
use Gemu\Gateway\Ipx\Soap\Identification;
use Gemu\Gateway\Ipx\Soap\OnlineLookup;
use Gemu\Gateway\Ipx\Soap\Subscription;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class Emulator extends BaseEmulator
{

    /**
     * Initializes parameters for use inside of other emulator invoked methods
     *
     * @param string $transactionKey
     *
     * @return array
     */
    protected function initParams($transactionKey)
    {
        return null;
    }

    protected function createSoapServer($scope)
    {
        return new \SoapServer(__DIR__.'/../../../../app/wsdl/Ipx/'.$scope);
    }

    protected function OnlineLookup()
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'text/xml; charset=ISO-8859-1');
        ob_start();
        $soapServer = $this->createSoapServer('OnlineLookup.wsdl');
        $soapServer->setObject(new OnlineLookup($this->cache));
        $soapServer->handle();
        return $response->setContent(ob_get_clean());
    }

    protected function Identification()
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'text/xml; charset=ISO-8859-1');
        ob_start();
        $soapServer = $this->createSoapServer('Identification.wsdl');
        $soapServer->setObject(new Identification($this->cache));
        $soapServer->handle();
        return $response->setContent(ob_get_clean());
    }

    protected function Subscription()
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'text/xml; charset=ISO-8859-1');
        ob_start();
        $soapServer = $this->createSoapServer('Subscription.wsdl');
        $soapServer->setObject(new Subscription($this->cache));
        $soapServer->handle();
        return $response->setContent(ob_get_clean());
    }

    protected function redirectUrl()
    {
        $rid = $this->request->get('rid');
        $params  = $this->cache->loadParams($rid);
        return new RedirectResponse(
            $params['return_url']
        );
    }
}
