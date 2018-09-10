<?php
declare(strict_types=1);

namespace Tale\Socket;

use Tale\Socket;
use Tale\SocketInterface;

class Factory
{
    private $schemes = [
        'tcp' => [AddressFamily::INET, CommunicationType::STREAM, ProtocolType::TCP],
        'tcp6' => [AddressFamily::INET6, CommunicationType::STREAM, ProtocolType::TCP],
        'udp' => [AddressFamily::INET, CommunicationType::DATAGRAM, ProtocolType::UDP],
        'udp6' => [AddressFamily::INET6, CommunicationType::DATAGRAM, ProtocolType::UDP],
        'icmp' => [AddressFamily::INET, CommunicationType::RAW, ProtocolType::ICMP],
        'icmp6' => [AddressFamily::INET6, CommunicationType::RAW, ProtocolType::ICMP],
        'unix' => [AddressFamily::UNIX, CommunicationType::STREAM, 0],
        'udg' => [AddressFamily::UNIX, CommunicationType::DATAGRAM, 0]
    ];

    /**
     * @return array
     */
    public function getSchemes(): array
    {
        return $this->schemes;
    }

    public function registerScheme(string $scheme, int $addressFamily, int $communicationType, int $protocolType): void
    {
        $this->schemes[$scheme] = [$addressFamily, $communicationType, $protocolType];
    }

    public function unregisterScheme(string $scheme): void
    {
        unset($this->schemes[$scheme]);
    }

    /**
     * @param int $addressFamily
     * @param int $communicationType
     * @param int $protocolType
     * @return SocketInterface
     */
    public function create(int $addressFamily, int $communicationType, int $protocolType): SocketInterface
    {
        $socket = @socket_create($addressFamily, $communicationType, $protocolType);
        if (!\is_resource($socket)) {
            throw Exception::fromGlobals('Failed to create socket');
        }
        return new Socket($socket);
    }

    public function createFromScheme(string $scheme): SocketInterface
    {
        if (!isset($this->schemes[$scheme])) {
            throw new Exception("Failed to create socket: Unknown scheme {$scheme}");
        }
        return $this->create(...$this->schemes[$scheme]);
    }

    public function connectFromUrlString(string $urlString, array $bindEndpoints = null): SocketInterface
    {
        [$socket, $endpoint] = $this->createFromUrlString($urlString);
        if ($bindEndpoints) {
            foreach ($bindEndpoints as $endpoint) {
                $socket->bind($endpoint);
            }
        }
        $socket->connect($endpoint);
        return $socket;
    }

    public function listenFromUrlString(string $urlString, int $backLog = 512): SocketInterface
    {
        [$socket, $endpoint] = $this->createFromUrlString($urlString);
        $socket->bind($endpoint);
        $socket->listen($backLog);
        return $socket;
    }

    private function createFromUrlString(string $urlString): array
    {
        ['scheme' => $scheme, 'host' => $host, 'port' => $port, 'path' => $path] = array_replace([
            'scheme' => 'tcp',
            'host' => null,
            'port' => 0,
            'path' => null
        ], parse_url($urlString));

        $endpoint = $host === null
            ? Endpoint::fromString($path)
            : Endpoint::fromString("{$host}:{$port}");

        if (\in_array($scheme, ['tcp', 'udp', 'icmp'], true) && $endpoint->isIpv6()) {
            $scheme .= '6';
        } else if ($endpoint->isUnix() && !\in_array($scheme, ['unix', 'udg'], true)) {
            $scheme = 'unix';
        }
        if (!isset($this->schemes[$scheme])) {
            throw new Exception("Failed to create socket: Unknown scheme {$scheme}");
        }
        $socket = $this->create(...$this->schemes[$scheme]);
        return [$socket, $endpoint];
    }
}