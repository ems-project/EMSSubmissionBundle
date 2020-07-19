<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle;

use League\Flysystem\AdapterInterface;
use League\Flysystem\FilesystemInterface;

interface FilesystemFactoryInterface
{
    public function create(AdapterInterface $adapter): FilesystemInterface;
}
