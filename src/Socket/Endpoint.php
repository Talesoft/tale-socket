<?php
declare(strict_types=1);

namespace Tale\Socket;

class Endpoint implements EndpointInterface
{

    /**
     * @var int
     */
    private $addressFamily;

    /**
     * @var string
     */
    private $address;

    /**
     * @var int
     */
    private $port;

    /**
     * Endpoint constructor.
     * @param int $addressFamily
     * @param string $address
     * @param int $port
     */
    public function __construct(int $addressFamily, string $address, int $port = 0)
    {
        $this->addressFamily = $addressFamily;
        $this->address = $this->validateAddress($address);
        $this->port = $port;
    }

    /**
     * @return int
     */
    public function getAddressFamily(): int
    {
        return $this->addressFamily;
    }

    public function isIpv4(): bool
    {
        return $this->addressFamily === AddressFamily::INET;
    }

    public function isIpv6(): bool
    {
        return $this->addressFamily === AddressFamily::INET6;
    }

    public function isUnix(): bool
    {
        return $this->addressFamily === AddressFamily::UNIX;
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    public function __toString()
    {
        switch ($this->addressFamily) {
            case AddressFamily::INET:
                return "{$this->address}:{$this->port}";
            case AddressFamily::INET6:
                return "[{$this->address}]:{$this->port}";
        }
        return $this->address;
    }

    private function validateAddress(string $address): string
    {
        if ($this->addressFamily === AddressFamily::UNIX) {
            return $address;
        }
        $hostname = null;
        $flag = $this->addressFamily === AddressFamily::INET ? FILTER_FLAG_IPV4 : FILTER_FLAG_IPV6;
        if (!filter_var($address, $flag | FILTER_FLAG_IPV4)) {
            $hostname = $address;
        }

        if ($hostname) {
            $address = gethostbyname($hostname);
            if ($hostname === $address) {
                throw new \InvalidArgumentException("Failed to resolve address {$address}");
            }
        }

        return $address;
    }

    public static function fromString(string $endpointString): self
    {
        if ($endpointString === '') {
            return new self(AddressFamily::INET, '0.0.0.0');
        }

        if (strpos($endpointString, '/') !== false) {
            return new self(AddressFamily::UNIX, $endpointString);
        }

        if (strpos($endpointString, ':') === false) {
            return new self(AddressFamily::INET, $endpointString);
        }

        if ($endpointString[0] === '[') {
            [$address, $port] = explode(']', $endpointString);
            if (\strlen($port) > 0) {
                if ($port[0] !== ':') {
                    throw new \InvalidArgumentException('Malformed ipv6: Expected : for port');
                }
                $port = (int)ltrim($port, ':');
            } else {
                $port = 0;
            }
            return new self(AddressFamily::INET6, ltrim($address, '['), $port);
        }

        [$address, $port] = explode(':', $endpointString);
        return new self(AddressFamily::INET, $address, (int)$port);
    }
}