<?php

use Symfony\Component\HttpFoundation\Request;

$api = $app['controllers_factory'];

$api->match(
    '/zeptomobile/mt', function() {
        echo "Operator ID: " . rand(100, 999) . "\r\n";
        echo "#Message Receive correctly\r\n";
        echo "ORDERID:" . 10053082950001 . "\r\n";
        return '';
    }
);

$api->match(
    '/zeptomobile/new/mt', function() {
        echo "OK:123456";
        return '';
    }
);


$api->match(
    '/send', function(Request $request) {
        $account = new Gemu\Models\Account(
            $request->get('msisdn'),
            $request->get('opid'),
            $request->get('gateway'),
            $request->get('country')
        );


        $account->mo($request->get('text'), $request->get('shortcode'));

        $params = [
            'country' => $request->get('country'),
            'msisdn' => $request->get('msisdn'),
            'text' => $request->get('text'),
            'shortcode' => $request->get('shortcode'),
            'opid' => $request->get('opid'),
        ];
        $url = "http://172.19.0.2/samplatform/api/v1/gemu/gateways/{$request->get('gateway')}/mo?" . http_build_query($params);
        echo file_get_contents($url);
        return '';
    }
);


/*
$api->post(
    '/accounts', function () {
        $gateway,$operatpr,$msisdn, $country
        echo "OK:123456";
        return '';
    }
);

$api->post(
    '/accounts/mo', function () {
        $accountid, $text, $sc
        echo "OK:123456";
        return '';
    }
);

$api->post(
    '/accounts/inbox', function () {
        $accountid, $text
        echo "OK:123456";
        return '';
    }
);
 */

return $api;
