<?php

namespace Unsend;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use Unsend\Exceptions\InvalidArgumentException;
use Unsend\Exceptions\MissingArgumentException;

class Unsend
{
    private GuzzleClient $client;

    private static string $apiBase = '/api/v1';

    public function __construct(GuzzleClient $client)
    {
        $this->client = $client;
    }

    /**
     * @throws GuzzleException
     */
    public function getEmail(string $emailId): ResponseInterface
    {
        return $this->client->get(
            self::buildUrl('/emails/'.$emailId)
        );
    }

    /**
     * @todo figure out why this returns 404
     *
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function listEmails(array $parameters = []): ResponseInterface
    {
        $supportedParameters = [
            'page',
            'limit',
            'startDate',
            'endDate',
            'domainId',
        ];

        foreach ($parameters as $key => $value) {
            if (! in_array($key, $supportedParameters, true)) {
                throw new InvalidArgumentException('“'.$key.'” is not a valid argument.');
            }
        }

        return $this->client->get(
            self::buildUrl('/emails'),
            [
                'query' => $parameters,
            ]
        );
    }

    /**
     * @throws GuzzleException
     * @throws MissingArgumentException
     * @throws InvalidArgumentException
     */
    public function sendEmail(array $parameters): ResponseInterface
    {
        $supportedParameters = [
            'to',
            'from',
            'subject',
            'templateId',
            'variables',
            'replyTo',
            'cc',
            'bcc',
            'text',
            'html',
            'attachments',
            'scheduledAt',
            'inReplyToId',
        ];

        $requiredParameters = [
            'to',
            'from',
        ];

        foreach ($requiredParameters as $parameter) {
            if (! isset($parameters[$parameter])) {
                throw new MissingArgumentException('“'.$parameter.'” is required.');
            }
        }

        foreach ($parameters as $key => $value) {
            if (! in_array($key, $supportedParameters, true)) {
                throw new InvalidArgumentException('“'.$key.'” is not a valid argument.');
            }
        }

        return $this->client->post(
            self::buildUrl('/emails'),
            [
                RequestOptions::JSON => $parameters,
            ]
        );
    }

    /**
     * @throws GuzzleException
     */
    public function batchEmail(array $parameters): ResponseInterface
    {
        return $this->client->post(
            self::buildUrl('/emails/batch'),
            [
                RequestOptions::JSON => $parameters,
            ]
        );
    }

    /**
     * @throws GuzzleException
     */
    public function updateSchedule(string $emailId, string $scheduledAt): ResponseInterface
    {
        return $this->client->patch(
            self::buildUrl('/emails/'.$emailId),
            [
                'scheduledAt' => $scheduledAt,
            ]
        );
    }

    /**
     * @throws GuzzleException
     */
    public function cancelSchedule(string $emailId): ResponseInterface
    {
        return $this->client->post(
            self::buildUrl('/emails/'.$emailId.'/cancel')
        );
    }

    /**
     * @throws GuzzleException
     */
    public function getContact(string $contactBookId, string $contactId): ResponseInterface
    {
        return $this->client->get(
            self::buildUrl('/contactBooks/'.$contactBookId.'/contacts/'.$contactId)
        );
    }

    /**
     * @throws GuzzleException
     */
    public function getContacts(string $contactBookId): ResponseInterface
    {
        return $this->client->get(
            self::buildUrl('/contactBooks/'.$contactBookId.'/contacts')
        );
    }

    /**
     * @throws GuzzleException
     */
    public function createContact(string $contactBookId, array $parameters): ResponseInterface
    {
        return $this->client->post(
            self::buildUrl('/contactBooks/'.$contactBookId.'/contacts'),
            [
                RequestOptions::JSON => $parameters,
            ]
        );
    }

    /**
     * @throws GuzzleException
     */
    public function updateContact(string $contactBookId, string $contactId, array $parameters): ResponseInterface
    {
        return $this->client->patch(
            self::buildUrl('/contactBooks/'.$contactBookId.'/contacts/'.$contactId),
            [
                RequestOptions::JSON => $parameters,
            ]
        );
    }

    /**
     * @throws GuzzleException
     */
    public function upsertContact(string $contactBookId, string $contactId, array $parameters): ResponseInterface
    {
        return $this->client->put(
            self::buildUrl('/contactBooks/'.$contactBookId.'/contacts/'.$contactId),
            [
                RequestOptions::JSON => $parameters,
            ]
        );
    }

    /**
     * @throws GuzzleException
     */
    public function deleteContact(string $contactBookId, string $contactId): ResponseInterface
    {
        return $this->client->delete(
            self::buildUrl('/contactBooks/'.$contactBookId.'/contacts/'.$contactId)
        );
    }

    /**
     * @todo figure out why this returns 404
     *
     * @throws GuzzleException
     */
    public function getDomain(int $id): ResponseInterface
    {
        return $this->client->get(
            self::buildUrl('/domains/'.$id)
        );
    }

    /**
     * @throws GuzzleException
     */
    public function getDomains(): ResponseInterface
    {
        return $this->client->get(
            self::buildUrl('/domains')
        );
    }

    /**
     * @throws GuzzleException
     */
    public function createDomain(array $parameters): ResponseInterface
    {
        return $this->client->post(
            self::buildUrl('/domains'),
            [
                RequestOptions::JSON => $parameters,
            ]
        );
    }

    /**
     * @throws GuzzleException
     */
    public function verifyDomain(int $id): ResponseInterface
    {
        return $this->client->put(
            self::buildUrl('/domains/'.$id.'/verify')
        );
    }

    private static function buildUrl(string $path): string
    {
        return self::$apiBase.$path;
    }
}
