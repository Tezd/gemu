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

    protected function createSoapServer($scope, array $classMap)
    {
        return new \SoapServer(
            __DIR__.'/../../../../app/wsdl/Ipx/'.$scope,
            array('classmap' => $classMap)
        );
    }

    protected function handleSoap(\SoapServer $soapServer)
    {
        ob_start();
        $response = new Response();
        $response->headers->set('Content-Type', 'text/xml; charset=ISO-8859-1');

        $soapServer->handle();
        return $response->setContent(ob_get_clean());
    }

    protected function OnlineLookup()
    {
        $server = $this->createSoapServer(
            'OnlineLookup.wsdl',
            array(
                'ResolveClientIPRequest' => '\\Gemu\\Gateway\\Ipx\\Soap\\OnlineLookup\\ResolveClientIPRequest'
            )
        );
        $server->setObject(new OnlineLookup($this->cache));
        return $this->handleSoap($server);
    }

    protected function Identification()
    {
        $server = $this->createSoapServer(
            'Identification.wsdl',
            array(
                'CreateSessionRequest' => '\\Gemu\\Gateway\\Ipx\\Soap\\Identification\\CreateSessionRequest',
                'CheckStatusRequest' => '\\Gemu\\Gateway\\Ipx\\Soap\\Identification\\CheckStatusRequest',
                'FinalizeSessionRequest' => '\\Gemu\\Gateway\\Ipx\\Soap\\Identification\\FinalizeSessionRequest'
            )
        );
        $server->setObject(new Identification($this->cache));
        return $this->handleSoap($server);
    }

    protected function Subscription()
    {
        $server = $this->createSoapServer(
            'Subscription.wsdl',
            array(
                'CreateSubscriptionRequest' => '\\Gemu\\Gateway\\Ipx\\Soap\\Subscription\\CreateSubscriptionRequest'
            )
        );
        $server->setObject(new Subscription($this->cache));
        return $this->handleSoap($server);
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
