<?php
declare(strict_types=1);

namespace Tale;

use Tale\Socket\EndpointInterface;

interface SocketInterface
{
    public function bind(EndpointInterface $endpoint): void;
    public function connect(EndpointInterface $endpoint): void;
    public function listen(int $backLog = 512): void;
    public function shutdown(int $how = 2): void;
    public function close(): void;
    public function accept(): SocketInterface;
    public function getLocalEndpoint(): EndpointInterface;
    public function getRemoteEndpoint(): EndpointInterface;
    public function getOption(int $level, int $option);
    public function setOption(int $level, int $option, $value): void;
    public function getCommunicationType(): int;
    public function receive(int $length): string;
    public function send(string $data): int;
}