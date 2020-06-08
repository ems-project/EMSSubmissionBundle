<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Tests\Functional;

use EMS\SubmissionBundle\Tests\Functional\App\Kernel;
use PHPStan\Testing\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Forms;

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
}
