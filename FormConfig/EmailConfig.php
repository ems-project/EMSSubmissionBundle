<?php

namespace EMS\SubmissionBundle\FormConfig;

use EMS\SubmissionBundle\Submit\RenderedSubmission;

class EmailConfig
{
    /** @var string */
    private $endpoint;
    /** @var string */
    private $from;
    /** @var string */
    private $subject;
    /** @var string */
    private $body = '';
    /** @var array */
    private $attachments = [] ;

    public function __construct(RenderedSubmission $submission)
    {
        $this->endpoint = $submission->getEndpoint();
        $message = \json_decode($submission->getMessage(), true);

        $this->from = $message['from'];
        $this->subject = $message['subject'];

        if (!empty($message['body'])) {
            $this->body = $this->sanitiseQuotes($message['body']);
        }

        if (!empty($message['attachments'])) {
            $this->attachments = $this->sanitiseAttachments($message['attachments']);
        }
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

    public function getAttachments(): array
    {
        return $this->attachments;
    }

    private function sanitiseQuotes(string $string): ?string
    {
        return preg_replace('/^&quot;|&quot;$/', '', $string);
    }

    private function sanitiseAttachments(array $attachments): array
    {
        $callback = function ($attachment) {
            return $this->sanitiseQuotes($attachment);
        };

        $func = function ($item) use (&$func, &$callback) {
            return is_array($item) ? array_map($func, $item) : call_user_func($callback, $item);
        };

        return array_map($func, $attachments);
    }
}
