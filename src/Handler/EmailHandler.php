<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Handler;

use EMS\FormBundle\Submission\AbstractHandler;
use EMS\FormBundle\Submission\FailedHandleResponse;
use EMS\FormBundle\Submission\HandleRequestInterface;
use EMS\FormBundle\Submission\HandleResponseInterface;
use EMS\SubmissionBundle\Request\EmailRequest;
use EMS\SubmissionBundle\Response\EmailHandleResponse;
use EMS\SubmissionBundle\Twig\TwigRenderer;

final class EmailHandler extends AbstractHandler
{
    /** @var \Swift_Mailer */
    private $mailer;
    /** @var TwigRenderer */
    private $twigRenderer;

    public function __construct(\Swift_Mailer $mailer, TwigRenderer $twigRenderer)
    {
        $this->mailer = $mailer;
        $this->twigRenderer = $twigRenderer;
    }

    public function handle(HandleRequestInterface $handleRequest): HandleResponseInterface
    {
        try {
            $endpoint = $this->twigRenderer->renderEndpoint($handleRequest);
            $message = $this->twigRenderer->renderMessageJSON($handleRequest);

            $emailRequest = new EmailRequest($endpoint, $message);
            $message = (new \Swift_Message($emailRequest->getSubject()))
                ->setFrom($emailRequest->getFrom())
                ->setTo($emailRequest->getEndpoint())
                ->setBody($emailRequest->getBody());

            foreach ($this->createAttachments($emailRequest) as $attachment) {
                $message->attach($attachment);
            }

            $failedRecipients = [];
            $this->mailer->send($message, $failedRecipients);

            if ([] !== $failedRecipients) {
                throw new \RuntimeException(\sprintf('Submission configured per mail and not send to %d receipients', \count($failedRecipients)));
            }
        } catch (\Exception $exception) {
            return new FailedHandleResponse(\sprintf('Submission failed, contact your admin. %s', $exception->getMessage()));
        }

        return new EmailHandleResponse($message);
    }

    /**
     * @return \Traversable<\Swift_Attachment>
     */
    private function createAttachments(EmailRequest $emailRequest): \Traversable
    {
        foreach ($emailRequest->getAttachments() as $attachment) {
            $filename = $attachment['originalName'] ?? $attachment['filename'] ?? null;
            $mimeType = $attachment['mimeType'] ?? null;

            if (null === $filename || null === $mimeType) {
                continue;
            }

            if (isset($attachment['base64'])) {
                $data = \base64_decode($attachment['base64']);

                yield new \Swift_Attachment($data, $filename, $mimeType);
                continue;
            }

            if (isset($attachment['pathname'])) {
                $swiftAttachment = \Swift_Attachment::fromPath($attachment['pathname'], $mimeType);
                $swiftAttachment->setFilename($filename);
                yield $swiftAttachment;
            }
        }
    }
}
