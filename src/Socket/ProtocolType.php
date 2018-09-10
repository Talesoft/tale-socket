<?php
declare(strict_types=1);

namespace Tale\Socket;

class ProtocolType
{
    public const TCP = \SOL_TCP;
    public const UDP = \SOL_UDP;
    public const ICMP = 58;

    private function __construct() {}

    public static function fromName(string $name): int
    {
        return getprotobyname($name);
    }
}