<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Request;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class HttpRequest
{
    /** @var array<string, string|array> */
    private $endpoint;
    /** @var string */
    private $body;

    private const OPTIONS = [
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
        $this->endpoint = $this->resolveEndpoint($endpoint);
        $this->body = $body;
    }

    public function getMethod(): string
    {
        $method = $this->endpoint['method'] ?? '';

        return \is_string($method) ? $method : '';
    }

    public function getUrl(): string
    {
        $url = $this->endpoint['url'] ?? '';

        return \is_string($url) ? $url : '';
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        $options = [
            'body' => $this->body,
        ];

        foreach (self::OPTIONS as $optionName => $default) {
            $options[$optionName] = $this->endpoint[$optionName] ?? $default;
        }

        return $options;
    }

    /**
     * @param array<string, mixed> $endpoint
     *
     * @return array<string, mixed>
     */
    private function resolveEndpoint(array $endpoint): array
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver
            ->setRequired(['url', 'method'])
            ->setDefaults(\array_merge(self::OPTIONS, ['method' => Request::METHOD_POST]))
        ;

        try {
            return $optionsResolver->resolve($endpoint);
        } catch (ExceptionInterface $e) {
            throw new \RuntimeException(\sprintf('Invalid endpoint configuration: %s', $e->getMessage()));
        }
    }
}
