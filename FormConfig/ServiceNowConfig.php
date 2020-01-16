<?php

namespace EMS\SubmissionBundle\FormConfig;

use EMS\SubmissionBundle\Submit\RenderedSubmission;

class ServiceNowConfig
{
    /** @var string */
    private $host;
    /** @var string */
    private $table;
    /** @var string */
    private $bodyEndpoint = '/api/now/table';
    /** @var string */
    private $attachmentEndpoint = '/api/now/attachment/file';
    /** @var string */
    private $username;
    /** @var string */
    private $password;

    /** @var string */
    private $body = '';
    /** @var array */
    private $attachments = [];

    public function __construct(RenderedSubmission $submission)
    {
        $endpoint = \json_decode($submission->getEndpoint(), true);
        $message = \json_decode($submission->getMessage(), true);

        $this->host = $endpoint['host'];
        $this->table = $endpoint['table'];
        $this->username = $endpoint['username'];
        $this->password = $endpoint['password'];

        if (!empty($endpoint['bodyEndpoint'])) {
            $this->bodyEndpoint = $endpoint['bodyEndpoint'];
        }

        if (!empty($endpoint['attachmentEndpoint'])) {
            $this->bodyEndpoint = $endpoint['attachmentEndpoint'];
        }

        if (!empty($message['body'])) {
            $body = \json_encode($message['body']);
            $this->body = (!empty($body)) ? $body : '';
        }

        if (!empty($message['attachments'])) {
            $this->attachments = $this->sanitiseAttachments($message['attachments']);
        }
    }

    public function getBodyEndpoint(): string
    {
        return $this->host . $this->bodyEndpoint . '/' . $this->table;
    }

    public function getAttachmentEndpoint(): string
    {
        return $this->host . $this->attachmentEndpoint;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getAttachments(): array
    {
        return $this->attachments;
    }

    public function getBasicAuth(): string
    {
        $credentials = \base64_encode(\sprintf('%s:%s', $this->getUsername(), $this->getPassword()));
        return \sprintf('Basic %s', $credentials);
    }

    private function sanitiseQuotes(string $string)
    {
        return \preg_replace('/^&quot;|&quot;$/', '', $string);
    }

    private function sanitiseAttachments(array $attachments): array
    {
        $recursiveSanitizer = function ($attachment) use (&$recursiveSanitizer) {
            return \is_array($attachment) ? \array_map($recursiveSanitizer, $attachment) : $this->sanitiseQuotes($attachment);
        };

        return \array_map($recursiveSanitizer, $attachments);
    }
}
