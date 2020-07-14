<?php

declare(strict_types=1);

namespace Involta\Universe;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;

interface RequestFactory
{
    public function build(string $method, string $url): RequestInterface;
    public function createStream($data): StreamInterface;
}
