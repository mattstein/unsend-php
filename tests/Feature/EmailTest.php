<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Unsend\Exceptions\ApiException;

test('throws exception for bad host', function () {
    $mock = new MockHandler([
        new \GuzzleHttp\Exception\ConnectException(
            'Could not resolve host: bad-host.example (see https://curl.haxx.se/libcurl/c/libcurl-errors.html)',
            new \GuzzleHttp\Psr7\Request('GET', '')
        ),
    ]);
    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);
    $unsend = new \Unsend\Unsend($client);
    $unsend->listEmails();
})->throws(GuzzleHttp\Exception\ConnectException::class);

test('wraps non-2xx in ApiException', function () {
    $mock = new MockHandler([
        new Response(404, ['Content-Type' => 'application/json'], '{"error":{"code":"NOT_FOUND","message":"Not found"}}'),
    ]);
    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);
    $unsend = new \Unsend\Unsend($client);
    $unsend->listEmails();
})->throws(ApiException::class);

test('wraps forbidden in ApiException', function () {
    $mock = new MockHandler([
        new Response(403, ['Content-Type' => 'application/json'], '{"error":{"code":"FORBIDDEN","message":"Invalid API token"}}'),
    ]);
    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);
    $unsend = new \Unsend\Unsend($client);
    $unsend->listEmails();
})->throws(ApiException::class);

test('sendEmail sends', function () {
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

    expect($response->getData()->emailId)->toBeString()
        ->and($response->getStatus())->toBe(200);
});

test('sendEmail requires arguments', function () {
    $mock = new MockHandler;
    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);
    $unsend = new \Unsend\Unsend($client);

    // Missing required `to` and `from`
    $unsend->sendEmail([
        'subject' => 'Library Test Email',
        'html' => '<p>This is a test!</p>',
        'text' => 'Heyo, this is a test!',
    ]);
})->throws(\Unsend\Exceptions\MissingArgumentException::class);

test('sendEmail rejects invalid arguments', function () {
    $mock = new MockHandler;
    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);
    $unsend = new \Unsend\Unsend($client);

    // Nonsense `favoriteBluth`
    $unsend->sendEmail([
        'favoriteBluth' => 'Gob',
        'to' => 'reply@example.tld',
        'from' => 'reply@example.tld',
        'subject' => 'Library Test Email',
        'html' => '<p>This is a test!</p>',
        'text' => 'Heyo, this is a test!',
    ]);
})->throws(\Unsend\Exceptions\InvalidArgumentException::class);
