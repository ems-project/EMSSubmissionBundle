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
    private $body;
    /** @var array */
    private $attachments;

    public function __construct(RenderedSubmission $submission)
    {
        $this->endpoint = $submission->getEndpoint();
        $message = \json_decode($submission->getMessage(), true);

        $this->from = $message['from'];
        $this->subject = $message['subject'];
        $this->body = preg_replace('/^&quot;|&quot;$/', '', $message['body']);

        $this->attachments = trim(preg_replace('/^&quot;|&quot;$/', '', $message['attachments']));
        $this->attachments = preg_split("/\r\n|\n|\r/", $this->attachments);
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
}
