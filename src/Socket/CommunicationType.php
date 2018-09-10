<?php
declare(strict_types=1);

namespace Tale\Socket;

class CommunicationType
{
    public const STREAM = \SOCK_STREAM;
    public const DATAGRAM = \SOCK_DGRAM;
    public const SEQUENTIAL_PACKET = \SOCK_SEQPACKET;
    public const RAW = \SOCK_RAW;
    public const RDM = \SOCK_RDM;

    private function __construct() {}
}