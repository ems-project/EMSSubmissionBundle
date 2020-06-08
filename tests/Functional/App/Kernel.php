<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Tests\Functional\App;

use EMS\SubmissionBundle\EMSSubmissionBundle;
use Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Twig\Loader\ArrayLoader;

final class Kernel extends BaseKernel
{
    public function getCacheDir()
    {
        return __DIR__ . '/../../tmp/cache/' .$this->environment;
    }

    public function getLogDir()
    {
        return __DIR__ . '/../../tmp/log';
    }

    public function registerBundles(): array
    {
        return [
            new EMSSubmissionBundle(),
            new SwiftmailerBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/config/config.yml');
    }

    protected function build(ContainerBuilder $container): void
    {
        $definitionTwig = new Definition(\Twig_Environment::class, [
            new Definition(ArrayLoader::class, [[]]),
            ['debug' => true, 'cache' => false]
        ]);

        $container->setDefinition('twig', $definitionTwig);
    }
}