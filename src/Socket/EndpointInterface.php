<?php
declare(strict_types=1);

namespace Tale\Socket;

interface EndpointInterface
{
    /**
     * @return int
     */
    public function getAddressFamily(): int;

    public function isIpv4(): bool;
    public function isIpv6(): bool;
    public function isUnix(): bool;

    /**
     * @return string
     */
    public function getAddress(): string;

    /**
     * @return int
     */
    public function getPort(): int;
}