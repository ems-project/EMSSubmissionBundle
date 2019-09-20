<?php

namespace EMS\SubmissionBundle\Submit;

use EMS\FormBundle\Submit\AbstractResponse;

class EmailResponse extends AbstractResponse
{
    public function getResponse(): string
    {
        return 'Submission send by mail.';
    }
}
