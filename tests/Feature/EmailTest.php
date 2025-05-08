<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

test('sends email', function () {
    $mock = new MockHandler([
        new Response(200, ['Content-Type' => 'application/json'], '{ "emailId": "bar" }'),
    ]);
    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);

    $unsend = new \Unsend\Unsend($client);
    $response = $unsend->sendEmail([
        'to' => 'reply@example.tld',
        'from' => 'reply@example.tld',
        'subject' => 'Library Test Email',
        'html' => '<p>This is a test!</p>',
        'text' => 'Heyo, this is a test!',
    ]);

    $responseData = json_decode($response->getBody()->getContents(), false);

    expect($response->getStatusCode())->toBeIn([200])
        ->and($responseData->emailId)->toBeString();
});
