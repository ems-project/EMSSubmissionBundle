<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Tests\Functional\App;

use EMS\SubmissionBundle\FilesystemFactoryInterface;
use League\Flysystem\Adapter\NullAdapter;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;

final class FilesystemFactory implements FilesystemFactoryInterface
{
    /**
     * When raised the create function will add a NullAdapter.
     *
     * @var bool
     */
    private $flagNullAdapter = true;

    public function create(AdapterInterface $adapter): FilesystemInterface
    {
        $adapter = $this->flagNullAdapter ? new NullAdapter() : $adapter;

        return new Filesystem($adapter);
    }

    public function setFlagNullAdapter(bool $flag): void
    {
        $this->flagNullAdapter = $flag;
    }
}
