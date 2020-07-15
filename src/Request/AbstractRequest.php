<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Request;

use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractRequest
{
    abstract protected function getEndpointOptionResolver(): OptionsResolver;

    /**
     * @param array<string, mixed> $endpoint
     *
     * @return array<string, mixed>
     */
    protected function resolveEndpoint(array $endpoint): array
    {
        try {
            return $this->getEndpointOptionResolver()->resolve($endpoint);
        } catch (ExceptionInterface $e) {
            throw new \RuntimeException(\sprintf('Invalid endpoint configuration: %s', $e->getMessage()));
        }
    }
}
