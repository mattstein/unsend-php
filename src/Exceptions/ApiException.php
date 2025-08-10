<?php

namespace Unsend\Exceptions;

use Psr\Http\Message\ResponseInterface;

class ApiException extends \RuntimeException
{
    private int $statusCode;

    private ?string $errorCode = null;

    private ?string $requestId = null;

    public static function fromResponse(ResponseInterface $response): self
    {
        $status = $response->getStatusCode();
        $body = (string) $response->getBody();

        $message = 'HTTP '.$status;
        $errorCode = null;

        if ($body !== '') {
            try {
                $decoded = json_decode($body, false, 512, JSON_THROW_ON_ERROR);
                if (isset($decoded->error)) {
                    $errorCode = isset($decoded->error->code) ? (string) $decoded->error->code : null;
                    $msg = isset($decoded->error->message) ? (string) $decoded->error->message : null;
                    if ($msg) {
                        $message = $msg;
                    }
                }
            } catch (\Throwable) {
                // keep default message
            }
        }

        $ex = new self($message, $status);
        $ex->statusCode = $status;
        $ex->errorCode = $errorCode;
        $ex->requestId = $response->getHeaderLine('x-request-id') ?: null;

        return $ex;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    public function getRequestId(): ?string
    {
        return $this->requestId;
    }
}
