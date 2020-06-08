<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Tests\Functional\Handler;

use EMS\FormBundle\FormConfig\FormConfig;
use EMS\FormBundle\FormConfig\SubmissionConfig;
use EMS\FormBundle\Submit\AbstractResponse;
use EMS\SubmissionBundle\Handler\EmailHandler;
use EMS\SubmissionBundle\Tests\Functional\AbstractFunctionalTest;
use Swift_Events_SendEvent;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class EmailHandlerTest  extends AbstractFunctionalTest
{
    /** @var EmailHandler */
    protected $emailHandler;
    /** @var \Swift_Mailer */
    protected $mailer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->emailHandler = $this->container->get('functional_test.emss.emailhandler');
        $this->mailer = $this->container->get('mailer');
    }

    public function testSubmitFormData(): void
    {
        $data = ['name' => 'David', 'email' => 'user1@test.test',];
        $form = $this->formFactory->createBuilder(FormType::class, $data, [])
            ->add('name', TextType::class)
            ->add('email', EmailType::class)
            ->getForm();

        $endpoint = '{{ data.email }}';
        $message = json_encode([
            'from' => 'noreply@test.test',
            'subject' => 'Test submission',
            'body' => 'Hi my name is {{ data.name }}'
        ]);

        $handle = $this->handle($form, $endpoint, $message, function (Swift_Events_SendEvent $evt) {
            $this->assertEquals(['user1@test.test'], array_keys($evt->getMessage()->getTo()));
            $this->assertEquals(['noreply@test.test'], array_keys($evt->getMessage()->getFrom()));
            $this->assertEquals('Test submission', $evt->getMessage()->getSubject());
            $this->assertEquals('Hi my name is David', $evt->getMessage()->getBody());
        });

        $this->assertEquals('{"status":"success","data":"Submission send by mail."}', $handle->getResponse());
    }

    public function testSubmitMultipleFiles(): void
    {
        $data = ['name' => 'David', 'files' => [
            new UploadedFile(__DIR__ . '/../../files/attachment.txt', 'attachment.txt', 'text/plain'),
            new UploadedFile(__DIR__ . '/../../files/attachment2.txt', 'attachment2.txt', 'text/plain'),
        ]];
        $form = $this->formFactory->createBuilder(FormType::class, $data, [])
            ->add('name', TextType::class)
            ->add('files', FileType::class, ['multiple' => true])
            ->getForm();

        $endpoint = 'test@example.com';
        $message = json_encode([
            'from' => 'noreply@test.test',
            'subject' => 'Test multiple file upload',
            'body' => "{{ data.files|map(v => v.getClientOriginalName())|join(' | ') }}",
            'attachments' => [
                'file1' => [
                    'pathname' => '{{ data.files.0.getPathname()|json_encode }}',
                    'originalName' => '{{ data.files.0.getClientOriginalName() }}',
                    'mimeType' => '{{ data.files.0.getClientMimeType() }}'
                ],
                'file2' => [
                    'pathname' => '{{ data.files.1.getPathname()|json_encode }}',
                    'originalName' => '{{ data.files.1.getClientOriginalName() }}',
                    'mimeType' => '{{ data.files.1.getClientMimeType() }}'
                ]
            ]
        ]);

        $handle = $this->handle($form, $endpoint, $message, function (Swift_Events_SendEvent $evt) {
            $this->assertEquals('attachment.txt | attachment2.txt', $evt->getMessage()->getBody());

            /** @var \Swift_Attachment[] $children */
            $children = $evt->getMessage()->getChildren();

            $this->assertEquals('attachment.txt', $children[0]->getFilename());
            $this->assertEquals('Text example attachment', $children[0]->getBody());

            $this->assertEquals('attachment2.txt', $children[1]->getFilename());
            $this->assertEquals('Text example attachment2', $children[1]->getBody());
        });

        $this->assertEquals('{"status":"success","data":"Submission send by mail."}', $handle->getResponse());
    }

    public function testEmptyEndpoint(): void
    {
        $form = $this->formFactory->createBuilder(FormType::class, [], [])->getForm();
        $message = json_encode([
            'from' => 'noreply@test.test',
            'subject' => 'Test submission',
            'body' => 'example'
        ]);
        $handle = $this->handle($form, '', $message);

        $this->assertEquals(
            '{"status":"error","data":"Submission failed, contact your admin. Address in mailbox given [] does not comply with RFC 2822, 3.6.2."}',
            $handle->getResponse()
        );
    }

    public function testInvalidMessage(): void
    {
        $form = $this->formFactory->createBuilder(FormType::class, [], [])->getForm();

        $handle = $this->handle($form, 'user@example.com', '', function (Swift_Events_SendEvent $evt) {
            $this->assertEquals(['user@example.com'], array_keys($evt->getMessage()->getTo()));
            $this->assertEquals(['noreply@elasticms.eu'], array_keys($evt->getMessage()->getFrom()));
            $this->assertEquals('Email submission', $evt->getMessage()->getSubject());
            $this->assertEquals('', $evt->getMessage()->getBody());
        });

        $this->assertEquals(
            '{"status":"success","data":"Submission send by mail."}',
            $handle->getResponse()
        );
    }

    public function testFailedRecipients(): void
    {
        $form = $this->formFactory->createBuilder(FormType::class, [], [])->getForm();

        $this->mailer->registerPlugin(new class implements \Swift_Events_SendListener {
            public function beforeSendPerformed(Swift_Events_SendEvent $evt)
            {
                throw new \Swift_RfcComplianceException('test');
            }

            public function sendPerformed(Swift_Events_SendEvent $evt)
            {
            }
        });

        $this->assertEquals(
            '{"status":"error","data":"Submission failed. Conctact your admin."}',
            $this->handle($form, 'user@example.com', '')->getResponse()
        );
    }

    private function handle(FormInterface $form, string $endpoint, string $message, callable $emailAssert = null): AbstractResponse
    {
        $submission = new SubmissionConfig(EmailHandler::class, $endpoint, $message);
        $formConfig = new FormConfig('1', 'nl', 'nl');

        if ($emailAssert) {
            $this->mailer->registerPlugin(new class($emailAssert) implements \Swift_Events_SendListener {
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
                    $callback = $this->callback;
                    $callback($evt);
                }
            });
        }

        return $this->emailHandler->handle($submission, $form, $formConfig);
    }
}