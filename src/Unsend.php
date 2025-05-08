<?php

namespace Unsend;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

class Unsend
{
    private GuzzleClient $client;

    public function __construct(GuzzleClient $client)
    {
        $this->client = $client;
    }

    /**
     * @throws GuzzleException
     */
    public function sendEmail(array $parameters): ResponseInterface
    {
        return $this->client->post(
            '/api/v1/emails',
            [
                RequestOptions::JSON => $parameters,
            ]
        );
    }
}
