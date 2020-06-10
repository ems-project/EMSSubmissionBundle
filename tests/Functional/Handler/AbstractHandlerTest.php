<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Tests\Functional\Handler;

use EMS\FormBundle\FormConfig\FormConfig;
use EMS\FormBundle\FormConfig\SubmissionConfig;
use EMS\FormBundle\Handler\AbstractHandler;
use EMS\FormBundle\Submit\AbstractResponse;
use EMS\SubmissionBundle\Tests\Functional\AbstractFunctionalTest;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class AbstractHandlerTest extends AbstractFunctionalTest
{
    /** @var FormFactoryInterface */
    protected $formFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->formFactory = Forms::createFormFactoryBuilder()->getFormFactory();
    }

    abstract protected function getHandler(): AbstractHandler;

    protected function handle(FormInterface $form, string $endpoint, string $message): AbstractResponse
    {
        $handler = $this->getHandler();

        $submission = new SubmissionConfig(get_class($handler), $endpoint, $message);
        $formConfig = new FormConfig('1', 'nl', 'nl');

        return $handler->handle($submission, $form, $formConfig);
    }

    protected function createForm(): FormInterface
    {
        $data = [
            'first_name' => 'testFirstName',
            'last_name' => 'testLastName',
            'email' => 'user1@test.test',
        ];

        return $this->formFactory->createBuilder(FormType::class, $data, [])
            ->add('first_name', TextType::class)
            ->add('last_name', TextType::class)
            ->add('email', EmailType::class)
            ->getForm();
    }

    protected function createFormUploadFiles(): FormInterface
    {
        $data = [
            'info' => 'Uploaded 2 files',
            'files' => [
                new UploadedFile(__DIR__.'/../../files/attachment.txt', 'attachment.txt', 'text/plain'),
                new UploadedFile(__DIR__.'/../../files/attachment2.txt', 'attachment2.txt', 'text/plain'),
            ],
        ];

        return $this->formFactory->createBuilder(FormType::class, $data, [])
            ->add('info', TextType::class)
            ->add('files', FileType::class, ['multiple' => true])
            ->getForm();
    }
}
