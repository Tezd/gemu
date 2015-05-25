<?php

namespace Gemu\Core;

use Gemu\Core\MVC\Controller;
use Silex\Application as Base;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\TwigServiceProvider;

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
    }

    private function additionalRegistration()
    {
        $this->register(new TwigServiceProvider(), array(
            'twig.path' => __DIR__.'/../../../app/views',
        ));

        $this->register(new ServiceControllerServiceProvider());
    }

    private function additionalSharing()
    {
        $twig = $this['twig'];
        $this['gemu.controller'] = $this->share(function() use ($twig) {
            return new Controller($twig);
        });
        $this['gemu.factory'] = $this->share(function () {
            return new Factory();
        });
    }
}
