<?php

namespace EMS\SubmissionBundle\Submission\ServiceNow;

class ServiceNowTableResponse
{
    /** @var string */
    private $number;

    public function __construct(string $json)
    {
        $this->number = \json_decode($json, true)['result']['number'];
    }

    public function getNumber(): string
    {
        return $this->number;
    }
}