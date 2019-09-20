<?php

namespace EMS\SubmissionBundle\Submit;

use EMS\FormBundle\Submit\AbstractResponse;

class ServiceNowResponse extends AbstractResponse
{
    /** @var string */
    private $number;

    public function __construct(string $json, AbstractResponse $previousResponse = null)
    {
        parent::__construct($previousResponse);
        $this->number = \json_decode($json, true)['result']['number'];
    }

    public function getResponse(): string
    {
        return sprintf('Ticket created with follow-up number %s', $this->number);
    }

    public function getNumber(): string
    {
        return $this->number;
    }
}
