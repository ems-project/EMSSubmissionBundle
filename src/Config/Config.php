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

    public function getEndpointJson(): array
    {
        return json_decode($this->endpoint, true) ?? [];
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getMessageJson(): array
    {
        return json_decode($this->message, true) ?? [];
    }
}
