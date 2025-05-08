<?php

namespace Unsend;

use GuzzleHttp\Client as GuzzleClient;

class Client
{
    /**
     * Returns a preconfigured instance of a Guzzle client.
     */
    public static function create(
        string $apiKey,
        string $baseUrl = 'https://app.unsend.dev',
    ): GuzzleClient {
        return new GuzzleClient([
            'base_uri' => $baseUrl,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer '.$apiKey,
            ],
        ]);
    }
}
