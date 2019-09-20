<?php

namespace EMS\SubmissionBundle\Handler;

use EMS\FormBundle\FormConfig\FormConfig;
use EMS\FormBundle\Handler\AbstractHandler;
use EMS\FormBundle\FormConfig\SubmissionConfig;
use EMS\FormBundle\Submit\AbstractResponse;
use EMS\FormBundle\Submit\FailedResponse;
use EMS\FormBundle\Submit\ResponseCollector;
use EMS\SubmissionBundle\FormConfig\EmailConfig;
use EMS\SubmissionBundle\Service\SubmissionRenderer;
use EMS\SubmissionBundle\Submit\EmailResponse;
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

    public function handle(SubmissionConfig $submission, FormInterface $form, FormConfig $config, AbstractResponse $response = null): AbstractResponse
    {
        try {
            $renderedSubmission = $this->renderer->render($submission, $form, $config, $response);
            $email = new EmailConfig($renderedSubmission);
            $message = (new \Swift_Message($email->getSubject()))
                ->setFrom($email->getFrom())
                ->setTo($email->getEndpoint())
                ->setBody($email->getBody());
        } catch (\Exception $exception) {
            return new FailedResponse(sprintf('Submission failed, contact your admin. %s', $exception->getMessage()));
        }

        $failedRecipients = [];
        $this->mailer->send($message, $failedRecipients);

        if ($failedRecipients !== []) {
            return new FailedResponse('Submission failed. Conctact your admin.');
        }

        return new EmailResponse();
    }
}
