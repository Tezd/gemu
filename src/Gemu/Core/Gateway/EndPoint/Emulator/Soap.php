<?php

namespace Gemu\Core\Gateway\EndPoint\Emulator;

use Gemu\Core\Error\BadSoapEndPoint;
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
     * @param $scope
     *
     * @return $this
     * @throws \Gemu\Core\Error\BadSoapEndPoint
     */
    private function createSoapServer($scope)
    {
        $namespace = substr(static::class, 0, strrpos(static::class, '\\'));
        $soapHandlerClass = $namespace.'\\Soap\\'.$scope;
        if (!class_exists($soapHandlerClass)) {
            throw new BadSoapEndPoint(static::class, $scope, $soapHandlerClass);
        }
        $this->server = new \SoapServer(
            __DIR__.'/../../../../../../app/wsdl/'.
            substr($namespace, strrpos($namespace, '\\') + 1).
            '/'.
            $scope.
            '.wsdl',
            array(
                'cache_wsdl' => WSDL_CACHE_NONE,
                'trace' => true
            )
        );
        $this->server->setObject(new $soapHandlerClass($this->cache));
        return $this;
    }

    /**
     * @return $this
     */
    private function buffer()
    {
        ob_start();
        return $this;
    }

    /**
     * @return $this
     */
    private function handle()
    {
        $this->server->handle();
        return $this;
    }

    /**
     * @return string
     */
    private function output()
    {
        return ob_get_clean();
    }

    /**
     * @todo refactor it not to rely on exception
     * @param string $name
     * @param array $arguments
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function __call($name, array $arguments)
    {
        try {
            return $this->parentCall($name, $arguments);
        }
        catch(\BadFunctionCallException $ex) {
            return new Response(
                $this->createSoapServer($name)->buffer()->handle()->output(),
                200,
                array(
                    'Content-Type' => 'text/xml; charset=ISO-8859-1'
                )
            );
        }
    }
}
