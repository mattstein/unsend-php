# Unsend PHP Library

This is a barebones little thing for sending [Unsend](https://unsend.dev) messages. You might want to wait until it’s fleshed out a bit.

```php
$client = \Unsend\Client::create('api-key', 'self-hosted-url');
$unsend = new \Unsend\Unsend($client);
$response = $unsend->sendEmail([
    'to' => 'hello@example.tld',
    'from' => 'reply@example.tld',
    'subject' => 'Library Test Email',
    'html' => '<p>This is a test!</p>',
    'text' => 'Heyo, this is a test!'
]);
```
