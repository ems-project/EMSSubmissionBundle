<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Tests\Functional\App;

use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

final class HttpClient implements HttpClientInterface
{
    /** @var MockHttpClient */
    private $mockClient;

    public function __construct(ResponseFactory $responseFactory)
    {
        $this->mockClient = new MockHttpClient($responseFactory);
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        return $this->mockClient->request($method, $url, $options);
    }

    public function stream($responses, float $timeout = null): ResponseStreamInterface
    {
        return $this->mockClient->stream($responses, $timeout);
    }
}
