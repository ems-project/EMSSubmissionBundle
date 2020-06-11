<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Response;

use EMS\FormBundle\Submission\AbstractHandleResponse;

final class EmailHandleResponse extends AbstractHandleResponse
{
    /** @var \Swift_Message */
    private $message;

    public function __construct(\Swift_Message $message)
    {
        $this->message = $message;

        parent::__construct(self::STATUS_SUCCESS, 'Submission send by mail.');
    }

    public function getMessage(): \Swift_Message
    {
        return $this->message;
    }
}
