# Symfony2 Redis Session Handler

This is a [fork of Baachi's work](https://github.com/Baachi/symfony/blob/redis-session-storage/src/Symfony/Component/HttpFoundation/Session/Storage/Handler/RedisSessionHandler.php)

## Use

```php
use Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler;

$sessionTimeout = 60 * 60 * 24 * 7; // 1 week
$redisClient = new \Redis('localhost');

new RedisSessionHandler($redisClient, $sessionTimeout);
```