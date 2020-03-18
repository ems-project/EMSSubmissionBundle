<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\FormConfig;

use EMS\SubmissionBundle\Submit\RenderedSubmission;

final class PdfConfig extends AbstractConfig
{
    /** @var array */
    private $endpoint;
    /** @var string */
    private $repository;
    /** @var string */
    private $directory;
    /** @var string */
    private $filename;
    /** @var string */
    private $body = '';

    public function __construct(RenderedSubmission $submission)
    {
        $this->endpoint = \json_decode($submission->getEndpoint(), true);
        $this->repository = $this->endpoint['repository'];
        $this->directory = $this->endpoint['directory'];

        $message = \json_decode($submission->getMessage(), true);
        $this->filename = $message['filename'];

        if (!empty($message['body'])) {
            $this->body = $this->sanitiseQuotes($message['body']);
        }
    }

    public function getFullEndpoint(): string
    {
        return $this->repository . $this->directory;
    }

    public function getRepository(): string
    {
        return $this->repository;
    }

    public function getDirectory(): string
    {
        return $this->directory;
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
