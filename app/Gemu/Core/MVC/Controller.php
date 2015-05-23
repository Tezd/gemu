<?php

namespace Gemu\Core\MVC;

use Symfony\Component\HttpFoundation\Response;

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

    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    public function homepage()
    {
        return $this->twig->render('homepage.twig');
//        return new Response('Xyu');
    }
}
