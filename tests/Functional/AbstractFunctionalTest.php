<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Tests\Functional;

use EMS\SubmissionBundle\Tests\Functional\App\Kernel;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\StringInput;

abstract class AbstractFunctionalTest extends TestCase
{
    /** @var ContainerInterface */
    protected $container;

    protected function setUp(): void
    {
        parent::setUp();
        
        $kernel = new Kernel('test', true);
        $kernel->boot();

        $this->container = $kernel->getContainer();
    }
}
