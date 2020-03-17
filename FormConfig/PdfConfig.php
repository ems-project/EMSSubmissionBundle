<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\FormConfig;

use EMS\SubmissionBundle\Submit\RenderedSubmission;

final class PdfConfig extends AbstractConfig
{
    /** @var string */
    private $endpoint;
    /** @var string */
    private $filename;
    /** @var string */
    private $body = '';

    public function __construct(RenderedSubmission $submission)
    {
        $this->endpoint = \json_decode($submission->getEndpoint(), true);
        $message = \json_decode($submission->getMessage(), true);

        $this->filename = $message['filename'];

        if (!empty($message['body'])) {
            $this->body = $this->sanitiseQuotes($message['body']);
        }
    }

    public function getEndpoint(): string
    {
        return $this->endpoint['repository'] . $this->endpoint['directory'];
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getBody(): string
    {
        return $this->body;
    }
}
