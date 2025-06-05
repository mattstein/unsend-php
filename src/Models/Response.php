<?php

namespace Unsend\Models;

use JsonException;
use Psr\Http\Message\ResponseInterface;

class Response
{
    private ResponseInterface $responseObject;

    private int $status;

    private mixed $data;

    /**
     * @throws JsonException
     */
    public static function create(ResponseInterface $responseObject): Response
    {
        $response = new self;
        $response->responseObject = $responseObject;
        $response->status = $responseObject->getStatusCode();
        $response->data = json_decode(
            $responseObject->getBody(),
            false,
            512,
            JSON_THROW_ON_ERROR
        );

        return $response;
    }

    public function getResponseObject(): ResponseInterface
    {
        return $this->responseObject;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getData(): mixed
    {
        return $this->data;
    }
}
