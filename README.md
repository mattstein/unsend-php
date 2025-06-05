# Unsend PHP Library

This is a small, unofficial SDK for [Unsend](https://unsend.dev).

## Installation & Usage

Install via Composer:

```
composer require composer require mattstein/unsend-php
```

Create a client instance with your API key and specify your Unsend domain if you self host:

```php
// Initialize the Unsend client
$client = \Unsend\Client::create('your-api-key', 'https://app.unsend.dev');
$unsend = new \Unsend\Unsend($client);
```

You can then use any of the included methods to interact with the API, where `getData()` provides decoded response data and `getResponseObject()` provides the entire Guzzle response:

```php
// Send an email
$response = $unsend->sendEmail([
    'to' => 'hello@example.tld',
    'from' => 'reply@example.tld',
    'subject' => 'Library Test Email',
    'html' => '<p>This is a test!</p>',
    'text' => 'This is a test!'
]);

// Print email ID
echo $response->getData()->emailId;
```

## Methods

Available methods follow the [API Reference](https://docs.unsend.dev/api-reference/introduction).

### `getEmail(string $emailId)`

Returns a single email record by ID.

### `listEmails(array $parameters = [])`

Returns an array of email records, optionally filtered by parameters.

### `sendEmail(array $parameters)`

Sends an email.

### `batchEmail(array $parameters)`

Sends up to 100 emails in one request.

### `updateSchedule(string $emailId, string $scheduledAt)`

Updates the targeted send time for a scheduled email.

### `cancelSchedule(string $emailId)`

Cancels a scheduled email.

### `getContact(string $contactBookId, string $contactId)`

Returns a single contact record.

### `getContacts(string $contactBookId, array $parameters = [])`

Returns an array of contact records, optionally filtered by parameters.

### `createContact(string $contactBookId, array $parameters)`

Creates a contact record.

### `updateContact(string $contactBookId, string $contactId, array $parameters = [])`

Updates a contact record.

### `upsertContact(string $contactBookId, string $contactId, array $parameters = [])`

Upserts a contact record.

### `deleteContact(string $contactBookId, string $contactId)`

Deletes a contact record.

### `getDomain(int $id)`

Returns a single domain record.

### `getDomains()`

Returns an array of domain records.

### `createDomain(array $parameters)`

Creates a domain record.

### `verifyDomain(int $id)`

Attempts to verify a domain.
