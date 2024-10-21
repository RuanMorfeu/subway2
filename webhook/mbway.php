function make_deposit($name, $phone, $value) {
    $client = new Client();
    $eupago = $client->request("POST", "https://checkoutweb.com/api/merchant/order", [
        'form_params' => [
            'amount' => $value,
            'currency' => 'EUR',
            'merchant_slug' => 'deibet',
            'identifier' => 'swde',
            'payment_type' => 'mbway',
            'customer' => [
                'name' => $name,
                'tax_id' => 12344,
                'phone' => $phone
            ],
            'callbackUrl' => 'https://checkoutweb.com/api/webhook/euroxpcreate' // Aqui está o callback URL
        ]
    ]);

    return json_decode($eupago->getBody(), true);
}
