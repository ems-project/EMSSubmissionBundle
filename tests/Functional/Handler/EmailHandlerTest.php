<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Tests\Functional\Handler;

use EMS\FormBundle\Submission\AbstractHandler;
use Swift_Events_SendEvent;

final class EmailHandlerTest extends AbstractHandlerTest
{
    /** @var \Swift_Mailer */
    private $mailer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mailer = $this->container->get('mailer');
    }

    protected function getHandler(): AbstractHandler
    {
        return $this->container->get('functional_test.emss.handler.email');
    }

    public function testSubmitFormData(): void
    {
        $endpoint = '{{ data.email }}';
        $message = \json_encode([
            'from' => 'noreply@test.test',
            'subject' => 'Test submission',
            'body' => 'Hi my name is {{ data.first_name }} {{ data.last_name }}',
        ]);

        $this->mailListener(function (Swift_Events_SendEvent $evt) {
            $this->assertEquals(['user1@test.test'], \array_keys($evt->getMessage()->getTo()));
            $this->assertEquals(['noreply@test.test'], \array_keys($evt->getMessage()->getFrom()));
            $this->assertEquals('Test submission', $evt->getMessage()->getSubject());
            $this->assertEquals('Hi my name is testFirstName testLastName', $evt->getMessage()->getBody());
        });

        $this->assertEquals(
            '{"status":"success","data":"Submission send by mail."}',
            $this->handle($this->createForm(), $endpoint, $message)->getResponse()
        );
    }

    public function testSubmitMultipleFiles(): void
    {
        $endpoint = 'test@example.com';
        $message = \file_get_contents(__DIR__.'/../fixtures/twig/message_email.twig');

        $this->mailListener(function (Swift_Events_SendEvent $evt) {
            $this->assertEquals('attachment.txt | attachment2.txt', $evt->getMessage()->getBody());

            /** @var \Swift_Attachment[] $attachments */
            $attachments = $evt->getMessage()->getChildren();

            $this->assertEquals('attachment.txt', $attachments[0]->getFilename());
            $this->assertEquals('Text example attachment', $attachments[0]->getBody());

            $this->assertEquals('attachment2.txt', $attachments[1]->getFilename());
            $this->assertEquals('Text example attachment2', $attachments[1]->getBody());
        });

        $this->assertEquals(
            '{"status":"success","data":"Submission send by mail."}',
            $this->handle($this->createFormUploadFiles(), $endpoint, $message)->getResponse()
        );
    }

    public function testEmptyEndpoint(): void
    {
        $message = \json_encode([
            'from' => 'noreply@test.test',
            'subject' => 'Test submission',
            'body' => 'example',
        ]);

        $this->assertEquals(
            '{"status":"error","data":"Submission failed, contact your admin. Address in mailbox given [] does not comply with RFC 2822, 3.6.2."}',
            $this->handle($this->createForm(), '', $message)->getResponse()
        );
    }

    public function testEmptyMessage(): void
    {
        $this->assertEquals(
            '{"status":"error","data":"Submission failed, contact your admin. From email address not defined."}',
            $this->handle($this->createForm(), 'user@example.com', '')->getResponse()
        );
    }

    public function testFailedRecipients(): void
    {
        $message = \json_encode(['from' => 'noreply@elasticms.eu']);

        $this->mailer->registerPlugin(new class() implements \Swift_Events_SendListener {
            public function beforeSendPerformed(Swift_Events_SendEvent $evt)
            {
                throw new \Swift_RfcComplianceException('test');
            }

            public function sendPerformed(Swift_Events_SendEvent $evt)
            {
            }
        });

        $this->assertEquals(
            '{"status":"error","data":"Submission failed, contact your admin. Submission configured per mail and not send to 1 recipients"}',
            $this->handle($this->createForm(), 'user@example.com', $message)->getResponse()
        );
    }

    private function mailListener(callable $callback): void
    {
        $this->mailer->registerPlugin(new class($callback) implements \Swift_Events_SendListener {
            /** @var callable */
            private $callback;

            public function __construct(callable $callback)
            {
                $this->callback = $callback;
            }

            public function beforeSendPerformed(Swift_Events_SendEvent $evt)
            {
            }

            public function sendPerformed(Swift_Events_SendEvent $evt)
            {
                ($this->callback)($evt);
            }
        });
    }
}
