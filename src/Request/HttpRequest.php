<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Request;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class HttpRequest extends AbstractRequest
{
    /** @var array{method: string, url: string} */
    private $endpoint;
    /** @var string */
    private $body;

    private const HTTP_OPTIONS = [
        'auth_basic' => null,
        'auth_bearer' => null,
        'headers' => [],
        'timeout' => 30,
        'query' => [],
    ];

    /**
     * @param array<string, mixed> $endpoint
     */
    public function __construct(array $endpoint, string $body)
    {
        /** @var array{method: string, url: string} $endpoint */
        $endpoint = $this->resolveEndpoint($endpoint);

        $this->endpoint = $endpoint;
        $this->body = $body;
    }

    public function getMethod(): string
    {
        return $this->endpoint['method'];
    }

    public function getUrl(): string
    {
        return $this->endpoint['url'];
    }

    /**
     * @return array<string, mixed>
     */
    public function getHttpOptions(): array
    {
        $options = [
            'body' => $this->body,
        ];

        foreach (self::HTTP_OPTIONS as $optionName => $default) {
            $options[$optionName] = $this->endpoint[$optionName] ?? $default;
        }

        return $options;
    }

    protected function getEndpointOptionResolver(): OptionsResolver
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver
            ->setRequired(['url', 'method'])
            ->setDefaults(\array_merge(self::HTTP_OPTIONS, ['method' => Request::METHOD_POST]))
        ;

        return $optionsResolver;
    }
}
