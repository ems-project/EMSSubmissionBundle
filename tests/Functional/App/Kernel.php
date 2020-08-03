<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Tests\Functional\App;

use DAMA\DoctrineTestBundle\DAMADoctrineTestBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use EMS\CommonBundle\EMSCommonBundle;
use EMS\SubmissionBundle\EMSSubmissionBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

final class Kernel extends BaseKernel
{
    public static function getPath(): string
    {
        return __DIR__.'/../../tmp/functional';
    }

    public function getProjectDir()
    {
        return __DIR__.'/../../';
    }

    public function getCacheDir()
    {
        return self::getPath().'/cache/'.$this->environment;
    }

    public function getLogDir()
    {
        return self::getPath().'/log';
    }

    public function registerBundles(): array
    {
        return [
            new FrameworkBundle(),
            new SwiftmailerBundle(),
            new TwigBundle(),
            new DoctrineBundle(),
            new DAMADoctrineTestBundle(),
            new EMSCommonBundle(),
            new EMSSubmissionBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__.'/config/config.yml');
    }
}
