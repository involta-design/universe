<?php

declare(strict_types=1);

namespace Involta\Universe;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

interface Universe
{
    public function __construct(ContainerInterface $container);

    public function setUsername(string $username): Universe;

    public function setPassword(string $password): Universe;

    public function setHttpAuthorisation(string $httpAuthorization): Universe;

    public function setUrl(string $url): Universe;

    public function setTokenFile(string $tokenFile): Universe;
}
