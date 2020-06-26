<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Request;

use EMS\SubmissionBundle\Config\Config;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class HttpRequest
{
    /** @var array<string, string|array> */
    private $endPoint;
    /** @var string */
    private $message;

    private const OPTIONS = [
        'auth_basic' => null,
        'auth_bearer' => null,
        'headers' => [],
        'timeout' => 30,
        'query' => [],
    ];

    public function __construct(Config $config)
    {
        $this->endPoint = $this->resolveEndpoint($config->getEndpointFromJson());
        $this->message = $config->getMessage();
    }

    public function getMethod(): string
    {
        $method = $this->endPoint['method'] ?? '';

        return is_string($method) ? $method : '';
    }

    public function getUrl(): string
    {
        $url = $this->endPoint['url'] ?? '';

        return is_string($url) ? $url : '';
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        $options = [
            'body' => $this->message,
        ];

        foreach (self::OPTIONS as $optionName => $default) {
            $options[$optionName] = $this->endPoint[$optionName] ?? $default;
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
            ->setDefaults(array_merge(self::OPTIONS, ['method' => Request::METHOD_POST]))
        ;

        try {
            return $optionsResolver->resolve($endpoint);
        } catch (ExceptionInterface $e) {
            throw new \RuntimeException(sprintf('Invalid endpoint configuration: %s', $e->getMessage()));
        }
    }
}
