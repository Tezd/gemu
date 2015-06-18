<?php

namespace Gemu\Core\Gateway\EndPoint\Emulator;

use Gemu\Core\Cache;
use Gemu\Core\Gateway\EndPoint\Emulator;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class Soap
 * @package Gemu\Core\Gateway\EndPoint\Emulator
 */
abstract class Soap extends Emulator
{
    use Handler {
        Handler::__call as parentCall;
    }

    /**
     * @type \SoapServer
     */
    private $server;

    /**
     * @param string $scope
     *
     * @return bool
     */
    private function createSoapServer($scope)
    {
        $namespace = substr(static::class, 0, strrpos(static::class, '\\'));
        $soapHandlerClass = $namespace.'\\Soap\\'.$scope;
        if (!class_exists($soapHandlerClass)) {
            return false;
        }
        $this->server = new \SoapServer(
            __DIR__.'/../../../../../../app/wsdl/'.
            substr($namespace, strrpos($namespace, '\\') + 1).
            '/'.
            $scope.
            '.wsdl',
            [
                'cache_wsdl' => WSDL_CACHE_NONE,
                'trace' => true
            ]
        );
        $this->server->setObject(new $soapHandlerClass($this->cache));
        return true;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function handle()
    {
        ob_start();
        $this->server->handle();
        return new Response(
            ob_get_clean(),
            200,
            [ 'Content-Type' => 'text/xml; charset=ISO-8859-1' ]
        );
    }

    /**
     * @param string $name
     * @param array $arguments
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function __call($name, array $arguments)
    {
        return $this->createSoapServer($name) ?
            $this->handle() :
            $this->parentCall($name, $arguments);
    }
}
