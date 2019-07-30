<?php

namespace EMS\SubmissionBundle\Submission;

class SubmitResponse
{
    /** @var array */
    private $responses = [];

    public function addResponse(string $response): void
    {
        $this->responses[] = $response;
    }

    public function getResponses(): array
    {
        return $this->responses;
    }
}