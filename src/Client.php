<?php

namespace Unsend;

use GuzzleHttp\Client as GuzzleClient;

class Client
{
    /**
     * Returns a preconfigured instance of a Guzzle client.
     *
     * @param array{
     *     base_uri?: string,
     *     timeout?: float|int,
     *     connect_timeout?: float|int,
     *     headers?: array<string,string>
     * } $options
     */
    public static function create(
        string $apiKey,
        string $baseUrl = 'https://app.unsend.dev',
        array $options = [],
    ): GuzzleClient {
        $defaultOptions = [
            'base_uri' => $baseUrl,
            'timeout' => 10.0,
            'connect_timeout' => 5.0,
            'headers' => [
                'Accept' => 'application/json',
                'User-Agent' => 'unsend-php/0.1 (PHP '.PHP_VERSION.')',
                'Authorization' => 'Bearer '.$apiKey,
            ],
        ];

        // Merge headers and options in a predictable way
        if (isset($options['headers'])) {
            $options['headers'] = array_merge($defaultOptions['headers'], $options['headers']);
        }

        $merged = array_replace_recursive($defaultOptions, $options);

        return new GuzzleClient($merged);
    }
}
