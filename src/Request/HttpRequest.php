<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Request;

use EMS\SubmissionBundle\Config\Config;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class HttpRequest
{
    public function __construct(Config $config)
    {
        $endPoint = $this->resolveEndpoint($config->getEndpointJson());
    }

    private function resolveEndpoint(array $endpoint): array
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver
            ->setRequired([
                'url',
                'method'
            ]);

        try {
            return $optionsResolver->resolve($endpoint);
        } catch (ExceptionInterface $e) {
            throw new \RuntimeException(sprintf('Invalid endpoint configuration: %s', $e->getMessage()));
        }
    }
}
