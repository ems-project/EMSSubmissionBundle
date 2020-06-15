<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Handler;

use EMS\FormBundle\Submission\AbstractHandler;
use EMS\FormBundle\Submission\FailedHandleResponse;
use EMS\FormBundle\Submission\HandleRequestInterface;
use EMS\FormBundle\Submission\HandleResponseInterface;
use EMS\SubmissionBundle\Config\ConfigFactory;
use EMS\SubmissionBundle\Request\EmailRequest;
use EMS\SubmissionBundle\Response\EmailHandleResponse;

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

    public function handle(HandleRequestInterface $handleRequest): HandleResponseInterface
    {
        try {
            $config = $this->configFactory->create($handleRequest);
            $emailRequest = new EmailRequest($config);
            $message = (new \Swift_Message($emailRequest->getSubject()))
                ->setFrom($emailRequest->getFrom())
                ->setTo($emailRequest->getEndpoint())
                ->setBody($emailRequest->getBody());

            foreach ($this->createAttachments($emailRequest) as $attachment) {
                $message->attach($attachment);
            }
        } catch (\Exception $exception) {
            return new FailedHandleResponse(sprintf('Submission failed, contact your admin. %s', $exception->getMessage()));
        }

        $failedRecipients = [];
        $this->mailer->send($message, $failedRecipients);

        if ($failedRecipients !== []) {
            return new FailedHandleResponse('Submission failed. Conctact your admin.');
        }

        return new EmailHandleResponse($message);
    }

    /**
     * @return \Traversable<\Swift_Attachment>
     */
    private function createAttachments(EmailRequest $emailRequest): \Traversable
    {
        foreach ($emailRequest->getAttachments() as $attachment) {
            if (isset($attachment['pdf']) && isset($attachment['filename'])) {
                $data = base64_decode($attachment['pdf']);

                yield new \Swift_Attachment($data, $attachment['filename'], 'application/pdf');
                continue;
            }

            if (isset($attachment['pathname']) && isset($attachment['mimeType']) && isset($attachment['originalName'])) {
                $swiftAttachment = \Swift_Attachment::fromPath($attachment['pathname'], $attachment['mimeType']);
                $swiftAttachment->setFilename($attachment['originalName']);
                yield $swiftAttachment;
            }
        }
    }
}
