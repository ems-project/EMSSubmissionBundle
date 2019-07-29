<?php

namespace EMS\SubmissionBundle\Handler;

use EMS\FormBundle\FormConfig\FormConfig;
use EMS\SubmissionBundle\FormConfig\EmailConfig;
use EMS\SubmissionBundle\FormConfig\SubmissionConfig;
use Symfony\Component\Form\FormInterface;

class EmailHandler extends AbstractHandler
{
    /** @var \Swift_Mailer */
    private $mailer;

    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function handle(SubmissionConfig $submission, FormInterface $form, FormConfig $config): string
    {
        $endpointTemplate = $this->templating->createTemplate($submission->getEndpoint());
        $endpoint = $endpointTemplate->render(['config' => $config, 'data' => $form->getData()]);

        $messageTemplate = $this->templating->createTemplate($submission->getMessage());
        $message = $messageTemplate->render(['config' => $config, 'data' => $form->getData()]);

        $email = new EmailConfig($endpoint, $message);
        try {
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