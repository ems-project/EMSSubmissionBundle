<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Tests\Functional;

use EMS\SubmissionBundle\Tests\Functional\App\Kernel;
use PHPStan\Testing\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class AbstractFunctionalTest extends TestCase
{
    /** @var ContainerInterface */
    protected $container;
    /** @var FormFactoryInterface */
    protected $formFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $kernel = new Kernel('test', true);
        $kernel->boot();

        $this->container = $kernel->getContainer();
        $this->formFactory = Forms::createFormFactoryBuilder()->getFormFactory();
    }

    protected function createUploadFilesForm(): FormInterface
    {
        $data = [
            'name' => 'David',
            'files' => [
                new UploadedFile(__DIR__.'/../files/attachment.txt', 'attachment.txt', 'text/plain'),
                new UploadedFile(__DIR__.'/../files/attachment2.txt', 'attachment2.txt', 'text/plain'),
            ],
        ];

        return $this->formFactory->createBuilder(FormType::class, $data, [])
            ->add('name', TextType::class)
            ->add('files', FileType::class, ['multiple' => true])
            ->getForm();
    }
}
