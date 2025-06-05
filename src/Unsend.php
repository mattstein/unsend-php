<?php

namespace Unsend;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use JsonException;
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
     *
     * @throws GuzzleException
     * @throws JsonException
     */
    public function getEmail(string $emailId)
    {
        $response = $this->client->get(
            self::buildUrl('/emails/'.$emailId)
        );

        return $this->prepareResponse($response);
    }

    /**
     * @see https://docs.unsend.dev/api-reference/emails/list-emails
     *
     * @todo figure out why this returns 404
     *
     * @throws GuzzleException
     * @throws InvalidArgumentException
     * @throws JsonException
     */
    public function listEmails(array $parameters = [])
    {
        self::limitParameters($parameters, [
            'page',
            'limit',
            'startDate',
            'endDate',
            'domainId',
        ]);

        $response = $this->client->get(
            self::buildUrl('/emails'),
            [
                'query' => $parameters,
            ]
        );

        return $this->prepareResponse($response);
    }

    /**
     * @see https://docs.unsend.dev/api-reference/emails/send-email
     *
     * @throws GuzzleException
     * @throws MissingArgumentException
     * @throws InvalidArgumentException
     * @throws JsonException
     */
    public function sendEmail(array $parameters)
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

        $response = $this->client->post(
            self::buildUrl('/emails'),
            [
                RequestOptions::JSON => $parameters,
            ]
        );

        return $this->prepareResponse($response);
    }

    /**
     * @see https://docs.unsend.dev/api-reference/emails/batch-email
     *
     * @throws GuzzleException
     * @throws MissingArgumentException
     * @throws InvalidArgumentException
     * @throws JsonException
     */
    public function batchEmail(array $parameters)
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

        $response = $this->client->post(
            self::buildUrl('/emails/batch'),
            [
                RequestOptions::JSON => $parameters,
            ]
        );

        return $this->prepareResponse($response);
    }

    /**
     * @see https://docs.unsend.dev/api-reference/emails/update-schedule
     *
     * @throws GuzzleException
     * @throws JsonException
     */
    public function updateSchedule(string $emailId, string $scheduledAt)
    {
        $response = $this->client->patch(
            self::buildUrl('/emails/'.$emailId),
            [
                'scheduledAt' => $scheduledAt,
            ]
        );

        return $this->prepareResponse($response);
    }

    /**
     * @see https://docs.unsend.dev/api-reference/emails/cancel-schedule
     *
     * @throws GuzzleException
     * @throws JsonException
     */
    public function cancelSchedule(string $emailId)
    {
        $response = $this->client->post(
            self::buildUrl('/emails/'.$emailId.'/cancel')
        );

        return $this->prepareResponse($response);
    }

    /**
     * @see https://docs.unsend.dev/api-reference/contacts/get-contact
     *
     * @throws GuzzleException
     * @throws JsonException
     */
    public function getContact(string $contactBookId, string $contactId)
    {
        $response = $this->client->get(
            self::buildUrl('/contactBooks/'.$contactBookId.'/contacts/'.$contactId)
        );

        return $this->prepareResponse($response);
    }

    /**
     * @see https://docs.unsend.dev/api-reference/contacts/get-contacts
     *
     * @throws GuzzleException
     * @throws InvalidArgumentException
     * @throws JsonException
     */
    public function getContacts(string $contactBookId, array $parameters = [])
    {
        self::limitParameters($parameters, [
            'emails',
            'page',
            'limit',
            'ids',
        ]);

        $response = $this->client->get(
            self::buildUrl('/contactBooks/'.$contactBookId.'/contacts'),
            [
                'query' => $parameters,
            ]
        );

        return $this->prepareResponse($response);
    }

    /**
     * @see https://docs.unsend.dev/api-reference/contacts/create-contact
     *
     * @throws GuzzleException
     * @throws MissingArgumentException
     * @throws InvalidArgumentException
     * @throws JsonException
     */
    public function createContact(string $contactBookId, array $parameters)
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

        $response = $this->client->post(
            self::buildUrl('/contactBooks/'.$contactBookId.'/contacts'),
            [
                RequestOptions::JSON => $parameters,
            ]
        );

        return $this->prepareResponse($response);
    }

    /**
     * @see https://docs.unsend.dev/api-reference/contacts/update-contact
     *
     * @throws GuzzleException
     * @throws InvalidArgumentException
     * @throws JsonException
     */
    public function updateContact(string $contactBookId, string $contactId, array $parameters = [])
    {
        self::limitParameters($parameters, [
            'firstName',
            'lastName',
            'properties',
            'subscribed',
        ]);

        $response = $this->client->patch(
            self::buildUrl('/contactBooks/'.$contactBookId.'/contacts/'.$contactId),
            [
                RequestOptions::JSON => $parameters,
            ]
        );

        return $this->prepareResponse($response);
    }

    /**
     * @see https://docs.unsend.dev/api-reference/contacts/upsert-contact
     *
     * @throws GuzzleException
     * @throws MissingArgumentException
     * @throws InvalidArgumentException
     * @throws JsonException
     */
    public function upsertContact(string $contactBookId, string $contactId, array $parameters = [])
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

        $response = $this->client->put(
            self::buildUrl('/contactBooks/'.$contactBookId.'/contacts/'.$contactId),
            [
                RequestOptions::JSON => $parameters,
            ]
        );

        return $this->prepareResponse($response);
    }

    /**
     * @see https://docs.unsend.dev/api-reference/contacts/delete-contact
     *
     * @throws GuzzleException
     * @throws JsonException
     */
    public function deleteContact(string $contactBookId, string $contactId)
    {
        $response = $this->client->delete(
            self::buildUrl('/contactBooks/'.$contactBookId.'/contacts/'.$contactId)
        );

        return $this->prepareResponse($response);
    }

    /**
     * @see https://docs.unsend.dev/api-reference/domains/get-domain
     *
     * @todo figure out why this returns 404
     *
     * @throws GuzzleException
     * @throws JsonException
     */
    public function getDomain(int $id)
    {
        $response = $this->client->get(
            self::buildUrl('/domains/'.$id)
        );

        return $this->prepareResponse($response);
    }

    /**
     * @throws GuzzleException
     * @throws JsonException
     */
    public function getDomains()
    {
        $response = $this->client->get(
            self::buildUrl('/domains')
        );

        return $this->prepareResponse($response);
    }

    /**
     * @see https://docs.unsend.dev/api-reference/domains/create-domain
     *
     * @throws GuzzleException
     * @throws MissingArgumentException
     * @throws JsonException
     */
    public function createDomain(array $parameters)
    {
        self::requireParameters($parameters, [
            'name',
            'region',
        ]);

        $response = $this->client->post(
            self::buildUrl('/domains'),
            [
                RequestOptions::JSON => $parameters,
            ]
        );

        return $this->prepareResponse($response);
    }

    /**
     * @see https://docs.unsend.dev/api-reference/domains/verify-domain
     *
     * @throws GuzzleException
     * @throws JsonException
     */
    public function verifyDomain(int $id)
    {
        $response = $this->client->put(
            self::buildUrl('/domains/'.$id.'/verify')
        );

        return $this->prepareResponse($response);
    }

    /**
     * @throws JsonException
     */
    private function prepareResponse(ResponseInterface $response)
    {
        return json_decode($response->getBody(), false, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Returns a full URL to the provided path using the relevant API version.
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
