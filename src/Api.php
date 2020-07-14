<?php

declare(strict_types=1);

namespace Involta\Universe;

use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Api implements Universe
{
    use Methods;

    private $container;
    private $username;
    private $password;
    private $url;
    private $httpAuthorization;
    private $token;

    private const AUTH_ERROR_CODE = 101;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function setHttpAuthorisation(string $httpAuthorization): Universe
    {
        $this->httpAuthorization = $httpAuthorization;
        return clone $this;
    }

    public function setUsername(string $username): Universe
    {
        $this->username = $username;
        return clone $this;
    }

    public function setUrl(string $url): Universe
    {
        $this->url = $url;
        return clone $this;
    }

    public function setPassword(string $password): Universe
    {
        $this->password = $password;
        return clone $this;
    }

    public function setTokenFile(string $tokenFile): Universe
    {
        $this->tokenFile = $tokenFile;
        return clone $this;
    }

    public function getHttpClient(): ClientInterface
    {
        return $this->container->get(ClientInterface::class);
    }

    public function getHttpRequest(): RequestInterface
    {
        $factory = $this->container->get(RequestFactory::class);
        return $factory->build('post', $this->url);
    }

    /**
     * @return ResponseInterface
     * @throws ApiException
     */
    private function auth()
    {
        $apiKey = base64_encode($this->username);
        $apiPass = base64_encode($this->password);

        $response = $this->sendRequest(
            [
                'method' => 'token',
                'apikey' => $apiKey,
                'apiPass' => $apiPass
            ]
        );

        return (bool)file_put_contents($this->tokenFile, $response['code']);
    }

    /**
     * @param array $body
     * @return array
     * @throws ApiException
     */
    private function call(array $body): array
    {
        try {
            return $this->sendRequest($body, $this->getToken());
        } catch (ApiException $e) {
            if (($e->getCode() === self::AUTH_ERROR_CODE) && $this->auth()) {
                return $this->sendRequest($body, $this->getToken());
            }

            throw $e;
        }
    }

    private function sendRequest(array $body, ?string $token = null): array
    {
        $factory = $this->container->get(RequestFactory::class);
        $stream = $factory->createStream(\json_encode($body));

        $client = $this->getHttpClient();
        $request = $this->getHttpRequest()
            ->withMethod('post')
            ->withBody($stream)
            ->withHeader('content-type', 'application/json');

        if (null !== $token) {
            $request = $request->withHeader('Token', $token);
        }

        if (isset($this->httpAuthorization)) {
            $request = $request->withHeader('authorization', $this->httpAuthorization);
        }

        $body = $client->sendRequest($request)->getBody();
        $content = $body->read($body->getSize());

        $response = \json_decode($content, true);

        if ($response['errorCode'] > 0) {
            throw new ApiException($response['message'], $response['errorCode']);
        }

        return $response;
    }

    private function getToken()
    {
        if (\file_exists($this->tokenFile) && \is_file($this->tokenFile)) {
            return $this->token = file_get_contents($this->tokenFile);
        }

        return null;
    }
}
