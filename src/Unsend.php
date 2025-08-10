<?php

namespace Unsend;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use Unsend\Exceptions\InvalidArgumentException;
use Unsend\Exceptions\MissingArgumentException;
use Unsend\Models\Response;
use Unsend\Exceptions\ApiException;

class Unsend
{
    private GuzzleClient $client;

    private static string $apiBase = '/api/v1';
    private const DEFAULT_PAGE_SIZE = 50;

    public function __construct(GuzzleClient $client)
    {
        $this->client = $client;
    }

    /**
     * Convenience factory to build the API client using API key and base URL.
     */
    /**
     * @param array{
     *     base_uri?: string,
     *     timeout?: float|int,
     *     connect_timeout?: float|int,
     *     headers?: array<string,string>
     * } $options
     */
    public static function create(string $apiKey, string $baseUrl = 'https://app.unsend.dev', array $options = []): self
    {
        return new self(Client::create($apiKey, $baseUrl, $options));
    }

    /**
     * Returns a single email record by ID.
     *
     * @see https://docs.unsend.dev/api-reference/emails/get-email
     *
     * @throws GuzzleException
     * @throws JsonException
     */
    public function getEmail(string $emailId): Response
    {
        return $this->sendRequest('GET', self::buildUrl('/emails/'.$emailId));
    }

    /**
     * Returns an array of email records, optionally filtered by parameters.
     *
     * @see https://docs.unsend.dev/api-reference/emails/list-emails
     *
     * @throws GuzzleException
     * @throws InvalidArgumentException
     * @throws JsonException
     * @param array{
     *     page?: int,
     *     limit?: int,
     *     startDate?: string,
     *     endDate?: string,
     *     domainId?: int
     * } $parameters
     */
    public function listEmails(array $parameters = []): Response
    {
        self::limitParameters($parameters, [
            'page',
            'limit',
            'startDate',
            'endDate',
            'domainId',
        ]);

        return $this->sendRequest('GET', self::buildUrl('/emails'), [
            'query' => $parameters,
        ]);
    }

    /**
     * Iterates all emails across pages, yielding each item.
     * Accepts the same filters as listEmails, plus optional 'limit' per page.
     *
     * @param array{
     *     page?: int,
     *     limit?: int,
     *     startDate?: string,
     *     endDate?: string,
     *     domainId?: int
     * } $parameters
     * @return \Generator<mixed>
     */
    public function iterateEmails(array $parameters = []): \Generator
    {
        $page = isset($parameters['page']) ? (int) $parameters['page'] : 1;
        $limit = isset($parameters['limit']) ? (int) $parameters['limit'] : self::DEFAULT_PAGE_SIZE;
        $baseParams = $parameters;

        while (true) {
            $response = $this->listEmails(array_merge($baseParams, [
                'page' => $page,
                'limit' => $limit,
            ]));

            $data = $response->getData();
            if (!isset($data->data) || !is_array($data->data) || count($data->data) === 0) {
                break;
            }

            foreach ($data->data as $item) {
                yield $item;
            }

            // Stop when we've exhausted pages
            if (isset($data->page, $data->total, $data->limit)) {
                $totalPages = (int) ceil(((int) $data->total) / ((int) $data->limit));
                if ($page >= $totalPages) {
                    break;
                }
            } elseif (count($data->data) < $limit) {
                break;
            }

            $page++;
        }
    }

    /**
     * Sends an email.
     *
     * @see https://docs.unsend.dev/api-reference/emails/send-email
     *
     * @throws GuzzleException
     * @throws MissingArgumentException
     * @throws InvalidArgumentException
     * @throws JsonException
     * @param array{
     *     to: string|list<string>,
     *     from: string,
     *     subject?: string,
     *     templateId?: string,
     *     variables?: array<string,mixed>,
     *     replyTo?: string,
     *     cc?: string|list<string>,
     *     bcc?: string|list<string>,
     *     text?: string,
     *     html?: string,
     *     attachments?: array<mixed>,
     *     scheduledAt?: string,
     *     inReplyToId?: string
     * } $parameters
     */
    public function sendEmail(array $parameters): Response
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

        return $this->sendRequest('POST', self::buildUrl('/emails'), [
            RequestOptions::JSON => $parameters,
        ]);
    }

    /**
     * Sends up to 100 emails in one request.
     *
     * @see https://docs.unsend.dev/api-reference/emails/batch-email
     *
     * @throws GuzzleException
     * @throws MissingArgumentException
     * @throws InvalidArgumentException
     * @throws JsonException
     * @param array<string,mixed> $parameters
     */
    public function batchEmail(array $parameters): Response
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

        return $this->sendRequest('POST', self::buildUrl('/emails/batch'), [
            RequestOptions::JSON => $parameters,
        ]);
    }

    /**
     * Updates the targeted send time for a scheduled email.
     *
     * @see https://docs.unsend.dev/api-reference/emails/update-schedule
     *
     * @throws GuzzleException
     * @throws JsonException
     */
    public function updateSchedule(string $emailId, string $scheduledAt): Response
    {
        return $this->sendRequest('PATCH', self::buildUrl('/emails/'.$emailId), [
            RequestOptions::JSON => [
                'scheduledAt' => $scheduledAt,
            ],
        ]);
    }

    /**
     * Cancels a scheduled email.
     *
     * @see https://docs.unsend.dev/api-reference/emails/cancel-schedule
     *
     * @throws GuzzleException
     * @throws JsonException
     */
    public function cancelSchedule(string $emailId): Response
    {
        return $this->sendRequest('POST', self::buildUrl('/emails/'.$emailId.'/cancel'));
    }

    /**
     * Returns a single contact record.
     *
     * @see https://docs.unsend.dev/api-reference/contacts/get-contact
     *
     * @throws GuzzleException
     * @throws JsonException
     */
    public function getContact(string $contactBookId, string $contactId): Response
    {
        return $this->sendRequest('GET', self::buildUrl('/contactBooks/'.$contactBookId.'/contacts/'.$contactId));
    }

    /**
     * Returns an array of contact records, optionally filtered by parameters.
     *
     * @see https://docs.unsend.dev/api-reference/contacts/get-contacts
     *
     * @throws GuzzleException
     * @throws InvalidArgumentException
     * @throws JsonException
     * @param array{
     *     emails?: string|list<string>,
     *     page?: int,
     *     limit?: int,
     *     ids?: string|list<string>
     * } $parameters
     */
    public function getContacts(string $contactBookId, array $parameters = []): Response
    {
        self::limitParameters($parameters, [
            'emails',
            'page',
            'limit',
            'ids',
        ]);

        return $this->sendRequest('GET', self::buildUrl('/contactBooks/'.$contactBookId.'/contacts'), [
            'query' => $parameters,
        ]);
    }

    /**
     * Creates a contact record.
     *
     * @see https://docs.unsend.dev/api-reference/contacts/create-contact
     *
     * @throws GuzzleException
     * @throws MissingArgumentException
     * @throws InvalidArgumentException
     * @throws JsonException
     * @param array{
     *     email: string,
     *     firstName?: string,
     *     lastName?: string,
     *     properties?: array<string,mixed>,
     *     subscribed?: bool
     * } $parameters
     */
    public function createContact(string $contactBookId, array $parameters): Response
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

        return $this->sendRequest('POST', self::buildUrl('/contactBooks/'.$contactBookId.'/contacts'), [
            RequestOptions::JSON => $parameters,
        ]);
    }

    /**
        * Updates a contact record.
        *
        * @see https://docs.unsend.dev/api-reference/contacts/update-contact
        *
        * @throws GuzzleException
        * @throws InvalidArgumentException
        * @throws JsonException
     * @param array{
     *     firstName?: string,
     *     lastName?: string,
     *     properties?: array<string,mixed>,
     *     subscribed?: bool
     * } $parameters
     */
    public function updateContact(string $contactBookId, string $contactId, array $parameters = []): Response
    {
        self::limitParameters($parameters, [
            'firstName',
            'lastName',
            'properties',
            'subscribed',
        ]);

        return $this->sendRequest('PATCH', self::buildUrl('/contactBooks/'.$contactBookId.'/contacts/'.$contactId), [
            RequestOptions::JSON => $parameters,
        ]);
    }

    /**
     * Upserts a contact record.
     *
     * @see https://docs.unsend.dev/api-reference/contacts/upsert-contact
     *
     * @throws GuzzleException
     * @throws MissingArgumentException
     * @throws InvalidArgumentException
     * @throws JsonException
     * @param array{
     *     email: string,
     *     firstName?: string,
     *     lastName?: string,
     *     properties?: array<string,mixed>,
     *     subscribed?: bool
     * } $parameters
     */
    public function upsertContact(string $contactBookId, string $contactId, array $parameters): Response
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

        return $this->sendRequest('PUT', self::buildUrl('/contactBooks/'.$contactBookId.'/contacts/'.$contactId), [
            RequestOptions::JSON => $parameters,
        ]);
    }

    /**
     * Deletes a contact record.
     *
     * @see https://docs.unsend.dev/api-reference/contacts/delete-contact
     *
     * @throws GuzzleException
     * @throws JsonException
     */
    public function deleteContact(string $contactBookId, string $contactId): Response
    {
        return $this->sendRequest('DELETE', self::buildUrl('/contactBooks/'.$contactBookId.'/contacts/'.$contactId));
    }

    /**
     * Returns a single domain record.
     *
     * @see https://docs.unsend.dev/api-reference/domains/get-domain
     *
     * @todo figure out why this returns 404
     *
     * @throws GuzzleException
     * @throws JsonException
     */
    public function getDomain(int $id): Response
    {
        return $this->sendRequest('GET', self::buildUrl('/domains/'.$id));
    }

    /**
     * Returns an array of domain records.
     *
     * @throws GuzzleException
     * @throws JsonException
     */
    public function getDomains(): Response
    {
        return $this->sendRequest('GET', self::buildUrl('/domains'));
    }

    /**
     * Creates a domain record.
     *
     * @see https://docs.unsend.dev/api-reference/domains/create-domain
     *
     * @throws GuzzleException
     * @throws MissingArgumentException
     * @throws JsonException
     * @param array{
     *     name: string,
     *     region: string
     * } $parameters
     */
    public function createDomain(array $parameters): Response
    {
        self::requireParameters($parameters, [
            'name',
            'region',
        ]);

        return $this->sendRequest('POST', self::buildUrl('/domains'), [
            RequestOptions::JSON => $parameters,
        ]);
    }

    /**
     * Attempts to verify a domain.
     *
     * @see https://docs.unsend.dev/api-reference/domains/verify-domain
     *
     * @throws GuzzleException
     * @throws JsonException
     */
    public function verifyDomain(int $id): Response
    {
        return $this->sendRequest('PUT', self::buildUrl('/domains/'.$id.'/verify'));
    }

    // Removed unused getResponseData()

    /**
     * Returns a full URL to the provided path using the relevant API version.
     */
    private static function buildUrl(string $path): string
    {
        return self::$apiBase.$path;
    }

    /**
     * Throws an exception if required keys are not present in provided arguments.
     *
     * @throws MissingArgumentException
     * @param array<string,mixed> $parameters
     * @param array<int,string> $requiredParameters
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
     * Throws an exception if an unexpected key is present in provided arguments.
     *
     * @throws InvalidArgumentException
     * @param array<string,mixed> $parameters
     * @param array<int,string> $supportedParameters
     */
    private static function limitParameters(array $parameters, array $supportedParameters = []): void
    {
        foreach ($parameters as $key => $value) {
            if (! in_array($key, $supportedParameters, true)) {
                throw new InvalidArgumentException('“'.$key.'” is not a valid argument.');
            }
        }
    }

    /**
     * Sends an HTTP request and returns a wrapped Response, throwing ApiException on non-2xx.
     *
     * @throws ApiException
     * @throws GuzzleException
     * @throws JsonException
     * @param array<string,mixed> $options
     */
    private function sendRequest(string $method, string $uri, array $options = []): Response
    {
        // Only set Content-Type when JSON body is present
        if (isset($options[RequestOptions::JSON])) {
            $options['headers']['Content-Type'] = 'application/json';
        }

        try {
            $response = $this->client->request($method, $uri, $options);
        } catch (\GuzzleHttp\Exception\ClientException|\GuzzleHttp\Exception\ServerException $e) {
            // For these exception types, a response is always available
            throw ApiException::fromResponse($e->getResponse());
        }

        return Response::create($response);
    }
}
