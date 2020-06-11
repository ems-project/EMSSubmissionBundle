<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Request;

use EMS\SubmissionBundle\Config\Config;

final class EmailRequest
{
    /** @var string */
    private $endpoint;
    /** @var string */
    private $from;
    /** @var string */
    private $subject;
    /** @var string */
    private $body = '';
    /** @var array<array> */
    private $attachments;

    public function __construct(Config $config)
    {
        $this->endpoint = $config->getEndpoint();
        $message = \json_decode($config->getMessage(), true);

        if (!isset($message['from'])) {
            throw new \Exception(sprintf('From email address not defined.'));
        }

        $this->from = $message['from'];
        $this->subject = $message['subject'] ?? 'Email submission';
        $this->body = $message['body'] ?? '';
        $this->attachments = $message['attachments'] ?? [];
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @return array<array>
     */
    public function getAttachments(): array
    {
        return $this->attachments;
    }
}
