<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Handler;

use EMS\FormBundle\FormConfig\FormConfig;
use EMS\FormBundle\FormConfig\SubmissionConfig;
use EMS\FormBundle\Handler\AbstractHandler;
use EMS\FormBundle\Submit\AbstractResponse;
use EMS\FormBundle\Submit\FailedResponse;
use EMS\SubmissionBundle\Config\ConfigFactory;
use EMS\SubmissionBundle\Request\EmailRequest;
use EMS\SubmissionBundle\Response\EmailResponse;
use Symfony\Component\Form\FormInterface;

final class EmailHandler extends AbstractHandler
{
    /** @var ConfigFactory */
    private $configFactory;
    /** @var \Swift_Mailer */
    private $mailer;

    public function __construct(ConfigFactory $configFactory, \Swift_Mailer $mailer)
    {
        $this->configFactory = $configFactory;
        $this->mailer = $mailer;
    }

    /**
     * @param FormInterface<FormInterface> $form
     */
    public function handle(SubmissionConfig $submission, FormInterface $form, FormConfig $config, AbstractResponse $previousResponse = null): AbstractResponse
    {
        try {
            $config = $this->configFactory->create($submission, $form, $config, $previousResponse);
            $emailRequest = new EmailRequest($config);
            $message = (new \Swift_Message($emailRequest->getSubject()))
                ->setFrom($emailRequest->getFrom())
                ->setTo($emailRequest->getEndpoint())
                ->setBody($emailRequest->getBody());

            $this->addAttachments($message, $emailRequest->getAttachments());
        } catch (\Exception $exception) {
            return new FailedResponse(sprintf('Submission failed, contact your admin. %s', $exception->getMessage()));
        }

        $failedRecipients = [];
        $this->mailer->send($message, $failedRecipients);

        if ($failedRecipients !== []) {
            return new FailedResponse('Submission failed. Conctact your admin.');
        }

        return new EmailResponse(AbstractResponse::STATUS_SUCCESS);
    }

    /**
     * @param array<array> $attachments
     */
    private function addAttachments(\Swift_Message $message, array $attachments): void
    {
        foreach ($attachments as $attachment) {
            $message->attach(
                \Swift_Attachment::fromPath($attachment['pathname'], $attachment['mimeType'])->setFilename($attachment['originalName'])
            );
        }
    }
}
