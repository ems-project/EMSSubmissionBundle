<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Submit;

use EMS\FormBundle\Submit\AbstractResponse;

final class PdfResponse extends AbstractResponse
{
    public function __construct(string $status)
    {
        parent::__construct($status, 'Submission store in PDF.');
    }
}
