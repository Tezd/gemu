<?php
/**
 * @todo maybe make objects instead of array for parameters for final endpoint functions
 * @todo make transaction_key more easier to track
 * @todo refactor NetM Emulator
 * @todo add post processor for responses so it can convert if needed
 * @todo improve creation of url in emulator classes
 * @todo make functions private :D inside of emulator classes cause they wont be needed anywhere
 * @todo maybe make final classes as well
 * @todo clean up
 * @todo check code
 * @todo resolve issue with assets
 * @todo add flow control for every step
 * @todo improve js
 * @todo create routes in other place
 * @todo make option to turn on|off debugger
 *
 */
require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;

$app = new Gemu\Core\Application();

/** @todo make that purpose be handled by gateway? */
$app->match('{purpose}/{gateway}/{endPoint}', function (Request $request, $purpose, $gateway) use ($app)
{
    return $app['gemu.factory']->getGateway($gateway)->$purpose($request);
})->assert('purpose', 'emulate|service');

$app->get('/', 'gemu.controller:homepage')->bind('homepage');

$app->get('remote/optin', 'gemu.controller:optin')->bind('optin');

$app->post('save/transaction', function (Request $request) use ($app) {
    return $app->json(
        array(
            'id' => $app['gemu.factory']
                ->getCache()
                ->saveParams(
                    array(
                        'config' => $request->request->all()
                    )
                )
        )
    );
});

$app['debug'] = true;
$app->run();
