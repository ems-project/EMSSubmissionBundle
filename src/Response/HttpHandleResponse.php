<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Response;

use EMS\FormBundle\Submission\AbstractHandleResponse;

final class HttpHandleResponse extends AbstractHandleResponse
{
    public function __construct()
    {
        parent::__construct(self::STATUS_SUCCESS, 'Submission send by http.');
    }
}
