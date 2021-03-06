<?php

namespace Gemu\Core;

use Gemu\Core\MVC\Controller;
use Silex\Application as Base;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\Request;

/**
 * @todo add configuration loading
 * Class Application
 * @package Gemu\Core
 */
final class Application extends Base
{
    /**
     * Additional registration should be before additional sharing
     * because sharing can rely on service being registered
     */
    public function __construct()
    {
        parent::__construct();
        $this->additionalRegistration();
        $this->additionalSharing();
        $this->initRouting();
        $this['debug'] = true;
    }

    private function additionalRegistration()
    {
        $this->register(
            new TwigServiceProvider(),
            [ 'twig.path' => __DIR__.'/../../../app/views' ]
        );

        $this->register(new ServiceControllerServiceProvider());
    }

    private function additionalSharing()
    {
        $this['gemu.controller'] = $this->share(function () {
            return new Controller($this['twig']);
        });
        $this['gemu.factory'] = $this->share(function () {
            return new Factory();
        });
    }

    /**
     * Emulate and service are separated because its easier to see the flow and code is more readable
     */
    private function initRouting()
    {
        $this->match('emulate/{gateway}/{endPoint}', function (Request $request, $gateway) {
            return $this['gemu.factory']->getEmulator($gateway)->handle($request);
        });

        $this->match('service/{gateway}/{endPoint}', function (Request $request, $gateway) {
            return $this['gemu.factory']->getService($gateway)->handle($request);
        });

        $this->get('/', 'gemu.controller:homepage')->bind('homepage');

        $this->get('remote/optin', 'gemu.controller:optin')->bind('optin');

        $this->post('save/transaction', function (Request $request) {
            return $this->json(
                [
                    'id' => $this['gemu.factory']
                        ->getCache()
                        ->saveParams([ 'config' => $request->request->all() ])
                ]
            );
        });
    }
}
