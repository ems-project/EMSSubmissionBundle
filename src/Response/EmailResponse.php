<?php

namespace EMS\SubmissionBundle\Response;

use EMS\FormBundle\Submit\AbstractResponse;

class EmailResponse extends AbstractResponse
{
    public function __construct(string $status)
    {
        parent::__construct($status, 'Submission send by mail.');
    }
}
