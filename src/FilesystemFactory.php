<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;

final class FilesystemFactory implements FilesystemFactoryInterface
{
    public function create(AdapterInterface $adapter): FilesystemInterface
    {
        return new Filesystem($adapter);
    }
}
