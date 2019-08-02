<?php

namespace EMS\SubmissionBundle\Submit;

use EMS\FormBundle\Submit\ResponseInterface;

class ServiceNowResponse implements ResponseInterface
{
    /** @var string */
    private $number;

    public function __construct(string $json)
    {
        $this->number = \json_decode($json, true)['result']['number'];
    }

    public function getResponse(): string
    {
        return sprintf('Ticket created with follow-up number %s', $this->number);
    }
}
