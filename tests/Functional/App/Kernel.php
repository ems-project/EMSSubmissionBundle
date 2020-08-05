<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Tests\Functional\App;

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
            new EMSSubmissionBundle(),
            new SwiftmailerBundle(),
            new FrameworkBundle(),
            new TwigBundle(),
            new EMSCommonBundle(),
            new DoctrineBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__.'/config/config.yml');
    }
}
