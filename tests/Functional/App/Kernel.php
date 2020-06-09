<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Tests\Functional\App;

use EMS\SubmissionBundle\EMSSubmissionBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

final class Kernel extends BaseKernel
{
    public function getCacheDir()
    {
        return __DIR__.'/../../tmp/cache/'.$this->environment;
    }

    public function getLogDir()
    {
        return __DIR__.'/../../tmp/log';
    }

    public function registerBundles(): array
    {
        return [
            new EMSSubmissionBundle(),
            new SwiftmailerBundle(),
            new FrameworkBundle(),
            new TwigBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__.'/config/config.yml');
    }
}
