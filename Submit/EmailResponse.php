<?php

namespace EMS\SubmissionBundle\Submit;

use EMS\FormBundle\Submit\AbstractResponse;

class EmailResponse extends AbstractResponse
{
    public function __construct(string $status)
    {
        parent::__construct($status, 'Submission send by mail.');
    }
}
