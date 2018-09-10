<?php

namespace Tale\Socket;

use RuntimeException;

/**
 * I took parts of this from here:
 * https://raw.githubusercontent.com/clue/php-socket-raw/master/src/Exception.php
 *
 * Generally, an exceptionally well written library, I almost would've used it, but:
 * - Re-implementing helps understanding
 * - I want it to link harder to PSR streams
 */
class Exception extends RuntimeException
{
    public static function fromCode($code, $messagePrefix = 'Socket error'): self
    {
        return new self($messagePrefix . ': ' . self::getErrorMessage($code), $code);
    }

    public static function fromResource($resource, $messagePrefix = 'Socket operation failed'): self
    {
        $code = socket_last_error($resource);
        socket_clear_error($resource);
        return self::fromCode($code, $messagePrefix);
    }

    public static function fromGlobals($messagePrefix = 'Socket operation failed'): self
    {
        $code = socket_last_error();
        socket_clear_error();
        return self::fromCode($code, $messagePrefix);
    }

    protected static function getErrorMessage($code)
    {
        $string = socket_strerror($code);
        foreach (get_defined_constants() as $key => $value) {
            if($value === $code && strpos($key, 'SOCKET_') === 0) {
                $string .= ' (' . $key . ')';
                break;
            }
        }
        return $string;
    }
}
