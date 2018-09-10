<?php
declare(strict_types=1);

namespace Tale\Socket;

class AddressFamily
{
    public const INET = \AF_INET;
    public const INET6 = \AF_INET6;
    public const UNIX = \AF_UNIX;

    private function __construct() {}
}