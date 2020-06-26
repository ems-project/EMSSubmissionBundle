<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Config;

final class Config
{
    /** @var string */
    private $endpoint;
    /** @var string */
    private $message;

    public function __construct(string $endpoint, string $message)
    {
        $this->endpoint = $endpoint;
        $this->message = $message;
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * @return array<mixed>
     */
    public function getEndpointFromJson(): array
    {
        return \json_decode($this->endpoint, true) ?? [];
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return array<mixed>
     */
    public function getMessageFromJson(): array
    {
        return \json_decode($this->message, true) ?? [];
    }
}
