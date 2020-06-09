<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Tests\Functional\App;

use PHPUnit\Runner\AfterLastTestHook;
use Symfony\Component\Filesystem\Filesystem;

final class ClearLogCacheListener implements AfterLastTestHook
{
    public function executeAfterLastTest(): void
    {
        (new Filesystem())->remove(Kernel::getPath());
    }
}
