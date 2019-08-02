<?php

namespace EMS\SubmissionBundle\Submit;

use EMS\FormBundle\Submit\ResponseInterface;

class EmailResponse implements ResponseInterface
{
    public function getResponse(): string
    {
        return 'Submission send by mail.';
    }
}