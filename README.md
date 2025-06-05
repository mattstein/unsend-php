# Unsend PHP Library

This is a barebones little thing for sending [Unsend](https://unsend.dev) messages. You might want to wait until it’s fleshed out a bit.

```php
// Initialize the Unsend client
$client = \Unsend\Client::create('your-api-key', 'https://app.unsend.dev');
$unsend = new \Unsend\Unsend($client);

// Send an email
$response = $unsend->sendEmail([
    'to' => 'hello@example.tld',
    'from' => 'reply@example.tld',
    'subject' => 'Library Test Email',
    'html' => '<p>This is a test!</p>',
    'text' => 'Heyo, this is a test!'
]);
```

See the [API Reference](https://docs.unsend.dev/api-reference/introduction) for expected usage.
