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
     * @see https://docs.unsend.dev/api-reference/emails/get-email
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
     * @see https://docs.unsend.dev/api-reference/emails/list-emails
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function listEmails(array $parameters = []): ResponseInterface
    {
        self::limitParameters($parameters, [
            'page',
            'limit',
            'startDate',
            'endDate',
            'domainId',
        ]);

        return $this->client->get(
            self::buildUrl('/emails'),
            [
                'query' => $parameters,
            ]
        );
    }

    /**
     * @see https://docs.unsend.dev/api-reference/emails/send-email
     * @throws GuzzleException
     * @throws MissingArgumentException
     * @throws InvalidArgumentException
     */
    public function sendEmail(array $parameters): ResponseInterface
    {
        self::requireParameters($parameters, [
            'to',
            'from',
        ]);

        self::limitParameters($parameters, [
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
        ]);

        return $this->client->post(
            self::buildUrl('/emails'),
            [
                RequestOptions::JSON => $parameters,
            ]
        );
    }

    /**
     * @see https://docs.unsend.dev/api-reference/emails/batch-email
     * @throws GuzzleException
     * @throws MissingArgumentException
     * @throws InvalidArgumentException
     */
    public function batchEmail(array $parameters): ResponseInterface
    {
        self::requireParameters($parameters, [
            'to',
            'from',
        ]);

        self::limitParameters($parameters, [
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
        ]);

        return $this->client->post(
            self::buildUrl('/emails/batch'),
            [
                RequestOptions::JSON => $parameters,
            ]
        );
    }

    /**
     * @see https://docs.unsend.dev/api-reference/emails/update-schedule
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
     * @see https://docs.unsend.dev/api-reference/emails/cancel-schedule
     * @throws GuzzleException
     */
    public function cancelSchedule(string $emailId): ResponseInterface
    {
        return $this->client->post(
            self::buildUrl('/emails/'.$emailId.'/cancel')
        );
    }

    /**
     * @see https://docs.unsend.dev/api-reference/contacts/get-contact
     * @throws GuzzleException
     */
    public function getContact(string $contactBookId, string $contactId): ResponseInterface
    {
        return $this->client->get(
            self::buildUrl('/contactBooks/'.$contactBookId.'/contacts/'.$contactId)
        );
    }

    /**
     * @see https://docs.unsend.dev/api-reference/contacts/get-contacts
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getContacts(string $contactBookId, array $parameters = []): ResponseInterface
    {
        self::limitParameters($parameters, [
            'emails',
            'page',
            'limit',
            'ids',
        ]);

        return $this->client->get(
            self::buildUrl('/contactBooks/'.$contactBookId.'/contacts'),
            [
                'query' => $parameters,
            ]
        );
    }

    /**
     * @see https://docs.unsend.dev/api-reference/contacts/create-contact
     * @throws GuzzleException
     * @throws MissingArgumentException
     * @throws InvalidArgumentException
     */
    public function createContact(string $contactBookId, array $parameters): ResponseInterface
    {
        self::requireParameters($parameters, [
            'email',
        ]);

        self::limitParameters($parameters, [
            'email',
            'firstName',
            'lastName',
            'properties',
            'subscribed',
        ]);

        return $this->client->post(
            self::buildUrl('/contactBooks/'.$contactBookId.'/contacts'),
            [
                RequestOptions::JSON => $parameters,
            ]
        );
    }

    /**
     * @see https://docs.unsend.dev/api-reference/contacts/update-contact
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function updateContact(string $contactBookId, string $contactId, array $parameters = []): ResponseInterface
    {
        self::limitParameters($parameters, [
            'firstName',
            'lastName',
            'properties',
            'subscribed',
        ]);

        return $this->client->patch(
            self::buildUrl('/contactBooks/'.$contactBookId.'/contacts/'.$contactId),
            [
                RequestOptions::JSON => $parameters,
            ]
        );
    }

    /**
     * @see https://docs.unsend.dev/api-reference/contacts/upsert-contact
     * @throws GuzzleException
     * @throws MissingArgumentException
     * @throws InvalidArgumentException
     */
    public function upsertContact(string $contactBookId, string $contactId, array $parameters = []): ResponseInterface
    {
        self::requireParameters($parameters, [
            'email',
        ]);

        self::limitParameters($parameters, [
            'email',
            'firstName',
            'lastName',
            'properties',
            'subscribed',
        ]);

        return $this->client->put(
            self::buildUrl('/contactBooks/'.$contactBookId.'/contacts/'.$contactId),
            [
                RequestOptions::JSON => $parameters,
            ]
        );
    }

    /**
     * @see https://docs.unsend.dev/api-reference/contacts/delete-contact
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
     * @see https://docs.unsend.dev/api-reference/domains/get-domain
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
     * @see https://docs.unsend.dev/api-reference/domains/create-domain
     * @throws GuzzleException
     * @throws MissingArgumentException
     */
    public function createDomain(array $parameters): ResponseInterface
    {
        self::requireParameters($parameters, [
            'name',
            'region',
        ]);

        return $this->client->post(
            self::buildUrl('/domains'),
            [
                RequestOptions::JSON => $parameters,
            ]
        );
    }

    /**
     * @see https://docs.unsend.dev/api-reference/domains/verify-domain
     * @throws GuzzleException
     */
    public function verifyDomain(int $id): ResponseInterface
    {
        return $this->client->put(
            self::buildUrl('/domains/'.$id.'/verify')
        );
    }

    /**
     * Returns a full URL to the provided path using the relevant API version.
     * @param string $path
     * @return string
     */
    private static function buildUrl(string $path): string
    {
        return self::$apiBase.$path;
    }

    /**
     * @throws MissingArgumentException
     */
    private static function requireParameters(array $parameters = [], array $requiredParameters = []): void
    {
        foreach ($requiredParameters as $parameter) {
            if (! isset($parameters[$parameter])) {
                throw new MissingArgumentException('“'.$parameter.'” is required.');
            }
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    private static function limitParameters(array $parameters, array $supportedParameters = []): void
    {
        foreach ($parameters as $key => $value) {
            if (! in_array($key, $supportedParameters, true)) {
                throw new InvalidArgumentException('“'.$key.'” is not a valid argument.');
            }
        }
    }
}
