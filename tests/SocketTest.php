<?php
declare(strict_types=1);

namespace Tale\Collection;

use PHPUnit\Framework\TestCase;
use Tale\Socket\Endpoint;
use Tale\Socket\Factory;

/**
 * @coversDefaultClass \Tale\Socket
 */
class SocketTest extends TestCase
{
    public function testConstruct(): void
    {
        $factory = new Factory();
        $socket = $factory->createFromScheme('tcp');
        $ep = Endpoint::fromString('google.de:80');
        var_dump($ep);
        $socket->connect($ep);
        var_dump('Connected');
        $socket->send("GET / HTTP/1.1\r\nHost: google.de\r\nConnection: close\r\n\r\n");
        var_dump('Sent');
        while (($data = $socket->receive(1024)) !== '') {
            var_dump("Received [$data]");
        }
    }
}
