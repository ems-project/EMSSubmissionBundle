<?php

namespace EMS\SubmissionBundle\Handler;

use EMS\FormBundle\FormConfig\FormConfig;
use EMS\SubmissionBundle\FormConfig\EmailConfig;
use EMS\SubmissionBundle\FormConfig\SubmissionConfig;
use EMS\SubmissionBundle\Service\SubmissionRenderer;
use Symfony\Component\Form\FormInterface;

class EmailHandler extends AbstractHandler
{
    /** @var \Swift_Mailer */
    private $mailer;
    /** @var SubmissionRenderer */
    private $renderer;

    public function __construct(\Swift_Mailer $mailer, SubmissionRenderer $renderer)
    {
        $this->mailer = $mailer;
        $this->renderer = $renderer;
    }

    public function handle(SubmissionConfig $submission, FormInterface $form, FormConfig $config): string
    {
        try {
            $renderedSubmission = $this->renderer->render($submission, $form, $config);
            $email = new EmailConfig($renderedSubmission);
            $message = (new \Swift_Message($email->getSubject()))
                ->setFrom($email->getFrom())
                ->setTo($email->getEndpoint())
                ->setBody($email->getBody());
        } catch (\Exception $exception) {
            return sprintf('Submission failed, contact your admin. %s', $exception->getMessage());
        }

        $failedRecipients = [];
        $this->mailer->send($message, $failedRecipients);

        if ($failedRecipients !== []) {
            return 'Submission failed. Conctact your admin.';
        }

        return 'Submission send by mail.';
    }
}
