<?php

namespace EMS\SubmissionBundle\FormConfig;

use EMS\SubmissionBundle\Submit\RenderedSubmission;

class EmailConfig extends AbstractConfig
{
    /** @var string */
    private $endpoint;
    /** @var string */
    private $from;
    /** @var string */
    private $subject;
    /** @var string */
    private $body = '';
    /** @var array<array-key, string> */
    private $attachments = [] ;

    public function __construct(RenderedSubmission $submission)
    {
        $this->endpoint = $submission->getEndpoint();
        $message = \json_decode($submission->getMessage(), true);

        $this->from = $message['from'];
        $this->subject = $message['subject'];

        if (!empty($message['body'])) {
            $this->body = $this->sanitiseQuotes($message['body']) ?? '';
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

    /**
     * @return array<array-key, string>
     */
    public function getAttachments(): array
    {
        return $this->attachments;
    }
}
