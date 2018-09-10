<?php
declare(strict_types=1);

namespace Tale;

use Tale\Socket\AddressFamily;
use Tale\Socket\Endpoint;
use Tale\Socket\EndpointInterface;
use Tale\Socket\Exception;

class Socket implements SocketInterface
{
    private $resource;

    public function __construct($resource)
    {
        if (!\is_resource($resource) || get_resource_type($resource) !== 'Socket') {
            throw new \InvalidArgumentException(sprintf(
                'Passed argument needs to be a resource of type "Socket", %s given',
                \gettype($resource)
            ));
        }

        $this->resource = $resource;
    }

    public function __destruct()
    {
        $this->close();
    }

    public function close(): void
    {
        if (!\is_resource($this->resource)) {
            return;
        }
        $this->shutdown();
        socket_close($this->resource);
    }

    public function shutdown(int $how = 2): void
    {
        if (!\is_resource($this->resource)) {
            return;
        }
        socket_shutdown($this->resource, $how);
    }

    public function bind(EndpointInterface $endpoint): void
    {
        if (!\is_resource($this->resource)) {
            throw new Exception('Failed to bind: Socket closed');
        }

        if (!@socket_bind($this->resource, $endpoint->getAddress(), $endpoint->getPort())) {
            throw Exception::fromResource($this->resource, 'Failed to bind socket');
        }
    }

    public function connect(EndpointInterface $endpoint): void
    {
        if (!\is_resource($this->resource)) {
            throw new Exception('Failed to connect: Socket closed');
        }

        if (!@socket_connect($this->resource, $endpoint->getAddress(), $endpoint->getPort())) {
            throw Exception::fromResource($this->resource, 'Failed to connect socket');
        }
    }

    public function listen(int $backLog = 512): void
    {
        if (!\is_resource($this->resource)) {
            throw new Exception('Failed to listen: Socket closed');
        }

        if (!@socket_listen($this->resource, $backLog)) {
            throw Exception::fromResource($this->resource, 'Failed to let socket listen');
        }
    }

    public function accept(): SocketInterface
    {
        if (!\is_resource($this->resource)) {
            throw new Exception('Failed to accept: Socket closed');
        }

        if (($socket = @socket_accept($this->resource)) === false) {
            throw Exception::fromResource($this->resource, 'Failed to accept socket');
        }
        return new Socket($socket);
    }

    public function getLocalEndpoint(): EndpointInterface
    {
        if (!\is_resource($this->resource)) {
            throw new Exception('Failed to get local endpoint: Socket closed');
        }

        if (!@socket_getsockname($this->resource, $address, $port)) {
            throw Exception::fromResource($this->resource);
        }
        return $this->buildEndpoint($address, $port);
    }

    public function getRemoteEndpoint(): EndpointInterface
    {
        if (!\is_resource($this->resource)) {
            throw new Exception('Failed to get remote endpoint: Socket closed');
        }

        if (!@socket_getpeername($this->resource, $address, $port)) {
            throw Exception::fromResource($this->resource);
        }
        return $this->buildEndpoint($address, $port);
    }

    public function getOption(int $level, int $option)
    {
        if (!\is_resource($this->resource)) {
            throw new Exception('Failed to get option: Socket closed');
        }

        if (($value = @socket_get_option($this->resource, $level, $option)) === false) {
            throw Exception::fromResource($this->resource);
        }
        return $value;
    }

    public function setOption(int $level, int $option, $value): void
    {
        if (!\is_resource($this->resource)) {
            throw new Exception('Failed to set option: Socket closed');
        }

        if (!@socket_set_option($this->resource, $level, $option, $value)) {
            throw Exception::fromResource($this->resource);
        }
    }

    public function getCommunicationType(): int
    {
        return $this->getOption(\SOL_SOCKET, \SO_TYPE);
    }

    public function receive(int $length): string
    {
        if (!\is_resource($this->resource)) {
            throw new Exception('Failed to receive data: Socket closed');
        }

        if (($data = socket_read($this->resource, $length, \PHP_BINARY_READ)) === false) {
            throw Exception::fromResource($this->resource);
        }
        return $data;
    }

    public function send(string $data): int
    {
        if (!\is_resource($this->resource)) {
            throw new Exception('Failed to send data: Socket closed');
        }

        if (($length = socket_write($this->resource, $data, \strlen($data))) === false) {
            throw Exception::fromResource($this->resource);
        }
        return $length;
    }

    private function buildEndpoint(string $address, int $port): EndpointInterface
    {
        if (strpos($address, '/') !== false) {
            return new Endpoint(AddressFamily::UNIX, $address);
        }
        if (strpos($address, ':') !== false) {
            return new Endpoint(AddressFamily::INET6, $address, $port);
        }
        return new Endpoint(AddressFamily::INET, $address, $port);
    }
}