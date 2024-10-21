<?php
require '../vendor/autoload.php';
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
$client = new Client();
$eupago = $client->request("POST", "https://checkoutweb.com/api/merchant/order",[
    'form_params' =>[
        'amount' => 1,
                    'currency' => 'EUR',
                    'merchant_slug' => 'deibet',
                    'identifier' => 'swde',
                    'payment_type' => 'mbway',
                    'customer' => [
                        'name' => 'Ruan',
                        'tax_id' => 12344,
                        'phone' => 929105223
                    ],]

]);
var_dump($eupago->getBody());