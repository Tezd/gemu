<?php

namespace Gemu\Core\MVC;

/**
 * Class Controller
 * @package Gemu\Core\MVC
 */
class Controller
{
    /**
     * @type \Twig_Environment
     */
    protected $twig;

    /**
     * @param \Twig_Environment $twig
     */
    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @return string
     */
    public function homepage()
    {
        return $this->twig->render(
            'homepage.twig',
            array(
                'gateways' => $this->getGateways()
            )
        );
    }

    /**
     * @return string
     */
    public function optIn()
    {
        return $this->twig->render('optin.twig');
    }

    /**
     * @return array
     */
    protected function getGateways()
    {
        $gateways = array();
        $dh  = opendir(__DIR__.'/../../Gateway');
        while (false !== ($filename = readdir($dh))) {
            if (!in_array($filename, array('.', '..'))) {
                $gateways[] = $filename;
            }
        }
        return $gateways;
    }
}
