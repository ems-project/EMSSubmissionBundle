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

    public function getMessage(): string
    {
        return $this->message;
    }
}
