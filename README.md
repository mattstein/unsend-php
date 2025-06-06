# Unsend PHP Library

This is a small, unofficial SDK for [Unsend](https://unsend.dev).

## Installation & Usage

Install via Composer:

```
composer require mattstein/unsend-php
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

```php
$response = $unsend->getEmail('cxbkzjgku000xmw2tg7lndauk');

echo $response->getData()->subject;
```

### `listEmails(array $parameters = [])`

Returns an array of email records, optionally filtered by parameters.

```php
$response = $unsend->listEmails([
    'domainId' => 3,
]);

foreach ($response->getData()->data as $email) {
    echo $email->subject . "\n";
}
```

### `sendEmail(array $parameters)`

Sends an email.

```php
$response = $unsend->sendEmail([
    'to' => 'hello@example.tld',
    'from' => 'reply@example.tld',
    'subject' => 'Library Test Email',
    'html' => '<p>This is a test!</p>',
    'text' => 'This is a test!'
]);

echo $response->getData()->emailId;
```

### `batchEmail(array $parameters)`

Sends up to 100 emails in one request.

### `updateSchedule(string $emailId, string $scheduledAt)`

Updates the targeted send time for a scheduled email.

```php
$response = $unsend->sendEmail([
    'to' => 'hello@example.tld',
    'from' => 'reply@example.tld',
    'subject' => 'Library Test Email',
    'html' => '<p>This is a test!</p>',
    'text' => 'This is a test!',
    'scheduledAt' => '2025-06-10T00:00:00Z'
]);

$scheduledEmailId = $response->getData()->emailId;

$unsend->updateSchedule($scheduledEmailId, '2025-06-07T00:00:00Z');
```

### `cancelSchedule(string $emailId)`

Cancels a scheduled email.

```php
$unsend->cancelSchedule('cxbkzjgku000xmw2tg7lndauk');
```

### `getContact(string $contactBookId, string $contactId)`

Returns a single contact record.

```php
$response = $unsend->getContact(
    'cxb19a523000foa3ctrd5h7u7',
    'cxb19bmdv000hoa3c3jfpx51t'
);

echo $response->getData()->email;
```

### `getContacts(string $contactBookId, array $parameters = [])`

Returns an array of contact records, optionally filtered by parameters.

```php
$response = $unsend->getContacts('cxb19a523000foa3ctrd5h7u7');

foreach ($response->getData() as $contact) {
    echo $contact->email . "\n";
}
```

### `createContact(string $contactBookId, array $parameters)`

Creates a contact record.

```php
$response = $unsend->createContact(
    'cxb19a523000foa3ctrd5h7u7',
    [
        'email' => 'gobiasindustries@example.com',
        'firstName' => 'Tobias',
        'lastName' => 'Fünke',
        'subscribed' => true,
    ]
);

echo $response->getData()->contactId;
```

### `updateContact(string $contactBookId, string $contactId, array $parameters = [])`

Updates a contact record.

```php
$unsend->updateContact(
    'cxb19a523000foa3ctrd5h7u7',
    'cxb19bmdv000hoa3c3jfpx51t',
    [
        'firstName' => 'Surely',
        'lastName' => 'Fünke',
    ]
);
```

### `upsertContact(string $contactBookId, string $contactId, array $parameters = [])`

Upserts a contact record.

### `deleteContact(string $contactBookId, string $contactId)`

Deletes a contact record.

```php
$unsend->deleteContact('cxb19a523000foa3ctrd5h7u7', 'cxb19bmdv000hoa3c3jfpx51t');
```

### `getDomain(int $id)`

Returns a single domain record.

### `getDomains()`

Returns an array of domain records.

```php
$response = $unsend->getDomains();

foreach ($response->getData() as $domain) {
    echo $domain->name . "\n";
}
```

### `createDomain(array $parameters)`

Creates a domain record.

```php
$response = $unsend->createDomain(
    [
        'name' => 'example.com',
        'region' => 'us-east-1',
    ]
);

echo $response->getData()->id;
```

### `verifyDomain(int $id)`

Attempts to verify a domain.

```php
$response = $unsend->verifyDomain(5);

echo $response->getData()->message;
```
