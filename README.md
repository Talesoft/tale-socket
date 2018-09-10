
[![Packagist](https://img.shields.io/packagist/v/talesoft/tale-socket.svg?style=for-the-badge)](https://packagist.org/packages/talesoft/tale-socket)
[![License](https://img.shields.io/github/license/Talesoft/tale-socket.svg?style=for-the-badge)](https://github.com/Talesoft/tale-socket/blob/master/LICENSE.md)
[![CI](https://img.shields.io/travis/Talesoft/tale-socket.svg?style=for-the-badge)](https://travis-ci.org/Talesoft/tale-socket)
[![Coverage](https://img.shields.io/codeclimate/coverage/Talesoft/tale-socket.svg?style=for-the-badge)](https://codeclimate.com/github/Talesoft/tale-socket)

Tale Socket
===============

What is Tale Collection?
------------------------

A simple socket implementation using the PHP socket extension.

Installation
------------

```bash
composer require talesoft/tale-socket
```

Usage
-----

```php
use Tale\Socket\Factory;

$factory = new Factory();

$socket = $factory->connectFromUrlString('tcp://google.com:80');

$socket->send("GET / HTTP/1.1\r\nHost: google.com\r\nConnection: close\r\n\r\n");

while (($data = $socket->read(1024)) !== '') {
    echo $data;
}
```